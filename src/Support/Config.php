<?php
namespace illmy\Yhholiday\Support;

class Config
{
    protected  $config;

    public function __construct(array $config = []) 
    {
        $this->config = $config;
    }

    public function get($key,$default = null)
    {
        $config = $this->config;

        if (isset($config[$key])) {
            return $config[$key];
        }

        if (!empty($default)) {
            return $default;
        }

        return $config;
    }
    
}