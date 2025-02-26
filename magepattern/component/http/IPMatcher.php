<?php
namespace Magepattern\Component\HTTP;
use Magepattern\Component\Debug\Logger;

class IPMatcher
{
    /**
     * @return bool
     */
    private static function isIpv6Enabled():bool
    {
        try {
            if (!defined('AF_INET6')) throw new \Exception('Unable to check Ipv6. Check that PHP was not compiled with option "disable-ipv6".', E_WARNING);
            return true;
        }
        catch (\Exception $e) {
            Logger::getInstance()->log($e);
            return false;
        }
    }

    /**
     * Validates an IP address.
     *
     * @param string $requestIp
     * @param string $ip
     * @return bool True valid, false if not.
     */
    public function checkIp(string $requestIp, string $ip): bool
    {
        $address = $ip;
        $netmask = 128;

        if (str_contains($ip, '/')) {
            list($address, $netmask) = explode('/', $ip, 2);
            if ($netmask < 1 || $netmask > 128) return false;
        }

        if(str_contains($requestIp, ':') && self::isIpv6Enabled()) {
            $bytesAddr = unpack("n*", inet_pton($address));
            $bytesTest = unpack("n*", inet_pton($requestIp));

            for ($i = 1, $ceil = ceil($netmask / 16); $i <= $ceil; $i++) {
                $left = $netmask - 16 * ($i-1);
                $left = ($left <= 16) ? $left : 16;
                $mask = ~(0xffff >> $left) & 0xffff;
                if (($bytesAddr[$i] & $mask) != ($bytesTest[$i] & $mask)) {
                    return false;
                }
            }
            return true;
        }
        else {
            return 0 === substr_compare(sprintf('%032b', ip2long($requestIp)), sprintf('%032b', ip2long($address)), 0, $netmask);
        }
    }
}