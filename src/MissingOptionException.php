<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use Throwable;


class MissingOptionException extends MissingArgumentException {


    public function __construct( string $message = 'Missing option', int $code = 0, ?Throwable $previous = null ) {
        parent::__construct( $message, $code, $previous );
    }


}
