<?php
namespace Magepattern\Component\File;
use Magepattern\Component\Debug\Logger;

class Finder
{
    /**
     * scans the directory and returns all files
     * @param string $directory
     * @param bool $recursive
     * @param string|array $exclude
     * @return array|false
     */
    public static function scanDir(string $directory, bool $recursive = false, string|array $exclude = []): array|false
    {
        try {
            $files = [];
            $di = new \DirectoryIterator($directory);
            for($di->rewind(); $di->valid(); $di->next()) {
                if(!$di->isDot()) {
                    if(!$recursive && !$di->isDir() && $di->isFile()) {
                        $save = is_array($exclude) ? !in_array($di->getFilename(), $exclude) : $di->getFilename() !== $exclude;
                        if($save) $files[] = $di->getFilename();
                    }
                    elseif($di->isDir()){
                        $save = is_array($exclude) ? !in_array($di->getFilename(), $exclude) : $di->getFilename() !== $exclude;
                        if($save) $files[] = $di->getFilename();
                    }
                }
            }
            return $files;
        }
        catch (\Exception $e) {
            Logger::getInstance()->log($e);
            return false;
        }
    }

    /**
     * scan folders recursive and returns all folders
     * @deprecated use scanDir with recursive instead
     * @param string $directory
     * @param string|array $exclude
     * @return array|false
     */
    #[Deprecated] public function scanRecursiveDir(string $directory, string|array $exclude = []): array|false
    {
        return $this->scanDir($directory, true, $exclude);
    }

    /**
     * scans the folder and returns all folders and files
     * @param string $directory
     * @return array
     */
    public static function scanRecursiveDirectoryFile(string $directory): array
    {
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory), \RecursiveIteratorIterator::SELF_FIRST);
        $dir = [];
        foreach($objects as $object) {
            $dir[] = $object->getFilename();
        }
        return $dir;
    }

    /**
     *  $directory = '/chemin/vers/votre/dossier';
     *  $excludeExtensions = ['phtml', 'txt'];
     *
     *  $files = dirFilterIterator($directory, $excludeExtensions);
     *
     *  foreach ($files as $file) {
     *       echo $file . "\n";
     *  }
     * @param string $directory
     * @param array $excludeExtensions
     * @return array
     */
    public static function dirFilterIterator(string $directory, array $excludeExtensions = []): array
    {
        $directoryIterator = new \RecursiveDirectoryIterator($directory);
        $recursiveIterator = new \RecursiveIteratorIterator($directoryIterator);

        $result = [];
        foreach ($recursiveIterator as $item) {
            if ($item->isFile()) {
                $extension = pathinfo($item->getPathname(), PATHINFO_EXTENSION);
                if (!in_array($extension, $excludeExtensions)) {
                    $result[] = $item->getPathname();
                }
            }
        }

        return $result;
    }

    /**
     * return size directory in bytes
     * $directory = '/chemin/vers/votre/dossier';
     * $size = Finder::sizeDirectory($directory);
     *
     * if ($size !== false) {
     * echo 'La taille du répertoire est de : ' . $size;
     * } else {
     *     echo 'Erreur lors du calcul de la taille du répertoire.';
     * }
     * @param string $directory
     * @return string|bool
     */
    public static function sizeDirectory(string $directory): string|bool
    {
        try {
            $foldersize = 0;
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $foldersize += $file->getSize();
                }
            }

            return $foldersize . ' bytes';
        } catch (\Exception $e) {
            Logger::getInstance()->log($e);
            return false;
        }
    }

    /**
     * @recursively check if a value is in array
     * @param string $needle ()
     * @param array $haystack ()
     * @param bool $type (optional)
     * @return bool
     */
    public static function in_array_recursive(string $needle, array $haystack, bool $type = false): bool
    {
        /*** an recursive iterator object ***/
        $rii = new RecursiveIteratorIterator(new RecursiveArrayIterator($haystack));

        /*** traverse the $iterator object ***/
        while($rii->valid())
        {
            /*** check for a match ***/
            if(($type === false && $rii->current() == $needle) || $rii->current() === $needle) return true;
            $rii->next();
        }
        /*** if no match is found ***/
        return false;
    }

    /**
     * filterFiles => filter files with extension
     * $t = new file_finder();
     * var_dump($t->filterFiles('mydir',['gif','png','jpe?g']));
     * or
     * var_dump($t->filterFiles('mydir','php'));
     * @param string $directory
     * @param string $extension
     * @internal param $dir
     * @return array|false
     */
    public static function filterFiles(string $directory, string $extension): array|false
    {
        try {
            $filterfiles = new \filterFiles($directory,$extension);
            $filter = [];
            foreach($filterfiles as $file) {
                if(($file->isDot()) || ($file->isDir())) continue;
                $filter[] .= $file;
            }
            return $filter;
        }
        catch (\Exception $e){
            Logger::getInstance()->log($e);
            return false;
        }
    }
}