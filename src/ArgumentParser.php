<?php


declare( strict_types = 1 );


namespace JDWX\Args;


use JDWX\Args\Exceptions\BadArgumentException;
use JDWX\Param\Parse;
use JDWX\Param\ParseException;
use JDWX\Strict\OK;


class ArgumentParser {


    public static function parseExistingFileBody( string $i_st, ?string &$o_stFilename ) : string {
        $o_stFilename = Parse::existingFilename( $i_st );
        try {
            return OK::file_get_contents( $o_stFilename );
        } catch ( \Exception ) {

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


    /** @return list<string> A list of filenames matching the glob. */
    public static function parseGlob( string $i_st, bool $i_bAllowEmpty = false ) : array {
        try {
            return Parse::glob( $i_st, i_bAllowEmpty: $i_bAllowEmpty );
        } catch ( ParseException $e ) {
            throw new BadArgumentException( $i_st, $e->getMessage(), $e->getCode(), $e );
        }
    }


}