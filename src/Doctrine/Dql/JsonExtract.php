<?php

namespace App\Doctrine\Dql;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * JSON_EXTRACT(field, path)
 *
 * Usage in DQL:
 *   JSON_EXTRACT(e.data, '$.name.nl')
 */
class JsonExtract extends FunctionNode
{
    public Node|string|null $fieldExpression = null;
    public Node|string|null $pathExpression = null;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        // argument 1
        $this->fieldExpression = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_COMMA);

        // argument 2 (string)
        $this->pathExpression = $parser->StringPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            'JSON_EXTRACT(%s, %s)',
            $this->fieldExpression->dispatch($sqlWalker),
            $this->pathExpression->dispatch($sqlWalker)
        );
    }
}