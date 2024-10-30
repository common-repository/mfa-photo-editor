<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );
/**
 * USAGE *
 * 
 * class MyPluginClass
 * {
 *      use \SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\Message;
 *   
 *      public function __construct()
 *      {
 *          //optional: insert this command in plugin class after having set the plugin key or name
 *          self::initializeMessage($this);
 *      }
 * }
**/
trait Message
{
    protected static $setting = 'sosidee';
    public static function initializeMessage($parent)
    {
        self::$setting = $parent->key;
    }
    
    public static function _add($message, $type)
    {
        add_action( 'admin_notices', function() use($message, $type) {
            add_settings_error( self::$setting, '666', $message, $type );
        } );
    }
    
    public static function msgInfo($message)
    {
        self::_add($message, 'info');
    }
    public static function msgErr($message)
    {
        self::_add($message, 'error');
    }
    public static function msgOk($message)
    {
        self::_add($message, 'success');
    }
    public static function msgWarn($message)
    {
        self::_add($message, 'warning');
    }

    public static function msgHtml()
    {
        \settings_errors();
    }
    
}