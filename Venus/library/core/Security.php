<?php
// Security Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class Security
{
	protected $LastError;
	
	public function GetImageSecure($Stream)
	{
		if(isset($Stream) and !empty($Stream))
		{
			$resource = imagecreatefromstring($Stream);
			if($resource !== false)
			{
				return $resource;
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
	
	public function SaveImageSecure($Stream,$File,$Format='jpg')
	{
		$Resource = $this->SecureImage($Stream);
		if(isset($Resource) and !empty($Resource))
		{
			if($Format == 'jpg')
			{
				imagejpeg($Resource,$File.'.'.$Format);
			}
			else if($Format == 'png')
			{
				imagepng($Resource,$File.'.'.$Format);
			}
			else if($Format == 'gif')
			{
				imagegif($Resource,$File.'.'.$Format);
			}
			else if($Format == 'wbmp')
			{
				imagewbmp($Resource,$File.'.'.$Format);
			}
			else
			{
				imagedestroy($Resource);
				return false;
			}
			imagedestroy($Resource);
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function SaveFileSecure($Input,$Output)
	{
		$BaseUnit = 4096;
		$InHandle = @fopen($Input, "rb");
		$OutHandle = @fopen($Output, "wb");
		if($InHandle and $OutHandle){
			while (!feof($InHandle)) {
				$buffer = fgetss($InHandle, $BaseUnit);
				if(fwrite($OutHandle,$buffer) === false)
				{
					$this->LastError[] = "cannot write to file specified";
					unlink($Output);
					return false;
				}
			}
			fclose($InHandle);
			fclose($OutHandle);
			return true;
		}
		else
		{
			$this->LastError[] = "cannot open specified files";
			return false;
		}
	}
	
	public function SaveStreamSecure($Stream,$Output)
	{
		if(file_put_contents($Output,strip_tags($Stream),LOCK_EX) === false)
			return false;
		else
			return true;
	}
	
	public function SaveFileBase64($Input,$Output)
	{
		$BaseUnit = 4096;
		$InHandle = @fopen($Input, "rb");
		$OutHandle = @fopen($Output, "wb");
		if($InHandle and $OutHandle){
			while (($buffer = fread($InHandle,$BaseUnit*3)) !== false) {
				$data = base64_encode($buffer);
				if(fwrite($OutHandle,$data,strlen($data)) === false)
				{
					$this->LastError[] = "cannot write to file specified";
					unlink($Output);
					return false;
				}
			}
			fclose($InHandle);
			fclose($OutHandle);
			return true;
		}
		else
		{
			$this->LastError[] = "cannot open specified files";
			return false;
		}
	}
	
	function Base64FileEncode($InputFile, $OutputFile)
	{
		$InHandle = fopen($InputFile, 'rb');
		$OutHandle = fopen($OutputFile, 'wb');
		if($InHandle and $OutHandle){
			$bufferSize = 3 * 256;
			while(!feof($InHandle)){
				$buffer = fread($InHandle, $bufferSize);
				$ebuffer = base64_encode($buffer);
				if(fwrite($OutHandle, $ebuffer) === false)
				{
					$this->LastError[] = "cannot write to file specified";
					unlink($OutputFile);
					return false;
				}
			}
			fclose($InHandle);
			fclose($OutHandle);
			return true;
		}
		else
		{
			$this->LastError[] = "cannot open specified files";
			return false;
		}
	}

	function Base64FileDecode($InputFile, $OutputFile)
	{
		$InHandle = fopen($InputFile, 'rb');
		$OutHandle = fopen($OutputFile, 'wb');
		if($InHandle and $OutHandle){
			$bufferSize = 4 * 256;
			while(!feof($InHandle)){
				$buffer = fread($InHandle, $bufferSize);
				$dbuffer = base64_decode($buffer);
				if(fwrite($OutHandle, $dbuffer) === false)
				{
					$this->LastError[] = "cannot write to file specified";
					unlink($OutputFile);
					return false;
				}
			}
			fclose($InHandle);
			fclose($OutHandle);
		}
		else
		{
			$this->LastError[] = "cannot open specified files";
			return false;
		}
	}
	
	function Base64UrlEncode($string)
	{
		$data = base64_encode($string);
		$data = str_replace(array('+','/','='),array('-','_',''),$data);
		return $data;
	}
	
	function Base64UrlDecode($string)
	{
		$data = str_replace(array('-','_'),array('+','/'),$string);
		$mod4 = strlen($data) % 4;
		if($mod4){
			$data .= substr('====', $mod4);
		}
		return base64_decode($data);
	}
	
	public function GenerateHash($Password)
	{
		retrurn password_hash($Password,PASSWORD_DEFAULT);
	}
	
	public function ValidatePassword($Password,$Hash)
	{
		return password_verify($Password,$Hash);
	}
	
	public function Compare($str1,$str2)
	{
		return $this->IsEqual($str1,$str2);
	}
	
	public function IsEqual($str1,$str2)
	{
		if(!function_exists('hash_equals')) 
		{
			if(strlen($str1) != strlen($str2))
			{
				return false;
			}
			else 
			{
				$res = $str1 ^ $str2;
				$ret = 0;
				for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
				return !$ret;
			}
		}
		else
		{
			return hash_equals($str1,$str2);
		}
	}
	
	function GetMimeType($File)
	{
		if(file_exists($File))
		{
			if (function_exists('finfo_file')) {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$type = finfo_file($finfo, $File);
				finfo_close($finfo);
				return $type;
			}
			else if (function_exists('mime_content_type'))
			{
				$type = mime_content_type($File);
				return $type;
			}
			else if (function_exists('exif_imagetype'))
			{
				$type = exif_imagetype($File);
				if($type !== false)
				{
					return image_type_to_mime_type($type);
				}
				else 
				{
					return 'application/octet-stream';
				}
			}
			else
			{
				return 'application/octet-stream';
			}
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
    }
	
	public function CleanString(string $input, string $encoding = 'UTF-8')
    {
        return htmlentities($input, ENT_QUOTES | ENT_HTML5, $encoding);
    }
	
	function XSSCleaner($data)
	{
		$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
		do
		{
			$old_data = $data;
			$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
		}
		while ($old_data !== $data);
		return $this->CleanString($data);
	}
	
	public function RFICleaner($data)
	{
		return basename($data);
	}
	
	public function GetUniqueID()
	{
		return uniqid();
	}
	
	public function GenerateCSRFToken($handler='csrf')
	{
		if(!isset($handler) or empty($handler))
			return false;
		session_start();
		$_SESSION[$handler] = md5(uniqid(rand(), true));
		session_regenerate_id();
		return true;
	}
	
	public function GenCSRF($handler='csrf')
	{
		return $this->GenerateCSRFToken($handler);
	}
	
	public function ValidateCSRFToken($handler='csrf',$value)
	{
		if(!isset($handler) or empty($handler))
			return false;
		session_start();
		if(isset($_SESSION[$handler]) and !empty($_SESSION[$handler]))
		{
			if($this->IsEqual($_SESSION[$handler],$value))
			{
				session_regenerate_id();
				return true;
			}
			else
			{
				session_regenerate_id();
				return false;
			}
		}
		else
		{
			session_regenerate_id();
			return false;
		}
	}
	
	public function ValidateCSRF($handler='csrf',$value)
	{
		return $this->ValidateCSRFToken($handler,$value);
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}
?>