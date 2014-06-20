<?php

namespace Bab\Bundle\ElasticsearchSandboxBundle\Parsing;

class Parser
{
    protected $lexer;

    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * parse
     *
     * @param string $string
     *
     * @return \Elastica\Query
     */
    public function parse($string)
    {
        $this->lexer->setInput($string);
        $this->lexer->moveNext();
        $this->lexer->moveNext();

        $query = null;
        $filters = array();
        while (null !== $this->lexer->token) {
            $currentType = $this->lexer->token['type'];

            $value = $this->lexer->token['value'];
            switch ($currentType) {
                case Lexer::T_SELECTOR:
                    $this->expect([Lexer::T_TWEET, Lexer::T_MENTION, LEXER::T_RETWEET]);
                    continue;
                case Lexer::T_TWEET:
                    continue;
                case Lexer::T_MENTION:
                    $filters[] = new \Elastica\Filter\Exists(array('mention'));
                    continue;
                case Lexer::T_RETWEET:
                    $filters[] = new \Elastica\Filter\Exists(array('retweet'));
                    continue;
                case Lexer::T_FROM:
                    $this->expect([Lexer::T_USERNAME]);
                    continue;
                case Lexer::T_USERNAME:
                    $filters[] = new \Elastica\Filter\Terms('user.screen_name', array($value));
                    continue;
                default:
                    throw new \InvalidArgumentException(sprintf(
                        'Can\'t deal with "%s".',
                        $this->lexer->token['value']
                    ));
            }

            $this->lexer->moveNext();
        }

        $query = new \Elastica\Query();

        if (0 < count($filters)) {
            $andFilter = new \Elastica\Filter\BoolAnd();
            foreach ($filters as $filter) {
                $andFilter->addFilter($filter);
            }

            $query->setQuery(
                new \Elastica\Query\Filtered(
                    new \Elastica\Query\MatchAll(),
                    $andFilter
                )
            );
        }


        return $query;
    }

    /**
     * expect
     *
     * @param array $tokens
     *
     * @throw InvalidArgumentException
     *
     * @return void
     */
    private function expect(array $tokens)
    {
        if ($this->lexer->isNextTokenAny($tokens)) {
            return;
        }

        $typesToString = array();
        array_walk($tokens, function($value) use (&$typesToString) {
            $typesToString[] = $this->lexer->getConstantName($value);
        });

        throw new \InvalidArgumentException(sprintf(
            'Expected token of type: ["%s"]. "%s" given.',
            implode('", "', $typesToString),
            $this->lexer->getConstantName($this->lexer->lookahead['type'])
        ));
    }
}
