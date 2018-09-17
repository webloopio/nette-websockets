<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 09.03. 2018
 * Time: 18:00
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\NetteWebsockets\Client;

use Nette\Security\IIdentity;
use Ratchet\ConnectionInterface;


class ClientFactory implements IClientFactory {

    public function create(
        ConnectionInterface $connection,
        IIdentity $identity = null
    ): IClientConnection {
        return new Client( $connection, $identity );
    }
}