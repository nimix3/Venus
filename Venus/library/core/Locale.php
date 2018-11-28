<?php
// Locale Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class Locale
{
	protected $LocaleBox;
	protected $LastError;
	
	public function __construct($dir=null)
	{
		if(isset($dir) and !empty($dir))
			$this->LoadLocaleDir($dir);
	}
	
	public function LoadLocaleDir($dir)
	{
		if(!isset($dir) or empty($dir))
			return null;
		if(!is_dir($dir))
			return null;
		$allhere = array_slice(scandir($dir), 2);
		if(isset($allhere) and !empty($allhere))
		{
			if(is_array($allhere))
			{
				$calls = array();
				foreach($allhere as $element)
				{
					$element = $dir."/".$element;
					if(is_file($element) and file_exists($element))
					{
						if(pathinfo($element)['extension'] == 'db')
						{
							try{
								if($this->LoadLocaleFile($element,false))
									$calls[] = $element;
							}
							catch(Exception $ex){
								$this->LastError[] = $ex->getMessage();
							}
						}
					}
				}
				if(isset($calls) and !empty($calls))
					return $calls;
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
	
	public function GetSlangDirection($slang='DEFAULT')
	{
		try{
			if($this->CheckSlang($slang))
			{
				if(isset($this->LocaleBox[$slang]['_DIR_']) and !empty($this->LocaleBox[$slang]['_DIR_']))
				{
					if($this->LocaleBox[$slang]['_DIR_'] == 'RTL')
						return "RTL";
					else
						return "LTR";
				}
				else
				{
					return "UNKNOWN";
				}
			}
			else
			{
				return "UNKNOWN";
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return "UNKNOWN";
		}
	}
	
	public function LoadLocaleFile($file,$reload=false)
	{
		try{
			if(file_exists($file))
			{
				$res = parse_ini_file($file,true);
				if($res !== false)
				{
					if(boolval($reload))
					{
						$this->LocaleBox = $res;
					}
					else
					{
						if(is_array($this->LocaleBox))
							$this->LocaleBox = array_merge_recursive($res, $this->LocaleBox);
						else
							$this->LocaleBox = $res;
					}
					return true;
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
	
	public function LoadLocaleString($str,$reload=false)
	{
		try{
			if(isset($str) and !empty($str))
			{
				$res = parse_ini_string($str,true);
				if($res !== false)
				{
					if(boolval($reload))
					{
						$this->LocaleBox = $res;
					}
					else
					{
						if(is_array($this->LocaleBox))
							$this->LocaleBox = array_merge_recursive($res, $this->LocaleBox);
						else
							$this->LocaleBox = $res;
					}
					return true;
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
	
	public function GetPhrase($phrase,$slang='DEFAULT')
	{
		try{
			if(isset($slang,$phrase) and !empty($slang) and !empty($phrase))
			{
				if($slang == 'DEFAULT')
					$slang = 'DEFAULT';
				if(isset($this->LocaleBox[$slang][$phrase]) and !empty($this->LocaleBox[$slang][$phrase]))
					return $this->LocaleBox[$slang][$phrase];
				else
					return null;
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
	
	public function CheckPhrase($phrase,$slang='DEFAULT')
	{
		try{
			if(isset($slang,$phrase) and !empty($slang) and !empty($phrase))
			{
				if($slang == 'DEFAULT')
					$slang = 'DEFAULT';
				if(isset($this->LocaleBox[$slang][$phrase]) and !empty($this->LocaleBox[$slang][$phrase]))
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
	
	public function CheckSlang($slang)
	{
		if(isset($slang) and !empty($slang))
		{
			if(isset($this->LocaleBox[$slang]) and !empty($this->LocaleBox[$slang]))
				return true;
			else
				return false;
		}
		else
		{
			return false;
		}
	}
	
	public function FindPhrase($phrase)
	{
		try{
			$Finds = array();
			if(isset($phrase) and !empty($phrase))
			{
				if(is_array($this->LocaleBox))
				{
					foreach($this->LocaleBox as $sk => $sections)
					{
						if(is_array($sections))
						{
							if(isset($sections[$phrase]) and !empty($sections[$phrase]))
							{
								$Finds[$sk] = $sections[$phrase];
							}
						}
					}
					return $Finds;
				}
				else
				{
					return array();
				}
			}
			else
			{
				return array();
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return array();
		}
	}
	
	public function FindSentence($sntnc)
	{
		try{
			$Finds = array();
			if(isset($sntnc) and !empty($sntnc))
			{
				if(is_array($this->LocaleBox))
				{
					foreach($this->LocaleBox as $sk => $sections)
					{
						if(is_array($sections))
						{
							if(in_array($sntnc,$sections))
							{
								$Finds[] = $sk;
							}
						}
					}
					return $Finds;
				}
				else
				{
					return array();
				}
			}
			else
			{
				return array();
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return array();
		}
	}
	
	public function Translate($string=null,$slang='DEFAULT') 
	{
		if($slang == 'DEFAULT')
			$slang = 'DEFAULT';
		if(isset($this->LocaleBox[$slang]) and !empty($this->LocaleBox[$slang]))
			if(is_array($this->LocaleBox[$slang]) and !empty($string))
			{
				try{
					foreach($this->LocaleBox[$slang] as $ind => $val){
						$string = str_replace('{{'.$ind.'}}',$val,$string);
					}   
					$string = preg_replace('/\{\{(.*?)\}\}/is','',$string);
					return $string;
				}
				catch(Exception $ex)
				{
					$this->LastError[] = $ex->getMessage();
					return null;
				}
			}
		return null;
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}