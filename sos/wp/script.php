<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

/**
 * Class Script
 * @package SOSIDEE_MFA_PHOTOEDITOR\SOS\WP
 *
 * @property $key
 * @property $url
 * @property $body
 */
class Script
{
    use Property
	{
        Property::__get as __getProp;
        Property::__set as __setProp;
	}

    private $dependency;
    
    public static $PLUGIN_URL = '';

    private $pages;

    public function __construct($key, $file, $dependency, $body)
    {
        $this->_addProperty('key');
        $this->_addProperty('url');
        $this->_addProperty('body');

        $this->key = $key;
        $this->url = $file;
        $this->body = $body;

        $this->dependency = $dependency;

        $this->pages = array();
    }

    public function __set($name, $value)
    {
        $ret = null;
        switch($name)
        {
            case 'url':
                if ( !sosidee_str_starts_with($value, 'http') && !sosidee_str_starts_with($value, '//') )
                {
                    if (!sosidee_str_ends_with($value, '.js'))
                    {
                        $value .= '.js';
                    }
                    $value = self::$PLUGIN_URL . "/assets/js/$value";
                }
                $ret = $this->__setProp($name, $value);
                break;
            default:
                $ret = $this->__setProp($name, $value);
        }
        return $ret;
    }

    public function addToPage()
    {
        $pages = func_get_args();
        if ( func_num_args() == 1 && is_array($pages[0]) ) {
            $pages = $pages[0];
        }
        for ($n = 0; $n < count($pages); $n++) {
            $this->pages[] = $pages[$n];
        }
        return $this;
    }

    public function html()
    {
        $action = !is_admin() ? 'wp_enqueue_scripts' : 'admin_enqueue_scripts';
        add_action( $action, function() {
            $add = !is_admin() || count($this->pages) == 0;
            if ( !$add )
            {
                for ($n=0; $n<count($this->pages); $n++)
                {
                    if ( $this->pages[$n]->isCurrent() )
                    {
                        $add = true;
                        break;
                    }
                }
            }
            if ( $add )
            {
                return wp_enqueue_script( $this->key, $this->url, $this->dependency, null, $this->body );
            }
            else
            {
                return false;
            }
        } );
        return $this;
    }
    
}