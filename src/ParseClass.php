<?php

namespace Sunnysideup\Huringa;

use PhpParser\NodeVisitor\NameResolver;

class ParseClass
{
	/**
	 * @param string $filePath
	 * @param bool $dryRun
     *
     * @return array
	 */
	public function parseCode($filePath, $dryRun = true)
	{
		$code = file_get_contents($filePath);

		/*/ Set up Parser /*/
		$lexer = new \PhpParser\Lexer\Emulative([
			'usedAttributes' => [
				'comments',
				'startLine',
				'endLine',
				'startTokenPos',
				'endTokenPos',
			],
		]);

        // rewrite classes
		$parser = new \PhpParser\Parser\Php5($lexer);
		$traverser = new \PhpParser\NodeTraverser();
		$traverser->addVisitor(new \PhpParser\NodeVisitor\CloningVisitor());
        $classVisitor = new ClassVisitor($code);

		$traverser->addVisitor($classVisitor);
		$traverser->addVisitor(new NameResolver(null, [
			'preserveOriginalNames' => false,
			'replaceNodes' => true,
		]));

		/*/ Parse the given file /*/
		$originalStaments = $parser->parse($code);
		$originalTokens = $lexer->getTokens();
		$statements = $traverser->traverse($originalStaments);

		if (is_array($statements)) {
			/*/ Convert AST to string /*/
			$printer = new \PhpParser\PrettyPrinter\Standard();
			$files = [];

			// Add the code of each class
			$classNodes = $classVisitor->getClassNodes();
			$targetPath = dirname($filePath);

			array_walk($classNodes,
				function ($node, $name) use (&$files, $printer, $targetPath, $originalStaments, $originalTokens) {
					$path = "{$targetPath}/{$name}.php";
                    $code = $printer->printFormatPreserving([$node], $originalStaments, $originalTokens) . "\n";

                    // transform any function ClassName[\(| ] to a PHP7 compatible function __construct(
                    $replace = sprintf("/function %s[\\(|\s*\\(]+/", $name);
                    $code = preg_replace($replace, 'function __construct(', $code);

					$files[$path] = $code;
				});

            /*/ Actually write the files /*/
            $output = [];

			array_walk($files, function ($content, $path) use ($dryRun, &$output) {
                $output[$path] = $content;

                if (is_null($dryRun)) {
                    return;
                } else if ($dryRun === true) {
					echo "\n# ========================================================\nFILE: $path\nCONTENT: $content";
				} else {
					echo "Writing content to {$path}\n";
					file_put_contents($path, $content);
				}
            });

            return $output;
        }

        return [$filePath => $code];
	}
}
