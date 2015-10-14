<?php
namespace Stratedge\Engine;

use BadMethodCallException;
use Stratedge\Engine\Interfaces\Options as OptionsInterface;

class Query
{
    protected $class;
    protected $options;


    public function __construct($class, OptionsInterface $options = null)
    {
        $this->setClass($class);

        if (is_null($options)) {
            $options = Factory::assemble('\\Stratedge\\Engine\\Options');
        }

        $this->setOptions($options);
    }

    public function getClass()
    {
        return $this->class;
    }


    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }


    public function getOptions()
    {
        return $this->options;
    }


    public function setOptions(OptionsInterface $options)
    {
        $this->options = $options;
        return $this;
    }


    public function select($columns = '*')
    {
        return call_user_func_array([$this->getClass(), 'select'], [$columns, $this->getOptions()]);
    }


    public function __call($name, $arguments)
    {
        if (method_exists($this->getOptions(), $name)) {
            call_user_func_array([$this->getOptions(), $name], $arguments);
            return $this;
        }

        throw new BadMethodCallException(
            "Call to undefined method " . get_class() . "::{$name}()"
        );
    }
}