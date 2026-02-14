<?php

namespace Magepattern\Component\Tool;

/**
 * Class ClientTool
 * Utilitaires pour l'identification technique du client (IP, Navigateur, OS).
 * Remplace l'ancienne classe Utils.
 */
class ClientTool
{
    /**
     * Récupère l'adresse IP du client (Support Proxy & Cloudflare).
     */
    public static function getIp(): string
    {
        // Ordre de priorité pour traverser les proxys
        $headers = [
            'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'
        ];

        foreach ($headers as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    // On valide que c'est une IP publique
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Détermine le nom du navigateur (Version optimisée sans browscap).
     */
    public static function getBrowser(?string $userAgent = null): string
    {
        $agent = $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (str_contains($agent, 'OPR/') || str_contains($agent, 'Opera')) return 'Opera';
        if (str_contains($agent, 'Edg'))    return 'Edge';
        if (str_contains($agent, 'Chrome')) return 'Chrome';
        if (str_contains($agent, 'Safari')) return 'Safari';
        if (str_contains($agent, 'Firefox')) return 'Firefox';
        if (str_contains($agent, 'MSIE') || str_contains($agent, 'Trident/7')) return 'Internet Explorer';

        if (self::isBot($agent)) return 'Bot';

        return 'Other';
    }

    /**
     * Détermine le système d'exploitation.
     */
    public static function getOs(?string $userAgent = null): string
    {
        $agent = $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (preg_match('/linux/i', $agent)) return 'Linux';
        if (preg_match('/macintosh|mac os x/i', $agent)) return 'Mac';
        if (preg_match('/windows|win32/i', $agent)) return 'Windows';
        if (preg_match('/android/i', $agent)) return 'Android';
        if (preg_match('/iphone|ipad|ipod/i', $agent)) return 'iOS';

        return 'Unknown';
    }

    /**
     * Vérifie si le client est un robot (Crawler/Spider).
     */
    public static function isBot(?string $userAgent = null): bool
    {
        $agent = strtolower($userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? '');
        $bots = ['bot', 'crawl', 'slurp', 'spider', 'mediapartners', 'facebook', 'whatsapp', 'telegram', 'google'];

        foreach ($bots as $bot) {
            if (str_contains($agent, $bot)) return true;
        }

        return false;
    }
}