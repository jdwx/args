<?php


declare( strict_types = 1 );


use JDWX\Args\Arguments;
use JDWX\Args\ExtraOptionsException;
use JDWX\Args\Option;
use JDWX\Args\Options;
use PHPUnit\Framework\TestCase;


final class OptionsTest extends TestCase {


    public function testAdd() : void {
        $opt = new Option( 'foo', i_bstValue: 'bar' );
        $options = new Options();
        $options->add( $opt );
        self::assertSame( 'bar', $options[ 'foo' ]->asString() );
    }


    public function testFromArguments() : void {
        $options = new Options( [
            new Option( 'foo', i_bstValue: 'bar' ),
            new Option( 'baz', i_bstValue: 'qux' ),
        ] );
        $args = new Arguments( [ '--foo=quux', '--baz=corge', ] );
        $options->fromArguments( $args );
        self::assertSame( 'quux', $options[ 'foo' ]->asString() );
        self::assertSame( 'corge', $options[ 'baz' ]->asString() );
    }


    public function testFromArgumentsForExtraOptions() : void {
        $options = new Options( [
            new Option( 'foo', i_bstValue: 'bar' ),
            new Option( 'baz', i_bstValue: 'qux' ),
        ] );
        $args = new Arguments( [ '--foo=quux', '--baz=corge', '--grault=garply' ] );
        self::expectException( ExtraOptionsException::class );
        $options->fromArguments( $args );
    }


    public function testFromArgumentsForStopper() : void {
        $options = new Options( [
            new Option( 'foo', i_bstValue: 'bar' ),
            new Option( 'baz', i_bstValue: 'qux' ),
        ] );
        $args = new Arguments( [ '--foo=quux', '--', '--baz=corge' ] );
        $options->fromArguments( $args );
        self::assertSame( 'quux', $options[ 'foo' ]->asString() );
        self::assertSame( 'qux', $options[ 'baz' ]->asString() );
        self::assertSame( '--baz=corge', $args->shiftStringEx() );
    }


    public function testFromArray() : void {
        $options = new Options( [
            new Option( 'foo', i_bFlagOnly: false ),
            new Option( 'bar', i_bFlagOnly: false ),
        ] );
        $options->fromArray( [ 'foo' => 'baz', 'bar' => 'quux' ] );
        self::assertSame( 'baz', $options[ 'foo' ]->asString() );
        self::assertSame( 'quux', $options[ 'bar' ]->asString() );
    }


    public function testOffsetExists() : void {
        $opt = new Option( 'foo', i_bFlagOnly: false );
        $options = new Options( [ $opt ] );
        self::assertTrue( isset( $options[ 'foo' ] ) );
        self::assertFalse( isset( $options[ 'bar' ] ) );
    }


    public function testOffsetGet() : void {
        $opt = new Option( 'foo', i_bstValue: 'bar' );
        $options = new Options( [ $opt ] );
        self::assertSame( 'bar', $options[ 'foo' ]->asString() );
    }


    public function testOffsetGetForUndefined() : void {
        $options = new Options();
        self::expectException( LogicException::class );
        $x = $options[ 'foo' ];
        unset( $x );
    }


    public function testOffsetSet() : void {
        $opt = new Option( 'foo', i_bFlagOnly: false );
        self::assertNull( $opt->asString() );
        $options = new Options( [ $opt ] );
        $options[ 'foo' ] = 'baz';
        self::assertSame( 'baz', $opt->asString() );
    }


    public function testOffsetUnset() : void {
        $opt = new Option( 'foo', i_bstValue: 'bar' );
        $options = new Options( [ $opt ] );
        self::assertSame( 'bar', $options[ 'foo' ]->asString() );
        unset( $options[ 'foo' ] );
        self::assertNull( $opt->asString() );
    }


}
