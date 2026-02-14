<?php

# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Mage Pattern.
# Copyright (C) 2012 - 2026 Gerits Aurelien
# -- END LICENSE BLOCK ------------------------------------

namespace Magepattern\Component\Tool;

use Magepattern\Component\Debug\Logger;
use Throwable;
use Exception;

/**
 * Class PathTool
 * * Fournit des utilitaires pour la manipulation et la validation des chemins système.
 */
class PathTool
{
    /**
     * Calcule le chemin racine (base path) du projet.
     * * Par défaut, remonte de deux niveaux à partir de ce fichier (Tool -> Component -> Racine).
     *
     * @param array $extendSearch Liste de segments de dossiers à supprimer du chemin si nécessaire.
     * @return string|false Le chemin nettoyé se terminant par un séparateur, ou false en cas d'erreur.
     */
    public static function basePath(array $extendSearch = []): string|false
    {
        try {
            $currentDir = __DIR__;

            // On ajoute 'lib' et 'vendor' aux dossiers techniques par défaut
            $default = ['component', 'tool', 'filter', 'lib', 'vendor', 'src', 'magepattern'];
            $search = array_map('strtolower', array_merge($extendSearch, $default));

            $segments = explode(DIRECTORY_SEPARATOR, $currentDir);
            $segments = array_values(array_filter($segments));

            // Remontée itérative
            while (!empty($segments) && in_array(strtolower(end($segments)), $search)) {
                array_pop($segments);
            }

            $prefix = (DIRECTORY_SEPARATOR === '/') ? DIRECTORY_SEPARATOR : '';
            $path = $prefix . implode(DIRECTORY_SEPARATOR, $segments) . DIRECTORY_SEPARATOR;

            return str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);

        } catch (\Throwable $e) {
            // Note: Assurez-vous que Logger est chargé avant d'appeler basePath
            // ou utilisez error_log() en fallback
            return false;
        }
    }

    /**
     * Détermine si un chemin est absolu.
     * * Gère les chemins Unix (/...) et Windows (C:\... ou \\...) ainsi que les schémas URL.
     *
     * @param string $file Le chemin à vérifier.
     * @return bool True si le chemin est absolu.
     */
    public static function isAbsolutePath(string $file): bool
    {
        if ($file === '') {
            return false;
        }

        // Vérification Unix / Réseau (/, \)
        if (str_starts_with($file, '/') || str_starts_with($file, '\\')) {
            return true;
        }

        // Vérification Windows (C:\, D:/, etc.)
        if (strlen($file) > 2
            && ctype_alpha($file[0])
            && $file[1] === ':'
            && (str_starts_with(substr($file, 2), '/') || str_starts_with(substr($file, 2), '\\'))
        ) {
            return true;
        }

        // Vérification Schéma (http://, phar://, etc.)
        return null !== parse_url($file, PHP_URL_SCHEME);
    }
}