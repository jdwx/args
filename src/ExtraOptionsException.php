<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use Throwable;


class ExtraOptionsException extends ExtraArgumentsException {


    public function __construct( array      $rstArgs, string $message = 'Extra options', int $code = 0,
                                 ?Throwable $previous = null ) {
        parent::__construct( $rstArgs, $message, $code, $previous );
    }


}
