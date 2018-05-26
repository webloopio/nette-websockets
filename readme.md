# Nette Websockets

## Installation
Install this package via composer
```shell
$ composer require webloopio/nette-websockets
```

## Setup and start
Enable needed extensions in Nette config.neon. We are using Kdyby/Console to run commands.

```yaml
extensions:
    netteWebsockets: Webloopio\NetteWebsockets\DI\NetteWebsocketsExtension
    console: Kdyby\Console\DI\ConsoleExtension
```

To start the websockets server, execute this command from your Nette project root
```shell
$ php www/index.php webloop:ws-server:start
```
You can stop the running server by pressing `ctrl+C` while in terminal


## Usage

### Defining controllers
This package provides Controller interface so you can divide your application into logic pieces. 
You can think of Controllers like Presenters in Nette.

To register a new controller you can simply extends package Controller class

```php
<?php
use Webloopio\NetteWebsockets\Controller\Controller;

class ChatController extends Controller {

}
```

Then you must register your new Controller in config

```yaml
services:
    authController:
        class: ChatController
        tags: [webloopio.nettews.controller]

# OR

netteWebsockets: 
    controllers:
        - ChatController
```


### Controller methods
We are providing two types of methods you can use

#### Action
```php
<?php
use Webloopio\NetteWebsockets\Controller\Controller;
use Webloopio\NetteWebsockets\Client\Client;
use Webloopio\NetteWebsockets\Server\Message;

class ChatController extends Controller {
    public function actionSend( Client $client, Message $message ) {
        $chatMessage = $message->getMessage(); // Hello!
        $userId = $client->getUserId();
        
        // some logic?
        $this->chatService->saveMessage( $userId, $chatMessage );
        
        // send raw WS message directly to user
        $client->send( "Hi" ); 
        // or
        $this->sendMessage( $client, "Hi again" );
    }
}
```
#### Loop
```php
<?php
use Webloopio\NetteWebsockets\Controller\Controller;
use Webloopio\NetteWebsockets\Client\Client;
use Webloopio\NetteWebsockets\Client\ClientCollection;
use Webloopio\NetteWebsockets\Server\Message;

class ChatController extends Controller {
    public function loopDispatch( ClientCollection $clients ) {
        // we can fetch new messages from db
        $messages = $this->chatService->getNewMessages();
        
        if( !$messages ) {
            return;
        }
        
        // dispatch new messages to all users that are online
        foreach( $clients->getClients() as $client ) {
            $this->sendMessage( $client, $messages->getJson() );
        }
    }
}
```

## Commands

#### Start server
```shell
$ php www/index.php webloop:ws-server:start
```