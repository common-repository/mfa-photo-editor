<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

/**
 * Metabox class
 * Useful for posts and pages
 *
 * @property $key
 * @property $title
 * @property $screen : link, comment, screen_ID, custom post type
 * @property $context : normal|side|advanced|after_title
 * @property $html : callback
 * @property $check : callback
 * @property $priority : high|core|default|low
 * @property $compatible : with block editor
 */
class MetaBox
{
    use Property, Message, Translation;

    public $fields;

    public function __construct($key, $title, $screen, $context, $priority, $compatible)
    {
        $this->_addProperty( 'key', $key );
        $this->_addProperty( 'title', $title );
        $this->_addProperty( 'screen' );
        $this->_addProperty( 'context', $context );
        $this->_addProperty( 'html' );
        $this->_addProperty( 'check' );
        $this->_addProperty( 'priority', $priority );
        $this->_addProperty( 'compatible', $compatible );

        $this->fields = array();

        if (!is_array($screen))
        {
            $screen = array($screen);
        }
        $this->screen = $screen;

    }
    
    private function getNonceId($post)
    {
        if ( !is_numeric($post) )
        {
            return "$this->key-$post->ID";
        }
        else
        {
            return "$this->key-$post";
        }
        
    }

    public function setContext($value)
    {
        $this->context = $value;
        return $this;
    }

    public function register($plugin)
    {
        global $pagenow;
        
        if (!is_null($this->html))
        {
            $html = array($this, 'callbackDisplay');
        }
        else
        {
            $html = function(){ echo "<p>function html() has not been defined</p>"; };
        }

        $context = $this->context;
        if ($context == 'after_title' && $plugin->gutenbergEnabled)
        {
            $context = 'advanced';
        }

        add_meta_box(
             $this->key
            ,$this->title
            ,$html
            ,$this->screen
            ,$context
            ,$this->priority
            ,array(
                '__block_editor_compatible_meta_box' => $this->compatible,
            )
        );

        if (!is_null($this->check))
        {
            add_action(
                 'save_post'
                ,array($this, 'callbackSave')
                ,10
                ,3
            );
            
            if ( $pagenow == 'post.php' )
            {
                add_action( 'admin_notices', array($this, 'handleAdminNotices') );
            }
        }
        
    }

    /**
     * Adds a nonce field and calls the MetaBox.html($this, $post) function
     */
    public function callbackDisplay( $post )
    {
        $nonce_name = $this->getNonceId( $post );
        wp_nonce_field( $nonce_name, $nonce_name );
        $this->loadFromDb( $post );
        return call_user_func( $this->html, $this, $post );
    }

    /**
     * Performs the routine check procedures 
     * and calls the function MetaBox.check($this, $post, $update)
     */
    public function callbackSave( $post_ID, $post, $update )
    {
        $msg = false;

        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
        {
            return $post_ID; //If in autosave the form has not been submitted
        }
        //if ( wp_is_post_autosave( $post_ID ) ) {
        //    return; //alternativa trovata in https://developer.wordpress.org/reference/functions/add_meta_box/
        //}
        if( defined( 'DOING_AJAX' ) && DOING_AJAX )
        {
            return $post_ID;
        }
        if ( is_multisite() && ms_is_switched() )
        {
            return $post_ID;
        }
        
        if ( isset( $_REQUEST['bulk_edit'] ) )
        {
            return $post_ID;
        }

        if ( wp_is_post_revision( $post_ID ) )
        {
            return $post_ID;
        }

        $nonce_name = $this->getNonceId($post_ID);
        if ( ! isset( $_POST[$nonce_name] ) || ! wp_verify_nonce( $_POST[$nonce_name], $nonce_name ) )
        {
            $msg = self::t_( __METHOD__ . '::nonce-invalid' );
        }
        else
        {
            $post_type = get_post_type_object( $post->post_type );
            if ( !current_user_can( $post_type->cap->edit_post, $post_ID ) )
            {
                $msg = self::t_( __METHOD__ . '::user-unauthorized' );
            }
        }

        if ($msg === false)
        {
            $this->loadFromDb($post);
            $this->loadFromRequest();
            return call_user_func( $this->check, $this, $post, $update );
        }
        else
        {
            $this->err($msg);
        }
    }

    public function addField( $key, $value = null, $is_checkbox = false )
    {
        $tag = "$this->key-" . self::checkId($key);
        $field = new Data\MbField( $key, $value, $tag, $is_checkbox );
        $this->fields[] = $field;
        return $field;
    }

    public function getField( $key )
    {
        $ret = false;
        for ($n=0; $n<count($this->fields); $n++)
        {
            if ($this->fields[$n]->key == $key)
            {
                $ret = $this->fields[$n];
                break;
            }
        }
        return $ret;
    }
    
    public function getFromDb( $post )
    {
        $ret = false;
        $results = get_post_meta( $post->ID, $this->key, true );
        if ($results)
        {
            $ret = maybe_unserialize( $results );
        }
        return $ret;
    }
    
    private function loadFromDb( $post )
    {
        $results = $this->getFromDb( $post );
        if ( is_array($results) )
        {
            foreach ($results as $key => $value)
            {
                for ($n=0; $n<count($this->fields); $n++)
                {
                    $field = $this->fields[$n];
                    if ($field->key == $key)
                    {
                        $field->value = $value;
                    }
                }
            }
        }
    }
    
    private function loadFromRequest()
    {
        for ($n=0; $n<count($this->fields); $n++)
        {
            $field = $this->fields[$n];
            if (!$field->isCheckbox)
            {
                if (isset($_POST[$field->tag]))
                {
                    $field->value = sanitize_text_field( $_POST[$field->tag] );
                }
            }
            else
            {
                $field->value = isset($_POST[$field->tag]);
            }
        }
    }
    
    /**
     * Save data in the 'postmeta' table
     * 
     * @param WP_Post $post : the post related to the metabox
     * @return mixed:
     *                  (bool) true: success
     *                  (bool) false: failure
     *                  (int)  0: no update (identical values)
     */
    public function save( $post )
    {
        $ret = 0;
        $prev_values = $this->getFromDb( $post );
        $values = array();
        for ($n=0; $n<count($this->fields); $n++)
        {
            $field = $this->fields[$n];
            $key = $field->key;
            $values[$key] = $field->value;
            if (!is_array($prev_values) || $prev_values[$key] != $field->value)
            {
                $ret = false;
            }
        }
        if ( $ret === false )
        {
            $ret = update_post_meta( $post->ID, $this->key, $values ); //
        }
        return $ret;
    }

    
    /**
     * Display the admin console messages simulating the standard wp layout
     */
    public function handleAdminNotices()
    {
        if ( !( $messages = get_transient( 'settings_errors' ) ) )
        {
            return;
        }

        foreach ( $messages as $msg )
        {
            $line = '<div id="setting-error-' . $msg['code'] . '" class="notice notice-' . $msg['type'] . ' settings-error is-dismissible">';
            $line .= ($msg['type'] == 'error' || $msg['type'] == 'warning') ? "<p><strong>{$msg['message']}</strong></p>" : "<p>{$msg['message']}</p>";
            $line .= '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>';
            $line .= '</div>';
            echo $line;
        }

        // these should avoid duplicating messages...
        delete_transient( 'settings_errors' );
        remove_action( 'admin_notices', array($this, 'handleAdminNotices') );
    }


    /**
     * Adds an admin console message
     * Use this in the metabox data checking function of the plugin
     * in place of self::msgXXX('message'), otherwise the message
     * will not be displayed
     *
     * @param $message
     */
    public function info( $message )
    {
        self::msgInfo( $message );
        $this->setTransient();
    }
    public function err( $message )
    {
        self::msgErr( $message );
        $this->setTransient();
    }
    public function ok( $message )
    {
        self::msgOk( $message );
        $this->setTransient();
    }
    public function warn( $message )
    {
        self::msgWarn( $message );
        $this->setTransient();
    }

    private function setTransient()
    {
        set_transient('settings_errors', get_settings_errors(), 30);
    }


    /**
     * Template of the displaying function
     * 
     * @param Metabox $metabox : a metabox
     * @param WP_Post $post : the post
     * 
    public function html($metabox, $post)
    {
        //get the field by key to write the control
        $field = $metabox->getField('field key');
        
    }
     */

    /**
     * Template of the data checking function
     * 
    public function check($metabox, $post, $update)
    {
        //get the field to check its value
        $field = $metabox->getField('field key');
        if ( $field->value == 'foo')
        {
            //etc.
        }

        //to save the data
        $metabox->save($post);
        
        //to add a message to the admin console
        $metabox->warn('message');
        
    }
     */

}