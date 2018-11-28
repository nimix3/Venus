<?php
// SQLi Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class SQLi
{
	protected $DBserver;
	protected $DBname;
	protected $DBuser;
	protected $DBpass;
	protected $DBport;
	protected $DBcharset;
	protected $DBobj;
	protected $DBQuery;
	protected $LastError;

	public function __construct($SQLConfig)
	{
		if(is_array($SQLConfig))
		{
			$this->DBserver = $SQLConfig['server'];
			$this->DBname = $SQLConfig['name'];
			$this->DBuser = $SQLConfig['username'];
			$this->DBpass = $SQLConfig['password'];
			$this->DBport = $SQLConfig['port'];
			$this->DBcharset = $SQLConfig['charset'];
		}	
	}
	
	public function setConfig($SQLConfig)
	{
		if(is_array($SQLConfig))
		{
			$this->DBserver = $SQLConfig['server'];
			$this->DBname = $SQLConfig['name'];
			$this->DBuser = $SQLConfig['username'];
			$this->DBpass = $SQLConfig['password'];
			$this->DBport = $SQLConfig['port'];
			$this->DBcharset = $SQLConfig['charset'];
			return true;
		}
		return false;	
	}
	
	public function __destruct()
	{
		return $this->CloseDB();
	}
	
	public function InitDB($DBserver=null,$DBuser=null,$DBpass=null,$DBname=null,$DBport=null,$charset='')
	{
		try{
			if(!isset($DBserver) or empty($DBserver))
				$DBserver = $this->DBserver;
			if(!isset($DBuser) or empty($DBuser))
				$DBuser = $this->DBuser;
			if(!isset($DBpass) or empty($DBpass))
				$DBpass = $this->DBpass;
			if(!isset($DBname) or empty($DBname))
				$DBname = $this->DBname;
			if(!isset($DBport) or empty($DBport))
				$DBport = $this->DBport;
			$__DBh = mysqli_connect($DBserver, $DBuser, $DBpass, $DBname, (int)$this->DBport);
			if(!$__DBh) {
				return false; //mysqli_connect_error();
			}
			else
			{
				$this->DBobj = $__DBh;
				if(!isset($charset) or empty($charset))
					$charset = $this->DBcharset;
				@ mysqli_set_charset($this->DBobj, $charset);
				@ mysqli_query($this->DBobj,"SET NAMES ".$charset);
				return true;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function InitAliveDB($DBserver=null,$DBuser=null,$DBpass=null,$DBname=null,$DBport=null,$charset='')
	{
		if(!isset($DBserver) or empty($DBserver))
			$DBserver = $this->DBserver;
		if(!isset($DBuser) or empty($DBuser))
			$DBuser = $this->DBuser;
		if(!isset($DBpass) or empty($DBpass))
			$DBpass = $this->DBpass;
		if(!isset($DBname) or empty($DBname))
			$DBname = $this->DBname;
		if(!isset($DBport) or empty($DBport))
			$DBport = $this->DBport;
		try{
			$__DBh = mysqli_connect("p:".$DBserver, $DBuser, $DBpass, $DBname, (int)$this->DBport);
			if (!$__DBh) {
				return false;
			}
			else
			{
				$this->DBobj = $__DBh;
				if(!isset($charset) or empty($charset))
					$charset = $this->DBcharset;
				@ mysqli_set_charset($this->DBobj, $charset);
				@ mysqli_query($this->DBobj,"SET NAMES ".$charset);
				return true;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function LastInsertId()
	{
		try{
			if(!$this->DBobj)
				return false;
			return mysqli_insert_id($this->DBobj);
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function StatusDB($Ping=false)
	{
		try{
			if($Ping)
			{
				if(!$this->DBobj)
					return false;
				if(mysqli_ping($this->DBobj))
					return true;
				else
					return false;
			}
			else
			{
				if(isset($this->DBobj) and !empty($this->DBobj))
					return true;
				else
					return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function KillDB()
	{
		try{	
			if(!$this->DBobj)
				return false;
			if(mysqli_kill($this->DBobj,mysqli_thread_id($this->DBobj)))
				return true;
			else
				return false;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function RecordDB()
	{
		try{
			if(!isset($this->DBobj) or empty($this->DBobj))
				return false;
			if(mysqli_begin_transaction($this->DBobj,MYSQLI_TRANS_START_READ_WRITE))
				return true;
			else
				return false;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function CommitDB()
	{
		try{
			if(!isset($this->DBobj) or empty($this->DBobj))
				return false;
			if(mysqli_commit($this->DBobj))
				return true;
			else
				return false;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function RollBackDB()
	{
		try{
			if(!$this->DBobj)
				return false;
			if(mysqli_rollback($this->DBobj))
				return true;
			else
				return false;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function CloseDB()
	{
		try{
			if($this->StatusDB())
			{
				@ mysqli_close($this->DBobj);
				@ $this->DBobj = null;
				return true;
			}
			else
				return false;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function GetQueryDB()
	{
		try{
			if(isset($this->DBQuery) and !empty($this->DBQuery))
				return $this->DBQuery;
			else
				return null;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function DebugDB()
	{
		try{
			if($this->StatusDB())
			{
				return mysqli_error($this->DBobj);
			}
			else
				return null;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function SecureDBQuery($str,$dbc=true)
	{
		try{
			if($dbc !== true)
			{
				$str = str_replace(array('(',')','[',']','{','}','*','?','!',';','&','%','-','_',"'",':"','/','\\','~','.','+','`','@','^','=','|','>','<'),'',$str);
				$str = str_replace(array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a"), array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z"), $str);
			}
			if($this->StatusDB())
			{
				$str = mysqli_real_escape_string($this->DBobj,$str);
			}
			return $str;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function SelectDB($select,$from,$where,$sign,$statement,$limit = 1,$xquery='')
	{
		try{
			@ $statement = str_replace("'","''",$statement);
			if(isset($where) and !empty($where))
				$sql = "SELECT ".$select." FROM `".$from."` WHERE ".$where.$sign.$statement." ".$xquery." LIMIT ".$limit;
			else
				$sql = "SELECT ".$select." FROM `".$from."` ".$xquery." LIMIT ".$limit;
			$this->DBQuery = $sql;
			if(!$this->StatusDB())
				$this->InitDB();
			if($this->StatusDB())
			{
				$res = null;
				@ $result = mysqli_query($this->DBobj,$sql);
				if(!isset($result) or empty($result) or is_bool($result))
					return null;
				if(mysqli_num_rows($result) <= 0)
					return null;
				while ($row = mysqli_fetch_assoc($result)) {
					$res[] = $row;
				}
				mysqli_free_result($result);
				return $res;
			}
			else
			{
				return null;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function SelectDBsecure($select,$from,$where,$sign,$statement,$vars='',$limit = 1,$xquery='')
	{
		try{
			if(!is_array($vars))
			{
				$vars = array($vars);
			}
			@ $statement = str_replace("'","''",$statement);
			if(isset($where) and !empty($where))
				$sql = "SELECT ".$select." FROM `".$from."` WHERE ".$where.$sign.$statement." ".$xquery." LIMIT ".$limit;
			else
				$sql = "SELECT ".$select." FROM `".$from."` ".$xquery." LIMIT ".$limit;
			$this->DBQuery = $sql;
			if(!$this->StatusDB())
				$this->InitDB();
			if($this->StatusDB())
			{
				$stmt = mysqli_prepare($this->DBobj,$sql);
				if($stmt === false)
				{
					$this->LastError[] = "Prepare error";
					return false;
				}
				$params = array();
				$types  = array_reduce($vars, function ($string, &$arg) use (&$params) {
					$params[] = &$arg;
					if (is_float($arg))         $string .= 'd';
					elseif (is_integer($arg))   $string .= 'i';
					elseif (is_string($arg))    $string .= 's';
					else                        $string .= 'b';
					return $string;
				}, '');
				array_unshift($params , $types);
				array_unshift($params , $stmt);
				//@ call_user_func_array("mysqli_stmt_bind_param",$params);
				mysqli_stmt_bind_param($stmt,$types,...array_values($vars));
				mysqli_stmt_execute($stmt);
				$res = null;
				if(function_exists('mysqli_stmt_get_result'))
				{
					@ $result = mysqli_stmt_get_result($stmt);
					if(!isset($result) or empty($result) or is_bool($result))
						return null;
					if(mysqli_num_rows($result) <= 0)
						return null;
					while ($row = mysqli_fetch_assoc($result)) {
						$res[] = $row;
					}
				}
				else
				{
					@ $result = $this->mysqlo_stmt_get_result($stmt);
					if(!isset($result) or empty($result) or is_bool($result))
						return null;
					while ($row = array_shift($result)) {
						$res[] = $row;
					}
				}
				@ mysqli_stmt_free_result($stmt);
				@ mysqli_free_result($result);
				mysqli_stmt_close($stmt);
				return $res;
			}
			else
			{
				return null;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function UpdateDB($table,$where,$sign,$statement,$data,$limit=1)
	{
		try{
			$d = "";
			foreach($data as $key => $val)
			{
				@ $val = str_replace("'","''",$val);
				$d .= '`'.$key.'`="'.$val.'",';
			}
			$d = trim($d, ",");
			$sql = "UPDATE `$table` SET ".$d." WHERE ".$where.$sign.$statement." LIMIT ".$limit;
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				@ $result = mysqli_query($this->DBobj,$sql);
				if(!$result)
					return false;
				else
					return $result;
			}
			else
			{
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function UpdateDBsecure($table,$where,$sign,$statement,$vars='',$data,$limit=1)
	{
		try{
			if(!is_array($vars))
			{
				$vars = array($vars);
			}
			$d = "";
			foreach($data as $key => $val)
			{
				$d .= '`'.$key.'`=?,';
			}
			$d = trim($d, ",");
			$sql = "UPDATE `$table` SET ".$d." WHERE ".$where.$sign.$statement." LIMIT ".$limit;
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				if(is_array($data))
				{
					$vars = array_merge($data,$vars);
				}
				$stmt = mysqli_prepare($this->DBobj,$sql);
				if($stmt === false)
				{
					$this->LastError[] = "Prepare error";
					return false;
				}
				$params = array();
				$types  = array_reduce($vars, function ($string, &$arg) use (&$params) {
					$params[] = &$arg;
					if (is_float($arg))         $string .= 'd';
					elseif (is_integer($arg))   $string .= 'i';
					elseif (is_string($arg))    $string .= 's';
					else                        $string .= 'b';
					return $string;
				}, '');
				array_unshift($params , $types);
				array_unshift($params , $stmt);
				//@ call_user_func_array("mysqli_stmt_bind_param",$params);
				mysqli_stmt_bind_param($stmt,$types,...array_values($vars));
				mysqli_stmt_execute($stmt);
				if(mysqli_stmt_affected_rows($stmt) <= 0)
				{
					@ mysqli_stmt_free_result($stmt);
					mysqli_stmt_close($stmt);
					return false;
				}
				else
				{
					@ mysqli_stmt_free_result($stmt);
					mysqli_stmt_close($stmt);
					return true;
				}
			}
			else
			{
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function UpdateOrInsertDB($table,$where,$sign,$statement,$data,$limit=1)
	{
		try{
			$res = $this->SelectDB('COUNT(*) AS total',$table,$where,$sign,$statement,$limit);
			if(isset($res[0]['total']) and !empty($res[0]['total']))
			{
				return $this->UpdateDB($table,$where,$sign,$statement,$data,$limit);
			}
			else
			{
				return $this->InsertDB($table,$data);
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function UpdateOrInsertDBsecure($table,$where,$sign,$statement,$vars='',$data,$limit=1)
	{
		try{
			$res = $this->SelectDBsecure('COUNT(*) AS total',$table,$where,$sign,$statement,$vars,$limit);
			if(isset($res[0]['total']) and !empty($res[0]['total']))
			{
				return $this->UpdateDBsecure($table,$where,$sign,$statement,$vars,$data,$limit);
			}
			else
			{
				return $this->InsertDBsecure($table,$data);
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function ReplaceDB($table,$data)
	{
		try{
			$d = "";
			foreach($data as $key => $val)
			{
				@ $val = str_replace("'","''",$val);
				$d .= '`'.$key.'`="'.$val.'",';
			}
			$d = trim($d, ",");
			$sql = "REPLACE INTO `$table` SET ".$d;
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				@ $result = mysqli_query($this->DBobj,$sql);
				if(!$result)
					return false;
				else
					return $result;
			}
			else
				return false;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function ReplaceDBsecure($table,$vars)
	{
		try{
			$d = "";
			foreach($vars as $key => $val)
			{
				@ $val = str_replace("'","''",$val);
				$d .= '`'.$key.'`= ?,';
			}
			$d = trim($d, ",");
			$sql = "REPLACE INTO `$table` SET ".$d;
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				$stmt = mysqli_prepare($this->DBobj,$sql);
				if($stmt === false)
				{
					$this->LastError[] = "Prepare error";
					return false;
				}
				$params = array();
				$types  = array_reduce($vars, function ($string, &$arg) use (&$params) {
					$params[] = &$arg;
					if (is_float($arg))         $string .= 'd';
					elseif (is_integer($arg))   $string .= 'i';
					elseif (is_string($arg))    $string .= 's';
					else                        $string .= 'b';
					return $string;
				}, '');
				array_unshift($params , $types);
				array_unshift($params , $stmt);
				//@ call_user_func_array("mysqli_stmt_bind_param",$params);
				mysqli_stmt_bind_param($stmt,$types,...array_values($vars));
				mysqli_stmt_execute($stmt);
				if(mysqli_stmt_affected_rows($stmt) <= 0)
				{
					@ mysqli_stmt_free_result($stmt);
					mysqli_stmt_close($stmt);
					return false;
				}
				else
				{
					@ mysqli_stmt_free_result($stmt);
					mysqli_stmt_close($stmt);
					return true;
				}
			}
			else
			{
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function DeleteDB($table,$where,$sign,$statement,$limit=1)
	{
		try{
			@ $statement = str_replace("'","''",$statement);
			$sql = "DELETE FROM `$table` WHERE ".$where.$sign.$statement." LIMIT ".$limit;
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				@ $result = mysqli_query($this->DBobj,$sql);
				if(!$result)
					return false;
				else
					return $result;
			}
			else
			{
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function DeleteDBsecure($table,$where,$sign,$statement,$vars='',$limit=1)
	{
		try{
			if(!is_array($vars))
			{
				$vars = array($vars);
			}
			@ $statement = str_replace("'","''",$statement);
			$sql = "DELETE FROM `$table` WHERE ".$where.$sign.$statement." LIMIT ".$limit;
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				$stmt = mysqli_prepare($this->DBobj,$sql);
				if($stmt === false)
				{
					$this->LastError[] = "Prepare error";
					return false;
				}
				$params = array();
				$types  = array_reduce($vars, function ($string, &$arg) use (&$params) {
					$params[] = &$arg;
					if (is_float($arg))         $string .= 'd';
					elseif (is_integer($arg))   $string .= 'i';
					elseif (is_string($arg))    $string .= 's';
					else                        $string .= 'b';
					return $string;
				}, '');
				array_unshift($params , $types);
				array_unshift($params , $stmt);
				//@ call_user_func_array("mysqli_stmt_bind_param",$params);
				mysqli_stmt_bind_param($stmt,$types,...array_values($vars));
				mysqli_stmt_execute($stmt);
				if(mysqli_stmt_affected_rows($stmt) <= 0)
				{
					@ mysqli_stmt_free_result($stmt);
					mysqli_stmt_close($stmt);
					return false;
				}
				else
				{
					@ mysqli_stmt_free_result($stmt);
					mysqli_stmt_close($stmt);
					return true;
				}
			}
			else
			{
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function InsertDB($table,$data)
	{
		try{
			$d = "";
			$e = "";
			foreach($data as $key => $val)
			{
				@ $val = str_replace("'","''",$val);
				$d .= "`$key`,";
				$e .= "'$val',";
			}
			$d = trim($d, ",");
			$e = trim($e, ",");
			$sql = "INSERT INTO `$table` (".$d.") VALUES (".$e.")";
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				@ $result = mysqli_query($this->DBobj,$sql);
				if(!$result)
					return false;
				else
					return $result;
			}
			else
			{
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function InsertDBsecure($table,$vars)
	{
		try{
			$d = "";
			$e = "";
			foreach($vars as $key => $val)
			{
				@ $val = str_replace("'","''",$val);
				$d .= "`$key`,";
				$e .= "?,";
			}
			$d = trim($d, ",");
			$e = trim($e, ",");
			$sql = "INSERT INTO `$table` (".$d.") VALUES (".$e.")";
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				$stmt = mysqli_prepare($this->DBobj,$sql);
				if($stmt === false)
				{
					$this->LastError[] = "Prepare error";
					return false;
				}
				$params = array();
				$types  = array_reduce($vars, function ($string, &$arg) use (&$params) {
					$params[] = &$arg;
					if (is_float($arg))         $string .= 'd';
					elseif (is_integer($arg))   $string .= 'i';
					elseif (is_string($arg))    $string .= 's';
					else                        $string .= 'b';
					return $string;
				}, '');
				array_unshift($params , $types);
				array_unshift($params , $stmt);
				//@ call_user_func_array("mysqli_stmt_bind_param",$params);
				mysqli_stmt_bind_param($stmt,$types,...array_values($vars));
				mysqli_stmt_execute($stmt);
				if(mysqli_stmt_affected_rows($stmt) <= 0)
				{
					@ mysqli_stmt_free_result($stmt);
					mysqli_stmt_close($stmt);
					return false;
				}
				else
				{
					@ mysqli_stmt_free_result($stmt);
					mysqli_stmt_close($stmt);
					return true;
				}
			}
			else
			{
				return false;
			}	
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}

	public function TableExistDB($table)
	{
		try{
			$sql = "DESCRIBE `".$table."`";
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				@ $result = mysqli_query($this->DBobj,$sql);
				if(!$result)
					return false;
				else
					return $result;
			}
			else
			{
				return null;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	public function ClearTableDB($table)
	{
		try{
			$sql = "TRUNCATE TABLE `".$table."`";
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				@ $result = mysqli_query($this->DBobj,$sql);
				if(!$result)
					return false;
				else
					return $result;
			}
			else
			{
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}

	public function ExecDB($exec)
	{
		try{
			$sql = $exec;
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				@ $result = mysqli_query($this->DBobj,$sql);
				return $result;
			}
			else
			{
				return null;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function ExecMultiDB($exec)
	{
		try{
			$sql = $exec;
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				@ $result = mysqli_multi_query($this->DBobj,$sql);
				return $result;
			}
			else
			{
				return null;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	public function ExecDBsecure($exec,$vars)
	{
		try{
			$sql = $exec;
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				$stmt = mysqli_prepare($this->DBobj,$sql);
				if($stmt === false)
				{
					$this->LastError[] = "Prepare error";
					return false;
				}
				$params = array();
				$types  = array_reduce($vars, function ($string, &$arg) use (&$params) {
					$params[] = &$arg;
					if (is_float($arg))         $string .= 'd';
					elseif (is_integer($arg))   $string .= 'i';
					elseif (is_string($arg))    $string .= 's';
					else                        $string .= 'b';
					return $string;
				}, '');
				array_unshift($params , $types);
				array_unshift($params , $stmt);
				//@ call_user_func_array("mysqli_stmt_bind_param",$params);
				mysqli_stmt_bind_param($stmt,$types,...array_values($vars));
				if(! mysqli_stmt_execute($stmt))
					return false;
				if(function_exists('mysqli_stmt_get_result'))
					@ $result = mysqli_stmt_get_result($stmt);
				else
					@ $result = $this->mysqlo_stmt_get_result($stmt);
				@ mysqli_stmt_free_result($stmt);
				@ mysqli_free_result($result);
				mysqli_stmt_close($stmt);
				return $result;
			}
			else
			{
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	public function mysqlo_stmt_get_result( $Statement ) 
	{
		try{
			$RESULT = array();
			mysqli_stmt_store_result($Statement);
			for($i=0;$i<intval(mysqli_stmt_num_rows($Statement));$i++) {
				$Metadata = mysqli_stmt_result_metadata($Statement);
				$PARAMS = array();
				while($Field=mysqli_fetch_field($Metadata)) {
					$PARAMS[] = &$RESULT[$i][$Field->name];
				}
				call_user_func_array(array($Statement,'bind_result'),$PARAMS);
				mysqli_stmt_fetch($Statement);
			}
			return $RESULT;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}

	public function getLastError()
	{
		return $this->LastError;
	}
}
?>