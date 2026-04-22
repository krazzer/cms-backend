<?php

namespace KikCMS\Doctrine\Dql;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;
use Doctrine\DBAL\Platforms\SqlitePlatform;

class JsonLength extends FunctionNode
{
    public Node|string|null $fieldExpression = null;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->fieldExpression = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        $platform = $sqlWalker->getConnection()->getDatabasePlatform();

        $field = $this->fieldExpression->dispatch($sqlWalker);

        if ($platform instanceof SqlitePlatform) {
            return sprintf('json_array_length(%s)', $field);
        }

        return sprintf('JSON_LENGTH(%s)', $field);
    }
}