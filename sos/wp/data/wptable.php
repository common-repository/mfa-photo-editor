<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\DATA;
use \SOSIDEE_MFA_PHOTOEDITOR\SOS\WP as SOS_WP_ROOT;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

class WpTable
{
    use SOS_WP_ROOT\Property;

    protected $columns;
    protected $primaryKey;
    public $name;

    public function __construct($name) {
        $this->primaryKey = false;

        $this->name = $name;
        $this->columns = array();
    }

    public function addColumn($name, $type, $length = '') {
        $ret = new WpColumn($this->name, $name, $type, $length);
        $this->columns[] = $ret;
        $this->_addProperty($name, $ret);
        return $ret;
    }

    public function addID($name = 'id') {
        $ret = $this->addColumn($name, WpColumnType::INTEGER);
        $ret->autoIncrement = true;
        $this->primaryKey = $name;
        return $ret;
    }
    public function addInteger($name)
    {
        return $this->addColumn($name, WpColumnType::INTEGER);
    }
    public function addTinyInteger($name)
    {
        return $this->addColumn($name, WpColumnType::TINY_INTEGER);
    }
    public function addSmallInteger($name)
    {
        return $this->addColumn($name, WpColumnType::SMALL_INTEGER);
    }
    public function addBoolean($name)
    {
        return $this->addColumn($name, WpColumnType::BOOLEAN);
    }
    public function addCurrency($name)
    {
        return $this->addColumn($name, WpColumnType::CURRENCY);
    }
    public function addVarChar($name, $length = '')
    {
        return $this->addColumn($name, WpColumnType::VARCHAR, $length);
    }
    public function addDateTime($name)
    {
        return $this->addColumn($name, WpColumnType::DATETIME);
    }



    protected function getColumnByName($name) {
        $ret = false;
        for ($n=0; $n<count($this->columns); $n++)
        {
            if ( strcasecmp($this->columns[$n]->name, $name) == 0 )
            {
                $ret = $this->columns[$n];
                break;
            }
        }
        return $ret;
    }

    public function getCommandSql() {
        $ret = "CREATE TABLE {$this->name} (";

        $count = count($this->columns);
        if ( $count > 0 ) {
            for ($n=0; $n<$count; $n++) {
                if ( $n > 0 ) {
                    $ret .= ",";
                }
                $ret .= PHP_EOL . $this->columns[$n]->getCommandSql();
            }
            if ($this->primaryKey !== false) {
                $ret .= "," . PHP_EOL . "PRIMARY KEY  ({$this->primaryKey})";
            }
        }
        $ret .= PHP_EOL . ")";
        return $ret;
    }


    /**
     * @param array $filters: associative array [ column_name => value ]
     *      e.g.
     *          'foo' => 123        means foo = 123
     *          'foo[>=]' => 123    means foo >= 123
     * @param array $orders mixed array [ column_name1, column_name2 => direction ] e.g. [ 'foo', 'bar' => 'DESC' ] note: foo is ASC (default)
     * @return array|false: array of objects | false
     */
    public function select( $filters = [], $orders = [] ) {
        global $wpdb;

        $ret = false;
        $error = false;
        $fields = array();
        $formats = array();
        $values = array();
        $operators = array();
        foreach ( $filters as $key => $value ) {
            $name = $key;
            $p1 = strrpos($key, '[');
            if ( $p1 === false ) {
                $fields[$name] = $value;
                $operators[$name] = '=';
            } else {
                $name = trim( substr($key, 0, $p1) );
                $fields[$name] = $value;
                $p2 = strrpos($key, ']');
                $operators[$name] = trim( substr($key, $p1+1, $p2 - $p1 - 1) );
            }
            $column = $this->getColumnByName($name);
            if ( $column !== false ) {
                if ( $column->type == WpColumnType::DATETIME || $column->type == WpColumnType::TIMESTAMP ) {
                    $values[] = $column->getDatetimeAsString($value);
                } else {
                    $values[] = $value;
                }
                $formats[$name] = $column->getQueryFormat();
            } else {
                $error = true;
                sosidee_log("WpTable.select() :: getColumnByName($name) returned false for table {$this->name}.");
                break;
            }
        }
        if( !$error ) {
            $sql = "SELECT * FROM {$this->name}";
            if ( count($fields) > 0 ) {
                $where = '';
                foreach ( $fields as $name => $value ) {
                    if ($where != '') {
                        $where .= ' AND ';
                    }
                    $where .= "{$name}{$operators[$name]}{$formats[$name]}";
                }
                $sql .= " WHERE $where";
            }
            if ( count($orders) > 0 ) {
                $order_list = '';
                foreach ($orders as $key => $value) {
                    if ($order_list != '') {
                        $order_list .= ', ';
                    }
                    if ( is_int($key) ) {
                        $order_list .= $value;
                    } else {
                        $order_list .= "$key $value";
                    }
                }
                $sql .= " ORDER BY $order_list";
            }

            if ( count($values) > 0) {
                $query = $wpdb->prepare($sql, $values);
            } else {
                $query = $sql;
            }
            $results = $wpdb->get_results($query, ARRAY_A); // ARRAY_A | ARRAY_N | OBJECT (default) | OBJECT_K
            if ( is_array($results) ) {
                if ( count($results) > 0 ) {
                    for ( $n=0; $n<count($results); $n++ ) {
                        $values = array();
                        $columns = &$results[$n]; //columns in the n^th row
                        foreach ( $columns as $name => $value ) {
                            $wpColumn = $this->getColumnByName($name);
                            $values[$name] = $wpColumn->getNativeValueFromString($value);
                        }
                        $columns = json_decode( json_encode($columns), false );
                        // values must be assigned after the column conversion to object
                        // in order to prevent datetime to be converted to a standard object (methods are lost)
                        foreach ( $columns as $name => $value ) {
                            $columns->{$name} = $values[$name];
                        }
                        unset($columns);
                    }
                }
                $ret = $results;
            } else {
                sosidee_log("WpTable.select() :: wpdb.get_results($query) returned null.");
            }
        }
        return $ret;

    }

    /**
     * @param array $data : associative array [ column_name => value ]
     *      e.g.
     *          'foo' => 123        means foo = 123
     * @return int|false : value of the new id or false in case of error
     */
    public function insert( $data ) {
        global $wpdb;

        $ret = false;
        $error = false;
        $formats = array();
        foreach ($data as $name => $value) {
            $column = $this->getColumnByName($name);
            if ($column !== false) {
                if ($column->type == WpColumnType::DATETIME || $column->type == WpColumnType::TIMESTAMP) {
                    $data[$name] = $column->getDatetimeAsString($value);
                }
                $formats[] = $column->getQueryFormat();
            } else {
                $error = true;
                sosidee_log("WpTable.select() :: getColumnByName($name) returned false for table {$this->name}.");
                break;
            }
        }
        if( !$error ) {
            if ( $wpdb->insert( $this->name, $data, $formats ) !== false ) {
                    $ret = $wpdb->insert_id;
            } else {
                sosidee_log("WpTable.insert() :: wpdb.insert($this->name, \$data, \$formats) returned false for \$data: " .  print_r($data, true) . " and \$formats:" . print_r($formats, true) );
            }
        }
        return $ret;
    }

    /**
     * @param array $data : associative array [ column_name => value ]
     *      e.g.
     *          'foo' => 123        means foo = 123
     * @param array $filters : associative array [ column_name => value ]
     *      e.g.
     *          'foo' => 123        means foo = 123
     * @return bool
     */
    public function update( $data, $filters ) {
        global $wpdb;

        $ret = false;
        $error = false;
        $formats = array();
        $filter_formats = array();
        foreach ($data as $name => $value) {
            $column = $this->getColumnByName( $name );
            if ( $column !== false ) {
                if ( $column->type == WpColumnType::DATETIME || $column->type == WpColumnType::TIMESTAMP ) {
                    $data[$name] = $column->getDatetimeAsString( $value );
                }
                $formats[] = $column->getQueryFormat();
            } else {
                $error = true;
                sosidee_log("WpTable.update() :: getColumnByName($name) returned false for table {$this->name}.");
                break;
            }
        }
        if( !$error ) {
            foreach ( $filters as $name => $value ) {
                $column = $this->getColumnByName( $name );
                if ($column !== false) {
                    if ( $column->type == WpColumnType::DATETIME || $column->type == WpColumnType::TIMESTAMP ) {
                        $filters[$name] = $column->getDatetimeAsString( $value );
                    }
                    $filter_formats[] = $column->getQueryFormat();
                } else {
                    $error = true;
                    sosidee_log("WpTable.update() :: getColumnByName($name) returned false for table {$this->name}.");
                    break;
                }
            }
        }
        if( !$error ) {
            if ( $wpdb->update( $this->name, $data, $filters, $formats, $filter_formats ) !== false ) {
                $ret = true;
            } else {
                sosidee_log("WpTable.update() :: wpdb.update($this->name, \$data, \$filters, \$formats, \$filter_formats) returned false for \$data: " .  print_r($data, true) . ", \$filters:" . print_r($filters, true) . ", \$formats:" . print_r($formats, true) . " and \$filter_formats:" . print_r($filter_formats, true) );
            }
        }
        return $ret;
    }

    public function get( $filters = [], $orders = [] ) {
        $results = $this->select( $filters, $orders );
        if ( is_array($results) ) {
            if ( count($results) == 1 ) {
                return $results[0];
            } else {
                if ( count($results) > 1 ) {
                    sosidee_log("WpTable.get() :: select() returned a wrong array length: " . count($results) . " (requested: 1) for \$filters: " . print_r($filters, true) );
                }
                return false;
            }
        } else {
            return false;
        }
    }

}