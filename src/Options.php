<?php
namespace Stratedge\Engine;

use Stratedge\Engine\Factory;
use Stratedge\Toolbox\NumberUtils;

class Options
{
    const DIR_ASC   = 'ASC';
    const DIR_DESC  = 'DESC';
    protected $conds = [];
    protected $binds = [];
    protected $max;
    protected $offset;
    protected $orders = [];

    public function __construct($cond = null, array $bind = [])
    {
        if (!is_null($cond)) {
            $this->cond($cond, $bind);
        }

        return $this;
    }

    public static function assemble()
    {
        return Factory::assemble(get_called_class(), func_get_args());
    }

    public function cond($cond, array $bind = [])
    {
        $this->conds = [$cond];
        
        if (!empty($bind)) {
            $this->bind($bind);
        }

        return $this;
    }

    public function bind($key, $value = null)
    {
        if (is_array($key)) {
            $this->binds = array_merge($this->binds, $key);
        } else {
            $this->binds = array_merge($this->binds, [$key => $value]);
        }

        return $this;
    }

    public function addCond($cond, array $bind = [])
    {
        $this->conds[] = $cond;

        if (!empty($bind)) {
            $this->bind($bind);
        }

        return $this;
    }

    public function addCondFromBind(array $bind)
    {
        foreach ($bind as $key => $value) {
            $this->addCond($key . ' = :' . $key);
        }

        return $this->bind($bind);
    }

    public function limit($max, $offset)
    {
        return $this->max($max)
                    ->offset($offset);
    }

    public function max($max)
    {
        if (!NumberUtils::isOnlyNumbers($max)) {
            //throw exception
        }

        $this->max = $max;

        return $this;
    }

    public function offset($offset)
    {
        if (!NumberUtils::isOnlyNumbers($offset)) {
            //throw exception
        }

        $this->offset = $offset;

        return $this;
    }

    public function addOrder($field, $dir = self::DIR_ASC)
    {
        $this->orders[] = [$field, $dir];

        return $this;
    }

    public function prependOrder($field, $dir = self::DIR_ASC)
    {
        array_unshift($this->orders, [$field, $dir]);

        return $this;
    }

    public function getConds()
    {
        return $this->conds;
    }

    public function getBinds()
    {
        return $this->binds;
    }

    public function getMax()
    {
        return $this->max;
    }

    public function hasMax()
    {
        return !is_null($this->max);
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function hasOffset()
    {
        return !is_null($this->offset);
    }

    public function getOrders()
    {
        return $this->orders;
    }
}
