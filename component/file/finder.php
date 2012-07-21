<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 20/07/12
 * Time: 21:04
 *
 */
class file_finder{
    /**
     * scans the directory and returns all files
     * @param string $directory
     * @param string exclude
     * @return array|null
     */
    public function scanDir($directory,$exclude=''){
        try{
            $file = null;
            $it = new DirectoryIterator($directory);
            for($it->rewind(); $it->valid(); $it->next()) {
                if(!$it->isDir() && !$it->isDot() && $it->isFile()){
                    if($it->getFilename() == $exclude) continue;
                    $file[] = $it->getFilename();
                }
            }
            return $file;
        }catch (Exception $e){
            $logger = new debug_logger(MP_TMP_DIR);
            $logger->log('error', 'php', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }
    /**
     * scan folders recursive and returns all folders
     * @param string $directory
     * @param string exclude
     * @return array|string
     */
    public function scanRecursiveDir($directory,$exclude=''){
        try{
            $file = '';
            $it = new DirectoryIterator($directory);
            for($it->rewind(); $it->valid(); $it->next()) {
                if($it->isDir() && !$it->isDot()){
                    if($it->getFilename() == $exclude) continue;
                    $file[] = $it->getFilename();
                }
            }
            return $file;
        }catch (Exception $e){
            $logger = new debug_logger(MP_TMP_DIR);
            $logger->log('error', 'php', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }
    /**
     * scans the folder and returns all folders and files
     * @param string $directory
     * @return string
     */
    public function scanRecursiveDirectoryFile($directory){
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory), RecursiveIteratorIterator::SELF_FIRST);
        $dir = '';
        foreach($objects as $name => $object){
            $dir[] .= $object->getFilename();
        }
        return $dir;
    }
    public function dirFilterIterator($directory){
        $directories = new AppendIterator () ;
        $directories->append (new RecursiveIteratorIterator (new RecursiveDirectoryIterator ($directory)));
        //$directories->append (new RecursiveIteratorIterator (new RecursiveDirectoryIterator ('/autre_repertoire/')));
        $itFiles = new ExtensionFilterIteratorDecorator($directories);
        $itFiles->setExtension ('.phtml');
        $t = '';
        foreach ( $itFiles as $Item )  {
            //applique le traitement à $Item
            return $t[] = $Item;
        }
    }

    /**
     * return size directory in bytes
     * @param string $directory
     */
    public function sizeDirectory($directory){
        try{
            $foldersize = 0;
            $dir = new sizeDirectory($directory);
            foreach($dir as $size) $foldersize += $size;
            return $foldersize.' bytes';
        }catch (Exception $e){
            $logger = new debug_logger(MP_TMP_DIR);
            $logger->log('error', 'php', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }
    /**
     *
     * @recursively check if a value is in array
     *
     * @param string $string (needle)
     *
     * @param array $array (haystack)
     *
     * @param bool $type (optional)
     *
     * @return bool
     *
     */
    function in_array_recursive($string, $array, $type=false)
    {
        /*** an recursive iterator object ***/
        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));

        /*** traverse the $iterator object ***/
        while($it->valid())
        {
            /*** check for a match ***/
            if( $type === false )
            {
                if( $it->current() == $string )
                {
                    return true;
                }
            }
            else
            {
                if( $it->current() === $string )
                {
                    return true;
                }
            }
            $it->next();
        }
        /*** if no match is found ***/
        return false;
    }
    /**
     * filterFiles => filter files with extension
     * $t = new magixcjquery_files_makefiles();
     * var_dump($t->filterFiles('mydir',array('gif','png','jpe?g')));
     * or
     * var_dump($t->filterFiles('mydir','php'));
     * @param $dir
     * @param $extension
     */
    public function filterFiles($directory,$extension){
        try {
            $filterfiles = new filterFiles($directory,$extension);
            $filter = '';
            foreach($filterfiles as $file) {
                if(($file->isDot()) || ($file->isDir())) continue;
                $filter[] .= $file;
            }
            return $filter;
        }catch (Exception $e){
            $logger = new debug_logger(MP_TMP_DIR);
            $logger->log('error', 'php', 'An error has occured : '.$e->getMessage(), debug_logger::LOG_VOID);
        }
    }
}
?>