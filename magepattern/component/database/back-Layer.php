<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Mage Pattern.
# The toolkit PHP for developer
# Copyright (C) 2012 - 2026 Gerits Aurelien contact[at]gerits-aurelien[dot]be
#
# OFFICIAL TEAM MAGE PATTERN:
#
#   * Gerits Aurelien (Author - Developer) contact[at]gerits-aurelien[dot]be
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
namespace Magepattern\Component\Database;
use Magepattern\Component\Debug\Logger;

class Layer
{
    /**
     * @access protected
     * DRIVER SGBD
     *
     * @var STRING
     */
    const DRIVER = MP_DBDRIVER;

    /**
     * @var Layer
     */
    protected static $_instance;

    /**
     * The raw adapter instance.
     *
     * @var Connector
     */
    public $adapter;

    /**
     * The connection configuration array.
     *
     * @var array $config
     */
    public array $config = [];

    /**
     * @var array $setOption
     */
    protected array $setOption = [
        'mode'        => 'assoc',
        'closeCursor' => true,
        'debugParams' => false
    ];

    /**
     * @var bool $inTransaction
     */
    protected bool $inTransaction = false;
    /**
     *
     * @var bool $isPrepared
     */
    protected bool $isPrepared = false;

    /**
     * Layer constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        try {
            if(!extension_loaded('pdo_sqlite')) throw new \Exception('Extension PDO not loaded',E_ERROR);
            if(array_key_exists('charset', $config)) $this->config['charset'] = $config['charset'];
            if(array_key_exists('port', $config)) $this->config['port'] = $config['port'];
        }
        catch (\Exception $e) {
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
        }
    }

    /**
     * Prevent clone to prevent double instance
     */
    private function __clone(){}

    /**
     * C'est la méthode qui "remplace" le constructeur vis à vis
     * des autres classes.
     *
     * Son rôle est de créer / distribuer une unique
     * instance de notre objet.
     */
    public static function getInstance()
    {
        if(!self::$_instance instanceof self) self::$_instance = new self;
        return self::$_instance;
    }

    /**
     * Charge la class correspondant au driver sélectionné
     * @return \PDO
     */
    public function connection()
    {
        try {
            if(!$this->adapter) {
                $this->adapter = match(self::DRIVER) {
                    'mysql', 'mariadb' => new MySQL(),
                    'pgsql' => new PostgreSQL(),
                    'sqlite' => new SQLite(),
                };
            }
            try {
                return $this->adapter->connect($this->config);
            }
            catch (\PDOException $e) {
                Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            }
        }
        catch (\UnhandledMatchError $e) {
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
        }
    }

    /**
     * @param string $mode
     * @return int
     */
    private function setMode(string $mode): int
    {
        return match($mode) {
            'class' => \PDO::FETCH_CLASS,
            'column' => \PDO::FETCH_NUM,
            default => \PDO::FETCH_ASSOC
        };
    }

    /**
     * @param array $option
     * @return array
     */
    private function setConfig(array $option = []) : array{

        $optionDB = is_array($option) ? $option : $this->setOption;
        return array_merge($this->setOption,$optionDB);
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     * @param string $query
     * @return false|\PDOStatement
     */
    public function query(string $query)
    {
        try {
            return $this->connection()->query($query);
        }
        catch (\PDOException $e) {
            Logger::getInstance()->log($e,'statement');
        }
    }

    /**
     * Prepares a statement for execution and returns a statement object
     * @param string $sql
     * @return bool|\PDOStatement
     */
    public function prepare(string $sql)
    {
        try {
            if ($this->isPrepared) throw new \Exception('This statement has been prepared already',E_WARNING);
            //$this->isPrepared = true;
            return $this->connection()->prepare($sql);
        }
        catch (\Exception $e) {
            Logger::getInstance()->log($e,'statement');
            return false;
        }
    }

    /**
     * Initiates a beginTransaction
     * @return false|\PDO
     */
    public function beginTransaction()
    {
        if ($this->inTransaction) return false;

        $connection = $this->connection();
        $connection->beginTransaction();
        $this->inTransaction = true;
        return $connection;
    }

    /**
     * instance exec
     *
     * @param string $sql
     */
    public function exec(string $sql)
    {
        $this->connection()->exec($sql);
    }

    /**
     * instance commit
     *
     */
    public function commit()
    {
        $this->connection()->commit();
        $this->inTransaction = false;
    }

    /**
     * instance rollback
     *
     */
    public function rollBack()
    {
        if($this->connection()->inTransaction()){
            $this->connection()->rollBack();
            $this->inTransaction = false;
        }
        else {
            Logger::getInstance()->log('Must call beginTransaction() before you can rollback','statement');
        }
    }

    /**
     * Global function for Insert, Update and Delete
     * @param string $type
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return bool|array
     */
    private function execute(string $type, string $sql, array $execute = [], array $setOption = []): bool|array
    {
        if($sql === '') {
            Logger::getInstance()->log('Empty request received','statement');
            return false;
        }

        try {
            $sql = preg_replace('/\s+/S', " ", $sql);
            // Logger::getInstance()->log($sql,'statement','Request');
            $prepare = $this->prepare($sql);
            if(is_object($prepare)) {
                $setConfig = $this->setConfig($setOption);
                if(in_array($type,['fetchAll','fetch','fetchObject','fetchColumn','columnCount','rowCount'])) $prepare->setFetchMode($this->setMode($setConfig['mode']));
                if(!empty($execute)) {
                    foreach ($execute as $param => $value) {
                        $data_type = gettype($value) === 'string' ? \PDO::PARAM_STR : \PDO::PARAM_INT;;
                        $prepare->bindValue($param,$value,$data_type);
                    }
                }
                $result = $prepare->execute();
                if($setConfig['debugParams']) $prepare->debugDumpParams();
                if(in_array($type,['fetchAll','fetch','fetchObject','fetchColumn','columnCount','rowCount'])) {
                    $result = match($type){
                        'fetchAll' => $prepare->fetchAll(),
                        'fetch' => $prepare->fetch(),
                        'fetchObject' => $prepare->fetchObject(),
                        'fetchColumn' => $prepare->fetchColumn(),
                        'columnCount' => $prepare->columnCount(),
                        'rowCount' => $prepare->rowCount(),
                    };
                }
                if($setConfig['closeCursor']) $prepare->closeCursor();
                //Logger::getInstance()->log($sql,'statement','queries');
                return $result;
            }
            else {
                throw new \Exception("$type Error with SQL prepare \n $sql",E_ERROR);
            }
        }
        catch (\Exception $e) {
            Logger::getInstance()->log($e,'statement');
            return false;
        }
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return false|array
     */
    public function fetchAll(string $sql, array $execute = [], array $setOption = []): false|array
    {
        return $this->execute('fetchAll', $sql, $execute, $setOption);
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return false|array
     */
    public function fetch(string $sql, array $execute = [], array $setOption = []): false|array
    {
        return $this->execute('fetch', $sql, $execute, $setOption);
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return false|object
     */
    public function fetchObject(string $sql, array $execute = [], array $setOption = []): false|object
    {
        return $this->execute('fetchObject', $sql, $execute, $setOption);
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return false|object
     */
    public function columnCount(string $sql, array $execute = [], array $setOption = []): false|object
    {
        return $this->execute('columnCount', $sql, $execute, $setOption);
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return false|object
     */
    public function rowCount(string $sql, array $execute = [], array $setOption = []): false|object
    {
        return $this->execute('rowCount', $sql, $execute, $setOption);
    }

    /**
     * @param string $sql
     * @param array $setOption
     * @return false|object
     */
    public function createTable(string $sql, array $setOption = []): false|object
    {
        return $this->execute('createTable', $sql, [], $setOption);
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return bool
     */
    public function insert(string $sql, array $execute = [], array $setOption = []): bool
    {
        return $this->execute('insert',$sql,$execute,$setOption);
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return bool
     */
    public function update(string $sql, array $execute = [], array $setOption = []): bool
    {
        return $this->execute('update',$sql,$execute,$setOption);
    }

    /**
     * @param string $sql
     * @param array $execute
     * @param array $setOption
     * @return bool
     */
    public function delete(string $sql, array $execute = [], array $setOption = []): bool
    {
        return $this->execute('delete',$sql,$execute,$setOption);
    }

    /**
     * Effectuer une Transaction prépare
     *
     * @param array $queries
     * @param array $config
     * @return array|false
     *
     * Example (prepare request with named parameters)
     * $queries = array(
     *   array('request'=>'DELETE FROM mytable WHERE id =:id','params'=>array(':id' => $id))
     * );
     *
     * OR (prepare request with question mark parameters)
     *
     * $queries = array(
     *   array('request'=>'DELETE FROM mytable WHERE id = ?','params'=>array($id))
     * );
     * component_routing_db::layer()->transaction($queries,array('type'=>'prepare'));
     *
     * Example (exec request)
     * $sql = array(
     *   'DELETE FROM mytable WHERE id ='.$id
     * );
     * component_routing_db::layer()->transaction($queries,array('type'=>'exec'));
     */
    public function transaction( array $queries, array $config = ['type'=>'prepare']): array|false
    {
        try {
            $transaction = $this->beginTransaction();

            if($transaction->inTransaction()) {
                if(is_array($queries)){
					$rslt = [];
                    foreach ($queries as $key => $value){
                        if(is_array($value) && $config['type'] === 'prepare'){
                            if (isset($value['request'])) {
                                $this->isPrepared = true;
                                $setConfig = $this->setConfig([]);
                                $prepare = $transaction->prepare($value['request']);
                                if(is_object($prepare)) {
                                    $prepare->setFetchMode($this->setMode($setConfig['mode']));
                                    $value['params'] ? $prepare->execute($value['params']) : $prepare->execute();
                                    if($setConfig['debugParams']) $prepare->debugDumpParams();
                                    $result = $prepare->fetchAll();
                                    if($setConfig['closeCursor']) $prepare->closeCursor();
                                    $rslt[$key] = $result;
                                }
                                $rslt[$key] = false;
                            }
                            else{
                                throw new \Exception('request key is not set',E_ERROR);
                            }
                        }
                        elseif($config['type'] === 'exec'){
                            $this->isPrepared = false;
                            $rslt[$key] = $transaction->exec($value);
                        }
                    }
                    $transaction->commit();
                    return $rslt;
                }
                else{
                    throw new \Exception('queries params is not array',E_ERROR);
                }
            }
            else{
                throw new \Exception('inTransaction : false',E_ERROR);
            }
        }
        catch(\Exception $e){
            Logger::getInstance()->log($e,'statement');
            $this->rollBack();
            return false;
        }
    }

    /**
     * @param string $type
     * @param string $sql
     * @param array $setOption
     * @return false|int
     */
    public function show(string $type, string $sql, array $setOption = []): int|false
    {
        $sql = match($type) {
            'database' => 'SHOW DATABASES LIKE  \''. $sql. '\'',
            'table' => 'SHOW TABLES FROM '.self::getInfo()->getDB().' LIKE  \''. $sql. '\'',
        };
        $setConfig = $this->setConfig($setOption);
        $prepare = $this->prepare($sql);
        if(is_object($prepare)){
            $prepare->execute();
            $result = $prepare->rowCount();
            if($setConfig['debugParams']) $prepare->debugDumpParams();
            if($setConfig['closeCursor']) $prepare->closeCursor();
            return $result;
        }
        return false;
    }

    /**
     * @param string $table
     * @param array $setOption
     * @return false|int
     */
    public function showTable(string $table, array $setOption = []): int|false
    {
        return $this->show('table',$table,$setOption);
    }

    /**
     * @param string $database
     * @param array $setOption
     * @return int|false
     */
    public function showDatabase(string $database, array $setOption = []): int|false
    {
        return $this->show('database',$database,$setOption);
    }

    /**
     * function truncate table
     *
     * @param string $table
     * @param array $setOption
     */
    public function truncateTable(string $table, array $setOption = [])
    {
        $sql = 'TRUNCATE TABLE '. $table;
        $setConfig = $this->setConfig($setOption);
        $prepare = $this->prepare($sql);
        if(is_object($prepare)) {
            $prepare->execute();
            if($setConfig['debugParams']) $prepare->debugDumpParams();
            if($setConfig['closeCursor']) $prepare->closeCursor();
        }
    }

    /**
     * @param int $column
     * @return array|false
     */
    public function getColumnMeta(int $column): array|false
    {
        return $this->connection()->getColumnMeta($column);
    }

    /**
     * Return an array of available PDO drivers
     * @return array
     */
    public function availableDrivers(): array
    {
        return $this->connection()->getAvailableDrivers();
    }

    /**
     * Returns the ID of the last inserted row or sequence value
     */
    public function lastInsertId()
    {
        return $this->connection()->lastInsertId();
    }

    /**
     * Quotes a string for use in a query.
     * @param string $string
     * @return string
     */
    public function quote(string $string): string
    {
        return $this->connection()->quote($string);
    }

    /**
     * Advances to the next rowset in a multi-rowset statement handle
     */
    public function nextRowset()
    {
        return $this->connection()->nextRowset();
    }
}