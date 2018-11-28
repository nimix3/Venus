<?php
// INIParser Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class INIParser
{
	protected $LastError;
	
	public function WriteINIFile($array, $file)
	{
		try{
			if(!isset($file,$array) or empty($file) or empty($array))
				return false;
			$res = array();
			if(is_array($array))
			{
				foreach($array as $key => $val)
				{
					if(is_array($val))
					{
						$res[] = "[$key]";
						foreach($val as $skey => $sval) 
						{
							$res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
						}
					}
					else $res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
				}
				if($this->safefilerewrite($file, implode("\r\n", $res)))
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
			return null;
		}
	}
	
	private function safefilerewrite($fileName, $dataToSave)
	{    
		try{
			if ($fp = fopen($fileName, 'w'))
			{
				$startTime = microtime();
				do
				{ 
					$canWrite = flock($fp, LOCK_EX);
					if(!$canWrite) usleep(round(rand(0, 100)*1000));
				} 
				while ((!$canWrite)and((microtime()-$startTime) < 1000));
				if ($canWrite)
				{            
					fwrite($fp, $dataToSave);
					flock($fp, LOCK_UN);
				}
				fclose($fp);
				return true;
			}
			else
				return false;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return null;
		}
	}
	
	public function ReadINIFile($file)
	{
		try{
			if(isset($file) and !empty($file))
			{
				if(file_exists($file))
					return parse_ini_file($file);
				else
					return null;
			}
			else
				return null;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return null;
		}
	}
	
	public function ReadINIString($str)
	{
		try{
			if(isset($str) and !empty($str))
			{
				return parse_ini_string($str);
			}
			else
				return null;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return null;
		}
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}
?>