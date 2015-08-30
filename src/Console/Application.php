<?php
namespace Stratedge\Engine\Console;

use Stratedge\Engine\Console\Config;
use Stratedge\Engine\Console\Objects\Entities\Edge;
use Stratedge\Engine\Console\Objects\Entities\Node;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Yaml\Yaml;

class Application extends SymfonyApplication
{
    public function __construct()
    {
        parent::__construct('Runcard by Jarret Byrne', '0.2.0-alpha');

        if (is_file('engine.yml')) {
            $config = Yaml::parse(file_get_contents('engine.yml'));
            Config::parse($config);
        }

        $this->addCommands([
            new Commands\Create\Entity\Edge(new Edge()),
            new Commands\Create\Entity\Node(new Node())
        ]);
    }
}
