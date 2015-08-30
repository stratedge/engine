<?php
namespace Stratedge\Engine\Console\Commands\Create\Entity;

use ReflectionClass;
use Stratedge\Engine\Console\Commands\Create\Entity;
use Stratedge\Engine\Console\Config;
use Stratedge\Toolbox\FileUtils;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Edge extends Entity
{
    use \Stratedge\Engine\Console\Traits\Columns;
    use \Stratedge\Engine\Console\Traits\FindFile;
    use \Stratedge\Engine\Console\Traits\ObtainNamespace;


    /**
     * @return string|null
     */
    public function getDefaultDirectory()
    {
        return Config::getDefaultEdgeDirectory();
    }


    /**
     * @return string|null
     */
    public function getDefaultNamespace()
    {
        return Config::getDefaultEdgeNamespace();
    }


    protected function configure()
    {
        $this->setName('create:entity:edge')
             ->setDescription('Create a new edge class')
             ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of the edge class to create'
             )
             ->addOption(
                'dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'Directoy into which the new node will be placed'
             );
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        system('clear');

        //Get the namespace for the class
        $this->setEntity($this->obtainNamespace($input, $output, $this->getEntity()));

        //Get the two nodes to relate
        for ($i = 1; $i <= 2; $i++) {
            $path = $this->findFile($input, $output, $i);

            $class = FileUtils::getClassFromPath($path);

            $obj = new ReflectionClass($class);

            $this->getEntity()->appendColumn(
                $obj->newInstanceWithoutConstructor()->getIdForEdge(),
                'integer'
            );
        }

        //Get the columns for the class
        while ($this->getStep() == 0) {

            system('clear');

            //Show the user the columns that are already defined
            $this->printColumns($input, $output, $this->getEntity());

            //Get the user's choice for what to do next for columns
            $column_action_choice = $this->getColumnActionChoice($input, $output);

            //Handle the user's input
            switch ($column_action_choice) {
                case self::$COLUMN_ACTION_ADD:
                    system('clear');
                    $this->setEntity($this->addColumn($input, $output, $this->getEntity()));
                    break;
                default:
                    $this->incrementStep();
                    break;
            }
        }

        //Add default columns to the list
        $this->getEntity()->prependColumn('id', 'integer');
        $this->getEntity()->appendColumn('created', 'datetime');
        $this->getEntity()->appendColumn('updated', 'datetime');
        $this->getEntity()->appendColumn('deleted', 'datetime');

        //Create the class for the entity in the correct path
        $this->createEntity($input, $output);
    }
}
