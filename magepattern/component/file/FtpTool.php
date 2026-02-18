<?php

namespace Magepattern\Component\File;

use FTP\Connection;
use Magepattern\Component\Debug\Logger;

class FtpTool
{
    private ?Connection $connection = null;
    private bool $isLoggedIn = false;

    /**
     * Utilisation de la "Constructor Property Promotion" de PHP 8
     */
    public function __construct(
        private string $host,
        private string $user,
        private string $pass,
        private int $port = 21,
        private bool $passiveMode = true, // Indispensable pour la plupart des serveurs modernes
        private int $timeout = 90
    ) {
        if (!extension_loaded('ftp')) {
            Logger::getInstance()->log("L'extension PHP FTP n'est pas activée.", "ftp", "error");
        }
    }

    /**
     * Ouvre la connexion si elle n'est pas déjà active
     */
    public function connect(): bool
    {
        if ($this->connection !== null && $this->isLoggedIn) {
            return true;
        }

        try {
            // 1. Connexion
            $this->connection = @ftp_connect($this->host, $this->port, $this->timeout);

            if (!$this->connection) {
                throw new \Exception("Impossible de se connecter au serveur FTP : {$this->host}");
            }

            // 2. Authentification
            if (!@ftp_login($this->connection, $this->user, $this->pass)) {
                throw new \Exception("Authentification FTP échouée pour l'utilisateur : {$this->user}");
            }

            // 3. Activation du mode passif (Recommandé)
            if ($this->passiveMode) {
                ftp_pasv($this->connection, true);
            }

            $this->isLoggedIn = true;
            return true;

        } catch (\Exception $e) {
            $this->disconnect(); // Nettoyage en cas d'échec
            Logger::getInstance()->log($e, "ftp", "error");
            return false;
        }
    }

    /**
     * Récupère la taille d'un fichier distant
     * @return int|false La taille en octets ou false
     */
    public function getSize(string $remoteFile): int|false
    {
        if (!$this->connect()) return false;

        $size = ftp_size($this->connection, $remoteFile);
        return ($size !== -1) ? $size : false;
    }

    /**
     * Télécharge un fichier du serveur vers local
     */
    public function download(string $localPath, string $remoteFile): bool
    {
        if (!$this->connect()) return false;

        try {
            // Vérification du dossier local
            $dir = dirname($localPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            return ftp_get($this->connection, $localPath, $remoteFile, FTP_BINARY);
        } catch (\Exception $e) {
            Logger::getInstance()->log($e, "ftp", "error");
            return false;
        }
    }

    /**
     * Envoie un fichier local vers le serveur (Bonus: manquant dans votre version originale)
     */
    public function upload(string $localFile, string $remotePath): bool
    {
        if (!$this->connect()) return false;

        if (!file_exists($localFile)) {
            Logger::getInstance()->log("Fichier local introuvable pour upload : $localFile", "ftp", "warning");
            return false;
        }

        return ftp_put($this->connection, $remotePath, $localFile, FTP_BINARY);
    }

    /**
     * Ferme la connexion proprement
     */
    public function disconnect(): void
    {
        if ($this->connection !== null) {
            @ftp_close($this->connection);
            $this->connection = null;
            $this->isLoggedIn = false;
        }
    }

    /**
     * Destructeur : Fermeture automatique à la fin du script
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}