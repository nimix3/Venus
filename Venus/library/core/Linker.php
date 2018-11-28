<?php
// Linker Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class Linker
{
	protected $LastError;
	protected $CurrentCC;
	protected $DefaultBase;
	protected $CurrentBase;
	protected $CCBase;
	
	public function __construct($CCs='',$DefBase='')
	{
		if(isset($CCs) and !empty($CCs))
		{
			if(is_array($CCs))
			{
				$this->CCBase = $CCs;
			}
		}
		if(isset($DefBase) and !empty($DefBase))
		{
			$this->SetDefaultBase($DefBase);
		}
		else
		{
			$this->SetDefaultBase();
		}
		$this->CurrentCC = $this->GetCCode();
	}
	
	public function GetCCode($engine='file',$config='')
	{
		if(isset($_SERVER['GEOIP_COUNTRY_CODE']) and !empty($_SERVER['GEOIP_COUNTRY_CODE']))
		{
			$this->CurrentCC = $_SERVER['GEOIP_COUNTRY_CODE'];
			return $_SERVER['GEOIP_COUNTRY_CODE'];
		}
		$ip = ip2long($_SERVER['REMOTE_ADDR']);
		if($engine == 'file')
		{
			if(isset($config) and !empty($config))
			{
				if(file_exists($config))
				{
					try{
						$db = file_get_contents($config);
						$db = explode(PHP_EOL,$db);
						if(is_array($db))
						{
							foreach($db as $item)
							{
								$rec = explode(',',$item);
								if($ip >= intval($rec[0]) and $ip <= intval($rec[1]))
								{
									if(isset($rec[2]) and !empty($rec[2]))
									{
										$this->CurrentCC = $rec[2];
										return $rec[2];
										break;
									}
									else
									{
										return 'UNKNOWN';
										break;
									}
								}
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
		else if($engine == 'api')
		{
			if(isset($config) and !empty($config))
			{
				try{
					$request = str_ireplace("{{ip}}",$ip,$config);
					$response = file_get_contents($request);
					if(isset($response) and !empty($response))
					{
						if(strlen($response) <= 3)
						{
							$this->CurrentCC = $response;
							return $response;
						}
						else
						{
							return null;
						}
					}
				}
				catch(Exception $ex)
				{
					$this->LastError[] = $ex->getMessage();
					return null;
				}
			}
			else
			{
				return null;
			}
		}
		else
			return null;
	}
	
	public function SetCCBase($cc,$base)
	{
		if(isset($cc,$base) and !empty($cc) and !empty($base))
		{
			$cc = strtoupper($cc);
			$this->CCBase[$cc] = $base;
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function SetDefaultBase($base=null)
	{
		if(isset($base) and !empty($base))
		{
			$this->CCBase['DEFAULT'] = $base;
			$this->DefaultBase = $base;
			return true;
		}
		else
		{
			$this->CCBase['DEFAULT'] = $_SERVER['HTTP_HOST'];
			$this->DefaultBase = $_SERVER['HTTP_HOST'];
			return true;
		}
		return false;
	}
	
	public function GetDefaultBase()
	{
		return $this->DefaultBase;
	}
	
	public function GetCurrentBase()
	{
		$cc = $this->GetCCode();
		if(isset($this->CCBase[$cc]) and !empty($this->CCBase[$cc]))
		{
			$this->CurrentBase = $this->CCBase[$cc];
		}
		else
		{
			$this->CurrentBase = $this->DefaultBase;
		}
		return $this->CurrentBase;
	}
	
	public function GetCurrentProto()
	{
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		return $protocol;
	}
	
	public function Linking($asset='',$check=false,$proto='')
	{
		if(isset($asset,$check) and !empty($asset))
		{
			if(file_exists($asset))
			{
				if(isset($proto) and !empty($proto))
					$protocol = $proto;
				else
					$protocol = $this->GetCurrentProto();
				$protocol = str_replace("//","",$protocol);
				$url = $protocol."//".$this->GetCurrentBase()."/".trim(str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath($asset)),'/');
				if(boolval($check))
				{
					$file_headers = @get_headers($url);
					if(!$file_headers || strpos($file_headers[0],"404 Not Found") !== false){
						return null;
					}
					else{
						return $url;
					}
				}
				else
				{
					return $url;
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
	
	public function Transforming($url='',$check=false,$proto='')
	{
		if(isset($url,$check) and !empty($url))
		{
			if(isset($proto) and !empty($proto))
				$protocol = $proto;
			else
				$protocol = parse_url($url, PHP_URL_SCHEME);
			$url = str_replace(parse_url($url, PHP_URL_SCHEME)."://".parse_url($url, PHP_URL_HOST),$protocol."://".$this->GetCurrentBase(),$url);
			if(boolval($check))
			{
				$file_headers = @get_headers($url);
				if(!$file_headers || strpos($file_headers[0],"404 Not Found") !== false){
					return null;
				}
				else{
					return $url;
				}
			}
			else
			{
				return $url;
			}
		}
		else
		{
			return null;
		}
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}