<?php
/**
 * 
 * THIS FILE MUST BE LOCATED IN THE PLUGIN FOLDER *
 * 
**/
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

if(!class_exists('SOSIDEE_CLASS_LOADER'))
{
    /**
     * Class autoloader
     * It works for all sosidee's plugins installed
     * Class namespaces are transformed into a folders structure
     */
    class SOSIDEE_CLASS_LOADER
    {
        
        public function load( $class )
        {
            $items = explode('\\', $class);
            if ( key_exists($items[0], $this->roots) ) //check if the class namespace root has been added (non sosidee's plugins exit here)
            {
                $items[0] = $this->roots[ $items[0] ];
                $class_path = implode(DIRECTORY_SEPARATOR, $items);
                $file_path = plugin_dir_path( __DIR__ ) . strtolower($class_path) . '.php';
            	if ( file_exists($file_path) )
            	{
            	    require_once $file_path;
            	}
            }
        }

        public function add( $namespace, $folder )
        {
            $this->roots[$namespace] = $folder;
        }

        //just the usual way to get a singleton
        private static $instance = null;
        final public static function instance()
        {
            if (self::$instance == null)
            {
                self::$instance = new \SOSIDEE_CLASS_LOADER();

                spl_autoload_register( array(self::$instance, 'load') );

            }
            return self::$instance;
        }

        private $roots;
        private function __construct()
        {
            $this->roots = array();
        }
    }
}

/* CUSTOM PHP FUNCTIONS */
if ( !function_exists('sosidee_str_starts_with') )
{
    function sosidee_str_starts_with( $haystack, $needle )
    {
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
}

if ( !function_exists('sosidee_str_ends_with') )
{
    function sosidee_str_ends_with( $haystack, $needle )
    {
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }
}

if ( !function_exists('sosidee_str_remove') )
{
    function sosidee_str_remove( $search, $subject )
    {
        return str_replace($search, '', $subject);
    }
}

if ( ! function_exists( 'sosidee_is_login_page' ) )
{
    function sosidee_is_login_page()
    {
        return in_array(
            $GLOBALS['pagenow'],
            array( 'wp-login.php', 'wp-register.php' ),
            true
        );
    }
}

if ( ! function_exists( 'sosidee_get_query_var' ) )
{
    function sosidee_get_query_var($var, $mixed = null)
    {
        $ret = get_query_var($var, null);
        if ( is_null ($ret) && isset( $_GET[$var] ) )
        {
            $ret = $_GET[$var];
        }
        else
        {
            $ret = $mixed;
        }
        return $ret;
    }
}
if ( ! function_exists( 'sosidee_dirname' ) ) {
    function sosidee_dirname( $path, $levels = 1 ) {
        if ( version_compare( phpversion(), '7.0.0') >= 0 ) {
            return dirname($path, $levels);
        } else {
            if ($levels > 1){
                return dirname( sosidee_dirname( $path, --$levels ) );
            }else{
                return dirname( $path );
            }
        }
    }
}