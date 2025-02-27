<?php
/**
 * Chargement du bootstrap
 */
$bootstrap = __DIR__.'/magepattern/bootstrap.php';
//print $bootstrap;
if (file_exists($bootstrap)){
    require $bootstrap;
}else{
    throw new Exception('Boostrap is not exist');
}
use Magepattern\Component\HTTP\Request,
    Magepattern\Component\HTTP\Url,
    Magepattern\Component\Tool\RSATool,
    Magepattern\Component\File\Finder,
    Magepattern\Component\Debug\Logger;

print Url::getUrl();
print '<br />';
print_r(Finder::dirFilterIterator('test',['jpeg']));
print Finder::sizeDirectory(__DIR__.'/test');
print '<br />';
$tableau = [
    'a' => 1,
    'b' => ['c' => 2, 'd' => 'recherche_moi'],
    'e' => 3,
];

$resultat = Finder::arrayContainsRecursive('recherche_moi', $tableau);

if ($resultat) {
    echo "'recherche_moi' a été trouvé dans le tableau.\n";
} else {
    echo "'recherche_moi' n'a pas été trouvé dans le tableau.\n";
}
print '<br />';
print RSATool::uniqID();
// Définition des constantes
define('MP_LOG_DIR', __DIR__ . '/logs');
//print MP_LOG_DIR;
define('MP_LOG_DETAILS', 'high');

$logger = Logger::getInstance();

// Débogage
//$logger->setLogLevel(0);
//$logger->setLogLevel(Logger::LOG_LEVEL_INFO);

// Test minimal
//$logger->log("Test");

//$logger->setLogLevel(Logger::LOG_LEVEL_DEBUG);
$logger->log("Message d'information", "php", "debug", Logger::LOG_MONTH, Logger::LOG_LEVEL_DEBUG);

//$logger->log("Message d'information", "test", "test", Logger::LOG_MONTH, Logger::LOG_LEVEL_INFO);
//$logger->log("Message d'avertissement", "test", "test", Logger::LOG_MONTH, Logger::LOG_LEVEL_WARNING);
//$logger->log("Message d'erreur", "test", "test", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);

//ob_flush();
//flush();
?>