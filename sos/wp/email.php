<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

class Email
{

    public static function send( $to, $subject, $message ) {

        $local = false;
        if ( !is_array($to) ) {
            $tos = $to;
        } else {
            $tos = implode( ',', $to );
        }
        $local = strpos ( $tos, '@localhost') !== false || strpos ( $to, '@127.0.0.1') !== false;

        if ( !$local ) {
            return wp_mail( $to, $subject, $message );
        } else {
            return mail( $to, $subject, $message );
        }
    }

}