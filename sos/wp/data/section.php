<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\DATA;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

/**
 * Data cluster
 * Each field's value is saved in a different record
 */
class Section extends Cluster
{
    
    public function addField($key, $title, $value = null, $type = FieldType::TEXT)
    {
        $key = $this->key . '-' . strtolower(trim($key));
        return parent::addField($key, $title, $value, $type);
    }
    
    public function load()
    {
        for ($n=0; $n<count($this->fields); $n++)
        {
            $field = $this->fields[$n];
            $field->load();
        }
    }
    
    public function register()
    {
        parent::register();

        for ($n=0; $n<count($this->fields); $n++)
        {
            $field = $this->fields[$n];
            $field->validate = $this->validate;
            $field->register();
        }
    }
    
}