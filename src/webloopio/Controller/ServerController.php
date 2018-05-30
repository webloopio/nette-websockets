<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 14.03. 2018
 * Time: 13:20
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\NetteWebsockets\Controller;

use Webloopio\NetteWebsockets\Client\ClientCollection;
use Webloopio\NetteWebsockets\Server\Message;


class ServerController extends Controller {

    public function actionTest( $client, Message $data ) {
        $this->sendMessage( $client, [ "action" => "works" ] );
    }

    public function actionResponse( $client, Message $message ) {
        $this->sendResponse( $client, $message, [ "response" => "works" ] );
    }

    /**
     * @timer 1s
     *
     * @param ClientCollection $clients
     */
    /*public function loopTestLoop( ClientCollection $clients ) {
        foreach( $clients->getClients() as $client ) {
            $this->sendMessage( $client, [ "testLoop" => 1 ] );
        }
    }*/

}