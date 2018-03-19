<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 09.03. 2018
 * Time: 17:36
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\NetteWebsockets\Controller;

use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\Timer;
use Tracy\Debugger;
use Webloopio\Exceptions\ControllerException;
use Webloopio\Exceptions\ControllerLogicException;
use Webloopio\Exceptions\ControllerNonExistingMethodException;
use Webloopio\NetteWebsockets\Helper\StringHelper;
use Webloopio\NetteWebsockets\Client\ClientCollection;
use Webloopio\NetteWebsockets\Helper\ParserHelper;
use Webloopio\NetteWebsockets\IClientConnection;
use Webloopio\NetteWebsockets\Server\Message;
use Webloopio\NetteWebsockets\Server\Server;


class Controller {

    const ALLOWED_TIME_UNITS = [ "ms", "s", "sec", "m", "min", "h", "hour" ];

    /**
     * @var  ClientCollection
     */
    private $clients;

    /**
     * @var  LoopInterface
     */
    private $loop;

    /**
     * @var int
     */
    static $globalTimer = 3;

    /**
     * @param string|null $action
     * @param IClientConnection $client
     * @param Message $message
     *
     * @return void
     * @throws ControllerNonExistingMethodException
     */
    public function call( string $action, IClientConnection $client, Message $message ) {
        // convert underscore
        $action = strpos( $action, "_" ) ? StringHelper::toCamelCase( $action ) : $action;

        // prefix it to be it like actionMyMethodName
        $methodName = "action" . ucfirst( $action );

        if( !method_exists( $this, $methodName ) ) {
            throw new ControllerNonExistingMethodException( "The method {$methodName} called in " . __CLASS__ . " does not exists" );
        }

        call_user_func( [ $this, $methodName ], $client, $message );
    }

    /**
     * @param IClientConnection $client
     * @param $data
     * @param string|null $action
     */
    public function sendMessage( IClientConnection $client, $data, string $action = null ) {
        $message = new Message( $data, $this->getName(), $action ? $action : $this->getActionName() );
        $client->send( $message->buildMessage() );
    }

    /**
     * @return void
     * @throws ControllerException
     * @throws \ReflectionException
     */
    public function startLoops() {
        if( !$this->loop ) {
            throw new ControllerException( "Loop dependency was not set in Controller" );
        }

        $methods = get_class_methods( get_class( $this ) );

        if( !$methods ) {
            return;
        }

        // find method prefixed with "loop" word
        foreach( $methods as $method ) {
            if( !method_exists( $this, $method ) || !preg_match( "/^loop/", $method ) ) {
                continue;
            }

            $controller = $this;
            $loopMethodSettings = $this->getLoopMethodSettings( $method );
            $timerInterval = $loopMethodSettings['timer'] ?? self::$globalTimer;

            $this->getLoop()->addPeriodicTimer( $timerInterval, function() use( $controller, $method ) {
                call_user_func( [ $controller, $method ], $this->getClients() );
            });
        }
    }

    /**
     * @param string $methodName
     *
     * @return array
     * @throws \ReflectionException
     * @throws ControllerLogicException
     */
    private function getLoopMethodSettings( string $methodName ) {
        if( !$methodName || !method_exists( $this, $methodName ) ) {
            return [];
        }
        $docComment = $this->getReflection()
                           ->getMethod( $methodName )
                           ->getDocComment();

        // array of [ annotation => value ]
        $methodAnnotations = ParserHelper::simpleAnnotationParser( $docComment );

        $timer = null;

        if( isset( $methodAnnotations['timer'] ) ) {
            $timerAnnotation = $methodAnnotations['timer'];

            $allowedUnitsStr = implode( "|", self::ALLOWED_TIME_UNITS );

            if( preg_match( "/^\d+$/", $timerAnnotation ) ) {
                $timer = (int) $timerAnnotation;
            }
            else if( preg_match( "/^([0-9]+[.])? ?[0-9]+({$allowedUnitsStr})$/", $timerAnnotation ) ) {
                $numberPart = (float) preg_replace( "/ ?({$allowedUnitsStr})$/", "", $timerAnnotation );
                $stringPart = (string) preg_replace( "/^([0-9]+[.])?[0-9]+ ?/", "", $timerAnnotation );

                if( $numberPart && $stringPart ) {
                    switch( $stringPart ) {
                        case "ms":
                            $timer = $numberPart / 1000;
                            break;
                        case "s":
                        case "sec":
                            $timer = $numberPart;
                            break;
                        case "m":
                        case "min":
                            $timer = $numberPart * 60;
                            break;
                        case "h":
                        case "hour":
                            $timer = $numberPart * 3600;
                            break;
                    }

                    if( $timer < Timer::MIN_INTERVAL ) {
                        throw new ControllerLogicException( "Timer value can not be lower than " . Timer::MIN_INTERVAL . "s" );
                    }
                }
            }
            else {
                throw new ControllerLogicException( "Wrong loop method annotation format. Allowed format is int or float number and allowed units are " . implode( ", ", self::ALLOWED_TIME_UNITS ) );
            }
        }

        return [
            'timer' => $timer
        ];
    }

    /**
     * @return \ReflectionClass
     * @throws \ReflectionException
     */
    private function getReflection() {
        $class = get_called_class();
        $classReflection = new \ReflectionClass( $class );
        return $classReflection;
    }

    /**
     * @return string
     */
    public function getName(): string {
        $name = get_class( $this );
        $name = preg_replace( "/Controller$/", "", $name );
        $name = StringHelper::unify( $name );
        return $name;
    }

    /**
     * @return string
     */
    public function getActionName(): string {
        $action = "";
        $trace = debug_backtrace();
        $blackListMethods = [ "sendMessage" ]; // omit these method caller names when searching for caller

        // if there is a method to omit in trace index 1, continue to index 2 caller method
        // TODO: make it smarter - for loop
        $caller = isset( $trace[1], $trace[1]['function'] ) && in_array( $trace[1]['function'], $blackListMethods )
            ? ( $trace[2] ?? [] )
            : ( $trace[1] ?: [] );

        if( !$caller ) {
            return '';
        }

        $callerMethod = $caller['function'] ?? "";

        if( $callerMethod ) {
            $action = preg_replace( "/^(action|loop)/", "", $callerMethod );
        }

        return $action;
    }

    /**
     * @return ClientCollection
     */
    public function getClients(): ClientCollection {
        return $this->clients;
    }

    /**
     * @return LoopInterface
     */
    public function getLoop(): LoopInterface {
        return $this->loop;
    }

    /**
     * @param ClientCollection $clientCollection
     */
    public function injectClientCollection( ClientCollection $clientCollection ) {
        $this->clients = $clientCollection;
    }

    /**
     * @param LoopInterface $loop
     */
    public function setAndStartLoops( LoopInterface $loop ) {
        $this->loop = $loop;
        try {
            $this->startLoops();
        }
        catch( \Exception $e ) {
            Debugger::log( $e->getMessage(), Server::DEBUG_LOGGER_NAME );
        }
    }
}
