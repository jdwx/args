<?php


declare( strict_types = 1 );


use JDWX\Args\StringParser;
use JDWX\Args\ParsedString;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/MyStringParser.php';


class StringParserTest extends TestCase {


    public function testParseLineForBackQuotes() : void {
        $x = StringParser::parseString( '`foo`' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseLineForBackQuotesWithEscapedBackQuote() : void {
        $x = StringParser::parseString( '`foo\\`bar`' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo`bar', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseLineForBackQuotesWithMissingEndQuote() : void {
        $x = StringParser::parseString( '`foo' );
        self::assertIsString( $x );
        self::assertStringContainsString( 'Unmatched', $x );
    }


    public function testParseLineForBackslashAsLastCharacter() : void {
        $x = StringParser::parseString( 'foo\\' );
        self::assertIsString( $x );
        self::assertStringContainsString( 'Hanging', $x );
    }


    public function testParseLineForBackslashUnicode() : void {
        $x = StringParser::parseString( 'foo\\u00C3bar' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 3, $x );
        self::assertSame( '\\u00C3', $x->getSegment( 1 )->getOriginal() );
        self::assertSame( 'Ã', $x->getSegment( 1 )->getProcessed() );
    }


    public function testParseLineForBackslashOctal() : void {
        $x = StringParser::parseString( 'foo\\101bar' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 3, $x );
        self::assertSame( '\\101', $x->getSegment( 1 )->getOriginal() );
        self::assertSame( 'A', $x->getSegment( 1 )->getProcessed() );
    }


    public function testParseLineForBackslashNewline() : void {
        /** @noinspection SpellCheckingInspection */
        $x = StringParser::parseString( "foo\\nbar" );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 3, $x );
        self::assertSame( '\\n', $x->getSegment( 1 )->getOriginal() );
        self::assertSame( "\n", $x->getSegment( 1 )->getProcessed() );
    }


    public function testParseLineForCommentPartialLine() : void {
        $x = StringParser::parseString( 'foo # bar' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 3, $x );
        self::assertSame( 'foo', $x->getSegment( 0 )->getProcessed() );
        self::assertSame( ' ', $x->getSegment( 1 )->getProcessed() );
        self::assertSame( '', $x->getSegment( 2 )->getProcessed() );
    }


    public function testParseLineForCommentInQuotes() : void {
        $x = StringParser::parseString( '"foo # bar"' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo # bar', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseLineForCommentWholeLine() : void {
        $x = StringParser::parseString( '# foo' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( '', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseLineForEmpty() : void {
        $x = StringParser::parseString( '' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 0, $x );
    }


    public function testParseLineForSingleWord() : void {
        $x = StringParser::parseString( 'foo' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseLineForDoubleQuotedWord() : void {
        $x = StringParser::parseString( '"foo"' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseLineForDoubleQuotedWords() : void {
        $x = StringParser::parseString( '"foo bar"' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo bar', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseLineForDoubleQuotedWordWithEscapedQuote() : void {
        $x = StringParser::parseString( '"foo\""' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo"', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseLineForDoubleQuoteMissingEndQuote() : void {
        $x = StringParser::parseString( 'foo "bar' );
        self::assertIsString( $x );
        self::assertStringContainsString( 'Unmatched', $x );
    }


    public function testParseLineForSingleQuoteMissingEndQuote() : void {
        $x = StringParser::parseString( "foo 'bar" );
        self::assertIsString( $x );
        self::assertStringContainsString( 'Unmatched', $x );
    }


    public function testParseLineForSingleQuotedWord() : void {
        $x = StringParser::parseString( "'foo'" );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseLineForSingleQuotedWords() : void {
        $x = StringParser::parseString( "'foo bar'" );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo bar', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseLineForSingleQuotedWordWithEscapedQuote() : void {
        $x = StringParser::parseString( "'foo\\' bar'" );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( "foo' bar", $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseLineForSingleQuotesWithEscapedBackslash() : void {
        $x = StringParser::parseString( '\'foo\\ bar\'' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo\\ bar', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseLineForSingleQuoteWithEscapeSequence() : void {
        $x = StringParser::parseString( "'foo\\n bar'" );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo\\n bar', $x->getSegment( 0 )->getProcessed() );
    }



    public function testParseQuoteForDoubleQuote() : void {
        $x = MyStringParser::myParseQuote( 'foo" bar', '"' );
        self::assertIsArray( $x );
        self::assertCount( 2, $x );
        self::assertSame( 'foo', $x[0] );
        self::assertSame( ' bar', $x[1] );
    }


    public function testParseQuoteForSingleQuote() : void {
        $x = MyStringParser::myParseQuote( "foo' bar", "'" );
        self::assertIsArray( $x );
        self::assertCount( 2, $x );
        self::assertSame( 'foo', $x[0] );
        self::assertSame( ' bar', $x[1] );
    }


    public function testParseQuoteForDoubleQuoteWithEscapedQuote() : void {
        $x = MyStringParser::myParseQuote( 'foo\" bar" baz', '"' );
        self::assertIsArray( $x );
        self::assertCount( 2, $x );
        self::assertSame( 'foo" bar', $x[0] );
        self::assertSame( ' baz', $x[1] );
    }


    public function testParseQuoteForSingleQuoteWithEscapedQuote() : void {
        $x = MyStringParser::myParseQuote( "foo\\' bar' baz", "'" );
        self::assertIsArray( $x );
        self::assertCount( 2, $x );
        self::assertSame( "foo' bar", $x[0] );
        self::assertSame( ' baz', $x[1] );
    }


    public function testParseQuoteForEscapedBackslash() : void {
        $x = MyStringParser::myParseQuote( 'foo\\ bar" baz', '"' );
        self::assertIsArray( $x );
        self::assertCount( 2, $x );
        self::assertSame( 'foo\\ bar', $x[0] );
        self::assertSame( ' baz', $x[1] );
    }


    public function testParseQuoteForUnterminatedQuote() : void {
        $x = MyStringParser::myParseQuote( 'foo bar', "'" );
        self::assertIsString( $x );
        self::assertStringContainsString( 'Unmatched', $x );
    }


}
