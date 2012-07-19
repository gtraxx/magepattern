<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 19/07/12
 * Time: 22:05
 *
 */
class http_session{
    /**
     * @access private
     * Démarre une nouvelle session
     */
    public function session_start($session_name='mp_default_s'){
        if(isset($session_name)){
            $name = $session_name;
        }
        $string = $_SERVER['HTTP_USER_AGENT'];
        $string .= 'SHIFLETT';
        /* Add any other data that is consistent */
        $fingerprint = md5($string);
        //Fermeture de la première session, ses données sont sauvegardées.
        session_write_close();
        session_name($name);
        ini_set('session.hash_function',1);
        session_start();
    }

    /**
     *
     * initialise les variables de session
     * @param array() $session
     * @throws Exception
     * @internal param bool $debug
     */
    private function ini_session_var($session){
        if(is_array($session)){
            foreach($session as $row => $val){
                $_SESSION[$row] = $val;
            }
        }else{
            throw new Exception('session init is not array');
        }
    }

    /**
     * @access public
     * Initialise la session ou renouvelle la session
     * @param $session_tabs
     * @param bool $setOption
     * @internal param array $session
     * @internal param bool $debug
     */
    public function session_run($session_tabs,$setOption=false){
        try {
            $setOption;
            $this->ini_session_var($session_tabs);
        }catch(Exception $e) {
            $logger = new debug_logger(MP_TMP_DIR);
            $logger->log('php', 'error', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }
    /**
     *
    $session = new http_session();
    if(!http_request::isSession('panier')){
        $array_sess = array(
            'panier'=>'test',
            'outils'=>'Le marteau du peuple'
        );
        $session->session_start('masession');
        $session->session_run($array_sess);
    }else{
        $session->debug();
    }
     */
    /**
     * @access public
     * Affiche le debug pour les sessions
     */
    /*public function debug(){
        if (M_FIREPHP) {
            $firebug = new magixcjquery_debug_magixfire();
            $firebug->magixFireGroup('Magix Session');
            //$firebug->magixFireLog($_SESSION);
            $firebug->magixFireDump('session run',$_SESSION);
            $firebug->magixFireGroupEnd();
        }else{
            var_dump($_SESSION);
        }
    }*/
}
?>