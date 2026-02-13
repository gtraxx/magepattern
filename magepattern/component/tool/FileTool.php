<?php

# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Mage Pattern.
# Copyright (C) 2012 - 2026 Gerits Aurelien
# -- END LICENSE BLOCK ------------------------------------

namespace Magepattern\Component\Tool;

use Magepattern\Component\Debug\Logger;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use RuntimeException;
use InvalidArgumentException;

/**
 * Class FileTool
 * * Fournit des utilitaires avancés pour la manipulation du système de fichiers :
 * création, suppression récursive, gestion des droits et compression.
 */
class FileTool
{
    /**
     * Niveau de compression par défaut pour les fichiers GZ (0-9).
     */
    protected static int $GZCompressionLevel = 9;

    /**
     * Crée un ou plusieurs répertoires récursivement.
     *
     * @param string|iterable $dirs Chemin unique ou tableau/itérateur de chemins.
     * @param int $mode Mode de permission (octal, ex: 0777).
     * @return bool True si tous les répertoires existent ou ont été créés.
     */
    public static function mkdir(string|iterable $dirs, int $mode = 0777): bool
    {
        try {
            foreach (self::toIterable($dirs) as $dir) {
                if (is_dir($dir)) {
                    continue;
                }
                if (!@mkdir($dir, $mode, true) && !is_dir($dir)) {
                    throw new RuntimeException(sprintf('Failed to create directory: %s', $dir));
                }
            }
            return true;
        } catch (Throwable $e) {
            Logger::getInstance()->log($e, "php", "error");
            return false;
        }
    }

    /**
     * Vérifie l'existence de fichiers ou répertoires.
     *
     * @param string|iterable $files Fichier(s) à vérifier.
     * @return bool True si tous les fichiers existent.
     */
    public static function exists(string|iterable $files): bool
    {
        foreach (self::toIterable($files) as $file) {
            if (!file_exists($file)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Supprime des fichiers ou des répertoires de manière récursive.
     *
     * @param string|iterable $files Fichier(s) ou dossier(s) à supprimer.
     * @return bool True si la suppression est réussie.
     */
    public static function remove(string|iterable $files): bool
    {
        try {
            $items = is_array($files) ? $files : iterator_to_array(self::toIterable($files));
            $items = array_reverse($items);

            foreach ($items as $file) {
                if (!file_exists($file) && !is_link($file)) {
                    continue;
                }

                if (is_dir($file) && !is_link($file)) {
                    self::remove(new FilesystemIterator($file, FilesystemIterator::SKIP_DOTS));
                    if (!@rmdir($file)) {
                        throw new RuntimeException(sprintf('Failed to remove directory: %s', $file));
                    }
                } else {
                    if (!@unlink($file)) {
                        throw new RuntimeException(sprintf('Failed to remove file: %s', $file));
                    }
                }
            }
            return true;
        } catch (Throwable $e) {
            Logger::getInstance()->log($e, "php", "error");
            return false;
        }
    }

    /**
     * Renomme ou déplace un fichier/répertoire.
     *
     * @param string $origin Chemin d'origine.
     * @param string $target Nouveau chemin.
     * @return bool
     */
    public static function rename(string $origin, string $target): bool
    {
        try {
            if (file_exists($target)) {
                throw new RuntimeException(sprintf('Target "%s" already exists.', $target));
            }
            if (!file_exists($origin)) {
                return false;
            }
            if (!@rename($origin, $target)) {
                throw new RuntimeException(sprintf('Failed to rename %s to %s', $origin, $target));
            }
            return true;
        } catch (Throwable $e) {
            Logger::getInstance()->log($e, "php", "error");
            return false;
        }
    }

    /**
     * Copie un fichier avec vérification de la date de modification.
     *
     * @param string $originFile Fichier source.
     * @param string $targetFile Destination.
     * @param bool $override Forcer l'écrasement si la cible existe.
     * @return bool
     */
    public static function copy(string $originFile, string $targetFile, bool $override = false): bool
    {
        try {
            if (!file_exists($originFile)) {
                throw new RuntimeException("Origin file not found: $originFile");
            }

            self::mkdir(dirname($targetFile));

            $doCopy = $override || !is_file($targetFile) || (filemtime($originFile) > filemtime($targetFile));

            if ($doCopy) {
                if (!@copy($originFile, $targetFile)) {
                    throw new RuntimeException(sprintf('Failed to copy %s to %s', $originFile, $targetFile));
                }
                return true;
            }
            return false;
        } catch (Throwable $e) {
            Logger::getInstance()->log($e, "php", "error");
            return false;
        }
    }

    /**
     * Modifie les permissions (chmod) de manière éventuellement récursive.
     *
     * @param string|iterable $files Fichier(s) cible(s).
     * @param int $mode Mode octal (ex: 0755).
     * @param int $umask Masque à appliquer.
     * @param bool $recursive Appliquer aux sous-dossiers.
     * @return bool
     */
    public static function chmod(string|iterable $files, int $mode, int $umask = 0000, bool $recursive = false): bool
    {
        try {
            foreach (self::toIterable($files) as $file) {
                if ($recursive && is_dir($file) && !is_link($file)) {
                    self::chmod(new FilesystemIterator($file, FilesystemIterator::SKIP_DOTS), $mode, $umask, true);
                }
                if (!@chmod($file, $mode & ~$umask)) {
                    throw new RuntimeException(sprintf('Failed to chmod: %s', $file));
                }
            }
            return true;
        } catch (Throwable $e) {
            Logger::getInstance()->log($e, "php", "error");
            return false;
        }
    }

    /**
     * Change le propriétaire des fichiers.
     *
     * @param string|iterable $files
     * @param string|int $user Nom d'utilisateur ou UID.
     * @param bool $recursive
     * @return bool
     */
    public static function chown(string|iterable $files, string|int $user, bool $recursive = false): bool
    {
        try {
            foreach (self::toIterable($files) as $file) {
                if ($recursive && is_dir($file) && !is_link($file)) {
                    self::chown(new FilesystemIterator($file, FilesystemIterator::SKIP_DOTS), $user, true);
                }
                $result = (is_link($file) && function_exists('lchown')) ? @lchown($file, $user) : @chown($file, $user);
                if (!$result) {
                    throw new RuntimeException(sprintf('Failed to chown: %s', $file));
                }
            }
            return true;
        } catch (Throwable $e) {
            Logger::getInstance()->log($e, "php", "error");
            return false;
        }
    }

    /**
     * Supprime récursivement les fichiers d'un dossier sans supprimer les dossiers eux-mêmes.
     *
     * @param string $directory Dossier cible.
     * @param bool $debug Si true, retourne la liste des fichiers sans les supprimer.
     * @return array Liste des fichiers traités ou résultats de suppression.
     */
    public static function removeRecursiveFile(string $directory, bool $debug = false): array
    {
        if (!is_dir($directory)) return [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $results = [];
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filePath = $file->getRealPath();
                $results[] = $debug ? $filePath : @unlink($filePath);
            }
        }
        return $results;
    }

    /**
     * Modifie une valeur de constante dans un contenu de fichier (config).
     *
     * @param string $name Nom de la constante.
     * @param string $val Nouvelle valeur.
     * @param string $content Contenu du fichier passé par référence.
     * @param bool $quote Entourer la valeur de quotes.
     */
    public static function writeConstValue(string $name, string $val, string &$content, bool $quote = true): void
    {
        $escapedVal = str_replace("'", "\'", $val);
        $replacement = $quote ? "'$escapedVal'" : $escapedVal;

        // Regex supportant define('NAME', 'val') ou 'NAME' => 'val'
        $pattern = "/('" . preg_quote($name, '/') . "')\s*,\s*(.*?)\s*[\)|,]/ms";
        $content = preg_replace($pattern, "$1, $replacement", $content);
    }

    /**
     * Compresse un fichier au format GZ.
     *
     * @param string $targetFile Chemin du fichier .gz à créer.
     * @param string $sourceDataPath Chemin du fichier source à compresser.
     * @param int $level Niveau de compression (1-9).
     * @return bool
     */
    public static function makeGZFile(string $targetFile, string $sourceDataPath, int $level = 9): bool
    {
        try {
            if (!extension_loaded('zlib')) {
                throw new RuntimeException('zlib extension not loaded');
            }

            if (!is_readable($sourceDataPath)) {
                throw new RuntimeException("Source file not readable: $sourceDataPath");
            }

            $content = file_get_contents($sourceDataPath);
            $gz = gzopen($targetFile, 'wb' . $level);

            if (!$gz) {
                throw new RuntimeException("Unable to create GZ file: $targetFile");
            }

            gzwrite($gz, $content);
            gzclose($gz);

            return true;
        } catch (Throwable $e) {
            Logger::getInstance()->log($e, "php", "error");
            return false;
        }
    }

    /**
     * Récupère des données sérialisées en cache si elles ont moins de 2 minutes.
     *
     * @param string $file Chemin du fichier cache.
     * @return mixed Les données désérialisées, null si expiré, false si inexistant.
     */
    public static function getCache(string $file): mixed
    {
        if (!file_exists($file)) return false;

        if (filemtime($file) > (time() - 120)) {
            $content = file_get_contents($file);
            return unserialize($content, ['allowed_classes' => true]);
        }

        @unlink($file);
        return null;
    }

    /**
     * Normalise une entrée vers un itérable.
     *
     * @param string|iterable $input
     * @return iterable
     */
    private static function toIterable(string|iterable $input): iterable
    {
        return is_string($input) ? [$input] : $input;
    }
}