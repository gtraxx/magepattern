<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 2/07/12
 * Time: 23:12
 *
 */
abstract class db_adapter_connector {
    /**
     * @access private
     * SGBD host
     *
     * @var string
     */
    protected static $host = MP_DBHOST;
    /**
     * @access protected
     * SGBD Name
     *
     * @var string
     */
    protected static $dbname = MP_DBNAME;
    /**
     * @access protected
     * SGBD User
     *
     * @var string
     */
    protected static $user = MP_DBUSER;
    /**
     * @access protected
     * SFBD Pass
     *
     * @var string
     */
    protected static $pass = MP_DBPASSWORD;
    /**
     * @access protected
     * Load DB INFO HOST AND NAME
     *
     * @return string
     */
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
        PDO::ATTR_EMULATE_PREPARES => false,
    );

    /**
     * Establish a PDO database connection.
     *
     * @param  array  $config
     * @return PDO
     */
    abstract public function connect($config);

    /**
     * Get the PDO connection options for the configuration.
     *
     * Developer specified options will override the default connection options.
     *
     * @param  array  $config
     * @return array
     */
    protected function options($config)
    {
        $options = (isset($config['options'])) ? $config['options'] : array();

        return $this->options + $options;
    }

}