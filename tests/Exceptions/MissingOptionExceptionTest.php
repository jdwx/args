<?php


declare( strict_types = 1 );


namespace Exceptions;


use JDWX\Args\Exceptions\MissingOptionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass( MissingOptionException::class )]
final class MissingOptionExceptionTest extends TestCase {


    public function testConstructor() : void {
        $ex = new MissingOptionException();
        self::assertSame( 'Missing option', $ex->getMessage() );
    }


}
