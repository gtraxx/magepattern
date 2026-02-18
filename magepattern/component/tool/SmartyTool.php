<?php

namespace Magepattern\Component\Tool;

use Smarty\Smarty;
use Magepattern\Bootstrap;

/**
 * SmartyTool - Gestionnaire de moteur de template Smarty v5
 * Permet la gestion de multiples contextes (Frontend, Admin, etc.) avec isolation des caches.
 */
class SmartyTool
{
    /** @var array<string, Smarty> Stockage des instances actives */
    private static array $instances = [];

    /** @var array<string, array> Registre des configurations par contexte */
    private static array $registry = [];

    /**
     * Enregistre la configuration pour un contexte spécifique
     * @param string $context Nom du contexte (ex: 'frontend', 'admin')
     * @param array $config Configuration (template_dir, plugins_dir, etc.)
     */
    public static function registerContext(string $context, array $config): void
    {
        self::$registry[$context] = $config;
    }

    /**
     * Récupère l'instance Smarty pour un contexte donné
     * @param string $context Nom du contexte
     * @return Smarty
     */
    public static function getInstance(string $context = 'frontend'): Smarty
    {
        if (!isset(self::$instances[$context])) {
            $config = self::$registry[$context] ?? [];
            self::$instances[$context] = self::createInstance($context, $config);
        }

        return self::$instances[$context];
    }

    /**
     * Initialisation interne d'une instance Smarty
     */
    private static function createInstance(string $context, array $config): Smarty
    {
        // 1. Chargement de la librairie via le Bootstrap
        Bootstrap::getInstance()->load('smarty');

        $smarty = new Smarty();

        // Détermination de la racine (remonte de magepattern/component/tool)
        $rootDir = dirname(__DIR__, 3);

        // 2. Configuration des chemins
        $templateDir = $config['template_dir'] ?? $rootDir . '/themes/default/templates';
        $compileDir  = $config['compile_dir']  ?? $rootDir . '/var/smarty/compile/' . $context;
        $cacheDir    = $config['cache_dir']    ?? $rootDir . '/var/smarty/cache/' . $context;
        $configDir   = $config['config_dir']   ?? $rootDir . '/config/smarty';

        $smarty->setTemplateDir($templateDir);
        $smarty->setCompileDir($compileDir);
        $smarty->setCacheDir($cacheDir);
        $smarty->setConfigDir($configDir);

        // 3. Gestion des Plugins (Dossier global + Dossiers spécifiques)
        $smarty->addPluginsDir($rootDir . '/magepattern/package/smarty-plugins');
        if (isset($config['plugins_dir'])) {
            $plugins = is_array($config['plugins_dir']) ? $config['plugins_dir'] : [$config['plugins_dir']];
            foreach ($plugins as $dir) {
                $smarty->addPluginsDir($dir);
            }
        }

        // 4. Paramètres par défaut
        $smarty->setCompileCheck($config['debug'] ?? true);
        $smarty->escape_html = $config['escape_html'] ?? true;

        // 5. Sécurité : Création automatique des dossiers de travail
        self::checkDirectories($compileDir, $cacheDir);

        return $smarty;
    }

    /**
     * Vérifie et crée les dossiers récursivement si nécessaire
     */
    private static function checkDirectories(string ...$dirs): void
    {
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
        }
    }
}