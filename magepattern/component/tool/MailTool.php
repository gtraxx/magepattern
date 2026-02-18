<?php

namespace Magepattern\Component\Tool;

use Magepattern\Bootstrap;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Magepattern\Component\Debug\Logger;
use Magepattern\Component\Tool\FormTool;

/**
 * MailTool - Moteur d'envoi d'emails pour Magepattern 3
 * Remplace l'ancienne implémentation SwiftMailer par Symfony Mailer.
 */
class MailTool
{
    private Mailer $mailer;

    /**
     * @param string $dsnOrType DSN (ex: smtp://...) ou type ('mail'/'smtp')
     * @param array $options Options pour compatibilité ascendante
     */
    public function __construct(string $dsnOrType, array $options = [])
    {
        // Chargement de l'autoloader externe via le Bootstrap de Magepattern
        Bootstrap::getInstance()->load('mailer');

        $dsn = (strpos($dsnOrType, '://') !== false)
            ? $dsnOrType
            : $this->buildDsnFromLegacy($dsnOrType, $options);

        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);
    }

    /**
     * Convertit les anciens paramètres SwiftMailer en chaîne DSN moderne
     */
    protected function buildDsnFromLegacy(string $type, array $options): string
    {
        if ($type === 'mail') return 'sendmail://default';

        $user = urlencode($options['setUsername'] ?? '');
        $pass = urlencode($options['setPassword'] ?? '');
        $host = $options['setHost'] ?? 'localhost';
        $port = $options['setPort'] ?? 25;
        $encryption = $options['setEncryption'] ?? null;

        $proto = ($encryption === 'ssl') ? 'smtps' : 'smtp';
        return "{$proto}://{$user}:{$pass}@{$host}:{$port}";
    }

    /**
     * Initialise un objet Email
     */
    public function createMessage(string $subject, string $from, string $reply, array $recipients, string $body, string $readReceipt = ''): Email
    {
        $email = (new Email())
            ->subject($subject)
            ->from($from)
            ->replyTo($reply)
            ->html($body)
            ->text(FormTool::tagClean($body));

        foreach ($recipients as $address => $name) {
            if (is_int($address)) {
                $email->addTo($name);
            } else {
                $email->addTo(new Address($address, $name));
            }
        }

        if ($readReceipt) {
            $email->getHeaders()->addMailboxHeader('Disposition-Notification-To', $readReceipt);
        }

        return $email;
    }

    /**
     * Ajout de pièces jointes (Optionnel)
     */
    public function attachFiles(Email $email, array $files): void
    {
        foreach ($files as $file) {
            if (is_array($file) && isset($file['path'])) {
                $email->attachFromPath($file['path'], $file['name'] ?? null, $file['mime'] ?? null);
            } else if (is_string($file) && file_exists($file)) {
                $email->attachFromPath($file);
            }
        }
    }

    /**
     * Exécute l'envoi
     */
    public function send(Email $email): bool
    {
        try {
            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            Logger::getInstance()->log($e, "php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);
            return false;
        }
    }
}