<?php
// SMSnow Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\t3rdparty;
class SMSnow
{
	protected $endpoint;
	protected $client;
	protected $username;
	protected $password;
	
	public function __construct($username,$password,$endpoint = 'http://127.0.0.1/webservice/send.php?wsdl')
	{
		date_default_timezone_set('Asia/tehran');
		$this->endpoint = $endpoint;
		$this->username = $username;
		$this->password = $password;
		$this->client = new SoapClient($this->endpoint,array('encoding'=>'UTF-8'));
	}
	
	public function SendSMS($from,$phones,$message,$type='0')
	{
		if(!is_array($phones))
		{
			if(strpos($phones,",") !== false)
			{
				$phones = explode(",",$phones);
			}
			else
				$phones = array($phones);
		}
		try{
			$res = $this->client->SendSMS($from,$phones,$message,$type,$this->username,$this->password);
			if(isset($res) and !empty($res))
			{
				return $res;
			}
			else
				return null;
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
	}
	
	public function SendSMSBulk($from,$phones,$message,$type)
	{
		if(!is_array($type))
		{
			if(strpos($type,",") !== false)
			{
				$type = explode(",",$type);
			}
			else
				$type = array($type);
		}
		if(!is_array($message))
		{
			if(strpos($message,",") !== false)
			{
				$message = explode(",",$message);
			}
			else
				$message = array($message);
		}
		if(!is_array($from))
		{
			if(strpos($from,",") !== false)
			{
				$from = explode(",",$from);
			}
			else
				$from = array($from);
		}
		if(!is_array($phones))
		{
			if(strpos($phones,",") !== false)
			{
				$phones = explode(",",$phones);
			}
			else
				$phones = array($phones);
		}
		try{
			$res = $this->client->SendMultiSMS($from,$phones,$message,$type,$this->username,$this->password);
			if(isset($res) and !empty($res))
			{
				return $res;
			}
			else
				return null;
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
	}

	public function GetStatus($IDs)
	{
		if(!is_array($IDs))
		{
			if(strpos($IDs,",") !== false)
			{
				$IDs = explode(",",$IDs);
			}
			else
				$IDs = array($IDs);
		}
		try{
			$res = $this->client->GetStatus($this->username,$this->password,$IDs);
			if(isset($res) and !empty($res))
			{
				return $res;
			}
			else
				return null;
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
	}

	public function GetCredit()
	{
		try{
			$res = $this->client->GetCredit($this->username,$this->password);
			if(isset($res) and !empty($res))
			{
				return $res;
			}
			else
				return null;
		}
		catch(Exception $e)
		{
			return $e->getMessage();
		}
	}
}