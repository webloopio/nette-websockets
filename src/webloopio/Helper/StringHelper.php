<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 13.03. 2018
 * Time: 13:37
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\NetteWebsockets\Helper;


class StringHelper {

    /**
     * @param string $str
     *
     * @return string
     */
    public static function toUnderScore( string $str ) {
        return lcfirst(preg_replace_callback( '#(?<=.)([A-Z])#', function ( $m ) {
            return '_' . strtolower( $m[1] );
        }, $str ) );
    }

    /**
     * @param string $str
     *
     * @return mixed
     */
    public static function toCamelCase( string $str ) {
        return preg_replace_callback( '#_(.)#', function( $m ) {
            return strtoupper( $m[1] );
        }, $str );
    }

    /**
     * @param string $class
     *
     * @return mixed
     */
    public static function trimNamespace( string $class ) {
        $class = explode( '\\', $class );
        return end( $class );
    }

    /**
     * @param string $name
     *
     * @return mixed|string
     */
    public static function unify( string $name ) {
        $name = self::trimNamespace( $name );
        $name = self::toCamelCase( $name );
        $name = lcfirst( $name );

        return $name;
    }

}