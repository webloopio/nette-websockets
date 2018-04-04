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

use Nette\DI\CompilerExtension;
use Webloopio\NetteWebsockets\Helper\StringHelper;
use Webloopio\NetteWebsockets\Client\ClientCollection;
use Webloopio\NetteWebsockets\Server\Server;


class NetteWebsocketsExtension extends CompilerExtension {

    private $defaults = [
        "controllers" => [
            "Webloopio\NetteWebsockets\Controller\ServerController"
        ]
    ];

    public function beforeCompile () {
        require_once __DIR__ . '/../Helper/shortcuts.php'; // TODO: přidat do composeru
    }

    public function loadConfiguration() {
        $this->validateConfig( $this->defaults );
        $config = $this->getConfig();
        $builder = $this->getContainerBuilder();

        $controllers = $config["controllers"];

        $this->setupControllerDependencies();

        foreach( $controllers as $controller ) {
            if( is_string( $controller ) ) {
                if( !class_exists( $controller ) ) {
                    throw new \LogicException( "Controller class $controller does not exists" );
                }

                $controllerTrimmedName = lcfirst( StringHelper::trimNamespace( $controller ) );

                $builder->addDefinition( $this->prefix( $controllerTrimmedName ) )
                        ->setFactory( $controller )
                        ->setInject( true );

            }
            else {
                throw new \RuntimeException( "Defined controller must by type of string, type of " . gettype( $controller ) . " provided instead" );
            }
        }

        $this->setupRunServer();
    }

    /**
     * Setup of the main websockets server
     * We're passing down registered controller names
     *
     * @return void
     */
    private function setupRunServer() {
        $builder = $this->getContainerBuilder();

        $config = $this->getConfig();

        $builder->addDefinition( $this->prefix( "server" ) )
                ->setFactory( Server::class, [ $config['controllers'] ] );
    }

    private function setupControllerDependencies() {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition( $this->prefix( "clientCollection" ) )
                ->setFactory( ClientCollection::class );
    }

}