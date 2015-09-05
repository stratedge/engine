<?php
namespace Stratedge\Engine\Entities;

use Stratedge\Engine\Entity;
use Stratedge\Engine\Factory;
use Stratedge\Engine\Interfaces\Entities\Node as NodeInterface;

abstract class Node extends Entity implements NodeInterface
{
    /**
     * Static function to create a new record and return the newly created
     * node
     * 
     * @param  array         $data
     * @return NodeInterface
     */
    public static function create(array $data = [])
    {
        $obj = Factory::assemble(get_called_class());

        $obj->hydrate($data);

        $obj->save();

        return $obj;
    }
}
