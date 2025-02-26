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
    protected static int $log_level = MP_LOG_LEVEL ?? 1;

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
    private function __construct(){}

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
     * @param string $path
     */
    public function setLogPath(string $path = '')
    {
        if(!defined('MP_LOG_DIR')) trigger_error("<code>MP_LOG_DIR</code> not defined", E_USER_ERROR);

        $path = $path ?: self::$log_path;

        if(is_dir($path)) {
            self::$log_path = realpath($path);
            self::$logger_ready = true;
        }
        else {
            trigger_error("<code>$path</code> not exist", E_USER_ERROR);
        }
    }

    /**
     * @param int $level
     */
    public function setLogLevel(int $level)
    {
        self::$log_level = $level;
    }

    /**
     * @param string $level full|high|medium|low
     */
    public function setLogDetails(string $level)
    {
        if(in_array($level, ['full','high','medium','low'])) self::$log_details = $level;
    }

    /**
     * @param string $folder Dossier dans lequel sera enregistré le fichier de log
     * @param string $filename Nom du fichier de log
     * @param string $archive Archivage : LOG_VOID, LOG_MONTH ou LOG_YEAR
     * @return bool|string Chemin vers le fichier de log
     **/
    private function getLogFilePath(string $folder, string $filename, string $archive): bool|string
    {
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
    public function log(string|\Exception $entry, string $folder = 'php', string $filename = '', string $archive = self::LOG_MONTH)
    {
        $level = $entry instanceof \Exception ? $entry->getCode() : E_ALL;

        if($level <= self::$log_level) {

            if(!$filename) {
                $trace = debug_backtrace();
                $filename = basename($trace[0]['file'], ".php");
            }

            $logfile = self::getLogFilePath($folder, $filename, $archive);

            if($logfile) {
                $str = $entry instanceof \Exception || $entry instanceof \PDOException ? $entry->getMessage() : $entry;
                $date = new \DateTime();

                switch (self::$log_details) {
                    case 'full':
                        $entry = $date->format('d/m/Y H:i:s').' | '.$str."\n";
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
                        $entry .= ' | '.$str;
                        self::$ms = $ms;
                        self::$last = $now;
                        break;
                    case 'medium':
                        $entry = $date->format('d/m/Y H:i:s').' '.$entry;
                        break;
                    case 'low':
                    default:
                        $entry = $date->format('d/m/Y H:i:s').' '.$str;
                }

                if(!preg_match('#\n$#',$entry)) $entry .= "\n";
                self::write($logfile, $entry);
            }
        }
    }

    /**
     * @param string $folder
     * @param string $filename
     * @param string $archive
     * @return bool
     */
    public function removeLog(string $folder, string $filename, string $archive = self::LOG_MONTH): bool
    {
        $logfile = $this->getLogFilePath($folder, $filename, $archive);
        if($logfile && file_exists($logfile)) FileTool::remove($logfile);
    }

	/**
	 * @return array
	 */
	private function the_trace_entry_to_return(): array
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
