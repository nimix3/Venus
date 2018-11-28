<?php
// ServerEvent Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class ServerEvent
{
	protected $Input;
	protected $Delay = 1000;
	protected $LastError;
	
	public function __construct($Delay)
	{
		@ ob_clean();
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');
		if(isset($Delay) and !empty($Delay))
		{
			$this->Delay = $Delay;
		}
		@ $this->Input = file_get_contents("php://input");
	}
	
	public function Run($Callable)
	{
		while(true) 
		{
			try{
				$output = call_user_func_array($Callable, array($this->Input));
				if(isset($output) and !empty($output))
				{
					echo $output;
				}
				else if($output = "end")
				{
					ob_flush();
					flush();
					break;
				}
				ob_flush();
				flush();
				if(intval($this->Delay) > 0)
					usleep(intval($this->Delay));
			}
			catch(Exception $ex)
			{
				$this->LastError[] = $ex->getMessage();
			}
		}
		ob_flush();
		flush();
		exit();
	}

	public function getLastError()
	{
		return $this->LastError;
	}
}
?>