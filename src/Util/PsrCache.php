<?php
/**MIT License

Copyright (c) 2026 Krajowy Integrator Płatności S.A.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

@author Krajowy Integrator Płatności S.A.
@copyright Krajowy Integrator Płatności S.A.*/

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
