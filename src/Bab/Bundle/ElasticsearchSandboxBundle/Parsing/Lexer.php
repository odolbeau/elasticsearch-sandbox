<?php

namespace Bab\Bundle\ElasticsearchSandboxBundle\Parsing;

use Doctrine\Common\Lexer\AbstractLexer;

class Lexer extends AbstractLexer
{
    const T_SELECTOR = 1;
    const T_TWEET    = 2;
    const T_MENTION  = 3;
    const T_RETWEET  = 4;
    const T_FROM     = 5;
    const T_USERNAME = 6;

    /**
     * getConstantName
     *
     * @param string $constant
     *
     * @return string
     */
    public function getConstantName($constant)
    {
        $litteral = $this->getLiteral($constant);

        return substr($litteral, strpos($litteral, '::') + 2);
    }

    /**
     * {@inheritDoc}
     */
    protected function getCatchablePatterns()
    {
        return array(
            '(?:[[:alnum:]]|[\xc8-\xcb]|[&\|@_-])*',
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
        if (0 === strpos($value, '@')) {
            $value = substr($value, 1);

            return self::T_USERNAME;
        }

        switch (strtoupper($value)) {
            case 'A':
            case 'AN':
                return self::T_SELECTOR;
            case 'TWEET':
                return self::T_TWEET;
            case 'MENTION':
                return self::T_MENTION;
            case 'RETWEET':
            case 'RT':
                return self::T_RETWEET;
            case 'FROM':
                return self::T_FROM;
            default:
                throw new \InvalidArgumentException(sprintf(
                    'Unknown identifier "%s".',
                    $value
                ));
        }
    }
}
