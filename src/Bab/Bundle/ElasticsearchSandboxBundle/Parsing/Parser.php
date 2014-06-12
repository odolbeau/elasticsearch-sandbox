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

        $query = null;
        $filters = array();
        $expectedType = array();
        while ($this->lexer->moveNext()) {
            $currentType = $this->lexer->lookahead['type'];
            $this->checkExpectedType($currentType, $expectedType);

            $value = $this->lexer->lookahead['value'];
            $expectedType = array();
            switch ($currentType) {
                case Lexer::T_SELECTOR:
                    $expectedType = [Lexer::T_TWEET, Lexer::T_MENTION, LEXER::T_RETWEET];
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
                    $expectedType = [Lexer::T_USERNAME];
                    continue;
                case Lexer::T_USERNAME:
                    $filters[] = new \Elastica\Filter\Terms('user.screen_name', array($value));
                    continue;
                default:
                    throw new \InvalidArgumentException(sprintf(
                        'Can\'t deal with "%s".',
                        $this->lexer->lookahead['value']
                    ));
            }
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
     * checkExpectedType
     *
     * Throw an exception if currentType don't equals to one of the
     * expectedType.
     *
     * @param string $currentType
     * @param array $expectedType
     *
     * @return void
     */
    protected function checkExpectedType($currentType, array $expectedType)
    {
        if (0 === count($expectedType)) {
            return;
        }
        if (in_array($currentType, $expectedType)) {
            return;
        }

        $typesToString = array();
        array_walk($expectedType, function($value) use (&$typesToString) {
            $typesToString[] = $this->lexer->getConstantName($value);
        });

        throw new \InvalidArgumentException(sprintf(
            'Expected token of type: ["%s"]. "%s" given.',
            implode('", "', $typesToString),
            $this->lexer->getConstantName($currentType)
        ));
    }
}
