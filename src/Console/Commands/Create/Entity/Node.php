<?php
namespace Stratedge\Engine\Console\Commands\Create\Entity;

use Stratedge\Engine\Console\Commands\Create\Entity;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Node extends Entity
{
    use \Stratedge\Engine\Console\Traits\Columns;
    use \Stratedge\Engine\Console\Traits\ObtainNamespace;

    protected function configure()
    {
        $this->setName('create:entity:node')
             ->setDescription('Create a new node class')
             ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of the node class to create'
             )
             ->addArgument(
                'directory',
                InputArgument::REQUIRED,
                'Directoy into which the new node will be placed'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        system('clear');

        //Get the namespace for the class
        $this->setEntity($this->obtainNamespace($input, $output, $this->getEntity()));

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
