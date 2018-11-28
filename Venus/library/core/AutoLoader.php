<?php
// AutoLoader Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class AutoLoader
{
	protected $LastError;
	
	public function __construct($reg=false)
	{
		if(boolval($reg))
		{
			$this->registerAutoLoader();
		}
	}
	
	public function DirList($dir='/')
	{
		if(!isset($dir) or empty($dir))
			return null;
		if(!is_dir($dir))
			return null;
		$allhere = array_slice(scandir($dir), 2);
		if(isset($allhere) and !empty($allhere))
		{
			if(is_array($allhere))
			{
				$calls = array();
				foreach($allhere as $element)
				{
					$element = $dir."/".$element;
					if(is_dir($element) and file_exists($element))
					{
						try{
							$calls[] = $element;
						}
						catch(Exception $ex){
							$this->LastError[] = $ex->getMessage();
						}
					}
				}
				if(isset($calls) and !empty($calls))
					return $calls;
				else
					return null;
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
	
	public function execList($dir='/',$ext='php')
	{
		if(!isset($ext) or empty($ext))
			$ext = 'php';
		if(!isset($dir) or empty($dir))
			return null;
		if(!is_dir($dir))
			return null;
		$allhere = array_slice(scandir($dir), 2);
		if(isset($allhere) and !empty($allhere))
		{
			if(is_array($allhere))
			{
				$calls = array();
				foreach($allhere as $element)
				{
					$element = $dir."/".$element;
					if(is_file($element) and file_exists($element))
					{
						if(pathinfo($element)['extension'] == $ext)
						{
							try{
								$calls[] = $element;
							}
							catch(Exception $ex){
								$this->LastError[] = $ex->getMessage();
							}
						}
					}
				}
				if(isset($calls) and !empty($calls))
					return $calls;
				else
					return null;
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
	
	public function execLoad($dir='/',$loadtype='REQUIRE')
	{
		if(!isset($dir) or empty($dir))
			return false;
		if(!is_dir($dir))
			return false;
		$allhere = array_slice(scandir($dir), 2);
		if(isset($allhere) and !empty($allhere))
		{
			if(is_array($allhere))
			{
				$calls = 0;
				foreach($allhere as $element)
				{
					$element = $dir."/".$element;
					if(is_file($element) and file_exists($element))
					{
						
						if(pathinfo($element)['extension'] == 'php')
						{
							try{
								if($loadtype == 'REQUIRE')
									require_once($element);
								else
									include_once($element);
								$calls++;
							}
							catch(Exception $ex){
								$this->LastError[] = $ex->getMessage();
							}
						}
					}
				}
				if($calls)
					return true;
				else
					return false;
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
	
    public static function registerAutoLoader()
    {
        spl_autoload_register(function ($class) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, str_replace('Venus\\','',$class)).'.php';
			if (file_exists($file)) {
				try{
                require_once($file);
                return true;
				}
				catch(Exception $ex)
				{
					$this->LastError[] = $ex->getMessage();
					return false;
				}
            }
            return false;
        });
    }
	
	public function getLastError()
	{
		return $this->LastError;
	}
}
?>