<?php
// Network Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class Network
{
	protected $LastError;
	
	public function ServeFile($file,$name)
	{
		try{
			date_default_timezone_set('GMT');
			if(!file_exists($file))
				return false;
			$data_file = $file;
			$data_size = filesize($data_file);
			$mime = 'application/otect-stream';
			if(isset($name) and !empty($name))
				$filename = $name;
			else
				$filename = basename($data_file);
			if (isset($_SERVER['HTTP_RANGE']) || isset($HTTP_SERVER_VARS['HTTP_RANGE'])) {
				$ranges_str = (isset($_SERVER['HTTP_RANGE']))?$_SERVER['HTTP_RANGE']:$HTTP_SERVER_VARS['HTTP_RANGE'];
				$ranges_arr = explode('-', substr($ranges_str, strlen('bytes=')));
				if ((intval($ranges_arr[0]) >= intval($ranges_arr[1]) && $ranges_arr[1] != "" && $ranges_arr[0] != "" )
					|| ($ranges_arr[1] == "" && $ranges_arr[0] == "")
				) {
					$ranges_arr[0] = 0;
					$ranges_arr[1] = $data_size - 1;
				}
			} else {
				$ranges_arr[0] = 0;
				$ranges_arr[1] = $data_size - 1;
			}
			$file = fopen($data_file, 'rb');
			$start = $stop = 0;
			if ($ranges_arr[0] === "") {
				$stop = $data_size - 1;
				$start = $data_size - intval($ranges_arr[1]);
			} elseif ($ranges_arr[1] === "") {
				$start = intval($ranges_arr[0]);
				$stop = $data_size - 1;
			} else {
				$stop = intval($ranges_arr[1]);
				$start = intval($ranges_arr[0]);
			}    
			fseek($file, $start, SEEK_SET);
			$start = ftell($file);
			fseek($file, $stop, SEEK_SET);
			$stop = ftell($file);
			$data_len = $stop - $start;
			if (isset($_SERVER['HTTP_RANGE']) || isset($HTTP_SERVER_VARS['HTTP_RANGE'])) {
				header('HTTP/1.0 206 Partial Content');
				header('Status: 206 Partial Content');
			}
			header('Accept-Ranges: bytes');
			header('Content-type: ' . $mime);
			header('Content-Disposition: attachment; filename="' . $filename . '"'); 
			header("Content-Range: bytes $start-$stop/" . $data_size );
			header("Content-Length: " . ($data_len + 1));
			fseek($file, $start, SEEK_SET);
			$bufsize = 2048000;
			ignore_user_abort(true);
			@set_time_limit(0);
			while (!(connection_aborted() || connection_status() == 1) && $data_len > 0) {
				echo fread($file, $bufsize);
				$data_len -= $bufsize;
				flush();
			}
			fclose($file);
			return true;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function Ping($host, $timeout = 1) {
		try{
            $package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
            $socket  = socket_create(AF_INET, SOCK_RAW, 1);
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $timeout, 'usec' => 0));
            socket_connect($socket, $host, null);
            $ts = microtime(true);
            socket_send($socket, $package, strLen($package), 0);
            if (socket_read($socket, 255))
                    $result = microtime(true) - $ts;
            else    $result = false;
            socket_close($socket);
            return $result;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return null;
		}
    }
	
	public function GetDNS($host,&$authns,&$addtl)
	{
		return dns_get_record($host,DNS_ALL,$authns,$addtl);
	}
	
	public function GetMX()
	{
		$mxhosts = array();
		$weight = array();
		if(dns_get_mx($hostname,$mxhosts,$weight))
			return $mxhosts;
		else
			return null;
	}
	
	public function GetOwnRequestHeaders()
	{
		return getallheaders();
	}
	
	public function GetOwnResponseHeaders()
	{
		return apache_response_headers();
	}
	
	public function GetHeader($host,$context=null)
	{
		if(isset($context) and !empty($context))
			return get_headers($host,1,$context);
		else
			return get_headers($host,1);
	}
	
	public function SetHeader($header,$replace=false,$response)
	{
		if(isset($response) and !empty($response))
			header($header,$replace,$response);
		else
			header($header,$replace);
	}
	
	public function RemoveHeader($hname)
	{
		header_remove($hname);
	}
	
	public function SendRequest($host,$method='GET',$proxy=null,$header='',$content='')
	{
		try{
			if(is_file($content) and file_exists($content))
			{
				if ($stream = fopen($content, 'r')){
					$content = stream_get_contents($stream);
					fclose($stream);
				}
				else
				{
					return null;
				}
			}
			$context = array(
			'http' => array(
				'method' => $method,
				'header' => $header,
				'content' => $content,
				'proxy' => $proxy,
				'request_fulluri' => true
				),
			);
			$context['http'] = array_filter($context['http']);
			$context = stream_context_create($context);
			return file_get_contents($host,false,$context);
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return null;
		}
	}
	
	function SendRequestAdvance($url='',$postfields='',$header='',$proxy='',$proxyauth='',$proxytype='',$refer='',$useragent='',$cookie='',$file='',$timeout=10,$ctimeout=10)
	{
		try{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , intval($ctimeout)); 
			curl_setopt($ch, CURLOPT_TIMEOUT, intval($timeout));
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type' => 'application/x-www-form-urlencoded', 'charset' => 'utf-8']);
			if(isset($cookie) and !empty($cookie)){
				curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
				curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
			}
			if(isset($header) and !empty($header))
				if(is_array($header))
					curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			if(isset($proxy) and !empty($proxy))
			{
				if(strpos($proxy,':') !== false)
				{
					$proxy = explode(':',$proxy);
					$proxyip = $proxy[0];
					$proxyport = $proxy[1];
					curl_setopt($ch, CURLOPT_PROXY, $proxyip);
					curl_setopt($ch, CURLOPT_PROXYPORT, $proxyport);
				}
				else
				{
					curl_setopt($ch, CURLOPT_PROXY, $proxy);
				}
				if(isset($proxyauth) and !empty($proxyauth))
					curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
				if(isset($proxytype) and !empty($proxytype))
					curl_setopt($ch, CURLOPT_PROXYTYPE, $proxytype);
			}
			if(isset($refer) and !empty($refer))
				curl_setopt($ch, CURLOPT_REFERER, $refer);
			else
				curl_setopt($ch, CURLOPT_REFERER, $url);
			if(isset($useragent) and !empty($useragent))
				curl_setopt($ch, CURLOPT_USERAGENT,$useragent);
			else
				curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36");
			if(isset($postfields) and !empty($postfields))
			{
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
			}
			if(isset($file) and !empty($file))
			{
				$fp = fopen($file, 'w+');
				curl_setopt($ch, CURLOPT_FILE, $fp);
			}
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			//curl_setopt($ch, CURLOPT_HEADER, false);
			$curl_result = curl_exec($ch);
			$curl_httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if(isset($fp) and !empty($fp))
				fclose($fp);
			return $curl_result;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return false;
		}
	}
	
	public function DownloadFile($url,$file,$timeout=50,$auth)
	{
		try{
			if(!isset($url,$file) or empty($url) or empty($file))
				return false;
			if(file_exists($file))
			{
				set_time_limit(0);
				$fp = fopen ($file, 'w+');
				$ch = curl_init($url);
				if(intval($timeout) > 0)
					curl_setopt($ch, CURLOPT_TIMEOUT, intval($timeout));
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				if(isset($auth) and !empty($auth))
					curl_setopt($ch, CURLOPT_USERPWD, $auth);
				curl_exec($ch);
				if(curl_errno($ch))
					$result = false;
				else
					$result = true;
				curl_close($ch);
				fclose($fp);
				return $result;
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
	
	public function UploadFile($url,$file,$info='',$auth)
	{
		try{
			if(!isset($url,$file) or empty($url) or empty($file))
				return false;
			if(file_exists($file))
			{
				if (function_exists('curl_file_create')) {
				$cFile = curl_file_create($file);
				} else {
				$cFile = '@' . realpath($file);
				}
				$post = array('extra_info' => $info,'file_contents'=> $cFile);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_POST,1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				if(isset($auth) and !empty($auth))
					curl_setopt($ch, CURLOPT_USERPWD, $auth);
				curl_exec ($ch);
				if(curl_errno($ch))
					$result = false;
				else
					$result = true;
				curl_close ($ch);
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
	
	public function getLastError()
	{
		return $this->LastError;
	}
}

if (!function_exists('getallheaders')) 
{
	function getallheaders() {
	$headers = [];
	foreach ($_SERVER as $name => $value) {
		if (substr($name, 0, 5) == 'HTTP_') {
			$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
		}
	}
	return $headers;
	}
}