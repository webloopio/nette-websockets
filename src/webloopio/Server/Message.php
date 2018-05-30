<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 13.03. 2018
 * Time: 16:35
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\NetteWebsockets\Server;

use Webloopio\Exceptions\MessageLogicException;
use Webloopio\NetteWebsockets\Helper\StringHelper;


class Message implements IMessage, IMessageJson, IEnhancedMessage {

    /**
     * @var string
     */
    protected $messageString = "";

    /**
     * @var array
     */
    protected $messageArray = [];

    /**
     * @var mixed
     */
    private $messageObject = null;

    /**
     * @var string
     */
    protected $isMessageJson = null;

    /**
     * @var null|string
     */
    protected $controller = null;

    /**
     * @var null|string
     */
    protected $action = null;

    /**
     * @var null|string
     */
    private $hash = null;

    /**
     * Message constructor.
     *
     * @param array|string $message
     * @param string|null $controller
     * @param string|null $action
     * @param string|null $hash
     */
    public function __construct(
        $message,
        string $controller = null,
        string $action = null,
        string $hash = null
    ) {
        if( is_string( $message ) ) {
            $this->messageString = $message;

            if( $this->isJson() ) {
                $this->messageArray = json_decode( $message, true );
            }
        }
        else if( is_array( $message ) ) {
            $this->messageArray = $message;
            $this->isMessageJson = true;
            $this->messageString = json_encode( $message );
        }

        if( $this->isJson() ) {
            $controller = $this->messageArray['controller'] ?? $controller;
            $action = $this->messageArray['action'] ?? $action;
            $hash = $this->messageArray['hash'] ?? $hash;
        }

        if( $controller ) {
            $this->setController( $controller );
        }
        if( $action ) {
            $this->setAction( $action );
        }
        if( $hash ) {
            $this->setHash( $hash );
        }
    }

    /**
     * @return bool
     */
    public function isJson(): bool {
        if( $this->isMessageJson === null ) {
            $result = json_decode( $this->messageString );
            $this->isMessageJson = json_last_error() === JSON_ERROR_NONE ? true : false;
        }
        return $this->isMessageJson;
    }

    /**
     * @param bool $json
     *
     * @return array|string
     */
    public function buildMessage( bool $json = true ) {
        $controller = $this->getController();
        $action = $this->getAction();
        $hash = $this->getHash();
        $event = ( $controller ? $controller . "/" : "" ) . $action . ( $hash ? "-$$" . $hash . "$$" : "" );

        $message = [
            'event' => $event,
            'data' => $this->getMessage(),
            'callerController' => $controller,
            'callerAction' => $action,
            'callerHash' => $hash,
        ];

        return $json ? json_encode( $message ) : $message;
    }

    /**
     * @param null|string $controller
     *
     * @return Message
     */
    public function setController( string $controller ): Message {
        $this->controller = StringHelper::unify( $controller );

        return $this;
    }

    /**
     * @param null|string $action
     *
     * @return Message
     */
    public function setAction( string $action ): Message {
        $this->action = StringHelper::unify( $action );

        return $this;
    }

    /**
     * @return string
     */
    public function getRaw(): string {
        return $this->messageString;
    }

    /**
     * @return array|string
     */
    public function getMessage() {
        $messageArray = $this->getMessageArray();
        return $messageArray ? $messageArray : $this->getRaw();
    }

    /**
     * @return array
     */
    public function getMessageArray(): array {
        return $this->messageArray;
    }

    /**
     * @return null|string
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * @return null|string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @return mixed|null
     */
    public function getData() {
        $messageArray = $this->getMessageArray();
        return $messageArray['data'] ?? null;
    }

    /**
     * @param string $className
     *
     * @throws MessageLogicException
     */
    public function transformMessageToObject( string $className ) {
        if( !$this->isJson() ) {
            throw new MessageLogicException( "If you want to transform the message in " . get_class() . " to object, it must be json-like." );
        }
        $classInterfaces = class_implements( $className );
        if( !isset( $classInterfaces[ IMessageObject::class ] ) ) {
            throw new MessageLogicException( "Providen class name must implement " . IMessageObject::class . " interface" );
        }
        /** @var IMessageObject $dataObject */
        $dataObject = new $className();
        $dataObject->setData( $this->getData() );
        $this->setMessageObject( $dataObject );
    }

    /**
     * @return mixed
     */
    public function getMessageObject() {
        return $this->messageObject;
    }

    /**
     * @param $data
     *
     * @throws MessageLogicException
     */
    public function setMessageObject( $data ) {
        if( !( $data instanceof IMessageObject ) ) {
            throw new MessageLogicException( "First parameter must be instance of " . IMessageObject::class );
        }
        $this->messageObject = $data;
    }

    /**
     * @return bool
     */
    public function isMessageObject(): bool {
        return $this->messageObject !== null;
    }

    /**
     * @return null|string
     */
    public function getHash() {
        return $this->hash;
    }

    /**
     * @param null|string $hash
     */
    public function setHash( string $hash ) {
        $this->hash = $hash;
    }

}