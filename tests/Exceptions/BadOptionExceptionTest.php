<?php


declare( strict_types = 1 );


namespace Exceptions;


use JDWX\Args\Exceptions\BadOptionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( BadOptionException::class )]
final class BadOptionExceptionTest extends TestCase {


    public function testConstructor() : void {
        $exception = new BadOptionException( 'foo' );
        self::assertSame( 'Bad option', $exception->getMessage() );
        self::assertSame( '--foo', $exception->getValue() );
    }


}
