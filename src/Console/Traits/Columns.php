<?php
namespace Stratedge\Engine\Console\Traits;

use Stratedge\Engine\Console\Interfaces\Entity as EntityInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

trait Columns
{
    public static $COLUMN_ACTION_ADD        = 'Add a column';
    public static $COLUMN_ACTION_UPDATE     = 'Update a column'; //Not implemented yet
    public static $COLUMN_ACTION_REMOVE     = 'Remove a column'; //Not implemented yet
    public static $COLUMN_ACTION_CONTINUE   = 'Continue';

    /**
     * Prints a table of the columns defined for the class's stored entity
     * 
     * @param  InputInterface  $input   [description]
     * @param  OutputInterface $output  [description]
     * @param  [type]          $columns [description]
     * @return [type]                   [description]
     */
    public function printColumns(
        InputInterface $input,
        OutputInterface $output,
        EntityInterface $entity
    ) {
        $output->writeln('<comment>Current columns:</comment>');

        $output->writeln('');

        if ($entity->hasColumns()) {
            $table = new Table($output);

            $table->setHeaders([
                'column',
                'type'
            ]);

            $table->setRows(
                $entity->getColumnsAsArrays()
            );

            $table->render();
        } else {
            $output->writeln('<error>No columns defined, yet!</error>');
        }

        $output->writeln('');
    }


    public function getColumnActionChoice(InputInterface $input, OutputInterface $output)
    {
        $question = new ChoiceQuestion(
                'Please select an action:',
                [
                    1 => self::$COLUMN_ACTION_ADD,
                    2 => self::$COLUMN_ACTION_CONTINUE
                ],
                self::$COLUMN_ACTION_CONTINUE
            );

        return $this->getHelper('question')->ask($input, $output, $question);
    }


    /**
     * Walks the user through adding a new column to the entity
     * 
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @param  EntityInterface $entity
     * @return EntityInterface
     */
    public function addColumn(
        InputInterface $input,
        OutputInterface $output,
        EntityInterface $entity
    ) {
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
                3 => 'text',
                4 => 'float',
                5 => 'date_time',
                6 => 'timestamp',
                7 => 'date',
                8 => 'time',
                9 => 'boolean'
            ],
            'integer'
        );

        $column_type = $this->getHelper('question')->ask($input, $output, $question);

        $entity->appendColumn($column_name, $column_type);

        return $entity;
    }
}
