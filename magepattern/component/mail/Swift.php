<?php
namespace Magepattern\Component\Mail;
use //CSSInliner\CSSInliner,
    //Magepattern\Bootstrap,
    Magepattern\Component\Debug\Logger,
    Magepattern\Component\Tool\FormTool;

class Swift extends Mailer
{
    /**
     *
     * Constructor
     * @param string $type
     * @param array $options
     * @internal param array $options
     */
	public function __construct(string $type, array $options = [])
    {
		$this->_mailer = \Swift_Mailer::newInstance($this->setTransportConfig($type, $options));
		//Bootstrap::getInstance()->load('cssinliner');
	}

    /**
     * @param string $type
     * @param array $options
     * @return \Swift_Transport
     */
    protected function setTransportConfig(string $type, array $options): \Swift_Transport
    {
        $options = $options ?? $this->_options;
        return match($type) {
    		'mail' => \Swift_MailTransport::newInstance(),
    		'smtp' => \Swift_SmtpTransport::newInstance()
                    ->setHost($options["setHost"])
                    ->setPort($options["setPort"])
                    ->setEncryption($options["setEncryption"])
                    ->setUsername($options["setUsername"])
                    ->setPassword($options["setPassword"]),
    	};
    }

    /**
     * @param string $subject
     * @param string $from
     * @param string $reply
     * @param array $recipients
     * @param string $body
     * @param string $setReadReceiptTo
     * @return mixed
     */
	public function setBodyMail(string $subject, string $from, string $reply, array $recipients, string $body, string $setReadReceiptTo = ''): mixed
    {
		$sw_message = \Swift_Message::newInstance();
		$sw_message->getHeaders()->get('Content-Type')->setValue('text/html');
		$sw_message->getHeaders()->get('Content-Type')->setParameter('charset', 'utf-8');
		$sw_message->setSubject($subject)
		       ->setEncoder(\Swift_Encoding::get8BitEncoding())
            // Set the from address of this message.
               ->setFrom($from)
            // Set the reply-to address of this message.
               ->setReplyTo($reply)
            //Set the to addresses of this message.
		       ->setTo($recipients)
		       ->setBody($body,'text/html')
		       ->addPart(FormTool::tagClean($body),'text/plain');
		if($setReadReceiptTo) $sw_message->setReadReceiptTo($setReadReceiptTo);
		return $sw_message;
    }

    /**
     * @param string $message
     * @return bool
     */
    public function send(string $message): bool
    {
        $failed  = [];
        try {
            $this->_mailer->send($message, $failed);
            if(!empty($failed)) throw new \Exception('Some mail could not be sent',E_WARNING);
            return empty($failed);
        }
        catch(\Exception $e) {
            $echoLogger = new \Swift_Plugins_Loggers_EchoLogger();
            $this->_mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($echoLogger));
            Logger::getInstance()->log($e,"php", "error", Logger::LOG_MONTH, Logger::LOG_LEVEL_ERROR);            Logger::getInstance()->log('Failures : '.$echoLogger->dump());
            return false;
        }
    }

    /**
     * Plugin decorator
     * @param string replacement
     * @internal param void $mailer
     */
    public function plugin_decorator(string $replacements)
    {
    	$decorator = new \Swift_Plugins_DecoratorPlugin($replacements);
		$this->_mailer->registerPlugin($decorator);
    }

    /**
     * Plugin antiflood
     * @param int $threshold
     * @param int $sleep
     * @internal param void $mailer
     */
	public function plugin_antiflood(int $threshold, int $sleep)
    {
    	//Use AntiFlood to re-connect after 100 emails specify a time in seconds to pause for (30 secs)
		$antiflood = new \Swift_Plugins_AntiFloodPlugin($threshold, $sleep);
		$this->_mailer->registerPlugin($antiflood);
    }

    /**
     * Plugin throttler
     * @param int $rate
     * @param $mode
     * @internal param void $mailer
     */
	public function plugin_throttler(int $rate,$mode)
    {
    	//Use AntiFlood to re-connect after 100 emails specify a time in seconds to pause for (30 secs)
		$throttler = new \Swift_Plugins_ThrottlerPlugin($rate,$mode);
		//Rate limit to 10MB per-minute OR Rate limit to 100 emails per-minute
		$this->_mailer->registerPlugin($throttler);
    }

    /**
     * fusion des plugins anti flood et throttler pour un envoi de masse
     * @param int $threshold
     * @param int $sleep
     * @param string $throttlermode
     * @internal param void $mailer
     */
    public function plugins_massive_mailer(int $threshold = 100, int  $sleep = 10, string $throttlermode = 'bytes')
    {
        switch($throttlermode) {
            case "bytes" :
                $rate = 1024 * 1024 * 10;
                $mode = \Swift_Plugins_ThrottlerPlugin::BYTES_PER_MINUTE;
                break;
            case "messages" :
            default:
                $rate = 100;
                $mode = \Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE;
                break;
        }

        if(!empty($threshold) AND !empty($sleep) AND !empty($throttlermode)) {
            $this->plugin_antiflood($threshold, $sleep);
            $this->plugin_throttler($rate, $mode);
        }
    }

    /**
     * CSS Inliner for responsive mail
     * @param string $html
     * @param array $css
     * @param string $path
     * @param bool $debug
     * @return string
     */
	public function plugin_css_inliner(string $html, array $css, string $path, bool $debug = false): string
    {
		$inliner = new CSSInliner();

        foreach ($css as $dir => $c) {
            if (is_array($c)) {
                foreach ($c as $d => $file) {
                    $inliner->addCSS($path. $d . '/' . $file);
                }
            }
            else {
                $inliner->addCSS($path. $dir . '/' . $c);
            }
        }

		return $inliner->render($html,$debug);
	}
}