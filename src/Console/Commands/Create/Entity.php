<?php
namespace Stratedge\Engine\Console\Commands\Create;

use Exception;
use Stratedge\Engine\Console\Interfaces\Entity as EntityInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

abstract class Entity extends Command
{
    protected $entity;
    protected $directory;
    protected $step = 0;

    public function __construct(EntityInterface $entity)
    {
        $this->setEntity($entity);
        parent::__construct();
    }


    /**
     * @return EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }


    /**
     * @param  EntityInterface $entity
     * @return self
     */
    public function setEntity(EntityInterface $entity)
    {
        $this->entity = $entity;
        return $this;
    }


    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }


    /**
     * @param  string $directory
     * @return self
     */
    public function setDirectory($directory)
    {
        if (empty($directory)) {
            $directory = $this->getDefaultDirectory();
        }

        //If the last character is a slash, remove it
        $this->directory = substr($directory, -1) == '/' ?
            substr($directory, 0, -1) :
            $directory;

        return $this;
    }


    /**
     * @return string|null
     */
    abstract public function getDefaultDirectory();


    /**
     * @return string|null
     */
    abstract public function getDefaultNamespace();


    /**
     * @return int
     */
    public function getStep()
    {
        return $this->step;
    }


    /**
     * @return self
     */
    public function incrementStep()
    {
        $this->step++;
        return $this;
    }


    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->getDirectory() .
            DIRECTORY_SEPARATOR .
            $this->getEntity()->getClassName() .
            '.php';
    }


    /**
     * Sets some initialization variables before the command runs
     * 
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return null
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        //Add the name of the class to our Entity object
        $this->getEntity()->setClassName(
            $input->getArgument('name')
        );

        //Store the directory provided in the command's input
        $this->setDirectory(
            $input->getOption('dir')
        );
    }


    public function createEntity(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();

        $output->writeln('');
        $output->writeln('<comment>Ensuring directory exists and is writable:</comment>');

        try {
            $fs->mkdir($this->getDirectory());
        } catch (IOExceptionInterface $e) {
            throw new Exception($e->getMessage());
        }

        $output->writeln('<info>Directory exists or was successfully created!</info>');

        if (!is_writable($this->getDirectory())) {
            throw new Exception('Directory is not writable!');
        }

        $output->writeln('<info>Directory is writable!</info>');

        $output->writeln('');
        $output->writeln('<comment>Ensuring file does not already exist:</comment>');

        if(file_exists($this->getFilePath())) {
            throw new Exception('File already exists!');
        }

        $output->writeln('<info>File does not already exist!</info>');

        $output->writeln('');
        $output->writeln('<comment>Creating class:</comment>');

        file_put_contents($this->getFilePath(), (string) $this->getEntity());

        $output->writeln('<info>Class created!</info>');
        $output->writeln('');
    }
}