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
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
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
     * Exclude all files with the defined extensions
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
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }

    /**
     * check if a value is in array
     *
     * $tableau = [
     *      'a' => 1,
     *      'b' => ['c' => 2, 'd' => 'recherche_moi'],
     *      'e' => 3
     * ];
     *
     * $resultat = Finder::arrayContainsRecursive('recherche_moi', $tableau);
     *
     * if ($resultat) {
     * echo "'recherche_moi' a été trouvé dans le tableau.\n";
     * } else {
     *      echo "'recherche_moi' n'a pas été trouvé dans le tableau.\n";
     * }
     * Recherche un entier
     * $tableau = [
     *      10,
     *      [20, 30, [40, 50]],
     *      60,
     * ];
     *
     * $resultat = Finder::arrayContainsRecursive(40, $tableau);
     *
     * if ($resultat) {
     *      echo "40 a été trouvé dans le tableau.\n";
     * } else {
     *      echo "40 n'a pas été trouvé dans le tableau.\n";
     * }
     * Recherche d'une valeur avec comparaison stricte (type = true)
     * $resultat1 = Finder::arrayContainsRecursive(10, $tableau); // Comparaison non stricte (par défaut)
     * $resultat2 = Finder::arrayContainsRecursive(10, $tableau, true); // Comparaison stricte
     *
     * echo "Résultat 1 (non strict) : " . ($resultat1 ? 'true' : 'false') . "\n";
     * echo "Résultat 2 (strict) : " . ($resultat2 ? 'true' : 'false') . "\n";
     * Recherche d'une valeur absente
     * $tableau = [
     *      'a' => 1,
     *      'b' => ['c' => 2, 'd' => 3],
     *      'e' => 4,
     * ];
     *
     * $resultat = Finder::arrayContainsRecursive(5, $tableau);
     *
     * if ($resultat) {
     *      echo "5 a été trouvé dans le tableau.\n";
     * } else {
     *      echo "5 n'a pas été trouvé dans le tableau.\n";
     * }
     * @param string $needle
     * @param array $haystack
     * @param bool $type
     * @return bool
     */
    public static function arrayContainsRecursive(string $needle, array $haystack, bool $type = false): bool
    {
        try {
            $rii = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($haystack));

            while ($rii->valid()) {
                if (($type === false && $rii->current() == $needle) || $rii->current() === $needle) {
                    return true;
                }
                $rii->next();
            }

            return false;

        } catch (\Exception $e) {
            Logger::getInstance()->log(
                'Erreur dans arrayContainsRecursive : ' . $e->getMessage() . "\n" .
                'Needle : ' . print_r($needle, true) . "\n" .
                'Haystack : ' . print_r($haystack, true) . "\n" .
                'Trace : ' . $e->getTraceAsString()
            );
            return false; // Ou lancez une exception, selon votre stratégie de gestion des erreurs
        }
    }

    /**
     * $directory = '/chemin/vers/votre/dossier';
     * $extension = 'txt';
     *
     * $files = Finder::filterFiles($directory, $extension);
     *
     * if ($files !== false) {
     * foreach ($files as $file) {
     *      echo $file . "\n";
     * }
     * } else {
     *      echo 'Erreur lors du filtrage des fichiers.';
     * }
     * Includes all files with the extension
     * @param string $directory
     * @param string $extension
     * @return array|false
     */
    public static function filterFiles(string $directory, string $extension): array|false
    {
        try {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            $filter = [];

            foreach ($iterator as $file) {
                if ($file->isDot() || $file->isDir()) {
                    continue;
                }

                if ($file->isFile() && pathinfo($file->getFilename(), PATHINFO_EXTENSION) === $extension) {
                    $filter[] = $file->getPathname();
                }
            }

            return $filter;
        } catch (\Exception $e) {
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }
}