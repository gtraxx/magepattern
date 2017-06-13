<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cssSelector\Parser\Shortcut;

use cssSelector\Node\ClassNode;
use cssSelector\Node\ElementNode;
use cssSelector\Node\SelectorNode;
use cssSelector\Parser\ParserInterface;

/**
 * CSS selector class parser shortcut.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class ClassParser implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($source)
    {
        // Matches an optional namespace, optional element, and required class
        // $source = 'test|input.ab6bd_field';
        // $matches = array (size=4)
        //     0 => string 'test|input.ab6bd_field' (length=22)
        //     1 => string 'test' (length=4)
        //     2 => string 'input' (length=5)
        //     3 => string 'ab6bd_field' (length=11)
        if (preg_match('/^(?:([a-z]++)\|)?+([\w-]++|\*)?+\.([\w-]++)$/i', trim($source), $matches)) {
            return array(
                new SelectorNode(new ClassNode(new ElementNode($matches[1] ?: null, $matches[2] ?: null), $matches[3])),
            );
        }

        return array();
    }
}
