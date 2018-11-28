<?php
// RealTime Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class RealTime
{
	protected $Host;
	protected $Port = 6437; //or 6436 is best for websockets.
	protected $Location;
	protected $Socket;
	protected $Clients;
	protected $LastError;
	protected $Status;
	protected $Delay = 100;
	protected $IPS;
	protected $MaxReq = 3;
	
	public function __construct($Host,$Port=6437,$Location,$Delay=100,$MaxReq=3)
	{
		if(isset($Host) and !empty($Host))
		{
			$this->Host = $Host;
		}
		if(isset($Port) and !empty($Port))
		{
			$this->Port = $Port;
		}
		if(isset($Location) and !empty($Location))
		{
			$this->Location = $Location;
		}
		if(isset($Delay) and !empty($Delay))
		{
			$this->Delay = $Delay;
		}
		if(isset($MaxReq) and !empty($MaxReq))
		{
			$this->MaxReq = $MaxReq;
		}
		try{
			$res = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if($res !== false)
				$this->Socket = $res;
			else
				$this->Status = false;
			if(!socket_set_option($this->Socket, SOL_SOCKET, SO_REUSEADDR, 1)) { 
				$this->Status = false;
				return;
			} 
			@ $res = socket_bind($this->Socket, 0, $this->Port) or die();
			if($res == false)
				$this->Status = false;
			$res = socket_listen($this->Socket);
			$this->Clients = array($this->Socket);
			$this->Status = $res;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			$this->Status = false;
		}
	}
	
	public function Send($msg,$user=null)
	{
		if(!isset($msg) or empty($msg))
			return false;
		if(isset($user) and !empty($user))
		{
			try{
				@socket_write($user,$msg,strlen($msg));
			}
			catch(Exception $ex)
			{
				$this->LastError[] = $ex->getMessage();
				return false;
			}
		}
		else
		{
			foreach($this->Clients as $Client)
			{
				try{
					@socket_write($Client,$msg,strlen($msg));
				}
				catch(Exception $ex)
				{
					$this->LastError[] = $ex->getMessage();
					return false;
				}
			}
			return true;
		}
	}
	
	public function Unmask($text) 
	{
		$length = ord($text[1]) & 127;
		if($length == 126) {
			$masks = substr($text, 4, 4);
			$data = substr($text, 8);
		}
		elseif($length == 127) {
			$masks = substr($text, 10, 4);
			$data = substr($text, 14);
		}
		else {
			$masks = substr($text, 2, 4);
			$data = substr($text, 6);
		}
		$text = "";
		for ($i = 0; $i < strlen($data); ++$i) {
			$text .= $data[$i] ^ $masks[$i%4];
		}
		return $text;
	}
	
	public function Mask($text)
	{
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($text);
		if($length <= 125)
			$header = pack('CC', $b1, $length);
		elseif($length > 125 && $length < 65536)
			$header = pack('CCn', $b1, 126, $length);
		elseif($length >= 65536)
			$header = pack('CCNN', $b1, 127, $length);
		return $header.$text;
	}
	
	public function Handshaking($receved_header,$user, $host, $port, $location="")
	{
		if(!isset($user) or empty($user))
			return false;
		$location = trim($location,"/");
		$headers = array();
		$lines = preg_split("/\r\n/", $receved_header);
		foreach($lines as $line)
		{
			$line = chop($line);
			if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
			{
				$headers[$matches[1]] = $matches[2];
			}
		}
		$secKey = $headers['Sec-WebSocket-Key'];
		$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		$upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
		"Upgrade: websocket\r\n" .
		"Connection: Upgrade\r\n" .
		"WebSocket-Origin: $host\r\n" .
		"WebSocket-Location: ws://$host:$port/$location\r\n".
		"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
		try{
			if(socket_write($user,$upgrade,strlen($upgrade)) !== false)
				return true;
			else
				return false;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function Run($Callable)
	{
		if($this->Status == false)
			return false;
		try{
			while (true) 
			{
				if(intval($this->Delay) > 0)
					usleep(intval($this->Delay));
				$changed = $w = $e = $this->Clients;
				@ $res = socket_select($changed, $w, $e, 0, 10);
				if($res === false)
				{
					usleep(100);
					continue;
				}
				if (in_array($this->Socket, $changed)) {
					$socket_new = socket_accept($this->Socket);
					$this->Clients[] = $socket_new;
					@ $header = socket_read($socket_new, 1024);
					if($this->Handshaking($header, $socket_new, $this->Host, $this->Port, $this->Location))
					{
						$this->IPS[$socket_new] = array("time"=>time(),"counter"=>1);
					}
					$res = array_search($this->Socket, $changed);
					unset($changed[$res]);
				}
				foreach ($changed as $socket) 
				{
					if(is_resource($socket) and !empty($socket))
					{
						while(socket_recv($socket, $buf, 2048, 0) >= 1)
						{
							if(intval($this->MaxReq) > 1)
							{
								if(isset($this->IPS[$socket]) and !empty($this->IPS[$socket]))
								{
									if(is_array($this->IPS[$socket]))
									{
										if(intval($this->IPS[$socket]["time"]) == time())
										{
											if(intval($this->IPS[$socket]["counter"]) >= intval($this->MaxReq))
											{
												$res = array_search($socket, $this->Clients);
												unset($this->IPS[$socket]);
												unset($this->Clients[$res]);
												@ socket_close($socket);
											}
											else
											{
												$this->IPS[$socket] = array("time"=>intval($this->IPS[$socket]["time"]),"counter"=>intval($this->IPS[$socket]["counter"])+1);
											}
										}
										else
										{
											$this->IPS[$socket] = array("time"=>time(),"counter"=>1);
										}
									}
								}
							}
							$received_text = $this->Unmask($buf);
							if($received_text == "000")
							{
								$response_text = "000";
							}
							else
							{
								$ClientIP = null;
								$ClientPort = null;
								@ socket_getpeername($socket, $ClientIP, $ClientPort);
								@ $response_text = call_user_func_array($Callable, array($received_text,$ClientIP,$ClientPort));
							}
							$response_text = $this->Mask($response_text);
							$this->Send($response_text,$socket);
							break 2;
						}
						$buf = @socket_read($socket, 1024, PHP_NORMAL_READ);
						if ($buf === false) {
							$res = array_search($socket, $this->Clients);
							@ socket_getpeername($socket, $ClientIP);
							unset($this->Clients[$res]);
							@ socket_close($socket);
						}
					}
					else
					{
						$res = array_search($socket, $changed);
						unset($changed[$res]);
					}
				}
			}
			socket_close($this->Socket);
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
		}
	}
	
	public function getStatus()
	{
		return $this->Status;
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}