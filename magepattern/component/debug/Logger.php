<?php
namespace Magepattern\Component\Debug;
use Magepattern\Component\Tool\PathTool,
    Magepattern\Component\Tool\FileTool,
    Magepattern\Component\Date\Timer;

class Logger
{
    const LOG_MONTH = 'MONTH';
    const LOG_YEAR = 'YEAR';

    /**
     * @var Logger
     */
    protected static $_instance;

    /**
     * @var int $log_level
     */
    const LOG_LEVEL_DEBUG = 0; // Informations de débogage détaillées
    const LOG_LEVEL_INFO = 1; // Informations générales
    const LOG_LEVEL_WARNING = 2; // Avertissements
    const LOG_LEVEL_ERROR = 3; // Erreurs

    protected static int $log_level = self::LOG_LEVEL_INFO;

    /**
     * @var string
     */
    protected static string
        $log_path = MP_LOG_DIR,
        $log_details = MP_LOG_DETAILS ?? 'low';

    /**
     * @var array $lastLog
     */
    protected static array $lastLog = [
        'filename' => '',
        'folder' => ''
    ];

    /**
     * @var bool $logger_ready
     */
    private static bool $logger_ready = false;

    /**
     * @var int
     */
	public static int
        $ms = 0,
        $last = 0;

    /**
     * Constructor is set to private to prevent unwanted instantiation
     */
    public function __construct() {
        self::$log_level = self::LOG_LEVEL_DEBUG; // Initialisation dans le constructeur
    }

    /**
     * Prevent clone to prevent double instance
     */
    private function __clone(){}

    /**
     * C'est la méthode qui "remplace" le constructeur vis à vis
     * des autres classes.
     *
     * Son rôle est de créer / distribuer une unique
     * instance de notre objet.
     */
    public static function getInstance()
    {
        if(!self::$_instance instanceof self) self::$_instance = new self;
        return self::$_instance;
    }

    /**
     * @param int $level
     * @return void
     */
    public function setLogLevel(int $level)
    {
        if (in_array($level, [self::LOG_LEVEL_DEBUG, self::LOG_LEVEL_INFO, self::LOG_LEVEL_WARNING, self::LOG_LEVEL_ERROR])) {
            self::$log_level = $level;
        }
    }
    /**
     * @param string $path
     */
    public function setLogPath(string $path = '')
    {
        if (!defined('MP_LOG_DIR')) {
            trigger_error("MP_LOG_DIR not defined. Using default path.", E_USER_WARNING);
            self::$log_path = __DIR__ . '/logs'; // Chemin par défaut
        } else {
            $path = $path ?: MP_LOG_DIR;
        }

        if (is_dir($path)) {
            self::$log_path = realpath($path);
            self::$logger_ready = true;
        } else {
            trigger_error("{$path} does not exist. Using default path.", E_USER_WARNING);
            self::$log_path = __DIR__ . '/logs'; // Chemin par défaut
            self::$logger_ready = true;
        }
    }

    /**
     * @param string $level full|high|medium|low
     */
    public function setLogDetails(string $level)
    {
        if (!defined('MP_LOG_DETAILS')) {
            trigger_error("MP_LOG_DETAILS not defined. Using default level 'low'.", E_USER_WARNING);
            self::$log_details = 'low'; // Niveau par défaut
        } else {
            if (in_array($level, ['full', 'high', 'medium', 'low'])) {
                self::$log_details = $level;
            } else {
                trigger_error("Invalid log detail level. Using default level 'low'.", E_USER_WARNING);
                self::$log_details = 'low'; // Niveau par défaut
            }
        }
    }

    /**
     * @param string $folder Dossier dans lequel sera enregistré le fichier de log
     * @param string $filename Nom du fichier de log
     * @param string $archive Archivage : LOG_VOID, LOG_MONTH ou LOG_YEAR
     * @return bool|string Chemin vers le fichier de log
     **/
    private function getLogFilePath(string $folder, string $filename, string $archive): bool|string {
        //var_dump($folder);
        //var_dump($filename);

        if(!self::$logger_ready) self::setLogPath();

        if(!self::$logger_ready){
            trigger_error("Logger is not ready", E_USER_WARNING);
            return false;
        }

        if( empty($folder) || empty($filename) ){
            trigger_error("Incorrect parameters", E_USER_WARNING);
            return false;
        }

        $path = self::$log_path.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR;
        //echo "Chemin du répertoire : " . $path . "\n";
        //var_dump(is_dir($path)); // Vérification de l'existence du répertoire
        if(!is_dir($path)) FileTool::mkdir([$path]);

        if($archive !== '') {
            $date = new \DateTime();
            $year = $date->format('Y');
            $month = $date->format('m');
            $path = $path.$year.DIRECTORY_SEPARATOR;
            if(!is_dir($path)) FileTool::mkdir(array($path));

            $filename = match($archive) {
                self::LOG_MONTH => $year.$month.'_'.$filename,
                self::LOG_YEAR => $year.'_'.$filename,
                default => $filename
            };
        }

        return $path.$filename.'.log';
    }

    /**
     * @param string $logfile Chemin vers le fichier de log
     * @param string $row Chaîne de caractères à ajouter au fichier
     */
    private function write(string $logfile, string $row)
    {
        //var_dump($logfile);
        //var_dump($row);

        if(!self::$logger_ready) exit;

        $file = fopen($logfile,'a+');
        fputs($file, $row);
        fclose($file);
    }

    /**
     * Enregistre $row dans le fichier log déterminé à partir des paramètres $type, $name et $archive
     *
     * @param string|\Exception $entry Texte à ajouter au fichier de log ou Exception reçue
     * @param string $folder Dossier dans lequel sera enregistré le fichier de log
     * @param string $filename Nom du fichier de log
     * @param string $archive (LOG_VOID|LOG_MONTH|LOG_YEAR)
     */
    public function log(string|\Exception $entry, string $folder = 'php', string $filename = '', string $archive = self::LOG_MONTH, int $level = self::LOG_LEVEL_INFO) {
        /*var_dump($folder);
        var_dump($filename);
        var_dump(self::$log_details);
        var_dump(self::$log_level);
        var_dump($level);*/
        //var_dump(self::$log_level);
        //var_dump($level);
        if (!isset(self::$log_level)) {
            self::$log_level = self::LOG_LEVEL_INFO;
        }
        if (self::$log_level <= $level) {
            /*var_dump(self::$log_level);
            var_dump($level);
            print 'test';*/

            if(!$filename) {
                $trace = debug_backtrace();
                $filename = basename($trace[0]['file'], ".php");
            }

            $logfile = self::getLogFilePath($folder, $filename, $archive);
            //var_dump($logfile);
            if($logfile) {
                // Récupérer le niveau de log
                $levelName = '';
                switch ($level) {
                    case self::LOG_LEVEL_DEBUG:
                        $levelName = 'DEBUG';
                        break;
                    case self::LOG_LEVEL_INFO:
                        $levelName = 'INFO';
                        break;
                    case self::LOG_LEVEL_WARNING:
                        $levelName = 'WARNING';
                        break;
                    case self::LOG_LEVEL_ERROR:
                        $levelName = 'ERROR';
                        break;
                    default:
                        $levelName = 'UNKNOWN';
                }

                $str = $entry instanceof \Exception || $entry instanceof \PDOException ? $entry->getMessage() : $entry;
                $date = new \DateTime();

                switch (self::$log_details) {
                    case 'full':
                        $entry = $date->format('d/m/Y H:i:s') . ' | [' . $levelName . '] | ' . $str . "\n";
                        foreach (debug_backtrace() as $trace) {
                            $entry .= str_replace(PathTool::basePath(),'',$trace['file']).' | '.$trace['line'].' | '.$trace['function']."\n";
                        }
                        break;
                    case 'high':
                        $now = microtime(true);
                        if(self::$lastLog['folder'] !== $folder ||self::$lastLog['filename'] !== $filename) self::$ms = self::$last = 0;
                        $ms = self::$ms ?? $now;
                        $last = self::$last;
                        $bt = debug_backtrace();
                        $entry = str_replace(PathTool::basePath(),'',$bt[0]['file']).' | '.$bt[0]['line'];
                        $entry .= ' | '.($ms === $now ? '0' : number_format($now - $ms,4)).' ms ';
                        $entry .= $last ? '(+'.number_format($now - $last,4).' ms)' : '';
                        $entry .= ' | [' . $levelName . '] | ' . $str;

                        self::$ms = $ms;
                        self::$last = $now;
                        break;
                    case 'medium':
                        $entry = $date->format('d/m/Y H:i:s') . ' [' . $levelName . '] ' . $entry;
                        break;
                    case 'low':
                    default:
                    $entry = $date->format('d/m/Y H:i:s') . ' [' . $levelName . '] ' . $str;
                }

                if(!preg_match('#\n$#',$entry)) $entry .= "\n";
                self::write($logfile, $entry);
            }
        }/*else {
            var_dump("Condition if false");
        }*/
    }

    /**
     * Supprime un fichier de log.
     * @param string $folder
     * @param string $filename
     * @param string $archive
     * @return bool
     */
    public function removeLog(string $folder, string $filename, string $archive = self::LOG_MONTH): bool
    {
        try {
            $logfile = $this->getLogFilePath($folder, $filename, $archive);

            if (!is_string($logfile)) {
                Logger::getInstance()->log('Erreur : Chemin de fichier de log invalide.', Logger::LOG_LEVEL_ERROR);
                return false;
            }

            if (file_exists($logfile)) {
                if (FileTool::remove($logfile)) {
                    Logger::getInstance()->log("Suppression du fichier de log : $logfile", Logger::LOG_LEVEL_INFO);
                    return true;
                } else {
                    Logger::getInstance()->log("Erreur : Impossible de supprimer le fichier de log : $logfile", Logger::LOG_LEVEL_ERROR);
                    return false;
                }
            } else {
                Logger::getInstance()->log("Avertissement : Le fichier de log n'existe pas : $logfile", Logger::LOG_LEVEL_WARNING);
                return true; // Considérer comme réussi si le fichier n'existe pas
            }
        } catch (\Exception $e) {
            Logger::getInstance()->log("Erreur lors de la suppression du fichier de log : " . $e->getMessage(), Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }

    /**
     * Récupère les détails de la fonction ou de la classe appelante pour le débogage.
     *
     * @return array Un tableau contenant les détails de la fonction ou de la classe appelante.
     */
    public function getDebugTraceEntry(): array
    {
        $trace = debug_backtrace();

        for ($i = 0; $i < count($trace); ++$i) {
            if ('debug' == $trace[$i]['function']) {
                if (isset($trace[$i + 1]['class'])) {
                    return [
                        'class' => $trace[$i + 1]['class'],
                        'line' => $trace[$i]['line']
                    ];
                }

                return [
                    'file' => $trace[$i]['file'],
                    'line' => $trace[$i]['line']
                ];
            }
        }

        return $trace[0];
    }
}
