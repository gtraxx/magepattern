<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 2/07/12
 * Time: 23:55
 *
 */
class db_layer{
    /**
     * @access protected
     * DRIVER SGBD
     *
     * @var STRING
     */
    protected static $driver = MP_DBDRIVER;
    /**
     * The raw adapter instance.
     *
     * @var adapter
     */
    public $adapter;

    /**
     * The connection configuration array.
     *
     * @var array
     */
    public $config;
    /**
     * @var array
     */
    protected static $option = array(
        'mode'=>'assoc',
        'closeCursor'=>true,
        'debugParams'=>false
    );

    /**
     *  Construct
     */
    public function __construct(){
        try{
            self::connection()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            $logger = new debug_logger($_SERVER["DOCUMENT_ROOT"].'/test');//__DIR__.'/test'
            $logger->log($e->getCode(), 'db', 'An error has occured :'.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }

    /**
     * @return string
     */
    private function driver(){
        return self::$driver;
    }

    /**
     * @return PDO
     */
    private function connection(){
        switch(self::driver()){
            case 'mysql':
                $adapter = new db_adapter_mysql();
            break;
        }
        return $adapter->connect($this->config);
    }

    /**
     * @param $mode
     * @return int
     */
    private function setMode($mode){
        switch($mode){
            case 'assoc':
                $fetchmode = PDO::FETCH_ASSOC;
                break;
            case 'class':
                $fetchmode = PDO::FETCH_CLASS;
                break;
            case 'column':
                $fetchmode = PDO::FETCH_NUM;
                break;
            default:
                $fetchmode = PDO::FETCH_ASSOC;
                break;
        }
        return $fetchmode;
    }

    /**
     * @param bool $option
     * @return array
     */
    private function setConfig($option = false){
        if($option != false){
            if(is_array($option)){
                $optionDB = $option;
            }
        }else{
            $optionDB = self::$option;
        }
        if(array_key_exists('mode', $optionDB)){
            $setConfig['mode'] = $optionDB['mode'];
        }else{
            $setConfig['mode'] = self::$option['mode'];
        }
        if(array_key_exists('closeCursor', $optionDB)){
            $setConfig['closeCursor'] = $optionDB['closeCursor'];
        }else{
            $setConfig['closeCursor'] = self::$option['closeCursor'];
        }
        if(array_key_exists('debugParams', $optionDB)){
            $setConfig['debugParams'] = $optionDB['debugParams'];
        }else{
            $setConfig['debugParams'] = self::$option['debugParams'];
        }
        return $setConfig;
    }
    /**
     *  Executes an SQL statement, returning a result set as a PDOStatement object
     *
     * @param request $query
     * @return void
     */
    public function query($query)
    {
        try{
            return self::connection()->query($query);
        }catch (PDOException $e){
            $logger = new debug_logger(MP_TMP_DIR);//__DIR__.'/test'
            $logger->log('statement', 'db', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }
    /**
     *  Prepares a statement for execution and returns a statement object
     *
     * @param request containt $sql
     * @return void
     */
    public function prepare($sql){
        try{
            return self::connection()->prepare($sql);
        }catch (PDOException $e){
            $logger = new debug_logger(MP_TMP_DIR);//__DIR__.'/test'
            $logger->log('statement', 'db', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }

    /**
     * Retourne un tableau contenant toutes les lignes du jeu d'enregistrements
     * @param $sql
     * @param bool $execute
     * @param bool $setOption
     * @return mixed
     */
    public function fetchAll($sql,$execute=false,$setOption=false){
        try{
            /**
             * Charge la configuration
             */
            $setConfig = $this->setConfig($setOption);
            $prepare = $this->prepare($sql);
            $prepare->setFetchMode($this->setMode($setConfig['mode']));
            $execute ? $prepare->execute($execute) : $prepare->execute();
            $setConfig['debugParams'] ? $prepare->debugDumpParams():'';
            $result = $prepare->fetchAll();
            $setConfig['closeCursor'] ? $prepare->closeCursor():'';
            return $result;
        }catch (PDOException $e){
            $logger = new debug_logger(MP_TMP_DIR);//__DIR__.'/test'
            $logger->log('statement', 'db', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }
}
?>