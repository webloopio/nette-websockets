<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 14.03. 2018
 * Time: 15:55
 * License: MIT
 * Since: 0.1
 */


if ( !function_exists( 'wsdump' ) ) {
    /**
     * Webloopio\NetteWebsockets\Debugger\WSDebugger::dump() shortcut.
     * @tracySkipLocation
     *
     * @param $var
     * @param string|null $name
     * @param string|null $foregroundColor
     */
    function wsdump(
        $var,
        string $name = null,
        string $foregroundColor = null
    ) {
        call_user_func_array( 'Webloopio\NetteWebsockets\Debugger\WSDebugger::dump', func_get_args() );
    }
}
