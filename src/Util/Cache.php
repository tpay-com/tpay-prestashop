<?php

namespace Tpay\Util;

final class Cache
{
    public static function set(string $key, string $value, int $ttl = 3600): void
    {
        $file = self::getCacheDir() . md5($key);
        $ttl += time();
        $data = base64_encode(serialize(['ttl' => $ttl, 'data' => $value]));

        file_put_contents($file, $data);
    }

    public static function get(string $key, $default = null): ?string
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

    public static function erase()
    {
        foreach (glob(self::getCacheDir() . '*') as $file) {
            if ($file === self::getCacheDir()) {
                continue;
            }

            unlink($file);
        }
    }

    private static function getCacheDir(): string
    {
        if (defined('_PS_CACHE_DIR_')) {
            return _PS_CACHE_DIR_;
        }

        return _PS_ROOT_DIR_ . '/var/cache/' . _PS_ENV_ . '/';
    }
}
