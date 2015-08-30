<?php
namespace Stratedge\Engine\Interfaces;

use Stratedge\Engine\Interfaces\Adapters\Database as DatabaseAdapterInterface;

interface DataStore
{
    /**
     * @param  DatabaseAdapterInterface $adapter
     * @return null
     */
    public static function register(DatabaseAdapterInterface $adapter);


    /**
     * @return DatabaseAdapterInterface|null
     */
    public static function getAdapter();
}
