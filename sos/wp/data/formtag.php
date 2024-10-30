<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\DATA;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

class FormTag
{
    public static function get()
    {
        $args = func_get_args();
        $tag = $args[0]; // mandatory
        $container = in_array( $tag, ['textarea', 'select', 'option', 'label'] );
        $html = '';
        $content = '';
        $ret = "<{$tag}";
        if ( func_num_args() > 1) {
            $attributes = $args[1];
            foreach ($attributes as $key => $value) {
                if ($key == 'checked' || $key == 'selected') {
                    if ($value === true) {
                        $ret .= " $key";
                    }
                } else {
                    if ( !is_null($value) ) {
                        if ($key == 'html') {
                            $html = $value; // sanitized in the previous call of this function
                        } else if ( $key == 'content' ) {
                            $content = esc_textarea( $value );
                        } else {
                            $ret .= " $key=\"" . esc_attr( $value ) . '"';
                        }
                    }
                }
            }
        }
        $ret .= ">";
        if ($container) {
            $ret .= "{$content}$html</$tag>";
        }
        return $ret;
    }

    public static function html()
    {
        $args = func_get_args();
        if ( func_num_args() == 1) {
            echo self::get( $args[0] );
        } else if ( func_num_args() == 2) {
            echo self::get( $args[0], $args[1] );
        } else {
            echo "wrong number of arguments in FormTag.html()";
        }
    }

}