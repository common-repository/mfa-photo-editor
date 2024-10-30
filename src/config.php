<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SRC;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

class Config
{
    public $helpUrl;
    public $images;

    public function __construct($helpUrl, $images)
    {
        $this->helpUrl = $helpUrl;
        $this->images = $images;
    }

    public function json()
    {
        return json_encode($this, JSON_UNESCAPED_SLASHES);
    }

}