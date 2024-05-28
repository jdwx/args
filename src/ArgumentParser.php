<?php


declare( strict_types = 1 );


namespace JDWX\Args;


class ArgumentParser {


    public static function parseBool( string $i_st ) : bool {
        switch ( trim( strtolower( $i_st ) ) ) {
            case 'true':
            case 'yes':
            case 'yeah':
            case 'y':
            case 'on':
            case '1':
                return true;
            case 'false':
            case 'no':
            case 'nope':
            case 'n':
            case 'off':
            case '0':
                return false;
        }
        throw new BadArgumentException( $i_st, "Invalid boolean value" );
    }


    public static function parseEmailAddress( string $i_st ) : string {
        if ( ! filter_var( $i_st, FILTER_VALIDATE_EMAIL ) ) {
            throw new BadArgumentException( $i_st, "Invalid email address" );
        }
        return $i_st;
    }


    public static function parseExistingDirectory( string $i_st ) : string {
        $st = self::parseExistingFilename( $i_st );
        if ( ! is_dir( $st ) ) {
            throw new BadArgumentException( $i_st, "Not a directory" );
        }
        return $st;
    }


    public static function parseExistingFileBody( string $i_st, ?string & $o_stFilename ) : string {
        $st = self::parseExistingFilename( $i_st );
        $o_stFilename = $st;
        return file_get_contents( $st );
    }


    public static function parseExistingFilename( string $i_st ) : string {
        if ( ! file_exists( $i_st ) ) {
            throw new BadArgumentException( $i_st, "Filename does not exist" );
        }
        return $i_st;
    }


    public static function parseFloat( string $i_st, float $i_fMin = -PHP_FLOAT_MAX,
                                       float $i_fMax = PHP_FLOAT_MAX ) : float {
        if ( ! is_numeric( $i_st ) ) {
            throw new BadArgumentException( $i_st, "Invalid floating-point number" );
        }
        $f = floatval( $i_st );
        if ( $f < $i_fMin || $f >= $i_fMax ) {
            throw new BadArgumentException( $i_st, "Float outside of range [{$i_fMin}, {$i_fMax})" );
        }
        return $f;
    }


    public static function parseHostname( string $i_st ) : string {
        if ( str_ends_with( $i_st, '.' )
            || !str_contains( $i_st, '.' )
            || ! filter_var( $i_st, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME ) ) {
            throw new BadArgumentException( $i_st, "Invalid hostname" );
        }
        return $i_st;
    }


    public static function parseGlob( string $i_st, bool $i_bAllowEmpty = false ) : array {
        $r = glob( $i_st );
        if ( false === $r || ( ! $i_bAllowEmpty && 0 === count( $r ) ) ) {
            throw new BadArgumentException( $i_st, "Invalid glob pattern" );
        }
        return $r;
    }


    public static function parseInteger( string $i_st, int $i_iMin = PHP_INT_MIN, int $i_iMax = PHP_INT_MAX ) : int {
        if ( ! is_numeric( $i_st ) ) {
            throw new BadArgumentException( $i_st, "Invalid integer" );
        }
        $i = intval( $i_st );
        if ( $i < $i_iMin || $i > $i_iMax ) {
            throw new BadArgumentException( $i_st, "Integer outside of range [{$i_iMin}, {$i_iMax})" );
        }
        return $i;
    }


    public static function parseIPAddress( string $i_st ) : string {
        if ( ! filter_var( $i_st, FILTER_VALIDATE_IP ) ) {
            throw new BadArgumentException( $i_st, "Invalid IP address" );
        }
        return $i_st;
    }


    public static function parseIPv4Address( string $i_st ) : string {
        if ( ! filter_var( $i_st, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
            throw new BadArgumentException( $i_st, "Invalid IPv4 address" );
        }
        return $i_st;
    }


    public static function parseIPv6Address( string $i_st ) : string {
        if ( ! filter_var( $i_st, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
            throw new BadArgumentException( $i_st, "Invalid IPv6 address" );
        }
        return $i_st;
    }


    /** @param string[] $i_rstKeywords */
    public static function parseKeywords( string $i_st, array $i_rstKeywords ) : string {
        if ( ! in_array( $i_st, $i_rstKeywords ) ) {
            $stKeywords = self::summarizeKeywords( $i_rstKeywords );
            throw new BadArgumentException( $i_st, "Expected keyword ({$stKeywords})" );
        }
        return $i_st;
    }


    /** @param string[] $i_rMap */
    public static function parseMap( string $i_st, array $i_rMap ) : string {
        if ( ! array_key_exists( $i_st, $i_rMap ) ) {
            $stKeywords = self::summarizeKeywords( array_keys( $i_rMap ) );
            throw new BadArgumentException( $i_st, "Expected one of ({$stKeywords})" );
        }
        return $i_rMap[ $i_st ];
    }


    public static function parseNonexistentFilename( string $i_st ) : string {
        if ( file_exists( $i_st ) ) {
            throw new BadArgumentException( $i_st, "File exists" );
        }
        $stDir = dirname( $i_st );
        if ( $stDir && ! is_dir( $stDir ) ) {
            throw new BadArgumentException( $i_st, "Directory does not exist" );
        }
        return $i_st;
    }


    public static function parsePositiveInteger( string $i_st, int $i_iMax ) : int {
        return self::parseInteger( $i_st, 1, $i_iMax );
    }


    public static function parseUnsignedInteger( string $i_st, int $i_iMax ) : int {
        return self::parseInteger( $i_st, 0, $i_iMax );
    }


    public static function summarizeKeywords( array $i_rKeywords ) : string {
        if ( count( $i_rKeywords ) > 5 ) {
            return join( ", ", array_slice( $i_rKeywords, 0, 4 ) ) . ", ...";
        }
        return join( ", ", $i_rKeywords );
    }


}