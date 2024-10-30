<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\Elementor;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

class Handler
{
    public $class;
    public $native;

    public function __construct( $class )
    {
        $this->class = $class;
        $this->native = null;
    }
}