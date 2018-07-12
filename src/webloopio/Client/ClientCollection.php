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
use React\EventLoop\LoopInterface;
use Webloopio\Exceptions\ClientException;
use Webloopio\Exceptions\ClientRuntimeException;
use Webloopio\NetteWebsockets\Client\IClientConnection;

/**
 * Class ClientCollection
 * @package AllStars\WebSockets
 */
class ClientCollection implements \Countable {

    const MATCH_CLIENT_BY_CONNECTION = "connection";
    const MATCH_CLIENT_USER_ID = "user_id";
    const MATCH_CLIENT_RESOURCE_ID = "resource_id";

    private $clients = [];

    private $authenticator;

    /**
     * @var null
     */
    private $authenticationType;

    /**
     * ClientCollection constructor.
     *
     * @param null $authenticationType
     * @param IAuthenticator|null $authenticator
     */
    function __construct(
        $authenticationType = null,
        IAuthenticator $authenticator = null
    ) {
        $this->authenticationType = $authenticationType;
        $this->authenticator = $authenticator;
    }

    /**
     * @param ConnectionInterface $connection
     * @param IIdentity|null $identity
     *
     * @return null|IClientConnection
     */
    public function addClient( ConnectionInterface $connection, IIdentity $identity = null ) {
        try {
            // TODO: handling multiple connections with same id
            $client = new Client( $connection, $identity );

            // set authenticator, if exists
            if( $this->authenticationType && $this->authenticator ) {
                $client->setAuthenticator( $this->authenticationType, $this->authenticator );
            }

            $this->clients[] = $client;

            return $client;
        }
        catch( ClientException $e ) {
            // TODO
            return null;
        }
    }

    /**
     * @param $byWhat
     * @param string $flag
     */
    public function removeClient( $byWhat, string $flag = "connection" ) {
        $client = null;

        if( $flag === "connection" ) {
            if( $byWhat instanceof ConnectionInterface ) {
                $client = $this->getClientByConnection( $byWhat );
            }
        }
        else if( $flag === "user_id" ) {
            $userId = (int) $byWhat;
            if( is_int( $userId ) ) {
                $client = $this->getClientByUserId( $userId );
            }
        }
        else if( $flag === "resource_id" ) {
            $resourceId = (int) $byWhat;
            if( is_int( $resourceId ) ) {
                $client = $this->getClientByResourceId( $resourceId );
            }
        }

        if( $client !== null ) {
            $client->getConnection()->close();
            if( ( $key = array_search( $client, $this->clients ) ) !== false ) {
                unset( $this->clients[$key] );
            }
        }
    }

    /**
     * @param ConnectionInterface $connection
     *
     * @return null|IClientConnection
     */
    public function getClientByConnection( ConnectionInterface $connection ) {
        if( $connection instanceof IClientConnection ) {
            $users = array_filter($this->clients, function ( Client $user ) use ( $connection ) {
                return $user === $connection;
            });
        }
        else {
            $users = array_filter($this->clients, function ( Client $user ) use ( $connection ) {
                return $user->getConnection() === $connection;
            });
        }

        return $users ? reset( $users ) : null;
    }

    /**
     * @param mixed $userId
     *
     * @return null|IClientConnection
     */
    public function getClientByUserId( $userId ) {
        $users = array_filter( $this->clients, function( Client $user ) use( $userId ) { return $user->getUserId() == $userId; } );
        return $users ? reset( $users ) : null;
    }

    /**
     * @param int $resourceId
     *
     * @return null|IClientConnection
     */
    public function getClientByResourceId( int $resourceId ) {
        $users = array_filter( $this->clients, function( Client $user ) use( $resourceId ) { return $user->getResourceId() === $resourceId; } );
        return $users ? reset( $users ) : null;
    }

    /**
     * @return Client[]
     */
    public function getClients(): array {
        return $this->clients;
    }

    /**
     * @return int
     */
    public function count() {
        return count( $this->clients );
    }

}