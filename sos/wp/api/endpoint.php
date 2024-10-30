<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\API;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

class EndPoint
{
    const ROOT = 'rapi';
    const ACTION = 'wp_rest';

    private static $nonce = '';

    private $version;
    private $method;
    private $callback;
    private $route;

    public $localized;
    private $configName;
    private $configData;

    public $nonceDisabled;

    public function __construct($method, $route, $callback, $version)
    {
        $this->method = $method;
        $this->route = $route;
        $this->callback = $callback;
        $this->version = $version;

        $this->localized = false;
        $this->configName = '';
        $this->configData = array();

        $this->nonceDisabled = false;
    }

    public static function getNonce()
    {
        if (self::$nonce == '')
        {
            self::$nonce = wp_create_nonce( self::ACTION );
        }
        return self::$nonce;
    }

    private function getNamespace()
    {
        $ret = self::ROOT;
        if (!sosidee_str_starts_with($ret, '/'))
        {
            $ret = "/$ret";
        }
        if ($this->version > 0)
        {
            if (!sosidee_str_ends_with($ret, '/'))
            {
                $ret .= '/';
            }
            $ret .= 'v'. strval($this->version);
        }
        return $ret;
    }

    private function getRoute()
    {
        $route = $this->route;
        if (!sosidee_str_starts_with($route, '/'))
        {
            $route = '/' . $route;
        }
        return $this->getNamespace() . $route;
    }

    public function getPath()
    {
        return '?rest_route=' . $this->getRoute();
    }

    public function getUrl()
    {
        return get_site_url() . '/' . $this->getPath();
    }

    public function localize($name = 'sosapi', $conf = [])
    {
        if (!array_key_exists('method', $conf))
        {
            $conf['method'] = $this->method;
        }
        if (!array_key_exists('url', $conf))
        {
            $conf['url'] = $this->getUrl(); //'?rest_route=' . $route;
        }

        if (array_key_exists('data', $conf) || $this->method == 'POST')
        {
            if (!array_key_exists('dataType', $conf))
            {
                $conf['dataType'] = 'json';
            }
            if (!array_key_exists('contentType', $conf))
            {
                $conf['contentType'] = 'application/json; charset=utf-8';
            }
            if ( $conf['dataType'] == 'json' && !empty($conf['data']) )
            {
                $conf['data'] = json_encode( $conf['data'] );
            }
        }

        $this->localized = true;
        $this->configName = $name;
        $this->configData = $conf;

    }

    public function enqueueScript()
    {
        if ($this->localized !== false)
        {
            $action = !is_admin() ? 'wp_enqueue_scripts' : 'admin_enqueue_scripts';
            add_action( $action, function() {
                if (!array_key_exists('nonce', $this->configData) && $this->nonceDisabled !== true)
                {
                    $this->configData['nonce'] = self::getNonce();
                }
                wp_register_script( self::ACTION, '', [], '', true );
                wp_enqueue_script( self::ACTION );
                wp_localize_script( self::ACTION, $this->configName, $this->configData );
            });
        }
    }

    public static function script($code)
    {
        wp_register_script( self::ACTION, '', ['jquery'], '', true );
        wp_enqueue_script( self::ACTION );
        wp_add_inline_script(self::ACTION, $code);
    }

    public function register()
    {
        $callback = $this->callback;
        if ( is_null($callback) )
        {
            $callback = function(){
                return new \WP_REST_Response( 'no callback has been defined', 418);
            };
        }

        $args = [
             'methods' => $this->method
            ,'callback' => $callback
        ];
        if ($this->nonceDisabled !== true)
        {
            $args['permission_callback'] = function () {
                $nonce = isset($_SERVER['HTTP_X_WP_NONCE']) ? $_SERVER['HTTP_X_WP_NONCE'] : '';
                return wp_verify_nonce( $nonce, self::ACTION);
            };
        }
        else
        {
            $args['permission_callback'] = function () {
                return true;
            };
        }

        register_rest_route( $this->getNamespace(), $this->route, $args );
    }

}