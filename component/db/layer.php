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
    protected static $setOption = array(
        'mode'=>'assoc',
        'closeCursor'=>true,
        'debugParams'=>false
    );

    /**
     *  Construct
     */
    public function __construct($config = false){
        try{
            if($config != false){
                if(is_array($config)){
                    if(array_key_exists('charset', $config)){
                        $this->config['charset'] = $config['charset'];
                    }else{
                        $this->config['charset'] = 'utf8';
                    }
                    if(array_key_exists('port', $config)){
                        $this->config['port'] = $config['port'];
                    }else{
                        $this->config['port'] = '3306';
                    }
                    /*if(array_key_exists('unix_socket', $config)){
                        $this->config['unix_socket'] = $config['unix_socket'];
                    }else{
                        $this->config['unix_socket'] = '3306';
                    }*/
                }
            }else{
                $this->config['charset'] = 'utf8';
                $this->config['port'] = '3306';
                //$this->config['unix_socket'] = '3306';
            }
            self::connection()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch (PDOException $e){
            $logger = new debug_logger(MP_TMP_DIR);
            $logger->log('statement', 'db', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
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
            case 'pgsql':
                $adapter = new db_adapter_postgres();
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
            $optionDB = self::$setOption;
        }
        if(array_key_exists('mode', $optionDB)){
            $setConfig['mode'] = $optionDB['mode'];
        }else{
            $setConfig['mode'] = self::$setOption['mode'];
        }
        if(array_key_exists('closeCursor', $optionDB)){
            $setConfig['closeCursor'] = $optionDB['closeCursor'];
        }else{
            $setConfig['closeCursor'] = self::$setOption['closeCursor'];
        }
        if(array_key_exists('debugParams', $optionDB)){
            $setConfig['debugParams'] = $optionDB['debugParams'];
        }else{
            $setConfig['debugParams'] = self::$setOption['debugParams'];
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
     * @example :
     * #### No params ###
     * $color = '';
        $db = new db_layer();
        $sql =  'SELECT id, color FROM fruit';
        foreach  ($db->fetchAll($sql) as $row) {
        $color.= $row['color'].'<br />';
        }
    print $color.'<br />';
     * ### With params ###
     * $id=1;
        $db = new db_layer();
        $sql =  'SELECT id, color
        FROM fruit
        WHERE id = ?';
        foreach  ($db->fetchAll($sql,array($id)) as $row) {
        $color.= $row['color'];
        }
        print $color.'<br />';
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

    /**
     * Récupère la ligne suivante d'un jeu de résultats
     * @param $sql
     * @param bool $execute
     * @param bool $setOption
     * @return mixed
     * @example:
     *
     * $select =  $db->fetch('SELECT id, color,name FROM fruit');
        print $select['name'];
     */
    public function fetch($sql,$execute=false,$setOption=false){
        try{
            /**
             * Charge la configuration
             */
            $setConfig = $this->setConfig($setOption);
            $prepare = $this->prepare($sql);
            $prepare->setFetchMode($this->setMode($setConfig['mode']));
            $execute ? $prepare->execute($execute) : $prepare->execute();
            $setConfig['debugParams'] ? $prepare->debugDumpParams():'';
            $result = $prepare->fetch();
            $setConfig['closeCursor'] ? $prepare->closeCursor():'';
            return $result;
        }catch (PDOException $e){
            $logger = new debug_logger(MP_TMP_DIR);//__DIR__.'/test'
            $logger->log('statement', 'db', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }

    /**
     * Récupère la prochaine ligne et la retourne en tant qu'objet
     * @param $sql
     * @param bool $execute
     * @param bool $setOption
     * @return mixed
     */
    public function fetchObject($sql,$execute=false,$setOption=false){
        try{
            /**
             * Charge la configuration
             */
            $setConfig = $this->setConfig($setOption);
            $prepare = $this->prepare($sql);
            $execute ? $prepare->execute($execute) : $prepare->execute();
            $setConfig['debugParams'] ? $prepare->debugDumpParams():'';
            $result = $prepare->fetchObject();
            $setConfig['closeCursor'] ? $prepare->closeCursor():'';
            return $result;
        }catch (PDOException $e){
            $logger = new debug_logger(MP_TMP_DIR);//__DIR__.'/test'
            $logger->log('statement', 'db', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }

    /**
     * Insertion d'une ligne
     * @param $sql
     * @param bool $execute
     * @param bool $setOption
     */
    public function insert($sql,$execute=false,$setOption=false){
        try{
            /**
             * Charge la configuration
             */
            $setConfig = $this->setConfig($setOption);
            $prepare = $this->prepare($sql);
            $prepare->execute($execute);
            $setConfig['debugParams'] ? $prepare->debugDumpParams():'';
            $setConfig['closeCursor'] ? $prepare->closeCursor():'';
        }catch (PDOException $e){
            $logger = new debug_logger(MP_TMP_DIR);//__DIR__.'/test'
            $logger->log('statement', 'db', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }

    /**
     * Modification d'une ligne
     * @param $sql
     * @param bool $execute
     * @param bool $setOption
     */
    public function update($sql,$execute=false,$setOption=false){
        try{
            /**
             * Charge la configuration
             */
            $setConfig = $this->setConfig($setOption);
            $prepare = $this->prepare($sql);
            $prepare->execute($execute);
            $setConfig['debugParams'] ? $prepare->debugDumpParams():'';
            $setConfig['closeCursor'] ? $prepare->closeCursor():'';
        }catch (PDOException $e){
            $logger = new debug_logger(MP_TMP_DIR);
            $logger->log('statement', 'db', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }

    /**
     * Suppression d'une ligne
     * @param $sql
     * @param bool $execute
     * @param bool $setOption
     */
    public function delete($sql,$execute=false,$setOption=false){
        try{
            /**
             * Charge la configuration
             */
            $setConfig = $this->setConfig($setOption);
            $prepare = $this->prepare($sql);
            $prepare->execute($execute);
            $setConfig['debugParams'] ? $prepare->debugDumpParams():'';
            $setConfig['closeCursor'] ? $prepare->closeCursor():'';
        }catch (PDOException $e){
            $logger = new debug_logger(MP_TMP_DIR);
            $logger->log('statement', 'db', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }

    /**
     *  Initiates a beginTransaction
     *
     * @internal param void $sql
     * @return void
     */
    public function beginTransaction(){
        self::connection()->beginTransaction();
    }
    /**
     * instance exec
     *
     * @param void $sql
     */
    public function exec($sql){
        self::connection()->exec($sql);
    }
    /**
     * instance commit
     *
     */
    public function commit(){
        self::connection()->commit();
    }
    /**
     * instance rollback
     *
     */
    public function rollback(){
        self::connection()->rollBack();
    }
    public function transaction($sql){
        try{
            $this->beginTransaction();
            if(is_array($sql)){
                foreach ($sql as $key){
                    $this->exec($key);
                }
                $this->commit();
            }else{
                throw new Exception("Exec transaction is not array");
            }
        }catch(Exception $e){
            $this->rollback();
            $logger = new debug_logger(MP_TMP_DIR);
            $logger->log('statement', 'db', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }
}
?>