<?php


declare( strict_types = 1 );


namespace Exceptions;


use JDWX\Args\Exceptions\ExtraOptionsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( ExtraOptionsException::class )]
final class ExtraOptionsExceptionTest extends TestCase {


    public function testConstructor() : void {
        $exception = new ExtraOptionsException( [ 'foo', 'bar' ] );
        self::assertSame( 'Extra options', $exception->getMessage() );
        self::assertSame( [ 'foo', 'bar' ], $exception->getArguments() );
    }


}
