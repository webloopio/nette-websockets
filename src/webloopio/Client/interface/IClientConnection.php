<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 13.03. 2018
 * Time: 16:05
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\NetteWebsockets;

use Ratchet\ConnectionInterface;

interface IClientConnection extends ConnectionInterface {
    public function getConnection(): ConnectionInterface;
    public function getResourceId(): int;
    public function getUserId(): int;
    public function setUserId( int $userId );
    public function getPresenterName();
    public function setPresenterName( string $presenterName );
    public function getPageId();
    public function setPageId( int $pageId );
}