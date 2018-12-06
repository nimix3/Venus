<?php
// Template Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// We use Lex syetem to build this library that is a lightweight template parser under MIT license.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class Template
{
    protected $allowPhp = false;
    protected $regexSetup = false;
    protected $scopeGlue = '.';
    protected $tagRegex = '';
    protected $cumulativeNoparse = false;
    protected $inCondition = false;
    protected $variableRegex = '';
    protected $variableLoopRegex = '';
    protected $variableTagRegex = '';
    protected $callbackTagRegex = '';
    protected $callbackLoopTagRegex = '';
    protected $noparseRegex = '';
    protected $conditionalRegex = '';
    protected $conditionalElseRegex = '';
    protected $conditionalEndRegex = '';
    protected $conditionalData = array();
    protected static $extractions = array(
        'noparse' => array(),
    );
    protected static $data = null;
    protected static $callbackData = array();
	protected $LastError;
	protected $Hashes;
	protected $tFile;
	protected $Strings;
	
	public function __construct()
	{
		ob_start();
	}
	
	public function Show($text, $data = array(), $callback = false, $allowPhp = false)
	{
		ob_clean();
		echo $this->View($text,$data,$callback,$allowPhp);
		ob_flush();
	}
	
	public function ShowFile($filename, $data = array(), $callback = false, $allowPhp = false)
	{
		ob_clean();
		if(file_exists($filename))
			echo $this->ViewFile($filename,$data,$callback,$allowPhp);
		else if(file_exists($filename.".qp"))
			echo $this->ViewFile($filename.".qp",$data,$callback,$allowPhp);
		else if(file_exists(dirname( __FILE__ )."/../../".$filename))
		    echo $this->ViewFile(dirname( __FILE__ )."/../../".$filename,$data,$callback,$allowPhp);
		else
		    echo $this->ViewFile(dirname( __FILE__ )."/../../".$filename.".qp",$data,$callback,$allowPhp);
		ob_flush();
	}
	
	public function ViewAllFile($filename, $orig_text, $callback)
	{
		if(file_exists($filename))
			$text = file_get_contents($filename);
		else
			return '';
		return $this->ViewAll($text, $data, $callback, $allowPhp);
	}
	
	public function ViewFile($filename, $data = array(), $callback = false, $allowPhp = false)
	{
		if(file_exists($filename))
			$text = file_get_contents($filename);
		else
			return '';
		return $this->View($text, $data, $callback, $allowPhp);
	}
	
    public function View($text, $data = array(), $callback = false, $allowPhp = false)
    {
        $this->setupRegex();
        $this->allowPhp = $allowPhp;
        if (is_object($data)) {
            $data = $this->toArray($data);    
        }
        if (self::$data === null) {
            self::$data = $data;
        } else {
            $data = array_merge(self::$data, $data);
            self::$callbackData = $data;
        }
        if (! $allowPhp) {
            $text = str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $text);
        }
        $text = $this->parseComments($text);
        $text = $this->extractNoparse($text);
        $text = $this->extractLoopedTags($text, $data, $callback);
        $text = $this->parseConditionals($text, $data, $callback);
        $text = $this->injectExtractions($text, 'looped_tags');
        $text = $this->parseVariables($text, $data, $callback);
        $text = $this->injectExtractions($text, 'callback_blocks');
        if ($callback) {
            $text = $this->parseCallbackTags($text, $data, $callback);
        }
        if (! $this->cumulativeNoparse) {
            $text = $this->injectExtractions($text);
        }
        return $text;
    }

    public function parseComments($text)
    {
        $this->setupRegex();
        return preg_replace('/\{\{#.*?#\}\}/s', '', $text);
    }

    public function parseVariables($text, $data, $callback = null)
    {
        $this->setupRegex();
        if (preg_match_all($this->variableLoopRegex, $text, $data_matches, PREG_SET_ORDER + PREG_OFFSET_CAPTURE)) {
            foreach ($data_matches as $index => $match) {
                if ($loop_data = $this->getVariable($match[1][0], $data)) {
                    $looped_text = '';
                    if (is_array($loop_data) or ($loop_data instanceof \IteratorAggregate)) {
                        foreach ($loop_data as $item_data) {
                            $str = $this->extractLoopedTags($match[2][0], $item_data, $callback);
                            $str = $this->parseConditionals($str, $item_data, $callback);
                            $str = $this->injectExtractions($str, 'looped_tags');
                            $str = $this->parseVariables($str, $item_data, $callback);
                            if ($callback !== null) {
                                $str = $this->parseCallbackTags($str, $item_data, $callback);
                            }
                            $looped_text .= $str;
                        }                        
                    }
                    $text = preg_replace('/'.preg_quote($match[0][0], '/').'/m', addcslashes($looped_text, '\\$'), $text, 1);
                } else { 
                    $text = $this->createExtraction('callback_blocks', $match[0][0], $match[0][0], $text);
                }
            }
        }
        if (preg_match_all($this->variableTagRegex, $text, $data_matches)) {
            foreach ($data_matches[1] as $index => $var) {
                if (($val = $this->getVariable($var, $data, '__lex_no_value__')) !== '__lex_no_value__') {
                    $text = str_replace($data_matches[0][$index], $val, $text);
                }
            }
        }
        return $text;
    }
	
    public function parseCallbackTags($text, $data, $callback)
    {
        $this->setupRegex();
        $inCondition = $this->inCondition;
        if ($inCondition) {
            $regex = '/\{\s*('.$this->variableRegex.')(\s+.*?)?\s*\}/ms';
        } else {
            $regex = '/\{\{\s*('.$this->variableRegex.')(\s+.*?)?\s*(\/)?\}\}/ms';
        }
        while (preg_match($regex, $text, $match, PREG_OFFSET_CAPTURE)) {
            $selfClosed = false;
            $parameters = array();
            $tag = $match[0][0];
            $start = $match[0][1];
            $name = $match[1][0];
            if (isset($match[2])) {
                $cb_data = $data;
                if ( !empty(self::$callbackData)) {
                    $data = $this->toArray($data);
                    $cb_data = array_merge(self::$callbackData, $data);
                }
                $raw_params = $this->injectExtractions($match[2][0], '__cond_str');
                $parameters = $this->parseParameters($raw_params, $cb_data, $callback);
            }
            if (isset($match[3])) {
                $selfClosed = true;
            }
            $content = '';
            $temp_text = substr($text, $start + strlen($tag));
            if (preg_match('/\{\{\s*\/'.preg_quote($name, '/').'\s*\}\}/m', $temp_text, $match, PREG_OFFSET_CAPTURE) && ! $selfClosed) {
                $content = substr($temp_text, 0, $match[0][1]);
                $tag .= $content.$match[0][0];
                $nested_regex = '/\{\{\s*('.preg_quote($name, '/').')(\s.*?)\}\}(.*?)\{\{\s*\/\1\s*\}\}/ms';
                if (preg_match($nested_regex, $content.$match[0][0], $nested_matches)) {
                    $nested_content = preg_replace('/\{\{\s*\/'.preg_quote($name, '/').'\s*\}\}/m', '', $nested_matches[0]);
                    $content = $this->createExtraction('nested_looped_tags', $nested_content, $nested_content, $content);
                }
            }
            $replacement = call_user_func_array($callback, array($name, $parameters, $content));
            $replacement = $this->ViewAll($replacement, $content, $callback);
            if ($inCondition) {
                $replacement = $this->valueToLiteral($replacement);
            }
            $text = preg_replace('/'.preg_quote($tag, '/').'/m', addcslashes($replacement, '\\$'), $text, 1);
            $text = $this->injectExtractions($text, 'nested_looped_tags');
        }
        return $text;
    }

    public function parseConditionals($text, $data, $callback)
    {
        $this->setupRegex();
        preg_match_all($this->conditionalRegex, $text, $matches, PREG_SET_ORDER);
        $this->conditionalData = $data;
        foreach ($matches as $match) {
            $this->inCondition = true;
            $condition = $match[2];
            if (preg_match_all('/(["\']).*?(?<!\\\\)\1/', $condition, $str_matches)) {
                foreach ($str_matches[0] as $m) {
                    $condition = $this->createExtraction('__cond_str', $m, $m, $condition);
                }
            }
            $condition = preg_replace($this->conditionalNotRegex, '$1!$2', $condition);
            if (preg_match_all($this->conditionalExistsRegex, $condition, $existsMatches, PREG_SET_ORDER)) {
                foreach ($existsMatches as $m) {
                    $exists = 'true';
                    if ($this->getVariable($m[2], $data, '__doesnt_exist__') === '__doesnt_exist__') {
                        $exists = 'false';
                    }
                    $condition = $this->createExtraction('__cond_exists', $m[0], $m[1].$exists.$m[3], $condition);
                }
            }
            $condition = preg_replace_callback('/\b('.$this->variableRegex.')\b/', array($this, 'processConditionVar'), $condition);
            if ($callback) {
                $condition = preg_replace('/\b(?!\{\s*)('.$this->callbackNameRegex.')(?!\s+.*?\s*\})\b/', '{$1}', $condition);
                $condition = $this->parseCallbackTags($condition, $data, $callback);
            }
            if (preg_match_all('/(["\']).*?(?<!\\\\)\1/', $condition, $str_matches)) {
                foreach ($str_matches[0] as $m) {
                    $condition = $this->createExtraction('__cond_str', $m, $m, $condition);
                }
            }
            $this->inCondition = false;
            $condition = preg_replace_callback('/\b('.$this->variableRegex.')\b/', array($this, 'processConditionVar'), $condition);
            $this->inCondition = true;
            $condition = $this->injectExtractions($condition, '__cond_str');
            $condition = $this->injectExtractions($condition, '__cond_exists');
            $conditional = '<?php ';
            if ($match[1] == 'unless') {
                $conditional .= 'if ( ! ('.$condition.'))';
            } elseif ($match[1] == 'elseunless') {
                $conditional .= 'elseif ( ! ('.$condition.'))';
            } else {
                $conditional .= $match[1].' ('.$condition.')';
            }
            $conditional .= ': ?>';
            $text = preg_replace('/'.preg_quote($match[0], '/').'/m', addcslashes($conditional, '\\$'), $text, 1);
        }
        $text = preg_replace($this->conditionalElseRegex, '<?php else: ?>', $text);
        $text = preg_replace($this->conditionalEndRegex, '<?php endif; ?>', $text);
        $text = $this->parsePhp($text);
        $this->inCondition = false;
        return $text;
    }

    public function ViewAll($text, $orig_text, $callback)
    {
        if (preg_match($this->recursiveRegex, $text, $match)) {
            $array_key = $match[1];
            $tag = $match[0];
            $next_tag = null;
            $children = self::$callbackData[$array_key];
            $child_count = count($children);
            $count = 1;
            if ($child_count == count($children, COUNT_RECURSIVE)) {
                $children = array($children);
                $child_count = 1;
            }
            foreach ($children as $child) {
                $has_children = true;
                $child = $this->toArray($child);
                if ( ! array_key_exists($array_key, $child)) {
                    $child[$array_key] = array();
                    $has_children = false;
                }
                $replacement = $this->View($orig_text, $child, $callback, $this->allowPhp);
                $current_tag = ($next_tag !== null) ? $next_tag : $tag;
                $next_tag = ($count == $child_count) ? '' : md5($tag.$replacement);
                $text = str_replace($current_tag, $replacement.$next_tag, $text);
                if ($has_children) {
                    $text = $this->ViewAll($text, $orig_text, $callback);
                }
                $count++;
            }
        }
        return $text;
    }

    public function scopeGlue($glue = null)
    {
        if ($glue !== null) {
            $this->regexSetup = false;
            $this->scopeGlue = $glue;
        }
        return $this->scopeGlue;
    }

    public function cumulativeNoparse($mode)
    {
        $this->cumulativeNoparse = $mode;
    }

    public static function injectNoparse($text)
    {
        if (isset(self::$extractions['noparse'])) {
            foreach (self::$extractions['noparse'] AS $hash => $replacement) {
                if (strpos($text, "noparse_{$hash}") !== FALSE) {
                    $text = str_replace("noparse_{$hash}", $replacement, $text);
                }
            }
        }
        return $text;
    }

    protected function processConditionVar($match)
    {
        $var = is_array($match) ? $match[0] : $match;
        if (in_array(strtolower($var), array('true', 'false', 'null', 'or', 'and')) or
            strpos($var, '__cond_str') === 0 or
            strpos($var, '__cond_exists') === 0 or
            is_numeric($var))
        {
            return $var;
        }
        $value = $this->getVariable($var, $this->conditionalData, '__processConditionVar__');
        if ($value === '__processConditionVar__') {
            return $this->inCondition ? $var : 'null';
        }
        return $this->valueToLiteral($value);
    }

    protected function processParamVar($match)
    {
        return $match[1].$this->processConditionVar($match[2]);
    }

    protected function valueToLiteral($value)
    {
        if (is_object($value) and is_callable(array($value, '__toString'))) {
            return var_export((string) $value, true);
        } elseif (is_array($value)) {
            return !empty($value) ? "true" : "false";
        } else {
            return var_export($value, true);
        }
    }

    protected function setupRegex()
    {
        if ($this->regexSetup) {
            return;
        }
        $glue = preg_quote($this->scopeGlue, '/');
        $this->variableRegex = $glue === '\\.' ? '[a-zA-Z0-9_'.$glue.']+' : '[a-zA-Z0-9_\.'.$glue.']+';
        $this->callbackNameRegex = $this->variableRegex.$glue.$this->variableRegex;
        $this->variableLoopRegex = '/\{\{\s*('.$this->variableRegex.')\s*\}\}(.*?)\{\{\s*\/\1\s*\}\}/ms';
        $this->variableTagRegex = '/\{\{\s*('.$this->variableRegex.')\s*\}\}/m';
        $this->callbackBlockRegex = '/\{\{\s*('.$this->variableRegex.')(\s.*?)\}\}(.*?)\{\{\s*\/\1\s*\}\}/ms';
        $this->recursiveRegex = '/\{\{\s*\*recursive\s*('.$this->variableRegex.')\*\s*\}\}/ms';
        $this->noparseRegex = '/\{\{\s*noparse\s*\}\}(.*?)\{\{\s*\/noparse\s*\}\}/ms';
        $this->conditionalRegex = '/\{\{\s*(if|unless|elseif|elseunless)\s*((?:\()?(.*?)(?:\))?)\s*\}\}/ms';
        $this->conditionalElseRegex = '/\{\{\s*else\s*\}\}/ms';
        $this->conditionalEndRegex = '/\{\{\s*endif\s*\}\}/ms';
        $this->conditionalExistsRegex = '/(\s+|^)exists\s+('.$this->variableRegex.')(\s+|$)/ms';
        $this->conditionalNotRegex = '/(\s+|^)not(\s+|$)/ms';
        $this->regexSetup = true;
        @ ini_set('pcre.backtrack_limit', 1000000);
    }

    protected function extractNoparse($text)
    {
        if (preg_match_all($this->noparseRegex, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $text = $this->createExtraction('noparse', $match[0], $match[1], $text);
            }
        }
        return $text;
    }

    protected function extractLoopedTags($text, $data = array(), $callback = null)
    {
        if (preg_match_all($this->callbackBlockRegex, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if ($this->parseParameters($match[2], $data, $callback)) {
                    $text = $this->createExtraction('callback_blocks', $match[0], $match[0], $text);
                } else {
                    $text = $this->createExtraction('looped_tags', $match[0], $match[0], $text);
                }
            }
        }
        return $text;
    }

    protected function createExtraction($type, $extraction, $replacement, $text)
    {
        $hash = md5($replacement);
        self::$extractions[$type][$hash] = $replacement;
        return str_replace($extraction, "{$type}_{$hash}", $text);
    }

    protected function injectExtractions($text, $type = null)
    {
        if ($type === null) {
            foreach (self::$extractions as $type => $extractions) {
                foreach ($extractions as $hash => $replacement) {
                    if (strpos($text, "{$type}_{$hash}") !== false) {
                        $text = str_replace("{$type}_{$hash}", $replacement, $text);
                        unset(self::$extractions[$type][$hash]);
                    }
                }
            }
        } else {
            if ( ! isset(self::$extractions[$type])) {
                return $text;
            }
            foreach (self::$extractions[$type] as $hash => $replacement) {
                if (strpos($text, "{$type}_{$hash}") !== false) {
                    $text = str_replace("{$type}_{$hash}", $replacement, $text);
                    unset(self::$extractions[$type][$hash]);
                }
            }
        }
        return $text;
    }

    protected function getVariable($key, $data, $default = null)
    {
        if (strpos($key, $this->scopeGlue) === false) {
            $parts = explode('.', $key);
        } else {
            $parts = explode($this->scopeGlue, $key);
        }
        foreach ($parts as $key_part) {
            if (is_array($data)) {
                if ( ! array_key_exists($key_part, $data)) {
                    return $default;
                }
                $data = $data[$key_part];
            } elseif (is_object($data)) {
                if ( ! isset($data->{$key_part})) {
                    return $default;
                }
                $data = $data->{$key_part};
            } else {
                return $default;
            }
        }
        return $data;
    }

    protected function parsePhp($text)
    {
        ob_start();
        $result = eval('?>'.$text.'<?php ');
        if ($result === false) {
            /*$output = 'You have a syntax error in your tags. The offending code: ';*/
            /*throw new ParsingException($output.str_replace(array('?>', '<?php '), '', $text));*/
			$this->LastError[] = "You have a syntax error in your codes.";
				return null;
        }
        return ob_get_clean();
    }

    protected function parseParameters($parameters, $data, $callback)
    {
        $this->conditionalData = $data;
        $this->inCondition = true;
        if (preg_match_all('/(["\']).*?(?<!\\\\)\1/', $parameters, $str_matches)) {
            foreach ($str_matches[0] as $m) {
                $parameters = $this->createExtraction('__param_str', $m, $m, $parameters);
            }
        }
        $parameters = preg_replace_callback(
            '/(.*?\s*=\s*(?!__))('.$this->variableRegex.')/is',
            array($this, 'processParamVar'),
            $parameters
        );
        if ($callback) {
            $parameters = preg_replace('/(.*?\s*=\s*(?!\{\s*)(?!__))('.$this->callbackNameRegex.')(?!\s*\})\b/', '$1{$2}', $parameters);
            $parameters = $this->parseCallbackTags($parameters, $data, $callback);
        }
        $parameters = $this->injectExtractions($parameters, '__param_str');
        $this->inCondition = false;
        if (preg_match_all('/(.*?)\s*=\s*(\'|"|&#?\w+;)(.*?)(?<!\\\\)\2/s', trim($parameters), $matches)) {
            $return = array();
            foreach ($matches[1] as $i => $attr) {
                $return[trim($matches[1][$i])] = stripslashes($matches[3][$i]);
            }
            return $return;
        }
        return array();
    }

    public function toArray($data = array())
    {
        if ($data instanceof ArrayableInterface) {
            $data = $data->toArray();
        }
        is_array($data) or $data = (array) $data;
        if (is_array($data)) {
            $data = array_change_key_case($data, CASE_LOWER);
        }
        return $data;
    }
	
	public function TemplateEngine($Hashes=null,$Strings=null) 
	{
		if(!isset($Hashes) or empty($Hashes))
			$Hashes = $this->Hashes;
		if(!isset($Strings) or empty($Strings))
			$Strings = $this->Strings;
		if(is_array($Hashes) and !empty($Strings))
		{
			try{
				$string = $Strings;
				$string = preg_replace('/\{\{#.*?#\}\}/s', '',$string);
				foreach($Hashes as $ind => $val){
					$string = str_replace('{{'.$ind.'}}',$val,$string);
				}   
				$string = preg_replace('/\{\{(.*?)\}\}/is','',$string);
				return $string;
			}
			catch(Exception $ex)
			{
				$this->LastError[] = $ex->getMessage();
				return null;
			}
		}
		$this->LastError[] = "Variables invalid";
		return null;
	}
	
	public function ChangeHash($newHash)
	{
		if(isset($newHash) and !empty($newHash))
		{
			if(is_array($newHash))
				$this->Hashes = $newHash;
			else
				return false;
		}
		else
		{
			return false;
		}
	}
	
	public function ChangeStrings($newStrings)
	{
		if(isset($newStrings) and !empty($newStrings))
		{
			$this->Strings = $newStrings;
		}
		else
		{
			return false;
		}
	}
	
	public function ChangeFile($newFile)
	{
		if(isset($newFile) and !empty($newFile))
		{
			$this->tFile = $newFile;
		}
		else
		{
			return false;
		}
	}

	public function TemplateParser($Hashes=null,$tFile=null) 
	{
		if(!isset($Hashes) or empty($Hashes))
			$Hashes = $this->Hashes;
		if(!isset($tFile) or empty($tFile))
			$tFile = $this->tFile;
		if(is_file($tFile) and file_exists($tFile))
		{
			try{
				$string = file_get_contents($tFile);
				if (isset($string) and !empty($string)) {
					$string = $this->TemplateEngine($string,$hash);
					return $string;
				}
				else
				{
					$this->LastError[] = "Empty Data";
					return null;
				}
			}
			catch(Exception $ex)
			{
				$this->LastError[] = $ex->getMessage();
				return null;
			}
		}
		$this->LastError[] = "File invalid";
		return null;
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}

class ParsingException extends \Exception {}

class ArrayableObjectExample implements ArrayableInterface
{
	private $attributes = array('foo' => 'bar');
	public function toArray()
	{
		return $this->attributes;
	}
}

interface ArrayableInterface 
{
	public function toArray();
}