<?php
// Listener Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class Listener
{
	protected $BasePath;
	protected $RequestMethod;
	protected $Request;
	protected $Tie;
	protected $GetParams;
	protected $PostParams;
	protected $FileParams;
	protected $LastError;
	
	public function __construct()
	{
		if(isset($_REQUEST) and !empty($_REQUEST))
		{
			$this->getRequest();
		}
	}
	
	public function Bind($methods='',$route='',$callback='',$arguments='',$pass=false,$real=false)
	{
		if(isset($methods) and !empty($methods))
		{
			$mthd = $this->getRequestMethod();
			$this->RequestMethod = $mthd;
			if(isset($mthd) and !empty($mthd))
			{
				if(strpos($methods,$mthd) !== false)
				{
					$rqst = $this->getRequest();
					$this->Request = $rqst;
					if(isset($route) and !empty($route))
					{
						if(strpos($route,'*') !== false)
						{
							$route = str_replace('*','',$route);
							if(strpos($rqst,$route) === 0)
							{
								$routex = str_replace($route,'',$rqst);
								if(isset($routex) and !empty($routex))
									$this->Tie = $routex;
								else
									$this->Tie = '';
								if(isset($callback) and !empty($callback))
								{
									if(is_callable($callback))
									{
										if(is_array($arguments) and !empty($arguments))
										{
											try{
												if($pass)
													@ array_unshift($arguments,$routex);
												@ $res = call_user_func_array($callback, $arguments);
												return $res;
											}
											catch(Exception $ex)
											{
												return false;
											}
										}
										else
										{
											try{
												if($pass)
													@ $res = call_user_func($callback, $routex, $arguments);
												else
													@ $res = call_user_func($callback, $arguments);
												return $res;
											}
											catch(Exception $ex)
											{
												return false;
											}
										}
									}
									else
									{
										if(strpos($callback,'@') !== false)
										{
											try{
												$clmtd = explode('@',$callback);
												if(is_array($arguments) and !empty($arguments))
													@ $res = call_user_func_array(array($clmtd[0],$clmtd[1]),$arguments);
												else
													@ $res = call_user_func(array($clmtd[0],$clmtd[1]), $arguments);
												return $res;
											}
											catch(Exception $ex)
											{
												return false;
											}
										}
										else if(is_array($callback))
										{
											try{
												if(is_array($arguments) and !empty($arguments))
													@ $res = call_user_func_array(array($callback[0],$callback[1]),$arguments);
												else
													@ $res = call_user_func(array($callback[0],$callback[1]), $arguments);
												return $res;
											}
											catch(Exception $ex)
											{
												return false;
											}
										}
										return false;
									}
								}
								else
								{
									if($real)
									{
										$rqst = $this->getRequest();
										$req = dirname( __FILE__ ) . $rqst;
										if(is_file($req) and is_dir($req))
										{
											header('location: '.req);
											exit();
										}
										elseif(is_file($req))
										{
											header('location: '.req);
											exit();
										}
										else
										{
											header('location: '.req);
											exit();
										}
									}
									return false;
								}
							}
							else
							{
								return false;
							}
						}
						else
						{
							if($rqst == $route)
							{
								if(isset($callback) and !empty($callback))
								{
									if(is_callable($callback))
									{
										if(is_array($arguments) and !empty($arguments))
										{
											try{
												@ $res = call_user_func_array($callback, $arguments);
												return $res;
											}
											catch(Exception $ex)
											{
												return false;
											}
										}
										else
										{
											try{
												@ $res = call_user_func($callback, $arguments);
												return $res;
											}
											catch(Exception $ex)
											{
												return false;
											}
										}
									}
									else
									{
										if(strpos($callback,'@') !== false)
										{
											try{
												$clmtd = explode('@',$callback);
												if(is_array($arguments) and !empty($arguments))
													@ $res = call_user_func_array(array($clmtd[0],$clmtd[1]),$arguments);
												else
													@ $res = call_user_func(array($clmtd[0],$clmtd[1]), $arguments);
												return $res;
											}
											catch(Exception $ex)
											{
												return false;
											}
										}
										else if(is_array($callback))
										{
											try{
												if(is_array($arguments) and !empty($arguments))
													@ $res = call_user_func_array(array($callback[0],$callback[1]),$arguments);
												else
													@ $res = call_user_func(array($callback[0],$callback[1]), $arguments);
												return $res;
											}
											catch(Exception $ex)
											{
												return false;
											}
										}
										return false;
									}
								}
								else
								{
									if($real)
									{
										$rqst = $this->getRequest();
										$req = dirname( __FILE__ ) . $rqst;
										if(is_file($req) and is_dir($req))
										{
											header('location: '.req);
											exit();
										}
										elseif(is_file($req))
										{
											header('location: '.req);
											exit();
										}
										else
										{
											header('location: '.req);
											exit();
										}
									}
									return false;
								}
							}
						}
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
	
	public function API($EndPoint="",$Callback)
	{
		if(!isset($Callback) or empty($Callback))
			return false;
		if(isset($EndPoint) and !empty($EndPoint))
			return $this->Bind('POST|GET','/api/'.$EndPoint.'/*',$Callback,array('GET'=>$this->GET(),'POST'=>$this->POST(),'FILES'=>$this->FILES()),true,false);
		else
			return $this->Bind('POST|GET','/api/*',$Callback,array('GET'=>$this->GET(),'POST'=>$this->POST(),'FILES'=>$this->FILES()),true,false);
	}
	
	public function GET()
	{
		if(isset($this->GetParams) and !empty($this->GetParams))
			return $this->GetParams;
		else
			return null;
	}
	
	public function POST()
	{
		if(isset($this->PostParams) and !empty($this->PostParams))
			return $this->PostParams;
		else
			return null;
	}
	
	public function FILES()
	{
		if(isset($this->FileParams) and !empty($this->FileParams))
			return $this->FileParams;
		else
			return null;
	}
	
	public function GetRequest()
	{
		@ $this->GetParams = $_GET;
		@ $this->PostParams = $_POST;
		@ $this->FileParams = $_FILES;
		$this->BasePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)).'/';
		$uri = substr($_SERVER['REQUEST_URI'], strlen($this->BasePath));	
		if(strstr($uri, '?') !== false){
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
		return '/'.trim($uri, '/');
	}
	
	public function GetRequestMethod()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_start();
            $method = 'GET';
        }
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->getallheaders();
            if(isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], array('PUT', 'DELETE', 'PATCH'))) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }
        return $method;
    }
	
	public function getallheaders() 
	{
		$headers = [];
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}