<?php
/*
Plugin Name: My FastAPP Photo Editor
Version: 2.1
Description: Allows you to select images from the Wordpress Media Library for the Photo Editor feature of the My FastAPP plugin.
Author: SOSidee.com srl
Author URI: https://sosidee.com
Text Domain: mfa-photo-editor
Domain Path: /languages
*/
namespace SOSIDEE_MFA_PHOTOEDITOR;
( defined( 'ABSPATH' ) and defined( 'WPINC' ) ) or die( 'you were not supposed to be here' );
defined('SOSIDEE') || define('SOSIDEE', true);

require_once "loader.php";

\SOSIDEE_CLASS_LOADER::instance()->add( 'SOSIDEE_MFA_PHOTOEDITOR', basename( plugin_dir_path( __FILE__ ) ) );

/**
 *  
 * Class of This Plugin *
 *
**/
class SosPlugin extends SOS\WP\Plugin
{

    public $data;

    public $dataUrl;
    public $jsonUrl;
    private $jsonFile;

    protected function __construct()
    {
        parent::__construct();

        //PLUGIN KEY & NAME 
        $this->key = 'sos-mfa-photo-editor';
        $this->name = 'MFA Photo Editor';

        $this->_addProperty('pgGallery');

        $this->data = null;

        $this->dataUrl = '';
        $this->jsonUrl = '';
        $this->jsonFile = '';

        $this->internationalize( 'mfa-photo-editor' ); //Text Domain

    }

    protected function initialize()
    {
        parent::initialize();

        $upload = wp_upload_dir();
        $data_root = '/data';
        $file = '/mfa-photo-editor.json';
        //$file = '/conf.json';
        $this->dataUrl = self::$url . $data_root;
        //$this->jsonUrl = $this->dataUrl . $file;
        $this->jsonUrl = $upload['baseurl'] . $file;
        //$this->jsonFile = self::$path. $data_root . $file;
        $this->jsonFile = $upload['basedir'] . $file;

        $this->data = new SRC\Data($this->key);
        $this->data->load();

        $this->apiSave = $this->addApiPost('sosmfa/gal/save', [$this, 'apiPostSave'] );

    }

    protected function initializeBackend()
    {
        $this->registerActivation('checkFileJson');

        //add admin page
        $this->pgGallery = $this->addPage('gallery');

        //add menu item
        $this->menu->icon = '-images-alt2';
        $this->menu->add( $this->pgGallery );

        $this->addScript('gallery')->addToPage( $this->pgGallery );
        $this->addStyle('gallery')->addToPage( $this->pgGallery );
        $this->addGoogleIcons();
        $this->addSweetAlert();

        $this->apiSave->localize( 'sosgal_api' );

        /*
        $data = [
             'copy2cb' => esc_js( self::t_('js.msg.copy.to.clipboard') )
            ,'js_ml_button' => esc_js( self::t_('js.media-lib.button') )
            ,'js_ml_title_overlay' => esc_js( self::t_('js.media-lib.overlay.title') )
            ,'js_ml_title_scenario' => esc_js( self::t_('js.media-lib.scenario.title') )
            ,'js_problem' => esc_js( self::t_('js.msg.problem.generic') )
            ,'js_img_item_title' => esc_js( self::t_('js.image.title') )
            ,'js_ico_trash_delete' => esc_js( self::t_('js.ico-trash.delete') )
            ,'js_ico_trash_undo' => esc_js( self::t_('js.ico-trash.undo') )
        ];
        */
        $this->registerLocalizedScript('sosgal_local', [$this, 'getLocalizedData'], $this->pgGallery);

        $this->addJsInitialize();

        //add_action( 'plugins_loaded', [ $this, 'checkFileJson'] );

    }

    public function getLocalizedData()
    {
        $data = [
            'copy2cb' => esc_js( self::t_('js.msg.copy.to.clipboard') )
            ,'js_ml_button' => esc_html( self::t_('js.media-lib.button') )
            ,'js_ml_title_overlay' => esc_html( self::t_('js.media-lib.overlay.title') )
            ,'js_ml_title_scenario' => esc_html( self::t_('js.media-lib.scenario.title') )
            ,'js_problem' => esc_js( self::t_('js.msg.problem.generic') )
            ,'js_img_item_title' => esc_js( self::t_('js.image.title') )
            ,'js_ico_trash_delete' => esc_js( self::t_('js.ico-trash.delete') )
            ,'js_ico_trash_undo' => esc_js( self::t_('js.ico-trash.undo') )
        ];
        return $data;
    }


    private function addJsInitialize()
    {
        $JS_LINE = "jsSosAddGalItem('{list}', '{id}', '{url}');";
        $JS_LINES = array();
        $overlays = $this->data->overlays;
        for($n=0; $n<count($overlays); $n++)
        {
            $id = $overlays[$n];
            $substitutions = [
                '{list}' => 'sos-gal-list-overlay'
                ,'{id}' => $id
                ,'{url}' => wp_get_attachment_image_src($id, 'thumbnail')[0]
            ];
            $searches = array_keys($substitutions);
            $replaces = array_values($substitutions);
            $JS_LINES[] = str_replace($searches, $replaces, $JS_LINE);
        }
        $scenarios = $this->data->scenarios;
        for($n=0; $n<count($scenarios); $n++)
        {
            $id = $scenarios[$n];
            $substitutions = [
                '{list}' => 'sos-gal-list-scenario'
                ,'{id}' => $id
                ,'{url}' => wp_get_attachment_image_src($id, 'thumbnail')[0]
            ];
            $searches = array_keys($substitutions);
            $replaces = array_values($substitutions);
            $JS_LINES[] = str_replace($searches, $replaces, $JS_LINE);
        }
        $JS_PLACEHOLDER = implode(' ', $JS_LINES);
        $jsInitialize = "function jsSosGalInitialize(){ $JS_PLACEHOLDER }";

        $this->registerInlineScript($jsInitialize, $this->pgGallery);
    }

    private function saveJsonFile()
    {
        $url = 'https://tutorial.myfastapp.com/?photoeditor=' . $this->version;

        $images = array();
        if ( count($this->data->overlays) > 0 )
        {
            for($n=0; $n<count($this->data->overlays); $n++)
            {
                $images[] = new SRC\Image('overlay', $this->data->overlays[$n]);
            }
        }
        else
        {
            $images[] = new SRC\Image('overlay');
        }
        if ( count($this->data->scenarios) > 0 )
        {
            for($n=0; $n<count($this->data->scenarios); $n++)
            {
                $images[] = new SRC\Image('scenario', $this->data->scenarios[$n]);
            }
        }
        else
        {
            $images[] = new SRC\Image('scenario');
        }

        $configuration = new SRC\Config($url, $images);

        $content = $configuration->json();

        return file_put_contents($this->jsonFile, $content, LOCK_EX) !== false;
    }

    public function checkFileJson()
    {
        if ( !file_exists( $this->jsonFile ) )
        {
            $this->saveJsonFile();
        }
    }

    public function apiPostSave(\WP_REST_Request $request)
    {
        $ret = (object) [
            'status' => 0
            ,'error' => true
            ,'message' => 'Unhandled problem.'
            ,'title' => null
            ,'icon' => 'error'
        ];

        $http_status = 400;

        $data = json_decode( $request->get_body() );
        if ( !empty($data) )
        {
            if ( isset($data->scenario) && isset($data->overlay) )
            {
                $this->data->scenarios = $data->scenario;
                $this->data->overlays = $data->overlay;

                if ( $this->data->save() )
                {
                    if ( $this->saveJsonFile() !== false )
                    {
                        $http_status = 200;
                        $ret->status = 1;
                        $ret->error = false;
                        $ret->icon = 'info';
                        $ret->message = self::t_('api.gallery.save.ok');;
                    }
                    else
                    {
                        $http_status = 500;
                        $ret->message = self::t_('api.file.save.problem');
                    }
                }
                else
                {
                    $http_status = 500;
                    $ret->message = self::t_('api.data.save.problem');
                }
            }
            else
            {
                $ret->message = self::t_('api.request.data.invalid');
            }
        }
        else
        {
            $ret->message = self::t_('api.request.body.empty');
        }

        if ($ret->status != 1)
        {
            $ret->status = $http_status;
        }
        return $this->apiSendResponse($http_status, $ret);
    }

    private function apiSendResponse($http_status, $response)
    {
        $response->title = esc_js( $response->title );
        $response->message = esc_js( $response->message );

        return new \WP_REST_Response( $response, $http_status);
    }

}

/**
 * DO NOT CHANGE BELOW UNLESS YOU KNOW WHAT YOU DO *
**/
$plugin = SosPlugin::instance(); //the class must be the one defined in this file
$plugin->run();
