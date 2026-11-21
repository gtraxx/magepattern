<?php

declare(strict_types=1);

// Charger l'autoloader de votre projet (à adapter selon l'emplacement du script)
// require_once __DIR__ . '/vendor/autoload.php';

use Magepattern\Component\File\FileTool;
use Magepattern\Component\File\CacheTool;
use Magepattern\Component\Database\QueryBuilder;
use Magepattern\Component\Database\Layer;

// 1. Définition du chemin physique (ex: depuis la racine du serveur)
$basePath = __DIR__ . '/var/cache/database';

// 2. Le FileTool prépare et sécurise le terrain (crée le dossier et le .htaccess si besoin)
$securePath = FileTool::createSecureCacheDir($basePath);

// 3. Le CacheTool prend le relais pour stocker les données
$cache = new CacheTool($securePath);

// =========================================================================
// EXEMPLE 1 : Lecture simple avec mise en cache (1h)
// =========================================================================
$qb = (new QueryBuilder())
    ->select('*')
    ->from('mc_cms_page');

$cacheKey = $cache->generateKey($qb->getSql(), $qb->getParams());
$data = $cache->get($cacheKey);

if ($data === null) {
    $data = Layer::getInstance()->fetchAll($qb->getSql(), $qb->getParams());
    if ($data !== false) {
        $cache->set($cacheKey, $data, 3600);
    }
}

// =========================================================================
// EXEMPLE 2 : Lecture avec requête préparée et tag de cache ("pages")
// =========================================================================
$qbPublished = (new QueryBuilder())
    ->select('*')
    ->from('mc_cms_page')
    ->where('published = :published', ['published' => 1]);

$cacheKey = $cache->generateKey($qbPublished->getSql(), $qbPublished->getParams(), 'pages');
$pages = $cache->get($cacheKey);

if ($pages === null) {
    $pages = Layer::getInstance()->fetchAll($qbPublished->getSql(), $qbPublished->getParams());
    if ($pages !== false) {
        $cache->set($cacheKey, $pages, 3600);
    }
}

// =========================================================================
// EXEMPLE 3 : Mise à jour via Layer::update() et invalidation ciblée
// =========================================================================
$qbUpdate = (new QueryBuilder())
    ->update('mc_cms_page', ['name_pages' => 'Nouveau Titre'])
    ->where('id_pages = :id', ['id' => 12]);

$updated = Layer::getInstance()->update($qbUpdate->getSql(), $qbUpdate->getParams());

if ($updated) {
    // Supprime uniquement les fichiers de cache liés au tag "pages"
    $cache->clearByTag('pages');
}

// =========================================================================
// EXEMPLE 4 : Stockage du cache hors de la racine web (httpdocs)
// =========================================================================
$privatePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'var_private' . DIRECTORY_SEPARATOR . 'cache';

// FileTool crée le dossier sécurisé en dehors de l'espace public web
$securePrivatePath = FileTool::createSecureCacheDir($privatePath);
$privateCache = new CacheTool($securePrivatePath);