<?php


declare( strict_types = 1 );


namespace JDWX\Args;


class StringParser {


    /**
     * @param string $i_st The string to parse for a quoted string, with the starting quote
     *                     character already removed
     * @param string $i_stQuoteCharacter The character ends the quoted string
     * @return array|string Return a text error as a string or an array
     *                      containing [ quoted-text, everything-after ]
     */
    protected static function parseQuote( string $i_st, string $i_stQuoteCharacter ) : array|string {
        $stOut = "";
        $stRest = $i_st;
        while ( true ) {
            $iSpan = strpos( $stRest, $i_stQuoteCharacter );
            if ( false === $iSpan ) {
                return "Unmatched {$i_stQuoteCharacter}.";
            }
            if ( $iSpan > 0 && substr( $stRest, $iSpan - 1, 1 ) === "\\" ) {
                $stOut .= substr( $stRest, 0, $iSpan - 1 ) . $i_stQuoteCharacter;
                $stRest = substr( $stRest, $iSpan + 1 );
                continue;
            }
            $stOut .= substr( $stRest, 0, $iSpan );
            $stRest = substr( $stRest, $iSpan + 1 );
            return [ $stOut, $stRest ];
        }
    }


    /**
     * Parse a string into one or more arguments
     */
    public static function parseString( string $i_stLine, string $i_class = ParsedString::class ) : ParsedString|string {
        $st = trim( preg_replace( "/\s\s+/", " ", $i_stLine ) );
        $pln = new $i_class();
        assert( $pln instanceof ParsedString );
        while ( $st !== "" ) {
            $iSpan = strcspn( $st, " \\\"'#`" );
            $stUnquoted = substr( $st, 0, $iSpan );
            $pln->addUnquoted( $stUnquoted );
            $ch = substr( $st, $iSpan, 1 );
            $stRest = substr( $st, $iSpan + 1 );
            if ( "" === $ch ) {
                # Everything remaining was unquoted.
                return $pln;
            } elseif ( ' ' === $ch ) {
                $pln->addSpace();
            } elseif ( '"' == $ch ) {
                $r = self::parseQuote( $stRest, '"' );
                if ( is_string( $r ) ) {
                    return $r;
                }
                $pln->addDoubleQuoted( $r[ 0 ] );
                $stRest = $r[ 1 ];
            } elseif ( "'" == $ch ) {
                $r = self::parseQuote( $stRest, '\'' );
                if ( is_string( $r ) ) {
                    return $r;
                }
                $pln->addSingleQuoted( $r[ 0 ] );
                $stRest = $r[ 1 ];
            } elseif ( "`" === $ch ) {
                $r = self::parseQuote( $stRest, '`' );
                if ( is_string( $r ) ) {
                    return $r;
                }
                $pln->addBackQuoted( $r[ 0 ] );
                $stRest = $r[ 1 ];
            } elseif ( "#" === $ch ) {
                $pln->addComment( $stRest );
                break;
            } elseif ( "\\" === $ch ) {
                if ( "" === $stRest ) {
                    return "Hanging backslash.";
                }
                if ( preg_match( '/[uU][0-9a-fA-F]{4}/', $stRest ) ) {
                    $stNext = substr( $stRest, 0, 5 );
                    $stRest = substr( $stRest, 5 );
                } elseif ( preg_match( '/[0-7]{1,3}/', $stRest ) ) {
                    $stNext = substr( $stRest, 0, 3 );
                    $stRest = substr( $stRest, 3 );
                } else {
                    $stNext = substr( $stRest, 0, 1 );
                    $stRest = substr( $stRest, 1 );
                }
                $pln->addUnquoted( '\\' . $stNext );
            }
            $st = $stRest;
        }
        return $pln;
    }


}
