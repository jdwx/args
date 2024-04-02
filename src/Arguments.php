<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use Countable;
use LogicException;


class Arguments implements Countable {


    public function __construct( protected array $args ) {
    }


    public function count() : int {
        return count( $this->args );
    }


    public function empty() : bool {
        return empty( $this->args );
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
     * Returns the remaining arguments, separated by spaces, as a single string.
     */
    public function endWithString() : string {
        return join( " ", $this->endWithArray() );
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
     * This is similar to shiftKeywords() but does not treat a mismatch as an error,
     * instead returning null.
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


    public function shiftBool() : ?bool {
        $nst = $this->shiftString();
        if ( $nst === null ) {
            return null;
        }
        switch ( $nst ) {
            case 'true':
            case 'yes':
            case 'on':
            case '1':
                return true;
            case "false":
            case 'no':
            case 'off':
            case '0':
                return false;
        }
        throw new BadArgumentException( $nst, "Invalid boolean value" );
    }


    public function shiftBoolEx() : bool {
        $nb = $this->shiftBool();
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
        if ( ! filter_var( $nst, FILTER_VALIDATE_EMAIL ) ) {
            throw new BadArgumentException( $nst, "Invalid email address" );
        }
        return $nst;
    }


    public function shiftEmailAddressEx() : string {
        $nst = $this->shiftEmailAddress();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( "Missing email address argument" );
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
        $o_nstFilename = $nst;
        return file_get_contents( $nst );
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
        if ( ! file_exists( $nst ) ) {
            throw new BadArgumentException( $nst, "Filename does not exist" );
        }
        return $nst;
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
    public function shiftFloat( float $i_fMin = PHP_FLOAT_MIN,
                                float $i_fMax = PHP_FLOAT_MAX ) : ?float {
        $nst = $this->shiftString();
        if ( $nst === null ) {
            return null;
        }
        if ( ! is_numeric( $nst ) ) {
            throw new BadArgumentException( $nst, "Invalid floating-point number" );
        }
        $f = floatval( $nst );
        if ( $f < $i_fMin || $f >= $i_fMax ) {
            throw new BadArgumentException( $nst, "Float outside of range [{$i_fMin}, {$i_fMax})" );
        }
        return $f;
    }


    public function shiftFloatEx( float $i_fMin = PHP_FLOAT_MIN,
                                  float $i_fMax = PHP_FLOAT_MAX ) : float {
        $nf = $this->shiftFloat( $i_fMin, $i_fMax );
        if ( is_float( $nf ) ) {
            return $nf;
        }
        throw new MissingArgumentException( "Missing float argument" );
    }


    public function shiftHostname() : ?string {
        $nst = $this->shiftString();
        if ( ! is_string( $nst ) ) {
            return null;
        }
        if ( str_ends_with( $nst, '.' )
                || !str_contains( $nst, '.' )
                || ! filter_var( $nst, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME ) ) {
            throw new BadArgumentException( $nst, "Invalid hostname" );
        }
        return $nst;
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
        if ( ! is_numeric( $nst ) ) {
            throw new BadArgumentException( $nst, "Expected integer" );
        }
        $i = intval( $nst );
        if ( $i < $i_iMin || $i > $i_iMax ) {
            throw new BadArgumentException( $nst, "Integer out of range: [{$i_iMin}, {$i_iMax}]" );
        }
        return $i;
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
        if ( ! filter_var( $nst, FILTER_VALIDATE_IP ) ) {
            throw new BadArgumentException( $nst, "Invalid IP address" );
        }
        return $nst;
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
        if ( ! filter_var( $nst, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
            throw new BadArgumentException( $nst, "Invalid IPv4 address" );
        }
        return $nst;
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
        if ( ! filter_var( $nst, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
            throw new BadArgumentException( $nst,"Invalid IPv6 address" );
        }
        return $nst;
    }


    public function shiftIPv6AddressEx() : string {
        $nst = $this->shiftIPv6Address();
        if ( is_string( $nst ) ) {
            return $nst;
        }
        throw new MissingArgumentException( "Missing IPv6 address argument" );
    }


    public function shiftKeyword( array $i_rstKeywords ) : ?string {
        $nst = $this->shiftString();
        if ( $nst === null ) {
            return null;
        }
        if ( in_array( $nst, $i_rstKeywords ) ) {
            return $nst;
        }
        $stKeywords = self::summarizeKeywords( $i_rstKeywords );
        throw new BadArgumentException( $nst, "Expected keyword ({$stKeywords})" );
    }


    public function shiftKeywordEx( array $i_rstKeywords ) : string {
        $nst = $this->shiftKeyword( $i_rstKeywords );
        if ( is_string( $nst ) ) {
            return $nst;
        }
        $stKeywords = self::summarizeKeywords( $i_rstKeywords );
        throw new MissingArgumentException( "Missing keyword ({$stKeywords}) argument" );
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
        if ( file_exists( $nst ) ) {
            throw new BadArgumentException( $nst, "File exists" );
        }
        $stDir = dirname( $nst );
        if ( $stDir && ! is_dir( $stDir ) ) {
            throw new BadArgumentException( $nst, "Directory does not exist" );
        }
        return $nst;
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
        return $this->shiftInteger( 1, $i_iMax );
    }


    public function shiftPositiveIntegerEx( int $i_iMax = PHP_INT_MAX ) : int {
        return $this->shiftIntegerEx( 1, $i_iMax );
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
        return $this->shiftInteger( 0, $i_iMax );
    }


    public function shiftUnsignedIntegerEx( int $i_iMax = PHP_INT_MAX ) : int {
        return $this->shiftIntegerEx( 0, $i_iMax );
    }


    public static function summarizeKeywords( array $i_rKeywords ) : string {
        if ( count( $i_rKeywords ) > 5 ) {
            return join( ", ", array_slice( $i_rKeywords, 0, 4 ) ) . ", ...";
        }
        return join( ", ", $i_rKeywords );
    }


}