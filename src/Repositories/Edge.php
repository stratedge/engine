<?php
namespace Stratedge\Engine\Repositories;

use Stratedge\Engine\Entities\Edge as Entity;
use Stratedge\Engine\Entities\Node as NodeEntity;

class Edge
{
    public static function register(Entity $e, NodeEntity $a, NodeEntity $b)
    {
        self::setEdge($e);
        self::setOpposite($a->getIdForEdge(), $b);
        self::setOpposite($b->getIdForEdge(), $a);
    }

    public static function getEdge()
    {
        return static::$edge;
    }

    public static function setEdge(Entity $edge)
    {
        static::$edge = $edge;
    }

    public static function setOpposite($property, NodeEntity $a)
    {
        static::$opposites[$property] = $a;
    }

    public static function getOpposite(NodeEntity $a)
    {
        return static::$opposites[$a->getIdForEdge()];
    }

    public static function create(NodeEntity $a, NodeEntity $b)
    {
        return self::getEdge()->create($a, $b);
    }

    public static function findOneBy(NodeEntity $a)
    {
        return self::getEdge()->findOneBy($a);
    }

    public static function findOppositeNode(NodeEntity $a)
    {
        return self::getEdge()->findOppositeNode($a, self::getOpposite($a));
    }

    public static function findOppositeNodes(NodeEntity $a)
    {
        return self::getEdge()->findOppositeNodes($a, self::getOpposite($a));
    }
}
