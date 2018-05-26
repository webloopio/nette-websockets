<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 13.03. 2018
 * Time: 16:38
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\NetteWebsockets\Server;


interface IMessage {
    public function getRaw(): string;
}

interface IMessageJson {
    public function isJson(): bool;
    public function getMessageArray(): array;
    public function getRaw(): string;
}

interface IEnhancedMessage {
    /**
     * @return string|null
     */
    public function getAction();

    /**
     * @return string|null
     */
    public function getController();

    /**
     * @param bool $json
     *
     * @return array|string
     */
    public function buildMessage( bool $json = true );

    /**
     * @param string $className
     */
    public function transformMessageToObject( string $className );

    /**
     * @return mixed
     */
    public function getMessageObject();

    /**
     * @param $data
     */
    public function setMessageObject( $data );

    /**
     * @return bool
     */
    public function isMessageObject(): bool;
}

interface IMessageObject {
    public function setData( array $classType );
}