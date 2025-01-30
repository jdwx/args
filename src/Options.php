<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use ArrayAccess;
use JDWX\Param\IParameter;
use LogicException;


/** @implements ArrayAccess<string, bool|string|IParameter> */
class Options implements ArrayAccess {


    /** @var list<Option> */
    private array $rOptions;


    /**
     * @param iterable<Option>|null $options
     * @noinspection PhpDocSignatureInspection
     */
    public function __construct( ?iterable $options = null ) {
        $this->rOptions = iterator_to_array( $options ?? [] );
    }


    public function add( Option $i_option ) : void {
        $this->rOptions[] = $i_option;
    }


    public function fromArguments( Arguments $i_args ) : void {
        foreach ( $this->rOptions as $opt ) {
            $opt->set( $i_args );
        }
        $i_args->endOptions();
    }


    /**
     * @param array<string, bool|string> $i_rNewValues
     */
    public function fromArray( array $i_rNewValues ) : void {
        foreach ( $i_rNewValues as $stOption => $xValue ) {
            $this[ $stOption ] = $xValue;
        }
    }


    /**
     * @param string $offset
     * @suppress PhanTypeMismatchDeclaredParamNullable
     */
    public function offsetExists( mixed $offset ) : bool {
        $opt = $this->fetch( $offset );
        return $opt instanceof Option;
    }


    /**
     * @param string $offset
     * @suppress PhanTypeMismatchDeclaredParamNullable
     */
    public function offsetGet( mixed $offset ) : IParameter {
        return $this->fetchEx( $offset )->asParameter();
    }


    /**
     * @param string $offset
     * @param bool|string $value
     * @suppress PhanTypeMismatchDeclaredParamNullable
     */
    public function offsetSet( mixed $offset, mixed $value ) : void {
        $opt = $this->fetchEx( $offset );
        $opt->set( $value );
    }


    public function offsetUnset( mixed $offset ) : void {
        $this->offsetSet( $offset, false );
    }


    protected function fetch( string $i_stName ) : ?Option {
        foreach ( $this->rOptions as $opt ) {
            if ( $opt->name() === $i_stName ) {
                return $opt;
            }
        }
        return null;
    }


    protected function fetchEx( string $i_stName ) : Option {
        $opt = $this->fetch( $i_stName );
        if ( $opt instanceof Option ) {
            return $opt;
        }
        throw new LogicException( "Option not defined: {$i_stName}" );
    }


}
