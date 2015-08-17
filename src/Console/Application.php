<?php
namespace Stratedge\Engine\Console;

use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{
    public function __construct()
    {
        parent::__construct('Runcard by Jarret Byrne', '0.1.0');

        $this->addCommands([
            new Commands\Create\Node(new Objects\Node())
        ]);
    }
}
