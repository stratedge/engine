<?php
namespace Stratedge\Engine\Console\Traits;

use Stratedge\Engine\Console\Interfaces\Entity as EntityInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

trait ObtainNamespace
{
    /**
     * Obtain and set the namespace of the entity
     * 
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @param  EntityInterface $entity
     * @return EntityInterface $entity
     */
    public function obtainNamespace(
        InputInterface $input,
        OutputInterface $output,
        EntityInterface $entity
    ) {
        $question = new Question(
            '<comment>Class namespace (default ' . $this->getDefaultNamespace() . '): </comment>',
            $this->getDefaultNamespace()
        );

        while (empty($namespace) || is_numeric($namespace)) {
            $namespace = $this->getHelper('question')->ask($input, $output, $question);
        }

        $entity->setNamespace($namespace);

        return $entity;
    }
}