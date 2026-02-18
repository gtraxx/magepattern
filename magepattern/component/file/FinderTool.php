<?php

namespace Magepattern\Component\File;

use FilesystemIterator;
use RecursiveArrayIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Magepattern\Component\Debug\Logger;

class FinderTool
{
    /**
     * Scanne un dossier et retourne la liste des fichiers/dossiers (Scan à plat).
     * @param string $directory Chemin du dossier
     * @param array $exclude    Noms de fichiers/dossiers à exclure
     * @return array|false
     */
    public static function scan(string $directory, array $exclude = ['.', '..']): array|false
    {
        try {
            if (!is_dir($directory)) {
                return false;
            }

            $files = [];
            // FilesystemIterator est plus moderne que DirectoryIterator pour ce cas
            $iterator = new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS);

            foreach ($iterator as $item) {
                if (!in_array($item->getFilename(), $exclude)) {
                    $files[] = $item->getFilename();
                }
            }
            return $files;
        } catch (\Exception $e) {
            Logger::getInstance()->log($e, "php_file", "error");
            return false;
        }
    }

    /**
     * Scanne récursivement un dossier pour lister tous les chemins.
     */
    public static function scanRecursive(string $directory): array
    {
        try {
            $result = [];
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                $result[] = $item->getPathname();
            }
            return $result;
        } catch (\Exception $e) {
            Logger::getInstance()->log($e, "php_file", "error");
            return [];
        }
    }

    /**
     * Filtre les fichiers selon leurs extensions (Include ou Exclude).
     * @param string $directory Le dossier à scanner
     * @param array $extensions Liste des extensions (ex: ['php', 'html'])
     * @param bool $exclude     Si true, exclut ces extensions. Si false, ne garde QUE ces extensions.
     */
    public static function filterByExtension(string $directory, array $extensions, bool $exclude = false): array
    {
        $result = [];
        try {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS));

            foreach ($iterator as $item) {
                if ($item->isFile()) {
                    $ext = $item->getExtension();

                    // Logique : Soit on exclut si trouvé, soit on inclut si trouvé
                    $match = in_array($ext, $extensions);

                    if (($exclude && !$match) || (!$exclude && $match)) {
                        $result[] = $item->getPathname();
                    }
                }
            }
        } catch (\Exception $e) {
            Logger::getInstance()->log($e, "php_file", "error");
        }
        return $result;
    }

    /**
     * Retourne la taille d'un dossier en octets (int).
     * (C'est mieux de retourner un int pour pouvoir le formater ensuite avec number_format ou convertir en MB).
     */
    public static function getSize(string $directory): int|false
    {
        try {
            $size = 0;
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS));

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
            return $size;
        } catch (\Exception $e) {
            Logger::getInstance()->log($e, "php_file", "error");
            return false;
        }
    }

    /**
     * Recherche une valeur dans un tableau multidimensionnel.
     * Note: Cette méthode est un utilitaire de tableau, mais conservée ici selon votre structure historique.
     */
    public static function searchInArray(mixed $needle, array $haystack, bool $strict = false): bool
    {
        try {
            $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($haystack));

            foreach ($iterator as $current) {
                if ($strict ? $current === $needle : $current == $needle) {
                    return true;
                }
            }
            return false;
        } catch (\Exception $e) {
            // Le log est simplifié pour éviter de dump tout le tableau dans les logs
            Logger::getInstance()->log("Search Error: " . $e->getMessage(), "php_array", "error");
            return false;
        }
    }
}