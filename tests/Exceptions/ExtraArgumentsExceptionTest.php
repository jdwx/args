<?php


declare( strict_types = 1 );


namespace Exceptions;


use JDWX\Args\Exceptions\ExtraArgumentsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( ExtraArgumentsException::class )]
final class ExtraArgumentsExceptionTest extends TestCase {


    public function testExtraArgumentsException() : void {
        $ex = new ExtraArgumentsException( [ 'foo', 'bar' ], 'message' );
        $r = $ex->getArguments();
        self::assertEquals( 'foo', $r[ 0 ] );
        self::assertEquals( 'bar', $r[ 1 ] );
        self::assertCount( 2, $r );
        self::assertEquals( 'message', $ex->getMessage() );
        self::assertEquals( 0, $ex->getCode() );
        self::assertNull( $ex->getPrevious() );
    }


}
