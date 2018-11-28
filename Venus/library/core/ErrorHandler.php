<?php
// ErrorHandler Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
use Throwable;
class ErrorHandler
{
    protected $additionnalLines = 5;
    protected $geshiInstance;
    protected $counter = 1;

    protected $handlerType;
    protected $message;
    protected $code = '';
    protected $file;
    protected $line = 0;
    protected $type;
	
	protected $debug = true;
	protected $mute = false;
	protected $log = true;
	protected $technicalEmail;
	protected $sendEmail = array(E_ERROR, E_USER_ERROR, E_USER_WARNING, E_WARNING);

    public function __construct($debug=true, $log=true, $mute=false, $technicalEmail=null)
    {
		if($debug == false)
			$this->debug = false;
		if($mute == true)
			$this->mute = true;
		if($log == false)
			$this->log = false;
		if(strpos($technicalEmail, '@'))
			$this->technicalEmail = $technicalEmail;
        set_error_handler([$this, 'errors']);
        set_exception_handler([$this, 'exceptions']);
    }

    public function errors($errNum, $errMsg, $errFile, $errLine)
    {
        $this->handlerType = 'error';
        $this->message = $errMsg;
        $this->file = $errFile;
        $this->type = $this->getErrorType($errNum);
        $this->line = $errLine;
		if($this->log)
			@ file_put_contents("error_log",$this->handlerType.": ".$this->type."  ".$this->message." on file ".$this->file." at line ".$this->line.PHP_EOL,FILE_APPEND);
		if(in_array($errNum, $this->sendEmail) and !$this->mute)
			@ $this->sendTechnicalEmail($errNum, $errMsg, $errFile, $errLine);
		if($this->debug)
			@ $this->display([]);
    }

    public function exceptions(Throwable $e)
    {
        $this->handlerType = 'exception';
        $this->message = $e->getMessage();
        $this->file = $e->getFile();
        $this->type = get_class($e);
        $this->line = $e->getLine();
		if($this->log)
			@ file_put_contents("error_log",$this->handlerType.": ".$this->type."  ".$this->message." on file ".$this->file." at line ".$this->line.PHP_EOL,FILE_APPEND);
		if(in_array(E_ERROR, $this->sendEmail) and !$this->mute)
			@ $this->sendTechnicalEmail(E_ERROR, $this->message, $this->file, $this->line);
		if($this->debug)
			@ $this->display($e->getTrace());
    }

    private function display(array $trace)
    {
        if (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        $this->code = self::trace($this->line, $this->file);
        $this->HeaderTemplate($this->handlerType,$this->type,$this->message);
        $this->BodyTemplate($this->counter,$this->file,$this->line,$this->code);
        if (!empty($trace)) {
            foreach ($trace as $e) {
                $e = (object)$e;
                $this->message = '';
				if(isset($e->file) and !empty($e->file))
					$this->file = $e->file;
				if(isset($e->line) and !empty($e->line))
					$this->line = $e->line;
                $this->code = $this->trace($this->line, $this->file);
                $this->counter++;
                $this->BodyTemplate($this->counter,$this->file,$this->line,$this->code);
            }
        }
        $this->FooterTemplate();
        ob_end_flush();
        exit;
    }

    private function trace($line, $file)
    {
        try {
            $fileContents = file($file);
            $source = '';
            for ($x = ($line - $this->additionnalLines - 1); $x < ($line + $this->additionnalLines); $x++) {
                if (!empty($fileContents[$x])) {
                    $source .= $fileContents[$x];
                }
            }
            return $this->highlight($line, $source);
        } catch (Throwable $e) {
            return '';
        }
    }

    private function highlight($line, $source, $useGeshi = false)
    {
		if($useGeshi)
			@ $this->geshiInstance = new \GeSHi;
        if (null != $this->geshiInstance) {
			$this->geshiInstance->set_language('php');
			$this->geshiInstance->set_header_type(GESHI_HEADER_NONE);
			$this->geshiInstance->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
			$this->geshiInstance->set_source($source);
			$this->geshiInstance->highlight_lines_extra([$this->additionnalLines + 1]);
			$this->geshiInstance->set_highlight_lines_extra_style('background-color: #FCFFBF; border: 1px solid red;');
			$this->geshiInstance->start_line_numbers_at($line - $this->additionnalLines);
			return $this->geshiInstance->parse_code();
		}
		else
		{
			$startline = $line - $this->additionnalLines;
			if($startline > 2)
				$startline -=2;
			return $this->highlight_php("<?php\n...\n".$source."\n...\n?>",$startline);
		}
    }

    protected function getErrorType($errNum)
    {
        $types = [
            1     => 'E_ERROR',
            2     => 'E_WARNING',
            4     => 'E_PARSE',
            8     => 'E_NOTICE',
            16    => 'E_CORE_ERROR',
            32    => 'E_CORE_WARNING',
            64    => 'E_COMPILE_ERROR',
            128   => 'E_COMPILE_WARNING',
            256   => 'E_USER_ERROR',
            512   => 'E_USER_WARNING',
            1024  => 'E_USER_NOTICE',
            2048  => 'E_STRICT',
            4096  => 'E_RECOVERABLE_ERROR',
            8192  => 'E_DEPRECATED',
            16384 => 'E_USER_DEPRECATED',
            32767 => 'E_ALL',
        ];
        return isset($types[$errNum]) ? $types[$errNum] : 'unknown error';
    }
	
	protected function sendTechnicalEmail($errNum, $errMsg, $errFile=false, $errLine=false)
	{
		if(strpos($this->technicalEmail, '@'))
		{
			$message = "Your website has generated an unexpected error:
			Error: $errNum";
			if($errFile)
				$message = $message . "
			File: $errFile";
			if($errLine)
				$message = $message . "
			Line: $errLine";
			$message = $message . "
			Message: $errMsg";
			$send = @ mail($this->technicalEmail, "Error on your website", $message, "From: NoReply@ErrorHandler.Venus");
			return $send;
		}
	}
	
	protected function FooterTemplate()
	{
		@ print(
		'</div>
		</body>
		</html>'
		);
	}
	
	protected function BodyTemplate($counter,$file,$line,$code)
	{
		@ print('<div class="stackTrace">
					<h2>Stack Trace</h2>
				<div class="trace">
					<div class="info">
						<div class="counter">'.$counter.'</div>
						<div class="fileinfo">'.$file.', line '.$line.'</div>
					</div>
					'.$code.'
				</div>
			</div>
		');
	}
	
	protected function HeaderTemplate($handlerType,$type,$message)
	{
		@ print('
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<meta name="description" content="Venus Framework | Errors">
			<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
			<title>Venus Framework | '.ucwords($handlerType).'</title>
			<style>
				body {
					margin: 20px 0;
					background-color: #EEEEEE;
					font: 13px/1.231 arial, helvetica, clean, sans-serif;
					font-family: "Myriad Pro", "Segoe UI", Helvetica, Arial, sans-serif;
				}
		
				h1, h2, h3, p {
					margin: 10px;
				}
		
				#container {
					margin: auto;
					max-width: 950px;
				}
		
				#message {
					margin-bottom: 30px;
					background: #E9DDDD none;
					padding: 5px;
					border: 3px solid red;
				}
		
				#message h1 {
					color: #a34b4b;
					font-size: 167%;
					font-weight: normal;
					font-style: normal;
				}
		
				.counter {
					background-color: gray;
					color: #FFFFFF;
					display: table-cell;
					vertical-align: middle;
					font-weight: bold;
					padding: 8px 12px;
				}
		
				.stackTrace {
					padding-bottom: 12px;
				}
		
				.stackTrace .info {
					border-bottom: 1px solid #9c9c9c;
					box-shadow: 0 3px 3px rgba(0, 0, 0, 0.2);
					background-color: #cecece;
				}
		
				.stackTrace h1 {
					color: gray;
					padding-top: 10px;
					font-size: 146.5%;
				}
		
				.stackTrace .fileinfo {
					display: table-cell;
					padding: 8px;
				}
		
				.stackTrace .trace {
					padding-bottom: 4px;
					background-color: #FAFAFA;
					border: 1px solid #9c9c9c;
				}
		
				.stackTrace .trace p {
					color: gray;
					font-size: .8em;
				}
				
				ol {
					overflow: auto;
					padding-left: 44px;
					padding-right: 40px;
				}
			</style>
		</head>
		<body>
		<div id="container">
			<div id="message">
				<h1>'.strtoupper($handlerType).': '.$type.'</h1>
		
				<h2>'.$message.'</h2>
			</div>
		');
	}
	
	private function highlight_php($string,$start=1)
	{
		$Line = explode("\n",$string);
		$line = "";
		for($i=$start;$i<=($start+count($Line));$i++)
		{
			$line .= "&nbsp;".$i."&nbsp;<br>";
		}
		ob_start();
		highlight_string($string);
		$Code=ob_get_contents();
		ob_end_clean();
		$header='<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-style: solid; border-width:1px; border-color: white black black white">
			<!--<tr>
			 <td width="100%" colspan="2"  style="border-style: solid; border-width:1px; border-color: white; background-color: #99ccff; font-family:Arial; color:white; font-weight:bold;">PHP Code:</td>
			</tr>-->
			<tr>
			<td width="3%" valign="top" style="background-color: #99ccff; border-style: solid; border-width:1px; border-color: white;"><code>'.$line.'</code></td>
			<td width="100%" valign="top" style="background-color: white;"><div style="white-space: nowrap; overflow: auto;"><code>';
		$footer=$Code.'</div></code></td>
			</tr>
		</table>';
		return $header.$footer;
	}
}
?>