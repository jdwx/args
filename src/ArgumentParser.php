<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use JDWX\Param\Parse;
use JDWX\Param\ParseException;


class ArgumentParser {


    /** @deprecated Retain until 1.2.0. Migrate to Parse in jdwx_param. */
    public static function parseBoolean( string $i_st ) : bool {
        try {
            return Parse::bool( $i_st );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


    /** @deprecated Retain until 1.2.0. Migrate to Parse in jdwx_param. */
    public static function parseEmailAddress( string $i_st ) : string {
        try {
            return Parse::emailAddress( $i_st );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


    public static function parseExistingDirectory( string $i_st ) : string {
        try {
            return Parse::existingDirectory( $i_st );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


    /**
     * @noinspection PhpUsageOfSilenceOperatorInspection
     * @phpstan-ignore parameterByRef.unusedType
     */
    public static function parseExistingFileBody( string $i_st, ?string &$o_stFilename ) : string {
        $o_stFilename = static::parseExistingFilename( $i_st );
        $x = @file_get_contents( $o_stFilename );
        if ( is_string( $x ) ) {
            return $x;
        }
        throw new BadArgumentException( $i_st, "Cannot read file: {$o_stFilename}" );
    }


    public static function parseExistingFilename( string $i_st ) : string {
        try {
            return Parse::existingFilename( $i_st );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


    /** @deprecated Retain until 1.2.0. Migrate to Parse in jdwx_param. */
    public static function parseFloat( string $i_st, float $i_fMin = -PHP_FLOAT_MAX,
                                       float  $i_fMax = PHP_FLOAT_MAX ) : float {
        if ( ! is_numeric( $i_st ) ) {
            throw new BadArgumentException( $i_st, "Invalid integer" );
        }
        try {
            return Parse::floatRangeHalfClosed( $i_st, $i_fMin, $i_fMax );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


    /** @return list<string> A list of filenames matching the glob. */
    public static function parseGlob( string $i_st, bool $i_bAllowEmpty = false ) : array {
        try {
            return Parse::glob( $i_st, i_bAllowEmpty: $i_bAllowEmpty );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


    /** @deprecated Retain until 1.2.0. Migrate to Parse in jdwx_param. */
    public static function parseHostname( string $i_st ) : string {
        try {
            return Parse::hostname( $i_st );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


    /** @deprecated Retain until 1.2.0. Migrate to Parse in jdwx_param. */
    public static function parseIPAddress( string $i_st ) : string {
        try {
            return Parse::ip( $i_st );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


    /** @deprecated Retain until 1.2.0. Migrate to Parse in jdwx_param. */
    public static function parseIPv4Address( string $i_st ) : string {
        try {
            return Parse::ipv4( $i_st );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


    /** @deprecated Retain until 1.2.0. Migrate to Parse in jdwx_param. */
    public static function parseIPv6Address( string $i_st ) : string {
        try {
            return Parse::ipv6( $i_st );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


    /** @deprecated Retain until 1.2.0. Migrate to Parse in jdwx_param. */
    public static function parseInteger( string $i_st, int $i_iMin = PHP_INT_MIN, int $i_iMax = PHP_INT_MAX ) : int {
        if ( ! is_numeric( $i_st ) ) {
            throw new BadArgumentException( $i_st, "Invalid integer" );
        }
        try {
            return Parse::intRangeOpen( $i_st, $i_iMin, $i_iMax );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


    /**
     * @param string[] $i_rstKeywords
     * @deprecated Retain until 1.2.0. Migrate to Parse in jdwx_param.
     */
    public static function parseKeywords( string $i_st, array $i_rstKeywords ) : string {
        $stKeywords = Parse::summarizeOptions( $i_rstKeywords );
        $stError = "Expected keyword ({$stKeywords}): {$i_st}";
        try {
            return Parse::arrayValue( $i_st, $i_rstKeywords, $stError );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


    /**
     * @param string[] $i_rMap
     * @deprecated Retain until 1.2.0. Migrate to Parse in jdwx_param.
     */
    public static function parseMap( string $i_st, array $i_rMap ) : string {
        try {
            return Parse::arrayMap( $i_st, $i_rMap );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


    /** @deprecated Retain until 1.2.0. Migrate to Parse in jdwx_param. */
    public static function parseNonexistentFilename( string $i_st ) : string {
        try {
            return Parse::nonexistentFilename( $i_st );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


    /** @deprecated Retain until 1.2.0. Migrate to Parse in jdwx_param. */
    public static function parsePositiveInteger( string $i_st, int $i_iMax ) : int {
        try {
            return Parse::intRangeOpen( $i_st, 1, $i_iMax );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


    /** @deprecated Retain until 1.2.0. Migrate to Parse in jdwx_param. */
    public static function parseUnsignedInteger( string $i_st, int $i_iMax ) : int {
        try {
            return Parse::intRangeOpen( $i_st, 0, $i_iMax );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


}