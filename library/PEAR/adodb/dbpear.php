<?php
class DbConnect {
	
	private $connectionstr;
	protected $con = false;
	private $count = false;
	private $errorMessage = "";
	private $FlagSelect = 0;
	private $lastSQL = "";

	public function __construct() {
		if(!defined("LOGS_DIR")) define("LOGS_DIR", dirname(__FILE__));
		if(defined("DB_CONNECTION_STRING")) {
			$this->connectionstr = DB_CONNECTION_STRING;
		} else {
			trigger_error("SERVER is not defined.",E_USER_ERROR);
		}
		$this->getConnection();
	}

	public function getConnection() {
		if( $this->con == false ) {
			return( $this->doConnect() );
		}
		return( $this->con);
	}

	public function doConnect() {
		try {
			$er = error_reporting(E_ALL);
			$options = array(
				"debug" => 2,
			);

			$this->con = DB::connect($this->connectionstr, $options);


			if( DB::isError( $this->con )) {
				print $this->con->getMessage();
				exit;
				#$this->errorMessage .= "Not connection fail [".$this->connectionstr."]. \n";
				$this->errorMessage .= "Not connection fail. \n" .$this->errorMessage ;
				error_log($this->errorMessage,0);
				print $this->errorMessage;
				exit;
				return false;
			}
			#mysql_query("SET NAMES 'utf8'");
			#mysqli_query($this->con, "SET NAMES 'utf8'");

			error_reporting($er);
			return( $this->con );
	    } catch (Exception $ex) {
            echo($ex->getMessage());
			exit;
	    }
	}

	public function doClose() {
		if( $this->con != false) {
			$this->con = false;
		}
	}

	protected function getKeys() {
		return "";
	}

	/**
	 * prepare
	 * @param @sql
	 */
	public function prepare($sql) {
		$this->lastSQL 		= $sql;
		$this->stmt 		= null;
		$this->affectedRows = 0;
		$this->numrows 		= 0;
		$this->stmt 		= $this->con->prepare($sql);
        return $this->stmt;
	}

	public function exec($params=array(),$mode=DB_FETCHMODE_ASSOC) {
		$log_file = LOGS_DIR."db".date("Ymd").".log";
		$lw = new LogWriter($log_file);
		$message = $this->lastSQL;
		if (sizeof($params)>0 && is_array($params)) $message .= " [".join(",", $params)."]";
		$lw->write($message);

		$result = $this->con->execute($this->stmt, $params);
		if (PEAR::isError($result)) {
			$lw->write("[ERROR]".$result->getMessage());
			throw new Exception($result->getMessage());
			return false;
		}

		$lw->close();

		if (!is_object($result) && $result == DB_OK) {
			return true;
		}

		if (is_object($result) == 1) {
			$nIdx=0;
	        $retVal = array();
			while ( $row = $result->fetchRow($mode) ) {
				$retVal[$nIdx++] = $row;
			}
			$this->count = $nIdx;
			return $retVal;
		}
		return array();
	}

	public function affected() {
		return $this->count;
	}

	public function quote($val) {
		$val = ereg_replace("\'","''",$val);
		$val = "'".$val."'";
		return( $val );
	}

	public function getLastSQL() {
		return $this->lastSQL;
	}

	public function clearError() {
		$this->errorMessage = "";
	}

	public function getError() {
		return $this->errorMessage;
	}

	/**
	 * Cac ham duoi day duoc bo xung khong co trong vesion goc.
	 * No duoc ke thua cac ham trong DB/common.php tu class DB_common
	 *
	 **/

	/**
	 * function beginTrans
	 *
	 * @param 	Boolean:	$onoff [Default: FALSE]
	 * @version 1.0
	 **/
	public function beginTrans($onoff = FALSE){
		$log_file = LOGS_DIR."db".date("Ymd").".log";
		$lw = new LogWriter($log_file);
		$message = "--Begin transaction: ";

	    $bool = $this->con->autoCommit($onoff);
		if ( $bool == DB_OK ) $message .= " success";
		else $message .= " failed";

		$lw->write($message);
		$lw->close();
	}

	/**
	 * function commitTrans
	 *
	 * @version 1.0
	 **/
	public function commitTrans() {
		$log_file = LOGS_DIR."db".date("Ymd").".log";
		$lw = new LogWriter($log_file);
		$message = "--Begin commmit: ";

		$bool = $this->con->commit();
		if ( $bool == DB_OK ) $message .= " success";
		else $message .= " failed";

		$lw->write($message);
		$lw->close();
    }

	/**
	 * function autoCommitTrans
	 *
	 * @param 	Boolean:	$onoff [Default: FALSE]
	 * @version 1.0
	 **/
    public function autoCommitTrans($onoff = FALSE) {
		$log_file = LOGS_DIR."db".date("Ymd").".log";
		$lw = new LogWriter($log_file);
		$message = "--Begin commmit: ";

		$bool = $this->con->autoCommit($onoff);
		if ( $bool == DB_OK ) $message .= " success";
		else $message .= " failed";

		$lw->write($message);
		$lw->close();
    }

	/**
	 * function rollbackTrans
	 *
	 * @version 1.0
	 **/
	public function rollbackTrans() {
		$log_file = LOGS_DIR."db".date("Ymd").".log";
		$lw = new LogWriter($log_file);
		$message = "--Begin rollback: ";

		$bool = $this->con->rollback();
		if ( $bool == DB_OK ) $message .= " success";
		else $message .= " failed";

		$lw->write($message);
		$lw->close();
    }

	/**
	 * function get id new insert
	 *
	 * @version 1.0
	 **/
	 public function getLastInsertId() {
        $strSQL = "SELECT last_insert_id()";
        $this->prepare($strSQL);
        $reId = $this->execRun();
        $lastId = $reId[0]["last_insert_id()"];
        return $lastId;
    }

	 /**
	  * DB_FETCHMODE_DEFAULT 	= 0
	  * DB_FETCHMODE_ORDERED 	= 1
	  * DB_FETCHMODE_ASSOC 		= 2
	  * DB_FETCHMODE_OBJECT 	= 3
	  * DB_FETCHMODE_FLIPPED	= 4
	  **/
	public function execRun($params = array(), $mode=DB_FETCHMODE_ASSOC) {
		$log_file = LOGS_DIR."db".date("Ymd").".log";
		$lw = new LogWriter($log_file);
		$message = $this->lastSQL;
		if (sizeof($params)>0 && is_array($params)) $message .= " [".join(",", $params)."]";
		$lw->write($message);

		$result = $this->con->execute($this->stmt, $params);
		if (PEAR::isError($result)) {
			$lw->write("[ERROR]".$result->getMessage());
			$this->rollbackTrans();
			throw new Exception($result->getMessage());
			return false;
		}

		$lw->close();

		if (!is_object($result) && $result == DB_OK) {
			return true;	
		}

		if (is_object($result) == 1) {
			$nIdx=0;
	        $retVal = array();
			while ( $row = $result->fetchRow($mode) ) {
				$retVal[$nIdx++] = $row;
			}
			$this->count = $nIdx;
			return $retVal;
		}
		return array();
	}

	/**
	 * function get max id
	 * 
	 * @param	String:	$table	[Default: null]
	 * @param	String:	$key	[Default: null]
	 * @version 1.0
	 **/
	 public function getMaxId($table = null, $key = null) {
        if( !empty($table) && !empty($key) && !is_array($key) ){
			$sql = "SELECT MAX(".$key.") AS maxId FROM ". $table;
			$this->prepare($sql);		 	
		 	$result = $this->execRun(array());
		 	return isset($result[0]["maxId"]) ? $result[0]["maxId"] : null;
		}
		return null;
    }
	
	/**
	 * function get next max id
	 * 
	 * @param	String:	$table	[Default: null]
	 * @param	String:	$key	[Default: null]
	 * @version 1.0
	 **/
	public function getNextId($table = null, $key = null) {
		$maxid = $this->getMaxId($table = null, $key = null);
		return (int)$maxid + 1;
	}
	
	/**
     * Automaticaly generates an insert or update query and call prepare()
     * and execute() with it
     *
     * @param string $table         the table name
     * @param array  $fields_values the associative array where $key is a
     *                               field name and $value its value
     * @param int    $mode          a type of query to make:
     *                               DB_AUTOQUERY_INSERT(1) or DB_AUTOQUERY_UPDATE(2)
     * @param string $where         for update queries: the WHERE clause to
     *                               append to the SQL statement.  Don't
     *                               include the "WHERE" keyword.
     *
     * @return mixed  a new DB_result object for successful SELECT queries
     *                 or DB_OK for successul data manipulation queries.
     *                 A DB_Error object on failure.
     *
     * @uses DB_common::autoExecute()
     */
	public function insert_db($table, $fields_values, $where = FALSE) {
        #=======================================================================
        # Write Log Change Data ================================================
        #=======================================================================
        if (strtolower(trim($table)) != 'tbl_writelog'):
            $_user     = Generals::getUserData();
            $new_datas = array();
            foreach ($fields_values as $key => $value ){
                $new_datas[] = $key.' = '.$value;
            }

            if (!empty($new_datas)):
                $introtext = 'Insert Table: '.$table.'<br/>';
                $introtext.= 'New Data:';
                $introtext.= '<p>'.implode('<br/>', $new_datas).'</p>';

                $logdata = array();
                $logdata['title']       = 'Insert Table: '.$table;
                $logdata['introtext']   = $introtext;
                $logdata['create_on']   = date('Y-m-d H:i:s');
                $logdata['create_by']   = $_user['id'];
                $this->insert_db('tbl_writelog', $logdata);
            endif;
        endif;
        #=======================================================================
        #=======================================================================

		$params   = array();
	 	$strSQL   = " INSERT INTO ".$table." (";
	 	$sqlValue = " VALUES (";
	 	foreach ($fields_values as $key => $value ){
			if(get_magic_quotes_gpc()) $value = stripslashes($value);
	 		$strSQL .= "`".$key."`,";
	 		$sqlValue .= "?,";
	 		array_push($params, $value);		 		
	 	}
	 	$strSQL = substr($strSQL, 0, -1). " )".substr($sqlValue, 0, -1)." ) ";
	 	$this->prepare($strSQL);
	 	return $this->execRun($params);
	}
	
	/**
	 * function update_db
	 * 
	 * @param	String:	$table		
	 * @param	Array :	$fields_values
	 * @param	String:	$where			[Default: FALSE]
	 * @param	Array :	$param			[Default: array]
	 * @version 1.0
	 **/
	public function update_db($table, $fields_values, $where = FALSE, $param = array()) {
        #=======================================================================
        # Write Log Change Data ================================================
        #=======================================================================
        if (strtolower(trim($table)) != 'tbl_writelog'):
            $condition = array();
            $new_datas = array();

            $_user = Generals::getUserData();
            $temp1 = explode('and', strtolower($where));
            foreach ($temp1 as $val1):
                $temp2 = explode('or', $val1);
                foreach ($temp2 as $val2):
                    $val2 = str_replace('  ', ' ', trim($val2));
                    $val2 = explode(' ', $val2);
                    $condition[] = array('field' => $val2[0], 'opera' => $val2[1]);
                endforeach;
            endforeach;

            foreach ($param as $key => $value ){
                $condition[$key] = $condition[$key]['field'].' '.$condition[$key]['opera'].' '.$value;
            }

            $this->prepare(" SELECT * FROM ".$table.($where ? " WHERE ".$where : ''));
            $result = $this->execRun($param);
            $result = $result[0];

            foreach ($fields_values as $key => $value ){
                if ($result[$key] != $value) $new_datas[] = $key.' = '.$value;
            }

            if (!empty($new_datas)):
                $introtext = 'Update Table: '.$table.'<br/>';
                $introtext.= $condition ? 'Where '.implode('; ', $condition).'<br/>' : '';
                $introtext.= '<p>'.implode('<br/>', $new_datas).'</p>';

                $logdata = array();
                $logdata['title']       = 'Update Table: '.$table;
                $logdata['introtext']   = $introtext;
                $logdata['create_on']   = date('Y-m-d H:i:s');
                $logdata['create_by']   = $_user['id'];
                $this->insert_db('tbl_writelog', $logdata);
            endif;
        endif;
        #=======================================================================
        #=======================================================================

	 	$params  = array();
	 	$strSQL  = " UPDATE ".$table." SET ";
	 	foreach ($fields_values as $key => $value ){
	 		if(get_magic_quotes_gpc()) $value = stripslashes($value);
	 		$strSQL .= $key ."= ?,";
	 		array_push($params, $value);
	 	}
	 	$strSQL  = substr($strSQL,0, -1);
	 	$strSQL .= " WHERE ".$where;
	 	foreach ($param as $key => $value ){
	 		array_push($params, $value);
	 	}
	 	
	 	$this->prepare($strSQL);
	 	return $this->execRun($params);
	}
	
	/**
	 * function delete_db
	 * 
	 * @param	String:	$table		
	 * @param	String:	$where			[Default: FALSE]
	 * @param	Array :	$param			[Default: array]
	 * @version 1.0
	 **/
	public function delete_db($table, $where = FALSE, $params = array()) {
        #=======================================================================
        # Write Log Change Data ================================================
        #=======================================================================
        if (strtolower(trim($table)) != 'tbl_writelog'):
            $condition = array();
            $old_datas = array();

            $_user = Generals::getUserData();
            $temp1 = explode('and', strtolower($where));
            foreach ($temp1 as $val1):
                $temp2 = explode('or', $val1);
                foreach ($temp2 as $val2):
                    $val2 = str_replace('  ', ' ', trim($val2));
                    $val2 = explode(' ', $val2);
                    $condition[] = array('field' => $val2[0], 'opera' => $val2[1]);
                endforeach;
            endforeach;
            foreach ($params as $key => $value ){
                $condition[$key] = $condition[$key]['field'].' '.$condition[$key]['opera'].' '.$value;
            }

            $this->prepare(" SELECT * FROM ".$table.($where ? " WHERE ".$where : ''));
            $result = $this->execRun($params);

            foreach ($result as $i => $items):
                foreach ($items as $key => $value ){
                    $old_datas[$i][] = $key.' = '.$value;
                }
            endforeach;

            if (!empty($old_datas)):
                $introtext = 'Delete Table: '.$table.'<br/>';
                $introtext.= $condition ? 'Where '.implode('; ', $condition).'<br/>' : '';
                $introtext.= 'Old Datas:';
                foreach ($old_datas as $olds):
                    $introtext.= '<p>'.implode('<br/>', $olds).'</p>';
                endforeach;

                $logdata = array();
                $logdata['title']       = 'Delete Table: '.$table;
                $logdata['introtext']   = $introtext;
                $logdata['create_on']   = date('Y-m-d H:i:s');
                $logdata['create_by']   = $_user['id'];
                $this->insert_db('tbl_writelog', $logdata);
            endif;
        endif;
        #=======================================================================
        #=======================================================================

		$strSQL = " DELETE FROM ".$table." ";
		if ($where && $params) {
			$strSQL.= " WHERE ".$where;
		}
		$this->prepare($strSQL);
		return $this->execRun($params);
	}
	
	function getFieldTable($_table) {
		$query	= "SHOW COLUMNS FROM {$_table} ";
		$this->prepare($query);
		$result = $this->exec();
		$return = array();
		if (is_array($result)) foreach ($result as $items):
			$return[] = $items['Field'];
		endforeach;
		
		return $return;
	}
	
	function getDataMapTable($datas, $table) {
		$fields = $this->getFieldTable($table);
		$values = array();
		if (!is_array($datas)) return $datas;
		foreach ($datas as $key => $val):
			if (in_array($key, $fields)) $values[$key] = $val;
		endforeach;
		
		return $values;
	}
}
?>
