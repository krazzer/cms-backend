<?php

namespace KikCMS\Doctrine\Dql;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * JSON_UNQUOTE(value)
 *
 * Usage:
 *   JSON_UNQUOTE(JSON_EXTRACT(e.data, '$.name.nl'))
 */
class JsonUnquote extends FunctionNode
{
    public Node|string|null $expression = null;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER); // JSON_UNQUOTE
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->expression = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf('JSON_UNQUOTE(%s)', $this->expression->dispatch($sqlWalker));
    }
}