<?php


declare( strict_types = 1 );


require_once __DIR__ . '/../vendor/autoload.php';


use JDWX\Args\Arguments;
use JDWX\Args\Option;
use JDWX\Args\Options;


# Remove the script name from the arguments for our example.
$argv = array_slice( $argv, 1 );

$args = new Arguments( $argv );
echo $args->shiftString(), "\n"; # Echoes "Hello," in the given example.

## -------------------------

$args = new Arguments( [ 'not-an-email-address' ] );
try {
    $email = $args->shiftEmailAddress();
} catch ( JDWX\Args\BadArgumentException $e ) {
    echo "Not a valid email address: ", $e->getValue(), "\n";
}

## -------------------------

$args = new Arguments( [ 1, 2, 3, 4, 5 ] );
while ( $i = $args->shiftInteger() ) {
    echo "Got integer: $i\n";
}

## -------------------------

# If the next argument is "prefix_example," $st will be set to "example."
# In this example, the i_bConsume flag is not set, so the argument will not
# be consumed.
$args = new Arguments( [ 'prefix_example' ] );
if ( $st = $args->peekString( 'prefix_' ) ) {
    echo "Got: {$st}\n";
    $st2 = $args->shiftString();
    echo "Shifted: {$st2}\n"; # Argument was not consumed.
} else {
    echo "Nope!\n";
}

## -------------------------

$rKeywords = [ 'example', 'demo', 'test' ];
$args = new Arguments( [ 'example', 'stuff' ] );
# If the next argument is "example," $st will be set to "example." In this example,
# the consume flag is set. The argument is consumed if (and only if) it matches.
# The default is kept as false for consistency with peekString(), but usually
# you do want to consume keyword-matching arguments.
if ( $st = $args->peekKeywords( $rKeywords, i_bConsume: true ) ) {
    echo "Got: $st\n";
} else {
    echo "Nope!\n";
}

## -------------------------

$options = new Options( [
    new Option( 'foo' ),
    new Option( 'bar', i_bstValue: true ),
    new Option( 'baz', i_bFlagOnly: false ),
    new Option( 'qux', '1', '0' ),
] );
$args = new Arguments( [ "--foo", "--no-bar", "--baz=quux", "--qux=3" ] );
$options->fromArguments( $args );
echo 'foo = ', $options[ 'foo' ]->asBool() ? 'true' : 'false', "\n";
echo 'bar = ', $options[ 'bar' ]->asBool() ? 'true' : 'false', "\n";
echo 'baz = ', $options[ 'baz' ]->asString(), "\n";
for ( $ii = 0 ; $ii < $options[ 'qux' ]->asInt() ; ++$ii ) {
    echo "qux\n";
}

## -------------------------

$args = new Arguments( [ "--foo=bar" ] );
$option = new Option( 'foo', i_bFlagOnly: false );
$option->set( $args );
echo 'foo as bool = ', $option->asBool(), "\n"; # Echoes "true" because flag was present.
echo 'foo as str = ', $option->asString(), "\n"; # Echoes "bar" in the given example.

## -------------------------

$args = new Arguments( [ "--foo=bar", "--baz", "--no-qux", "leftover" ] );
$rOptions = $args->handleOptions();
echo $rOptions[ 'foo' ], "\n"; # Echoes "bar"
echo ( $rOptions[ 'baz' ] === true ) ? 'true' : 'Nope!', "\n"; # Echoes "true"
echo ( $rOptions[ 'qux' ] === false ) ? 'false' : 'Nope!', "\n"; # Echoes "false"
echo $args->shiftString(), "\n"; # Echoes "leftover"
