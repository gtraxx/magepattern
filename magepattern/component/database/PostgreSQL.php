<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Mage Pattern.
# The toolkit PHP for developer
# Copyright (C) 2012 - 2025 Gerits Aurelien contact[at]gerits-aurelien[dot]be
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

class PostgreSQL extends Connector
{
    /**
     * The PDO connection options.
     *
     * @var array $options
     */
    protected array $options = [
        \PDO::ATTR_CASE => \PDO::CASE_LOWER,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
        \PDO::ATTR_STRINGIFY_FETCHES => false
    ];

    /**
     * Establish a \PDO database connection.
     *
     * @param  array  $config
     * @return \PDO
     */
    public function connect(array $config): \PDO
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
        if (isset($config['port'])) $dsn .= ";port={$config['port']}";

        $connection = new \PDO($dsn, $username, $password, $this->options($config));

        // If a character set has been specified, we'll execute a query against
        // the database to set the correct character set. By default, this is
        // set to UTF-8 which should be fine for most scenarios.
        if (isset($config['charset'])) $connection->prepare("SET NAMES '{$config['charset']}'")->execute();

        return $connection;
    }
}