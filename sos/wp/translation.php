<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

/**
 * Adds functions for the internationalization of the plugin
 * It works with the file 'translations.php', located in the plugin folder
 */
trait Translation
{

    private static $translation_file = false;

	/**
	 * Checks for the key defined in the file $translation_file
	 * Handles placeholders with recursive translations
	 *
	 * @param string|array[2] of strings $args
	 *      if string: key for the translation
	 *      if string[2]:
	 *          string[0]: key
	 *          string[1]: associative array [placeholder => ph_key] (ph_key: the key to translate or substitute the placeholder)
	 * @return string
	 */
    public static function t_($args)
    {
        if ( !is_array($args) )
        {
            $key = $args;
        }
        else
        {
            $key = isset($args[0]) ? $args[0] : '';
        }
        $ret = $key;

        if ( self::$translation_file !== false )
        {

            if ( self::$translation_file != '' && file_exists(self::$translation_file) && $key != '' )
            {
                $ret = require( self::$translation_file );
                if ( is_array($args) && isset($args[1]) )
                {
                    foreach( $args[1] as $k => $v )
                    {
                        $t = self::t_($v);
                        $ret = str_replace($k, $t, $ret);
                    }
                }
            }

        }
        else
        {
            if (self::class !== 'SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\Plugin')
            {
                $plugin = \SOSIDEE_MFA_PHOTOEDITOR\SosPlugin::instance();
                $ret = $plugin::t_($args);
            }
        }

        return $ret;
    }
    
    public static function te($args)
    {
        echo self::t_($args);
    }

	/**
	 * N.B. this function must be called only by a SOS\WP\Plugin object
	 *
	 * @param string $text_domain : Text Domain of the plugin
	 */
    protected function internationalize( $text_domain )
    {
        if ( $this instanceof Plugin )
        {
        	$file = sosidee_dirname( plugin_dir_path( __FILE__ ), 2) . '/translations.php';
            if ( file_exists($file) )
            {
	            self::$translation_file = $file;
            }

            add_action( 'plugins_loaded', function() use ($text_domain) {
                load_plugin_textdomain( $text_domain, false, basename( sosidee_dirname( plugin_dir_path( __FILE__ ), 2) ) . '/languages/' );
            });
        }
        else
        {
	        trigger_error('The method SOS\WP\Translation::internationalize() is supposed to be called only by a SOS\WP\Plugin object.', E_USER_WARNING);
        }
    }
}