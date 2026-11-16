<?php

namespace App\Support;

use App\Models\Setting;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Optional "office network only" rule for Team check-in / check-out.
 * Allowed addresses are kept in the setting `office_ips`
 * (one IP or range such as 192.168.10.0/24 per line or comma separated).
 * An empty setting means there is no restriction.
 */
class OfficeNetwork
{
    public const SETTING = 'office_ips';

    /** @return array<int, string> */
    public static function parse(?string $text): array
    {
        $parts = preg_split('/[\s,;]+/', (string) $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique($parts));
    }

    /** True when a single entry is a valid IP or CIDR range. */
    public static function validEntry(string $entry): bool
    {
        if (filter_var($entry, FILTER_VALIDATE_IP)) {
            return true;
        }

        if (! str_contains($entry, '/')) {
            return false;
        }

        [$ip, $bits] = explode('/', $entry, 2);

        if (! filter_var($ip, FILTER_VALIDATE_IP) || ! ctype_digit($bits)) {
            return false;
        }

        $max = str_contains($ip, ':') ? 128 : 32;

        return (int) $bits <= $max;
    }

    public static function allowed(?string $ip, ?string $setting = null): bool
    {
        $ranges = self::parse($setting ?? Setting::get(self::SETTING));

        if ($ranges === []) {
            return true;
        }

        if (! $ip) {
            return false;
        }

        return IpUtils::checkIp($ip, $ranges);
    }
}
