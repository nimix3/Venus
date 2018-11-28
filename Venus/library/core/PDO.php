<?php
// PDO Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class PDO
{
	protected $DBtype;
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
			$this->DBtype = $SQLConfig['db'];
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
			$this->DBtype = $SQLConfig['db'];
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
	
	public function InitDB($DBtype='mysql',$DBserver=null,$DBuser=null,$DBpass=null,$DBname=null,$DBport=null,$charset='')
	{
		try{
			if(!isset($DBtype) or empty($DBtype))
				$DBtype = $this->DBtype;
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
			$DBOption = [
				\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
				\PDO::ATTR_EMULATE_PREPARES   => false,
			];
			if(!isset($charset) or empty($charset))
				$charset = $this->DBcharset;
			if(!isset($charset) or empty($charset))
				$charset = 'utf8';
			$DBdsn = $DBtype.':host='.$DBserver.'; dbname='.$DBname.'; charset='.$charset;
			try {
				$__DBh = new \PDO($DBdsn, $DBuser, $DBpass, $DBOption);
				$this->DBobj = $__DBh;
				@ $this->DBobj->query("SET NAMES ".$charset);
				return true;
			}
			catch (Exception $e) {
				$this->LastError[] = $ex->getMessage();
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function InitDBManual($DBdsn='',$DBuser=null,$DBpass=null)
	{
		try{
			if(!isset($DBuser) or empty($DBuser))
				$DBuser = $this->DBuser;
			if(!isset($DBpass) or empty($DBpass))
				$DBpass = $this->DBpass;
			$DBOption = [
				\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
				\PDO::ATTR_EMULATE_PREPARES   => false,
			];
			if(!isset($DBdsn) or empty($DBdsn))
				return false;
			try {
				$__DBh = new \PDO($DBdsn, $DBuser, $DBpass, $DBOption);
				$this->DBobj = $__DBh;
				return true;
			}
			catch (Exception $e) {
				$this->LastError[] = $ex->getMessage();
				return false;
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
		try{
			if(!isset($DBtype) or empty($DBtype))
				$DBtype = $this->DBtype;
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
			$DBOption = [
				\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
				\PDO::ATTR_PERSISTENT 		  => true,
				\PDO::ATTR_EMULATE_PREPARES   => false,
			];
			$DBdsn = $DBtype.':host='.$DBserver.';dbname='.$DBname.';charset='.$charset;
			try {
				$__DBh = new \PDO($DBdsn, $DBuser, $DBpass, $DBOption);
				$this->DBobj = $__DBh;
				if(!isset($charset) or empty($charset))
					$charset = $this->DBcharset;
				@ $this->DBobj->query("SET NAMES ".$charset);
				return true;
			}
			catch (Exception $e) {
				$this->LastError[] = $ex->getMessage();
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function InitAliveDBManual($DBdsn='',$DBuser=null,$DBpass=null)
	{
		try{
			if(!isset($DBuser) or empty($DBuser))
				$DBuser = $this->DBuser;
			if(!isset($DBpass) or empty($DBpass))
				$DBpass = $this->DBpass;
			$DBOption = [
				\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
				\PDO::ATTR_PERSISTENT 		  => true,
				\PDO::ATTR_EMULATE_PREPARES   => false,
			];
			if(!isset($DBdsn) or empty($DBdsn))
				return false;
			try {
				$__DBh = new \PDO($DBdsn, $DBuser, $DBpass, $DBOption);
				$this->DBobj = $__DBh;
				return true;
			}
			catch (Exception $e) {
				$this->LastError[] = $ex->getMessage();
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function LastInsertId($name=null)
	{
		try{
			if(!$this->DBobj)
				return false;
			return $this->DBobj->lastInsertId($name);
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
				/*
				if(!$this->DBobj)
					return false;
				$Status = $this->DBobj->getAttribute(\PDO::ATTR_CONNECTION_STATUS);
				if(isset($Status) and !empty($Status))
					return true;
				else
					$Status = \PDO::getAttribute(\PDO::ATTR_SERVER_INFO);
				if(isset($Status) and !empty($Status))
					return true;
				*/
				return boolval($this->DBobj->query('SELECT 1+1'));
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
			return false;
		}
	}
	
	public function KillDB()
	{
		try{	
			if(!isset($this->DBobj) or empty($this->DBobj))
				return false;
			unset($this->DBobj);
				return true;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function RecordDB()
	{
		try{
			if(!isset($this->DBobj) or empty($this->DBobj))
				return false;
			$this->DBobj->beginTransaction();
				return true;
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
			$this->DBobj->commit();
				return true;
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
			if(!isset($this->DBobj) or empty($this->DBobj))
				return false;
			$this->DBobj->rollBack();
				return true;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function CloseDB()
	{
		try{
			if(isset($this->DBobj) and !empty($this->DBobj))
			{
				@ $this->DBobj = null;
				unset($this->DBobj);
				return true;
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
				return $this->DBobj->errorInfo();
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
			if(isset($this->DBQuery) and !empty($this->DBQuery))
			{
				$str = $this->DBobj->quote($str);
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
				@ $result = $this->DBobj->query($sql);
				if(!isset($result) or empty($result) or is_bool($result))
					return null;
				if($result->rowCount() <= 0)
					return null;
				while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
					$res[] = $row;
				}
				$result->closeCursor();
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
				$stmt = $this->DBobj->prepare($sql);
				foreach($vars as $param => $var)
				{
					if(is_numeric($param))
						$param++;
					@ $stmt->bindValue($param, $var, $this->GetType($var));
				}
				if(!$stmt->execute())
					return null;
				if($stmt->rowCount() <= 0)
					return null;
				while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
					$res[] = $row;
				}
				$stmt->closeCursor();
				unset($stmt);
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
				@ $result = $this->DBobj->query($sql);
				if(!isset($result) or empty($result))
					return false;
				if($result->rowCount() <= 0)
					return false;
				else
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
				$stmt = $this->DBobj->prepare($sql);
				if(is_array($data))
				{
					$vars = array_merge($data,$vars);
				}
				$Cc = 1;
				foreach($vars as $param => $var)
				{
					@ $stmt->bindValue($Cc, $var, $this->GetType($var));
					$Cc++;
				}
				$Cc = 1;
				$stmt->execute();
				if($stmt->rowCount() <= 0)
				{
					$stmt->closeCursor();
					unset($stmt);
					return false;
				}
				else
				{
					$stmt->closeCursor();
					unset($stmt);
					return true;
				}
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
				@ $result = $this->DBobj->query($sql);
				if(!isset($result) or empty($result) or is_bool($result))
					return false;
				if($result->rowCount() <= 0)
				{
					return false;
				}
				else
				{
					$result->closeCursor();
					return true;
				}
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
				$stmt = $this->DBobj->prepare($sql);
				$Cc = 1;
				foreach($vars as $param => $var)
				{
					@ $stmt->bindValue($Cc, $var, $this->GetType($var));
					$Cc++;
				}
				$Cc = 1;
				$stmt->execute();
				if($stmt->rowCount() <= 0)
				{
					$stmt->closeCursor();
					unset($stmt);
					return false;
				}
				else
				{
					$stmt->closeCursor();
					unset($stmt);
					return true;
				}
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
				@ $result = $this->DBobj->query($sql);
				if(!isset($result) or empty($result) or is_bool($result))
					return false;
				if($result->rowCount() <= 0)
				{
					return false;
				}
				else
				{
					$result->closeCursor();
					return true;
				}
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
				$stmt = $this->DBobj->prepare($sql);
				$Cc = 1;
				foreach($vars as $param => $var)
				{
					@ $stmt->bindValue($Cc, $var, $this->GetType($var));
					$Cc++;
				}
				$Cc = 1;
				$stmt->execute();
				if($stmt->rowCount() <= 0)
				{
					$stmt->closeCursor();
					unset($stmt);
					return false;
				}
				else
				{
					$stmt->closeCursor();
					unset($stmt);
					return true;
				}
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
				@ $result = $this->DBobj->query($sql);
				if(!isset($result) or empty($result) or is_bool($result))
					return false;
				if($result->rowCount() <= 0)
				{
					return false;
				}
				else
				{
					$result->closeCursor();
					return true;
				}
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
				$stmt = $this->DBobj->prepare($sql);
				$Cc = 1;
				foreach($vars as $param => $var)
				{
					@ $stmt->bindValue($Cc, $var, $this->GetType($var));
					$Cc++;
				}
				$Cc = 1;
				$stmt->execute();
				if($stmt->rowCount() <= 0)
				{
					$stmt->closeCursor();
					unset($stmt);
					return false;
				}
				else
				{
					$stmt->closeCursor();
					unset($stmt);
					return true;
				}
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

	public function TableExistDB($table)
	{
		try{
			$sql = "DESCRIBE `".$table."`";
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				@ $result = $this->DBobj->query($sql);
				if(!isset($result) or empty($result) or is_bool($result))
					return false;
				if($result->rowCount() <= 0)
				{
					return false;
				}
				else
				{
					$result->closeCursor();
					return true;
				}
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

	public function ClearTableDB($table)
	{
		try{
			$sql = "TRUNCATE TABLE `".$table."`";
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				@ $result = $this->DBobj->query($sql);
				if(!isset($result) or empty($result) or is_bool($result))
					return false;
				if($result->rowCount() <= 0)
				{
					return false;
				}
				else
				{
					$result->closeCursor();
					return true;
				}
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

	public function ExecDB($exec)
	{
		try{
			$sql = $exec;
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				@ $result = $this->DBobj->query($sql);
				return $result->fetchAll();
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
	
	public function ExecMultiDB($exec)
	{
		try{
			$sql = $exec;
			$this->DBQuery = $sql;
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				@ $result = $this->DBobj->exec($sql);
				return $result;
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

	public function ExecDBsecure($exec,$vars)
	{
		try{
			$sql = $exec;
			$this->DBQuery = $sql;
			if(!is_array($vars))
				$vars = array($vars);
			if(!$this->DBobj)
				$this->InitDB();
			if($this->DBobj)
			{
				$stmt = $this->DBobj->prepare($sql);
				$Cc = 1;
				foreach($vars as $param => $var)
				{
					@ $stmt->bindValue($Cc, $var, $this->GetType($var));
					$Cc++;
				}
				$Cc = 1;
				$stmt->execute();
				return $stmt->fetchAll();
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
	
	protected function GetType($Var=null)
	{
		$Types = array('boolean'=>\PDO::PARAM_BOOL,'integer'=>\PDO::PARAM_INT,'string'=>\PDO::PARAM_STR,'NULL'=>\PDO::PARAM_NULL);
		$Type = gettype($Var);
		if(isset($Types[$Type]) and !empty($Types[$Type]))
		{
			return $Types[$Type];
		}
		else
		{
			return \PDO::PARAM_STR;
		}
	}

	public function getLastError()
	{
		return $this->LastError;
	}
}
?>