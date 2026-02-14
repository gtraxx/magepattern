<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
    Magepattern\Component\Tool\FormTool,
    Magepattern\Component\HTTP\Url,
    Magepattern\Component\HTTP\JSON,
    Magepattern\Component\Tool\RSATool,
    Magepattern\Component\File\Finder,
    Magepattern\Component\Debug\Logger,
    Magepattern\Component\Database\Layer,
    Magepattern\Component\XML\XMLReader,
    Magepattern\Component\Tool\PathTool,
    Magepattern\Component\Tool\EscapeTool,
    Magepattern\Component\Tool\LocalizationTool;

//print $_GET['test_get'];
print Url::current();
$test = '';
if(Request::isGet('test')) $test = FormTool::simpleClean($_GET['test']);//
//print $test;
//if (http_request::isGet('id')) $this->id = form_inputEscape::numeric($_GET['id']);
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
define('MP_LOG_DETAILS', 'full');

$logger = Logger::getInstance();

// Débogage
//$logger->setLogLevel(0);
//$logger->setLogLevel(Logger::LOG_LEVEL_INFO);

// Test minimal
//$logger->log("Test");

//$logger->setLogLevel(Logger::LOG_LEVEL_DEBUG);
//$logger->log("Message d'information", "php", "debug", Logger::LOG_MONTH, Logger::LOG_LEVEL_DEBUG);

//$logger->log("Message d'information", "test", "test", Logger::LOG_MONTH, Logger::LOG_LEVEL_INFO);
//$logger->log("Message d'avertissement", "test", "test", Logger::LOG_MONTH, Logger::LOG_LEVEL_WARNING);
//$logger->log("Message d'erreur", "test", "test", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);

//ob_flush();
//flush();
$jsonString = '{"name":"Jane Doe","age":25,"city":"London"}';

$jsonInstance = new JSON();
$decodedData = $jsonInstance->decode($jsonString);
print '<br />';
if (is_array($decodedData) || is_object($decodedData)) {
    echo "Decoded data:\n";
    print_r($decodedData);
    print '<br />';
} else {
    echo "Error decoding JSON:\n";
    echo $decodedData; // $decodedData contient le message d'erreur
}
print '<br />';
$json = new JSON();
$jsondata = $json->decode('{"id": 10}');
print_r($jsondata);
print '<br />';
// Exemple avec options personnalisées
$options = ['assoc' => true];
$assocArray = $jsonInstance->decode($jsonString, $options);

if (is_array($assocArray)) {
    echo "\n\nDecoded data as associative array:\n";
    print_r($assocArray);
    print '<br />';
} else {
    echo "Error decoding JSON:\n";
    echo $assocArray; // $assocArray contient le message d'erreur
}

$jsonInstance = new JSON();

$data = [
    'name' => 'John Doe',
    'age' => 30,
    'city' => 'New York',
];

$json = $jsonInstance->encode($data);
$logger->log($json, "php", "info", Logger::LOG_MONTH, Logger::LOG_LEVEL_INFO);

if (is_string($json)) {
    echo "JSON encoded data:\n";
    echo $json;
    print '<br />';
} else {
    echo "Error encoding JSON:\n";
    echo $json;
}

$url = "test avec éàç";
$cleanedUrl = URL::clean($url);

echo "URL nettoyée : " . $cleanedUrl;
print '<br />';
print URL::clean(
    'truc machin.01&machin=truc',
    ['dot'=>'display','ampersand' => 'strict']
);
print '<br />';
define('MP_DBHOST' , 'localhost');
define('MP_DBNAME' , 'magixtemp');
define('MP_DBUSER' , 'root');
define('MP_DBPASSWORD' , 'root');
define('MP_DBDRIVER'  , 'mysql');
/* EN DEBUT DE FICHIER */
function getmicrotime()
{
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}

/* AVANT REQUETE */
$time_start = getmicrotime();
$db = new Layer();
$sql =  'SELECT * FROM mc_news_content WHERE id_content =:id';
$fetch = $db->fetchAll($sql, ['id' => 1]);

print_r($fetch);
/* APRES REQUETE */
$time_end = getmicrotime();
$time = $time_end - $time_start;
echo "Requete exécutée en $time secondes";
// Connexion principale (par défaut)
/*$mainDb = new Layer();

// Connexion secondaire (ex: SQLite pour des logs ou une archive)
$archiveDb = new Layer([
    'driver' => 'sqlite',
    'path'   => '/var/db/archive.sqlite' // Supposant que votre classe SQLite gère ce paramètre
]);

$users = $mainDb->fetchAll("SELECT * FROM users");
$archiveDb->insert("INSERT INTO logs ...", $users);*/
print '<br />';
$xml = '<?xml version="1.0"?>
        <user>
            <id>10</id>
            <name>Aurelien</name>
            <role>Admin</role>
        </user>';

$data = XMLReader::toArray($xml);
echo $data['name']; // Affiche: Aurelien

/*$data = XMLReader::fromFile('flux_actualites.xml');
foreach ($data['item'] as $item) {
    echo $item['title'];
}*/
print PathTool::basePath();
print '<br />';
$input = "C\\'est l\\'été"; // C\'est l\'été
echo EscapeTool::cleanQuote($input);
print '<br />';
$nom = "Hélène & Garçon";
echo EscapeTool::decode_utf8($nom);



// Récupérer tous les pays en français
$mesPaysFr = [
    "FR" => "France",
    "BE" => "Belgique",
    "CA" => "Canada"
];

// Injection
LocalizationTool::setCountries($mesPaysFr, 'fr');
$listeComplete = LocalizationTool::getCountries('fr');
print_r($listeComplete);
?>