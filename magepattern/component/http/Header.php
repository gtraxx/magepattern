<?php
namespace Magepattern\Component\HTTP;

class Header
{
    /**
     * @var array
     */
    protected static array $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    ];

    /**
     * @param int $code
     * @return string
     */
    public static function getStatusText(int $code): string
    {
        return self::$statusTexts[$code] ?? '';
    }

    /**
     * @param int $code
     */
    public static function setStatus(int $code)
    {
        header('HTTP/1.1 '.$code.' '.self::$statusTexts[$code]);
    }

    /**
     * @param string $cache
     */
    public static function cache_control(string $cache)
    {
        $control = match($cache) {
            'nocache' => ['no-store', 'no-cache', 'max-age=0', 'must-revalidate'],
            'public', 'private' => ['public', 'must-revalidate']
        };
        header('Cache-Control: '.implode(',',$control));
    }

    /**
     * @param string $type
     * @param string $charset
     */
    public static function content_type(string $type, string $charset = 'UTF-8')
    {
        $content = match($type) {
            'text' => 'text/plain',
            'html' => 'text/html',
            'json' => 'application/json',
            'excel' => 'application/vnd.ms-excel',
            'excel2007' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        };
        header('Content-type: '.$content.'; charset='.$charset);
    }

    /**
     * @param string $charset
     */
	public static function set_json_headers(string $charset = 'UTF-8')
	{
	    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	    header('Last-Modified: '.gmdate("D, d M Y H:i:s").'GMT');
        header("Pragma: no-cache" );
		self::cache_control("nocache");
		self::setStatus(200);
		self::content_type('json',$charset);
	}

	/**
	 * @param string $origin
	 * @param array $validOrigins
	 * @param bool $redirect
	 */
    public static function amp_headers(string $origin, array $validOrigins, $redirect = false){
        header('AMP-Same-Origin: true');
        header('Access-Control-Allow-Origin: '.(in_array($origin,$validOrigins) ? $origin : implode(' ',$validOrigins)));
        header('AMP-Access-Control-Allow-Source-Origin: '.$origin);
        header('Access-Control-Expose-Headers: AMP-Access-Control-Allow-Source-Origin'.($redirect ? ', AMP-Redirect-To' : ''));
		if($redirect) {
			$headers = getallheaders();
			header('AMP-Redirect-To: '.($redirect === true ? $headers['Referer'] : $redirect));
		}
    }
}