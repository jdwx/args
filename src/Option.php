<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use JDWX\Param\IParameter;
use JDWX\Param\Parameter;
use JDWX\Param\Parse;
use JDWX\Param\ParseException;
use LogicException;


class Option {


    private ?string $nstValue;

    private bool $bValue;

    private readonly bool $bFlagOnly;


    public function __construct( private readonly string  $stName,
                                 private readonly ?string $nstValueOnTrue = null,
                                 private readonly ?string $nstValueOnFalse = null,
                                 ?bool                    $i_bFlagOnly = null,
                                 bool|string              $i_bstValue = false ) {
        $this->bFlagOnly = $i_bFlagOnly ??
            ( is_null( $nstValueOnTrue ) && is_null( $nstValueOnFalse ) && is_bool( $i_bstValue ) );
        $this->set( $i_bstValue );
    }


    public function asBool() : bool {
        return $this->bValue;
    }


    public function asParameter() : IParameter {
        if ( $this->bFlagOnly ) {
            return new Parameter( $this->bValue );
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
