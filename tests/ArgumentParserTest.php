<?php


declare( strict_types = 1 );


use JDWX\Args\ArgumentParser;
use JDWX\Args\BadArgumentException;
use PHPUnit\Framework\TestCase;


class ArgumentParserTest extends TestCase {


    /**
     * @noinspection PhpDeprecationInspection
     * @suppress PhanDeprecatedFunction
     */
    public function testParseBoolean() : void {
        self::assertTrue( ArgumentParser::parseBoolean( 'true' ) );
        static::expectException( BadArgumentException::class );
        ArgumentParser::parseBoolean( 'foo' );
    }


    /**
     * @noinspection PhpDeprecationInspection
     * @suppress PhanDeprecatedFunction
     */
    public function testParseEmailAddress() : void {
        self::assertSame( 'foo@example.com', ArgumentParser::parseEmailAddress( 'foo@example.com' ) );
        static::expectException( BadArgumentException::class );
        ArgumentParser::parseEmailAddress( 'foo' );
    }


    public function testParseExistingDirectory() : void {
        $r = ArgumentParser::parseExistingDirectory( __DIR__ . '/data' );
        static::assertSame( __DIR__ . '/data', $r );

        static::expectException( BadArgumentException::class );
        ArgumentParser::parseExistingDirectory( __DIR__ . '/data/foo' );
    }


    public function testParseExistingDirectoryForFile() : void {
        static::expectException( BadArgumentException::class );
        ArgumentParser::parseExistingDirectory( __DIR__ . '/data/a.txt' );
    }


    /**
     * @noinspection PhpDeprecationInspection
     * @suppress PhanDeprecatedFunction
     */
    public function testParseFloat() : void {
        self::assertEqualsWithDelta( 1.23, ArgumentParser::parseFloat( '1.23' ), 0.001 );
        static::expectException( BadArgumentException::class );
        ArgumentParser::parseFloat( 'foo' );
    }


    public function testParseGlob() : void {
        $r = ArgumentParser::parseGlob( __DIR__ . '/data/*.txt' );
        static::assertCount( 3, $r );
        static::assertContains( __DIR__ . '/data/a.txt', $r );
        static::assertContains( __DIR__ . '/data/b.txt', $r );
        static::assertContains( __DIR__ . '/data/c.txt', $r );
    }


    public function testParseGlobNoMatch() : void {
        static::expectException( BadArgumentException::class );
        ArgumentParser::parseGlob( __DIR__ . '/data/*.foo' );
    }


    /**
     * @noinspection PhpDeprecationInspection
     * @suppress PhanDeprecatedFunction
     */
    public function testParseHostname() : void {
        static::assertSame( 'www.example.com', ArgumentParser::parseHostname( 'www.example.com' ) );
        static::expectException( BadArgumentException::class );
        ArgumentParser::parseHostname( 'foo' );
    }


    /**
     * @noinspection PhpDeprecationInspection
     * @suppress PhanDeprecatedFunction
     */
    public function testParseIPAddress() : void {
        static::assertSame( '192.0.2.1', ArgumentParser::parseIPAddress( '192.0.2.1' ) );
        static::expectException( BadArgumentException::class );
        ArgumentParser::parseIPAddress( 'foo' );
    }


    /**
     * @noinspection PhpDeprecationInspection
     * @suppress PhanDeprecatedFunction
     */
    public function testParseIPv4Address() : void {
        static::assertSame( '192.0.2.1', ArgumentParser::parseIPv4Address( '192.0.2.1' ) );
        static::expectException( BadArgumentException::class );
        ArgumentParser::parseIPv4Address( 'foo' );
    }


    /**
     * @noinspection PhpDeprecationInspection
     * @suppress PhanDeprecatedFunction
     */
    public function testParseIPv6Address() : void {
        static::assertSame( '2001:db8::1', ArgumentParser::parseIPv6Address( '2001:db8::1' ) );
        static::expectException( BadArgumentException::class );
        ArgumentParser::parseIPv6Address( 'foo' );
    }


    /**
     * @noinspection PhpDeprecationInspection
     * @suppress PhanDeprecatedFunction
     */
    public function testParseInteger() : void {
        self::assertSame( 123, ArgumentParser::parseInteger( '123' ) );
        static::expectException( BadArgumentException::class );
        ArgumentParser::parseInteger( 'foo' );
    }


    /**
     * @noinspection PhpDeprecationInspection
     * @suppress PhanDeprecatedFunction
     */
    public function testParseKeywords() : void {
        self::assertSame( 'foo', ArgumentParser::parseKeywords( 'foo', [ 'foo' ] ) );
        static::expectException( BadArgumentException::class );
        ArgumentParser::parseKeywords( 'bar', [ 'foo' ] );
    }


    /**
     * @noinspection PhpDeprecationInspection
     * @suppress PhanDeprecatedFunction
     */
    public function testParseMap() : void {
        self::assertSame( 'bar', ArgumentParser::parseMap( 'foo', [ 'foo' => 'bar' ] ) );
        static::expectException( BadArgumentException::class );
        ArgumentParser::parseMap( 'baz', [ 'foo' => 'bar' ] );
    }


    /**
     * @noinspection PhpDeprecationInspection
     * @suppress PhanDeprecatedFunction
     */
    public function testParseNonexistentFilename() : void {
        $st = __DIR__ . '/foo';
        self::assertSame( $st, ArgumentParser::parseNonexistentFilename( $st ) );
        static::expectException( BadArgumentException::class );
        ArgumentParser::parseNonexistentFilename( __FILE__ );
    }


    /**
     * @noinspection PhpDeprecationInspection
     * @suppress PhanDeprecatedFunction
     */
    public function testParsePositiveInteger() : void {
        self::assertSame( 123, ArgumentParser::parsePositiveInteger( '123', 1000 ) );
        static::expectException( BadArgumentException::class );
        ArgumentParser::parsePositiveInteger( 'foo', 1000 );
    }


    /**
     * @noinspection PhpDeprecationInspection
     * @suppress PhanDeprecatedFunction
     */
    public function testParseUnsignedInteger() : void {
        self::assertSame( 123, ArgumentParser::parsePositiveInteger( '123', 1000 ) );
        static::expectException( BadArgumentException::class );
        ArgumentParser::parsePositiveInteger( 'foo', 1000 );
    }


}
