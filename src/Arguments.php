<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use Countable;
use JDWX\Param\IParameter;
use JDWX\Param\Parameter;
use JDWX\Param\Parse;
use JDWX\Param\ParseException;
use LogicException;
use TypeError;


class Arguments extends ArgumentParser implements Countable {


    /** @param list<string> $args */
    public function __construct( protected array $args ) {}


    /**
     * This is a convenience method for parsing a boolean option, handling
     * cases like "--do-the-thing=yes" as well as "--do-the-thing" alone.
     *
     * If strict mode is not specified (the default), then any value that
     * does not parse as a boolean is treated as true. Otherwise, a
     * non-boolean value will throw a BadArgumentException explaining
     * that a boolean was expected for the named option.
     *
     * @param string $i_stName The name of the option.
     * @param bool|string|null $i_xValue The value provided.
     * @param bool $i_bStrict If true, an exception is thrown if the value
     *                        given does not parse as boolean.
     * @return bool The boolean value of the option.
     */
    public static function booleanOption( string  $i_stName, bool|string|null $i_xValue, bool $i_bStrict = false,
                                          ?string $i_nstMessage = null ) : bool {
        if ( is_bool( $i_xValue ) ) {
            return $i_xValue;
        }
        if ( is_null( $i_xValue ) ) {
            return false;
        }
        try {
            return Parse::bool( $i_xValue );
        } catch ( ParseException $e ) {
            if ( ! $i_bStrict ) {
                return true;
            }
            $stReason = $i_nstMessage ?? "Expected boolean for option \"{$i_stName}\"";
            throw new BadArgumentException( $i_xValue, $stReason, $e->getCode(), $e );
        }

    }


    /**
     * You need to overload this in child classes or else it
     * will copy the wrong class!
     */
    public static function fromString( string $i_st ) : self {
        $parsed = StringParser::parseString( $i_st );
        assert( static::class === self::class );
        return new self( $parsed->getSegments() );
    }


    /**
     * A convenience function for dealing with string options that might have
     * default values.
     *
     * @param bool|string|null $i_xValue
     * @param string|null $i_nstTrueDefault
     * @return string|null
     */
    public static function stringOption( bool|string|null $i_xValue,
                                         ?string          $i_nstTrueDefault = null ) : ?string {
        if ( is_null( $i_xValue ) || false === $i_xValue ) {
            return null;
        }

        if ( true === $i_xValue && is_string( $i_nstTrueDefault ) ) {
            return $i_nstTrueDefault;
        }

        # At this point, the value is a string.
        assert( is_string( $i_xValue ) );

        # If we were given a default value for true, we want to replace the
        # given value if (and only if) it parses as true.
        if ( is_string( $i_nstTrueDefault ) ) {
            try {
                if ( Parse::bool( $i_xValue ) ) {
                    return $i_nstTrueDefault;
                } else {
                    return null;
                }
            } catch ( ParseException ) {
                // This indicates a custom value was provided.
            }
        }
        return $i_xValue;
    }


    /**
     * A convenience function for dealing with string options that really need to
     * be present.
     *
     * @param string $i_stName
     * @param bool|string|null $i_xValue
     * @param string|null $i_nstTrueDefault
     * @param string|null $i_nstMessage
     * @return string
     */
    public static function stringOptionEx( string  $i_stName, bool|string|null $i_xValue,
                                           ?string $i_nstTrueDefault = null,
                                           ?string $i_nstMessage = '' ) : string {
        $nst = self::stringOption( $i_xValue, $i_nstTrueDefault );
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException(
            $i_nstMessage ?? "Missing string argument for option \"{$i_stName}\""
        );
    }


    /**
     * You need to overload this in child classes or else it
     * will copy the wrong class!
     */
    public function copy() : self {
        assert( $this::class === self::class );
        return new self( $this->args );
    }


    public function count() : int {
        return count( $this->args );
    }


    public function empty() : bool {
        return empty( $this->args );
    }


    /**
     * Asserts that we have handled all arguments.
     */
    public function end() : void {
        if ( $this->empty() ) {
            return;
        }
        throw new ExtraArgumentsException( $this->args );
    }


    /**
     * @return list<string> The remaining unprocessed arguments.
     *
     * Returns the remaining unprocessed arguments as an array.
     */
    public function endWithArray() : array {
        $rst = $this->args;
        $this->args = [];
        return $rst;
    }


    /**
     * @return list<string> A list of matching filenames
     * Returns the remaining arguments processed as filename globs with duplicates
     * removed.
     */
    public function endWithGlob( bool $i_bAllowEmpty = false ) : array {
        $rFiles = [];
        while ( $rGlob = $this->shiftGlob( $i_bAllowEmpty ) ) {
            $rFiles = array_merge( $rFiles, $rGlob );
        }
        return array_values( array_unique( $rFiles ) );
    }


    /**
     * @return list<string> A list of matching filenames
     *
     * Returns the remaining arguments processed as filename globs. This
     * method throws an exception if no arguments are available, which
     * is useful when you want to ensure you have at least one matching
     * filename.
     */
    public function endWithGlobEx( bool $i_bAllowEmpty = false ) : array {
        $rFiles = $this->endWithGlob( $i_bAllowEmpty );
        if ( 0 != count( $rFiles ) ) {
            return $rFiles;
        }
        throw new MissingArgumentException( "Missing glob argument" );
    }


    /**
     * Returns the remaining arguments, separated by spaces, as a single string.
     */
    public function endWithString() : ?string {
        if ( $this->empty() ) {
            return null;
        }
        return join( " ", $this->endWithArray() );
    }


    /**
     * Returns the remaining arguments, separated by spaces, as a single string.
     * Requires at least one argument to be present.
     */
    public function endWithStringEx( string $i_stMissing = "Missing argument" ) : string {
        $nst = $this->endWithString();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( $i_stMissing );
    }


    /**
     *
     * @return array<string, mixed> The options that were present with their associated values.
     *
     * Handles options in the form of --key=value or --key. These are extracted
     * out of the argument list and returned as an associative array. The
     * remaining arguments are preserved. The special argument "--" can be used
     * to stop option processing.
     *
     * If an option is specified as --key=value, the value of key is the string "value."
     * If an option is specified as --key, the value of key is true.
     * If an option is specified as --no-key, the value of key is false.
     *
     * If an option is specified more than once, the last value is used.
     */
    public function handleOptions() : array {
        $rOptions = [];
        $rstNewArgs = [];
        $bSkip = false;
        foreach ( $this->args as $stArg ) {
            if ( $bSkip ) {
                $rstNewArgs[] = $stArg;
                continue;
            }
            if ( "--" === $stArg ) {
                $bSkip = true;
                continue;
            }
            if ( str_starts_with( $stArg, "--" ) ) {
                $stArg = substr( $stArg, 2 );
                if ( str_contains( $stArg, "=" ) ) {
                    [ $stKey, $stValue ] = explode( "=", $stArg, 2 );
                    $rOptions[ $stKey ] = $stValue;
                    continue;
                }
                if ( str_starts_with( $stArg, "no-" ) ) {
                    $stArg = substr( $stArg, 3 );
                    $rOptions[ $stArg ] = false;
                    continue;
                }
                $rOptions[ $stArg ] = true;
            } else {
                $rstNewArgs[] = $stArg;
            }
        }
        $this->args = $rstNewArgs;
        return $rOptions;
    }


    /**
     * @param list<string> $i_rstOptions List of valid options.
     * @return array<string, mixed> The options that were defined and
     *                              associated values given.
     *
     * Like handleOptions() but enforces a predefined list of options.
     */
    public function handleOptionsAllowed( array $i_rstOptions ) : array {
        $rOptions = $this->handleOptions();
        foreach ( $rOptions as $stKey => $bstValue ) {
            if ( in_array( $stKey, $i_rstOptions ) ) {
                continue;
            }
            if ( is_bool( $bstValue ) ) {
                throw new BadArgumentException( $bstValue ? "true" : "false", "Unknown option \"{$stKey}\"" );
            }
            throw new BadArgumentException( $bstValue, "Unknown option \"{$stKey}\"" );
        }
        return $rOptions;
    }


    /**
     * @param array<string, mixed> $i_rstOptions The valid options and their
     *                                           default values.
     * @return array<string, mixed> The options that were defined and
     *                              associated values given.
     *
     * Takes an array of options and their default values. E.g.,
     * [ 'happy' => true, 'value' => 42 ]. A default string value is
     * used if the option is specified by itself. E.g., "--value" returns
     * [ 'value' => 42 ], but "--value=99" returns [ 'value' => 99 ].
     * If the default value is false, "--value" returns [ 'value' => true ].
     * Returns an array with the options that were found as keys and
     * the values provided or taken from the defaults as values.
     */
    public function handleOptionsDefined( array $i_rstOptions ) : array {
        $rOptions = $this->handleOptionsAllowed( array_keys( $i_rstOptions ) );
        foreach ( $i_rstOptions as $stKey => $stValue ) {
            if ( array_key_exists( $stKey, $rOptions ) ) {
                if ( $rOptions[ $stKey ] === true && $stValue !== false ) {
                    continue;
                }
                $i_rstOptions[ $stKey ] = $rOptions[ $stKey ];
            }
        }
        return $i_rstOptions;
    }


    /**
     *
     * @param list<string> $i_rKeywords The keywords to look for.
     * @param bool $i_bConsume If true, a matched keyword is removed from the argument list.
     *
     * This is similar to shiftKeywords() but does not treat a mismatch as an error,
     * instead returning null. Unlike peekString(), this method returns the whole match
     * so you can tell which keyword matched.
     */
    public function peekKeywords( array $i_rKeywords, bool $i_bConsume = false ) : ?string {
        if ( 0 == count( $this->args ) ) {
            return null;
        }
        $st = $this->args[ 0 ];
        if ( ! in_array( $st, $i_rKeywords ) ) {
            return null;
        }
        if ( $i_bConsume ) {
            array_shift( $this->args );
        }
        return $st;
    }


    /**
     * Peeks at the next argument.
     *
     * If a prefix is specified, the next argument must start with that prefix
     * for it to be peeked (otherwise null is returned). If a prefix is specified,
     * the prefix is removed from the returned string.
     *
     * If a prefix is specified, the $i_bConsume flag indicates whether it
     * should be removed from the argument list.
     *
     * This is useful for parsing arguments that are optional, but have a specific
     * position in the argument list. For example, something that changes how
     * subsequent arguments will be handled if (and only if) it is present.  If it's
     * present, you want to know that. But if it's not, you don't want to mess up
     * another argument finding that out.
     *
     * It is not valid to use consume without a prefix. Doing so is equivalent to
     * using the shiftString() method.
     *
     * If no arguments remain, or if the next argument doesn't match the prefix,
     * null is returned.
     */
    public function peekString( ?string $i_stPrefix = null, bool $i_bConsume = false ) : ?string {
        if ( ! is_string( $i_stPrefix ) && $i_bConsume ) {
            throw new LogicException( "A prefix is required to consume. Consider using shiftString() instead." );
        }
        if ( count( $this->args ) == 0 ) {
            return null;
        }
        $st = $this->args[ 0 ];
        if ( is_string( $i_stPrefix ) ) {
            if ( ! str_starts_with( $st, $i_stPrefix ) ) {
                return null;
            }
            if ( $i_bConsume ) {
                array_shift( $this->args );
            }
            return substr( $st, strlen( $i_stPrefix ) );
        }
        return $st;
    }


    /**
     * @param string $i_stExpected The string that is expected to be the next
     *                             argument.
     * @param bool $i_bConsume If true, a matching argument is removed from
     *                         the list.
     * @return bool True if the next argument matches/matched the expected
     *              string.
     *
     * This is used to check if the next argument is a specific string. If
     * it is, the argument is (by default) removed from the list. If it is
     * not, the list is unchanged.
     *
     * There are a number of legacy use cases where this is required, but new
     * code should use handleOptions() instead.
     *
     */
    public function peekStringExact( string $i_stExpected, bool $i_bConsume = true ) : bool {
        if ( $this->empty() ) {
            return false;
        }
        if ( $this->args[ 0 ] !== $i_stExpected ) {
            return false;
        }
        if ( $i_bConsume ) {
            array_shift( $this->args );
        }
        return true;
    }


    public function shift() : ?IParameter {
        if ( $this->empty() ) {
            return null;
        }
        return new Parameter( array_shift( $this->args ) );
    }


    public function shiftBoolean() : ?bool {
        $np = $this->shift();
        try {
            return $np?->asBool();
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    public function shiftBooleanEx( ?string $i_nstRequired = null ) : bool {
        $nb = $this->shiftBoolean();
        if ( is_bool( $nb ) ) {
            return $nb;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing boolean argument' );
    }


    public function shiftConstant( string $i_stConstant ) : ?string {
        $np = $this->shift();
        try {
            return $np?->asConstant( $i_stConstant );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    public function shiftConstantEx( string $i_stConstant ) : string {
        $nst = $this->shiftConstant( $i_stConstant );
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( "Missing constant argument '{$i_stConstant}'" );
    }


    public function shiftCurrency() : ?int {
        $np = $this->shift();
        try {
            return $np?->asCurrency();
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    public function shiftCurrencyEx( string $i_nstRequired = null ) : int {
        $ni = $this->shiftCurrency();
        if ( is_int( $ni ) ) {
            return $ni;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing currency argument' );
    }


    public function shiftEmailAddress() : ?string {
        $np = $this->shift();
        try {
            return $np?->asEmailAddress();
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    public function shiftEmailAddressEx( string $i_nstRequired = null ) : string {
        $nst = $this->shiftEmailAddress();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing email address argument' );
    }


    public function shiftEx() : Parameter {
        $np = $this->shift();
        if ( $np instanceof Parameter ) {
            return $np;
        }
        throw new MissingArgumentException( "Missing argument" );
    }


    public function shiftExistingDirectory() : ?string {
        $np = $this->shift();
        try {
            return $np?->asExistingDirectory();
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    public function shiftExistingDirectoryEx( string $i_nstRequired = null ) : string {
        $nst = $this->shiftExistingDirectory();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing existing directory argument' );
    }


    /**
     * Similar to shiftExistingFilename() but returns the contents
     * of the specified file instead of the filename. Optionally stores
     * the filename into the string argument, if one is given.
     */
    public function shiftExistingFileBody( ?string &$o_nstFilename = null ) : ?string {
        $np = $this->shift();
        if ( ! $np instanceof IParameter ) {
            return null;
        }
        return self::parseExistingFileBody( $np->asString(), $o_nstFilename );
    }


    /**
     * Similar to shiftExistingFilenameEx() but returns the contents
     * of the specified file instead of the filename. Optionally stores
     * the filename into the string argument, if one is given.
     */
    public function shiftExistingFileBodyEx( ?string &$o_nstFilename = null, ?string $i_nstRequired = null ) : string {
        $st = $this->shiftExistingFilenameEx( $i_nstRequired );
        return self::parseExistingFileBody( $st, $o_nstFilename );
    }


    /**
     * Expects a string argument that is the name of an existing file.
     */
    public function shiftExistingFilename() : ?string {
        $np = $this->shift();
        try {
            return $np?->asExistingFilename();
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    public function shiftExistingFilenameEx( ?string $i_nstRequired = null ) : string {
        $nst = $this->shiftExistingFilename();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing filename argument' );
    }


    /**
     * Expects a string argument that parses to a floating-point value (optionally,
     * within a specified range).
     *
     * Unlike shiftInteger() the range is half-open (i.e., the minimum is inclusive
     * and the maximum is exclusive).  This is because the interval [0, 1) is a
     * common use case.
     */
    public function shiftFloat( float $i_fMin = -PHP_FLOAT_MAX,
                                float $i_fMax = PHP_FLOAT_MAX ) : ?float {
        $np = $this->shift();
        try {
            return $np?->asFloatRangeHalfClosed( $i_fMin, $i_fMax );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    public function shiftFloatEx( float   $i_fMin = -PHP_FLOAT_MAX,
                                  float   $i_fMax = PHP_FLOAT_MAX,
                                  ?string $i_nstRequired = null ) : float {
        $nf = $this->shiftFloat( $i_fMin, $i_fMax );
        if ( is_float( $nf ) ) {
            return $nf;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing float argument' );
    }


    /**
     * Expects a string argument that is a glob pattern. The glob is expanded
     * and the resulting list of files is returned.
     *
     * @param bool $i_bAllowEmpty If true, an empty glob is allowed.
     * @return list<string>|null The list of files that match the glob, or null if
     *                   no argument is available.
     */
    public function shiftGlob( bool $i_bAllowEmpty = false ) : ?array {
        $np = $this->shift();
        if ( ! $np instanceof IParameter ) {
            return null;
        }
        return self::parseGlob( $np->asString(), $i_bAllowEmpty );
    }


    /**
     * Expects a string argument that is a glob pattern. The glob is expanded
     * and the resulting list of files is returned.
     *
     * @param bool $i_bAllowEmpty If true, an empty glob is allowed.
     * @return list<string> The list of files that match the glob.
     */
    public function shiftGlobEx( bool $i_bAllowEmpty = false, ?string $i_nstRequired = null ) : array {
        $nrFiles = $this->shiftGlob( $i_bAllowEmpty );
        if ( is_array( $nrFiles ) ) {
            return $nrFiles;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing glob argument' );
    }


    public function shiftHostname() : ?string {
        $np = $this->shift();
        try {
            return $np?->asHostname();
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    public function shiftHostnameEx( ?string $i_nstRequired = null ) : string {
        $nst = $this->shiftHostname();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing hostname argument' );
    }


    public function shiftIPAddress() : ?string {
        $np = $this->shift();
        try {
            return $np?->asIP();
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    public function shiftIPAddressEx( ?string $i_nstRequired = null ) : string {
        $nst = $this->shiftIPAddress();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing IP address argument' );
    }


    public function shiftIPv4Address() : ?string {
        $np = $this->shift();
        try {
            return $np?->asIPv4();
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    public function shiftIPv4AddressEx( ?string $i_nstRequired = null ) : string {
        $nst = $this->shiftIPv4Address();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing IPv4 address argument' );
    }


    public function shiftIPv6Address() : ?string {
        $np = $this->shift();
        try {
            return $np?->asIPv6();
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    public function shiftIPv6AddressEx( ?string $i_nstRequired = null ) : string {
        $nst = $this->shiftIPv6Address();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing IPv6 address argument' );
    }


    public function shiftInteger( int $i_iMin = PHP_INT_MIN,
                                  int $i_iMax = PHP_INT_MAX ) : ?int {
        $np = $this->shift();
        try {
            return $np?->asIntRangeOpen( $i_iMin, $i_iMax );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    public function shiftIntegerEx( int     $i_iMin = PHP_INT_MIN,
                                    int     $i_iMax = PHP_INT_MAX,
                                    ?string $i_nstRequired = null ) : int {
        $ni = $this->shiftInteger( $i_iMin, $i_iMax );
        if ( is_int( $ni ) ) {
            return $ni;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing integer argument' );
    }


    /** @param string[] $i_rstKeywords The acceptable values for this parameter. */
    public function shiftKeyword( array $i_rstKeywords ) : ?string {
        $np = $this->shift();
        try {
            return $np?->asKeyword( $i_rstKeywords );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    /** @param string[] $i_rstKeywords The acceptable values for this parameter. */
    public function shiftKeywordEx( array $i_rstKeywords, ?string $i_nstRequired = null ) : string {
        $nst = $this->shiftKeyword( $i_rstKeywords );
        if ( is_string( $nst ) ) {
            return $nst;
        }
        $stKeywords = Parse::summarizeOptions( $i_rstKeywords );
        throw new MissingArgumentException( $i_nstRequired ?? "Missing keyword ({$stKeywords}) argument" );
    }


    /**
     * @param string[] $i_rMap An array of keywords and values.
     * @return string|null The value associated with the keyword, or null if
     *                     the keyword is missing.
     *
     * This is similar to shiftKeyword() but compares to the keys of $i_rMap
     * instead of the values, and returns the value of the matching key.
     * This is useful for accepting a bunch of variants of a keyword, e.g.:
     * [
     *   'yes' => 'yes', 'yeah' => 'yes', 'y' => 'yes',
     *   'no' => 'no', 'nope' => 'no', 'n' => 'no'
     * ]
     */
    public function shiftMap( array $i_rMap ) : ?string {
        $np = $this->shift();
        try {
            return $np?->asMap( $i_rMap );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    /**
     * @param string[] $i_rMap An array of keywords and values.
     * @return string The value associated with the keyword.
     *
     * This is similar to shiftKeyword() but compares to the keys of $i_rMap
     * instead of the values, and returns the value of the matching key.
     * This is useful for accepting a bunch of variants of a keyword, e.g.:
     * [
     *   'yes' => 'yes', 'yeah' => 'yes', 'y' => 'yes',
     *   'no' => 'no', 'nope' => 'no', 'n' => 'no'
     * ]
     */
    public function shiftMapEx( array $i_rMap, ?string $i_nstRequired = null ) : string {
        $nst = $this->shiftMap( $i_rMap );
        if ( is_string( $nst ) ) {
            return $nst;
        }
        $stKeywords = Parse::summarizeOptions( array_keys( $i_rMap ) );
        throw new MissingArgumentException( $i_nstRequired ?? "Missing one of ({$stKeywords}) argument" );
    }


    /**
     * Expects an argument specifying a filename that does not currently exist,
     * but could be created. E.g., any referenced parent directories must exist.
     */
    public function shiftNonexistentFilename() : ?string {
        $np = $this->shift();
        try {
            return $np?->asNonexistentFilename();
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    /**
     * Expects an argument specifying a filename that does not currently exist,
     * but could be created. E.g., any referenced parent directories must exist.
     */
    public function shiftNonexistentFilenameEx( ?string $i_nstRequired = null ) : string {
        $nst = $this->shiftNonexistentFilename();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing file argument' );
    }


    public function shiftPositiveInteger( int $i_iMax = PHP_INT_MAX ) : ?int {
        $np = $this->shift();
        try {
            return $np?->asIntRangeOpen( 1, $i_iMax );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    public function shiftPositiveIntegerEx( int $i_iMax = PHP_INT_MAX, ?string $i_nstRequired = null ) : int {
        $ni = $this->shiftPositiveInteger( $i_iMax );
        if ( is_int( $ni ) ) {
            return $ni;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing positive integer argument' );
    }


    public function shiftString() : ?string {
        $np = $this->shift();
        try {
            return $np?->asString();
        } catch ( TypeError $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    public function shiftStringEx( ?string $i_nstRequired = null ) : string {
        $nst = $this->shiftString();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing argument' );
    }


    public function shiftUnsignedInteger( int $i_iMax = PHP_INT_MAX ) : ?int {
        $np = $this->shift();
        try {
            return $np?->asIntRangeOpen( 0, $i_iMax );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $np, $e );
        }
    }


    public function shiftUnsignedIntegerEx( int $i_iMax = PHP_INT_MAX, ?string $i_nstRequired = null ) : int {
        $ni = $this->shiftUnsignedInteger( $i_iMax );
        if ( is_int( $ni ) ) {
            return $ni;
        }
        throw new MissingArgumentException( $i_nstRequired ?? 'Missing unsigned integer argument' );
    }


}