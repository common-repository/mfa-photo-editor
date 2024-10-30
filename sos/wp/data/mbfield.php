<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\DATA;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

class MbField
{
    public $key;
    public $value;
    public $tag;
    public $isCheckbox;
    
    public function __construct($key, $value, $tag, $chk = false)
    {
        $this->key = $key;
        $this->value = $value;
        $this->tag = $tag;
        $this->isCheckbox = $chk;
    }
    
}