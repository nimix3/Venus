<?php
// Config Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class Config
{
	protected $LastError;
	protected $Config;
	
	public function __construct($File='')
	{
		if(isset($File) and !empty($File))
		{
			$this->LoadConfig($File);
		}
	}
	
	public function LoadConfig($File='/config/config.php')
	{
		try{
			if(file_exists($File))
			{
				if(is_readable($File))
				{
					unset($Configurations);
					include_once($File);
					if(isset($Configurations) and !empty($Configurations))
					{
						$this->Config[basename($File)] = $Configurations;
						unset($Configurations);
						return $this->Config[basename($File)];
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
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return null;
		}
	}
	
	public function GetConfig($Handler='')
	{
		if(isset($Handler) and !empty($Handler))
		{
			if(isset($this->Config[$Handler]) and !empty($this->Config[$Handler]))
			{
				return $this->Config[$Handler];
			}
			else
			{
				return null;
			}
		}
		else
		{
			return $this->Config;
		}
	}
	
	public function SetConfig($Conf='',$Handler='')
	{
		if(isset($Handler) and !empty($Handler))
		{
			$this->Config[$Handler] = $Conf;
		}
		else
		{
			$this->Config = $Conf;
		}
		return $Conf;
	}
	
	public function GetHandlerList()
	{
		if(isset($this->Config) and !empty($this->Config))
		{
			if(is_array($this->Config))
			{
				$out = array();
				foreach($this->Config as $key => $val)
				{
					$out[$key] = $key;
				}
				return $out;
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
}
?>