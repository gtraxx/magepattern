<?php

# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Mage Pattern.
# Copyright (C) 2012 - 2026 Gerits Aurelien
# -- END LICENSE BLOCK ------------------------------------

namespace Magepattern\Component\Tool;

use ArrayObject;
use Traversable;
use IteratorAggregate;

class ArrayTool
{
    /**
     * Convertit une variable en itérateur (Traversable).
     * Si ce n'est pas un tableau ou un itérateur, on le transforme en tableau d'abord.
     *
     * @param mixed $var
     * @return Traversable
     */
    public static function toIterator(mixed $var): Traversable
    {
        if ($var instanceof Traversable) {
            return $var;
        }

        return new ArrayObject(is_array($var) ? $var : [$var]);
    }

    /**
     * Convertit un itérateur (ou un tableau) en tableau PHP standard.
     * Supporte la récursivité.
     *
     * @param iterable $iterator
     * @param bool $recursive
     * @return array
     */
    public static function iteratorToArray(iterable $iterator, bool $recursive = true): array
    {
        // Cas simple non récursif ou tableau natif
        if (!$recursive) {
            return is_array($iterator) ? $iterator : iterator_to_array($iterator);
        }

        // Si l'objet possède sa propre méthode toArray (ex: Collections)
        if (is_object($iterator) && method_exists($iterator, 'toArray')) {
            return $iterator->toArray();
        }

        $array = [];
        foreach ($iterator as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $array[$key] = $value;
                continue;
            }

            // Appel récursif si c'est un itérable (Tableau ou Objet Traversable)
            if (is_iterable($value)) {
                $array[$key] = self::iteratorToArray($value, $recursive);
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * Remplace les éléments d'un tableau par ceux d'un autre (Wrapper natif).
     *
     * @param array $arr Tableau de base.
     * @param array $new_arr Tableau de remplacement.
     * @return array
     */
    public static function replaceArray(array $arr, array $new_arr): array
    {
        return array_replace($arr, $new_arr);
    }

    /**
     * Retourne les valeurs d'une colonne spécifique du tableau d'entrée.
     * (Wrapper natif direct, suppression du polyfill PHP 5.5).
     *
     * @param array $input Tableau multidimensionnel.
     * @param string|int|null $columnKey La colonne à récupérer.
     * @param string|int|null $indexKey La colonne à utiliser comme index (optionnel).
     * @return array
     */
    public static function array_column(array $input, string|int|null $columnKey, string|int|null $indexKey = null): array
    {
        return array_column($input, $columnKey, $indexKey);
    }

    /**
     * Trie un tableau de tableaux associatifs par une clé donnée.
     * Utilise l'opérateur Spaceship (<=>) de PHP 7+.
     *
     * @param string|int $field La clé sur laquelle trier.
     * @param array $array Le tableau à trier (passé par référence).
     * @param string $direction 'asc' ou 'desc'.
     * @return void
     */
    public static function array_sortBy(string|int $field, array &$array, string $direction = 'asc'): void
    {
        usort($array, function ($a, $b) use ($field, $direction) {
            $valA = $a[$field] ?? null;
            $valB = $b[$field] ?? null;

            // Opérateur Spaceship : renvoie -1, 0 ou 1 automatiquement
            if ($direction === 'desc') {
                return $valB <=> $valA;
            }

            return $valA <=> $valB;
        });
    }
}