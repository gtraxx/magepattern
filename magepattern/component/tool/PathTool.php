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
            // Dossier actuel de ce fichier
            $currentDir = __DIR__;

            // Segments à supprimer (insensible à la casse pour plus de flexibilité)
            $search = array_merge(['Component', 'Tool'], $extendSearch);

            // Transformation du chemin en tableau de segments
            $segments = explode(DIRECTORY_SEPARATOR, $currentDir);

            // On filtre les segments qui correspondent à notre recherche
            $cleanSegments = array_filter($segments, function($segment) use ($search) {
                return !in_array($segment, $search) && $segment !== '';
            });

            // Reconstruction du chemin
            $path = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $cleanSegments) . DIRECTORY_SEPARATOR;

            // Nettoyage final des doubles séparateurs (cas Windows/Linux mixtes)
            $finalPath = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);

            if (empty($finalPath) || $finalPath === DIRECTORY_SEPARATOR) {
                throw new Exception('PathTool Error: Calculated base path is invalid or empty.');
            }

            return $finalPath;
        } catch (Throwable $e) {
            Logger::getInstance()->log($e, "php", "error");
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