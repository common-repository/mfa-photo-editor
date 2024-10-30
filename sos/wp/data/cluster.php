<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\DATA;
use \SOSIDEE_MFA_PHOTOEDITOR\SOS\WP as SOS_WP_ROOT;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

/**
 * Base class for the data classes 'Section' and 'Group'
 * Data are saved in the 'options' table of the database
 */
class Cluster
{
    use SOS_WP_ROOT\Property
	{
        SOS_WP_ROOT\Property::__set as __setProp;
	}
    use SOS_WP_ROOT\Translation;

    public $fields;

    public $page;
    public $validate;

    public function __construct($key, $title)
    {
        $this->_addProperty('key', '');
        $this->_addProperty('title', '');
        $this->_addProperty('description', '');

	    $this->_addProperty('encrypted', false);

        $this->key = $key;
        $this->title = $title;

        $this->page = '';
        $this->validate = null;
        $this->fields = array();
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

    /**
     * Creates a data field and adds it to the fields array
     * 
     * @param string $key : (unique) ID of the field
     * @param string $title : title (used for the label)
     * @param string $value: default value
     * @param FieldType $type : type of layout (form control)
     * 
     * $return a Field object
     */
    public function addField($key, $title, $value = null, $type = FieldType::TEXT)
    {
        $field = Field::create($this, $key, $title, $value, $type);
        $this->fields[] = $field;
        return $field;
    }

    protected function getFieldIndex($key)
    {
        $ret = false;
        for ($n=0; $n<count($this->fields); $n++)
        {
            $field = $this->fields[$n];
            if ($field->key == $key)
            {
                $ret = $n;
                break;
            }
        }
        return $ret;
    }

    public function getField($key)
    {
        $ret = null;
        for ($n=0; $n<count($this->fields); $n++)
        {
            $field = $this->fields[$n];
            if ($field->key == $key)
            {
                $ret = $field;
                break;
            }
        }
        return $ret;
    }

    /**
     * Assignes an admin page to the data cluster 
     * 
     * @param string | BE\Page $page : ID of the page or the page itself
     * 
     */
    public function setPage($page)
    {
        if (is_string($page))
        {
            $this->page = $page;
        }
        else
        {
            $this->page = $page->key;
        }
    }

    /**
     * Loads the fields value from the database
     */
    public function load()
    {
        //it's overriden by the inherited classes
    }

    protected function translate()
    {
        $this->title = self::t_( $this->title );
        $this->description = self::t_( $this->description );
        
        for ($n=0; $n<count($this->fields); $n++)
        {
            $field = $this->fields[$n];
            $field->translate();
        }
    }

    protected function initialize()
    {
        $this->translate();
        
        $callback = $this->description != '' ? function() { echo $this->description; } : null;

        add_settings_section(
                 $this->key
                ,$this->title
                ,$callback
                ,$this->page
            );
    }
    
    public function register()
    {

        $this->initialize();
        for ($n=0; $n<count($this->fields); $n++)
        {
            $field = $this->fields[$n];
            if ($field->encrypted)
            {
	            $this->encrypted = true;
            }
            $field->initialize();
        }
    }
    
    public function html($no_submit = false)
    {
        $this->load();

        settings_fields($this->key);
        do_settings_sections($this->page);
        
        if (!$no_submit)
        {
            submit_button();
        }
    }
    
}