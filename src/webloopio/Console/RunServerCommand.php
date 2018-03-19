<?php
/**
 * Created by Daniel Batěk (http://webloop.io/)
 * User: keyBeatz
 * Package: webloopio:nette-websockets
 * Date: 19.03. 2018
 * Time: 15:52
 * License: MIT
 * Since: 0.1
 */

namespace Webloopio\NetteWebsockets\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webloopio\NetteWebsockets\Server\Server;


class RunServerCommand extends Command
{
    private $server;

    public function __construct(
        Server $server
    ) {
        parent::__construct();

        $this->server = $server;
    }
    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName( 'webloop:ws-server:start' )
             ->setDescription( 'Start WebSocket server.' );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute( InputInterface $input, OutputInterface $output ) {
        wsdump( "Starting WebSockets server" );
        $this->server->run();
    }
}