<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Mage Pattern.
# The toolkit PHP for developer, integrated in SC BOX
# Copyright (C) 2012  Gerits Aurelien <aurelien@magix-dev.be> - <aurelien@sc-box.com>
#
# OFFICIAL TEAM MAGE PATTERN:
#
#   * Gerits Aurelien (Author - Developer) <aurelien@sc-box.com>
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Redistributions of source code must retain the above copyright notice,
# this list of conditions and the following disclaimer.
#
# Redistributions in binary form must reproduce the above copyright notice,
# this list of conditions and the following disclaimer in the documentation
# and/or other materials provided with the distribution.
#
# DISCLAIMER

# Do not edit or add to this file if you wish to upgrade Mage Pattern to newer
# versions in the future. If you wish to customize Mage Pattern for your
# needs please refer to http://www.sc-box.com for more information.
#
# -- END LICENSE BLOCK -----------------------------------

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
    public function start($session_name='mp_default_s'){
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
     * Création d'un token
     * @param $tokename
     * @return array
     */
    public function token($tokename){
        if (empty($_SESSION[$tokename])){
            return $_SESSION[$tokename] = filter_rsa::tokenID();
        }else{
            if (isset($_SESSION[$tokename])){
                return $_SESSION[$tokename];
            }
        }
    }

    /**
     *
     * initialise les variables de session
     * @param array() $session
     * @throws Exception
     * @internal param bool $debug
     */
    private function iniSessionVar($session){
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
    public function run($session_tabs,$setOption=false){
        try {
            $setOption;
            $this->iniSessionVar($session_tabs);
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
    public function debug(){
        if (M_FIREPHP) {
            $firebug = new debug_firephp();
            $firebug->group('Magix Session');
            //$firebug->magixFireLog($_SESSION);
            $firebug->dump('session run',$_SESSION);
            $firebug->groupEnd();
        }else{
            var_dump($_SESSION);
        }
    }
}
?>