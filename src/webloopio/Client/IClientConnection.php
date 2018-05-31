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

namespace Webloopio\NetteWebsockets\Client;

use Ratchet\ConnectionInterface;

interface IClientConnection extends ConnectionInterface {
    public function getConnection(): ConnectionInterface;
    public function getResourceId(): int;
    public function getUserId();
    public function getUserRoles(): array;
    public function getUserIdentityData(): array;
    public function getPresenterName();
    public function setPresenterName( string $presenterName );
    public function getPageId();
    public function setPageId( int $pageId );
    public function isLoggedIn(): bool;
    public function verifyToken( string $token );
    public function logout();
    public function login( $login, $password );

}