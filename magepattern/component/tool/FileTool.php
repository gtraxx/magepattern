<?php
namespace Magepattern\Component\Tool;
use Magepattern\Component\Debug\Logger;

class FileTool
{
    /**
     * level compressor for GZ
     * @var int $GZCompressionLevel
     */
    protected static int $GZCompressionLevel = 0;

    /**
     * Creates a directory recursively.
     *
     * @param string|array|\Traversable $dirs The directory path
     * @param int $mode The directory mode
     * @return bool
     */
    public static function mkdir(string|array|\Traversable $dirs, $mode = 0777): bool
    {
        try {
            foreach (ArrayTool::toIterator($dirs) as $dir) {
                if (is_dir($dir)) continue;
                if (true !== @mkdir($dir, $mode, true)) throw new \Exception(sprintf('Failed to create %s', $dir),E_WARNING);
            }
            return true;
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e);
            return false;
        }
    }

    /**
     * Checks the existence of files or directories.
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a \Traversable instance to check
     * @return Boolean true if the file exists, false otherwise
     */
    public static function exists(string|array|\Traversable $files): bool
    {
        foreach (ArrayTool::toIterator($files) as $file) {
            if (!file_exists($file)) return false;
        }
        return true;
    }

    /**
     * Removes files or directories.
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a \Traversable instance to remove
     * @return bool
     */
    public static function remove(string|array|\Traversable $files): bool
    {
        $files = iterator_to_array(ArrayTool::toIterator($files));
        $files = array_reverse($files);

        try {
            foreach ($files as $file) {
                if (!file_exists($file) && !is_link($file)) continue;

                if (is_dir($file) && !is_link($file)) {
                    self::remove(new \FilesystemIterator($file));
                    if (true !== @rmdir($file)) throw new \Exception(sprintf('Failed to remove directory %s', $file), E_WARNING);
                }
                else {
                    $removing = (defined('PHP_WINDOWS_VERSION_MAJOR') && is_dir($file)) ? @rmdir($file) : @unlink($file);
                    if (true !== $removing) throw new \Exception(sprintf('Failed to remove file %s', $file), E_WARNING);
                }
            }
            return true;
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e);
            return false;
        }
    }

    /**
     * This function rename files and dir
     *
     * @access public
     * @param array $files
     * @return bool
     */
    public static function rename(array $files): bool
    {
        try {
            if (is_readable($files['target'])) throw new \Exception(sprintf('Cannot rename because the target "%s" already exist.', $files['origin']),E_WARNING);
            if (!file_exists($files['origin'])) return false;
            if (true !== @rename($files['origin'], $files['target'])) throw new \Exception(sprintf('Failed to rename %s', $files['origin']),E_WARNING);
            return true;
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e);
            return false;
        }
    }

    /**
     * Copies a file.
     *
     * This method only copies the file if the origin file is newer than the target file.
     * By default, if the target already exists, it is not overridden.
     *
     * @param string $originFile The original filename
     * @param string $targetFile The target filename
     * @param bool $override Whether to override an existing file or not
     * @return bool
     */
    public static function copy(string $originFile, string $targetFile, $override = false): bool
    {
        self::mkdir(dirname($targetFile));
        $doCopy = ($override || !is_file($targetFile)) ?? filemtime($originFile) > filemtime($targetFile);

        if ($doCopy) {
            try {
                if (true !== @copy($originFile, $targetFile)) throw new \Exception(sprintf('Failed to copy %s to %s', $originFile, $targetFile));
                return true;
            }
            catch(\Exception $e) {
                Logger::getInstance()->log($e);
                return false;
            }
        }
        else {
            Logger::getInstance()->log('Target file newer than the origin file to copy');
            return false;
        }
    }

    /**
     * Change mode for an array of files or directories.
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a \Traversable instance to change mode
     * @param int $mode The new mode (octal)
     * @param int $umask The mode mask (octal)
     * @param bool $recursive Whether change the mod recursively or not
     * @return bool
     */
    public static function chmod(string|array|\Traversable $files, int $mode, $umask = 0000, $recursive = false): bool
    {
        try {
            foreach (ArrayTool::toIterator($files) as $file) {
                if ($recursive && is_dir($file) && !is_link($file)) self::chmod(new \FilesystemIterator($file), $mode, $umask, true);
                if (true !== @chmod($file, $mode & ~$umask)) throw new \Exception(sprintf('Failed to chmod file %s', $file),E_WARNING);
            }
            return true;
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e);
            return false;
        }
    }

    /**
     * Change the owner of an array of files or directories
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a \Traversable instance to change owner
     * @param string $user The new owner user name
     * @param bool $recursive Whether change the owner recursively or not
     * @return bool
     */
    public static function chown(string|array|\Traversable $files, string $user, $recursive = false): bool
    {
        try {
            foreach (ArrayTool::toIterator($files) as $file) {
                if ($recursive && is_dir($file) && !is_link($file)) self::chown(new \FilesystemIterator($file), $user, true);
                $chownResult = (is_link($file) && function_exists('lchown')) ? @lchown($file, $user) : @chown($file, $user);
                if (true !== $chownResult) throw new \Exception(sprintf('Failed to chown file %s', $file), E_WARNING);
            }
            return true;
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e);
            return false;
        }
    }

    /**
     * Change the group of an array of files or directories
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a \Traversable instance to change group
     * @param string $group The group name
     * @param bool $recursive Whether change the group recursively or not
     * @return bool
     */
    public static function chgrp(string|array|\Traversable $files, string $group, $recursive = false): bool
    {
        try {
            foreach (ArrayTool::toIterator($files) as $file) {
                if ($recursive && is_dir($file) && !is_link($file)) self::chgrp(new \FilesystemIterator($file), $group, true);
                $chgrpResult = (is_link($file) && function_exists('lchgrp')) ? @lchgrp($file, $group) : @chgrp($file, $group);
                if (true !== $chgrpResult) throw new \Exception(sprintf('Failed to chgrp file %s', $file));
            }
            return true;
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e);
            return false;
        }
    }

    /**
     * Erase Recursive file in multi dir
     * @param string $directory
     * @param bool $debug
     * @return array
     */
    public static function removeRecursiveFile(string $directory, $debug=false): array
    {
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory), \RecursiveIteratorIterator::SELF_FIRST);
        $dir = [];
        foreach($objects as $name => $object) {
            if($object->isDir($name)) continue;
            $dir[] =  $debug == true ? $name :  @unlink($name);
        }
        return $dir;
    }

    /**
     * writing values in constants
     * @param string $name
     * @param string $val
     * @param string path construct $str
     * Creates config.php file
     * @example :
        $full_conf = file_get_contents($config_in);
        writeConstValue('M_DBDRIVER',$M_DBDRIVER,$full_conf);
        writeConstValue('M_DBHOST',$M_DBHOST,$full_conf);
        writeConstValue('M_DBUSER',$M_DBUSER,$full_conf);
        writeConstValue('M_DBPASSWORD',$M_DBPASSWORD,$full_conf);
        writeConstValue('M_DBNAME',$M_DBNAME,$full_conf);
        writeConstValue('M_LOG',$M_LOG,$full_conf);
        writeConstValue('M_TMP_DIR',$M_TMP_DIR,$full_conf);
        writeConstValue('M_FIREPHP',$M_FIREPHP,$full_conf);
     * @param bool $quote
     */
    public static function writeConstValue(string $name, string $val, string &$str, $quote = true)
    {
        $quote = $quote ? '$1,\''.$val.'\');' : '$1,'.$val.');';
        $val = str_replace("'","\'",$val);
        $str = preg_replace('/(\''.$name.'\')(.*?)$/ms',$quote,$str);
    }

    /**
     * protected abstract function for create file XML and create GZ
     * @param string $file
     * @param mixed $data
     * @return bool
     */
    public function makeGZFile(string $file, mixed $data): bool
    {
        try {
            if(self::$GZCompressionLevel) {
                if(!extension_loaded('zlib')) throw new \Exception('Unable to find zlib extension');

                if(!$fp = fopen($data, "r")) throw new \Exception('Unable to open sitemap file : '.$file);
                $filesize = filesize($data);

                if($filesize === false) throw new \Exception("filesize error");

                $datafile = fread($fp, $filesize);
                fclose($fp);
                $mode = 'w'.self::$GZCompressionLevel;

                if(!$zp = gzopen($file, $mode)) throw new \Exception('Unable to create/update GZIP sitemap file : '.$file);

                gzwrite($zp, $datafile);
                gzclose($zp);
            }
            return true;
        }
        catch(\Exception $e) {
            Logger::getInstance()->log($e);
            return false;
        }
    }

    /**
     * @param mixed $data
     * @param int $level
     * @return false|string
     */
    protected function compress(mixed $data, int $level = 0): false|string
    {

        if(!$level) return $data;
        return gzcompress($data, $level);
    }

    /**
     * Check cache
     * If younger than 5 min, returns it
     * If older, delete it and return null
     *
     * @param string $file
     * @return mixed
     */
    private function getCache(string $file): mixed
    {
        if(file_exists($file)) {
            if(filemtime($file) > (time() - 60 * 2)) {
                return unserialize(file_get_contents($file));
            }
            else {
                unlink($file);
                return true;
            }
        }
        return false;
    }
}