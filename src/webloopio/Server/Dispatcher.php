<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 09.03. 2018
 * Time: 18:01
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\NetteWebsockets\Server;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Tracy\Debugger;
use Webloopio\Exceptions\ControllerException;
use Webloopio\Exceptions\ControllerNonExistingMethodException;
use Webloopio\Exceptions\ControllerRuntimeException;
use Webloopio\Exceptions\ServerException;
use Webloopio\NetteWebsockets\Client\ClientCollection;
use Webloopio\NetteWebsockets\Controller\ControllerCollection;
use Webloopio\NetteWebsockets\Controller\ServerController;
use Webloopio\NetteWebsockets\Client\IClientConnection;


class Dispatcher implements MessageComponentInterface {

    /**
     * @var ClientCollection
     */
    protected $clientCollection;

    /**
     * @var ControllerCollection
     */
    protected $controllerCollection;

    public function __construct(
        ControllerCollection $controllerCollection,
        ClientCollection $clientCollection

    ) {
        $this->controllerCollection = $controllerCollection;
        $this->clientCollection = $clientCollection;
    }

    /**
     * @param ConnectionInterface $connection
     *
     * @throws ServerException
     */
    public function onOpen( ConnectionInterface $connection ) {
        // Store the new connection
        $client = $this->clientCollection->addClient( $connection );
        $this->sendInitMessage( $client );

        wsdump( "New connection! ({$connection->resourceId})" );
        wsdump( "Total connections: " . count($this->clientCollection) );
    }

    /**
     * @param ConnectionInterface $connection
     * @param $message
     */
    public function onMessage( ConnectionInterface $connection, $message ) {
        try {
            $clientConnection = $this->clientCollection->getClientByConnection( $connection );
            $message = new Message( $message );
            $controllerName = $message->getController();
            $controllerActionName = $message->getAction();
            $controllerInstance = $this->controllerCollection->getControllerInstance( $controllerName );

            wsdump( "Received new message from resourceId {$connection->resourceId}", $message->getMessage() );

            if( $controllerInstance === null ) {
                wsdump("erro", $this->controllerCollection->getControllerInstances());
                throw new ControllerRuntimeException( "Controller was not found by given name: $controllerName" );
            }

            $controllerInstance->call( $controllerActionName, $clientConnection, $message );
        }
        // TODO: catch blocks fallbacks
        catch( ControllerNonExistingMethodException $e ) {
            wsdump( $e->getMessage() );
            Debugger::log( $e->getMessage(), Server::DEBUG_LOGGER_NAME );
        }
        catch( ControllerException $e ) {
            wsdump( $e->getMessage() );
            Debugger::log( $e->getMessage(), Server::DEBUG_LOGGER_NAME );
        }
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function onClose( ConnectionInterface $connection ) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clientCollection->removeClient( $connection, ClientCollection::MATCH_CLIENT_BY_CONNECTION );

        wsdump( "Connection {$connection->resourceId} has disconnected" );
    }

    /**
     * @param ConnectionInterface $connection
     * @param \Exception $e
     */
    public function onError( ConnectionInterface $connection, \Exception $e ) {
        wsdump( "An error has occurred: {$e->getMessage()}" );

        $connection->close();
    }

    /**
     * @param IClientConnection $client
     *
     * @throws ServerException
     */
    protected function sendInitMessage( IClientConnection $client ) {
        $controller = $this->getServerController();

        $controller->sendMessage( $client, [
            'resource_id' => $client->getResourceId()
        ], 'init' );
    }

    /**
     * @return ServerController
     * @throws ServerException
     */
    public function getServerController(): ServerController {
        $serverController = $this->controllerCollection->getControllerInstance( "serverController" );

        if( !$serverController ) {
            throw new ServerException( "serverController was not found in DI container. Did you register it?" );
        }

        return $serverController;
    }

}