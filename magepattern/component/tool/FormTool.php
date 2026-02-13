<?php

# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Mage Pattern.
# Copyright (C) 2012 - 2026 Gerits Aurelien
# -- END LICENSE BLOCK ------------------------------------

namespace Magepattern\Component\Tool;

use Magepattern\Component\Debug\Logger;
use Magepattern\Component\HTTP\Url;
use Throwable;
use ValueError;

/**
 * Class FormTool
 * Centralise le nettoyage et la validation des données issues des formulaires.
 */
class FormTool
{
    /**
     * Nettoyage simple : Trim + Escape HTML basique.
     */
    public static function simpleClean(string $str): string
    {
        return trim(HTMLTool::escapeHTML($str));
    }

    /**
     * Nettoyage extrême : Trim + Escape HTML complet/agressif.
     */
    public static function extremeClean(string $str): string
    {
        return trim(HTMLTool::escapeExtremeHTML($str));
    }

    /**
     * Nettoyage des balises : Trim + Strip Tags.
     */
    public static function tagClean(string $str): string
    {
        return trim(EscapeTool::clean($str));
    }

    /**
     * Nettoyage pour URL : Slugify + Trim.
     */
    public static function rewriteUrl(string $str): string
    {
        return trim(Url::clean($str));
    }

    /**
     * Nettoyage des guillemets (Quotes).
     */
    public static function cleanQuote(string $str): string
    {
        return trim(EscapeTool::cleanQuote($str));
    }

    /**
     * Nettoyage + Conversion en minuscules.
     */
    public static function cleanStrtolower(string $str): string
    {
        return trim(HTMLTool::escapeHTML(StringTool::strtolower($str)));
    }

    /**
     * Nettoyage + Troncature de texte.
     */
    public static function truncateClean(string $str, int $lg_max, string $delimiter): string
    {
        return trim(StringTool::truncate($str, $lg_max, $delimiter));
    }

    /**
     * Ne garde que les caractères alphanumériques.
     */
    public static function alphaNumeric(string $str): string
    {
        return trim(StringTool::isAlphaNumeric($str));
    }

    /**
     * Ne garde que les caractères numériques.
     */
    public static function numeric(string $str): string
    {
        return trim(MathTool::isNumeric($str));
    }

    /**
     * Nettoyage récursif d'un tableau.
     * Applique cleanQuote si la clé est présente dans $haystack, sinon simpleClean.
     *
     * @param array $array
     * @param string $haystack Chaîne contenant les clés nécessitant un traitement spécial (ex: 'content,description')
     * @return array
     */
    public static function arrayClean(array $array, string $haystack = 'content'): array
    {
        foreach ($array as $key => &$val) {
            if (is_array($val)) {
                $val = self::arrayClean($val, $haystack);
            } else {
                if (empty($val) && $val !== '0') { // '0' ne doit pas devenir null
                    $val = null;
                } else {
                    // Note: str_contains($haystack, $key) vérifie si la clé est dans la chaine de config.
                    // Ex: haystack="title,content", key="content" -> True.
                    $val = str_contains($haystack, (string)$key)
                        ? self::cleanQuote((string)$val)
                        : self::simpleClean((string)$val);
                }
            }
        }
        unset($val); // Sécurité : on casse la référence à la fin de la boucle
        return $array;
    }

    /**
     * Nettoyage extrême récursif d'un tableau.
     *
     * @param array $array
     * @return array
     */
    public static function arrayExtremeClean(array $array): array
    {
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $array[$key] = self::arrayExtremeClean($val);
            } else {
                $array[$key] = self::extremeClean((string)$val);
            }
        }
        return $array;
    }

    /**
     * Sanitize une valeur selon un type donné.
     *
     * @param string $str La valeur à nettoyer.
     * @param string $type mail|url|numeric|float
     * @param string $flag fraction|thousand|scientific
     * @return string
     */
    public static function sanitize(string $str, string $type, string $flag = ''): string
    {
        try {
            $filter = match ($type) {
                'mail'    => FILTER_SANITIZE_EMAIL,
                'url'     => FILTER_SANITIZE_URL,
                'numeric' => FILTER_SANITIZE_NUMBER_INT,
                'float'   => FILTER_SANITIZE_NUMBER_FLOAT,
                default   => throw new ValueError("Unknown sanitizer type: $type")
            };

            $options = 0;
            if ($type === 'float' && $flag !== '') {
                $options = match ($flag) {
                    'fraction'   => FILTER_FLAG_ALLOW_FRACTION,
                    'thousand'   => FILTER_FLAG_ALLOW_THOUSAND,
                    'scientific' => FILTER_FLAG_ALLOW_SCIENTIFIC,
                    default      => 0
                };
            }

            $result = filter_var($str, $filter, $options);

            // filter_var peut retourner false, on retourne une chaine vide pour respecter le typage
            return ($result === false) ? '' : (string)$result;

        } catch (Throwable $e) {
            Logger::getInstance()->log($e, "php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            // En cas d'erreur critique, on retourne la chaine vide par sécurité plutôt que la chaine brute
            return '';
        }
    }
}