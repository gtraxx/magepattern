<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cssSelector\Tests\Parser\Handler;

use cssSelector\Parser\Handler\HashHandler;
use cssSelector\Parser\Token;
use cssSelector\Parser\Tokenizer\TokenizerPatterns;
use cssSelector\Parser\Tokenizer\TokenizerEscaping;

class HashHandlerTest extends AbstractHandlerTest
{
    public function getHandleValueTestData()
    {
        return array(
            array('#id', new Token(Token::TYPE_HASH, 'id', 0), ''),
            array('#123', new Token(Token::TYPE_HASH, '123', 0), ''),

            array('#id.class', new Token(Token::TYPE_HASH, 'id', 0), '.class'),
            array('#id element', new Token(Token::TYPE_HASH, 'id', 0), ' element'),
        );
    }

    public function getDontHandleValueTestData()
    {
        return array(
            array('id'),
            array('123'),
            array('<'),
            array('<'),
            array('#'),
        );
    }

    protected function generateHandler()
    {
        $patterns = new TokenizerPatterns();

        return new HashHandler($patterns, new TokenizerEscaping($patterns));
    }
}