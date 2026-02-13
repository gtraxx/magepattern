<?php

# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Mage Pattern.
# The toolkit PHP for developer
# Copyright (C) 2012 - 2026 Gerits Aurelien
#
# -- END LICENSE BLOCK ------------------------------------

namespace Magepattern\Component\HTTP;

use Magepattern\Component\Debug\Logger;
use JsonException;

/**
 * Class JSON
 * * Fournit une interface robuste pour l'encodage et le décodage JSON
 * avec gestion native des exceptions et journalisation des erreurs.
 */
class JSON
{
    /**
     * Options de configuration pour les opérations JSON.
     */
    private array $options = [
        'decode' => [
            'assoc' => true,
            'depth' => 512,
            'flags' => JSON_THROW_ON_ERROR,
        ],
        'encode' => [
            'flags' => JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
            'depth' => 512,
        ],
    ];

    /**
     * JSON constructor.
     * * @param array $customOptions Permet de surcharger les flags ou la profondeur par défaut.
     */
    public function __construct(array $customOptions = [])
    {
        $this->options = array_replace_recursive($this->options, $customOptions);
    }

    /**
     * Décode une chaîne JSON en structure PHP.
     *
     * @param string $json La chaîne à décoder.
     * @param array $overrideOptions Options locales pour cet appel spécifique.
     * @return mixed Données décodées ou null en cas d'erreur.
     */
    public function decode(string $json, array $overrideOptions = []): mixed
    {
        $opt = array_merge($this->options['decode'], $overrideOptions);

        try {
            return json_decode($json, $opt['assoc'], $opt['depth'], $opt['flags']);
        } catch (JsonException $e) {
            Logger::getInstance()->log(
                "JSON Decode Error: " . $e->getMessage(),
                'php',
                'json_error',
                Logger::LOG_MONTH,
                Logger::LOG_LEVEL_ERROR
            );
            return null;
        }
    }

    /**
     * Encode une valeur PHP en chaîne JSON.
     *
     * @param mixed $value La valeur à encoder (tableau, objet, etc.).
     * @param array $overrideOptions Options locales (ex: JSON_PRETTY_PRINT).
     * @return string|null La chaîne JSON résultante ou null en cas d'erreur.
     */
    public function encode(mixed $value, array $overrideOptions = []): ?string
    {
        $opt = array_merge($this->options['encode'], $overrideOptions);

        try {
            return json_encode($value, $opt['flags'], $opt['depth']);
        } catch (JsonException $e) {
            Logger::getInstance()->log(
                "JSON Encode Error: " . $e->getMessage(),
                'php',
                'json_error',
                Logger::LOG_MONTH,
                Logger::LOG_LEVEL_ERROR
            );
            return null;
        }
    }

    /**
     * Valide la syntaxe d'une chaîne JSON sans la décoder.
     * * @param string $json
     * @return bool
     */
    public function isValid(string $json): bool
    {
        if (function_exists('json_validate')) {
            return json_validate($json);
        }

        try {
            json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return true;
        } catch (JsonException $e) {
            Logger::getInstance()->log(
                "JSON isValid Error: " . $e->getMessage(),
                'php',
                'json_error',
                Logger::LOG_MONTH,
                Logger::LOG_LEVEL_ERROR
            );
            return false;
        }
    }

    /**
     * Charge et décode un fichier JSON.
     *
     * @param string $path Chemin vers le fichier .json
     * @param array $overrideOptions
     * @return mixed
     */
    public function fromFile(string $path, array $overrideOptions = []): mixed
    {
        if (!file_exists($path) || !is_readable($path)) {
            Logger::getInstance()->log("JSON File missing or not readable: $path", 'php', 'json_error');
            return null;
        }

        $content = file_get_contents($path);
        return $this->decode($content, $overrideOptions);
    }

    /**
     * Encode et sauvegarde des données dans un fichier JSON.
     *
     * @param string $path Chemin de destination.
     * @param mixed $value Données à sauvegarder.
     * @param bool $pretty Activer le formatage lisible (Indentation).
     * @return bool True si l'écriture a réussi.
     */
    public function toFile(string $path, mixed $value, bool $pretty = true): bool
    {
        $flags = $this->options['encode']['flags'];

        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $json = $this->encode($value, ['flags' => $flags]);

        if ($json === null) {
            return false;
        }

        return (bool) file_put_contents($path, $json, LOCK_EX);
    }
}