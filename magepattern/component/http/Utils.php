<?php
namespace Magepattern\Component\HTTP;
use Magepattern\Component\Debug\Logger;
use Magepattern\Component\Tool\StringTool;

class Utils
{
    /**
     * function register real IP
     * @return string
     */
    public static function getIp(): string
    {
        if (isset($_SERVER)) return $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
        else return getenv('HTTP_CLIENT_IP') ?? getenv('HTTP_X_FORWARDED_FOR') ?? getenv('REMOTE_ADDR');
    }

    /**
     * Return the browser name of the user agent given
     * @param string $user_agent
     * @return string
     */
    public static function get_browser_name(string $user_agent): string
    {
        $browser = "Other";
        if (StringTool::str_search($user_agent,['Nav','Gold','X11','Mozilla','Netscape'])
            && StringTool::str_search($user_agent,['MSIE','Konqueror','Firefox','Safari'],false))
            $browser = "Netscape";
        elseif (StringTool::str_search($user_agent,['Opera','OPR/'])) $browser = "Opera";
        elseif (str_contains($user_agent, 'Edge')) $browser = 'Edge';
        elseif (str_contains($user_agent, 'Chrome')) $browser = 'Chrome';
        elseif (str_contains($user_agent, "Safari")) $browser = "Safari";
        elseif (str_contains($user_agent, "Firefox")) $browser = "Firefox";
        elseif (StringTool::str_search($user_agent,['MSIE','Trident/7'])) $browser = "IE";
        elseif (str_contains($user_agent, "Lynx")) $browser = "Lynx";
        elseif (str_contains($user_agent, "WebTV")) $browser = "WebTV";
        elseif (str_contains($user_agent, "Konqueror")) $browser = "Konqueror";
        elseif ((stripos($user_agent, "bot")) || (str_contains($user_agent, "Google")) ||
            (str_contains($user_agent, "Slurp")) || (str_contains($user_agent, "Scooter")) ||
            (stripos($user_agent, "Spider")) || (stripos($user_agent, "Infoseek")))
            $browser = "Bot";

        return $browser;
    }

    /**
     * Return the current browser
     * @return string
     */
    public static function getBrowser(): string
    {
        //@Todo The best is to use the get_browser function of php, need browscap set in the php.ini
        try {
            if(ini_get('browscap')) {
                $browserInfos = get_browser(null, true);
                $browser = $browserInfos['browser'];
            }
            else {
                throw new \Exception('browscap ini not defined',E_NOTICE);
            }
        }
        catch (\Exception $e) {
            Logger::getInstance()->log($e);
            $browser = self::get_browser_name($_SERVER['HTTP_USER_AGENT']);
        }

        return $browser;
    }
}