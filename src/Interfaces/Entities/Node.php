<?php
namespace Stratedge\Engine\Interfaces\Entities;

use Stratedge\Engine\Interfaces\Entity as EntityInterface;

interface Node extends EntityInterface
{
    /**
     * Static function to create a new record and return the newly created
     * node
     * 
     * @param  array           $data
     * @return NodeInterface
     */
    public static function create(array $data = []);
}
