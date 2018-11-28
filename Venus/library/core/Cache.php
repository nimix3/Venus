<?php
// Cache Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class Cache
{
	public $DBCache;
	public $FileCache;
	public $MemCache;
	public $SharedMemory;
	
	public function __construct($new=false)
	{
		if($new)
		{
			$this->DBCache = new DBCache();
			$this->FileCache= new FileCache();
			$this->MemCache = new MemCache();
			$this->SharedMemory = new SharedMemory();
		}
	}
}

class DBCache
{
	protected $LastError;
	protected $DBtype;
	protected $DBserver;
	protected $DBname;
	protected $DBuser;
	protected $DBpass;
	protected $DBport;
	protected $DBcharset;
	protected $DBobj;
	protected $CacheTable = 'Cache';
	
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
		return $this->CloseCache();
	}
	
	protected function CloseDB()
	{
		try{
			if(isset($this->DBobj) and !empty($this->DBobj))
			{
				@ $this->DBobj = null;
				@ unset($this->DBobj);
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
	
	public function CloseCache()
	{
		return $this->CloseDB();
	}
	
	public function InitCache($DBtype='mysql',$DBserver=null,$DBuser=null,$DBpass=null,$DBname=null,$DBport=null,$charset='',$CacheTable='Cache')
	{
		try{
			if($this->InitDB($DBtype,$DBserver,$DBuser,$DBpass,$DBname,$DBport,$charset))
			{
				$this->CacheTable = $CacheTable;
				@ $this->ExecDB('CREATE TABLE IF NOT EXISTS `'.$this->CacheTable.'` ( `key` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL , `value` VARCHAR(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL , `expire` VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL , PRIMARY KEY (`key`)) ENGINE = MEMORY CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;');
				return true;
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
	
	public function AddItem($Key,$Value,$Exp=0)
	{
		try{
			if(!isset($this->DBobj) or empty($this->DBobj))
			{
				return false;
			}
			if($this->DBobj->InsertDBsecure($this->CacheTable,array('key'=>$Key,'value'=>$Value,'expire'=>intval($Exp))))
			{
				return true;
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
	
	public function SetItem($Key,$Value,$Exp=0)
	{
		try{
			if(!isset($this->DBobj) or empty($this->DBobj))
			{
				return false;
			}
			if($this->DBobj->ReplaceDBsecure($this->CacheTable,array('key'=>$Key,'value'=>$Value,'expire'=>intval($Exp))))
			{
				return true;
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
	
	public function ReplaceItem($Key,$Value,$Exp=0)
	{
		return $this->SetItem($Key,$Value,$Exp);
	}
	
	public function FlushCache()
	{
		try{
			if(!isset($this->DBobj) or empty($this->DBobj))
			{
				return false;
			}
			if($this->DBobj->DeleteDBsecure($this->CacheTable,'1','','','',9999999))
			{
				return true;
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
	
	public function RemoveItem($Key)
	{
		try{
			if(!isset($this->DBobj) or empty($this->DBobj))
			{
				return false;
			}
			if($this->DBobj->DeleteDBsecure($this->CacheTable,'key','=','?',array($Key)))
			{
				return true;
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
	
	public function GetItemsAsKey()
	{
		try{
			if(!isset($this->DBobj) or empty($this->DBobj))
			{
				return false;
			}
			$Res = $this->DBobj->SelectDBsecure('*',$this->CacheTable,'1','','','',9999999);
			if(isset($Res[0]) and !empty($Res[0]))
			{
				if(is_array($Res))
				{
					$Keys = array();
					foreach($Res as $Item)
					{
						$Keys[] = $Item['key'];
					}
					return $Keys;
				}
				else
				{
					return false;
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
	
	public function GetItem($Key)
	{
		try{
			if(!isset($this->DBobj) or empty($this->DBobj))
			{
				return false;
			}
			$Res = $this->DBobj->SelectDBsecure('*',$this->CacheTable,'key','=','?',array($Key));
			if(isset($Res[0]) and !empty($Res[0]))
			{
				if(intval($Res[0]['value']['expire']) < time() and intval($Res[0]['value']['expire']) > 1)
				{
					$this->RemoveItem($Key);
					return false;
				}
				else
				{
					return $Res[0]['value'];
				}
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
	
	public function IncrementItem($Key,$Offset=1,$Exp=0)
	{
		try{
			$Res = $this->GetItem($Key);
			if(isset($Res) and !empty($Res))
			{
				if(is_numeric($Res))
				{
					$Res += $Offset;
					if($this->SetItem($Key,intval($Res),$Exp))
					{
						return intval($Res);
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
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
	
	public function DecrementItem($Key,$Offset=1,$Exp=0)
	{
		try{
			$Res = $this->GetItem($Key);
			if(isset($Res) and !empty($Res))
			{
				if(is_numeric($Res))
				{
					$Res -= $Offset;
					if($this->SetItem($Key,intval($Res),$Exp))
					{
						return intval($Res);
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
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


class SharedMemory
{
	protected $LastError;
	
	public function SetItem($ID,$Data)
	{
		try{
			@ $shmobj = shmop_open(intval($ID), "a", 0, 0);
			@ shmop_delete($shmobj);
			@ shmop_close($shmobj);
			$shmobj = shmop_open(intval($ID), "c", 0644, strlen(serialize($Data)));
			if($shmobj === false)
			{
				return false;
			}
			else
			{
				$Res = shmop_write($shmobj, serialize($Data), 0);
				shmop_close($shmobj);
				return boolval($Res);
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function GetItem($ID)
	{
		try{
			$shmobj = shmop_open(intval($ID), "a", 0, 0);
			if($shmobj === false)
			{
				return false;
			}
			else
			{
				$Data = shmop_read($shmobj, 0, shmop_size($shmobj));
				if($Data === false)
				{
					shmop_close($shmobj);
					return null;
				}
				else
				{
					shmop_close($shmobj);
					$Res = unserialize($Data);
					return $Res;
				}
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function RemoveItem($ID)
	{
		try{
			$shmobj = shmop_open(intval($ID), "a", 0, 0);
			if($shmobj === false)
			{
				return false;
			}
			else
			{
				shmop_delete($shmobj);
				shmop_close($shmobj);
				return true;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}


class FileCache
{
	protected $LastError;
	protected $CacheObj;
	protected $CacheFile;
	
	public function __construct($File)
	{
		if(isset($File) and !empty($File))
		{
			$this->InitCache($File);
		}
	}
	
	public function InitCache($File)
	{
		try{
			if(!file_exists($File))
				return false;
			$this->CacheFile = $File;
			$Data = @file_get_contents($File);
			if($Data === false)
				return false;
			$Data = $this->unmask($Data);
			$this->CacheObj = unserialize($Data);
			return true;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	private function unmask($input)
	{
		try{
			$pos = strpos($input, PHP_EOL);
			if ($pos === false)
				return $input;
			return substr($input, $pos + 1);
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function SaveCache($File=null)
	{
		try{
			if(!isset($File) or empty($File))
				$File = $this->CacheFile;
			$res = file_put_contents($File,'<?php die(); ?>'.PHP_EOL.serialize($this->CacheObj),LOCK_EX);
			if($res === false)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function AddItem($Key,$Value,$Exp=0)
	{
		try{
			if(isset($Key,$Value) and !empty($Key))
			{
				if(isset($this->CacheObj[$Key]) and !empty($this->CacheObj[$Key]))
					return false;
				$this->CacheObj[$Key] = array('value'=>$Value,'expire'=>intval($Exp));
				if($this->SaveCache())
					return true;
				else
					return false;
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
	
	public function ReplaceItem($Key,$Value,$Exp=0)
	{
		try{
			if(isset($Key,$Value) and !empty($Key))
			{
				$this->CacheObj[$Key] = array('value'=>$Value,'expire'=>intval($Exp));
				if($this->SaveCache())
					return true;
				else
					return false;
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
	
	public function SetItem($Key,$Value,$Exp=0)
	{
		try{
			if(isset($Key,$Value) and !empty($Key))
			{
				$this->CacheObj[$Key] = array('value'=>$Value,'expire'=>intval($Exp));
				if($this->SaveCache())
					return true;
				else
					return false;
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
	
	public function FlushCache()
	{
		try{
			$this->CacheObj = null;
			if($this->SaveCache())
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
	
	public function RemoveItem($Key)
	{
		try{
			if(!isset($this->CacheObj[$Key]) or empty($this->CacheObj[$Key]))
				return false;
			unset($this->CacheObj[$Key]);
			if($this->SaveCache())
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
	
	public function GetItem($Key)
	{
		try{
			if(!isset($this->CacheObj[$Key]) or empty($this->CacheObj[$Key]))
				return false;
			if(intval($this->CacheObj[$Key]['expire']) < time())
			{
				$this->RemoveItem($Key);
				return false;
			}
			else
			{
				return $this->CacheObj[$Key]['value'];
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function IncrementItem($Key,$Offset=1)
	{
		try{
			if(!isset($this->CacheObj[$Key]) or empty($this->CacheObj[$Key]))
				return false;
			if(intval($this->CacheObj[$Key]['expire']) < time() and intval($this->CacheObj[$Key]['expire']) > 0)
			{
				$this->RemoveItem($Key);
				return false;
			}
			else
			{
				if(is_numeric(CacheObj[$Key]['value']))
				{
					if($this->SetItem($Key,$this->CacheObj[$Key]['value']+$Offset))
						return $this->CacheObj[$Key]['value'];
					else
						return false;
				}
				else
				{
					return false;
				}
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function DecrementItem($Key,$Offset=1)
	{
		try{
			if(!isset($this->CacheObj[$Key]) or empty($this->CacheObj[$Key]))
				return false;
			if(intval($this->CacheObj[$Key]['expire']) < time() and intval($this->CacheObj[$Key]['expire']) > 0)
			{
				$this->RemoveItem($Key);
				return false;
			}
			else
			{
				if(is_numeric(CacheObj[$Key]['value']))
				{
					if($this->SetItem($Key,$this->CacheObj[$Key]['value']-$Offset))
						return $this->CacheObj[$Key]['value'];
					else
						return false;
				}
				else
				{
					return false;
				}
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function GetItemsAsKey()
	{
		try{
			if(isset($this->CacheObj) and !empty($this->CacheObj))
			{
				if(is_array($this->CacheObj))
				{
					$Res = array();
					foreach($this->CacheObj as $Key => $Val)
					{
						$Res[] = $Key;
					}
					return $Res;
				}
			}
			return null;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function CloseCache()
	{
		if($this->SaveCache())
			return true;
		else
			return false;
	}
	
	public function __destruct()
	{
		return $this->CloseCache();
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}


class MemCache
{
	protected $LastError;
	protected $CacheObj;
	
	public function __construct($Host,$Port,$Auth=null)
	{
		if(isset($Host,$Port) and !empty($Host) and !empty($Port))
		{
			$this->InitCache($Host,$Port,$Auth);
		}
	}

	public function InitCache($Host,$Port,$Auth=null)
	{
		try{
			$this->CacheObj = new \Memcached();
			if($this->CacheObj->addServer($Host, $Port))
			{
				$this->CacheObj->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
				if(isset($Auth) and !empty($Auth))
				{
					if(is_array($Auth))
						$this->CacheObj->setSaslAuthData($Auth['username'],$Auth['password']);
				}
				return true;
			}
			else
			{
				$this->LastError[] = $this->CacheObj->getResultCode();
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function FlushCache()
	{
		try{
			if(!isset($this->CacheObj) or empty($this->CacheObj))
			{
				return false;
			}
			if($this->CacheObj->flush())
			{
				return true;
			}
			else
			{
				$this->LastError[] = $this->CacheObj->getResultCode();
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function AddCacheServer($Servers)
	{
		try{
			if(!isset($this->CacheObj) or empty($this->CacheObj))
			{
				return false;
			}
			if($this->CacheObj->addServers($Servers))
			{
				return true;
			}
			else
			{
				$this->LastError[] = $this->CacheObj->getResultCode();
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function AddItem($Key,$Value,$Exp=0)
	{
		try{
			if(!isset($this->CacheObj) or empty($this->CacheObj))
			{
				return false;
			}
			if($this->CacheObj->add($Key,$Value,$Exp))
			{
				return true;
			}
			else
			{
				$this->LastError[] = $this->CacheObj->getResultCode();
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function ReplaceItem($Key,$Value,$Exp=0)
	{
		try{
			if(!isset($this->CacheObj) or empty($this->CacheObj))
			{
				return false;
			}
			if($this->CacheObj->replace($Key,$Value,$Exp))
			{
				return true;
			}
			else
			{
				$this->LastError[] = $this->CacheObj->getResultCode();
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function SetItem($Key,$Value,$Exp=0)
	{
		try{
			if(!isset($this->CacheObj) or empty($this->CacheObj))
			{
				return false;
			}
			if($this->CacheObj->set($Key,$Value,$Exp))
			{
				return true;
			}
			else
			{
				$this->LastError[] = $this->CacheObj->getResultCode();
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function SetMultiItems($Items,$Exp=0)
	{
		try{
			if(!isset($this->CacheObj) or empty($this->CacheObj))
			{
				return false;
			}
			if($this->CacheObj->setMulti($Items,$Exp))
			{
				return true;
			}
			else
			{
				$this->LastError[] = $this->CacheObj->getResultCode();
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function GetItem($Key,$Exp=0)
	{
		try{
			if(!isset($this->CacheObj) or empty($this->CacheObj))
			{
				return false;
			}
			if($res = $this->CacheObj->get($Key))
			{
				return $res;
			}
			else
			{
				$this->LastError[] = $this->CacheObj->getResultCode();
				return null;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function RemoveItem($Key,$Exp=0)
	{
		try{
			if(!isset($this->CacheObj) or empty($this->CacheObj))
			{
				return false;
			}
			if($res = $this->CacheObj->delete($Key))
			{
				return $res;
			}
			else
			{
				$this->LastError[] = $this->CacheObj->getResultCode();
				return null;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function GetItemsAsKey()
	{
		try{
			if(!isset($this->CacheObj) or empty($this->CacheObj))
			{
				return false;
			}
			if($res = $this->CacheObj->getAllKeys())
			{
				return $res;
			}
			else
			{
				$this->LastError[] = $this->CacheObj->getResultCode();
				return null;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function DecrementItem($Key,$Offset=1)
	{
		try{
			if(!isset($this->CacheObj) or empty($this->CacheObj))
			{
				return false;
			}
			if($res = $this->CacheObj->decrement($Key,$Offset))
			{
				return $res;
			}
			else
			{
				$this->LastError[] = $this->CacheObj->getResultCode();
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function IncrementItem($Key,$Offset=1)
	{
		try{
			if(!isset($this->CacheObj) or empty($this->CacheObj))
			{
				return false;
			}
			if($res = $this->CacheObj->increment($Key,$Offset))
			{
				return $res;
			}
			else
			{
				$this->LastError[] = $this->CacheObj->getResultCode();
				return false;
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function CloseCache()
	{
		return $this->CacheObj->quit();
	}
	
	public function __destruct()
	{
		return $this->CloseCache();
	}

	public function getLastError()
	{
		return $this->LastError;
	}
}
?>