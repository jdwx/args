<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use JDWX\Param\IParameter;
use Throwable;


class BadArgumentException extends ArgumentException {


    private string $value;


    public function __construct( string|IParameter|null $i_value, string|Throwable $message, int $code = 0,
                                 ?Throwable             $previous = null ) {
        if ( $message instanceof Throwable ) {
            $previous = $message;
            $message = $previous->getMessage();
            $code = $previous->getCode();
        }
        parent::__construct( $message, $code, $previous );
        $this->value = $i_value instanceof IParameter ? json_encode( $i_value ) : $i_value;
    }


    public function getValue() : string {
        return $this->value;
    }


}
