<?php
namespace Stratedge\Engine\Console\Commands\Create;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class Node extends Command
{
    protected $node;
    protected $directory;
    protected $step = 0;

    public function __construct(\Stratedge\Engine\Console\Objects\Node $node)
    {
        $this->setNode($node);
        parent::__construct();
    }

    public function getNode()
    {
        return $this->node;
    }

    public function setNode(\Stratedge\Engine\Console\Objects\Node $node)
    {
        $this->node = $node;
        return $this;
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    public function setDirectory($directory)
    {
        if (substr($directory, -1) == '/') {
            $directory = substr($directory, 0, -1);
        }

        $this->directory = $directory;

        return $this;
    }

    public function getStep()
    {
        return $this->step;
    }

    public function incrementStep()
    {
        $this->step++;
        return $this;
    }

    protected function configure()
    {
        $this->setName('create:node')
             ->setDescription('Create a new node object')
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

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->getNode()->setClass($input->getArgument('name'));
        $this->setDirectory($input->getArgument('directory'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        system('clear');

        $namespace = $this->obtainNamespace($input, $output);

        $this->getNode()->setNamespace($namespace);

        while ($this->getStep() == 0) {

            system('clear');

            $this->printColumns($input, $output, $this->getNode()->getColumnsAsArrays());

            $question = new ChoiceQuestion(
                'Please select an action:',
                [
                    1 => 'Add a column',
                    2 => 'Update a column',
                    3 => 'Continue'
                ],
                'Continue'
            );

            $answer = $this->getHelper('question')->ask($input, $output, $question);

            // system('clear');

            switch ($answer) {
                case 'Add a column':
                    system('clear');
                    $this->addColumn($input, $output);
                    break;
                // case 'Update a column':
                //     $this->updateColumn($input, $output);
                //     break;
                default:
                    $this->incrementStep();
            }
        }

        $this->ensureDirectory($input, $output);

        $path = $this->getDirectory() . '/' . $this->getNode()->getClass() . '.php';

        $this->getNode()->prependColumn('id', 'integer')
                        ->appendColumn('created', 'integer')
                        ->appendColumn('updated', 'integer')
                        ->appendColumn('deleted', 'integer');

        file_put_contents($path, $this->getNode());

        $output->writeln('');

        $output->writeln(
            sprintf(
                '<info>Class %s created in %s</info>',
                $this->getNode()->getClass(),
                realpath($path)
            )
        );

        $output->writeln('');
    }

    public function obtainNamespace(InputInterface $input, OutputInterface $output)
    {
        $question = new Question('<comment>Class namespace: </comment>');

        $namespace = $this->getNode()->getNamespace();

        while (empty($namespace) || is_numeric($namespace)) {
            $namespace = $this->getHelper('question')->ask($input, $output, $question);
        }

        return $namespace;
    }

    public function printColumns(InputInterface $input, OutputInterface $output, $columns)
    {
        $output->writeln('<comment>Current columns:</comment>');

        $output->writeln('');

        if ($this->getNode()->hasColumns()) {
            $table = new Table($output);

            $table->setHeaders(['column', 'type']);

            $table->setRows($columns);

            $table->render();
        } else {
            $output->writeln('<error>No columns defined, yet!</error>');
        }

        $output->writeln('');
    }

    public function addColumn(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Add a column</comment>');

        $output->writeln('');

        $question = new Question('<comment>Column name: </comment>');

        $column_name = $this->getHelper('question')->ask($input, $output, $question);

        $output->writeln('');

        $question = new ChoiceQuestion(
            '<comment>Please select a column type:</comment>',
            [
                1 => 'integer',
                2 => 'varchar',
                3 => 'text'
            ],
            'integer'
        );

        $column_type = $this->getHelper('question')->ask($input, $output, $question);

        $this->getNode()->appendColumn($column_name, $column_type);
    }

    public function ensureDirectory()
    {
        if (!is_dir($this->getDirectory())) {
            mkdir($this->getDirectory(), 0777, true);
        }
    }
}
