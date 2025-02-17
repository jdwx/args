<?php


declare( strict_types = 1 );


use JDWX\Args\ImplicitArgumentException;
use PHPUnit\Framework\TestCase;


final class ImplicitArgumentExceptionTest extends TestCase {


    public function testConstruct() : void {
        $e = new ImplicitArgumentException();
        self::assertStringContainsString( 'Implicit argument', $e->getMessage() );
    }


}
