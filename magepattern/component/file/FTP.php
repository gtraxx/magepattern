<?php
namespace Magepattern\Component\File;

class FTP
{
    /**
     * @var string $ftp_server
     * @var string $ftp_port
     * @var string $ftp_user
     * @var string $ftp_pass
     */
    protected string
        $ftp_server,
        $ftp_port,
        $ftp_user,
        $ftp_pass;

    /**
     * @var bool $ftp_enabled
     */
    protected bool
        $ftp_enabled = false;

    /**
     * @var resource $ftp_stream
     */
    private
        $ftp_stream;

    /**
     * @param string $ftp_server
     * @param string $ftp_port
     * @param string $ftp_user
     * @param string $ftp_pass
     */
    public function __construct(string $ftp_server, string $ftp_port, string $ftp_user, string $ftp_pass)
    {
        if(extension_loaded('ftp')) {
            $this->ftp_server = $ftp_server;
            $this->ftp_port = $ftp_port;
            $this->ftp_user = $ftp_user;
            $this->ftp_pass = $ftp_pass;
            $this->ftp_enabled = true;
        }
    }

    /**
     * Connexion FTP
     * @return false
     */
    private function ftpConnect(): bool
    {
        if($this->ftp_enabled) return false;
        // set up basic connection
        $connect = ftp_connect($this->ftp_server,$this->ftp_port);
        // login with username and password
        $login_result = ftp_login($connect, $this->ftp_user, $this->ftp_pass);
        $this->ftp_stream = $connect;
        return $connect && $login_result;
    }

    /**
     * @param string $server_file
     * @return string|false Return the file size or false
     */
    public function getRemoteFileSize(string $server_file): string|false
    {
        if($this->ftpConnect()) {
            $res = ftp_size($this->ftp_stream, $server_file);
            $getFileSize = $res != -1 ? $res : '';
            ftp_close($this->ftp_stream);
            return $getFileSize;
        }
        return false;
    }

    /**
     * @param string $directory_file
     * @param string $server_file
     * @return bool
     */
    public function getRemoteFile(string $directory_file, string $server_file): bool
    {
        if($this->ftpConnect()) {
            $timeout = ftp_get_option($this->ftp_stream, FTP_TIMEOUT_SEC);
            // try to download server_file and save to local_file
            $dwnldResult = ftp_get($this->ftp_stream, $directory_file, $server_file, FTP_BINARY);
            ftp_close($this->ftp_stream);
            return $dwnldResult;
        }
        return false;
    }
}