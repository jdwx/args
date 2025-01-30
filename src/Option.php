<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use JDWX\Param\IParameter;
use JDWX\Param\Parameter;
use JDWX\Param\Parse;
use JDWX\Param\ParseException;
use LogicException;


class Option {


    private string|bool $bstValue = false;

    private bool $bFlagOnly;


    public function __construct( private readonly string      $stName,
                                 private readonly bool|string $bstValueOnTrue = true,
                                 private readonly bool|string $bstValueOnFalse = false,
                                 ?bool                        $i_bFlagOnly = null,
                                 bool|string                  $i_bstValue = false ) {
        $this->bFlagOnly = $i_bFlagOnly ??
            ( $bstValueOnTrue === true && $bstValueOnFalse === false && is_bool( $i_bstValue ) );
        $this->set( $i_bstValue );
    }


    public function asBoolean() : bool {
        if ( is_bool( $this->bstValue ) ) {
            return $this->bstValue;
        }
        try {
            return Parse::bool( $this->bstValue );
        } catch ( ParseException ) {
            assert( ! $this->bFlagOnly );
            # Values that don't parse as boolean are assumed to be true.
            # (If we were in flag mode, we would have thrown an exception
            # when the value was set.)
            return true;
        }
    }


    public function asParameter() : IParameter {
        return new Parameter( $this->bstValue );
    }


    public function asString() : ?string {
        if ( $this->bFlagOnly ) {
            throw new LogicException( 'asString() called on flag ' . $this->stName );
        }
        if ( is_string( $this->bstValue ) ) {
            return $this->bstValue;
        }
        if ( false === $this->bstValue ) {
            return null;
        }
        assert( true === $this->bstValueOnTrue );
        throw new BadOptionException( $this->stName, 'Option --' . $this->stName . ' requires a value.' );
    }


    public function asStringEx() : string {
        $nst = $this->asString();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingOptionException( 'Option ' . $this->stName . ' does not have a value.' );
    }


    public function name() : string {
        return $this->stName;
    }


    public function set( bool|string|Arguments $i_bstValue ) : void {

        if ( $i_bstValue instanceof Arguments ) {
            $xValue = $i_bstValue->handleOption( $this->stName );
            if ( null === $xValue ) {
                return;
            }
            $i_bstValue = $xValue;
        }

        if ( true === $i_bstValue ) {
            $this->bstValue = $this->bstValueOnTrue;
            return;
        }

        if ( false === $i_bstValue ) {
            $this->bstValue = $this->bstValueOnFalse;
            return;
        }

        $nbParsedBool = null;
        try {
            if ( Parse::bool( $i_bstValue ) ) {
                $nbParsedBool = true;
            } else {
                $nbParsedBool = false;
            }
        } catch ( ParseException ) {
            // Do nothing.
        }

        if ( $this->bFlagOnly ) {
            if ( null === $nbParsedBool ) {
                throw new BadOptionException( $this->stName, 'Option ' . $this->stName . ' does not accept a value.' );
            }
            if ( true === $nbParsedBool ) {
                $this->bstValue = $this->bstValueOnTrue;
            } else {
                $this->bstValue = $this->bstValueOnFalse;
            }
            return;
        }

        if ( null === $nbParsedBool ) {
            $this->bstValue = $i_bstValue;
            return;
        }

        if ( true === $nbParsedBool ) {
            $this->bstValue = $this->bstValueOnTrue;
            return;
        }

        $this->bstValue = $this->bstValueOnFalse;
    }


}
