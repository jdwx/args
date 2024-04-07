<?php


declare( strict_types = 1 );


use JDWX\Args\StringParser;
use PHPUnit\Framework\TestCase;


class ParsedStringTest extends TestCase {


    public function testCount() : void {
        $x = StringParser::parseString( "foo bar baz" );
        self::assertCount( 5, $x );
        self::assertSame( 5, $x->count() );
    }


    public function testGetArguments() : void {
        $x = StringParser::parseString( "foo 1 baz" );
        $args = $x->getArguments();
        self::assertCount( 3, $args );
        self::assertSame( "foo", $args->shiftString() );
        self::assertSame( 1, $args->shiftInteger() );
        self::assertSame( "baz", $args->shiftString() );
    }


    public function testGetOriginal() : void {
        $x = StringParser::parseString( "foo bar baz" );
        self::assertEquals( "foo bar baz", $x->getOriginal() );
        self::assertEquals( "bar baz", $x->getOriginal( 1 ) );
    }


    public function testGetSegments() : void {
        $x = StringParser::parseString( "foo bar baz" );
        $r = $x->getSegments();
        self::assertEquals( "foo", $r[ 0 ] );
        self::assertEquals( "bar", $r[ 1 ] );
        self::assertEquals( "baz", $r[ 2 ] );
        self::assertCount( 3, $r );
    }


    public function testSubstBackQuotes() : void {
        $x = StringParser::parseString( "baz foo qux" );
        $x->substBackQuotes( function( $i_st ) { return $i_st; } );
        self::assertSame( "baz foo qux", $x->getProcessed() );

        $x = StringParser::parseString( "baz `foo` qux" );
        $x->substBackQuotes( function() { return "bar"; } );
        self::assertSame( "baz bar qux", $x->getProcessed() );
    }


    public function testSubstVariables() : void {
        $x = StringParser::parseString( "foo \$bar baz" );
        self::assertTrue( $x->substVariables( [ "bar" => "bar" ] ) );
        self::assertEquals( "foo bar baz", $x->getProcessed() );
    }


    public function testSubstVariablesForUndefinedVariable() : void {
        $x = StringParser::parseString( "foo \$bar baz" );
        $y = $x->substVariables( [] );
        self::assertIsString( $y );
        self::assertStringContainsString( "Undefined", $y );
    }


}
