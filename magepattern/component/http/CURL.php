<?php
namespace Magepattern\Component\HTTP;
use Magepattern\Component\Debug\Logger,
    Magepattern\Component\Tool\StringTool;

class CURL
{
    /**
     * @return bool
     */
    private function curl_exist(): bool
    {
        try {
            if(extension_loaded('curl')) throw new \Exception('curl extension not loaded',E_WARNING);
            return true;
        }
        catch (\Exception $e){
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }

    /**
     * @param string $url
     * @param array $data
     * @param array $options
     * @param string|array $files
     * @param bool $debug
     * @return bool|string
     */
    private function executeCurl(string $url, array $data = [], array $options = [], string|array $files = [], bool $debug = false) : bool|string
    {
        try {
            if($this->curl_exist()) {
                if(!StringTool::isURL($url)) throw new \Exception('url passed is not valid',E_WARNING);

                $curlopt = [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true
                ];
                if(!empty($files)) {
                    $postfield = [];
                    foreach($data['files'] as $name => $file) {
                        $postfield[$name] = new \CURLFile($file);
                    }
                    $curlopt = [CURLOPT_POSTFIELDS => $postfield];
                }
                if($data['ssl']) $curlopt[CURLOPT_SSL_VERIFYPEER] = false;

                $ch = curl_init();
                curl_setopt_array($ch, array_merge($options, $curlopt));
                $response = curl_exec($ch);
                $curlInfo = curl_getinfo($ch);
                curl_close($ch);

                if(curl_errno($ch)) throw new \Exception('Curl error occurs',E_ERROR);

                if ($debug) {
                    var_dump($curlInfo);
                    var_dump($response);
                }

                if(isset($data['$directory']) && isset($data['status'])) {
                    if((($data['status'] === 0 && $curlInfo['httpCode'] < 400) || $data['status'] === $curlInfo['httpCode']) && !file_exists($data['$directory'])) {
                        $fp = fopen($data['$directory'],'wb');
                        fwrite($fp, $response);
                        fclose($fp);
                    }
                    else {
                        return false;
                    }
                }
                elseif ($curlInfo['http_code'] === 200 && $response) return $response;
            }
            return false;
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);            return false;
        }
    }

    /**
     * @param string $url
     * @param bool $ssl
     * @param bool $debug
     * @return bool
     */
    public function isDomainAvailable(string $url, bool $ssl = false, bool $debug = false): bool
    {
        $options = [
            CURLOPT_HEADER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_NOBODY => true
        ];
        return $this->executeCurl($url, ['ssl' => $ssl], $options);
    }

    /**
     * @param string $url
     * @param string $file
     * @return bool|string
     */
    private function curlSetPing(string $url, string $file): bool|string
    {
        $options = [
            CURLOPT_REFERER => Url::getUrl(),
            CURLOPT_NOBODY => true
        ];
        return $this->executeCurl('https://www.google.com/webmasters/tools/ping?sitemap=http%3A%2F%2F'.$url.'%2F'.$file, [], $options);
    }

    /**
     * @param string $url
     * @param string $directory
     * @param int $status
     * @param bool $debug
     * @return bool|string
     */
    public function copyRemoteFile(string $url, string $directory, int $status = 0, bool $debug = false): bool|string
    {
        $options = [
            CURLOPT_HEADER => false,
            CURLOPT_CONNECTTIMEOUT => 60,
            //CURLOPT_BINARYTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FAILONERROR => true
        ];
        return $this->executeCurl($url, ['directory' => $directory, 'status' => $status], $options);
    }

    /**
     * Prepare request Data with Curl (no files)
     * @example
     *  $json = json_encode(array(
     *      'category'=>array(
     *      'id'  =>'16'
     *  )));
     *  print_r($json);
     *  print $this->webservice->setPrepareSendData([
     *      'wsAuthKey' => $this->setWsAuthKey(),
     *      'method' => 'xml',
     *      'data' => $test,
     *      'customRequest' => 'DELETE',
     *      'debug' => false,
     *      'url' => 'http://www.mywebsite.tld/webservice/catalog/categories/'
     *  ]);
     * @param array $data
     * @return bool|string
     */
    public function sendData(array $data): bool|string
    {
        $type = match ($data['method']) {
            'xml' => 'text/xml',
            default => 'application/json',
        };

        $options = [
            CURLINFO_HEADER_OUT     => true,
            CURLOPT_HTTPAUTH        => CURLAUTH_BASIC,
            CURLOPT_USERPWD         => $data['wsAuthKey'],
            CURLOPT_HTTPHEADER      => ["Authorization : Basic ".$data['wsAuthKey'], 'Content-type: '.$type, 'Accept: '.$type, 'charset=utf-8'],
            CURLOPT_CONNECTTIMEOUT  => 300,
            CURLOPT_CUSTOMREQUEST   => $data['customRequest'],
            CURLOPT_SSL_VERIFYPEER  => false
        ];

        $spec_options = match ($data['customRequest']) {
            'GET' => [
                CURLOPT_TIMEOUT => 300,
                CURLOPT_SSL_VERIFYHOST  => false
            ],
            'POST' => [
                CURLOPT_HEADER=> false,
                CURLOPT_NOBODY=> false,
                CURLOPT_POST => true
            ],
            default => [
                CURLOPT_HEADER=> false,
                CURLOPT_NOBODY=> false,
            ],
        };

        return $this->executeCurl($data['url'], $data, [urlencode($data['data'])], array_merge($options,$spec_options));
    }

    /**
     * @param array $data
     * @param array $files
     * @return bool|string
     */
    private function sendFiles(array $data, array $files): bool|string
    {
        $options = [
            CURLOPT_HEADER => false,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_NOBODY => false,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $data['wsAuthKey'],
            CURLOPT_HTTPHEADER => ["Authorization : Basic ".$data['wsAuthKey']],
            CURLOPT_CONNECTTIMEOUT => 300,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POST => true,
            CURLOPT_SSL_VERIFYPEER => false
        ];

        return $this->executeCurl($data['url'], $data, [$files], $options);
    }

    /**
     * Prepare post Img with Curl (files only)
     * @example
     *  print $this->webservice->setPreparePostImg([
     *      'wsAuthKey' => $this->setWsAuthKey(),
     *      'url' => 'http://www.website.tld/webservice/catalog/categories/3',
     *      'debug' => false,
     *  ]);
     * @param array $data
     * @return bool|string
     */
    public function sendPostImg(array $data): bool|string
    {
        try {
            if (isset($_FILES[$data['name']])) {
                return $this->sendFiles($data,$_FILES[$data['name']]);
            }
            else {
                throw new \Exception('File key not set',E_WARNING);
            }
        }
        catch (\Exception $e) {
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);            return false;
        }
    }

    /**
     * Send Copy file on remote url
     * @param array $data
     * @return bool|string
     */
    public function sendMultiImg(array $data): bool|string
    {
        try {
            if (isset($data['files'])) {
                return $this->sendFiles($data,$data['files']);
            }
            else {
                throw new \Exception('File not set',E_WARNING);
            }
        }
        catch (\Exception $e) {
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);            return false;
        }
    }

    /**
     * Send Copy file on remote url
     * @deprecated use sendMultiImg instead
     * @param array $data
     * @return bool|string
     */
    #[Deprecated] public function sendCopyFiles(array $data): bool|string
    {
        return $this->sendMultiImg($data);
    }

    /**
     * Send Copy file on remote url
     * @deprecated use sendMultiImg instead
     * @param array $data
     * @return bool|string
     */
    #[Deprecated] public function sendCopyImg(array $data): bool|string
    {
        return $this->sendMultiImg($data);
    }

    /**
     * @param array $data
     * @return bool|string
     */
    public function sendGet(array $data): bool|string
    {
        $data['customRequest'] = 'GET';
        return $this->sendData($data);
    }
}
