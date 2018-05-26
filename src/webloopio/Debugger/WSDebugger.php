<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 14.03. 2018
 * Time: 15:52
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\NetteWebsockets\Debugger;

use Tracy\Dumper;


class WSDebugger {

    public static $debugOn = true;

    public static $terminalBgColors = [
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_gray' => '47',
    ];

    public static $terminalFgColors = [
        'black' => '0;30',
        'dark_gray' => '1;30',
        'blue' => '0;34',
        'light_blue' => '1;34',
        'green' => '0;32',
        'light_green' => '1;32',
        'cyan' => '0;36',
        'light_cyan' => '1;36',
        'red' => '0;31',
        'light_red' => '1;31',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'light_gray' => '0;37',
        'white' => '1;37',
    ];

    public static function dump(
        $var,
        string $name = null,
        string $foregroundColor = null,
        string $backgroundColor = null
    ) {
        if( !self::$debugOn ) {
            return;
        }

        $colors = [];
        if( $foregroundColor ) {
            $colors[0] = $foregroundColor;
        }
        if( $backgroundColor ) {
            $colors[1] = $backgroundColor;
        }

        if( $name ) {
            echo $name . ": \n";
        }
        echo self::toTerminal( $var, null, $colors );
    }

    public static function toTerminal( $var, array $options = null, array $colors = [] ) {
        return htmlspecialchars_decode( strip_tags( preg_replace_callback( '#<span class="tracy-dump-(\w+)">|</span>#', function( $m ) use( $colors ) {
            $dumpColor = isset( $m[1], Dumper::$terminalColors[ $m[1] ]) ? Dumper::$terminalColors[ $m[1] ] : '0';
            $colored = '';
            $foregroundColor = isset( $colors[0], self::$terminalFgColors[ $colors[0] ] ) ? self::$terminalFgColors[$colors[0]] : $dumpColor;
            $colored .= "\033[" . $foregroundColor . 'm';
            if( isset( $colors[1], self::$terminalBgColors[ $colors[1] ] ) ) {
                $colored .= "\033[" . self::$terminalBgColors[ $colors[1] ] . 'm';
            }
            return $colored;
        }, Dumper::toHtml($var, $options))), ENT_QUOTES);
    }

}