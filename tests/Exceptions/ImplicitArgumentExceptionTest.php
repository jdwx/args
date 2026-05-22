<?php


declare( strict_types = 1 );


namespace Exceptions;


use JDWX\Args\Exceptions\ImplicitArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( ImplicitArgumentException::class )]
final class ImplicitArgumentExceptionTest extends TestCase {


    public function testConstruct() : void {
        $e = new ImplicitArgumentException();
        self::assertStringContainsString( 'Implicit argument', $e->getMessage() );
    }


}
