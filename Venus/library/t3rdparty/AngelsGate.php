<?php
// AngelsGateLite Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// Angles Gate: A Flexible IoT Security Protocol for Cloud-fog Communications (Lite Edition) https://github.com/nimix3/AngelsGateLite is under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\t3rdparty;
class AngelsGate
{
	use HashController;
	
	public $Request;
	public $Data;
	public $Deviceid;
	public $Ssalt;
	public $Stime;
	public $Segment;
	public $Config;
	public $Token;
	public $Addition;

	public function __construct($ConfFile='config/config.php')
	{
		try{
			if(isset($ConfFile) and !empty($ConfFile))
			{
				if(file_exists($ConfFile))
				{
					include($ConfFile);
					$this->Config = $Configurations;
					unset($Configurations);
				}
				else
				{
					include('config/config.php');
					$this->Config = $Configurations;
					unset($Configurations);
				}
			}
			else
				$this->RawOutput('ERROR_SERVER_FATAL',true);
			return;
		}
		catch(Exception $e) {
			$this->RawOutput('ERROR_SERVER_FATAL',true);
		}
	}

	public function Signal()
	{
		if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST')
		{
			$input = file_get_contents("php://input");
			if(isset($input) and !empty($input))
			{
				$input = json_decode($input,true);
				$RawToken = $input['S'];
				$this->Addition = $input['A'];
				if(isset($input) and !empty($input))
				{
					try{
						$Crypto = new CryptoEx();
						$this->Token = $Crypto->AdvDecrypt($RawToken,$this->Config['KEY'],$this->Config['IV']);
						try{
							return $this;
						}
						catch(Exception $ex)
						{
							$this->RawOutput('-1',true);
						}
					}
					catch(Exception $ex)
					{
						$this->RawOutput('-2',true);
					}
				}
				else
				{
					$this->RawOutput('-3',true);
				}
			}
			else
			{
				$this->RawOutput('-5',true);
			}
		}
		else
		{
			$this->RawOutput('-6',true);
		}
		exit();
	}

	public function Input()
	{
		$input = "";
		if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST')
			$input = file_get_contents("php://input");
		else
			$this->RawOutput('ERROR_INPUT_INVALID',true);
		
		if(!isset($input) or empty($input))
			$this->RawOutput('ERROR_INPUT_EMPTY',true);
		try{
			$Crypto = new CryptoEx();
			$input = $Crypto->AdvDecrypt($input,$this->Config['KEY'],$this->Config['IV']);
			$input = json_decode($input,true);
			$this->Request = $input['Request'];
			$this->Deviceid = $input['Deviceid'];
			$this->Ssalt = $Crypto->RSADecrypt($input['Ssalt'],$this->Config['Priv8Key']);
			$this->Data = $Crypto->AdvDecrypt($input['Data'],base64_encode(substr($this->Ssalt.base64_decode($this->Config['KEY']),0,16)),$this->Config['IV']);
			if($this->Config['compress'])
			{
				$this->Data = gzinflate($this->Data);
			}
			$this->Stime = $input['Time'];
			$this->Segment = $input['Segment'];
			if(!isset($input['Signature'],$input['Segment'],$input['Time'],$input['Request'],$input['Data'],$input['Deviceid'],$input['Ssalt']) or empty($input['Request']) or empty($input['Deviceid']) or empty($input['Ssalt']) or empty($input['Signature']) or empty($input['Time']) or empty($input['Segment']))
			{
				$this->Output('ERROR_INPUT_BROKEN',$this->Deviceid,true);
			}
			if($this->ComputeHash($this->Ssalt.date("Y").$input['Request'].$this->Data.$input['Deviceid'],$this->Ssalt) != $input['Signature'])
			{
				$this->Output('ERROR_INPUT_CRACKED',$this->Deviceid,true);
			}
			if((time() - intval($this->Stime)) > 86400 or (intval($this->Stime) - time() > 86400))
			{
				$this->Output('ERROR_INPUT_INVALIDTIME',$this->Deviceid,true);
			}
			if(method_exists($this, 'HashChecker'))
			{
				if(! $this->HashChecker(new SQLi($this->Config),$this->Ssalt,$_SERVER['REMOTE_ADDR'],$this->Deviceid,intval($this->Config["TimeLimit"])))
				{
					$this->Output('ERROR_INPUT_INVALIDHASH',$this->Deviceid,true);
				}
			}
			return $this;
		}
		catch(Exception $e)
		{
			$this->RawOutput('ERROR_INPUT_UNKNOW',true);
		}
	}
	
	public function SyncTime()
	{
			$this->RawOutput(time(),true);
	}
	
	public function Output($data,$deviceid,$ex=false)
	{
		if(!isset($deviceid) or empty($deviceid))
		{
			$deviceid = $this->Deviceid;
		}
		@ header_remove("Server");
		@ header_remove("Content-Type");
		@ header_remove("Transfer-Encoding");
		@ header_remove("Set-Cookie");
		@ header_remove("P3P");
		@ header_remove("Date");
		@ header_remove("X-Page-Speed");
		@ header_remove("Cache-Control");
		try{
			if(!isset($this->Segment) or empty($this->Segment))
				$this->Segment = rand(100000,9999999);
			$token = $this->GenerateID(rand(8,16));
			if(is_array($data))
			{
				$dataSig = base64_encode(json_encode($data,JSON_UNESCAPED_UNICODE));
			}
			else
			{
				$dataSig = base64_encode($data);
			}
			$Signature = $this->ComputeHash($this->Ssalt . $dataSig . date('Y') . $this->Segment . $deviceid,$token);
			$Crypto = new CryptoEx();
			$datax = array(
				'Signature'=> $Signature,
				'Data'=> $dataSig,
				'Deviceid'=> md5($deviceid),
				'Token'=> $token,
				'Segment'=> $this->Segment
				);
			if($this->Config['compress'])
			{
				$datax['Data'] = gzdeflate($datax['Data'],9,ZLIB_ENCODING_DEFLATE);
			}
			$datax = json_encode($datax,JSON_UNESCAPED_UNICODE);
			echo $Crypto->AdvEncrypt($datax,base64_encode(substr($this->Ssalt.base64_decode($this->Config['KEY']),0,16)),$this->Config['IV']);
			if($ex)
				exit();
		}
		catch(Exception $e)
		{
			$this->RawOutput('ERROR_SERVER_FATAL',true);
		}
	}
	
	private function ComputeHash($text,$salt)
	{
		if(strlen($salt) % 2 == 0)
			return str_rot13(base64_encode(hash("sha256",base64_encode($text).md5($salt))));
		else
			return str_rot13(base64_encode(hash("sha256",hash('sha1',$salt).base64_encode($text))));
	}
	
	private function GenerateID($max = 8)
	{
		if(intval($max) <= 1)
			return mt_rand(0,9);
		else if(intval($max) <= 2)
			return mt_rand(0,99);
		else if(intval($max) > 11)
			return substr(substr(time(),-8).mt_rand(10000,9999999).rand(100,9999999),0,intval($max));
		else
			return substr(mt_rand(1000,999999).rand(1000,9999999).substr(mt_rand(1000,time()),-4),0,intval($max));
	}

	public function RawOutput($data,$ex=false)
	{
		@ header('Content-type: application/json; charset=utf-8');
		header_remove("Server");
		header_remove("Content-Type");
		header_remove("Transfer-Encoding");
		header_remove("Set-Cookie");
		header_remove("P3P");
		header_remove("Date");
		header_remove("X-Page-Speed");
		header_remove("Cache-Control");
		echo $data;
		if($ex)
			exit();
	}
}

trait HashController
{
	public function HashChecker($SQL,$Ssalt,$IP,$Deviceid,$timelimit=86400)
	{
		if(!isset($SQL,$Ssalt,$Deviceid,$IP) or empty($SQL) or empty($Ssalt) or empty($Deviceid) or empty($IP))
			return false;
		if($SQL->InitDB())
		{
			$Deviceid = $SQL->SecureDBQuery($Deviceid,true);
			$Ssalt = $SQL->SecureDBQuery($Ssalt,true);
			$IP = $SQL->SecureDBQuery($IP,true);
			$resx = $SQL->SelectDBsecure('*','HashTable','session','=','? AND `ssalt` = ?',array($Deviceid,$Ssalt));
			if(isset($resx[0]) and !empty($resx[0]))
			{
				if(intval($resx[0]['time']) + intval($timelimit) >= time())
				{
					return false;
				}
				else
				{
					$SQL->UpdateDBsecure('HashTable','session','=','? AND `ssalt` = ?',array($Deviceid,$Ssalt),array('time'=>time(),'ip'=>$IP),1);
					return true;
				}
			}
			else
			{
				$SQL->InsertDBsecure('HashTable',array('ssalt'=>$Ssalt,'time'=>time(),'ip'=>$IP,'session'=>$Deviceid));
				return true;
			}
		}
		else
		{
			return false;
		}
	}
}