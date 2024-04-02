<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use Throwable;


class MissingArgumentException extends ArgumentException {


    public function __construct( string $message = "Missing argument", int $code = 0, ?Throwable $previous = null ) {
        parent::__construct( $message, $code, $previous );
    }


}

