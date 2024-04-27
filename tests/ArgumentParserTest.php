<?php


declare( strict_types = 1 );


use JDWX\Args\ArgumentParser;
use JDWX\Args\BadArgumentException;
use PHPUnit\Framework\TestCase;


class ArgumentParserTest extends TestCase {


    public function testParseGlob() : void {
        $r = ArgumentParser::parseGlob( __DIR__ . '/data/*.txt' );
        static::assertCount( 3, $r );
        static::assertContains( __DIR__ . '/data/a.txt', $r );
        static::assertContains( __DIR__ . '/data/b.txt', $r );
        static::assertContains( __DIR__ . '/data/c.txt', $r );
    }


    public function testParseGlobNoMatch() : void {
        static::expectException( BadArgumentException::class );
        ArgumentParser::parseGlob( __DIR__ . '/data/*.foo');
    }


}
