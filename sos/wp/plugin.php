<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP;
use \Elementor as NativeElementor;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

/**
 * Base class for the plugins
 *
 * @property $key
 * @property $name
 * @property $path
 * @property $url
 * @property $version
 */
class Plugin
{
    use Property
	{
        Property::__get as __getProp;
        Property::__set as __setProp;
	}
    use Message, Asset, Translation;

    private $localizedScriptHandles;
    private $inlineScriptHandles;

    protected $menu;
    protected $pages;
    protected $clusters;
    protected $scripts;
    protected $styles;
    protected $shortcodes;
    protected $metaboxes;
    protected $elementorWidgets; //contains the class names of the custom widgets for Elementor
    protected $endpoints;

    public $gutenbergEnabled;

    public $qsArgs;

    public static $path = '';
    public static $url = '';
    
    protected function __construct()
    {
        $this->_addProperty('key', 'sos-plugin'); // used in the database and for the slugs
        $this->_addProperty('name', 'SOS Plugin');
        //$this->_addProperty('path', '');
        //$this->_addProperty('url', '');
        $this->_addProperty('version', '');

        $this->localizedScriptHandles = array();
        $this->inlineScriptHandles = array();

        $this->menu = null;
        $this->pages = array();
        $this->clusters = array();
        $this->scripts = array();
        $this->styles = array();
        $this->shortcodes = array();
        $this->metaboxes = array();
        $this->elementorWidgets = array();
        $this->endpoints = array();
        $this->gutenbergEnabled = false;

        $this->qsArgs = array();

        self::$path = sosidee_dirname( plugin_dir_path( __FILE__ ) , 2);
        self::$url = sosidee_dirname( plugin_dir_url( __FILE__ ) , 2);
        Script::$PLUGIN_URL = self::$url;
        Style::$PLUGIN_URL = self::$url;
    }

    private static $_instances = array();
    final public static function instance()
    {
        $calledClass = get_called_class();

        if (!isset( self::$_instances[$calledClass]) )
        {
            self::$_instances[$calledClass] = new $calledClass();
        }

        return self::$_instances[$calledClass];
    }

    public function __get($name)
    {
        $ret = null;
        switch($name)
        {
            default:
                $ret = $this->__getProp($name);
        }
        return $ret;
    }

    public function __set($name, $value)
    {
        $ret = null;
        switch($name)
        {
            case 'key':
                $value = str_replace( '_', '-', self::checkId($value) );
                if (!sosidee_str_starts_with($value, 'sos'))
                {
                    $value = 'sos-' . $value;
                }
                $ret = $this->__setProp($name, $value);
                break;
            default:
                $ret = $this->__setProp($name, $value);
        }
        return $ret;
    }
    
    /**
     * Creates and adds a backend page located in the 'admin' folder
     * 
     * @param string $file : just the filename, without path
     * 
     * @return  SOS\WP\BE\Page object
     */
    protected function addPage( $file, $name = '')
    {
        $path = self::$path . DIRECTORY_SEPARATOR . 'admin';
        if ( !sosidee_str_starts_with($file, DIRECTORY_SEPARATOR) )
        {
            $path .= DIRECTORY_SEPARATOR;
        }
        $path .= $file;
        $page = new BE\Page($path, $name);
        $page->key = $this->key . '-' . count($this->pages);
        $this->pages[] = $page;
        return $page;
    }

    /**
     * Creates and adds a metabox with:
     *      - screen = post and page
     *      - context = normal
     * 
     * @param $key : (unique) ID of the metabox
     * @param $title : title of the metabox
     * 
     * @return  SOS\WP\MetaBox object
     */
    protected function addMetaBox( $key, $title, $screen = ['post', 'page'], $context = 'normal', $priority = 'high', $compatible = true )
    {
        $key = $this->key . '-mb_' . self::checkId($key);
        $ret = new MetaBox($key, $title, $screen, $context, $priority, $compatible);
        $this->metaboxes[] = $ret;
        return $ret;
    }

    private function addCluster($key, $title, $type)
    {
        $key = $this->key . '_' . $key;
        $cluster = false;
        if ($type == 'section')
        {
            $cluster = new Data\Section($key, $title);
        }
        else if ($type == 'group')
        {
            $cluster = new Data\Group($key, $title);
        }
        if ($cluster !== false)
        {
            $this->clusters[] = $cluster;
        }
        return $cluster;
    }
    protected function addSection($key, $title)
    {
        return $this->addCluster($key, $title, 'section');
    }
    protected function addGroup($key, $title)
    {
        return $this->addCluster($key, $title, 'group');
    }

    protected function getCluster($key)
    {
        $ret = null;
        for ($n=0; $n<count($this->clusters); $n++)
        {
            $cluster = $this->clusters[$n];
            if ($cluster->key == $key)
            {
                $ret = $cluster;
                break;
            }
        }
        return $ret;
    }

    private function getClusterIndex($key)
    {
        $ret = false;
        for ($n=0; $n<count($this->clusters); $n++)
        {
            $cluster = $this->clusters[$n];
            if ($cluster->key == $key)
            {
                $ret = $n;
                break;
            }
        }
        return $ret;
    }

    protected function addStyle($file)
    {
        $key = $this->key . '-' . count($this->styles);
        $style = new Style($key, $file);
        $this->styles[] = $style;
        return $style;
    }

    protected function addScript($file, $jquery_dependency = true, $in_body = false)
    {
        $key = $this->key . '-' . count($this->scripts);

        $dependency = $jquery_dependency ? array('jquery') : array();

        $script = new Script($key, $file, $dependency, $in_body);
        $this->scripts[] = $script;
        return $script;
    }

    protected function addInlineScript($code, $handle = '-inline')
    {
        $handle = $this->key . $handle;
        if ( !in_array($handle, $this->inlineScriptHandles) )
        {
            wp_register_script( $handle, '', ['jquery'], '', true );
            wp_enqueue_script( $handle );
            $this->inlineScriptHandles[] = $handle;
        }
        wp_add_inline_script($handle, $code);
    }

    protected function registerInlineScript($code, $pages = [], $handle = '-reg-inline')
    {
        $action = !is_admin() ? 'wp_enqueue_scripts' : 'admin_enqueue_scripts';
        add_action( $action, function() use ($code, $pages, $handle) {
            if (!is_array($pages))
            {
                $pages = [$pages];
            }
            $add = count($pages) == 0;
            if ( !$add )
            {
                for ($n=0; $n<count($pages); $n++)
                {
                    if ( $pages[$n]->isCurrent() )
                    {
                        $add = true;
                        break;
                    }
                }
            }
            if ( $add )
            {
                $this->addInlineScript($code, $handle);
            }
            else
            {
                return false;
            }
        } );
    }

    protected function addLocalizedScript($name, $data, $handle = '')
    {
        $handle = $this->key . $handle;
        if ( !in_array($handle, $this->localizedScriptHandles) )
        {
            wp_register_script( $handle, '', [], '', true );
            wp_enqueue_script( $handle );
            $this->localizedScriptHandles[] = $handle;
        }
        wp_localize_script($handle, $name, $data);
    }

    protected function registerLocalizedScript($name, $callback, $pages = [], $handle = '')
    {
        $action = !is_admin() ? 'wp_enqueue_scripts' : 'admin_enqueue_scripts';
        add_action( $action, function() use ($name, $callback, $pages, $handle) {
            if ( !is_array($pages) )
            {
                $pages = [ $pages ];
            }
            $add = count($pages) == 0;
            if ( !$add )
            {
                for ($n=0; $n<count($pages); $n++)
                {
                    if ( $pages[$n]->isCurrent() )
                    {
                        $add = true;
                        break;
                    }
                }
            }
            if ( $add )
            {
                $data = $callback();
                $this->addLocalizedScript($name, $data, $handle);
            }
            else
            {
                return false;
            }
        } );
    }

    protected function addShortCode($key, $callback)
    {
        $shortcode = new ShortCode($key, $callback);
        $this->shortcodes[] = $shortcode;
        return $shortcode;
    }


    protected function addWidget($class)
    {
        $class_root = explode('\\', __NAMESPACE__)[0];
        $handler = new Elementor\Handler( $class_root . '\\' . $class );
        $this->elementorWidgets[] = $handler;
    }

    private function _addApiEndPoint($method, $route, $callback, $version)
    {
        $ret = new API\EndPoint($method, $route, $callback, $version );
        $this->endpoints[] = $ret;
        $this->addApiAjax();
        return $ret;
    }
    protected function addApiGet($route, $callback = null, $version = 1)
    {
        return $this->_addApiEndPoint('GET', $route, $callback, $version);
    }

    protected function addApiPost($route, $callback = null, $version = 1)
    {
        return $this->_addApiEndPoint('POST', $route, $callback, $version);
    }

    protected function addApiHead($route, $callback = null, $version = 1)
    {
        return $this->_addApiEndPoint('HEAD', $route, $callback, $version);
    }

    protected function startSession()
    {
        add_action('init',
            function () {
                if (session_status() == PHP_SESSION_NONE)
                {
                    session_start();
                }
            }
        );
    }

    protected function registerActivation( $callback )
    {
        $reflector = new \ReflectionClass( get_class($this) );
        $file = $reflector->getFileName();

        register_activation_hook( $file, [ $this, $callback ] );

    }

    protected function getVersion()
    {
        $reflector = new \ReflectionClass( get_class($this) );
        $file = $reflector->getFileName();
        $plugin_data = get_file_data($file, [
            'Version' => 'Version',
        ], 'plugin');
        $this->version = $plugin_data['Version'];
    }

    public function run()
    {
        $this->initialize();
        
        if ( !is_admin() )
        {
            $this->initializeFrontend();
        }
        else
        {
            $this->menu = new BE\Menu( $this->name );
            $this->initializeBackend();
        }
        
        $this->finalize();

        return $this;
    }

    /**
     * EVENTS
     */
    protected function initialize()
    {
        //to be overridden if needed
        //e.g.: data settings for both front- and back-ends

        if ($this->version == '')
        {
            $this->getVersion();
            if ($this->version == '')
            {
                add_action('plugins_loaded', function() {
                    if ( $this->version == '' ) {
                        $this->getVersion();
                    }
                });
            }
        }
    }

    protected function initializeBackend()
    {
        //to be overridden if needed
        //e.g.: pages, menu, metaboxes... for the backend
        add_action( 'enqueue_block_editor_assets', function() {
            $this->gutenbergEnabled = true;
        } );

    }

    protected function initializeFrontend()
    {
        //to be overridden if needed
        //e.g.: shortcode
    }

    public function registerData()
    {
        for ($n=0; $n<count($this->clusters); $n++)
        {
            $cluster = $this->clusters[$n];
            $cluster->register();
        }
    }

    public function registerMetaBox($post_type)
    {
        if ( in_array( $post_type, array( 'post', 'page' ) ) )
        {
            for ( $n=0; $n<count($this->metaboxes); $n++ )
            {
                $metabox = $this->metaboxes[$n];
                $metabox->register($this);
            }
        }
    }

    public function registerApi()
    {
        for ( $n=0; $n<count($this->endpoints); $n++ )
        {
            $ep = $this->endpoints[$n];
            $ep->register();
        }
    }

    public function initializePage()
    {
        for ( $n=0; $n<count($this->pages); $n++ )
        {
            $this->pages[$n]->translate();
            $this->pages[$n]->url = admin_url('admin.php?page=' . $this->pages[$n]->key);
        }
    }
    
    public function initializeMenu()
    {
        $this->menu->initialize();
    }

    public function checkElementor()
    {
        // Check if Elementor installed and activated
        if ( did_action( 'elementor/loaded' ) )
        {
            // Check for required Elementor version
            if (defined('ELEMENTOR_VERSION') && version_compare(ELEMENTOR_VERSION, '2.0.0', '>=') )
            {
                add_action('elementor/widgets/widgets_registered', [$this, 'initializeElementor']);
            }
        }
    }

    public function initializeElementor()
    {
        for ( $n=0; $n<count($this->elementorWidgets); $n++ )
        {
            $class = $this->elementorWidgets[$n]->class;
            $native = new $class();
            $native->initialize();
            NativeElementor\Plugin::instance()->widgets_manager->register_widget_type( $native );
            $this->elementorWidgets[$n]->native = $native;
        }
    }

    protected function finalize()
    {
        if ( count($this->qsArgs) > 0)
        {
            add_filter( 'query_vars', function($vars) {
                if ( is_array($vars) )
                {
                    foreach ($this->qsArgs as $qs)
                    {
                        $vars[] = $qs;
                    }
                    return $vars;
                }
                else
                {
                    return $this->qsArgs;
                }
            } );
        }

        if ( count($this->endpoints) > 0 )
        {
            add_action( 'rest_api_init', array($this, 'registerApi' ) );
            for ($n=0; $n<count($this->endpoints); $n++)
            {
                $ep = $this->endpoints[$n];
                $ep->enqueueScript();
            }
        }

        if ( is_admin() )
        {
            if ( count($this->clusters) > 0 )
            {
                add_action( 'admin_init', array($this, 'registerData') );
            }
            if ( count($this->metaboxes) > 0 )
            {
                add_action( 'add_meta_boxes', array($this, 'registerMetaBox') );
                for ( $n=0; $n<count($this->metaboxes); $n++ )
                {
                    if ( $this->metaboxes[$n]->context == 'after_title' )
                    {
                        add_action('edit_form_after_title', function() {
                            if ($this->gutenbergEnabled) { return false;}
                            global $post, $wp_meta_boxes;
                            do_meta_boxes( get_current_screen(), 'after_title', $post ); // Output the "after_title" meta boxes
                            unset( $wp_meta_boxes[get_post_type($post)]['after_title'] ); // Remove the initial "after_title" meta boxes
                        } );
                        break;
                    }
                }
            }
            if ( count($this->pages) > 0 )
            {
                add_action( 'admin_menu', array($this, 'initializePage') );
            }
            if ( count($this->menu->pages) > 0 )
            {
                add_action( 'admin_menu', array($this, 'initializeMenu') );
            }
            if ( count($this->scripts) > 0 )
            {
                for ($n=0; $n<count($this->scripts); $n++)
                {
                    $this->scripts[$n]->html();
                }
            }
            if ( count($this->styles) > 0 )
            {
                for ( $n=0; $n<count($this->styles); $n++ )
                {
                    $this->styles[$n]->html();
                }
            }
        }
        else
        {
            if (count($this->shortcodes) > 0)
            {
                add_action( 'the_posts', array($this, 'lookForShortcodes' ) );
            }
        }

        if (count($this->elementorWidgets) > 0)
        {
            add_action( 'plugins_loaded', array( $this, 'checkElementor' ) );
        }

    }


    /**
     * Checks if a shortcode is present in the posts and calls the function hasShortcode()
     * The function hasShortcode() is called once per tag found in the posts
     */
    public function lookForShortcodes( $posts )
    {
	    if ( empty($posts) ) { return $posts; }

	    $tags = array();
	    foreach ( $posts as $post )
	    {
		    for ( $n=0; $n<count($this->shortcodes); $n++ )
		    {
			    $tag = $this->shortcodes[$n]->tag;
			    if ( stripos($post->post_content, "[{$tag} ") !== false || stripos($post->post_content, "[{$tag}]") !== false )
			    {
				    if ( !in_array($tag, $tags) )
				    {
					    $tags[] = $tag;
					    $this->hasShortcode( $tag );
				    }
			    }
		    }
	    }
	    return $posts;
    }

    /**
    *   Selects a data group by record id, 
    *   fills its fields with the values loaded from the database
    *   and returns the cluster array index of the group
    * 
    * @param integer $id : record id of the data group
    *
    * @return integer : index of the group in the $clusters array
    **/
    protected function getGroupIndexById( $id )
    {
        global $wpdb;

        $ret = false;
        $sql = "SELECT option_name, option_value FROM $wpdb->options WHERE option_id=%d";
        $query = $wpdb->prepare( $sql, $id);
        $results = $wpdb->get_row($query, ARRAY_A);
        if ($results)
        {
            $key = sanitize_key( $results["option_name"] );
            $ret = $this->getClusterIndex($key);
            if ($ret !== false)
            {
                $cluster = $this->clusters[$ret];
                if ( $cluster instanceof Data\Group )
                {
                    $data = maybe_unserialize( $results["option_value"] );
                    if ( !$cluster->loadFields( $data ) )
                    {
                        $ret = false;
                    }
                }
                else
                {
                    $ret = false;
                }
            }
        }
        return $ret;
    }

    protected function isEncryptionPossible()
    {
    	return extension_loaded( 'openssl' )
		    && defined('SECURE_AUTH_KEY') && SECURE_AUTH_KEY != ''
	           && defined('SECURE_AUTH_SALT') && SECURE_AUTH_SALT != '';
    }

    /**
    * It's called when a shortcode is present in a post or page
    * 
    * @param string $tag : tag of the shortcode found in a post/page
    * 
    **/
    protected function hasShortcode($tag)
    {
        //to be overridden if needed
        //e.g.: to add scripts and stylesheets to the header
    }
    
    /**
     * Template for the data validation function
     * @param string $cluster_key : key of the cluster
     * @param array $inputs : values sent by the user ( associative array [field key => input value] )
     * 
     * @return array : values to be saved ( associative array [field key => output value] )
     * 
    public function validateData( $cluster_key, $inputs )
    {
        $outputs = array();
        foreach ($inputs as $field_key => $field_value)
        {
            $value = sanitize_in_some_way( $field_value );
            if ($value is OK)
            {
                $outputs[$field_key] = $value;
            }
            else
            {
                $cluster = $this->getCluster($cluster_key);
                $field = $cluster->getField($field_key);
                $outputs[$field_key] = $field->value; //old value
                self::msgErr("error message"); //message to admin console
            }
        }
        return $outputs;
    }
     */

}