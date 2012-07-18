<?php
/**
 * Created by Magix Dev.
 * User: aureliengerits
 * Date: 18/06/12
 * Time: 23:01
 *
 */
class debug_logger {

    private $pathlog; # Dossier où sont enregistrés les fichiers logs (ex: /Applications/MAMP/www/monsite/logs)
    private $ready; # Le logger est prêt quand le dossier de dépôt des logs existe

    # Granularité (pour l'archivage des logs)
    const LOG_VOID  = 'VOID';  # Aucun archivage
    const LOG_MONTH = 'MONTH'; # Archivage mensuel
    const LOG_YEAR  = 'YEAR';  # Archivage annuel

    /**
     * Constructeur
     * Vérifie que le dossier dépôt éxiste
     *
     * @param string $path Chemin vers le dossier de dépôt
     **/
    public function __construct($path){
        $this->ready = false;

        # Si le dépôt n'éxiste pas
        if( !is_dir($path) ){
            trigger_error("<code>$path</code> n'existe pas", E_USER_WARNING);
            return false;
        }

        $this->pathlog = realpath($path);
        $this->ready = true;

        return true;
    }

    /**
     * Retourne le chemin vers un fichier de log déterminé à partir des paramètres $type, $name et $granularity.
     * (ex: /Applications/MAMP/www/monsite/logs/erreurs/201202/201202_erreur_connexion.log)
     * Elle créé le chemin si il n'éxiste pas.
     *
     * @param string $type Dossier dans lequel sera enregistré le fichier de log
     * @param string $name Nom du fichier de log
     * @param string $granularity Granularité : LOG_VOID, LOG_MONTH ou LOG_YEAR
     * @return string Chemin vers le fichier de log
     **/
    public function path($type, $name, $granularity = self::LOG_YEAR){
        # On vérifie que le logger est prêt (et donc que le dossier de dépôt existe
        if( !$this->ready ){
            trigger_error("Logger is not ready", E_USER_WARNING);
            return false;
        }

        # Contrôle des arguments
        if( !isset($type) || empty($name) ){
            trigger_error("Paramètres incorrects", E_USER_WARNING);
            return false;
        }

        # Création dossier du type (ex: /Applications/MAMP/www/monsite/logs/erreurs/)
        if( empty($type) ){
            $type_path = $this->pathlog.'/';
        } else {
            $type_path = $this->pathlog.'/'.$type.'/';
            if( !is_dir($type_path) ){
                mkdir($type_path);
            }
        }

        # Création du dossier granularity (ex: /Applications/MAMP/www/monsite/logs/erreurs/201202/)
        if( $granularity == self::LOG_VOID ){
            $logfile = $type_path.$name.'.log';
        }
        elseif( $granularity == self::LOG_MONTH ){
            $current_month    = date('Ym');
            $type_path_month    = $type_path.$current_month;
            if( !is_dir($type_path_month) ){
                mkdir($type_path_month);
            }
            $logfile = $type_path_month.'/'.$current_month.'_'.$name.'.log';
        }
        elseif( $granularity == self::LOG_YEAR ){
            $current_year    = date('Y');
            $type_path_year    = $type_path.$current_year;
            if( !is_dir($type_path_year) ){
                mkdir($type_path_year);
            }
            $logfile = $type_path_year.'/'.$current_year.'_'.$name.'.log';
        }
        else{
            trigger_error("Granularité '$granularity' non prise en charge", E_USER_WARNING);
            return false;
        }

        return $logfile;
    }

    /**
     * Enregistre $row dans le fichier log déterminé à partir des paramètres $type, $name et $granularity
     *
     * @param string $type Dossier dans lequel sera enregistré le fichier de log
     * @param string $name Nom du fichier de log
     * @param string $row Texte à ajouter au fichier de log
     * @param string $granularity Granularité : LOG_VOID, LOG_MONTH ou LOG_YEAR
     **/
    public function log($type, $name, $row, $granularity = self::LOG_YEAR){
        $date = new date_dateformat();
        # Contrôle des arguments
        if( !isset($type) || empty($name) || empty($row) ){
            trigger_error("Paramètres incorrects", E_USER_WARNING);
            return false;
        }

        $logfile = $this->path($type, $name, $granularity);

        if( $logfile === false ){
            trigger_error("Impossible d'enregistrer le log", E_USER_WARNING);
            return false;
        }

        # Ajout de la date et de l'heure au début de la ligne
        $row = $date->dateDefine('d/m/Y H:i:s').' '.$row;

        # Ajout du retour chariot de fin de ligne si il n'y en a pas
        if( !preg_match('#\n$#',$row) ){
            $row .= "\n";
        }

        $this->write($logfile, $row);
    }

    /**
     * Écrit (append) $row dans $logfile
     *
     * @param string $logfile Chemin vers le fichier de log
     * @param string $row Chaîne de caractères à ajouter au fichier
     **/
    private function write($logfile, $row){
        if( !$this->ready ){return false;}

        if( empty($logfile) ){
            trigger_error("<code>$logfile</code> est vide", E_USER_WARNING);
            return false;
        }

        $fichier = fopen($logfile,'a+');
        fputs($fichier, $row);
        fclose($fichier);
    }

}
?>