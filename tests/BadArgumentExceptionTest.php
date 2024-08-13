<?php


declare( strict_types = 1 );


use PHPUnit\Framework\TestCase;


class BadArgumentExceptionTest extends TestCase {


    public function testBadArgumentException() : void {
        $ex = new JDWX\Args\BadArgumentException( "value", "message" );
        self::assertEquals( "value", $ex->getValue() );
        self::assertEquals( "message", $ex->getMessage() );
        self::assertEquals( 0, $ex->getCode() );
        self::assertNull( $ex->getPrevious() );
    }


}
