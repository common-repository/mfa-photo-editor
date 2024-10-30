<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SRC;
//use \SOSIDEE_MFA_PHOTOEDITOR\SOS\WP as SOS_WP_ROOT;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

/**
 * @property array overlays
 * @property array scenarios
 */
class Data
{
    private $ids;
    private $key;
    private $keys;

    //private $jsonFile;

    public function __construct($plugin_key) //$file
    {
        $this->key = $plugin_key . '_data_';
        $this->keys = ['scenarios', 'overlays'];

        foreach ($this->keys as $key)
        {
            $this->ids[$key] = [];
        }
    }

    public function __get($name)
    {
        $ret = false;
        if ( in_array($name, $this->keys) )
        {
            $ret = $this->ids[$name];
        }
        return $ret;
    }

    public function __set($name, $value)
    {
        $ret = false;
        if ( in_array($name, $this->keys) )
        {
            $this->ids[$name] = $value;
            $ret = true;
        }
        return $ret;
    }

    public function load()
    {
        foreach ($this->keys as $key)
        {
            $option = $this->key . $key;
            $this->ids[$key] = get_option($option, []);
        }
    }

    public function save()
    {
        foreach ($this->keys as $key)
        {
            $option = $this->key . $key;
            $value = $this->ids[$key];
            update_option($option, $value);
        }
        return true;
    }

}