<?php
namespace Stratedge\Engine\Console\Objects\Entities;

use Stratedge\Engine\Console\Objects\Entity;

class Edge extends Entity
{
    use \Stratedge\Engine\Console\Traits\ParseTemplate;

    public function __toString()
    {
        return $this->parseTemplate(__DIR__ . '/../../Templates/edge.class.tpl', [
            'namespace' => $this->getNamespace(),
            'class' => $this->getClassName(),
            'definitions' => $this->parseColumnDefinitions(),
            'declarations' => $this->parseColumnDeclarations()
        ]);
    }
}