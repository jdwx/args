<?php


declare( strict_types = 1 );


use JDWX\Args\StringParser;


class MyStringParser extends StringParser {


    public static function myParseQuote( string $i_st, string $i_stQuoteCharacter ) : array|string {
        return parent::parseQuote( $i_st, $i_stQuoteCharacter );
    }


}
