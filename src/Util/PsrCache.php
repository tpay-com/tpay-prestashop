<?php

namespace Tpay\Util;

use Psr\SimpleCache\CacheInterface;

class PsrCache implements CacheInterface
{
    public function get($key, $default = null)
    {
        return unserialize(Cache::get($key, $default));
    }

    public function set($key, $value, $ttl = null)
    {
        return Cache::set($key, serialize($value), (int) $ttl);
    }

    public function delete($key)
    {
        return Cache::delete($key);
    }

    public function clear()
    {
        return Cache::erase();
    }

    public function getMultiple($keys, $default = null)
    {
        $elements = [];
        foreach ($keys as $key) {
            $elements[] = $this->get($key, $default);
        }

        return $elements;
    }

    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            if (false === $this->set($key, $value, $ttl)) {
                return false;
            }
        }

        return true;
    }

    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            if (false === $this->delete($key)) {
                return false;
            }
        }

        return true;
    }

    public function has($key)
    {
        return null !== $this->get($key);
    }
}
