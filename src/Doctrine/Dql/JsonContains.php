<?php

namespace KikCMS\Doctrine\Dql;

use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class JsonContains extends FunctionNode
{
    public Node|string|null $fieldExpression = null;
    public Node|string|null $valueExpression = null;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->fieldExpression = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_COMMA);

        $this->valueExpression = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        $platform = $sqlWalker->getConnection()->getDatabasePlatform();

        $field = $this->fieldExpression->dispatch($sqlWalker);
        $value = $this->valueExpression->dispatch($sqlWalker);

        if ($platform instanceof SqlitePlatform) {
            return sprintf('EXISTS (SELECT 1 FROM json_each(%s) WHERE json_each.value = %s)', $field, $value);
        }

        return sprintf('JSON_CONTAINS(%s, %s)', $field, $value);
    }
}
