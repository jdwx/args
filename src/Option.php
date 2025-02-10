<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use JDWX\Param\IParameter;
use JDWX\Param\Parameter;
use JDWX\Param\Parse;
use JDWX\Param\ParseException;
use LogicException;


class Option {


    private ?string $nstValue = null;

    private bool $bValue = false;

    private readonly bool $bFlagOnly;


    public function __construct( private readonly string  $stName,
                                 private readonly ?string $nstValueOnTrue = null,
                                 private readonly ?string $nstValueOnFalse = null,
                                 ?bool                    $i_bFlagOnly = null,
                                 bool|string|Arguments    $i_xValue = false ) {
        $this->bFlagOnly = $i_bFlagOnly ??
            ( is_null( $nstValueOnTrue ) && is_null( $nstValueOnFalse ) && is_bool( $i_xValue ) );
        $this->set( $i_xValue );
    }


    /**
     * This doesn't work like the other simple methods because Option does
     * not natively support arrays.
     */
    public static function simpleArray( string $i_stName, Arguments|array $i_xValue,
                                        bool   $i_bRequired = true ) : array {
        if ( $i_xValue instanceof Arguments ) {
            $i_xValue = $i_xValue->collectOption( $i_stName );
        }
        assert( is_array( $i_xValue ) );
        if ( count( $i_xValue ) > 0 || ! $i_bRequired ) {
            return $i_xValue;
        }
        throw new MissingOptionException( 'Option --' . $i_stName . ' is required.' );
    }


    public static function simpleBool( string $i_stName, Arguments|bool|string $i_xValue ) : bool {
        $opt = new Option( $i_stName, i_bFlagOnly: true, i_xValue: $i_xValue );
        return $opt->asBool();
    }


    public static function simpleString( string  $i_stName, Arguments|bool|string $i_xValue,
                                         ?string $i_nstValueOnTrue = null ) : ?string {
        $opt = new Option( $i_stName, $i_nstValueOnTrue, i_bFlagOnly: false, i_xValue: $i_xValue );
        return $opt->asString();
    }


    public static function simpleStringEx( string  $i_stName, Arguments|bool|string $i_xValue,
                                           ?string $i_nstValueOnTrue = null ) : string {
        $nst = static::simpleString( $i_stName, $i_xValue, $i_nstValueOnTrue );
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingOptionException( 'Option --' . $i_stName . ' is required.' );
    }


    public function asBool() : bool {
        return $this->bValue;
    }


    public function asParameter() : IParameter {
        if ( $this->bFlagOnly ) {
            return new Parameter( $this->bValue ? 'true' : 'false' );
        }
        return new Parameter( $this->nstValue );
    }


    public function asString() : ?string {
        if ( $this->bFlagOnly ) {
            throw new LogicException( 'Option --' . $this->stName . ' cannot not have a value.' );
        }
        if ( $this->bValue && is_null( $this->nstValue ) ) {
            throw new BadOptionException( $this->stName, 'Option --' . $this->stName . ' requires a value.' );
        }
        return $this->nstValue;
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
            $this->bValue = true;
            $this->nstValue = $this->nstValueOnTrue;
            return;
        }

        if ( false === $i_bstValue ) {
            $this->bValue = false;
            $this->nstValue = $this->nstValueOnFalse;
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

        if ( null === $nbParsedBool ) {
            if ( $this->bFlagOnly ) {
                throw new BadOptionException( $this->stName, 'Option ' . $this->stName . ' does not accept a value.' );
            }
            $nbParsedBool = true;
        }

        $this->bValue = $nbParsedBool;
        $this->nstValue = $i_bstValue;

    }


}
