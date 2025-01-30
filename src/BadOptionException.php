<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use JDWX\Param\IParameter;
use Throwable;


class BadOptionException extends BadArgumentException {


    public function __construct( string|IParameter|null $i_value, Throwable|string $message = 'Bad option',
                                 int                    $code = 0, ?Throwable $previous = null ) {
        parent::__construct( '--' . $i_value, $message, $code, $previous );
    }


}
