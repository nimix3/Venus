<?php
// Session Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class Session
{
	protected $LastError;
	
	public function __construct()
	{
		try{
			return session_start();
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
		}
	}
		
	public function InitSession()
	{
		try{
			return session_start();
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return null;
		}
	}
	
	public function DestroySession($full = true)
	{
		try{
			if((bool)$full)
			{
				session_unset();
				session_destroy(); 
			}
			else
			{
				session_unset();
			}
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return null;
		}
	}
	
	public function SessionID()
	{
		try{
			if(session_start())
				return session_id();
			return null;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return null;
		}
	}
	
	public function RevokeSession()
	{
		try{
			return session_regenerate_id();
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return null;
		}
	}
	
	public function SessionSetData($data)
	{
		try{
			if(is_array($data))
			{
				$_SESSION = $data;
				return true;
			}
			return false;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return null;
		}
	}
	
	public function SessionGetData()
	{
		try{
			if(isset($_SESSION) and !empty($_SESSION))
			{
				return $_SESSION;
			}
			return null;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return null;
		}
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}
?>