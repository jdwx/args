<?php


declare( strict_types = 1 );


namespace Exceptions;


use JDWX\Args\Exceptions\BadArgumentException;
use JDWX\Param\Parameter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( BadArgumentException::class )]
final class BadArgumentExceptionTest extends TestCase {


    public function testBadArgumentException() : void {
        $ex = new BadArgumentException( 'foo', 'bar' );
        self::assertEquals( 'foo', $ex->getValue() );
        self::assertEquals( 'bar', $ex->getMessage() );
        self::assertEquals( 0, $ex->getCode() );
        self::assertNull( $ex->getPrevious() );

        $ex = new BadArgumentException( 'baz', $ex );
        self::assertSame( 'bar', $ex->getMessage() );
    }


    public function testConstructorForParameter() : void {
        $p = new Parameter( 'foo' );
        $ex = new BadArgumentException( $p, 'bad argument' );
        self::assertSame( '"foo"', $ex->getValue() );
        self::assertSame( 'bad argument', $ex->getMessage() );
    }


}
