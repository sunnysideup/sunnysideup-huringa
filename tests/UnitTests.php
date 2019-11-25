<?php

namespace Sunnysideup\Huringa\Tests;

use PHPUnit\Framework\TestCase;
use Sunnysideup\Huringa\ParseClass;

final class UnitTests extends TestCase
{

    public function testClassSplit()
    {
        $parser = new ParseClass();

		foreach (glob(__DIR__ . '/samples/ClassSplit/*.php') as $sample) {
            $parsed = $parser->parseCode($sample, null);
            $this->assertEquals(2, count($parsed), 'Parser generates 2 files');
        }
    }

    public function testConstructorRewrite()
    {
        $parser = new ParseClass();

		foreach (glob(__DIR__ . '/samples/ConstructorRewrites/*.php') as $sample) {
            $parsed = $parser->parseCode($sample, null);
            $this->assertEquals(1, count($parsed), 'Parser generates a file');

            $this->assertEquals(
                $parsed[$sample],
                file_get_contents(str_replace('.php', '.expected', $sample)),
                "$sample matches the output file."
            );
        }
    }
}
