<?php
namespace Stratedge\Engine\Interfaces;

interface Options
{
    public function where($stmt, $data = null);

    public function getWhere();
}
