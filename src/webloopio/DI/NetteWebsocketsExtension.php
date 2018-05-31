<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 09.03. 2018
 * Time: 15:16
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\NetteWebsockets\DI;

use Kdyby\Console\DI\ConsoleExtension;
use Nette\DI\CompilerExtension;
use Webloopio\NetteWebsockets\Client\IAuthenticator;
use Webloopio\NetteWebsockets\Client\IJWTAuthenticator;
use Webloopio\NetteWebsockets\Console\RunServerCommand;
use Webloopio\NetteWebsockets\Helper\StringHelper;
use Webloopio\NetteWebsockets\Client\ClientCollection;
use Webloopio\NetteWebsockets\Server\Server;


class NetteWebsocketsExtension extends CompilerExtension {

    const TAG_CONTROLLER = 'webloopio.nettews.controller';

    const AUTHENTICATION_JWT = 'jwt';

    static public $debug = false;

    private $defaults = [
        "controllers" => [
            "Webloopio\NetteWebsockets\Controller\ServerController"
        ],
        "authentication" => null,
    ];

    public function loadConfiguration() {
        $this->validateConfig( $this->defaults );
        $config = $this->getConfig();
        $builder = $this->getContainerBuilder();

        $controllers = $config["controllers"];
        $authenticationType = $config["authentication"];

        $clientCollectionDeps = [];
        $clientCollectionDeps[] = $authenticationType;
        if( $authenticationType === self::AUTHENTICATION_JWT ) {
            $clientCollectionDeps[] = "@" . IJWTAuthenticator::class;
        }

        // setup Controller dependencies
        $builder->addDefinition( $this->prefix( "clientCollection" ) )
                ->setFactory( ClientCollection::class, $clientCollectionDeps );

        // instantiate all Controllers defined in config
        foreach( $controllers as $controller ) {
            if( !is_string( $controller ) ) {
                throw new \RuntimeException("Defined controller must by type of string, type of " . gettype($controller) . " provided instead");
            }
            if( !class_exists( $controller ) ) {
                throw new \LogicException( "Controller class $controller does not exists" );
            }

            $controllerTrimmedName = lcfirst( StringHelper::trimNamespace( $controller ) );

            $builder->addDefinition( $this->prefix( $controllerTrimmedName ) )
                    ->setFactory( $controller )
                    ->setInject( true );
        }

        // setup server (kdyby) commands
        $commands = [
            'server' => RunServerCommand::class,
        ];
        foreach( $commands as $name => $command ) {
            $builder->addDefinition( $this->prefix( 'command.' . lcfirst( $name ) ) )
                    ->setType( $command )
                    ->addTag( ConsoleExtension::TAG_COMMAND );
        }

        // Setup of the main websockets server
        // We're passing down registered controller names
        $builder->addDefinition( $this->prefix( "server" ) )
                ->setFactory( Server::class, [ $config['controllers'], $config['authentication'] ] );
    }

}