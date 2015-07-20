<?php
namespace Stratedge\Pumper\Repositories;

use Stratedge\Pumper\Entities\Node as Entity;

abstract class Node
{
    public static function register(Entity $n)
    {
        self::setNode($n);
    }

    public static function getNode()
    {
        return static::$node;
    }

    public static function setNode(Entity $node)
    {
        static::$node = $node;
    }

    public static function create(array $data = [])
    {
        return self::getNode()->create($data);
    }

    public static function find($id)
    {
        return self::findOneBy(['id' => $id]);
    }

    public static function findOneBy(array $data = [])
    {
        return self::getNode()->findOneBy($data);
    }

    public static function findBy(array $data = [])
    {
        return self::getNode()->findBy($data);
    }
}
