<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 09.03. 2018
 * Time: 18:27
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\Exceptions;


class ControllerException extends \Exception {}

class ControllerRuntimeException extends ControllerException {}

class ControllerLogicException extends ControllerException {}

class ControllerNonExistingMethodException extends ControllerException {}