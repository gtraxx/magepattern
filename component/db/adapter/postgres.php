<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 2/07/12
 * Time: 23:19
 *
 */
class db_adapter_postgres extends db_adapter_connector {
    /**
     * The PDO connection options.
     *
     * @var array
     */
    protected $options = array(
        PDO::ATTR_CASE => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    );

    /**
     * Establish a PDO database connection.
     *
     * @param  array  $config
     * @return PDO
     */
    public function connect($config)
    {
        /**
         * host
         */
        $host = self::$host;
        /**
         * name database
         */
        $database = self::$dbname;
        /**
         * user database
         */
        $username = self::$user;
        /**
         * password database
         */
        $password = self::$pass;
        /**
         * dsn
         */

        $dsn = "pgsql:host={$host};dbname={$database}";

        // The developer has the freedom of specifying a port for the PostgresSQL
        // database or the default port (5432) will be used by PDO to create the
        // connection to the database for the developer.
        if (isset($config['port']))
        {
            $dsn .= ";port={$config['port']}";
        }

        $connection = new PDO($dsn, $username, $password, $this->options($config));

        // If a character set has been specified, we'll execute a query against
        // the database to set the correct character set. By default, this is
        // set to UTF-8 which should be fine for most scenarios.
        if (isset($config['charset']))
        {
            $connection->prepare("SET NAMES '{$config['charset']}'")->execute();
        }

        return $connection;
    }
}