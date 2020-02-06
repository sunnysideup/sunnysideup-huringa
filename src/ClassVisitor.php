<?php

namespace Sunnysideup\Huringa;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class ClassVisitor extends NodeVisitorAbstract
{
    /** @var string */
    private $pathCode;

    /** @var Node[] */
    private $classNodes = [];

    final public function __construct($pathCode)
    {
        $this->pathCode = (string) $pathCode;
    }

    /** @return Node[] */
    final public function getClassNodes()
    {
        return $this->classNodes;
    }

    final public function leaveNode(Node $node)
    {
        $statement = null;

        if ($node instanceof Node\Stmt\Class_) {
            $name = (string) $node->name;
            $this->classNodes[$name] = $node;
        }

        return $statement;
    }

    /** @return Parser */
    private function getParser()
    {
        static $parser;
        if ($parser === null) {
            $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);
        }
        return $parser;
    }
}
