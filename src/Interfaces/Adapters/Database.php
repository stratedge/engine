<?php
namespace Stratedge\Engine\Interfaces\Adapters;

interface Database
{
    /**
     * Used to insert data in the database. Returns the row's new primary key.
     * value
     * 
     * @param  string $table
     * @param  array  $data
     * @return int
     */
    public function insert($table, array $data);


    /**
     * Used to update data in the database. Returns the number of rows updated.
     * 
     * @param  string $table
     * @param  array  $data
     * @param  array  $options
     * @return int
     */
    public function update($table, array $data, array $options = []);
}
