<?php


declare( strict_types = 1 );


use JDWX\Args\Arguments;
use JDWX\Args\BadOptionException;
use JDWX\Args\MissingOptionException;
use JDWX\Args\Option;
use PHPUnit\Framework\TestCase;


final class OptionTest extends TestCase {


    public function testAsBoolean() : void {
        $opt = new Option( 'foo', i_bFlagOnly: false );
        self::assertFalse( $opt->asBool() );
        $opt->set( true );
        self::assertTrue( $opt->asBool() );
        $opt->set( 'true' );
        self::assertTrue( $opt->asBool() );
        $opt->set( 'false' );
        self::assertFalse( $opt->asBool() );
        $opt->set( 'foo' );
        self::assertTrue( $opt->asBool() );
    }


    public function testAsBooleanForFlag() : void {
        $opt = new Option( 'foo', i_bFlagOnly: true );
        $opt->set( true );
        self::assertTrue( $opt->asBool() );
        $opt->set( false );
        self::assertFalse( $opt->asBool() );
        $opt->set( 'true' );
        self::assertTrue( $opt->asBool() );
        $opt->set( 'false' );
        self::assertFalse( $opt->asBool() );
        self::expectException( BadOptionException::class );
        $opt->set( 'foo' );
    }


    public function testAsParameterForFlag() : void {
        $opt = new Option( 'foo', i_bFlagOnly: true );
        self::assertFalse( $opt->asParameter()->asBool() );
        $opt->set( true );
        self::assertTrue( $opt->asParameter()->asBool() );
    }


    public function testAsParameterForValue() : void {
        $opt = new Option( 'foo', 'bar' );
        self::assertTrue( $opt->asParameter()->isNull() );
        $opt->set( true );
        self::assertSame( 'bar', $opt->asParameter()->asString() );
    }


    public function testAsString() : void {
        $opt = new Option( 'foo', i_bFlagOnly: false );
        self::assertNull( $opt->asString() );
        $opt->set( 'false' );
        self::assertFalse( $opt->asBool() );
        self::assertSame( 'false', $opt->asString() );
        $opt->set( 'bar' );
        self::assertSame( 'bar', $opt->asString() );
        $opt->set( true );
        self::expectException( BadOptionException::class );
        $opt->asString();
    }


    public function testAsStringExForMissingValue() : void {
        $opt = new Option( 'foo', i_bFlagOnly: false );
        self::expectException( MissingOptionException::class );
        $opt->asStringEx();
    }


    public function testAsStringExForUnspecifiedTrue() : void {
        $opt = new Option( 'foo', i_bFlagOnly: false, i_xValue: 'yup' );
        self::assertSame( 'yup', $opt->asStringEx() );
        $opt->set( true );
        self::expectException( BadOptionException::class );
        $opt->asStringEx();
    }


    public function testAsStringForFlag() : void {
        $opt = new Option( 'foo', i_bFlagOnly: true );
        self::expectException( LogicException::class );
        $opt->asString();
    }


    public function testSetForArguments() : void {
        $opt = new Option( 'foo', i_bFlagOnly: false );
        $args = new Arguments( [ '--foo=bar', '--baz=qux', 'quux' ] );
        $opt->set( $args );
        self::assertSame( 'bar', $opt->asString() );
        $rOptions = $args->handleOptions();
        self::assertArrayHasKey( 'baz', $rOptions );
        self::assertSame( 'qux', $rOptions[ 'baz' ] );
        self::assertSame( 'quux', $args->shiftString() );
        $args->end();
    }


    public function testSetForArgumentsWithBareFlag() : void {
        $opt = new Option( 'foo' );
        $args = new Arguments( [ '--foo' ] );
        $opt->set( $args );
        self::assertTrue( $opt->asBool() );

        $args = new Arguments( [ '--foo', '--no-foo' ] );
        $opt->set( $args );
        self::assertFalse( $opt->asBool() );

        $args = new Arguments( [ '--foo', '--', '--no-foo' ] );
        $opt->set( $args );
        self::assertTrue( $opt->asBool() );
    }


    public function testSetForArgumentsWithMissingOption() : void {
        $opt = new Option( 'foo', i_bFlagOnly: false );
        $args = new Arguments( [ '--baz=qux', 'quux' ] );
        $opt->set( $args );
        self::assertNull( $opt->asString() );
    }


    public function testSetForArgumentsWithValue() : void {
        $opt = new Option( 'foo', 'bar' );
        self::assertFalse( $opt->asBool() );
        self::assertNull( $opt->asString() );
        $args = new Arguments( [ '--foo' ] );
        $opt->set( $args );
        self::assertSame( 'bar', $opt->asString() );

        $args = new Arguments( [ '--foo=qux' ] );
        $opt->set( $args );
        self::assertSame( 'qux', $opt->asString() );
    }


    public function testSimpleBool() : void {
        self::assertTrue( Option::simpleBool( 'foo', 'yes' ) );
        self::assertFalse( Option::simpleBool( 'foo', 'no' ) );
        $args = new Arguments( [ '--no-foo' ] );
        self::assertFalse( Option::simpleBool( 'foo', $args ) );
        $args = new Arguments( [ '--foo' ] );
        self::assertTrue( Option::simpleBool( 'foo', $args ) );
    }


    public function testSimpleInt() : void {
        self::assertSame( 42, Option::simpleInt( 'foo', '42' ) );
        $args = new Arguments( [ 'bar', 'baz', 'qux' ] );
        self::assertNull( Option::simpleInt( 'foo', $args ) );
        self::assertSame( 42, Option::simpleInt( 'foo', true, 42 ) );
        self::assertSame( 42, Option::simpleInt( 'foo', false, 21, 42 ) );
        self::assertSame( 21, Option::simpleInt( 'foo', true, 21, 42 ) );
    }


    public function testSimpleIntEx() : void {
        self::assertSame( 42, Option::simpleIntEx( 'foo', '42' ) );
        self::assertSame( 42, Option::simpleIntEx( 'foo', true, 42 ) );
        self::assertSame( 42, Option::simpleIntEx( 'foo', false, 21, 42 ) );
        $args = new Arguments( [ 'bar', 'baz', 'qux' ] );
        self::expectException( MissingOptionException::class );
        Option::simpleIntEx( 'foo', $args );
    }


    public function testSimpleString() : void {
        self::assertSame( 'bar', Option::simpleString( 'foo', 'bar' ) );
        self::assertNull( Option::simpleString( 'foo', false ) );
        self::assertSame( 'bar', Option::simpleString( 'foo', true, 'bar' ) );
        self::assertSame( 'baz', Option::simpleString( 'foo', false, 'bar', 'baz' ) );
        self::expectException( BadOptionException::class );
        self::assertNull( Option::simpleString( 'foo', true ) );
    }


    public function testSimpleStringEx() : void {
        self::assertSame( 'bar', Option::simpleStringEx( 'foo', 'bar' ) );
        self::assertSame( 'bar', Option::simpleStringEx( 'foo', true, 'bar' ) );
        self::assertSame( 'baz', Option::simpleStringEx( 'foo', false, 'bar', 'baz' ) );
        self::expectException( MissingOptionException::class );
        $args = new Arguments( [] );
        Option::simpleStringEx( 'foo', $args );
    }


}
