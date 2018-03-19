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
     * Message constructor.
     *
     * @param array|string $message
     * @param string|null $controller
     * @param string|null $action
     */
    public function __construct(
        $message,
        string $controller = null,
        string $action = null
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
        }

        if( $controller ) {
            $this->setController( $controller );
        }
        if( $action ) {
            $this->setAction( $action );
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
        $event = ( $controller ? $controller . "/" : "" ) . $action;

        $message = [
            'event' => $event,
            'data' => $this->getMessage(),
            'callerController' => $controller,
            'callerAction' => $action
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

}