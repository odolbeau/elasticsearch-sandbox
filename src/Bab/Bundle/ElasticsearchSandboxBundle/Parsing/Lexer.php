<?php

namespace Bab\Bundle\ElasticsearchSandboxBundle\Parsing;

use Doctrine\Common\Lexer\AbstractLexer;

class Lexer extends AbstractLexer
{
    const T_SELECTOR = 1;
    const T_MENTION= 2;
    const T_RETWEET= 3;

    /**
     * getConstantName
     *
     * @param string $constant
     *
     * @return string
     */
    public function getConstantName($constant)
    {
        $class = new \ReflectionClass(__CLASS__);
        $constants = array_flip($class->getConstants());

        if (!in_array($constant, $constants)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown constant %s',
                $constant
            ));
        }

        return $constants[$constant];
    }

    /**
     * {@inheritDoc}
     */
    protected function getCatchablePatterns()
    {
        return array(
            '(?:"|\')(?:[[:alnum:]]|[\xc8-\xcb]|[, ])*(?:"|\')',
            '(?:[[:alnum:]]|[\xc8-\xcb]|[&\|-])*',
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getNonCatchablePatterns()
    {
        return array('\s+', '(.)');
    }

    /**
     * {@inheritDoc}
     */
    protected function getType(&$value)
    {
        switch (strtoupper($value)) {
            case 'A':
            case 'AN':
                return self::T_SELECTOR;
            case 'MENTION':
                return self::T_MENTION;
            case 'RETWEET':
            case 'RT':
                return self::T_RETWEET;
            default:
                throw new \InvalidArgumentException(sprintf(
                    'Unknown identifier "%s".',
                    $value
                ));
        }
    }
}
