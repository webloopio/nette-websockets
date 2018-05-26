<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 13.03. 2018
 * Time: 12:53
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\NetteWebsockets\Controller;

use Nette\DI\Container;
use Webloopio\Exceptions\ControllerLogicException;
use Webloopio\NetteWebsockets\DI\NetteWebsocketsExtension;


class ControllerCollectionFactory {

    /**
     * @var Container
     */
    private $container;
    /**
     * @var array|string[]
     */
    private $controllerFullyQualifiedNames;

    /**
     * ControllerCollectionFactory constructor.
     *
     * @param Container $container
     * @param string[] $controllerFullyQualifiedNames
     */
    function __construct(
        Container $container,
        array $controllerFullyQualifiedNames
    ) {
        $this->container = $container;
        $this->controllerFullyQualifiedNames = $controllerFullyQualifiedNames;
    }

    /**
     * @throws ControllerLogicException
     *
     * @return ControllerCollection
     */
    public function create(): ControllerCollection {
        $controllerCollection = new ControllerCollection();

        // add controllers registered by config
        foreach( $this->controllerFullyQualifiedNames as $controllerName ) {
            // try to find instantiated service in Nette DI container
            $controllerInstance = $this->container->getByType( $controllerName );

            if( !$controllerInstance ) {
                throw new ControllerLogicException( "Controller with name $controllerName was not found in Nette DI container. Did you register it?" );
            }

            // if successfully found add it to collection
            $controllerCollection->addControllerInstance( $controllerInstance );
        }

        // add controllers registered by tag
        foreach( $this->container->findByTag( NetteWebsocketsExtension::TAG_CONTROLLER ) as $controllerName => $tags ) {
            $controllerInstance = $this->container->getByType( $controllerName );
            $controllerCollection->addControllerInstance( $controllerInstance );
        }

        return $controllerCollection;
    }

}