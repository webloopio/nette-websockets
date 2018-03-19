<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 09.03. 2018
 * Time: 16:52
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\NetteWebsockets\Server;

use Nette\DI\Container;
use Webloopio\Exceptions\ServerLogicException;
use Webloopio\NetteWebsockets\Client\ClientCollection;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Webloopio\NetteWebsockets\Controller\ControllerCollection;
use Webloopio\NetteWebsockets\Controller\ControllerCollectionFactory;


class Server {

    /**
     * @var ClientCollection
     */
    private $clientCollection;

    /**
     * @var Dispatcher
     */
    private $dispatcher = null;

    /**
     * @var WsServer
     */
    private $wsServer = null;

    /**
     * @var HttpServer
     */
    private $httpServer = null;

    /**
     * @var IoServer|null
     */
    private $server = null;

    public static $settingPort = 8080;
    /**
     * @var Container
     */
    private $container;

    /**
     * @var ControllerCollection
     */
    private $controllerCollection;

    /**
     * @var array
     */
    private $controllerNames;

    public static $debug = true;

    const DEBUG_LOGGER_NAME = "nette_websockets";

    /**
     * Server constructor.
     *
     * @param array $controllerNames
     * @param Container $container
     * @param ClientCollection $clientCollection
     */
    function __construct(
        array $controllerNames,
        Container $container,
        ClientCollection $clientCollection
    ) {
        $this->container = $container;
        $this->controllerNames = $controllerNames;
        $this->clientCollection = $clientCollection;
    }

    /**
     * @throws \Webloopio\Exceptions\ControllerLogicException
     */
    private function boostrap() {
        $this->controllerCollection = (new ControllerCollectionFactory( $this->container, $this->controllerNames ))->create();
        $this->dispatcher = new Dispatcher( $this->controllerCollection, $this->clientCollection );
        $this->wsServer = new WsServer( $this->dispatcher );
        $this->httpServer = new HttpServer( $this->wsServer );
        $this->server = IoServer::factory( $this->httpServer, static::$settingPort, '127.0.0.1' );

        $this->controllerCollection->setLoops( $this->server->loop );
    }

    /**
     * @throws ServerLogicException
     * @throws \Webloopio\Exceptions\ControllerLogicException
     */
    public function run() {
        // TODO: handle exceptions
        $this->boostrap();

        if( $this->server === null ) {
            throw new ServerLogicException( "Websockets server is not set, try to run bootstrap again" );
        }

        wsdump( "Server successfully started" );
        $this->server->run();
    }

}