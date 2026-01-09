<?php

namespace Tpay\Util;

final class Cache
{
    public static function set(string $key, string $value, int $ttl = 3600): bool
    {
        $file = self::getCacheDir().md5($key);
        $ttl += time();
        $data = base64_encode(serialize(['ttl' => $ttl, 'data' => $value]));

        return (bool) file_put_contents($file, $data);
    }

    public static function get(string $key, $default = null)
    {
        $file = self::getCacheDir().md5($key);

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
        $file = self::getCacheDir().md5($key);
        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    public static function erase()
    {
        foreach (glob(self::getCacheDir().'*') as $file) {
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

        return _PS_ROOT_DIR_.'/var/cache/'._PS_ENV_.'/';
    }
}
