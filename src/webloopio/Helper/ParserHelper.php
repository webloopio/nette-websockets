<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 16.03. 2018
 * Time: 22:10
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\NetteWebsockets\Helper;


class ParserHelper {

    /**
     * Parsing phpdoc annotations to associative array
     *
     * @param string $docComment
     *
     * @return array|null - outputs something like [ @annotation => 'value' ]
     */
    public static function simpleAnnotationParser( string $docComment ) {
        preg_match_all( '/@(\w+)\s+(.*)\r?\n/m', $docComment, $matches );

        if( empty( $matches[1] ) || empty( $matches[2] ) ) {
            return null;
        }

        $output = [];

        for( $i = 0; $i < count( $matches[1] ); $i++ ) {
            $output[ $matches[1][$i] ] = $matches[2][$i];
        }

        return $output;
    }

}