<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 19/06/12
 * Time: 00:46
 *
 */
class db_dataObjects extends PDO {
    /**
     * Instance de la classe PDO
     *
     * @var PDO
     * @access private
     */
    private static $PDOInstance = null;
    protected static $_getAbstract = null;
    /**
     * @throws Exception
     */
    private function _construct (){
        if (!(self::getInfo() instanceof CallDbData)) {
            throw new Exception('Invalid instanceof CallDbData');
        }
        if (!(self::PDOInstance() instanceof PDO)) {
            throw new Exception('Invalid instanceof PDO');
        }
        try{
            self::PDOInstance()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //self::PDOInstance()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        }catch(PDOException $e){
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
    /**
     * instance singleton self (CallDbData)
     * @access protected
     */
    private static function _getAbstract(){
        if (!isset(self::$_getAbstract)){
            if(is_null(self::$_getAbstract)){
                self::$_getAbstract = new CallDbData();
            }
        }
        return self::$_getAbstract;
    }
    /**
     * instance singleton self (PDO)
     * @access protected
     */
    private static function PDOInstance(){
        if (!isset(self::$PDOInstance)){
            if(is_null(self::$PDOInstance)){
                try {
                    self::$PDOInstance = new PDO(self::getInfo()->getconnStr(),self::getInfo()->getuser(),self::getInfo()->getpass()/*,
						array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
					*/);
                    self::$PDOInstance->exec("SET CHARACTER SET utf8");
                } catch (PDOException $e) {
                    self::$PDOInstance = false;
                    die($e->getMessage());
                }
            }
        }
        return self::$PDOInstance;
    }
}