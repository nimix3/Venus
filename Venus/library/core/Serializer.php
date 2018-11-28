<?php
// Serializer Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class Serializer
{
	protected $LastError;
	protected $CWD;
	
	public function __construct()
	{
		$this->CWD = getcwd();
	}
	
	public function RegisterShadow()
	{
		try{
			register_shutdown_function(array($this, "_ShadowSave"));
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function SaveShadow()
	{
		try{
			return $this->_ShadowSave();
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function _ShadowSave()
	{
		try{
			global $GLOBALS;
			$ShadowFile = $this->CWD.'/'.basename($_SERVER["SCRIPT_FILENAME"]);
			$Consts = get_defined_constants();
			file_put_contents($ShadowFile.".consts",$this->PHPSerialize($Consts));
			$Vars = $GLOBALS;
			$Vars["GLOBALS"]["GLOBALS"] = null;
			file_put_contents($ShadowFile.".vars",$this->PHPSerialize($Vars));
			return true;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function RecoverShadow($ShadowFile='',$exec=true,$delete=true)
	{
		try{
			global $GLOBALS;
			if(!isset($ShadowFile) or empty($ShadowFile))
				$ShadowFile = $this->CWD.'/'.basename($_SERVER["SCRIPT_FILENAME"]);
			if(file_exists($ShadowFile.".vars"))
			{
				$this->Vars = $this->PHPDeserialize(file_get_contents($ShadowFile.".vars"));
			}
			if(file_exists($ShadowFile.".consts"))
			{
				$this->Consts = $this->PHPDeserialize(file_get_contents($ShadowFile.".consts"));
			}
			if(boolval($exec))
			{
				foreach($this->Vars as $Var => $Val)
				{
					try{
						global $$Var;
						$$Var = $Val;
					}
					catch(Exception $ex)
					{
						continue;
					}
				}
				foreach($this->Consts as $Const => $Value)
				{
					try{
						define($Const,$Value);
					}
					catch(Exception $ex)
					{
						continue;
					}
				}
			}
			if(boolval($delete))
			{
				@ unlink($ShadowFile.".vars");
				@ unlink($ShadowFile.".consts");
			}
			return true;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function PHPSerialize($Object)
	{
		try{
			if(is_resource($Object))
				return null;
			return base64_encode(serialize($Object));
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function PHPDeserialize($Object)
	{
		try{
			@ $Object = base64_decode($Object);
			if(empty($Object))
				return null;
			return Unserialize($Object);
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function SerializeObject($Object)
	{
		try{
			if(is_array($Object) or is_object($Object))
			{
				$Object = json_encode($Object,JSON_UNESCAPED_UNICODE);
			}
			return base64_encode($Object);
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return NULL;
		}
	}
	
	public function DeserializeObject($Object)
	{
		try{
			@ $Object = base64_decode($Object);
			if(empty($Object))
				return null;
			$DeSe = json_decode($Object,true);
			if($DeSe === null)
			{
				return $Object;
			}
			return $DeSe;
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