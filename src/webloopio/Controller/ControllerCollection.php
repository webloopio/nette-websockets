<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 09.03. 2018
 * Time: 21:10
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\NetteWebsockets\Controller;

use React\EventLoop\LoopInterface;
use Webloopio\Exceptions\ControllerLogicException;
use Webloopio\NetteWebsockets\Helper\StringHelper;


class ControllerCollection {

    /**
     * @var array
     */
    private $controllerInstances = [];

    /**
     * @return Controller[]
     */
    public function getControllerInstances(): array {
        return $this->controllerInstances;
    }

    /**
     * @param $controllerInstance
     *
     * @return ControllerCollection
     * @throws ControllerLogicException
     */
    public function addControllerInstance( $controllerInstance ): ControllerCollection {

        $controllerClassName = get_class( $controllerInstance );

        if( isset( $this->controllerInstances[$controllerClassName] ) ) {
            throw new ControllerLogicException( "$controllerClassName instance already exists in collection" );
        }

        $controllerStrippedName = StringHelper::unify( $controllerClassName );
        $this->controllerInstances[$controllerStrippedName] = $controllerInstance;

        return $this;
    }

    /**
     * @param string $controllerClassName
     *
     * @return Controller|null
     */
    public function getControllerInstance( string $controllerClassName ) {
        $name = StringHelper::unify( $controllerClassName );
        // if Controller suffix is missing, add it
        $name = !preg_match( "/Controller$/", $name ) ? $name . "Controller" : $name;

        return $this->controllerInstances[$name] ?? null;
    }

    /**
     * @param LoopInterface $loop
     */
    public function setLoops( LoopInterface $loop ) {
        foreach( $this->getControllerInstances() as $controllerInstance ) {
            $controllerInstance->setAndStartLoops( $loop );
        }
    }

}