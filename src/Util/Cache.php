<?php

namespace Tpay\Util;

final class Cache
{
    public const CACHE_DIR = __DIR__ . '/../../cache/';

    public static function set(string $key, string $value, int $ttl = 3600): void
    {
        $file = self::CACHE_DIR . md5($key);
        $ttl += time();
        $data = base64_encode(serialize(['ttl' => $ttl, 'data' => $value]));

        file_put_contents($file, $data);
    }

    public static function get(string $key, $default = null): ?string
    {
        $file = self::CACHE_DIR . md5($key);

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
        foreach (glob(self::CACHE_DIR . '*') as $file) {
            if ($file === self::CACHE_DIR) {
                continue;
            }

            unlink($file);
        }
    }
}
