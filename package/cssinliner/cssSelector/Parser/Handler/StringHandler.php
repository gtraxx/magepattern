<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cssSelector\Parser\Handler;

use cssSelector\Exception\InternalErrorException;
use cssSelector\Exception\SyntaxErrorException;
use cssSelector\Parser\Reader;
use cssSelector\Parser\Token;
use cssSelector\Parser\TokenStream;
use cssSelector\Parser\Tokenizer\TokenizerEscaping;
use cssSelector\Parser\Tokenizer\TokenizerPatterns;

/**
 * CSS selector comment handler.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class StringHandler implements HandlerInterface
{
    /**
     * @var TokenizerPatterns
     */
    private $patterns;

    /**
     * @var TokenizerEscaping
     */
    private $escaping;

    /**
     * @param TokenizerPatterns $patterns
     * @param TokenizerEscaping $escaping
     */
    public function __construct(TokenizerPatterns $patterns, TokenizerEscaping $escaping)
    {
        $this->patterns = $patterns;
        $this->escaping = $escaping;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Reader $reader, TokenStream $stream)
    {
        $quote = $reader->getSubstring(1);

        if (!in_array($quote, array("'", '"'))) {
            return false;
        }

        $reader->moveForward(1);
        $match = $reader->findPattern($this->patterns->getQuotedStringPattern($quote));

        if (!$match) {
            throw new InternalErrorException(sprintf('Should have found at least an empty match at %s.', $reader->getPosition()));
        }

        // check unclosed strings
        if (strlen($match[0]) === $reader->getRemainingLength()) {
            throw SyntaxErrorException::unclosedString($reader->getPosition() - 1);
        }

        // check quotes pairs validity
        if ($quote !== $reader->getSubstring(1, strlen($match[0]))) {
            throw SyntaxErrorException::unclosedString($reader->getPosition() - 1);
        }

        $string = $this->escaping->escapeUnicodeAndNewLine($match[0]);
        $stream->push(new Token(Token::TYPE_STRING, $string, $reader->getPosition()));
        $reader->moveForward(strlen($match[0]) + 1);

        return true;
    }
}
