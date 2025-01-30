<?php


declare( strict_types = 1 );


require __DIR__ . '/../vendor/autoload.php';


use JDWX\Args\Arguments;
use JDWX\Args\StringParser;


( function () : void {

    $parse = StringParser::parseString( 'The $foo brown fox jumps over the `LAZY` dog.' );

    $parse->substVariables( [
        'foo' => 'quick',
    ] );
    $parse->substBackQuotes( fn( string $st ) => strtolower( $st ) );

    $args = new Arguments( $parse );
    while ( $st = $args->shiftString() ) {
        echo 'Arg: ', $st, "\n";
    }

} )();