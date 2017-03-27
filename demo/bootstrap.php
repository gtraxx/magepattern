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
 * Time: 17:28
 * 
 */

if (version_compare(phpversion(), '5.3.0', '<')) {
    echo  'Votre version de PHP est incompatible';
    exit;
}
/**
 * Fichier de configuration
 */
$config_in = 'common.inc.php';
if (file_exists($config_in)) {
    require $config_in;
}else{
    throw new Exception('Error Ini Common Files');
    exit;
}
/**
 * Include magepattern
 */
$magepattern = '../_autoload.php';
if (file_exists($magepattern)) {
    require $magepattern;
}else{
    throw new Exception('Error load library');
    exit;
}
/**
 * Constante Firephp
 */
if(defined('MP_FIREPHP')){
    if(MP_FIREPHP){
        debug_firephp::configErrorHandler();
    }
}
/**
 * Autoloader registerPrefixes pour les composants de l'application
 */
$loader = new autoloader();
$loader->addPrefixes(array(
    'frontend' => 'app'
));
$loader->register();