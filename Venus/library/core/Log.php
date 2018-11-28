<?php
// Log Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class Log
{
	protected $LastError;
	protected $LastErrors;
	protected $Logdir;
	protected $loglimit;
	
	public function __construct($LogOptions)
	{
		if(isset($LogOptions['logdir']) and !empty($LogOptions['logdir']))
		{
			$this->Logdir = $LogOptions['logdir'];
		}
		else
		{
			$this->Logdir = '/Logs';
		}
		if(!is_dir($this->Logdir) or !file_exists($this->Logdir))
			@ mkdir($this->Logdir,0755);
	}
	
	public function LogMessage($errcode,$errtype,$errlocation="none",$errmessage)
	{
		try{
			if(isset($errcode) and !empty($errcode) and isset($errtype) and !empty($errtype) and isset($errmessage) and !empty($errmessage) and isset($errlocation) and !empty($errlocation))
			{
				if(is_array($errmessage))
					$errmessage = json_encode($errmessage);
				$error = array('ErrorType'=>$errtype,'ErrorCode'=>$errcode,'ErrorLocation'=>$errlocation,'ErrorMessage'=>$errmessage,'ErrorTime'=>time());
				if(file_put_contents($this->Logdir."/".date("Ymd").".txt",json_encode($error),FILE_APPEND) !== false)
				{
					$LastError = json_encode($error);
					$LastErrors[] = json_encode($error);
					return true;
				}
				else
					return false;
			}
			return false;
		}
		catch(Exception $ex)
		{
			return false;
		}
	}
	
	public function LogError($errcode,$errtype,$errlocation="none",$errmessage)
	{
		try{
			if(isset($errcode) and !empty($errcode) and isset($errtype) and !empty($errtype) and isset($errmessage) and !empty($errmessage) and isset($errlocation) and !empty($errlocation))
			{
				if(is_array($errmessage))
					$errmessage = json_encode($errmessage);
				$error = array('ErrorType'=>$errtype,'ErrorCode'=>$errcode,'ErrorLocation'=>$errlocation,'ErrorMessage'=>$errmessage,'ErrorTime'=>time());
				$LastError = json_encode($error);
				$LastErrors[] = json_encode($error);
				return true;
			}
			return false;
		}
		catch(Exception $ex)
		{
			return false;
		}
	}
	
	public function ClearLogs()
	{
		$this->LastError = null;
		$this->LastErrors = null;
		return;	
	}
	
	public function GetLastError()
	{
		try{
			if(isset($this->LastError) and !empty($this->LastError))
			{
				return json_decode($this->LastError,true);
			}
			else
				return null;
		}
		catch(Exception $ex)
		{
			return false;
		}
	}
	
	public function GetError($i=0)
	{
		try{
			if(isset($this->LastErrors[$i]) and !empty($this->LastErrors[$i]))
			{
				return json_decode($this->LastErrors[$i],true);
			}
			else
				return null;
		}
		catch(Exception $ex)
		{
			return false;
		}
	}
	
	public function GetErrors()
	{
		try{
			if(isset($this->LastErrors) and !empty($this->LastErrors))
			{
				$output = array();
				if(is_array($this->LastErrors))
				{
					foreach($this->LastErrors as $errors)
					{
						$output[] = json_decode($errors,true);
					}
					return $output;
				}
				else
					return false;
			}
			else
				return null;
		}
		catch(Exception $ex)
		{
			return false;
		}
	}
	
	public function LoadErrors($logname)
	{
		try{
			if(file_exists($this->Logdir."/".logname.".txt"))
			{
				$data = file_get_contents($this->Logdir."/".logname.".txt");
				if(isset($data) and !empty($data))
				{
					$data = explode(PHP_EOL,$data);
					if(is_array($data))
					{
						foreach($data as $item)
						{
							$this->LastErrors[] = $item;
						}
						return true;
					}
					else
					{
						return false;
					}
				}
			}
			else
				return false;
		}
		catch(Exception $ex)
		{
			return false;
		}
	}
}
?>