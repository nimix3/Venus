<?php
// Network Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class Filter
{
	protected $Options;
	protected $LastError;
	
	public function __construct($UserOptions)
	{
		if(isset($UserOptions) and !empty($UserOptions))
		{
			if(is_array($UserOptions))
				$this->Options = array_merge($this->Options,$UserOptions);
			else
				$this->Options = array_merge($this->Options,array($UserOptions));
		}
		else{
			$this->Options['sanitize_mode'] = SANITIZE_INVALID_STRIP;
			$this->Options['sanitize_extension'] = SANITIZE_EXTENSION_MBSTRING;
			if(!function_exists('mb_substitute_character') or !function_exists('mb_convert_encoding'))
			{
				if(!function_exists('iconv'))
				{
					$this->Options['sanitize_extension'] = SANITIZE_EXTENSION_PHP;	
				}
				else
				{
					$this->Options['sanitize_extension'] = SANITIZE_EXTENSION_ICONV;
				}
			}	
			$this->Options['sanitize_convert_from'] = 'ISO-8859-1';
			$this->Options['sanitize_input_encoding'] = 'UTF-8';
			$this->Options['sanitize_strip_reserved'] = true;
			$this->Options['sanitize_pcre_has_props'] = sanitize_check_pcre_unicode_props();
		}
	}
	
	public function get_isset($key,$default=null,$more=null){ return $this->sanitize($_GET[$key],'isset',$default,$more);  }
	public function get_str($key,$default=null,$more=null){ return $this->sanitize($_GET[$key],'str',$default,$more);  }
	public function get_str_multi($key,$default=null,$more=null){ return $this->sanitize($_GET[$key],'str_multi',$default,$more);  }
	public function get_int32($key,$default=null,$more=null){ return $this->sanitize($_GET[$key],'int32',$default,$more);  }
	public function get_int64($key,$default=null,$more=null){ return $this->sanitize($_GET[$key],'int64',$default,$more);  }
	public function get_html($key,$default=null,$more=null){ return $this->sanitize($_GET[$key],'html',$default,$more);  }
	public function get_bool($key,$default=null,$more=null){ return $this->sanitize($_GET[$key],'bool',$default,$more);  }
	public function get_rx($key,$default=null,$more=null){ return $this->sanitize($_GET[$key],'rx',$default,$more);  }
	public function get_in($key,$default=null,$more=null){ return $this->sanitize($_GET[$key],'in',$default,$more);  }
	public function post_isset($key,$default=null,$more=null){ return $this->sanitize($_POST[$key],'isset',$default,$more);  }
	public function post_str($key,$default=null,$more=null){ return $this->sanitize($_POST[$key],'str',$default,$more);  }
	public function post_str_multi($key,$default=null,$more=null){ return $this->sanitize($_POST[$key],'str_multi',$default,$more);  }
	public function post_int32($key,$default=null,$more=null){ return $this->sanitize($_POST[$key],'int32',$default,$more);  }
	public function post_int64($key,$default=null,$more=null){ return $this->sanitize($_POST[$key],'int64',$default,$more);  }
	public function post_html($key,$default=null,$more=null){ return $this->sanitize($_POST[$key],'html',$default,$more);  }
	public function post_bool($key,$default=null,$more=null){ return $this->sanitize($_POST[$key],'bool',$default,$more);  }
	public function post_rx($key,$default=null,$more=null){ return $this->sanitize($_POST[$key],'rx',$default,$more);  }
	public function post_in($key,$default=null,$more=null){ return $this->sanitize($_POST[$key],'in',$default,$more);  }
	public function request_isset($key, $default=null,$more=null){ return $this->sanitize($_REQUEST[$key],'isset',$default,$more);  }
	public function request_str($key, $default=null,$more=null){ return $this->sanitize($_REQUEST[$key],'str',$default,$more);  }
	public function request_str_multi($key,$default=null,$more=null){ return $this->sanitize($_REQUEST[$key],'str_multi',$default, $more);  }
	public function request_int32($key,$default=null,$more=null){ return $this->sanitize($_REQUEST[$key],'int32',$default,$more);  }
	public function request_int64($key,$default=null,$more=null){ return $this->sanitize($_REQUEST[$key],'int64',$default,$more);  }
	public function request_html($key,$default=null,$more=null){ return $this->sanitize($_REQUEST[$key],'html',$default,$more);  }
	public function request_bool($key,$default=null,$more=null){ return $this->sanitize($_REQUEST[$key],'bool',$default,$more);  }
	public function request_rx($key,$default=null,$more=null){ return $this->sanitize($_REQUEST[$key],'rx',$default,$more);  }
	public function request_in($key,$default=null,$more=null){ return $this->sanitize($_REQUEST[$key],'in',$default,$more);  }
	
	public function sanitize($input, $type, $default=null, $more=null){
		if ($type == 'isset') return isset($input);
		if (!isset($input)) return $default;
		switch ($type){
			case 'str':
				return sanitize_string($input, false);
			case 'str_multi':
				return sanitize_string($input, true);
			case 'int32':
				return sanitize_int32($input);
			case 'int64':
				return sanitize_int64($input);
			case 'html':
				return null;
			case 'bool':
				return $input ? true : false;
			case 'rx':
				if (preg_match($more, $input)) return $input;
				return $default;
			case 'in':
				foreach ($more as $match){
					if ($input === $match){
						return $input;
					}
				}
				return $default;
		}
		return null;
	}

	private function sanitize_string($input, $allow_newlines){
		if (!is_string($input)) $input = "$input";
		if ($this->Options['sanitize_input_encoding'] != 'UTF-8'){
			$input = sanitize_convert_string($input, $this->Options['sanitize_input_encoding'], 'UTF-8');
		}else{
			$test = sanitize_convert_string($input, 'UTF-8', 'UTF-8');
			if ($test != $input){
				switch ($this->Options['sanitize_mode']){
					case SANITIZE_INVALID_THROW:
						throw new Exception('Sanitize found invalid input');
					case SANITIZE_INVALID_CONVERT:
						$input = sanitize_convert_string($input, $this->Options['sanitize_convert_from']);
						break;
					case SANITIZE_INVALID_STRIP:
						$input = $test;
						break;
					default:
						$this->LastError[] = 'Unknown sanitize mode';
						return;
				}
			}
		}
		$rx = '[\x00-\x08]|[\x0E-\x1F]|\x7F|\xC2[\x80-\x84\x86-\x9F]|\xEF\xBB\xBF|\xE2\x81[\xAA-\xAF]|\xEF\xBF[\xB9-\xBA]|\xF3\xA0[\x80-\x81][\x80-\xBF]|\xED[\xA0-\xBF][\x80-\xBF]|\xf4[\x90-\xbf][\x80-\xbf][\x80-\xbf]'; # |\p{Cn}
		$input = preg_replace('!'.$rx.'!', '', $input);
		if ($this->Options['sanitize_strip_reserved']){
			if ($this->Options['sanitize_pcre_has_props']){
				$input = preg_replace('!\p{Cn}!u', '', $input);
			}else{
				$this->LastError[] = 'PCRE has not been compiled with unicode property support. Try disabling sanitize_strip_reserved';
				return null;
			}
		}else{
			$rx = '((\xF4\x8F|\xEF|\xF0\x9F|\xF0\xAF|\xF0\xBF|((\xF1|\xF2|\xF3)(\x8F|\x9F|\xAF|\xBF)))\xBF(\xBE|\xBF))|\xEF\xB7[\x90-\xAF]';
			$input = preg_replace('!'.$rx.'!', '', $input);
		}
		$lf = $allow_newlines ? "\n" : " ";
		$ff = $allow_newlines ? "\n\n" : " ";
		$map = array(
			"\xE2\x80\xA8"	=> $lf,
			"\xE2\x80\xA9"	=> $ff,
			"\xC2\x85"	=> $lf,
			"\x09"		=> " ",
			"\x0B"		=> $ff,
			"\x0C"		=> $ff,
			"\r\n"		=> $lf,
			"\r"		=> $lf,
			"\n"		=> $lf,
			"\xEF\xBF\xBC"	=> '?',
			"\xEF\xBF\xBD"	=> '?',
		);
		$input = str_replace(array_keys($map), $map, $input);
		return $input;
	}
	
	private function sanitize_convert_string($input, $from){
		switch ($this->Options['sanitize_extension']){
			case SANITIZE_EXTENSION_PHP:
				if ($from == 'ISO-8859-1'){
					return utf8_encode($input);
				}
				if ($from == 'UTF-8'){
					return sanitize_clean_utf8($input);
				}
				$this->LastError[] = 'Pure PHP sanitize can only convert from ISO-8859-1';
				return;
			case SANITIZE_EXTENSION_MBSTRING:
				if (!function_exists('mb_substitute_character')) {$this->LastError[] = 'NO-MBSTRING-SUPPORT'; return null;}
				if (!function_exists('mb_convert_encoding')) {$this->LastError[] = 'NO-MBSTRING-SUPPORT'; return null;}
				if ($from == 'UTF-8'){
					mb_substitute_character('long');
					return mb_convert_encoding(sanitize_strip_overlong($input), 'UTF-8', 'UTF-8');
				}
				mb_substitute_character(0xFFFD);
				return mb_convert_encoding($input, 'UTF-8', $from);
			case SANITIZE_EXTENSION_ICONV:
				if (!function_exists('iconv')) {$this->LastError[] = 'NO-ICONV-SUPPORT'; return null;}
				return substr(@iconv($from, 'UTF-8//IGNORE', sanitize_strip_overlong($input).'XXXX'), 0, -4);
		}
		$this->LastError[] = "Unknown sanitize extension";
		return null;
	}

	private function sanitize_strip_overlong($input){
		return preg_replace('![\xC0-\xC1\xF5-\xFF]|\xE0[\x80-\x9F][\x80-\xbf]|\xF0[\x80-\x8F][\x80-\xBF][\x80-\xBF]!', '', $input);
	}

	private function sanitize_clean_utf8($data){
		$rx = '';
		$rx .= '([\xC0-\xC1\xF5-\xFF])';
		$rx .= '|([\xC0-\xDF](?=[^\x80-\xBF]|$))';
		$rx .= '|([\xE0-\xEF](?=[\x80-\xBF]{0,1}([^\x80-\xBF]|$)))';
		$rx .= '|([\xF0-\xF7](?=[\x80-\xBF]{0,2}([^\x80-\xBF]|$)))';
		$rx .= '|((?<=[\x00-\x7F]|^)[\x80-\xBF]+)';
		$rx .= '|((?<=[\xC0-\xDF][\x80-\xBF]{1})[\x80-\xBF]+)';
		$rx .= '|((?<=[\xE0-\xEF][\x80-\xBF]{2})[\x80-\xBF]+)';
		$rx .= '|((?<=[\xF0-\xF7][\x80-\xBF]{3})[\x80-\xBF]+)';
		$rx .= '|(\xE0[\x80-\x9F])';
		$rx .= '|(\xF0[\x80-\x8F])';
		return preg_replace("!$rx!s", '', preg_replace("!$rx!s", '', $data));
	}

	private function sanitize_int32($input, $complain=false){
		$r = intval($input);
		if ($r == 2147483647 && $complain){
			return null;
		}
		return $r;
	}

	private function sanitize_int64($input){
		if (preg_match('!^(\d+)!', $input, $m)){
			return $m[1];
		}
		return 0;
	}

	private function sanitize_check_pcre_unicode_props(){
		if (@preg_match('!\p{Ll}!', 'hello')){
			return true;
		}
		return false;
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}
define('SANITIZE_INVALID_STRIP',1);
define('SANITIZE_INVALID_THROW',2);
define('SANITIZE_INVALID_CONVERT',3);
define('SANITIZE_EXTENSION_PHP',1);
define('SANITIZE_EXTENSION_MBSTRING',2);
define('SANITIZE_EXTENSION_ICONV',3);