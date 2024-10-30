<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\DATA;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

class FormFieldType
{
    const TEXT = 1;
    const TEXTAREA = 2;
    const CHECK = 3;
    const SELECT = 4;
    const NUMBER = 6;
    const DATE = 7;
    const HIDDEN = 9;
}