<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\DATA;
use \SOSIDEE_MFA_PHOTOEDITOR\SOS\WP as SOS_WP_ROOT;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

/**
 * @property $key
 * @property $title
 * @property $description
 * @property $label
 * @property $min
 * @property $max
 * @property $step
 * @property $encrypted
 */
class Field
{
    use SOS_WP_ROOT\Property
	{
        SOS_WP_ROOT\Property::__get as __getProp;
        SOS_WP_ROOT\Property::__set as __setProp;
	}
    use SOS_WP_ROOT\Translation;
    use Db, Encryption;

    public $value;
    protected $type;
    protected $parent;

    public $options;
    public $validate;
    
    public $handled; //to avoid a double call when the record does not exist and it's inserted

    public function __construct($key, $title, $value, $type)
    {
        $this->_addProperty('key', '');
        $this->_addProperty('title', $title);
        $this->_addProperty('description', '');
        $this->_addProperty('label', '');
        $this->_addProperty('min', false);
        $this->_addProperty('max', false);
        $this->_addProperty('step', false);
	    $this->_addProperty('encrypted', false);

        $this->parent = null;
        
        $this->key = $key;
        $this->value = $value;
        $this->type = $type;

        $this->options = array();
        $this->validate = null;
        $this->handled = false;
    }

    public function __set($name, $value)
    {
        $ret = null;
        switch($name)
        {
            case 'key':
                $value = self::checkId($value);
                $ret = $this->__setProp($name, $value);
                break;
            default:
                $ret = $this->__setProp($name, $value);
        }
        return $ret;
    }
    
    public static function create($parent, $key, $title, $value, $type)
    {
        $ret = new self($key, $title, $value, $type);
        $ret->parent = $parent;
        return $ret;
    }
    
    public function html()
    {
        $tag_key = '?';
        if ( $this->parent instanceof Section )
        {
            $tag_key = $this->key;
        }
        else if ( $this->parent instanceof Group )
        {
            $tag_key = "{$this->parent->key}[{$this->key}]";
        }

        $current = $this->value;
        $html = '';
        if ($this->type == FieldType::CHECK)
        {
            $html_tag = "<input type=\"checkbox\" id=\"{$tag_key}\" name=\"{$tag_key}\" value=\"1\"" . checked(1, $current, false) . "/>";
            if ($this->label != '')
            {
                $html_tag .= "<label for=\"{$tag_key}\">{$html_tag}{$this->label}</label>";
            }
            $html .= $html_tag;
        }
        else if ($this->type == FieldType::SELECT)
        {
            $html .= '<select id="' . $tag_key . '" name="' . $tag_key . '">';
            foreach ($this->options as $value => $text)
            {
                $html .= "<option value=\"{$value}\"" . selected($current, $value, false) . ">{$text}</option>";
            }
            $html .= '</select>';
        }
        else if ($this->type == FieldType::OPTION)
        {
            $html_tag = '';
            foreach ($this->options as $value => $text)
            {
                if ($html_tag != '')
                {
                    $html_tag .= '<br />';
                }
                $html_tag .= '<label>';
                $html_tag .= '<input type="radio" name="' . $tag_key . '" value="' . esc_attr( $value ) . '"' . checked($current, $value, false) . '/>';
                $html_tag .= $text . '</label>';
            }
            $html .= $html_tag;
        }
        else if ($this->type == FieldType::TEXTAREA)
        {
            $html .= '<textarea id="' . $tag_key . '" name="' . $tag_key . '" class="large-text" />';
            $html .= esc_textarea( $current );
            $html .= '</textarea>';
        }
        else if ($this->type == FieldType::NUMBER)
        {
            $html .= '<input type="number" id="' . $tag_key . '" name="' . $tag_key . '" value="' . esc_attr( $current ) . '" class="small-text"';
            if ($this->min !== false)
            {
                $html .= 'min="' . esc_attr($this->min) .'"';
            }
            if ($this->max !== false)
            {
                $html .= 'max="' . esc_attr($this->max) .'"';
            }
            if ($this->step !== false)
            {
                $html .= 'step="' . esc_attr($this->step) .'"';
            }
            $html .= ' />';
        }
        else if ($this->type == FieldType::TEXT)
        {
            $html .= '<input type="text" id="' . $tag_key . '" name="' . $tag_key . '" value="' . esc_attr( $current ) . '" class="regular-text" />';
        }
        else if ($this->type == FieldType::CHECKLIST)
        {
            $html_tag = '';
            $func = 'js' . str_replace( ['-', '[', ']'], ['_'], $tag_key );
            $count = 0;
            $values = explode(';', $current);
            foreach ($this->options as $value => $text)
            {
                $value_chk = in_array($value, $values);
                $tag_key_chk = $tag_key . $count;
                if ($html_tag != '')
                {
                    $html_tag .= '<br />';
                }
                $html_tag .= '<label for="' . $tag_key_chk . '">';
                $html_tag .= '<input onclick="' . $func .'(this.value,this.checked);" type="checkbox" id="' . $tag_key_chk . '" name="' . $tag_key_chk . '" value="' . esc_attr($value) . '" ' . checked(true, $value_chk, false) . '/>';
                $html_tag .= $text . '</label>';
                $count++;
            }
            $html_tag .= '<input type="hidden" id="' . $tag_key . '" name="' . $tag_key . '" value="' . esc_attr( $current ) . '"/>';
            //add javascript
            $js = <<<EOD
<script type="application/javascript">
function $func( v, m ) {
    let field = self.document.getElementById( '$tag_key' );
    let values = field.value.split( ';' );
    if ( m && !values.includes(v) ) {
        values.push(v);
    } else if ( !m && values.includes(v) ) {
        values = values.filter( function(e, i, a) { return e != v; }, v );
    }
    field.value = values.join( ';' );
}
</script>
EOD;
            $html_tag .= $js;
            $html .= $html_tag;
        }
        else
        {
            $html .= '<label id="' . $tag_key . '" name="' . $tag_key . '">' . esc_attr( $current ) . '</label>';
        }
        
        if ($this->description != '')
        {
            $html .= '<p class="description">' . $this->description . '</p>';
        }
        
        echo $html;
    }

    public function load()
    {
        if ( $this->parent instanceof Section )
        {
            $value = get_option($this->key, $this->value);
	        $this->setValue( $value );
        }
        else if ( $this->parent instanceof Group )
        {
            //
        }
        
    }

    public function setValue( $value )
    {
	    if ( !$this->encrypted )
	    {
		    $this->value = $value;
	    }
	    else
	    {
		    $this->value = $this->decrypt( $value );
	    }
    }
    
    /**
     * $input is the field value
     */
    public function callback( $input )
    {
        if (!is_null($this->validate))
        {
            if (!$this->handled)
            {
                $this->handled = true;
                $result = call_user_func( $this->validate, $this->parent->key, array($this->key => $input) );
                if ( is_array($result) )
                {
                    $ret = array_values($result)[0];
                }
                else
                {
                    $ret = $result;
                }
                if ( !$this->encrypted )
                {
                	return $ret;
                }
                else
                {
                	return $this->encrypt( $ret );
                }
            }
            else
            {
                return $input;
            }
        }
    }
    
    public function translate()
    {
        $this->title = self::t_( $this->title );
        $this->description = self::t_( $this->description );
        $this->label = self::t_( $this->label );
        foreach ($this->options as $value => $text)
        {
            $this->options[$value] = self::t_( $text );
        }
    }
    
    
    public function initialize()
    {

        add_settings_field(
             $this->key
            ,$this->title
            ,array($this, 'html')
            ,$this->parent->page
            ,$this->parent->key
        );
    }
    
    public function register()
    {

        $callback = is_null($this->validate) ? null : ["sanitize_callback" => array($this, 'callback')];
        
        register_setting(
             $this->parent->key
            ,$this->key
            ,$callback
        );

    }

}