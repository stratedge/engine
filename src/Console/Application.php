<?php
namespace Stratedge\Engine\Console;

use Stratedge\Engine\Console\Objects\Entities\Edge;
use Stratedge\Engine\Console\Objects\Entities\Node;
use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{
    public function __construct()
    {
        parent::__construct('Runcard by Jarret Byrne', '0.2.0-alpha');

        $this->addCommands([
            new Commands\Create\Entity\Edge(new Edge()),
            new Commands\Create\Entity\Node(new Node())
        ]);
    }
}
