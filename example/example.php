<?php


declare( strict_types = 1 );


require_once __DIR__ . '/../vendor/autoload.php';


use JDWX\Args\Arguments;


# Remove the script name from the arguments for our example.
$argv = array_slice( $argv, 1 );

$args = new Arguments( $argv );
echo $args->shiftString(), "\n"; # Echoes "Hello," in the given example.


$args = new JDWX\Args\Arguments( [ 'not-an-email-address' ] );
try {
    $email = $args->shiftEmailAddress();
} catch ( JDWX\Args\BadArgumentException $e ) {
    echo "Not a valid email address: ", $e->getValue(), "\n";
}


