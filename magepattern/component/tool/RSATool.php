<?php
namespace Magepattern\Component\Tool;
use Magepattern\Component\Debug\Logger;

class RSATool
{
    /**
     * @param string $data
     * @return string
     */
    private static function md5_base64(string $data): string
    {
        return preg_replace('/=+$/','',base64_encode(pack('H*',md5($data))));
    }

    /**
     * @param string $type
     * @param string $data
     * @return string
     */
    public static function hashEncode(string $data, string $type = 'md5'): string
    {
        return match ($type) {
            'sha1' => sha1($data),
            'md5_base64' => self::md5_base64($data),
            default => md5($data)
        };
    }

    /**
     * Return an unique identifier
     * @return string
     */
    public static function uniqID(int $length = 16): string {
        try {
            $bytes = random_bytes(ceil($length / 2));
            $hex = bin2hex($bytes);
            return substr($hex, 0, $length);
        } catch (\TypeError $e) {
            Logger::getInstance()->log("Erreur de type lors de la génération d'un ID unique : " . $e->getMessage(), 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return '';
        } catch (\Exception $e) {
            Logger::getInstance()->log("Erreur lors de la génération d'un ID unique : " . $e->getMessage(), 'php', 'error', Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return '';
        }
    }

    /**
     * @param int $length
     * @return string
     * @throws \Random\RandomException
     */
    public static function tokenID(int $length = 20): string {
        if (function_exists('openssl_random_pseudo_bytes')) {
            return base64_encode(openssl_random_pseudo_bytes($length));
        } else if (function_exists('random_bytes')) {
            return base64_encode(random_bytes($length));
        } else {
            throw new \Exception("No cryptographically secure random number generation function is available.");
        }
    }

    /**
     * @param string $size Tiny|Mirco|Small|Large
     * @return string
     */
    public static function randUI(string $size = 'Large'): string
    {
        return match($size) {
            'Tiny' => sprintf('%04x',mt_rand(0, 0xffff)),
            'Mirco' => sprintf('%04x%04x',mt_rand(0, 0xffff), mt_rand(0, 0xffff)),
            'Small' => sprintf('%04x%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                // 16 bits for "time_mid"
                mt_rand(0, 0xffff),
                // 48 bits for "node"
                mt_rand(0, 0xffff)
            ),
            default => sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                // 16 bits for "time_mid"
                mt_rand(0, 0xffff),
                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                mt_rand(0, 0x0fff) | 0x4000,
                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                mt_rand(0, 0x3fff) | 0x8000,
                // 48 bits for "node"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff))
        };
    }

    /**
     * @param int $length
     * @return string
     */
    public static function randString(int $length): string
    {
        $uId = '';
        srand( (double)microtime()*rand(1000000,9999999) ); // Create random number
        $arrChar = [];
        for( $i=65; $i<90; $i++ ) {
            array_push( $arrChar, chr($i) ); // Add A-Z
            array_push( $arrChar, strtolower( chr($i) ) ); // Add a-z
        }
        for( $i=48; $i<57; $i++ ) {
            array_push( $arrChar, chr($i) ); // Add 0-9
        }
        for($i=0; $i< $length; $i++ ) {
            $uId .= $arrChar[rand( 0,count($arrChar)-1)]; // Pick randomly a number or a character
        }
        return $uId;
    }

    /**
     * @param $length
     * @return string
     */
    public static function randomInt($length): string
    {
        $int = '0';
        try {
            $int = '';
            for ($i = 0; $i < $length; $i++) {
                $int .= random_int(0, 9);
            }
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
        }
        return $int;
    }
    /**
     * Generate x random ids
     * @param int $nb, amount of random ids to generate
     * @param int $max, id max possible
     * @param int $min, id min possible
     * @param bool $duplicate, indicate whether duplicate ids are allowed or not
     * @return array
     */
    public static function getRandomIds(int $nb, int $max = 0, int $min = 1, bool $duplicate = false)
    {
        $ids = [];
        if($nb && $max > 0) {
            if($duplicate) {
                for($i=$min;$i<=$nb;$i++) {
                    $ids[] = rand($min,$max);
                }
            }
            else {
                do {
                    $rand_id = rand($min,$max);
                    if(!in_array($rand_id,$ids)) $ids[] = $rand_id;
                } while (count($ids) < $nb);
            }
        }
        return $ids;
    }

    /**
     * Create a random ID based on various transformation parameter
     * (numeric => alphanumeric, alphanumeric => numeric)
     * @param string $in
     * @param bool $to_num
     * @param bool $pad_up
     * @param string $passKey
     * @return string
     */
    public static function alphaID(string $in, $to_num = false, $pad_up = false, $passKey = ''): string
    {
        try {
            if(extension_loaded('bcmath')) throw new \Exception('bcmath extension not loaded',E_WARNING);
            $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

            if ($passKey !== '') {
                // Although this function's purpose is to just make the
                // ID short - and not so much secure,
                // with this patch by Simon Franz (http://blog.snaky.org/)
                // you can optionally supply a password to make it harder
                // to calculate the corresponding numeric ID
                $passhash = hash('sha256',$passKey);
                if(strlen($passhash) < strlen($index)) $passhash = hash('sha512',$passKey);

                $i = [];
                $p = [];
                for ($n = 0; $n < strlen($index); $n++) {
                    $i[] = substr($index, $n,1);
                    $p[] = substr($passhash, $n,1);
                }

                array_multisort($p,  SORT_DESC, $i);
                $index = implode($i);
            }

            $base  = strlen($index);

            if ($to_num) {
                // Digital number  <<--  alphabet letter code
                $in  = strrev($in);
                $out = 0;
                $len = strlen($in) - 1;
                for ($t = 0; $t <= $len; $t++) {
                    $bcpow = bcpow($base, $len - $t);
                    $out   = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
                }

                if (is_numeric($pad_up)) {
                    $pad_up--;
                    if ($pad_up > 0) {
                        $out -= pow($base, $pad_up);
                    }
                }
                $out = sprintf('%F', $out);
                $out = substr($out, 0, strpos($out, '.'));
            }
            else {
                // Digital number  -->>  alphabet letter code
                if (is_numeric($pad_up)) {
                    $pad_up--;
                    if ($pad_up > 0) {
                        $in += pow($base, $pad_up);
                    }
                }
                $out = "";
                for ($t = floor(log($in, $base)); $t >= 0; $t--) {
                    $bcp = bcpow($base, $t);
                    $a   = floor($in / $bcp) % $base;
                    $out = $out . substr($index, $a, 1);
                    $in  = $in - ($a * $bcp);
                }
                $out = strrev($out); // reverse
            }
            return $out;
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);            return '';
        }
    }
}