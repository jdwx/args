<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use Throwable;


class BadArgumentException extends ArgumentException {


    public function __construct( private readonly string $value, string $message, int $code = 0, ?Throwable $previous = null ) {
        parent::__construct( $message, $code, $previous );
    }


    public function getValue() : string {
        return $this->value;
    }


}
