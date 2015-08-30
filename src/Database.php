<?php
namespace Stratedge\Engine;

use Stratedge\Engine\Interfaces\Adapters\Database as DatabaseAdapterInterface;
use Stratedge\Engine\Interfaces\DataStore as DataStoreInterface;

class Database implements DataStoreInterface
{
    protected static $adapter;

    /**
     * @param  DatabaseAdapterInterface $adapter
     * @return null
     */
    public static function register(DatabaseAdapterInterface $adapter)
    {
        self::$adapter = $adapter;
    }


    /**
     * @return DatabaseAdapterInterface|null
     */
    public static function getAdapter()
    {
        return self::$adapter;
    }
}
