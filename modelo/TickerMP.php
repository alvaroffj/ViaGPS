<?php
/**
 * Description of tickerMP
 *
 * @author Alvaro Flores
 */
include_once 'modelo/Bd.php';

class TickerMP {
	protected $_dbTable = "ticker";
    protected $_id = "ticker_id";
    protected $_bd;

	function __construct() {
		$this->_bd = new Bd('bitcoin', 'WwQNBMM2NGdvJTCX', '10.179.7.224', 'bitcoin');
    }

    function fetchAll($attr = null) {
    	if($attr == null) {
            $sAttr = "*";
        } else {
            $sAttr = implode(",", $attr);
        }

        $sql = "SELECT $sAttr FROM $this->_dbTable";
        $res = $this->_bd->sql($sql);
        $arr = array();
        while($row = mysql_fetch_object($res)) {
            $arr[] = $row;
        }
        return $arr;
    }

    function fetchLast() {
        $sql = "SELECT * FROM $this->_dbTable ORDER BY ticker_id DESC LIMIT 0,1";
        $res = $this->_bd->sql($sql);
        $row = mysql_fetch_object($res);
        return $row;
    }

    function insert($data) {
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        
        $i = 0;
        foreach($keys as $k) {
            if($k!=$this->_id) {
                if($i) {
                    $vars .= ", ".strtoupper($k);
                    $vals .= ", '".$this->_bd->limpia($data->$k)."'";
                } else {
                    $vars = strtoupper($k);
                    $vals = "'".$this->_bd->limpia($data->$k)."'";
                }
            }
            $i++;
        }
        
        $sql = "INSERT INTO $this->_dbTable ($vars) VALUES ($vals)";
        $this->_bd->sql($sql);
        return mysql_insert_id($this->_bd->get_conex());
    }
}