<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\Elementor;
use \Elementor as NativeElementor;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

class Section
{
    public $key;
    public  $title;
    public  $tab;

    public function __construct($key)
    {
        $this->key = $key;
        $this->title = $key;
        $this->tab = NativeElementor\Controls_Manager::TAB_CONTENT;
    }

    public  function getArgs()
    {
        $ret = array();
        if ($this->title != '')
        {
            $ret['label'] = $this->title;
        }
        if ($this->tab != '')
        {
            $ret['tab'] = $this->tab;
        }
        return $ret;
    }

}