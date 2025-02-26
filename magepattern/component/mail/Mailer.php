<?php
namespace Magepattern\Component\Mail;

abstract class Mailer
{
	/**
	 * instance $_mailer
	 * @var $_mailer
	 */
	protected $_mailer;

	/**
	 * défini les options de transport
	 * @var array $_options
	 */
	protected array $_options = [
        'setHost'		=>	'',
        'setPort'		=>	25,
        'setEncryption'	=>	'',
        'setUsername'	=>	'',
        'setPassword'	=>	''
    ];

    /**
     * Mailer constructor.
     * @param string $type
     * @param array $options
     */
    public abstract function __construct (string $type,array $options = []);

    /**
     * @param array $options
     */
    //public abstract function setOptions (array $options = []);

    /**
     * INI transport
     * @param string $type
     * @param array $options
     */
    protected abstract function setTransportConfig(string $type, array $options);

    /**
     * @param string $subject Mail object
     * @param string $reply ReplyTo address
     * @param string $from Mail
     * @param array $recipients Mail recipient
     * @param string $body Mail body
     * @param string $setReadReceiptTo
     */
    public abstract function setBodyMail(string $subject, string $from, string $reply, array $recipients, string $body, string $setReadReceiptTo = '');

    /**
     * @param string $message
     * @return bool
     */
    public abstract function send(string $message): bool;
}