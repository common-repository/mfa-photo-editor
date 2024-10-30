<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\DATA;
use \SOSIDEE_MFA_PHOTOEDITOR\SOS\WP as SOS_WP_ROOT;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

class Form
{
    //use SOS_WP_ROOT\Property;
    use SOS_WP_ROOT\Message;

    private $_nonce;
    private $_actions;

    protected $_plugin;

    public $_name;
    public $_callback;
    public $_posted;
    public $_action;

    public $_fields;

    private $_pages;

    public function __construct($name, $callback = null) {
        $this->_nonce = "sos_form-$name";
        $this->_actions = array();
        $this->_name = $name;
        $this->_callback = $callback;
        $this->_posted = false;
        $this->_action = "";

        $this->_fields = array();
        $this->_pages = array();

        if ( is_admin() ) {
            // moved from 'init' to 'current_screen' in order to allow checking for screen object in initialize()
            add_action( 'current_screen', array($this,'sanitize') );
        } else {
            add_action( 'the_post', array($this,'sanitize') );
        }

        $this->_plugin = \SOSIDEE_MFA_PHOTOEDITOR\SosPlugin::instance();

    }

    private function addField($type, $name, $value) {
        $ret = new FormField( $type, $name, $value );
        $this->_fields[] = $ret;
        //$this->{$name} = $ret; not necessary
        return $ret;
    }

    public function addTextBox($name, $value = '') {
        return $this->addField(FormFieldType::TEXT, $name, $value);
    }
    public function addNumericBox($name, $value = 0) {
        return $this->addField(FormFieldType::NUMBER, $name, $value);
    }
    public function addDatePicker($name, $value = null) {
        if ($value == 'now') {
            $dt = new \DateTime();
            $value = $dt->format('Y-m-d');
        }
        return $this->addField(FormFieldType::DATE, $name, $value);
    }
    public function addHidden($name, $value = '') {
        return $this->addField(FormFieldType::HIDDEN, $name, $value);
    }
    public function addTextArea($name, $value = '') {
        return $this->addField(FormFieldType::TEXTAREA, $name, $value);
    }
    public function addCheckBox($name, $value = false) {
        return $this->addField(FormFieldType::CHECK, $name, $value);
    }
    public function addSelect($name, $value = 0) {
        return $this->addField(FormFieldType::SELECT, $name, $value);
    }

    private function getActionName( $action ) {
        $ret = "{$this->_name}_action";
        if ($action != '') {
            if ( !in_array($action, $this->_actions) ) {
                $this->_actions[] = $action;
            }
            $ret .= "_{$action}";
        }
        return  $ret;
    }
    public function htmlButton( $action = '', $value = 'ok', $style = '', $onclick = null ) {
        $name = $this->getActionName( $action );

        FormTag::html( 'input', [
                'type' => 'submit'
                ,'id' => $name
                ,'name' => $name
                ,'value' => $value
                ,'class' => "button button-primary"
                ,'style' => "width:90px;$style"
                ,'onclick' => $onclick
                ,'title' => $value
            ]
        );
    }
    public function htmlSave( $value = 'salva' )
    {
        $style = 'color: #ffffff; background-color: #28a745; border-color: #28a745;';
        $this->htmlButton('save', $value, $style);
    }
    public function htmlDelete( $value = 'elimina', $message = "Eliminare?" ) {
        $style = 'color: #ffffff; background-color: #dc3545; border-color: #dc3545;';
        $message = addslashes( htmlentities($message) );
        $onclick = "return self.confirm('$message');";
        $this->htmlButton('delete', $value, $style, $onclick);
    }

    public function htmlLinkButton( $url, $value = 'ok', $style = 'width:90px;' ) {
        $style = !empty($style) ? $style : null;
        FormTag::html( 'input', [
                'type' => 'button'
                ,'value' => $value
                ,'class' => "button button-primary"
                ,'style' => $style
                ,'onclick' => "self.location.href='$url'"
                ,'title' => $value
            ]
        );
    }

    public function htmlLinkButton2( $url, $value = 'ok', $style = 'width:90px;' ) {
        $style = !empty($style) ? $style : null;
        FormTag::html( 'input', [
                'type' => 'submit'
                ,'value' => $value
                ,'class' => "button button-secondary"
                ,'style' => $style
                ,'onclick' => "self.location.href='$url'"
                ,'title' => $value
            ]
        );
    }

    protected function initialize() { }

    private function chekPost() {
        $this->_posted = false;
        if ( isset( $_POST[$this->_nonce] ) ) {
            if ( wp_verify_nonce( $_POST[$this->_nonce], $this->_name ) ) {
                $this->_posted = true;
            }
        }
    }

    public function sanitize() {
        $this->chekPost();

        $continue = !is_admin();
        if ( !$continue ) {
            for ($n=0; $n<count($this->_pages); $n++)
            {
                if ( $this->_pages[$n]->isCurrent() )
                {
                    $continue = true;
                    break;
                }
            }
        }
        if ( !$continue ) {
            return false;
        }

        $this->initialize();

        if ( $this->_posted == true ) {
            $actList = "{$this->_name}_actions";
            if ( isset( $_POST[$actList]) ) {
                $actions = explode(',', $_POST[$actList]);
                for ($n=0; $n<count($actions); $n++) {
                    $action = $actions[$n];
                    $name = "{$this->_name}_action_{$action}";
                    if ( isset( $_POST[$name]) ) {
                        $this->_action = $action;
                        break;
                    }
                }
            }
            for ($n=0; $n<count($this->_fields); $n++) {
                $field = $this->_fields[$n];
                if ( isset($_POST[$field->name]) || $field->type == FormFieldType::CHECK ) {
                    switch ( $field->type ) {
                        case FormFieldType::TEXTAREA:
                            $field->value = sanitize_textarea_field( $_POST[$field->name] );
                            break;
                        case FormFieldType::CHECK:
                            $field->value = isset( $_POST[$field->name] );
                            break;
                        default:
                            $field->value = sanitize_text_field( $_POST[$field->name] );
                    }
                }
            }
            if ( !is_null($this->_callback) ) {
                return call_user_func( $this->_callback, $this->_fields );
            } else {
                return true;
            }
        }
        return false;
    }

    public function openHtml() {
        /*
        if ( is_admin() ) {
            $url = wp_parse_url( admin_url( "admin.php" ), PHP_URL_PATH ) . "?page=" . $_GET["page"];
        } else {
            global $wp;
            $url = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
        }
        */
        FormTag::html( 'form', [
                //'action' => $url
                'method' => 'post'
            ]
        );
    }

    public function closeHtml() {
        wp_nonce_field( $this->_name, $this->_nonce );
        if ( count($this->_actions) > 0 ) {
            $name = "{$this->_name}_actions";
            $value = implode(',', $this->_actions);
            FormTag::html( 'input', [
                    'type' => 'hidden'
                    ,'id' => $name
                    ,'name' => $name
                    ,'value' => $value
                ]
            );
        }
        echo "</form>";
;    }

    public function addToPage()
    {
        $pages = func_get_args();
        if ( func_num_args() == 1 && is_array($pages[0]) ) {
            $pages = $pages[0];
        }
        for ($n = 0; $n < count($pages); $n++) {
            $this->_pages[] = $pages[$n];
        }
        return $this;
    }


}