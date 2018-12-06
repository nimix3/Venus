<?php
// Listener Class Library V.1 By nikic & mrjgreen & NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// FastRoute - Fast request router for PHP by nikic https://github.com/nikic/FastRoute
// PHRoute - Fast request router for PHP by mrjgreen https://github.com/mrjgreen/phroute
// Special thanks to Levi Morrison, Daniel Lowrey, Blake Mizerany and other contributors.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
use ReflectionClass;
use ReflectionMethod;

class Listener
{
	protected $Router;
	protected $Page404Action;
	protected $Page403Action;
	protected $LastError;
	
	public function __construct()
	{
		$this->Router = new RouteCollector(new RouteParser);
		$this->Page404Action = function() { http_response_code(404); exit(); };
		$this->Page403Action = function() { http_response_code(403); exit(); };
	}
	
	public function __destruct()
	{
		$this->Execute(null,null,function(){});
	}
	
	public function BoilCallBack()
	{
		$args = func_get_args();
		if(!isset($args[0]) or empty($args[0]))
		{
			return function(){};
		}
		return function() use ($args)
		{
			$argz = func_get_args();
			$call = array_shift($args);
			$argv = array_merge($args,$argz);
			if(is_callable($call))
			{
				return call_user_func_array($call,$argv);
			}
			else if(is_array($call))
			{
				return call_user_func_array(array($call[0],$call[1]),$argv);
			}
			else if(strpos($call,'@'))
			{
				$call = explode('@',$call);
				return call_user_func_array(array($call[0],$call[1]),$argv);
			}
			else
			{
				return false;
			}
		};
	}
	
	public function SetPage404Action($callable)
	{
		if(isset($callable) and !empty($callable) and is_callable($callable))
		{
			$this->Page404Action = $callable;
		}
		else
		{
			$this->Page404Action = function() { http_response_code(404); exit(); };
		}
	}
	
	public function SetPage403Action($callable)
	{
		if(isset($callable) and !empty($callable) and is_callable($callable))
		{
			$this->Page403Action = $callable;
		}
		else
		{
			$this->Page403Action = function() { http_response_code(403); exit(); };
		}
	}
	
	public function Bind($methods='',$route='',$callback='',$filters=[])
	{
		if(strpos($methods,"|") !== false)
		{
			$methods = explode("|",$methods);
		}
		$this->Router->addRoute($methods, $route, $callback, $filters);
		return $this;
	}
	
	public function Get($route='',$callback='',$filters=[])
	{
		$this->Router->get($route, $callback, $filters);
		return $this;
	}
	
	public function Head($route='',$callback='',$filters=[])
	{
		$this->Router->head($route, $callback, $filters);
		return $this;
	}
	
	public function Post($route='',$callback='',$filters=[])
	{
		$this->Router->post($route, $callback, $filters);
		return $this;
	}
	
	public function Patch($route='',$callback='',$filters=[])
	{
		$this->Router->patch($route, $callback, $filters);
		return $this;
	}
	
	public function Delete($route='',$callback='',$filters=[])
	{
		$this->Router->delete($route, $callback, $filters);
		return $this;
	}
	
	public function Options($route='',$callback='',$filters=[])
	{
		$this->Router->options($route, $callback, $filters);
		return $this;
	}
	
	public function Any($route='',$callback='',$filters=[])
	{
		$this->Router->any($route, $callback, $filters);
		return $this;
	}
	
	public function Put($route='',$callback='',$filters=[])
	{
		$this->Router->get($route, $callback, $filters);
		return $this;
	}
	
	public function BindStatic($methods='',$route='',$callback='')
	{
		$this->Router->addStaticRoute($methods, $route, $callback);
		return $this;
	}
	
	public function Router()
	{
		return $this->Router;
	}
	
	public function GetRouter()
	{
		return $this->Router;
	}
	
	public function Execute($methods=null,$uri=null,$callable=null)
	{
		$dispatcher = new Dispatcher($this->Router->getData());
		if(!isset($methods) or empty($methods))
			$methods = $_SERVER['REQUEST_METHOD'];
		if(!isset($uri) or empty($uri))
			$uri = $_SERVER['REQUEST_URI'];
		try{
			$response = $dispatcher->dispatch($methods,$uri);
		}
		catch (HttpRouteNotFoundException $e) 
		{
			if(is_callable($this->Page404Action))
				return call_user_func($this->Page404Action);
		}
		catch (HttpMethodNotAllowedException $e)
		{
			if(is_callable($this->Page403Action))
				return call_user_func($this->Page403Action);
		}
		catch(Exception $ex)
		{
			if(is_callable($this->Page403Action))
				return call_user_func($this->Page403Action);
		}
		if(isset($callable) and !empty($callable) and is_callable($callable))
		{
			if(is_callable($callable))
				return $callable($response);
		}
		else
		{
			return $response;
		}
	}
	
	public function GetRequest()
	{
		$this->BasePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)).'/';
		$uri = substr($_SERVER['REQUEST_URI'], strlen($this->BasePath));	
		if(strstr($uri, '?') !== false){
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
		return '/'.trim($uri, '/');
	}
	
	public function GetProtocolSchema()
	{
		return strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
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

class Dispatcher 
{
    private $staticRouteMap;
    private $variableRouteData;
    private $filters;
    private $handlerResolver;
    public $matchedRoute;

    public function __construct(RouteDataInterface $data, HandlerResolverInterface $resolver = null)
    {
        $this->staticRouteMap = $data->getStaticRoutes();
        $this->variableRouteData = $data->getVariableRoutes();
        $this->filters = $data->getFilters();
        if ($resolver === null)
        {
        	$this->handlerResolver = new HandlerResolver();
        }
        else
        {
        	$this->handlerResolver = $resolver;
        }
    }

    public function dispatch($httpMethod, $uri)
    {
        list($handler, $filters, $vars) = $this->dispatchRoute($httpMethod, trim($uri, '/'));
        list($beforeFilter, $afterFilter) = $this->parseFilters($filters);
        if(($response = $this->dispatchFilters($beforeFilter)) !== null)
        {
            return $response;
        }
        $resolvedHandler = $this->handlerResolver->resolve($handler);
        $response = call_user_func_array($resolvedHandler, $vars);
        return $this->dispatchFilters($afterFilter, $response);
    }

    private function dispatchFilters($filters, $response = null)
    {
        while($filter = array_shift($filters))
        {
        	$handler = $this->handlerResolver->resolve($filter);
            if(($filteredResponse = call_user_func($handler, $response)) !== null)
            {
                return $filteredResponse;
            }
        }
        return $response;
    }

    private function parseFilters($filters)
    {        
        $beforeFilter = array();
        $afterFilter = array();
        if(isset($filters[Route::BEFORE]))
        {
            $beforeFilter = array_intersect_key($this->filters, array_flip((array) $filters[Route::BEFORE]));
        }
        if(isset($filters[Route::AFTER]))
        {
            $afterFilter = array_intersect_key($this->filters, array_flip((array) $filters[Route::AFTER]));
        }
        return array($beforeFilter, $afterFilter);
    }

    private function dispatchRoute($httpMethod, $uri)
    {
        if (isset($this->staticRouteMap[$uri]))
        {
            return $this->dispatchStaticRoute($httpMethod, $uri);
        }
        return $this->dispatchVariableRoute($httpMethod, $uri);
    }

    private function dispatchStaticRoute($httpMethod, $uri)
    {
        $routes = $this->staticRouteMap[$uri];
        if (!isset($routes[$httpMethod]))
        {
            $httpMethod = $this->checkFallbacks($routes, $httpMethod);
        }
        return $routes[$httpMethod];
    }

    private function checkFallbacks($routes, $httpMethod)
    {
        $additional = array(Route::ANY);
        if($httpMethod === Route::HEAD)
        {
            $additional[] = Route::GET;
        }
        foreach($additional as $method)
        {
            if(isset($routes[$method]))
            {
                return $method;
            }
        }
        $this->matchedRoute = $routes;
        throw new HttpMethodNotAllowedException('Allow: ' . implode(', ', array_keys($routes)));
    }

    private function dispatchVariableRoute($httpMethod, $uri)
    {
        foreach ($this->variableRouteData as $data) 
        {
            if (!preg_match($data['regex'], $uri, $matches))
            {
                continue;
            }
            $count = count($matches);
            while(!isset($data['routeMap'][$count++]));
            
            $routes = $data['routeMap'][$count - 1];
            if (!isset($routes[$httpMethod]))
            {
                $httpMethod = $this->checkFallbacks($routes, $httpMethod);
            } 
            foreach (array_values($routes[$httpMethod][2]) as $i => $varName)
            {
                if(!isset($matches[$i + 1]) || $matches[$i + 1] === '')
                {
                    unset($routes[$httpMethod][2][$varName]);
                }
                else
                {
                    $routes[$httpMethod][2][$varName] = $matches[$i + 1];
                }
            }
            return $routes[$httpMethod];
        }
        throw new HttpRouteNotFoundException('Route ' . $uri . ' does not exist');
    }
}

class HandlerResolver implements HandlerResolverInterface 
{
	public function resolve ($handler)
	{
		if(is_array($handler) && is_string($handler[0]))
		{
			$handler[0] = new $handler[0];
		}
		return $handler;
	}
}

interface HandlerResolverInterface
{
	public function resolve($handler);
}

class Route
{
    const BEFORE = 'before';
    const AFTER = 'after';
    const PREFIX = 'prefix';
    const ANY = 'ANY';
    const GET = 'GET';
    const HEAD = 'HEAD';
    const POST = 'POST';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    const DELETE = 'DELETE';
    const OPTIONS = 'OPTIONS';
}

class RouteCollector implements RouteDataProviderInterface
{
    const DEFAULT_CONTROLLER_ROUTE = 'index';
    const APPROX_CHUNK_SIZE = 10;
    private $routeParser;
    private $filters = [];
    private $staticRoutes = [];
    private $regexToRoutesMap = [];
    private $reverse = [];
    private $globalFilters = [];
    private $globalRoutePrefix;

    public function __construct(RouteParser $routeParser = null) {
        $this->routeParser = $routeParser ?: new RouteParser();
    }

    public function hasRoute($name) {
        return isset($this->reverse[$name]);
    }

    public function route($name, array $args = null)
    {
        $url = [];
        $replacements = is_null($args) ? [] : array_values($args);
        $variable = 0;
        foreach($this->reverse[$name] as $part)
        {
            if(!$part['variable'])
            {
                $url[] = $part['value'];
            }
            elseif(isset($replacements[$variable]))
            {
                if($part['optional'])
                {
                    $url[] = '/';
                }
                $url[] = $replacements[$variable++];
            }
            elseif(!$part['optional'])
            {
                throw new BadRouteException("Expecting route variable '{$part['name']}'");
            }
        }
        return implode('', $url);
    }

    public function addRoute($httpMethod, $route, $handler, array $filters = [])
	{
        if(is_array($route))
        {
            list($route, $name) = $route;
        }
        $route = $this->addPrefix($this->trim($route));
        list($routeData, $reverseData) = $this->routeParser->parse($route);
        if(isset($name))
        {
            $this->reverse[$name] = $reverseData;
        }
        $filters = array_merge_recursive($this->globalFilters, $filters);
        isset($routeData[1]) ? 
            $this->addVariableRoute($httpMethod, $routeData, $handler, $filters) :
            $this->addStaticRoute($httpMethod, $routeData, $handler, $filters);
        return $this;
    }

    private function addStaticRoute($httpMethod, $routeData, $handler, $filters)
    {
        $routeStr = $routeData[0];
        if (isset($this->staticRoutes[$routeStr][$httpMethod]))
        {
            throw new BadRouteException("Cannot register two routes matching '$routeStr' for method '$httpMethod'");
        }
        foreach ($this->regexToRoutesMap as $regex => $routes) {
            if (isset($routes[$httpMethod]) && preg_match('~^' . $regex . '$~', $routeStr))
            {
                throw new BadRouteException("Static route '$routeStr' is shadowed by previously defined variable route '$regex' for method '$httpMethod'");
            }
        }
        $this->staticRoutes[$routeStr][$httpMethod] = array($handler, $filters, []);
    }

    private function addVariableRoute($httpMethod, $routeData, $handler, $filters)
    {
        list($regex, $variables) = $routeData;
        if (isset($this->regexToRoutesMap[$regex][$httpMethod]))
        {
            throw new BadRouteException("Cannot register two routes matching '$regex' for method '$httpMethod'");
        }
        $this->regexToRoutesMap[$regex][$httpMethod] = [$handler, $filters, $variables];
    }

    public function group(array $filters, \Closure $callback)
    {
        $oldGlobalFilters = $this->globalFilters;
        $oldGlobalPrefix = $this->globalRoutePrefix;
        $this->globalFilters = array_merge_recursive($this->globalFilters, array_intersect_key($filters, [Route::AFTER => 1, Route::BEFORE => 1]));
        $newPrefix = isset($filters[Route::PREFIX]) ? $this->trim($filters[Route::PREFIX]) : null;
        $this->globalRoutePrefix = $this->addPrefix($newPrefix);
        $callback($this);
        $this->globalFilters = $oldGlobalFilters;
        $this->globalRoutePrefix = $oldGlobalPrefix;
    }
    private function addPrefix($route)
    {
        return $this->trim($this->trim($this->globalRoutePrefix) . '/' . $route);
    }

    public function filter($name, $handler)
    {
        $this->filters[$name] = $handler;
    }

    public function get($route, $handler, array $filters = [])
    {
        return $this->addRoute(Route::GET, $route, $handler, $filters);
    }
	
    public function head($route, $handler, array $filters = [])
    {
        return $this->addRoute(Route::HEAD, $route, $handler, $filters);
    }

    public function post($route, $handler, array $filters = [])
    {
        return $this->addRoute(Route::POST, $route, $handler, $filters);
    }

    public function put($route, $handler, array $filters = [])
    {
        return $this->addRoute(Route::PUT, $route, $handler, $filters);
    }

    public function patch($route, $handler, array $filters = [])
    {
        return $this->addRoute(Route::PATCH, $route, $handler, $filters);
    }

    public function delete($route, $handler, array $filters = [])
    {
        return $this->addRoute(Route::DELETE, $route, $handler, $filters);
    }

    public function options($route, $handler, array $filters = [])
    {
        return $this->addRoute(Route::OPTIONS, $route, $handler, $filters);
    }

    public function any($route, $handler, array $filters = [])
    {
        return $this->addRoute(Route::ANY, $route, $handler, $filters);
    }

    public function controller($route, $classname, array $filters = [])
    {
        $reflection = new ReflectionClass($classname);
        $validMethods = $this->getValidMethods();
        $sep = $route === '/' ? '' : '/';
        foreach($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
        {
            foreach($validMethods as $valid)
            {
                if(stripos($method->name, $valid) === 0)
                {
                    $methodName = $this->camelCaseToDashed(substr($method->name, strlen($valid)));
                    $params = $this->buildControllerParameters($method);
                    if($methodName === self::DEFAULT_CONTROLLER_ROUTE)
                    {
                        $this->addRoute($valid, $route . $params, [$classname, $method->name], $filters);
                    }
                    $this->addRoute($valid, $route . $sep . $methodName . $params, [$classname, $method->name], $filters);
                    break;
                }
            }
        }
        return $this;
    }

    private function buildControllerParameters(ReflectionMethod $method)
    {
        $params = '';
        foreach($method->getParameters() as $param)
        {
            $params .= "/{" . $param->getName() . "}" . ($param->isOptional() ? '?' : '');
        }
        return $params;
    }

    private function camelCaseToDashed($string)
    {
        return strtolower(preg_replace('/([A-Z])/', '-$1', lcfirst($string)));
    }

    public function getValidMethods()
    {
        return [
            Route::ANY,
            Route::GET,
            Route::POST,
            Route::PUT,
            Route::PATCH,
            Route::DELETE,
            Route::HEAD,
            Route::OPTIONS,
        ];
    }

    public function getData()
    {
        if (empty($this->regexToRoutesMap))
        {
            return new RouteDataArray($this->staticRoutes, [], $this->filters);
        }
        return new RouteDataArray($this->staticRoutes, $this->generateVariableRouteData(), $this->filters);
    }

    private function trim($route)
    {
        return trim($route, '/');
    }

    private function generateVariableRouteData()
    {
        $chunkSize = $this->computeChunkSize(count($this->regexToRoutesMap));
        $chunks = array_chunk($this->regexToRoutesMap, $chunkSize, true);
        return array_map([$this, 'processChunk'], $chunks);
    }

    private function computeChunkSize($count)
    {
        $numParts = max(1, round($count / self::APPROX_CHUNK_SIZE));
        return ceil($count / $numParts);
    }

    private function processChunk($regexToRoutesMap)
    {
        $routeMap = [];
        $regexes = [];
        $numGroups = 0;
        foreach ($regexToRoutesMap as $regex => $routes) {
            $firstRoute = reset($routes);
            $numVariables = count($firstRoute[2]);
            $numGroups = max($numGroups, $numVariables);
            $regexes[] = $regex . str_repeat('()', $numGroups - $numVariables);
            foreach ($routes as $httpMethod => $route) {
                $routeMap[$numGroups + 1][$httpMethod] = $route;
            }
            $numGroups++;
        }
        $regex = '~^(?|' . implode('|', $regexes) . ')$~';
        return ['regex' => $regex, 'routeMap' => $routeMap];
    }
}

class RouteDataArray implements RouteDataInterface
{
    private $variableRoutes;
    private $staticRoutes;
    private $filters;

    public function __construct(array $staticRoutes, array $variableRoutes, array $filters)
    {
        $this->staticRoutes = $staticRoutes;
        $this->variableRoutes = $variableRoutes;
        $this->filters = $filters;
    }

    public function getStaticRoutes()
    {
        return $this->staticRoutes;
    }

    public function getVariableRoutes()
    {
        return $this->variableRoutes;
    }

    public function getFilters()
    {
        return $this->filters;
    }
}

interface RouteDataProviderInterface
{
    public function getData();
}

interface RouteDataInterface
{
    public function getStaticRoutes();
    public function getVariableRoutes();
    public function getFilters();
}

class RouteParser
{
    const VARIABLE_REGEX = 
"~\{
    \s* ([a-zA-Z0-9_]*) \s*
    (?:
        : \s* ([^{]+(?:\{.*?\})?)
    )?
\}\??~x";
    const DEFAULT_DISPATCH_REGEX = '[^/]+';
    private $parts;
    private $reverseParts;
    private $partsCounter;
    private $variables;
    private $regexOffset;
    private $regexShortcuts = array(
        ':i}'  => ':[0-9]+}',
	':a}'  => ':[0-9A-Za-z]+}',
	':h}'  => ':[0-9A-Fa-f]+}',
        ':c}'  => ':[a-zA-Z0-9+_\-\.]+}'
    );

    public function parse($route)
    {
        $this->reset();
        $route = strtr($route, $this->regexShortcuts);
        if (!$matches = $this->extractVariableRouteParts($route))
        {
            $reverse = array(
                'variable'  => false,
                'value'     => $route
            );
            return [[$route], array($reverse)];
        }
        foreach ($matches as $set) {
            $this->staticParts($route, $set[0][1]);        
            $this->validateVariable($set[1][0]);
            $regexPart = (isset($set[2]) ? trim($set[2][0]) : self::DEFAULT_DISPATCH_REGEX);
            $this->regexOffset = $set[0][1] + strlen($set[0][0]);
            $match = '(' . $regexPart . ')';
            $isOptional = substr($set[0][0], -1) === '?';
            if($isOptional)
            {
                $match = $this->makeOptional($match);
            }
            $this->reverseParts[$this->partsCounter] = array(
                'variable'  => true,
                'optional'  => $isOptional,
                'name'      => $set[1][0]
            );
            $this->parts[$this->partsCounter++] = $match;
        }
        $this->staticParts($route, strlen($route));
        return [[implode('', $this->parts), $this->variables], array_values($this->reverseParts)];
    }

    private function reset()
    {
        $this->parts = array();
        
        $this->reverseParts = array();
    
        $this->partsCounter = 0;
        $this->variables = array();
        $this->regexOffset = 0;
    }
	
    private function extractVariableRouteParts($route)
    {
        if(preg_match_all(self::VARIABLE_REGEX, $route, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER))
        {
            return $matches;
        }
    }

    private function staticParts($route, $nextOffset)
    {
        $static = preg_split('~(/)~u', substr($route, $this->regexOffset, $nextOffset - $this->regexOffset), 0, PREG_SPLIT_DELIM_CAPTURE);
        foreach($static as $staticPart)
        {
            if($staticPart)
            {
                $quotedPart = $this->quote($staticPart);
                $this->parts[$this->partsCounter] = $quotedPart;
                $this->reverseParts[$this->partsCounter] = array(
                    'variable'  => false,
                    'value'     => $staticPart
                );
                $this->partsCounter++;
            }
        }
    }

    private function validateVariable($varName)
    {
        if (isset($this->variables[$varName]))
        {
            throw new BadRouteException("Cannot use the same placeholder '$varName' twice");
        }
        $this->variables[$varName] = $varName;
    }

    private function makeOptional($match)
    {
        $previous = $this->partsCounter - 1;
        if(isset($this->parts[$previous]) && $this->parts[$previous] === '/')
        {
            $this->partsCounter--;
            $match = '(?:/' . $match . ')';
        }
        return $match . '?';
    }

    private function quote($part)
    {
        return preg_quote($part, '~');
    }
}

class BadRouteException extends \LogicException {}
class HttpException extends \Exception {}
class HttpMethodNotAllowedException extends HttpException {}
class HttpRouteNotFoundException extends HttpException {}