<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use Throwable;


class ExtraArgumentsException extends ArgumentException {


    /** @param list<string> $rstArgs */
    public function __construct( private readonly array $rstArgs, string $message = 'Extra arguments',
                                 int                    $code = 0, ?Throwable $previous = null ) {
        parent::__construct( $message, $code, $previous );
    }


    /** @return list<string> */
    public function getArguments() : array {
        return $this->rstArgs;
    }


}
