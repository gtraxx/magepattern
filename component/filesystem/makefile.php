<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 20/07/12
 * Time: 21:09
 *
 */
class filesystem_makefile{
    /**
     * Creates a directory recursively.
     *
     * @param string|array|\Traversable $dirs The directory path
     * @param integer $mode The directory mode
     *
     * @throws Exception
     * @copyright symfony 2
     * 
     */
    public function mkdir($dirs, $mode = 0777){
        foreach ($this->toIterator($dirs) as $dir) {
            if (is_dir($dir)) {
                continue;
            }

            if (true !== @mkdir($dir, $mode, true)) {
                throw new Exception(sprintf('Failed to create %s', $dir));
            }
        }
    }
    /**
     * Checks the existence of files or directories.
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a \Traversable instance to check
     *
     * @return Boolean true if the file exists, false otherwise
     * @copyright symfony 2
     * 
     */
    public function exists($files)
    {
        foreach ($this->toIterator($files) as $file) {
            if (!file_exists($file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Removes files or directories.
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a \Traversable instance to remove
     *
     * @throws Exception
     * @copyright symfony 2
     * 
     */
    public function remove($files){
        $files = iterator_to_array($this->toIterator($files));
        $files = array_reverse($files);
        foreach ($files as $file) {
            if (!file_exists($file) && !is_link($file)) {
                continue;
            }

            if (is_dir($file) && !is_link($file)) {
                $this->remove(new \FilesystemIterator($file));

                if (true !== @rmdir($file)) {
                    throw new Exception(sprintf('Failed to remove directory %s', $file));
                }
            } else {
                // https://bugs.php.net/bug.php?id=52176
                if (defined('PHP_WINDOWS_VERSION_MAJOR') && is_dir($file)) {
                    if (true !== @rmdir($file)) {
                        throw new Exception(sprintf('Failed to remove file %s', $file));
                    }
                } else {
                    if (true !== @unlink($file)) {
                        throw new Exception(sprintf('Failed to remove file %s', $file));
                    }
                }
            }
        }
    }

    /**
     * This function rename files and dir
     *
     * @access public
     * @param $files
     * @throws Exception
     */
    public function rename($files){
        try{
            if(is_array($files)){
                foreach ($files as $origin => $target) {
                    // we check that target does not exist
                    if (is_readable($target)) {
                        throw new Exception(sprintf('Cannot rename because the target "%s" already exist.', $origin));
                    }
                    if(!file_exists($origin)){
                        continue;
                    }
                    if (true !== @rename($origin, $target)) {
                        throw new Exception(sprintf('Failed to rename %s', $origin));
                    }
                }
            }else{
                throw new Exception(sprintf('%s is not array', $files));
            }
        }catch(Exception $e) {
            $logger = new debug_logger(MP_TMP_DIR);
            $logger->log('php', 'error', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }

    /**
     * Copies a file.
     *
     * This method only copies the file if the origin file is newer than the target file.
     *
     * By default, if the target already exists, it is not overridden.
     *
     * @param string $originFile The original filename
     * @param string $targetFile The target filename
     * @param array|bool $override Whether to override an existing file or not
     *
     * @throws Exception
     * @copyright symfony 2
     */
    public function copy($originFile, $targetFile, $override = false)
    {
        $this->mkdir(dirname($targetFile));

        if (!$override && is_file($targetFile)) {
            $doCopy = filemtime($originFile) > filemtime($targetFile);
        } else {
            $doCopy = true;
        }

        if ($doCopy) {
            if (true !== @copy($originFile, $targetFile)) {
                throw new Exception(sprintf('Failed to copy %s to %s', $originFile, $targetFile));
            }
        }
    }

    /**
     * Change mode for an array of files or directories.
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a \Traversable instance to change mode
     * @param integer $mode The new mode (octal)
     * @param integer $umask The mode mask (octal)
     * @param Boolean $recursive Whether change the mod recursively or not
     *
     * @throws Exception When the change fail
     */
    public function chmod($files, $mode, $umask = 0000, $recursive = false)
    {
        foreach ($this->toIterator($files) as $file) {
            if ($recursive && is_dir($file) && !is_link($file)) {
                $this->chmod(new \FilesystemIterator($file), $mode, $umask, true);
            }
            if (true !== @chmod($file, $mode & ~$umask)) {
                throw new Exception(sprintf('Failed to chmod file %s', $file));
            }
        }
    }

    /**
     * Change the owner of an array of files or directories
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a \Traversable instance to change owner
     * @param string $user The new owner user name
     * @param Boolean $recursive Whether change the owner recursively or not
     *
     * @throws Exception When the change fail
     */
    public function chown($files, $user, $recursive = false)
    {
        foreach ($this->toIterator($files) as $file) {
            if ($recursive && is_dir($file) && !is_link($file)) {
                $this->chown(new \FilesystemIterator($file), $user, true);
            }
            if (is_link($file) && function_exists('lchown')) {
                if (true !== @lchown($file, $user)) {
                    throw new Exception(sprintf('Failed to chown file %s', $file));
                }
            } else {
                if (true !== @chown($file, $user)) {
                    throw new Exception(sprintf('Failed to chown file %s', $file));
                }
            }
        }
    }

    /**
     * Change the group of an array of files or directories
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a \Traversable instance to change group
     * @param string $group The group name
     * @param Boolean $recursive Whether change the group recursively or not
     *
     * @throws Exception When the change fail
     */
    public function chgrp($files, $group, $recursive = false)
    {
        foreach ($this->toIterator($files) as $file) {
            if ($recursive && is_dir($file) && !is_link($file)) {
                $this->chgrp(new \FilesystemIterator($file), $group, true);
            }
            if (is_link($file) && function_exists('lchgrp')) {
                if (true !== @lchgrp($file, $group)) {
                    throw new Exception(sprintf('Failed to chgrp file %s', $file));
                }
            } else {
                if (true !== @chgrp($file, $group)) {
                    throw new Exception(sprintf('Failed to chgrp file %s', $file));
                }
            }
        }
    }
    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     *
     * @return Boolean
     * @copyright symfony 2
     * 
     */
    public function isAbsolutePath($file){
        if (strspn($file, '/\\', 0, 1)
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && substr($file, 1, 1) === ':'
                && (strspn($file, '/\\', 2, 1))
            )
            || null !== parse_url($file, PHP_URL_SCHEME)
        ) {
            return true;
        }

        return false;
    }

    /**
     * writing values in constants
     * @param string $name
     * @param void $val
     * @param path construct $str
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
    public function writeConstValue($name,$val,&$str,$quote=true){
        if($quote){
            $quote = '$1,\''.$val.'\');';
        }else{
            $quote = '$1,'.$val.');';
        }
        $val = str_replace("'","\'",$val);
        $str = preg_replace('/(\''.$name.'\')(.*?)$/ms',$quote,$str);
    }

    /**
     * @param mixed $files
     *
     * @return \Traversable
     * @copyright symfony 2
     * 
     */
    private function toIterator($files)
    {
        if (!$files instanceof \Traversable) {
            $files = new \ArrayObject(is_array($files) ? $files : array($files));
        }

        return $files;
    }
}
?>