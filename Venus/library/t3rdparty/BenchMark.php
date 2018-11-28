<?php
// BenchMark Class Library V.1 By devster and edited by NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// Micro PHP benchmark library https://github.com/devster/ubench have No License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\t3rdparty;
class BenchMark
{
    protected $start_time;
    protected $end_time;
    protected $memory_usage;

    public function Start()
    {
        $this->start_time = microtime(true);
    }

    public function End()
    {
        if (!$this->HasStarted())
        {
            throw new LogicException("You must call start()");
        }
        $this->end_time = microtime(true);
        $this->memory_usage = memory_get_usage(true);
    }

    public function GetTime($raw = false, $format = null)
    {
        if (!$this->HasStarted())
        {
            throw new LogicException("You must call start()");
        }
        if (!$this->HasEnded())
        {
            throw new LogicException("You must call end()");
        }
        $elapsed = $this->end_time - $this->start_time;
        return $raw ? $elapsed : self::ReadableElapsedTime($elapsed, $format);
    }

    public function GetMemoryUsage($raw = false, $format = null)
    {
        return $raw ? $this->memory_usage : self::ReadableSize($this->memory_usage, $format);
    }

    public function GetMemoryPeak($raw = false, $format = null)
    {
        $memory = memory_get_peak_usage(true);
        return $raw ? $memory : self::ReadableSize($memory, $format);
    }

    public function Run(callable $callable)
    {
        $arguments = func_get_args();
        array_shift($arguments);
        $this->Start();
        $result = call_user_func_array($callable, $arguments);
        $this->End();
        return $result;
    }

    public static function ReadableSize($size, $format = null, $round = 3)
    {
        $mod = 1024;
        if (is_null($format)) {
            $format = '%.2f%s';
        }
        $units = explode(' ','B Kb Mb Gb Tb');
        for ($i = 0; $size > $mod; $i++) {
            $size /= $mod;
        }
        if (0 === $i) {
            $format = preg_replace('/(%.[\d]+f)/', '%d', $format);
        }
        return sprintf($format, round($size, $round), $units[$i]);
    }

    public static function ReadableElapsedTime($microtime, $format = null, $round = 3)
    {
        if (is_null($format)) {
            $format = '%.3f%s';
        }
        if ($microtime >= 1) {
            $unit = 's';
            $time = round($microtime, $round);
        } else {
            $unit = 'ms';
            $time = round($microtime*1000);
            $format = preg_replace('/(%.[\d]+f)/', '%d', $format);
        }
        return sprintf($format, $time, $unit);
    }
	
    public function HasEnded()
    {
        return isset($this->end_time);
    }
	
    public function HasStarted()
    {
        return isset($this->start_time);
    }
}