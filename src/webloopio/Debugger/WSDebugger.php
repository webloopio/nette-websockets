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

    public static function dump() {
        if( !self::$debugOn ) {
            return;
        }

        $args = func_get_args();

        foreach( $args as $var ) {
            echo self::toTerminal( $var );
        }

        echo  "--------------------------\n";
    }

    public static function toTerminal( $var, array $options = null ) {
        return htmlspecialchars_decode(strip_tags(preg_replace_callback('#<span class="tracy-dump-(\w+)">|</span>#', function ($m) {
            return "\033[" . (isset($m[1], Dumper::$terminalColors[$m[1]]) ? Dumper::$terminalColors[$m[1]] : '0') . 'm';
        }, Dumper::toHtml($var, $options))), ENT_QUOTES);
    }

}