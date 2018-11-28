<?php
// SMSnow Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\t3rdparty;
class SMSnow2
{
	protected $endpoint;
	protected $username;
	protected $password;
	
	public function __construct($username,$password,$endpoint = 'http://127.0.0.1/webservice/url/send.php')
	{
		date_default_timezone_set('Asia/tehran');
		$this->endpoint = $endpoint;
		$this->username = $username;
		$this->password = $password;
	}
	
	public function SendSMS($from,$phones,$message,$type='0')
	{
		if(is_array($phones))
		{
			$phones = implode(",",$phones);
		}
		$ch = curl_init($this->endpoint);
         curl_setopt_array($ch, array(
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_SAFE_UPLOAD => true,
         CURLOPT_POST => true,
         CURLOPT_HEADER => false,
         CURLOPT_HTTPHEADER => array(
             'Content-Type: multipart/form-data'
         ),
         CURLOPT_POSTFIELDS => array(
			'method' => 'sendsms',
			'username' => $this->username,
			'password' => $this->password,
			'from' => $from,
			'to' => $phones,
			'text' => $message,
			'format' => 'json',
			'type' => $type
			),
         CURLOPT_TIMEOUT => 0,
         CURLOPT_CONNECTTIMEOUT => 6000,
         CURLOPT_SSL_VERIFYPEER => false
         ));
		$output = curl_exec($ch);
		curl_close($ch);
			return json_decode($output,true);
	}

	public function GetStatus($ids)
	{
		if(is_array($ids))
		{
			$ids = implode(",",$ids);
		}
		$ch = curl_init($this->endpoint);
         curl_setopt_array($ch, array(
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_SAFE_UPLOAD => true,
         CURLOPT_POST => true,
         CURLOPT_HEADER => false,
         CURLOPT_HTTPHEADER => array(
             'Content-Type: multipart/form-data'
         ),
         CURLOPT_POSTFIELDS => array(
			'method' => 'getstatus',
			'username' => $this->username,
			'password' => $this->password,
			'id' => $ids,
			'format' => 'json'
			),
         CURLOPT_TIMEOUT => 0,
         CURLOPT_CONNECTTIMEOUT => 6000,
         CURLOPT_SSL_VERIFYPEER => false
         ));
		$output = curl_exec($ch);
		curl_close($ch);
			return json_decode($output,true);
	}
	
	public function GetCredit()
	{
		$ch = curl_init($this->endpoint);
         curl_setopt_array($ch, array(
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_SAFE_UPLOAD => true,
         CURLOPT_POST => true,
         CURLOPT_HEADER => false,
         CURLOPT_HTTPHEADER => array(
             'Content-Type: multipart/form-data'
         ),
         CURLOPT_POSTFIELDS => array(
			'method' => 'getcredit',
			'username' => $this->username,
			'password' => $this->password,
			'format' => 'json'
			),
         CURLOPT_TIMEOUT => 0,
         CURLOPT_CONNECTTIMEOUT => 6000,
         CURLOPT_SSL_VERIFYPEER => false
         ));
		$output = curl_exec($ch);
		curl_close($ch);
			return json_decode($output,true);
	}
}