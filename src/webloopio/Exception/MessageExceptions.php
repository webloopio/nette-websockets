<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 09.03. 2018
 * Time: 18:25
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\Exceptions;

class MessageException extends \Exception {}

class MessageRuntimeException extends MessageException {}

class MessageLogicException extends MessageException {}