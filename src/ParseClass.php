<?php

namespace Sunnysideup\Huringa;

use PhpParser\NodeVisitor\NameResolver;

class ParseClass
{
	/**
	 * @param string $filePath
	 * @param bool $dryRun
     * @param string[] $options
     *
     * @return array
	 */
	public function parseCode($filePath, $dryRun = true, $options = [])
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
                function ($node, $name) use (&$files, $printer, $targetPath, $originalStaments, $originalTokens,
                    $options, $filePath) {
                    $code = $printer->printFormatPreserving([$node], $originalStaments, $originalTokens) . "\n";

                    // transform any function ClassName[\(| ] to a PHP7 compatible function __construct(
                    if (!isset($options['constructor-rewrite']) || $options['constructor-rewrite']) {
                        $replace = sprintf("/function %s[\\(|\s*\\(]+/", $name);
                        $code = preg_replace($replace, 'function __construct(', $code);
                    }

                    if (!isset($options['class-file-create']) || $options['class-file-create']) {
					    $path = "{$targetPath}/{$name}.php";
                        $files[$path] = $code;
                    } else {
                        if (!isset($files[$filePath])) {
                            $files[$filePath] = '';
                        }

                        $files[$filePath] .= $code;
                    }
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
