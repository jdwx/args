<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use Countable;
use LogicException;


class Arguments extends ArgumentParser implements Countable {


    public function __construct( protected array $args ) {
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
     * Returns the remaining unprocessed arguments as an array.
     */
    public function endWithArray() : array {
        $rst = $this->args;
        $this->args = [];
        return $rst;
    }


    /**
     * Returns the remaining arguments processed as filename globs.
     */
    public function endWithGlob( bool $i_bAllowEmpty = false ) : array {
        $rFiles = [];
        while ( $rGlob = $this->shiftGlob( $i_bAllowEmpty ) ) {
            $rFiles = array_merge( $rFiles, $rGlob );
        }
        return array_unique( $rFiles );
    }


    /**
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
     * @param array $i_rstOptions The valid options and their default values.
     * @return array The options that were defined and associated values
     *               given.
     *
     * Takes an array of options and their default values. E.g.,
     * [ 'happy' => true, 'value' => 42 ]. The default value is used
     * if the option is specified by itself. E.g., "--value" returns
     * [ 'value' => 42 ], but "--value=99" returns [ 'value' => 99 ].
     * Returns an array with the options that were found as keys and
     * the values provided or taken from the defaults as values.
     */
    public function handleOptionsDefined( array $i_rstOptions ) : array {
        $rOptions = $this->handleOptions();
        foreach ( $rOptions as $stKey => $bstValue ) {
            if ( ! in_array( $stKey, $i_rstOptions ) ) {
                if ( is_bool( $bstValue ) ) {
                    throw new BadArgumentException( $bstValue ? "true" : "false", "Unknown option \"{$stKey}\"" );
                }
                throw new BadArgumentException( $bstValue, "Unknown option \"{$stKey}\"" );
            }
        }
        return $rOptions;
    }


    /**
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
     * There are a number of legacy use cases where this is useful, but new
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


    /** @deprecated Preserve until 1.1.0 */
    public function shiftBool() : ?bool {
        return $this->shiftBoolean();
    }


    /** @deprecated Preserve until 1.1.0 */
    public function shiftBoolEx() : bool {
        return $this->shiftBooleanEx();
    }


    public function shiftBoolean() : ?bool {
        $nst = $this->shiftString();
        if ( $nst === null ) {
            return null;
        }
        return self::parseBool( $nst );
    }


    public function shiftBooleanEx() : bool {
        $nb = $this->shiftBoolean();
        if ( is_bool( $nb ) ) {
            return $nb;
        }
        throw new MissingArgumentException( "Missing boolean argument" );
    }


    public function shiftEmailAddress() : ?string {
        $nst = $this->shiftString();
        if ( ! is_string( $nst ) ) {
            return null;
        }
        return self::parseEmailAddress( $nst );
    }


    public function shiftEmailAddressEx() : string {
        $nst = $this->shiftEmailAddress();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( "Missing email address argument" );
    }


    public function shiftExistingDirectory() : ?string {
        $nst = $this->shiftString();
        if ( ! is_string( $nst ) ) {
            return null;
        }
        return self::parseExistingDirectory( $nst );
    }


    public function shiftExistingDirectoryEx() : string {
        $nst = $this->shiftExistingDirectory();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( "Missing directory argument" );
    }


    /**
     * Similar to shiftExistingFilename() but returns the contents
     * of the specified file instead of the filename. Optionally stores
     * the filename into the string argument, if one is given.
     */
    public function shiftExistingFileBody( ?string &$o_nstFilename = null ) : ?string {
        $nst = $this->shiftExistingFilename();
        if ( ! is_string( $nst ) ) {
            $o_nstFilename = null;
            return null;
        }
        return self::parseExistingFileBody( $nst, $o_nstFilename );
    }


    /**
     * Similar to shiftExistingFilenameEx() but returns the contents
     * of the specified file instead of the filename. Optionally stores
     * the filename into the string argument, if one is given.
     */
    public function shiftExistingFileBodyEx( ?string &$o_nstFilename = null ) : string {
        $st = $this->shiftExistingFilenameEx();
        $o_nstFilename = $st;
        return file_get_contents( $st );
    }


    /**
     * Expects a string argument that is the name of an existing file.
     */
    public function shiftExistingFilename() : ?string {
        $nst = $this->shiftString();
        if ( ! is_string( $nst ) ) {
            return null;
        }
        return self::parseExistingFilename( $nst );
    }


    public function shiftExistingFilenameEx() : string {
        $nst = $this->shiftExistingFilename();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( "Missing filename argument" );
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
        $nst = $this->shiftString();
        if ( $nst === null ) {
            return null;
        }
        return self::parseFloat( $nst, $i_fMin, $i_fMax );
    }


    public function shiftFloatEx( float $i_fMin = -PHP_FLOAT_MAX,
                                  float $i_fMax = PHP_FLOAT_MAX ) : float {
        $nf = $this->shiftFloat( $i_fMin, $i_fMax );
        if ( is_float( $nf ) ) {
            return $nf;
        }
        throw new MissingArgumentException( "Missing float argument" );
    }


    /**
     * Expects a string argument that is a glob pattern. The glob is expanded
     * and the resulting list of files is returned.
     *
     * @param bool $i_bAllowEmpty If true, an empty glob is allowed.
     * @return array|null The list of files that match the glob, or null if
     *                   no argument is available.
     */
    public function shiftGlob( bool $i_bAllowEmpty = false ) : ?array {
        $nst = $this->shiftString();
        if ( $nst === null ) {
            return null;
        }
        return self::parseGlob( $nst, $i_bAllowEmpty );
    }


    /**
     * Expects a string argument that is a glob pattern. The glob is expanded
     * and the resulting list of files is returned.
     *
     * @param bool $i_bAllowEmpty If true, an empty glob is allowed.
     * @return array The list of files that match the glob.
     */
    public function shiftGlobEx( bool $i_bAllowEmpty = false ) : array {
        $nrFiles = $this->shiftGlob( $i_bAllowEmpty );
        if ( is_array( $nrFiles ) ) {
            return $nrFiles;
        }
        throw new MissingArgumentException( "Missing glob argument" );
    }


    public function shiftHostname() : ?string {
        $nst = $this->shiftString();
        if ( ! is_string( $nst ) ) {
            return null;
        }
        return self::parseHostname( $nst );
    }


    public function shiftHostnameEx() : string {
        $nst = $this->shiftHostname();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( "Missing hostname argument" );
    }


    public function shiftInteger( int $i_iMin = PHP_INT_MIN,
                                  int $i_iMax = PHP_INT_MAX ) : ?int {
        $nst = $this->shiftString();
        if ( $nst === null ) {
            return null;
        }
        return self::parseInteger( $nst, $i_iMin, $i_iMax );
    }


    public function shiftIntegerEx( int $i_iMin = PHP_INT_MIN,
                                    int $i_iMax = PHP_INT_MAX ) : int {
        $ni = $this->shiftInteger( $i_iMin, $i_iMax );
        if ( is_int( $ni ) ) {
            return $ni;
        }
        throw new MissingArgumentException( "Missing integer argument" );
    }


    public function shiftIPAddress() : ?string {
        $nst = $this->shiftString();
        if ( ! is_string( $nst ) ) {
            return null;
        }
        return self::parseIPAddress( $nst );
    }


    public function shiftIPAddressEx() : string {
        $nst = $this->shiftIPAddress();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( "Missing IP address argument" );
    }


    public function shiftIPv4Address() : ?string {
        $nst = $this->shiftString();
        if ( ! is_string( $nst ) ) {
            return null;
        }
        return self::parseIPv4Address( $nst );
    }


    public function shiftIPv4AddressEx() : string {
        $nst = $this->shiftIPv4Address();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( "Missing IPv4 address argument" );
    }


    public function shiftIPv6Address() : ?string {
        $nst = $this->shiftString();
        if ( ! is_string( $nst ) ) {
            return null;
        }
        return self::parseIPv6Address( $nst );
    }


    public function shiftIPv6AddressEx() : string {
        $nst = $this->shiftIPv6Address();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( "Missing IPv6 address argument" );
    }


    /** @param string[] $i_rstKeywords The acceptable values for this parameter. */
    public function shiftKeyword( array $i_rstKeywords ) : ?string {
        $nst = $this->shiftString();
        if ( $nst === null ) {
            return null;
        }
        return self::parseKeywords( $nst, $i_rstKeywords );
    }


    /** @param string[] $i_rstKeywords The acceptable values for this parameter. */
    public function shiftKeywordEx( array $i_rstKeywords ) : string {
        $nst = $this->shiftKeyword( $i_rstKeywords );
        if ( is_string( $nst ) ) {
            return $nst;
        }
        $stKeywords = self::summarizeKeywords( $i_rstKeywords );
        throw new MissingArgumentException( "Missing keyword ({$stKeywords}) argument" );
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
        $nst = $this->shiftString();
        if ( $nst === null ) {
            return null;
        }
        return self::parseMap( $nst, $i_rMap );
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
    public function shiftMapEx( array $i_rMap ) : string {
        $nst = $this->shiftMap( $i_rMap );
        if ( is_string( $nst ) ) {
            return $nst;
        }
        $stKeywords = self::summarizeKeywords( array_keys( $i_rMap ) );
        throw new MissingArgumentException( "Missing one of ({$stKeywords}) argument" );
    }


    /**
     * Expects an argument specifying a filename that does not currently exist,
     * but could be created. E.g., any referenced parent directories must exist.
     */
    public function shiftNonexistentFilename() : ?string {
        $nst = $this->shiftString();
        if ( ! is_string( $nst ) ) {
            return null;
        }
        return self::parseNonexistentFilename( $nst );
    }


    /**
     * Expects an argument specifying a filename that does not currently exist,
     * but could be created. E.g., any referenced parent directories must exist.
     */
    public function shiftNonexistentFilenameEx() : string {
        $nst = $this->shiftString();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( "Missing file argument" );
    }


    public function shiftPositiveInteger( int $i_iMax = PHP_INT_MAX ) : ?int {
        $nst = $this->shiftString();
        if ( $nst === null ) {
            return null;
        }
        return self::parsePositiveInteger( $nst, $i_iMax );
    }


    public function shiftPositiveIntegerEx( int $i_iMax = PHP_INT_MAX ) : int {
        $ni = $this->shiftPositiveInteger( $i_iMax );
        if ( is_int( $ni ) ) {
            return $ni;
        }
        throw new MissingArgumentException( "Missing positive integer argument" );
    }


    public function shiftString() : ?string {
        if ( count( $this->args ) == 0 ) {
            return null;
        }
        return array_shift( $this->args );
    }


    public function shiftStringEx() : string {
        $nst = $this->shiftString();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( "Missing argument" );
    }


    public function shiftUnsignedInteger( int $i_iMax = PHP_INT_MAX ) : ?int {
        $nst = $this->shiftString();
        if ( $nst === null ) {
            return null;
        }
        return self::parseUnsignedInteger( $nst, $i_iMax );
    }


    public function shiftUnsignedIntegerEx( int $i_iMax = PHP_INT_MAX ) : int {
        $ni = $this->shiftUnsignedInteger( $i_iMax );
        if ( is_int( $ni ) ) {
            return $ni;
        }
        throw new MissingArgumentException( "Missing unsigned integer argument" );
    }


    public static function fromString( string $i_st ) : static {
        $parsed = StringParser::parseString( $i_st );
        return new static( $parsed->getSegments() );
    }


}