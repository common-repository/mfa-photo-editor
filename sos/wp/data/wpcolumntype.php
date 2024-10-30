<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\DATA;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );


class WpColumnType
{
    const BOOLEAN = 'bit';

    const INTEGER = 'int';
    const TINY_INTEGER = 'tinyint';
    const SMALL_INTEGER = 'smallint';

    const FLOAT = 'float';
    const DOUBLE = 'double';
    const DECIMAL = 'decimal';
    const CURRENCY = 'decimal';

    const DATETIME = 'datetime';
    const TIMESTAMP = 'timestamp';

    const VARCHAR = 'varchar';
    const TEXT = 'text';
}