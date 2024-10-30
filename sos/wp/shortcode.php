<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

class ShortCode
{

	public $callback;
    public $tag;

    public function __construct($tag, $callback)
    {
        $this->tag = $tag;
        $this->callback = $callback;
        
        add_shortcode( $this->tag, array( $this, 'sanitize' ) );
    }
    
    public function sanitize( $attributes, $content, $tag )
    {
        $args = array();
        if (is_array($attributes))
        {
            foreach ($attributes as $key => $value)
            {
                $args[ sanitize_key($key) ] = sanitize_text_field($value);
            }
        }
        return call_user_func( $this->callback, $args, $content, $tag );
    }
    
}