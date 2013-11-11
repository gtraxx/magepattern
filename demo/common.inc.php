<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Mage Pattern.
# The toolkit PHP for developer
# Copyright (C) 2012 - 2013 Gerits Aurelien contact[at]aurelien-gerits[dot]be
#
# OFFICIAL TEAM MAGE PATTERN:
#
#   * Gerits Aurelien (Author - Developer) contact[at]aurelien-gerits[dot]be
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
# needs please refer to http://www.magepattern.com for more information.
#
# -- END LICENSE BLOCK -----------------------------------
/**
 * Created by SC BOX.
 * User: aureliengerits
 * Date: 11/11/13
 * Time: 17:30
 * 
 */
// Database driver (mysql, pgsql)
define('MP_DBDRIVER','mysql');

// Database hostname (usually "localhost")
define('MP_DBHOST','localhost');

// Database user
define('MP_DBUSER','root');

// Database password
define('MP_DBPASSWORD','root');

// Database name
define('MP_DBNAME','test');

// DEBUG ('debug' OR 'log' OR false)
define('MP_LOG','debug');

// Path for error log
define('MP_LOG_DIR','/Applications/MAMP/htdocs/magepattern/demo/log');

// FirePHP (false or true)
define('MP_FIREPHP',true);

if(defined('MP_LOG')){
    if(MP_LOG == 'debug'){
        $dis_errors = 1;
    }elseif(MP_LOG == 'log'){
        $dis_errors = 1;
    }else{
        $dis_errors = 0;
    }
    ini_set('display_errors', $dis_errors);
}