<?php


declare( strict_types = 1 );


namespace JDWX\Args;


/**
 * This is not really a full-featured interface for Arguments.
 * It's here to enforce the signature of the constructor.
 */
interface ArgumentsInterface {


    /** @param list<string> $i_rArgs */
    public function __construct( array $i_rArgs );


}