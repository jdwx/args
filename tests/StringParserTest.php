<?php


declare( strict_types = 1 );


use JDWX\Args\ParsedString;
use JDWX\Args\StringParser;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/MyStringParser.php';


class StringParserTest extends TestCase {


    public function testParseQuoteForDoubleQuote() : void {
        $x = MyStringParser::myParseQuote( 'foo" bar', '"' );
        self::assertIsArray( $x );
        self::assertCount( 2, $x );
        self::assertSame( 'foo', $x[ 0 ] );
        self::assertSame( ' bar', $x[ 1 ] );
    }


    public function testParseQuoteForDoubleQuoteWithEscapedQuote() : void {
        $x = MyStringParser::myParseQuote( 'foo\" bar" baz', '"' );
        self::assertIsArray( $x );
        self::assertCount( 2, $x );
        self::assertSame( 'foo" bar', $x[ 0 ] );
        self::assertSame( ' baz', $x[ 1 ] );
    }


    public function testParseQuoteForEscapedBackslash() : void {
        $x = MyStringParser::myParseQuote( 'foo\\ bar" baz', '"' );
        self::assertIsArray( $x );
        self::assertCount( 2, $x );
        self::assertSame( 'foo\\ bar', $x[ 0 ] );
        self::assertSame( ' baz', $x[ 1 ] );
    }


    public function testParseQuoteForSingleQuote() : void {
        $x = MyStringParser::myParseQuote( "foo' bar", "'" );
        self::assertIsArray( $x );
        self::assertCount( 2, $x );
        self::assertSame( 'foo', $x[ 0 ] );
        self::assertSame( ' bar', $x[ 1 ] );
    }


    public function testParseQuoteForSingleQuoteWithEscapedQuote() : void {
        $x = MyStringParser::myParseQuote( "foo\\' bar' baz", "'" );
        self::assertIsArray( $x );
        self::assertCount( 2, $x );
        self::assertSame( "foo' bar", $x[ 0 ] );
        self::assertSame( ' baz', $x[ 1 ] );
    }


    public function testParseQuoteForUnterminatedQuote() : void {
        $x = MyStringParser::myParseQuote( 'foo bar', "'" );
        self::assertIsString( $x );
        self::assertStringContainsString( 'Unmatched', $x );
    }


    public function testParseStringForBackQuotes() : void {
        $x = StringParser::parseString( '`foo` bar' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 3, $x );
        self::assertSame( 'foo', $x->getSegment( 0 )->getProcessed() );
        self::assertSame( ' ', $x->getSegment( 1 )->getProcessed() );
        self::assertSame( 'bar', $x->getSegment( 2 )->getProcessed() );

        $x = StringParser::parseString( '`foo` bar', i_bBackquotes: false );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 3, $x );
        self::assertSame( '`foo`', $x->getSegment( 0 )->getProcessed() );
        self::assertSame( ' ', $x->getSegment( 1 )->getProcessed() );
        self::assertSame( 'bar', $x->getSegment( 2 )->getProcessed() );
    }


    public function testParseStringForBackQuotesWithEscapedBackQuote() : void {
        $x = StringParser::parseString( '`foo\\`bar`' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo`bar', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseStringForBackQuotesWithMissingEndQuote() : void {
        $x = StringParser::parseString( '`foo' );
        self::assertIsString( $x );
        self::assertStringContainsString( 'Unmatched', $x );
    }


    public function testParseStringForBackslashAsLastCharacter() : void {
        $x = StringParser::parseString( 'foo\\' );
        self::assertIsString( $x );
        self::assertStringContainsString( 'Hanging', $x );
    }


    public function testParseStringForBackslashNewline() : void {
        /** @noinspection SpellCheckingInspection */
        $x = StringParser::parseString( "foo\\nbar" );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 3, $x );
        self::assertSame( '\\n', $x->getSegment( 1 )->getOriginal() );
        self::assertSame( "\n", $x->getSegment( 1 )->getProcessed() );
    }


    public function testParseStringForBackslashOctal() : void {
        $x = StringParser::parseString( 'foo\\101bar' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 3, $x );
        self::assertSame( '\\101', $x->getSegment( 1 )->getOriginal() );
        self::assertSame( 'A', $x->getSegment( 1 )->getProcessed() );
    }


    public function testParseStringForBackslashUnicode() : void {
        $x = StringParser::parseString( 'foo\\u00C3bar' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 3, $x );
        self::assertSame( '\\u00C3', $x->getSegment( 1 )->getOriginal() );
        self::assertSame( 'Ã', $x->getSegment( 1 )->getProcessed() );
    }


    public function testParseStringForCommentInQuotes() : void {
        $x = StringParser::parseString( '"foo # bar"' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo # bar', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseStringForCommentPartialLine() : void {
        $x = StringParser::parseString( 'foo # bar' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 3, $x );
        self::assertSame( 'foo', $x->getSegment( 0 )->getProcessed() );
        self::assertSame( ' ', $x->getSegment( 1 )->getProcessed() );
        self::assertSame( '', $x->getSegment( 2 )->getProcessed() );
    }


    public function testParseStringForCommentWholeLine() : void {
        $x = StringParser::parseString( '# foo' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( '', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseStringForDoubleQuoteMissingEndQuote() : void {
        $x = StringParser::parseString( 'foo "bar' );
        self::assertIsString( $x );
        self::assertStringContainsString( 'Unmatched', $x );
    }


    public function testParseStringForDoubleQuotedWord() : void {
        $x = StringParser::parseString( '"foo"' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseStringForDoubleQuotedWordEscapedQuote() : void {
        $x = StringParser::parseString( '"foo\""' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo"', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseStringForDoubleQuotedWords() : void {
        $x = StringParser::parseString( '"foo bar"' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo bar', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseStringForDoubleQuotes() : void {
        $x = StringParser::parseString( '"foo" bar' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 3, $x );
        self::assertSame( 'foo', $x->getSegment( 0 )->getProcessed() );
        self::assertSame( ' ', $x->getSegment( 1 )->getProcessed() );
        self::assertSame( 'bar', $x->getSegment( 2 )->getProcessed() );

        $x = StringParser::parseString( '"foo" bar', i_bDoubleQuotes: false );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 3, $x );
        self::assertSame( '"foo"', $x->getSegment( 0 )->getProcessed() );
        self::assertSame( ' ', $x->getSegment( 1 )->getProcessed() );
        self::assertSame( 'bar', $x->getSegment( 2 )->getProcessed() );
    }


    public function testParseStringForEmpty() : void {
        $x = StringParser::parseString( '' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 0, $x );
    }


    public function testParseStringForSingleQuoteMissingEndQuote() : void {
        $x = StringParser::parseString( "foo 'bar" );
        self::assertIsString( $x );
        self::assertStringContainsString( 'Unmatched', $x );
    }


    public function testParseStringForSingleQuoteWithEscapeSequence() : void {
        $x = StringParser::parseString( "'foo\\n bar'" );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo\\n bar', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseStringForSingleQuotedWord() : void {
        $x = StringParser::parseString( "'foo'" );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseStringForSingleQuotedWordEscapedQuote() : void {
        $x = StringParser::parseString( "'foo\\' bar'" );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( "foo' bar", $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseStringForSingleQuotedWords() : void {
        $x = StringParser::parseString( "'foo bar'" );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo bar', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseStringForSingleQuotes() : void {
        $x = StringParser::parseString( "'foo' bar" );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 3, $x );
        self::assertSame( 'foo', $x->getSegment( 0 )->getProcessed() );
        self::assertSame( ' ', $x->getSegment( 1 )->getProcessed() );
        self::assertSame( 'bar', $x->getSegment( 2 )->getProcessed() );

        $x = StringParser::parseString( "'foo' bar", i_bSingleQuotes: false );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 3, $x );
        self::assertSame( "'foo'", $x->getSegment( 0 )->getProcessed() );
        self::assertSame( ' ', $x->getSegment( 1 )->getProcessed() );
        self::assertSame( 'bar', $x->getSegment( 2 )->getProcessed() );
    }


    public function testParseStringForSingleQuotesEscapedBackslash() : void {
        $x = StringParser::parseString( '\'foo\\ bar\'' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo\\ bar', $x->getSegment( 0 )->getProcessed() );
    }


    public function testParseStringForSingleWord() : void {
        $x = StringParser::parseString( 'foo' );
        self::assertInstanceOf( ParsedString::class, $x );
        self::assertCount( 1, $x );
        self::assertSame( 'foo', $x->getSegment( 0 )->getProcessed() );
    }


}
