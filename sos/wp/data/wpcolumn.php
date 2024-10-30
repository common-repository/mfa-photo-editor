<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\DATA;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

class WpColumn
{
    protected $parent; //name

    public $name;
    public $type;
    public $defaultValue;
    public $autoIncrement;
    public $nullable;
    public $length;


    public function __construct($parent, $name, $type, $length = '')
    {
        $this->parent = $parent;

        $this->name = $name;
        $this->type = $type;
        $this->length = $length != '' ? $length : $this->getLengthByType($type);

        $this->defaultValue = null;
        $this->autoIncrement = false;
        $this->nullable = false;

    }

    private function getLengthByType($type)
    {
        $ret = '';
        switch ($type)
        {
            case WpColumnType::VARCHAR:
                $ret = '255';
                break;
            case WpColumnType::INTEGER:
                $ret = '11';
                break;
            case WpColumnType::TINY_INTEGER:
                $ret = '4';
                break;
            case WpColumnType::SMALL_INTEGER:
                $ret = '6';
                break;
            case WpColumnType::CURRENCY:
                $ret = '10,2';
                break;
            case WpColumnType::BOOLEAN:
                $ret = '1';
                break;
        }
        return $ret;
    }

    public static function getDatetimeAsString($value = null, $unquoted = false)
    {
        $ret = "";
        $quote = !$unquoted ? "'" : "";
        if ( is_null($value) )
        {
            $value = new \DateTime();
        }
        if ($value instanceof \DateTime)
        {
            $ret = $quote . $value->format('Y-m-d H:i:s') . $quote;
        }
        elseif ($value === 0 || $value == '0')
        {
            $ret = "{$quote}0000-00-00 00:00:00{$quote}";
        }
        elseif ($value == 'CURRENT_TIMESTAMP')
        {
            $ret = $value;
        }
        else
        {
            $ret = "$quote{$value}$quote";
        }
        return $ret;
    }

    public static function getDatetimeFromString($value, $format = 'Y-m-d H:i:s')
    {
        $ret = false;
        try
        {
            $ret = \DateTime::createFromFormat($format, $value);
            if ( !($ret instanceof \DateTime) )
            {
                $ret = false;
            }
        }
        catch (\Exception $e) {
            sosidee_log($e);
        }
        return $ret;
    }

    public function setDefaultValue( $value )
    {
        $this->defaultValue = $value;
    }

    public function setDefaultValueAsCurrentDateTime()
    {
        $this->setDefaultValue( 'CURRENT_TIMESTAMP' );
    }

    protected function getValueAsSqlString($value)
    {
        $ret = '';
        switch ($this->type)
        {
            case WpColumnType::BOOLEAN:
                $ret = boolval($value) ? '1' : '0';
                break;
            case WpColumnType::INTEGER:
            case WpColumnType::TINY_INTEGER:
            case WpColumnType::SMALL_INTEGER:
            case WpColumnType::DOUBLE:
            case WpColumnType::DECIMAL:
            case WpColumnType::CURRENCY:
                $ret = strval($value);
                break;
            case WpColumnType::TEXT:
            case WpColumnType::VARCHAR:
                $ret = "'" . $value . "'";
                break;
            case WpColumnType::DATETIME:
            case WpColumnType::TIMESTAMP:
                $ret = self::getDatetimeAsString($value);
                break;
        }
        return $ret;
    }

    public function getNativeValueFromString($value)
    {
        $ret = $value;
        switch ($this->type)
        {
            case WpColumnType::BOOLEAN:
                $ret = boolval($value);
                break;
            case WpColumnType::INTEGER:
            case WpColumnType::TINY_INTEGER:
            case WpColumnType::SMALL_INTEGER:
                $ret = intval($value);
                break;
            case WpColumnType::FLOAT:
                $ret = floatval($value);
                break;
            case WpColumnType::DECIMAL:
            case WpColumnType::CURRENCY:
            case WpColumnType::DOUBLE:
                $ret = doubleval($value);
                break;
            case WpColumnType::DATETIME:
            case WpColumnType::TIMESTAMP:
                $ret = self::getDatetimeFromString($value);
                break;
        }
        return $ret;
    }

    public function getCommandSql()
    {
        $ret = "{$this->name} {$this->type}";
        if ($this->length != '')
        {
            $ret .= "({$this->length})";
        }
        $ret .= $this->nullable ? " NULL" : " NOT NULL";
        if ( !is_null($this->defaultValue) )
        {
            $ret .= " DEFAULT " . $this->getValueAsSqlString($this->defaultValue);
        }
        if ($this->autoIncrement)
        {
            $ret .= " AUTO_INCREMENT";
        }

        return $ret;
    }

    public function getQueryFormat()
    {
        $ret = '?';
        switch ($this->type)
        {
            case WpColumnType::BOOLEAN:
            case WpColumnType::INTEGER:
            case WpColumnType::TINY_INTEGER:
            case WpColumnType::SMALL_INTEGER:
                $ret = '%d';
                break;
            case WpColumnType::CURRENCY:
            case WpColumnType::DECIMAL:
            case WpColumnType::FLOAT:
            case WpColumnType::DOUBLE:
                $ret = '%f';
                break;
            default:
                $ret = '%s';
        }
        return $ret;
    }

}