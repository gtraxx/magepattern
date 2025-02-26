<?php
/*
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of MAGIX CMS.
# MAGIX CMS, The content management system optimized for users
# Copyright (C) 2008 - 2021 magix-cms.com <support@magix-cms.com>
#
# OFFICIAL TEAM :
#
#   * Aurelien Gerits (Author - Developer) <aurelien@magix-cms.com>
#   * Salvatore Di Salvo (Author - Developer) <disalvo.infographiste@gmail.com>
#
# Redistributions of files must retain the above copyright notice.
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# -- END LICENSE BLOCK -----------------------------------
#
# DISCLAIMER
#
# Do not edit or add to this file if you wish to upgrade MAGIX CMS to newer
# versions in the future. If you wish to customize MAGIX CMS for your
# needs please refer to http://www.magix-cms.com for more information.
*/
namespace Magepattern;

final class Bootstrap
{
    /**
     * @var Bootstrap $_instance
     */
    private static $_instance;

    /**
     * @var array $libraries Available libraries
     */
    private static array $libraries = [
        'autoloader'   => __DIR__.'/component/Autoload.php',
        'swift'        => __DIR__.'/package/Swift-5.2.1/lib/swift_required.php',
        'mobiledetect' => __DIR__.'/package/mobiledetect/Mobile_Detect.php',
        'dompdf'       => __DIR__.'/package/dompdf/autoload.inc.php',
        'cssinliner'   => __DIR__.'/package/cssinliner/init.php',
        'chromephp'    => __DIR__.'/package/chrome-logger/ChromePhp.php'
    ];

    /**
     * @var array $boot_libraries Libraries to always load
     */
    private static array $boot_libraries = [
        'autoloader',
        'swift',
        'mobiledetect'
    ];

    /**
     * @var array $loaded_libraries Libraries currently loaded
     */
    private static array $loaded_libraries = [];

    /**
     * Constructor is set to private to prevent unwanted instantiation
     */
    private function __construct(){}

    /**
     * Prevent clone to prevent double instance
     */
    private function __clone(){}

    /**
     * @return Bootstrap
     */
    public static function getInstance()
    {
        // Check if there is an existing instance, if not create one
        if(!self::$_instance instanceof self) self::$_instance = new self;
        // Return instance
        return self::$_instance;
    }

    /**
     * Load a specific library
     * @param string $library
     * @return bool
     */
    public function load(string $library): bool
    {
        if(in_array($library,self::$loaded_libraries)) return true;

        if (file_exists(self::$libraries[$library])) {
            require self::$libraries[$library];
            self::$loaded_libraries[] = $library;
            return true;
        }
        else return false;
    }

    /**
     * Load required libraries
     */
    private function getFilesRequire()
    {
        foreach(self::$boot_libraries as $library) {
            self::load($library);
        }
    }

    /**
     *
     */
    public function getClassAutoloader()
    {
        $this->getFilesRequire();
        $autoloader = new Component\Autoload();
        $autoloader->addNamespace(
            'Magepattern\Component',
            [
                'Database' => __DIR__.'/component/database',
                'Date' => __DIR__.'/component/date',
                'Debug' => __DIR__.'/component/debug',
                'File' => __DIR__.'/component/file',
                'HTTP' => __DIR__.'/component/http',
                'Mail' => __DIR__.'/component/mail',
                'Tool' => __DIR__.'/component/tool',
                'XML' => __DIR__.'/component/xml'
            ]
        );
        $autoloader->register();
    }
}

Bootstrap::getInstance()->getClassAutoloader();