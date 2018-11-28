<?php
// SMSnow Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\t3rdparty;
class SMSnow3
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
		$param = array(
        'Username'=>$this->username,
        'Password'=>$this->password,
        'fromNum'=>$from,
		'toNum'=>$phones,
		'Content'=>$message,
		'Type'=>$type
		);
		try{
			$res = $this->client->__soapCall('SendSMS', $param);
			if(isset($res) and !empty($res))
			{
				$res;
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
		$param = array(
        'Username'=>$this->username,
        'Password'=>$this->password,
        'Id'=>$IDs
		);
		try{
			$res = $this->client->__soapCall('GetStatus', $param);
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
		$param = array(
        'Username'=>$this->username,
        'Password'=>$this->password
		);
		try{
			$res = $this->client->__soapCall('GetCredit', $param);
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