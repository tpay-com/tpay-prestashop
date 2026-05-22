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

@author Krajowy Integrator Płatności S.A.*/

namespace Tpay\Util;

final class Cache
{
    public static function set(string $key, string $value, int $ttl = 3600): bool
    {
        $file = self::getCacheDir() . md5($key);
        $ttl += time();
        $data = base64_encode(serialize(['ttl' => $ttl, 'data' => $value]));

        return (bool) file_put_contents($file, $data);
    }

    public static function get(string $key, $default = null)
    {
        $file = self::getCacheDir() . md5($key);

        if (file_exists($file)) {
            $data = unserialize(base64_decode(file_get_contents($file)));

            if ($data['ttl'] > time()) {
                return $data['data'];
            }

            unlink($file);
        }

        return $default ?: null;
    }

    public static function delete(string $key): bool
    {
        $file = self::getCacheDir() . md5($key);
        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    public static function erase()
    {
        foreach (glob(self::getCacheDir() . '*') as $file) {
            if ($file === self::getCacheDir()) {
                continue;
            }

            unlink($file);
        }

        return true;
    }

    private static function getCacheDir(): string
    {
        if (defined('_PS_CACHE_DIR_')) {
            return _PS_CACHE_DIR_;
        }

        return _PS_ROOT_DIR_ . '/var/cache/' . _PS_ENV_ . '/';
    }
}
