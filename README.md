# jdwx/args

PHP library for handling command-line arguments

It's designed to provide methods for safely retrieving most common argument types,
including strings, integers, floating point values, boolean flags, sets of keywords,
filenames, hostnames, IP addresses, and email addresses.

It's useful for handling arguments presented to a PHP script on the shell command line, e.g.:

```bash
YourPrompt$ ./example.php Hello, world.
```

Or it could refer to the arguments on a command entered into a PHP-based REPL, such as a custom management tool.

## Installation

You can require it directly with Composer:

```bash
composer require jdwx/args
```

Or download the source from GitHub: https://github.com/jdwx/args.git

## Requirements

This library requires PHP 8.2 or later. It might work with earlier versions of PHP 8,
but it has not been tested with them.

## Usage

```php
<?php

declare( strict_types = 1 );

require_once __DIR__ . '/../vendor/autoload.php';

use JDWX\Args\Arguments;

# Remove the script name from the arguments for our example.
$argv = array_slice( $argv, 1 );

$args = new Arguments( $argv );
echo $args->shiftString(), "\n"; # Echoes "Hello," in the given example.
```

All "shift" methods throw an exception if an argument exists but is invalid:

```php

$args = new JDWX\Args\Arguments( [ 'not-an-email-address' ] );
try {
    $email = $args->shiftEmailAddress();
} catch ( JDWX\Args\BadArgumentException $e) {
    echo "Not a valid email address: ", $e->getValue(), "\n";
}
```

The default form of such methods returns null if no more arguments are present,
which is useful for iterating in a while loop:

```php
$args = new JDWX\Args\Arguments( [ 1, 2, 3, 4, 5 ] );
while ( $i = $args->shiftInteger() ) {
    echo "Got integer: $i\n";
}
```

The shift methods also provide a variant that throws an exception if no more
arguments are present. This is useful for ensuring that a required argument is
present:

```php
while ( $st = $args->shiftString() ) {
    $i = $args->shiftIntegerEx();
    assert( is_int( $i ) );
    echo "Got: $st => $i\n";
}
```

It is also possible to "peek" at the next argument without necessarily consuming it.
This is supported for strings that match a certain prefix:

```php

# If the next argument is "prefix_example," $st will be set to "example."
# In this example, the i_bConsume flag is not set, so the argument will not
# be consumed.
$args = new JDWX\Args\Arguments( [ 'prefix_example' ] );
if ( $st = $args->peekString( 'prefix_' ) ) {
    echo "Got: {$st}\n";
    $st2 = $args->shiftString();
    echo "Shifted: {$st2}\n"; # Argument was not consumed.
} else {
    echo "Nope!\n";
}
```

or for strings in set of keywords:

```php

$rKeywords = [ 'example', 'demo', 'test' ];
# If the next argument is "example," $st will be set to "example." In this example,
# the consume flag is set so the argument is consumed if (and only if) it matches.
# The default is kept as false for consistency with peekString(), but usually
# you do want to consume keyword-matching arguments.
if ( $st = $args->peekKeywords( $rKeywords, i_bConsume: true ) ) {
    echo "Got: $st\n";
} else {
    echo "Nope!\n";
}
````

The library also provides handling for optional Gnu-style arguments that begin with
two hyphens. It supports both boolean flags (e.g., --flag and --no-flag) and options that require a value (e.g., --key=value). It does
**not
** support short options (e.g, "-h") or options that require a value to be specified in the next argument (e.g., "--key value").

```php
$options = new Options( [
    new Option( 'foo' ),
    new Option( 'bar', i_bstValue: true ),
    new Option( 'baz', i_bFlagOnly: false ),
    new Option( 'qux', '1', '0' ),
] );
$args = new JDWX\Args\Arguments( [ "--foo", "--no-bar", "--baz=quux", "--qux=3" ] );
$options->fromArguments( $args );
echo 'foo = ', $options[ 'foo' ]->asBool() ? 'true' : 'false', "\n";
echo 'bar = ', $options[ 'bar' ]->asBool() ? 'true' : 'false', "\n";
echo 'baz = ', $options[ 'baz' ]->asString(), "\n";
for ( $ii = 0 ; $ii < $options[ 'qux' ]->asInt() ; ++$ii ) {
    echo "qux\n";
}
```

If you only have one flag, you can use the Option class directly.

```php
$args = new Arguments( [ "--foo=bar" ] );
$option = new Option( 'foo', i_bFlagOnly: false );
$option->set( $args );
echo 'foo as bool = ', $option->asBool(), "\n"; # Echoes "true" because flag was present.
echo 'foo as str = ', $option->asString(), "\n"; # Echoes "bar" in the given example.
```

If you don't want to bother predefining options, you can also extract everything that looks like one from the arguments:

```php
$args = new Arguments( [ "--foo=bar", "--baz", "--no-qux", "leftover" ] );
$rOptions = $args->handleOptions();
echo $rOptions[ 'foo' ], "\n"; # Echoes "bar"
echo ( $rOptions[ 'baz' ] === true ) ? 'true' : 'Nope!', "\n"; # Echoes "true"
echo ( $rOptions[ 'qux' ] === false ) ? 'false' : 'Nope!', "\n"; # Echoes "false"
echo $args->shiftString(), "\n"; # Echoes "leftover"
```

Handling options through any of these interfaces removes them from the argument list, allowing remaining arguments to be processed without further interference.

## Stability

This library is considered stable and is used in production code. Additional parsing
methods may be added in the future, but existing methods should not be removed or
changed in a way that breaks backwards compatibility. Exceptions to address security
issues or bugs may occur, but are expected to be rare.

## History

This library has been in use for many years. It was refactored out of a larger codebase
in 2023 and was first released as a standalone library in 2024.
