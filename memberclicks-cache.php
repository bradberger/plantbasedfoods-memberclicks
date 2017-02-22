<?php

class MemberclicksCache
{

    static $expiration;

    public function __construct($expiration = 0)
    {
        $this->expiration = $expiration;
        $dir = plugin_dir_path(__FILE__).'cache/';
        if(!is_dir($dir)) {
            @mkdir($dir);
        }
    }

    public function get($key, $fromJSON = true)
    {
        $fn = $this->getFileName($key);
        if (file_exists($fn) && (!$this->expiration || filemtime($fn)+$this->expiration < time())) {
            $value = @file_get_contents($fn);
            if (!$value) {
                return null;
            }
            return $fromJSON ? json_decode($value) : $value;
        }
        return null;
    }

    public function clear()
    {
        foreach(glob($this->getFileName('*')) as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function set($key, $value, $encodeToJSON = true)
    {
        if ($encodeToJSON) {
            $value = json_encode($value);
        }
        $fn = $this->getFileName($key);
        return @file_put_contents($fn, $value, LOCK_EX);
    }

    public function delete($key)
    {
        return @unlink($this->getFileName($key));
    }

    private function getFileName($key) {
        return plugin_dir_path(__FILE__).'cache/'.$key;
    }
}
