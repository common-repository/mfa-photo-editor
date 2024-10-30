<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\DATA;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

trait Db
{
    private $_dbid = 0;
    
    /**
     * Retrieve the record id in 'options' table using the object key
     * 
     * @return  integer : record id
     *          boolean : false in case of error
     */
    public function getDbId()
    {
        $ret = $this->_dbid;
        if ($ret == 0)
        {
            global $wpdb;
            $sql = "SELECT option_id FROM {$wpdb->options} WHERE option_name=%s";
            $query = $wpdb->prepare( $sql, $this->key );
            $result = $wpdb->get_var( $query );
            if ( $result )
            {
                $this->_dbid = intval( $result );
                $ret = $this->_dbid;
            }
            else
            {
                $ret = false;
            }
        }
        return $ret;
    }

}