#!/usr/bin/php
<?php

namespace Sunnysideup\Huringa;

require_once dirname(__FILE__) .'/vendor/autoload.php';

set_error_handler(function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

$parameters = $argv;
$command = basename(array_shift($parameters));

if (count($parameters) < 1 || in_array('--help', $parameters)) {
    echo <<<TXT
Usage:

    {$command} [options] <subject-file> <target-directory> [path-code]
With options:
    --help      Display this help message
    --dry-run   Show what would be written

Where:
    - <target-directory>
Example:
	{$command} --dry-run 'app/src'"
TXT;
} else {
	require __DIR__.'/vendor/autoload.php';

    /*/ Create variables from the command-line parameters /*/
    $dryRun = false;

	$parameters = array_map(function ($parameter) use (&$dryRun) {
        $isNamedParam = strpos($parameter, '--') === 0;
        if ($isNamedParam && $parameter === '--dry-run') {
            $dryRun = true;
        }
        return $isNamedParam?null:$parameter;
	}, $parameters);

    $filePath = realpath(array_shift($parameters));

    if (!is_dir($filePath)) {
        throw new \InvalidArgumentException("Could not find directory at given path {$filePath}");
    } else {
        echo "Indexing ". $filePath . PHP_EOL;

		parseFolder($filePath, $dryRun);
    }
}

function parseFolder($dir, $dryRun) {
    $parser = new ParseClass();

	foreach (scandir($dir) as $child) {
		if ($child == '.' || $child == '..') {
			continue;
        }

        $path = $dir . '/'. $child;

		if (is_dir($path)) {
            echo "- Indexing ". $child . PHP_EOL;
			parseFolder($path, $dryRun);
		} elseif (is_file($path)) {
            if (strpos($child, '.php') === false) {
                continue;
            }

            echo "Parsing ". $child . PHP_EOL;
			$parser->parseCode($path, $dryRun);
		} else {

        }
	}

}
