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
namespace Magepattern\Component;

class Autoload {
    /**
     * An associative array where the key is a namespace prefix and the value
     * is an array of base directories for classes in that namespace.
     *
     * @var array $prefixes
     */
    protected array $prefixes = [];

    /**
     * An associative array where the values are the files already required
     *
     * @var array $registered
     */
    protected array $registered = [];

    /**
     * Register loader with SPL autoloader stack.
     *
     * @return void
     */
    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * Adds a base directory for a namespace prefix.
     *
     * @param string $prefix The namespace prefix.
     * @param string|array $base_dir A base directory for class files in the
     * namespace.
     * @param bool $prepend If true, prepend the base directory to the stack
     * instead of appending it; this causes it to be searched first rather
     * than last.
     * @return void
     */
    public function addNamespace(string $prefix, string|array $base_dir, $prepend = false)
    {
        // normalize namespace prefix
        $prefix = trim($prefix, '\\') . '\\';

        if(is_string($base_dir)) $base_dir = [$base_dir];

        if(is_array($base_dir)) {
            foreach ($base_dir as $suffix => $dir) {
                // normalize the base directory with a trailing separator
                $path = rtrim($dir, DIRECTORY_SEPARATOR) . '/';

                $key = $prefix . (is_string($suffix) ? $suffix : '') . '\\';

                // initialize the namespace prefix array
                if (isset($this->prefixes[$key]) === false) {
                    $this->prefixes[$key] = array();
                }

                // retain the base directory for the namespace prefix
                if ($prepend) {
                    array_unshift($this->prefixes[$key], $path);
                } else {
                    array_push($this->prefixes[$key], $path);
                }
            }
        }
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param string $class The fully-qualified class name.
     * @return string|false The mapped file name on success, or false on
     * failure.
     */
    public function loadClass(string $class): string|false
    {
        // the current namespace prefix
        $prefix = $class;

        // work backwards through the namespace names of the fully-qualified
        // class name to find a mapped file name
        while (false !== $pos = strrpos($prefix, '\\')) {
            $nameSpace = explode('\\', $class);
            $classname = array_pop($nameSpace);

            // retain the trailing namespace separator in the prefix
            $prefix = substr($class, 0, $pos + 1);

            // try to load a mapped file for the prefix and relative class
            $mapped_file = $this->loadMappedFile($prefix, $classname);
            if ($mapped_file) {
                $this->registered[] = $mapped_file;
                return $mapped_file;
            }

            // remove the trailing namespace separator for the next iteration
            // of strrpos()
            $prefix = rtrim($prefix, '\\');
        }

        // never found a mapped file
        return false;
    }

    /**
     * Load the mapped file for a namespace prefix and relative class.
     *
     * @param string $prefix The namespace prefix.
     * @param string $relative_class The relative class name.
     * @return string|false False if no mapped file can be loaded, or the
     * name of the mapped file that was loaded.
     */
    protected function loadMappedFile(string $prefix, string $relative_class): string|false
    {
        // are there any base directories for this namespace prefix?
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }

        // look through base directories for this namespace prefix
        foreach ($this->prefixes[$prefix] as $base_dir) {
            // replace the namespace prefix with the base directory,
            // replace namespace separators with directory separators
            // in the relative class name, append with .php
            $file = $base_dir
                . str_replace('\\', '/', $relative_class)
                . '.php';

            // if the mapped file exists, require it
            if ($this->requireFile($file)) {
                // yes, we're done
                return $file;
            }
        }

        // never found it
        return false;
    }

    /**
     * If a file exists, require it from the file system.
     *
     * @param string $file The file to require.
     * @return bool True if the file exists, false if not.
     */
    protected function requireFile(string $file): bool
    {
        if (file_exists($file) && !in_array($file, $this->registered)) {
            require $file;
            return true;
        }
        return false;
    }
}