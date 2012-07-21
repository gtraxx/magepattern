<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 2/07/12
 * Time: 23:19
 *
 */
class db_adapter_sqlite extends db_adapter_connector {

    /**
     * Establish a PDO database connection.
     *
     * @param  array  $config
     * @return PDO
     */
    public function connect($config)
    {
        $options = $this->options($config);

        // SQLite provides supported for "in-memory" databases, which exist only for
        // lifetime of the request. Any given in-memory database may only have one
        // PDO connection open to it at a time. These are mainly for tests.

        if ($config['database'] == ':memory:')
        {
            return new PDO('sqlite::memory:', null, null, $options);
        }

        $path = path('storage').'database'.DS.$config['database'].'.sqlite';

        return new PDO('sqlite:'.$path, null, null, $options);
    }
}