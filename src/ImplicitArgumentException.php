<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use Throwable;


/**
 * Used to indicate that the arguments given are potentially valid, but only
 * when predicated an expectation that didn't hold. This most often occurs
 * in stateful commands. I.e., sequences of commands where the first command
 * sets up a state that the second command depends on. An
 * ImplicitArgumentException might occur if the second command is given
 * without the first.
 */
class ImplicitArgumentException extends ArgumentException {


    public function __construct( string     $message = 'Implicit argument exception', int $code = 0,
                                 ?Throwable $previous = null ) {
        parent::__construct( $message, $code, $previous );
    }


}
