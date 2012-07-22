<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 21/07/12
 * Time: 22:15
 *
 */
class filter_rsa{

    /**
     * @param $data
     * @return string
     */
    private function md5_base64($data) {
        return preg_replace('/=+$/','',base64_encode(pack('H*',md5($data))));
    }

    public static function hashEncode($type = 'md5',$data){

        switch($type){
            case 'sha1':
                return sha1($data);
                break;
            case 'md5':
                return md5($data);
                break;
            case 'md5_base64':
                return self::md5_base64($data);
                break;
        }
    }
    /**
     * @static
     * @access public
     * retourne un identifiant unique
     */
    public static function uniqID(){
        $id = uniqid(mt_rand(), true);
        return base_convert($id, 10, 36);
    }

    /**
     *
     * Génération de token ou jeton
     */
    public static function tokenID(){
        return md5(session_id() . time() . $_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Génération de micro id
     * @return string
     */
    public static function randMicroUI() {
        return sprintf('%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 48 bits for "node"
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Génération de pseudo ID aléatoire
     * @return string
     */
    public static function randUI() {
        return sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',

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
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * @access public
     * @static
     * Génération d'un identifiant alphanumérique
     * @param integer $numStr
     * @return string
     */
    public static function randString($numStr){
        $uId = '';
        srand( (double)microtime()*rand(1000000,9999999) ); // Genere un nombre aléatoire
        $arrChar = array(); // Nouveau tableau
        for( $i=65; $i<90; $i++ ) {
            array_push( $arrChar, chr($i) ); // Ajoute A-Z au tableau
            array_push( $arrChar, strtolower( chr( $i ) ) ); // Ajouter a-z au tableau
        }
        for( $i=48; $i<57; $i++ ) {
            array_push( $arrChar, chr( $i ) ); // Ajoute 0-9 au tableau
        }
        for( $i=0; $i< $numStr; $i++ ) {
            //$uId .= $arrChar[rand( 0, count( $arrChar ) )]; // Ecrit un aléatoire
            $uId .= $arrChar[rand( 0,count($arrChar)-1)];// Ecrit un aléatoire
        }
        return $uId;
    }

    /**
     * @access public
     * @static
     * Génère un ID aléatoire sur base de différent paramètres de transformation
     * (numérique => alphanumérique,alphanumérique => numérique)
     * @param unknown_type $in
     * @param bool|\unknown_type $to_num
     * @param bool|\unknown_type $pad_up
     * @param string $passKey
     * @return int|number|string
     */
    public static function alphaID($in, $to_num = false, $pad_up = false, $passKey = null){

        $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        if ($passKey !== null) {
            // Although this function's purpose is to just make the
            // ID short - and not so much secure,
            // with this patch by Simon Franz (http://blog.snaky.org/)
            // you can optionally supply a password to make it harder
            // to calculate the corresponding numeric ID

            for ($n = 0; $n<strlen($index); $n++) {
                $i[] = substr( $index,$n ,1);
            }

            $passhash = hash('sha256',$passKey);
            $passhash = (strlen($passhash) < strlen($index))
                ? hash('sha512',$passKey)
                : $passhash;

            for ($n=0; $n < strlen($index); $n++) {
                $p[] =  substr($passhash, $n ,1);
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
        } else {
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
}
?>