<?php
// sTemplate Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class sTemplate
{
	protected $Hashes;
	protected $tFile;
	protected $Strings;
	protected $LastError;
	
	public function __construct($Hashes=null,$tFile=null,$Strings=null)
	{
		if(isset($tFile) and !empty($tFile) or isset($Strings) and !empty($Strings))
		{
			if(is_array($Hashes))
				$this->Hashes = $Hashes;
			$this->tFile = $tFile;
			$this->Strings = $Strings;
		}
	}
	
	public function TemplateEngine($Hashes=null,$Strings=null) 
	{
		if(!isset($Hashes) or empty($Hashes))
			$Hashes = $this->Hashes;
		if(!isset($Strings) or empty($Strings))
			$Strings = $this->Strings;
		if(is_array($Hashes) and !empty($Strings))
		{
			try{
				$string = $Strings;
				$string = preg_replace('/\{\{#.*?#\}\}/s', '',$string);
				foreach($Hashes as $ind => $val){
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
		$this->LastError[] = "Invalid variables";
		return null;
	}
	
	public function ChangeHash($newHash)
	{
		if(isset($newHash) and !empty($newHash))
		{
			if(is_array($newHash))
				$this->Hashes = $newHash;
			else
				return false;
		}
		else
		{
			return false;
		}
	}
	
	public function ChangeStrings($newStrings)
	{
		if(isset($newStrings) and !empty($newStrings))
		{
			$this->Strings = $newStrings;
		}
		else
		{
			return false;
		}
	}
	
	public function ChangeFile($newFile)
	{
		if(isset($newFile) and !empty($newFile))
		{
			$this->tFile = $newFile;
		}
		else
		{
			return false;
		}
	}

	public function TemplateParser($Hashes=null,$tFile=null) 
	{
		if(!isset($Hashes) or empty($Hashes))
			$Hashes = $this->Hashes;
		if(!isset($tFile) or empty($tFile))
			$tFile = $this->tFile;
		if(is_file($tFile) and file_exists($tFile))
		{
			try{
				$string = file_get_contents($tFile);
				if (isset($string) and !empty($string)) {
					$string = $this->TemplateEngine($string,$Hashes);
					return $string;
				}
				else
				{
					$this->LastError[] = "Empty Data";
					return null;
				}
			}
			catch(Exception $ex)
			{
				$this->LastError[] = $ex->getMessage();
				return null;
			}
		}
		$this->LastError[] = "Invalid file";
		return null;
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}
?>