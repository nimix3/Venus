<?php
// Plugin Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class Controller
{
	protected $Classes;
	protected $Locks;
	protected $Instances;
	protected $InstancesName;
	protected $Select;

    public function __construct($Instance=null,$Object=null) {
		$this->Instances = new \ArrayObject();
		$this->Select = new \stdClass();
		if(isset($Instance,$Object) and !empty($Instance) and !empty($Object))
			$this->setInstance($Instance,$Object);
    }
	
	public function registerClass($Class,$Alias,$Lock=false)
	{
		if(isset($Class,$Alias) and !empty($Class) and !empty($Alias))
		{
			if(is_string($Class) and is_string($Alias))
			{
				if(boolval($this->Locks[$Class]) == false)
				{
					$this->Locks[$Class] = boolval($Lock);
					$this->Classes[$Class] = $Alias;
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
	
	public function isLock($Class)
	{
		return boolval($this->Locks[$Class]);
	}
	
	public function checkClass($Class)
	{
		if(isset($this->Classes[$Class]) and !empty($this->Classes[$Class]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function getClassList()
	{
		if(isset($this->Classes) and !empty($this->Classes))
		{
			return $this->Classes;
		}
		else
		{
			return null;
		}
	}
	
	public function createClass($Class)
	{
		if(isset($this->Classes[$Class]) and !empty($this->Classes[$Class]))
		{
			$ClassAlias = $this->Classes[$Class];
			$Args = func_get_args();
			$Args = array_shift($Args);
			if(is_array($Args))
			{
				$reflector = new \ReflectionClass($ClassAlias);
				return $reflector->newInstanceArgs($Args);
			}
			else
			{
				return new $ClassAlias();
			}
		}
		else
		{
			return null;
		}
	}
	
	public function setInstance($Instance,$Object,$replace=true)
	{
		if(!isset($Instance,$Object) or empty($Instance) or empty($Object))
			return false;
		if(isset($this->Instances->$Instance) and !empty($this->Instances->$Instance) and !$replace)
		{
			return false;
		}
		else
		{
			if(is_callable($Object) or is_object($Object))
			{
				$this->Instances->$Instance = $Object;
				$this->InstancesName[$Instance] = $Object;
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
	public function getInstance($Instance)
	{
		if(isset($this->Instances->$Instance) and !empty($this->Instances->$Instance))
		{
			return $this->Instances->$Instance;
		}
		else
		{
			return null;
		}
	}
	
	public function getInstancesList()
	{
		if(isset($this->Instances) and !empty($this->Instances))
		{
			return $this->InstancesName;
		}
		else
		{
			return null;
		}
	}
	
	public function setDefault($Instance)
	{
		if(isset($this->Instances->$Instance) and !empty($this->Instances->$Instance))
		{
			return $this->Select = $this->Instances->$Instance;
		}
		else
		{
			return null;
		}
	}
	
	public function getDefault()
	{
		if(isset($this->Select) and !empty($this->Select))
		{
			return $this->Select;
		}
		else
		{
			return null;
		}
	}
	
	public function checkMethod($Instance,$method='')
	{
		if(isset($this->Instances->$Instance) and !empty($this->Instances->$Instance))
		{
			return method_exists($this->Instances->$Instance,$method);
		}
		else
		{
			return null;
		}
	}
	
	public function getInstanceClass($Instance)
	{
		if(isset($this->Instances->$Instance) and !empty($this->Instances->$Instance))
		{
			$class = get_parent_class($this->Instances->$Instance);
			if($class === false)
				return get_class($this->Instances->$Instance);
			else
				return $class;
		}
		else
		{
			return null;
		}
	}

    public function __call($method, $args) {
        return call_user_func_array(array($this->Select, $method), $args);
    }

    public function __get($key) {
        return $this->Select->$key;
    }

    public function __set($key, $val) {
        return $this->Select->$key = $val;
    }
}

class Pool
{
	public function __call($method, $arguments) {
        return call_user_func_array(\Closure::bind($this->$method, $this, get_called_class()), $arguments);
    }
}

class Plugin
{
	protected $LastError;
	protected $LoadedModule;
	protected $_Controller;
	protected $_Pool;
	protected $_curPlugName;
	protected $_curPlugDir;
	
	public function __construct($plugins=null)
	{
		$this->_Controller = new Controller();
		$this->_Pool = new Pool();
		if(isset($plugins) and !empty($plugins))
		{
			$this->LoadPlugins($plugins);
		}
	}
	
	public function GetPluginInfo($plg='')
	{
		if(isset($plg) and !empty($plg))
		{
			if(file_exists($plg) and is_dir($plg))
			{
				if(file_exists($plg."/info.db") and is_file($plg."/info.db"))
				{
					$arr = parse_ini_file($plg."/info.db");
					if(isset($arr['status']) and !empty($arr['status']))
						return $arr;
					else
						return null;
				}
				else
				{
					return null;
				}
			}
			else
			{
				return null;
			}
		}
		else
		{
			return null;
		}
	}
	
	public function __set($key, $val)
	{
		if($key == 'Controller' or $key == 'Pool' or $key == 'LoadedModule' or $key == 'LastError')
			return null;
		else
			return $this->$key = $val;
    }
	
	public function GetController()
	{
		return $this->_Controller;
	}
	
	public function Controller()
	{
		return $this->_Controller;
	}
	
	public function GetPool()
	{
		return $this->_Pool;
	}
	
	public function Pool()
	{
		return $this->_Pool;
	}
	
	protected function setCurrentPluginName($str)
	{
		if(isset($str) and !empty($str))
		{
			$this->_curPlugName = $str;
		}
	}
	
	protected function getOwnName()
	{
		if(isset($this->_curPlugName) and !empty($this->_curPlugName))
		{
			return $this->_curPlugName;
		}
		else
		{
			return null;
		}
	}
	
	protected function getOwnDirectory()
	{
		if(isset($this->_curPlugDir) and !empty($this->_curPlugDir))
		{
			return $this->_curPlugDir;
		}
		else
		{
			return null;
		}
	}
	
	protected function setCurrentPluginDirectory($str)
	{
		if(isset($str) and !empty($str))
		{
			$this->_curPlugDir = $str;
		}
	}
	
	public function LoadPlugins($dir='/',$loadtype='REQUIRE')
	{
		if(!isset($dir) or empty($dir))
			return false;
		if(!is_dir($dir))
			return false;
		$calls = 0;
		$LoadedModule = array();
		$rootdir = array_slice(scandir($dir), 2);
		if(isset($rootdir) and !empty($rootdir))
		{
			if(is_array($rootdir))
			{
				foreach($rootdir as $itemdir)
				{
					$itemdir = $dir."/".$itemdir;
					if(file_exists($itemdir."/info.db") and is_file($itemdir."/info.db"))
					{
						$arr = parse_ini_file($itemdir."/info.db");
						if(isset($arr['status']) and !empty($arr['status']))
						{
							if(strtolower($arr['status']) != "active")
							{
								continue;
							}
							else
							{
								$this->setCurrentPluginName($arr['name']);
								$LoadedModule[basename($itemdir)] = $arr['name'];
							}
						}
						else
						{
							continue;
						}
					}
					else
					{
						continue;
					}
					$all1 = array_slice(scandir($itemdir."/class"), 2);
					if(is_array($all1))
						array_walk($all1, function(&$value, $key, $itemdir) { $value = basename($itemdir).'/class/'.$value; }, $itemdir);
					$all2 = array_slice(scandir($itemdir."/lib"), 2);
					if(is_array($all2))
						array_walk($all2, function(&$value, $key, $itemdir) { $value = basename($itemdir).'/lib/'.$value; }, $itemdir);
					if(is_array($all1) and is_array($all2))
						@ $allhere = array_merge($all1,$all2);
					else if(is_array($all1))
						@ $allhere = $all1;
					else
						@ $allhere = $all2;
					if(isset($allhere) and !empty($allhere))
					{
						if(is_array($allhere))
						{
							foreach($allhere as $element)
							{
								$element = $dir."/".$element;
								$this->setCurrentPluginDirectory($element);
								if(is_file($element) and file_exists($element))
								{
									if(pathinfo($element)['extension'] == 'php')
									{
										try{
											if($loadtype == 'REQUIRE')
												require_once($element);
											else
												include_once($element);
											$calls++;
										}
										catch(Exception $ex){
											$this->LastError[] = $ex->getMessage();
										}
									}
								}
							}
						}
						else
						{
							continue;
						}
					}
					else
					{
						continue;
					}
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
		if($calls)
			return true;
		else
			return false;
	}
	
	public function PluginsList($dir='/')
	{
		if(!isset($dir) or empty($dir))
			return null;
		if(!is_dir($dir))
			return null;
		$ListModule = array();
		$rootdir = array_slice(scandir($dir), 2);
		if(isset($rootdir) and !empty($rootdir))
		{
			if(is_array($rootdir))
			{
				foreach($rootdir as $itemdir)
				{
					$itemdir = $dir."/".$itemdir;
					if(file_exists($itemdir."/info.db") and is_file($itemdir."/info.db"))
					{
						$arr = parse_ini_file($itemdir."/info.db");
						if(isset($arr['status']) and !empty($arr['status']))
						{
							$ListModule[basename($itemdir)] = $arr['name'];
						}
						else
						{
							continue;
						}
					}
					else
					{
						continue;
					}
				}
			}
			else
			{
				return null;
			}
		}
		else
		{
			return null;
		}
		return $ListModule;
	}
	
	public function CheckPoolMethod($method='')
	{
		return method_exists($this->Pool,$method);
	}
	
	public function InstallPlugin($dir='/',$pln='')
	{
		$itemdir = $dir.'/'.$pln;
		if(file_exists($itemdir."/info.db") and is_file($itemdir."/info.db"))
		{
			$arr = parse_ini_file($itemdir."/info.db");
			if(isset($arr['status']) and !empty($arr['status']))
			{
				if(file_exists($itemdir."/setup/install.php") and is_file($itemdir."/setup/install.php"))
				{
					$this->setCurrentPluginDirectory($itemdir);
					$this->setCurrentPluginName($arr['name']);
					try{
						require_once($itemdir."/setup/install.php");
						return true;
					}
					catch(Exception $ex)
					{
						$this->LastError[] = $ex->getMessage();
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
		else
		{
			return false;
		}
	}
	
	public function UninstallPlugin($dir='/',$pln='')
	{
		$itemdir = $dir.'/'.$pln;
		if(file_exists($itemdir."/info.db") and is_file($itemdir."/info.db"))
		{
			$arr = parse_ini_file($itemdir."/info.db");
			if(isset($arr['status']) and !empty($arr['status']))
			{
				if(file_exists($itemdir."/setup/uninstall.php") and is_file($itemdir."/setup/uninstall.php"))
				{
					$this->setCurrentPluginDirectory($itemdir);
					$this->setCurrentPluginName($arr['name']);
					try{
						require_once($itemdir."/setup/uninstall.php");
						return true;
					}
					catch(Exception $ex)
					{
						$this->LastError[] = $ex->getMessage();
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
		else
		{
			return false;
		}
	}
	
	public function EnablePlugin($dir='/',$pln='')
	{
		$itemdir = $dir.'/'.$pln;
		if(file_exists($itemdir."/info.db") and is_file($itemdir."/info.db"))
		{
			$arr = parse_ini_file($itemdir."/info.db");
			if(isset($arr['status']) and !empty($arr['status']))
			{
				$arr['status'] = 'active';
				try{
					$this->write_ini_file($arr, $itemdir."/info.db");
				}
				catch(Exception $ex)
				{
					$this->LastError[] = $ex->getMessage();
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
	
	public function DisablePlugin($dir='/',$pln='')
	{
		$itemdir = $dir.'/'.$pln;
		if(file_exists($itemdir."/info.db") and is_file($itemdir."/info.db"))
		{
			$arr = parse_ini_file($itemdir."/info.db");
			if(isset($arr['status']) and !empty($arr['status']))
			{
				$arr['status'] = 'inactive';
				try{
					$this->write_ini_file($arr, $itemdir."/info.db");
				}
				catch(Exception $ex)
				{
					$this->LastError[] = $ex->getMessage();
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
	
	public function GetPoolMethods()
	{
		$methods = array();
		$methods = get_class_methods($this->Pool);
		return $methods;
	}
	
	private function write_ini_file($array, $file)
	{
		$res = array();
		foreach($array as $key => $val)
		{
			if(is_array($val))
			{
				$res[] = "[$key]";
				foreach($val as $skey => $sval) $res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
			}
			else $res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
		}
		$this->safefilerewrite($file, implode("\r\n", $res));
	}
	
	private function safefilerewrite($fileName, $dataToSave)
	{    if ($fp = fopen($fileName, 'w'))
		{
			$startTime = microtime(TRUE);
			do
			{            $canWrite = flock($fp, LOCK_EX);
			if(!$canWrite) usleep(round(rand(0, 100)*1000));
			} while ((!$canWrite)and((microtime(TRUE)-$startTime) < 5));
			if ($canWrite)
			{            fwrite($fp, $dataToSave);
				flock($fp, LOCK_UN);
			}
			fclose($fp);
		}
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}