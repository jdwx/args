<?php


declare( strict_types = 1 );


use PHPUnit\Framework\TestCase;


class ExtraArgumentsExceptionTest extends TestCase {


    public function testExtraArgumentsException() {
        $ex = new JDWX\Args\ExtraArgumentsException( [ "foo", "bar" ], "message" );
        $r = $ex->getArguments();
        self::assertEquals( "foo", $r[ 0 ] );
        self::assertEquals( "bar", $r[ 1 ] );
        self::assertCount( 2, $r );
        self::assertEquals( "message", $ex->getMessage() );
        self::assertEquals( 0, $ex->getCode() );
        self::assertNull( $ex->getPrevious() );
    }


}
