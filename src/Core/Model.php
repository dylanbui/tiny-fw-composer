<?php

namespace TinyFw\Core;

abstract class Model
{
	protected $_table_name;
	protected $_primary_key;
	protected $_conn;

	/*
	 * @The sql query
	 */
	private $_sql;

    /*
     * @The select sql query
     */
    private $_select_cmd;

	/**
	 * @The name=>value pairs
	 */
	private $_values = array();

    /**
     * Holds an array table data (fields, string fields)
     * @var $schemaTableData
     */
    private static $schemaTableData = array();

//    $start = microtime(TRUE);
//    $runtime = microtime(TRUE) - $start;

	function __construct($conn = NULL)
	{
		if(is_null($conn))
			$this->_conn = DbConnection::getInstance();
		else
			$this->_conn = $conn;

        if (empty(self::$schemaTableData[$this->_table_name]))
        {
            $fields = $this->getTableField();
            $strFields = implode("`,`",$fields);
            $str_select = "`" . $strFields . "`";
            $str_select_cmd = "SELECT {$str_select} FROM {$this->_table_name}";

            self::$schemaTableData[$this->_table_name]['fields'] = $fields;
            self::$schemaTableData[$this->_table_name]['str_select_fields'] = $str_select;
            self::$schemaTableData[$this->_table_name]['str_select_cmd'] = $str_select_cmd;
        }
        $this->_select_cmd = self::$schemaTableData[$this->_table_name]['str_select_cmd'];
	}

	/**
	 * add a value to the values array
     *
	 * @access public
	 * @param string $key the array key
	 * @param string $value The value
	 */
	public function addValue($key, $value)
	{
		$this->_values[$key] = $value;
	}

	/**
	 * set the values
	 *
	 * @access public
	 * @param array
	 */
	public function setValues($array)
	{
		$this->_values = $array;
	}

    /**
     * Get one row by id
     *
     * @access public
     * @param int $id
     * @return row (array)
     */
	public function get($id)
	{
        $this->_sql = $this->_select_cmd . " WHERE `{$this->_primary_key}` = {$id}";
        return $this->_conn->selectOneRow($this->_sql);
	}

    /**
     * Get row record from table
     *
     * @Coder : DucBui 12/14/2010
     * @access public
     * @param string $condition
     * @param array $params
     * @return row (array)
     */
    public function getRow($condition,$params = array())
    {
        $result = $this->getRowset($condition,$params, NULL, 0, 1);
        if(isset($result[0])) return $result[0];
        return FALSE;
    }

    /**
     * Get any rows record from table
     *
     * @Coder : DucBui 12/14/2010
     * @access public
     * @param string $condition
     * @param array $params
     * @param string $order_by
     * @param int $offset
     * @param int $limit
     * @return array rows (array)
     */
    public function getRowset($condition = NULL,$params = array(),$order_by = NULL,$offset = 0,$limit = 0)
    {
        $this->_sql = $this->_select_cmd;
        if(!is_null($condition))
            $this->_sql .= " WHERE " . $condition;
        if(!is_null($order_by))
            $this->_sql .= " ORDER BY " . $order_by;
        if($limit > 0)
            $this->_sql .= " LIMIT {$offset},{$limit} ";
        try{
            $this->_sql = $this->compileBinds($this->_sql, $params);
            return $this->_conn->query($this->_sql);
        }catch(\Exception $e)
        {
            echo 'Model Exception: ' . $e->getMessage();
            exit();
        }
    }

    /**
     * Get total row
     *
     * @Coder : DucBui 12/14/2010
     * @access public
     * @param string $condition
     * @param array $params
     * @return int number
     */
    public function getTotalRow($condition = NULL,$params = array())
    {
        $sSql = "SELECT COUNT(*) AS TotalRow FROM " . $this->_table_name;
        if(!is_null($condition))
            $sSql .= " WHERE " . $condition;

        $sth = $this->_conn->selectOneRow($sSql, $params);
        return $sth['TotalRow'];
    }

    /**
     * Get count affected
     *
     * @Coder : DucBui 12/14/2010
     * @access public
     * @return count affected
     */
    public function countAffected()
    {
        return $this->_conn->countAffected();
    }

    /**
     * On or Off active field
     *
     * @access public
     * @param $id
     * @return count affected rows
     */
    public function setActiveField($id)
    {
        $sql = "SELECT `active` FROM {$this->_table_name} WHERE `{$this->_primary_key}` = :id";
        $row = $this->_conn->selectOneRow($sql, array(':id' => $id));
        // -- Run fast more than get($id) --
        $act = ($row['active'] == 1 ? 0 : 1);
        return $this->update($id, array('active' => $act));
    }

	/**
	 * insert a record into a table
	 *
	 * @access public
	 * @param array $values An array of fieldnames and values
	 * @return int The last insert ID
	 */
	public function insert($values = NULL)
	{
		$values = is_null($values) ? $this->_values : $values;
        $this->_sql = "INSERT INTO {$this->_table_name} SET ";
	
		$obj = new \CachingIterator(new \ArrayIterator($values));
		try
		{
			foreach( $obj as $field=>$val)
			{
                $this->_sql .= "`$field` = :$field";
                $this->_sql .=  $obj->hasNext() ? ',' : '';
                $this->_sql .= "\n";
			}
            $this->_conn->insert($this->_sql, $values);
            return $this->_conn->getLastId();
		}
		catch(\Exception $e)
		{
            echo 'Model Exception: ' . $e->getMessage();
            exit();
		}
	}
	
	/**
	 * delete a recored from a table
	 *
	 * @access public
	 * @param int $id
     * @return count affected rows
	 */
	public function delete($id)
	{
		return $this->deleteWithCondition("{$this->_primary_key} = ?", array($id));		
	}
	
	/**
	 * update a table
	 *
	 * @access public
	 * @param string $condition
     * @param array $params
     * @return count affected rows
	 */
	public function deleteWithCondition($condition, $params)
	{
		try
		{
            $this->_sql = "DELETE FROM {$this->_table_name} WHERE {$condition}";
            $this->_conn->delete($this->_sql, $params);
            return $this->_conn->countAffected();
		}
		catch(\Exception $e)
		{
            echo 'Model Exception: ' . $e->getMessage();
            exit();
		}
	}

	/**
	 * update row in table with id and value
	 *
	 * @access public
	 * @param int $id
     * @param array $values
     * @return count affected rows
	 */
	public function update($id,$values = NULL)
	{
		return $this->updateWithCondition("{$this->_primary_key} = ?", array($id), $values);
	}

	/**
     * update row in table with id and value
	 *
	 * @access public
     * @param string $condition
     * @param array $params => params of condition
     * @param array $values
     * @return count affected rows
	 *
	 */
	public function updateWithCondition($condition, $params, $values = NULL)
	{
		$condition = $this->compileBinds($condition, $params);
		
		$values = is_null($values) ? $this->_values : $values;
		try
		{
			$obj = new \CachingIterator(new \ArrayIterator($values));

            $this->_sql = "UPDATE {$this->_table_name} SET \n";
			foreach( $obj as $field=>$val)
			{
                $this->_sql .= "`$field` = :$field";
                $this->_sql .= $obj->hasNext() ? ',' : '';
                $this->_sql .= "\n";
			}
            $this->_sql .= " WHERE $condition";

            $this->_conn->update($this->_sql, $values);
            return $this->_conn->countAffected();
		}
		catch(\Exception $e)
		{
            echo 'Model Exception: ' . $e->getMessage();
            exit();
		}
	}	

    /**
     * Run query
     *
     * @access public
     * @param $sql The table name
     * @param $params Parameter
     * @return array
     */
    public function runQuery($sql, $params = NULL)
    {
        $sql = $this->compileBinds($sql, $params);
        $this->_sql = $sql;
        return $this->_conn->query($sql);
    }

    /**
     * Get one record from table
     *
     * @access public
     * @param $sql The table name
     * @param $params Parameter
     * @return array
     */
    public function runQueryGetFirstRow($sql = NULL, $params = NULL)
    {
        $this->_sql = $sql;
        return $this->_conn->selectOneRow($sql, $params);
    }

    /**
     * Show debug
     *
     * @Coder : DucBui 12/14/2010
     * @access public
     * @param bool $showSchema
     */
    public function showDebug($showSchema = false)
    {
        echo "<pre>";
        echo "Current sql : ".$this->_sql;
        echo "</pre>";
        if ($showSchema) {
            echo "<pre>";
            print_r(self::$schemaTableData);
            echo "</pre>";
            exit();
        }

    }
    /*
       +--------------------------------------------------------------------------
       |   PRIVATE FUNCTION
       +--------------------------------------------------------------------------
    */
    /**
     * Compile Binds
     *
     * @Coder : DucBui 12/14/2010
     * @access public
     * @param string $sql
     * @param array $binds
     * @return string
     */
	private function compileBinds($sql, $binds)
	{
		if (strpos($sql, '?') === FALSE || empty($binds))
		{
			return $sql;
		}

		if ( ! is_array($binds))
		{
			$binds = array($binds);
		}

		// Get the sql segments around the bind markers
		$segments = explode('?', $sql);

		// The count of bind should be 1 less then the count of segments
		// If there are more bind arguments trim it down
		if (count($binds) >= count($segments)) {
			$binds = array_slice($binds, 0, count($segments)-1);
		}

		// Construct the binded query
		$result = $segments[0];
		$i = 0;
		foreach ($binds as $bind)
		{
			$result .= $this->_conn->escape($bind);
			$result .= $segments[++$i];
		}

		return $result;
	}

    /**
     * Get all field name of table
     *
     * @access public
     * @return array
     */
	private function getTableField()
	{
		$sQuery = "SHOW FIELDS FROM " . $this->_table_name;
		$results = $this->_conn->query($sQuery);
        $fields = array();
		foreach ($results as $result)
			$fields[] = $result['Field'];

        return $fields;
	}
} // end of class

?>
