<?php


declare( strict_types = 1 );


use JDWX\Args\ArgumentParser;
use JDWX\Args\Exceptions\BadArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( ArgumentParser::class )]
final class ArgumentParserTest extends TestCase {


    public function testParseExistingFilename() : void {
        self::assertSame( __FILE__, ArgumentParser::parseExistingFilename( __FILE__ ) );
        self::expectException( BadArgumentException::class );
        ArgumentParser::parseExistingFilename( __DIR__ . '/foo' );
    }


    public function testParseGlob() : void {
        $r = ArgumentParser::parseGlob( __DIR__ . '/data/*.txt' );
        self::assertCount( 3, $r );
        self::assertContains( __DIR__ . '/data/a.txt', $r );
        self::assertContains( __DIR__ . '/data/b.txt', $r );
        self::assertContains( __DIR__ . '/data/c.txt', $r );
    }


    public function testParseGlobNoMatch() : void {
        self::expectException( BadArgumentException::class );
        ArgumentParser::parseGlob( __DIR__ . '/data/*.foo' );
    }


}
