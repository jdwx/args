<?php


use JDWX\Args\Arguments;
use JDWX\Args\BadArgumentException;
use JDWX\Args\ExtraArgumentsException;
use JDWX\Args\MissingArgumentException;
use PHPUnit\Framework\TestCase;


class ArgumentsTest extends TestCase {


    public function testCount() : void {
        $args = new Arguments( [ 'Hello', 'world!' ] );
        self::assertEquals( 2, $args->count() );
        self::assertCount( 2, $args );
    }


    public function testForExtraArguments() : void {
        $args = new Arguments( [ 'Hello', 'world!' ] );
        self::expectException( ExtraArgumentsException::class );
        $args->end();
    }


    public function testEmpty() : void {
        $args = new Arguments( [] );
        self::assertTrue( $args->empty() );
        $args = new Arguments( [ 'Hello', 'world!' ] );
        self::assertFalse( $args->empty() );
        $args->shiftString();
        $args->shiftString();
        self::assertTrue( $args->empty() );
        self::assertEmpty( $args );
        $args->end();
    }


    public function testEndsWithArray() : void {
        $r = [ 'Hello', 'world!' ];
        $args = new Arguments( $r );
        self::assertEquals( $r, $args->endWithArray() );
        self::assertTrue( $args->empty() );
    }


    public function testEndsWithGlob() : void {
        $args = new Arguments( [ __DIR__ . '/data/*.txt', __DIR__ . '/data/a.*' ] );
        $r = $args->endWithGlob();
        self::assertCount( 5, $r );
        self::assertContains( __DIR__ . '/data/a.json', $r );
        self::assertContains( __DIR__ . '/data/a.txt', $r );
        self::assertContains( __DIR__ . '/data/a.yml', $r );
        self::assertContains( __DIR__ . '/data/b.txt', $r );
        self::assertContains( __DIR__ . '/data/c.txt', $r );
        self::assertNotContains( __DIR__ . '/data/b.json', $r );
        self::assertTrue( $args->empty() );
    }


    public function testEndsWithString() : void {
        $args = new Arguments( [ 'Hello', 'world!' ] );
        self::assertEquals( 'Hello world!', $args->endWithString() );
        self::assertTrue( $args->empty() );
    }


    public function testHandleOptions() : void {
        $args = new Arguments( [ '--foo=bar', '--baz', 'Hello', '--no-qux', '--', '--quux', 'world!' ] );
        $rOptions = $args->handleOptions();
        self::assertEquals( 'bar', $rOptions[ 'foo' ] );
        self::assertTrue( $rOptions[ 'baz' ] );
        self::assertFalse( $rOptions[ 'qux' ] );
        self::assertCount( 3, $rOptions );
        self::assertEquals( [ 'Hello', '--quux', 'world!' ], $args->endWithArray() );
    }


    public function testHandleOptionsDefined() : void {
        $args = new Arguments( [ '--foo=bar', '--baz', 'Hello', '--no-qux', '--', '--quux', 'world!' ] );
        $rOptions = $args->handleOptionsDefined( [ 'foo', 'baz', 'qux' ] );
        self::assertEquals( 'bar', $rOptions[ 'foo' ] );
        self::assertTrue( $rOptions[ 'baz' ] );
        self::assertFalse( $rOptions[ 'qux' ] );
        self::assertCount( 3, $rOptions );
        self::assertEquals( [ 'Hello', '--quux', 'world!' ], $args->endWithArray() );
    }


    public function testHandleOptionsDefinedForBadOptionForBoolean() : void {
        $args = new Arguments( [ '--foo=bar', '--baz', 'Hello', '--no-qux', '--', '--quux', 'world!' ] );
        self::expectException( BadArgumentException::class );
        $args->handleOptionsDefined( [ 'foo', 'baz' ] );
    }


    public function testHandleOptionsDefinedForBadOptionForString() : void {
        $args = new Arguments( [ '--foo=bar', '--baz', 'Hello', '--qux=quux', '--', '--quux', 'world!' ] );
        self::expectException( BadArgumentException::class );
        $args->handleOptionsDefined( [ 'foo', 'baz' ] );
    }


    public function testPeekKeywords() : void {
        $rKeywords = [ 'foo', 'bar' ];
        $args = new Arguments( [ 'foo', 'bar', 'baz' ] );
        self::assertEquals( 'foo', $args->peekKeywords( $rKeywords ) );
        self::assertEquals( 'foo', $args->peekKeywords( $rKeywords, true ) );
        self::assertEquals( 'bar', $args->peekKeywords( $rKeywords, true ) );
        self::assertNull( $args->peekKeywords( $rKeywords ) );
        $args->shiftStringEx();
        self::assertNull( $args->peekKeywords( $rKeywords ) );
    }


    public function testPeekString() : void {
        $args = new Arguments( [ 'Hello', 'world!' ] );
        self::assertNull( $args->peekString( 'foo' ) );
        self::assertEquals( 'Hello', $args->peekString() );
        self::assertSame( "", $args->peekString( "Hello", true ) );
        self::assertEquals( 'world!', $args->peekString() );
        self::assertSame( "", $args->peekString( "world!", true ) );
        self::assertNull( $args->peekString() );
        self::expectException( LogicException::class );
        $args->peekString( null, true );
    }


    public function testShiftBoolean() : void {
        $args = new Arguments( [ 'true', 'yes', 'on', '1', 'false', 'no', 'off', '0', 'foo' ] );
        self::assertTrue( $args->shiftBoolean() );
        self::assertTrue( $args->shiftBoolean() );
        self::assertTrue( $args->shiftBoolean() );
        self::assertTrue( $args->shiftBoolean() );
        self::assertFalse( $args->shiftBoolean() );
        self::assertFalse( $args->shiftBoolean() );
        self::assertFalse( $args->shiftBoolean() );
        self::assertFalse( $args->shiftBoolean() );
        self::expectException( BadArgumentException::class );
        $args->shiftBoolean();
    }


    /**
     * @noinspection PhpDeprecationInspection Preserve until 1.1.0.
     * @suppress PhanDeprecatedFunction
     */
    public function testShiftBool() : void {
        $args = new Arguments( [ 'true' ] );
        self::assertTrue( $args->shiftBool() );
        self::assertNull( $args->shiftBool() );
    }


    /**
     * @noinspection PhpDeprecationInspection Preserve until 1.1.0.
     * @suppress PhanDeprecatedFunction
     */
    public function testShiftBoolEx() : void {
        $args = new Arguments( [ 'true' ] );
        self::assertTrue( $args->shiftBoolEx() );
        self::expectException( MissingArgumentException::class );
        $args->shiftBoolEx();
    }


    public function testShiftBooleanForNoArg() : void {
        $args = new Arguments( [] );
        self::assertNull( $args->shiftBoolean() );
    }


    public function testShiftBooleanEx() : void {
        $args = new Arguments( [ 'true' ] );
        self::assertTrue( $args->shiftBooleanEx() );
        self::expectException( MissingArgumentException::class );
        $args->shiftBooleanEx();
    }


    public function testShiftEmailAddress() : void {
        $args = new Arguments( [ 'foo@example.com' ] );
        self::assertEquals( 'foo@example.com', $args->shiftEmailAddress() );
        self::assertNull( $args->shiftEmailAddress() );
    }


    public function testShiftEmailAddressForNonEmailAddress() : void {
        $args = new Arguments( [ 'foo' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftEmailAddress();
    }


    public function testShiftEmailAddressEx() : void {
        $args = new Arguments( [ 'foo@example.com' ] );
        self::assertEquals( 'foo@example.com', $args->shiftEmailAddressEx() );
        self::expectException( MissingArgumentException::class );
        $args->shiftEmailAddressEx();
    }


    public function testShiftExistingFileBody() : void {
        $args = new Arguments( [ __FILE__ ] );
        self::assertEquals( file_get_contents( __FILE__ ), $args->shiftExistingFileBody( $st ) );
        self::assertEquals( __FILE__, $st );
        self::assertTrue( $args->empty() );
        self::assertNull( $args->shiftExistingFileBody() );
    }


    public function testShiftExistingFileBodyExForSuccess() : void {
        $args = new Arguments( [ __FILE__ ] );
        self::assertEquals( file_get_contents( __FILE__ ), $args->shiftExistingFileBodyEx( $st ) );
        self::assertEquals( __FILE__, $st );
        self::assertTrue( $args->empty() );
    }


    public function testShiftExistingFileBodyExForNoSuchFile() : void {
        $args = new Arguments( [ '/no/such/file/nonexistent' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftExistingFileBodyEx();
    }


    public function testShiftExistingFileBodyExForNoArgument() : void {
        $args = new Arguments( [] );
        self::expectException( MissingArgumentException::class );
        $args->shiftExistingFileBodyEx();
    }


    public function testShiftExistingFilename() : void {
        $args = new Arguments( [ __FILE__ ] );
        self::assertEquals( __FILE__, $args->shiftExistingFilename() );
        self::assertTrue( $args->empty() );
        self::assertNull( $args->shiftExistingFilename() );
    }


    public function testShiftExistingFilenameExForSuccess() : void {
        $args = new Arguments( [ __FILE__ ] );
        self::assertEquals( __FILE__, $args->shiftExistingFilenameEx() );
        self::assertTrue( $args->empty() );
    }


    public function testShiftExistingFilenameExForNoSuchFile() : void {
        $args = new Arguments( [ '/no/such/file/nonexistent' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftExistingFilenameEx();
    }


    public function testShiftExistingFilenameExForNoArgument() : void {
        $args = new Arguments( [] );
        self::expectException( MissingArgumentException::class );
        $args->shiftExistingFilenameEx();
    }


    public function testShiftFloat() : void {
        $args = new Arguments( [ '123', '456', '78.9' ] );
        self::assertEqualsWithDelta( 123.0, $args->shiftFloat(), 0.0001 );
        self::assertEqualsWithDelta( 456.0, $args->shiftFloat(), 0.0001 );
        self::assertEqualsWithDelta( 78.9, $args->shiftFloat(), 0.0001 );
        self::assertNull( $args->shiftFloat() );
    }


    public function testShiftFloatForOutOfRange() : void {
        $args = new Arguments( [ '0.123', '1.5' ] );
        self::assertEqualsWithDelta( 0.123, $args->shiftFloat( 0, 1 ), 0.0001 );
        self::expectException( BadArgumentException::class );
        $args->shiftFloat( 0, 1 );
    }


    public function testShiftFloatForExact() : void {
        $args = new Arguments( [ '1.0' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftFloat( 0, 1.0 );
    }


    public function testShiftFloatForNonFloat() : void {
        $args = new Arguments( [ 'foo' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftFloat();
    }


    public function testShiftFloatEx() : void {
        $args = new Arguments( [ '123' ] );
        self::assertEquals( 123.0, $args->shiftFloatEx() );
        self::expectException( MissingArgumentException::class );
        $args->shiftFloatEx();
    }


    public function testShiftGlob() : void {
        $args = new Arguments( [ __DIR__ . '/data/*.txt' ] );
        $r = $args->shiftGlob();
        static::assertIsArray( $r );
        assert( is_array( $r ) );
        self::assertCount( 3, $r );
        self::assertContains( __DIR__ . '/data/a.txt', $r );
        self::assertContains( __DIR__ . '/data/b.txt', $r );
        self::assertContains( __DIR__ . '/data/c.txt', $r );
    }


    public function testShiftGlobForNoArgs() : void {
        $args = new Arguments( [] );
        static::assertNull( $args->shiftGlob() );
    }


    public function testShiftGlobForNoMatch() : void {
        $args = new Arguments( [ __DIR__ . '/data/*.foo' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftGlob();
    }


    public function testShiftGlobForNoMatchIsOK() : void {
        $args = new Arguments( [ __DIR__ . '/data/*.foo' ] );
        $r = $args->shiftGlob( true );
        static::assertEmpty( $r );
    }


    public function testShiftGlobEx() : void {
        $args = new Arguments( [ __DIR__ . '/data/*.txt' ] );
        $r = $args->shiftGlobEx();
        self::assertCount( 3, $r );
        self::assertContains( __DIR__ . '/data/a.txt', $r );
        self::assertContains( __DIR__ . '/data/b.txt', $r );
        self::assertContains( __DIR__ . '/data/c.txt', $r );
    }


    public function testShiftGlobExForNoArgs() : void {
        $args = new Arguments( [] );
        self::expectException( MissingArgumentException::class );
        $args->shiftGlobEx();
    }


    public function testShiftHostname() : void {
        $args = new Arguments( [ 'example.com', 'www.example.com' ] );
        self::assertEquals( 'example.com', $args->shiftHostname() );
        self::assertEquals( 'www.example.com', $args->shiftHostname() );
        self::assertNull( $args->shiftHostname() );
    }


    public function testShiftHostnameForNonHostname() : void {
        $args = new Arguments( [ '_foo' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftHostname();
    }


    public function testShiftHostnameEx() : void {
        $args = new Arguments( [ 'example.com' ] );
        self::assertEquals( 'example.com', $args->shiftHostnameEx() );
        self::expectException( MissingArgumentException::class );
        $args->shiftHostnameEx();
    }


    public function testShiftInteger() : void {
        $args = new Arguments( [ '123', '456', '78.9', '0' ] );
        self::assertSame( 123, $args->shiftInteger() );
        self::assertSame( 456, $args->shiftInteger() );
        self::assertSame( 78, $args->shiftInteger() );
        self::assertSame( 0, $args->shiftInteger() );
        self::assertNull( $args->shiftInteger() );
    }


    public function testShiftIntegerForNonInteger() : void {
        $args = new Arguments( [ 'foo' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftInteger();
    }


    public function testShiftIntegerForOutOfRange() : void {
        $args = new Arguments( [ '123', '1000' ] );
        self::assertEquals( 123, $args->shiftInteger( 0, 123 ) );
        self::expectException( BadArgumentException::class );
        $args->shiftInteger( 0, 500 );
    }


    public function testShiftIntegerEx() : void {
        $args = new Arguments( [ '123' ] );
        self::assertEquals( 123, $args->shiftIntegerEx() );
        self::expectException( MissingArgumentException::class );
        $args->shiftIntegerEx();
    }


    public function testShiftIPAddress() : void {
        /** @noinspection SpellCheckingInspection */
        $args = new Arguments( [ '1.2.3.4', '1234:5678:90ab:cdef::1' ] );
        self::assertEquals( '1.2.3.4', $args->shiftIPAddress() );
        /** @noinspection SpellCheckingInspection */
        self::assertEquals( '1234:5678:90ab:cdef::1', $args->shiftIPAddress() );
        self::assertNull( $args->shiftIPAddress() );
    }


    public function testShiftIPAddressForNonIPAddress() : void {
        $args = new Arguments( [ 'foo' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftIPAddress();
    }


    public function testShiftIPAddressEx() : void {
        $args = new Arguments( [ '1.2.3.4' ] );
        self::assertEquals( '1.2.3.4' , $args->shiftIPAddressEx() );
        self::expectException( MissingArgumentException::class );
        $args->shiftIPAddressEx();
    }


    public function testShiftIPv4Address() : void {
        $args = new Arguments( [ '1.2.3.4' ] );
        self::assertEquals( '1.2.3.4', $args->shiftIPv4Address() );
        self::assertNull( $args->shiftIPv4Address() );
    }


    public function testShiftIPv4AddressForNonIPv4Address() : void {
        $args = new Arguments( [ 'foo' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftIPv4Address();
    }


    public function testShiftIPv4AddressForIPv6Address() : void {
        /** @noinspection SpellCheckingInspection */
        $args = new Arguments( [ '1234:5678:90ab:cdef::1' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftIPv4Address();
    }


    public function testShiftIPv4AddressEx() : void {
        $args = new Arguments( [ '1.2.3.4' ] );
        self::assertEquals( '1.2.3.4', $args->shiftIPv4AddressEx() );
        self::expectException( MissingArgumentException::class );
        $args->shiftIPv4AddressEx();
    }


    public function testShiftIPv6Address() : void {
        /** @noinspection SpellCheckingInspection */
        $args = new Arguments( [ '1234:5678:90ab:cdef::1' ] );
        /** @noinspection SpellCheckingInspection */
        self::assertEquals( '1234:5678:90ab:cdef::1', $args->shiftIPv6Address() );
        self::assertNull( $args->shiftIPv6Address() );
    }


    public function testShiftIPv6AddressForNonIPv6Address() : void {
        $args = new Arguments( [ 'foo' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftIPv6Address();
    }


    public function testShiftIPv6AddressForIPv4Address() : void {
        $args = new Arguments( [ '1.2.3.4' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftIPv6Address();
    }


    public function testShiftIPv6AddressEx() : void {
        /** @noinspection SpellCheckingInspection */
        $args = new Arguments( [ '1234:5678:90ab:cdef::1' ] );
        /** @noinspection SpellCheckingInspection */
        self::assertEquals( '1234:5678:90ab:cdef::1', $args->shiftIPv6AddressEx() );
        self::expectException( MissingArgumentException::class );
        $args->shiftIPv6AddressEx();
    }


    public function testShiftKeyword() : void {
        $rKeywords = [ 'foo', 'bar' ];
        $args = new Arguments( [ 'foo', 'bar' ] );
        self::assertEquals( 'foo', $args->shiftKeyword( $rKeywords ) );
        self::assertEquals( 'bar', $args->shiftKeyword( $rKeywords ) );
        self::assertNull( $args->shiftKeyword( $rKeywords ) );
    }


    public function testShiftKeywordForNotKeyword() : void {
        $rKeywords = [ 'foo', 'bar' ];
        $args = new Arguments( [ 'foo', 'baz' ] );
        self::assertEquals( 'foo', $args->shiftKeyword( $rKeywords ) );
        self::expectException( BadArgumentException::class );
        $args->shiftKeyword( $rKeywords );
    }


    public function testShiftKeywordEx() : void {
        $rKeywords = [ 'foo', 'bar' ];
        $args = new Arguments( [ 'foo' ] );
        self::assertEquals( 'foo', $args->shiftKeywordEx( $rKeywords ) );
        self::expectException( MissingArgumentException::class );
        $args->shiftKeywordEx( $rKeywords );
    }


    public function testShiftMap() : void {
        $rMap = [ 'foo' => 'bar', 'baz' => 'qux' ];
        $args = new Arguments( [ 'foo', 'baz' ] );
        self::assertEquals( 'bar', $args->shiftMap( $rMap ) );
        self::assertEquals( 'qux', $args->shiftMap( $rMap ) );
        self::assertNull( $args->shiftMap( $rMap ) );
    }


    public function testShiftMapForNotKey() : void {
        $rMap = [ 'foo' => 'bar', 'baz' => 'qux' ];
        $args = new Arguments( [ 'foo', 'quux' ] );
        self::assertEquals( 'bar', $args->shiftMap( $rMap ) );
        self::expectException( BadArgumentException::class );
        $args->shiftMap( $rMap );
    }


    public function testShiftMapEx() : void {
        $rMap = [ 'foo' => 'bar', 'baz' => 'qux' ];
        $args = new Arguments( [ 'foo' ] );
        self::assertEquals( 'bar', $args->shiftMapEx( $rMap ) );
        self::expectException( MissingArgumentException::class );
        $args->shiftMapEx( $rMap );
    }


    public function testShiftNonexistentFilename() : void {
        $st = __DIR__ . PATH_SEPARATOR . 'nonexistent-file-xyz123';
        $args = new Arguments( [ $st ] );
        self::assertEquals( $st, $args->shiftNonexistentFilename() );
        $args->end();
        self::assertNull( $args->shiftNonexistentFilename() );
    }


    public function testShiftNonexistentFilenameForExistingFilename() : void {
        $args = new Arguments( [ __FILE__ ] );
        self::expectException( BadArgumentException::class );
        $args->shiftNonexistentFilename();
    }


    public function testShiftNonexistentFilenameForBadDirectory() : void {
        $st = __DIR__ . '/nonexistent-directory-xyz123/nonexistent-file-xyz123';
        $args = new Arguments( [ $st ] );
        self::expectException( BadArgumentException::class );
        $args->shiftNonexistentFilename();
    }


    public function testShiftNonexistentFilenameEx() : void {
        $st = __DIR__ . PATH_SEPARATOR . 'nonexistent-file-xyz123';
        $args = new Arguments( [ $st ] );
        self::assertEquals( $st, $args->shiftNonexistentFilenameEx() );
        $args->end();
        self::expectException( MissingArgumentException::class );
        $args->shiftNonexistentFilenameEx();
    }


    public function testShiftPositiveInteger() : void {
        $args = new Arguments( [ '123', '456', '78.9' ] );
        self::assertEquals( 123, $args->shiftPositiveInteger() );
        self::assertEquals( 456, $args->shiftPositiveInteger() );
        self::assertEquals( 78, $args->shiftPositiveInteger() );
        self::assertNull( $args->shiftPositiveInteger() );
        $args->end();
    }


    public function testShiftPositiveIntegerForNonInteger() : void {
        $args = new Arguments( [ 'foo' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftPositiveInteger();
    }


    public function testShiftPositiveIntegerForOutOfRange() : void {
        $args = new Arguments( [ '-100' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftPositiveInteger();
    }


    public function testShiftPositiveIntegerForZero() : void {
        $args = new Arguments( [ '0' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftPositiveInteger();
    }


    public function testShiftPositiveIntegerEx() : void {
        $args = new Arguments( [ '123' ] );
        self::assertEquals( 123, $args->shiftPositiveIntegerEx() );
        self::expectException( MissingArgumentException::class );
        $args->shiftPositiveIntegerEx();
    }


    public function testShiftString() : void {
        $args = new Arguments( [ 'Hello', 'world!' ] );
        self::assertEquals( 'Hello', $args->shiftString() );
        self::assertEquals( 'world!', $args->shiftString() );
        $args->end();
        self::assertNull( $args->shiftString() );
    }


    public function testShiftStringEx() : void {
        $args = new Arguments( [ 'Hello' ] );
        self::assertEquals( 'Hello', $args->shiftStringEx() );
        self::expectException( MissingArgumentException::class );
        $args->shiftStringEx();
    }


    public function testShiftUnsignedInteger() : void {
        $args = new Arguments( [ '123', '456', '0', '78.9' ] );
        self::assertEquals( 123, $args->shiftUnsignedInteger() );
        self::assertEquals( 456, $args->shiftUnsignedInteger() );
        self::assertEquals( 0, $args->shiftUnsignedInteger() );
        self::assertEquals( 78, $args->shiftUnsignedInteger() );
        self::assertNull( $args->shiftUnsignedInteger() );
        $args->end();
    }


    public function testShiftUnsignedIntegerForNonInteger() : void {
        $args = new Arguments( [ 'foo' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftUnsignedInteger();
    }


    public function testShiftUnsignedIntegerForOutOfRange() : void {
        $args = new Arguments( [ '-100' ] );
        self::expectException( BadArgumentException::class );
        $args->shiftUnsignedInteger();
    }


    public function testShiftUnsignedIntegerEx() : void {
        $args = new Arguments( [ '123' ] );
        self::assertEquals( 123, $args->shiftUnsignedIntegerEx() );
        self::expectException( MissingArgumentException::class );
        $args->shiftUnsignedIntegerEx();
    }


    public function testSummarizeKeywords() : void {
        $r = [ 'foo', 'bar', 'baz' ];
        self::assertEquals( 'foo, bar, baz', Arguments::summarizeKeywords( $r ) );
        $r = [ 'foo', 'bar', 'baz', 'qux', 'quux' ];
        self::assertEquals( 'foo, bar, baz, qux, quux', Arguments::summarizeKeywords( $r ) );
        $r = [ 'foo', 'bar', 'baz', 'qux', 'quux', 'hi' ];
        self::assertEquals( 'foo, bar, baz, qux, ...', Arguments::summarizeKeywords( $r ) );
    }


}
