<?php


declare( strict_types = 1 );


use JDWX\Args\StringParser;


class MyStringParser extends StringParser {


    /**
     * @return list<string>|string
     * Leaks a protected function for testing purposes.
     */
    public static function myParseQuote( string $i_st, string $i_stQuoteCharacter ) : array|string {
        return parent::parseQuote( $i_st, $i_stQuoteCharacter );
    }


}
