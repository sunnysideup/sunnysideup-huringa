<?php

namespace Sunnysideup\Huringa;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class ClassVisitor extends NodeVisitorAbstract
{
    /** @var string  */
	private $pathCode;

    /** @var Node[] */
	private $classNodes = [];

    /** @return Node[] */
    final public function getClassNodes()
    {
        return $this->classNodes;
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

	final public function __construct($pathCode)
    {
        $this->pathCode = (string) $pathCode;
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
}
