<?php


declare( strict_types = 1 );


namespace Exceptions;


use JDWX\Args\Exceptions\MissingArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( MissingArgumentException::class )]
final class MissingArgumentExceptionTest extends TestCase {


    public function testConstructor() : void {
        $ex = new MissingArgumentException();
        self::assertSame( 'Missing argument', $ex->getMessage() );
    }


}
