<?php
namespace Stratedge\Engine\Interfaces;

interface Factory
{
    /**
     * Returns either a new instance of the given class or the object that has
     * been registered for that class.
     * 
     * @param  string $name Fully qualified namespace name
     * @param  array  $args Arguments to pass to the class' constructor
     * @return object
     */
    public static function assemble($name, array $args = []);


    /**
     * Registers an object to the given fully qualified class name so the object
     * can be returned when the class is requested.
     * 
     * @param  string $name
     * @param  object $obj
     * @return null
     */
    public static function register($name, $obj);


    /**
     * Clears all objects registered to the factory.
     * 
     * @return null
     */
    public static function reset();
}
