<?php
namespace Magepattern\Component\HTTP;
use Magepattern\Component\Debug\Logger;
use Magepattern\Component\Tool\RSATool;

class Session extends Utils
{
    /**
     * @var bool $ssl
     */
	protected bool
        $ssl;

    /**
     * Session constructor.
     * @param bool $ssl
     */
	public function __construct(bool $ssl = false)
    {
		$this->ssl = $ssl;
	}

	/**
	 * Regenerate the session id and update the session
	 * @param int $lifetime
	 * @return string
	 */
	public function regenerate(int $lifetime = 0): string
    {
		$cookie_params = session_get_cookie_params();
		session_regenerate_id();
		$id = session_id();
		$session_name = session_name();
		session_write_close();
		session_name($session_name);
		session_id($id);
		session_start();
		setcookie($session_name,$id,$lifetime,'/',$cookie_params['domain'],$this->ssl,true);
		return $id;
	}

	/**
	 * Start a new session
	 * @param string $session_name
	 * @param array $params
	 * @return string|false session id, false on error
	 */
    public function start(string $session_name = 'mp_default_s', array $params = []): string|false
    {
		try {
			if (is_string($session_name) && $session_name !== '') {
				$ssid = session_id();

				if (!isset($_SESSION)) session_cache_limiter('nocache');

				//Fermeture de la première session, ses données sont sauvegardées.
				if(session_name() !== $session_name || !empty($params)) {
					session_write_close();

					// **PREVENTING SESSION FIXATION**
					// Session ID cannot be passed through URLs
					ini_set('session.use_only_cookies', 1);

					$cookie_params = array_merge(session_get_cookie_params(),$params);
					session_set_cookie_params($cookie_params['lifetime'], '/', $cookie_params['domain'], $this->ssl, true);

					if(!isset($_COOKIE[$session_name])) {
						session_name($session_name);
						ini_set('session.hash_function',1);
						session_start();
						session_regenerate_id();
						$ssid = session_id();
					}
					else {
						$ssid = $_COOKIE[$session_name];
						session_name($session_name);
						session_id($ssid);
						session_start();
					}
				}
				return $ssid;
			}
			else {
				throw new \Exception('Unable to start a new session. No session name defined',E_WARNING);
			}
		}
		catch(\Exception $e) {
            Logger::getInstance()->log($e);
			return false;
		}
    }

	/**
	 * Reset a session
	 */
    public function destroy()
    {
    	session_unset();
    	$_SESSION = [];
        session_destroy();
        session_start();
    }

	/**
	 * Close a session
	 * @param string $session_name
	 */
    public function close(string $session_name)
    {
    	session_name($session_name);
		session_start();
		session_unset();
		session_destroy();
		$CookieInfo = session_get_cookie_params();
		if ( (empty($CookieInfo['domain'])) && (empty($CookieInfo['secure'])) ) setcookie($session_name, '', time()-3600, $CookieInfo['path']);
		elseif (empty($CookieInfo['secure'])) setcookie($session_name, '', time()-3600, $CookieInfo['path'], $CookieInfo['domain']);
		else setcookie($session_name, '', time()-3600, $CookieInfo['path'], $CookieInfo['domain'], $CookieInfo['secure']);
    }

    /**
     * Creation of token
     * @param string $name
     * @param string $token
     * @return string
     */
    public function token(string $name = 'token', string $token = ''): string
    {
        return !Request::isSession($name) || empty($_SESSION[$name]) ? ($_SESSION[$name] = $token ?: RSATool::tokenID()) : $_SESSION[$name];
    }

    /**
     * Init session variables
     * @param array $session
     * @internal param bool $debug
     */
    private function iniSessionVar(array $session) {
        $_SESSION = array_merge($_SESSION, $session);
    }

    /**
     * Init session ou renew the session
     * @param $session_tabs
     * @param bool $setOption
     * @internal param array $session
     * @internal param bool $debug
     */
    public function run(array $session_tabs = [])
    {
        if(!empty($session_tabs)) $this->iniSessionVar($session_tabs);
    }

    /**
     * @param string $requestIp
     * @return string|bool
     */
    public function ip(string $requestIp = ''): string|bool
    {
        $ip = parent::getIp();
        $matcher = new IPMatcher();
        return $matcher->checkIp($requestIp ?? $ip,$ip) ? $ip : false;
    }

    /**
     * @return string browser
     */
    public function browser(): string
    {
        return parent::getBrowser();
    }
}