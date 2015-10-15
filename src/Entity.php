<?php
namespace Stratedge\Engine;

use DateTime;
use InvalidArgumentException;
use Stratedge\Engine\Database;
use Stratedge\Engine\Interfaces\Entity as EntityInterface;
use Stratedge\Engine\Interfaces\Options as OptionsInterface;
use Stratedge\Engine\Options;
use Stratedge\Toolbox\NumberUtils;
use Stratedge\Toolbox\StringUtils;

abstract class Entity implements EntityInterface
{
    const TYPE_INT          = 'Integer'; //Deprecated
    const TYPE_STR          = 'String'; //Deprecated
    const TYPE_INTEGER      = 'Integer';
    const TYPE_FLOAT        = 'Float';
    const TYPE_STRING       = 'String';
    const TYPE_TEXT         = 'Text';
    const TYPE_DATETIME     = 'DateTime';
    const TYPE_TIMESTAMP    = 'Timestamp';
    const TYPE_DATE         = 'Date';
    const TYPE_TIME         = 'Time';
    const TYPE_BOOL         = 'Bool';


    /**
     * Magic method used to support getters and setters
     * 
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (strpos($name, 'get') === 0) {
            $name = $this->fromGetter($name);
            return $this->$name;
        } else if (strpos($name, 'set') === 0) {
            $name = $this->fromSetter($name);
            $this->$name = $this->formatData($name, $arguments[0]);
            return;
        }

        //@todo Throw an exception (but which kind?)
    }


    /**
     * Magic method used to support getters
     * 
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            $func = $this->toGetter($name);
            return $this->$func($value);
        }

        //@todo Throw an exception (but which kind?)
    }


    /**
     * Magic method used to support setters
     * 
     * @param  string $name
     * @param  mixed  $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $func = $this->toSetter($name);
            return $this->$func($value);
        }
        
        //@todo Throw an exception (but which kind?)
    }


    /**
     * Persists data in the database, handling both creation and updating
     * 
     * @return self
     */
    public function save()
    {
        if (is_null($this->getPrimaryKeyValue())) {
            return $this->saveNew();
        } else {
            return $this->saveExisting();
        }
    }


    /**
     * Creates a new record in the database
     * 
     * @return self
     */
    protected function saveNew()
    {
        if (
            $this->hasColumn('created') &&
            $this->isColumnDateType('created')
        ) {
            $this->setCreated(date('Y-m-d H:i:s'));
        }

        if (
            $this->hasColumn('updated') &&
            $this->isColumnDateType('updated')
        ) {
            $this->setUpdated(date('Y-m-d H:i:s'));
        }

        if (
            $this->hasColumn('deleted') &&
            $this->isColumnDateType('deleted')
        ) {
            $this->setDeleted('0000-00-00 00:00:00');
        }

        $adapter = Database::getAdapter();

        $id = $adapter->insert(
            $this->getTable(),
            $this->getColumnData()
        );

        $this->setPrimaryKeyValue($id);

        return $this;
    }


    /**
     * Updates an existing record in the database
     * 
     * @return self
     */
    protected function saveExisting()
    {
        if (
            $this->hasColumn('updated') &&
            $this->isColumnDateType('updated')
        ) {
            $this->setUpdated(date('Y-m-d H:i:s'));
        }

        $adapter = Database::getAdapter();

        $id = $adapter->update(
            $this->getTable(),
            $this->getColumnData(),
            [
                $this->getPrimaryKey() . ' = :id',
                'bind' => [
                    $this->getPrimaryKey() => $this->getPrimaryKeyValue()
                ]
            ]
        );

        return $this;
    }


    /**
     * Given an associative array of data, calls the getter for each key of the
     * array to set the column values
     * 
     * @param  array $data
     * @return self
     */
    public function hydrate(array $data = [])
    {
        foreach ($data as $name => $value) {
            $name = $this->toSetter($name);
            $this->$name($value);
        }

        return $this;
    }


    /**
     * Returns the name of the column marked as the entity's primary key
     * 
     * @return string
     */
    public function getPrimaryKey()
    {
        return isset($this->primary_key) ? $this->primary_key : 'id';
    }


    /**
     * Returns the value of the column defined as the entity's primary key
     * 
     * @return mixed
     */
    public function getPrimaryKeyValue()
    {
        $primary_key = $this->getPrimaryKey();
        $func = $this->toGetter($primary_key);
        return $this->$func();
    }


    /**
     * Sets the value of the property representing the table's primary key
     * 
     * @param  string|int $id
     * @return mixed
     */
    public function setPrimaryKeyValue($id)
    {
        $primary_key = $this->getPrimaryKey();
        $func = $this->toSetter($primary_key);
        return $this->$func($id);
    }


    /**
     * Returns the name of the table the entity uses
     * 
     * @return string
     */
    public function getTable()
    {
        if (isset($this->table)) {
            return $this->table;
        } else {
            $table = end(explode('\\', get_class($this)));
            return StringUtils::toSnakeCase($table);
        }
    }


    /**
     * Converts a snake-case column name to the getter function name for that
     * column
     * 
     * @param  string $name
     * @return string
     */
    protected function toGetter($name)
    {
        return 'get' . StringUtils::toCamelCase($name, true);
    }


    /**
     * Converts a snake-case column name to the setter function name for that
     * column
     * 
     * @param  string $name
     * @return string
     */
    protected function toSetter($name)
    {
        return 'set' . StringUtils::toCamelCase($name, true);
    }


    /**
     * Converts a camel-case getter function name for a column back to the
     * snake-case name of the column
     * 
     * @param  string $name
     * @return string
     */
    protected function fromGetter($name)
    {
        return StringUtils::toSnakeCase(substr($name, 3));
    }


    /**
     * Converts a camel-case setter function name for a column back to the
     * snake-case name of the column
     * 
     * @param  string $name
     * @return string
     */
    protected function fromSetter($name)
    {
        return StringUtils::toSnakeCase(substr($name, 3));
    }


    /**
     * Returns a list of columns defined for the entity
     * 
     * @return array
     */
    public function getColumns()
    {
        return array_keys($this->columns);
    }


    /**
     * Returns a list of the columns defined for the entity and the values set
     * for those columns if a value is set at all
     * 
     * @return array
     */
    public function getColumnData()
    {
        $data = [];

        foreach ($this->getColumns() as $column) {
            //Get the value
            $func = $this->toGetter($column);
            $value = $this->$func();

            //Use the value if it is not null
            if (!is_null($value)) {
                $data[$column] = $value;
            }
        }

        return $data;
    }


    /**
     * Returns the data-type of the provided snake-case column name
     * 
     * @param  string      $name
     * @return string|null       If the column does not exist, null is returned
     */
    public function getColumnType($name)
    {
        return $this->columns[$name][0];
    }


    /**
     * Returns true if the column is defined, otherwise false
     * 
     * @param  string $name
     * @return bool
     */
    public function hasColumn($name)
    {
        return in_array($name, $this->getColumns());
    }


    /**
     * Returns true if the given column is expected to contain a date, otherwise
     * false
     * 
     * @param  string $name The name of the column to evaluate
     * @return bool
     */
    protected function isColumnDateType($name)
    {
        return in_array(
            $this->getColumnType($name),
            [
                self::TYPE_DATETIME,
                self::TYPE_TIMESTAMP,
                self::TYPE_DATE
            ]
        );
    }


    /**
     * Returns the correct format for the value provided depending on the type
     * of the given column
     * 
     * @param  string $name  The name of the column to format data for
     * @param  mixed  $value The value to format
     * @return mixed
     */
    protected function formatData($name, $value)
    {
        if (!$this->hasColumn($name)) {
            return $value;
        }

        switch ($this->getColumnType($name)) {
            case self::TYPE_INT:
            case self::TYPE_INTEGER:
                return (int) $value;
            case self::TYPE_STR:
            case self::TYPE_STRING:
            case self::TYPE_TEXT:
                return (string) $value;
            case self::TYPE_FLOAT:
                return (float) $value;
            case self::TYPE_DATETIME:
            case self::TYPE_TIMESTAMP:
            case self::TYPE_DATE:
            case self::TYPE_TIME:
                return $this->formatDate($value, $this->getColumnType($name));
            case self::TYPE_BOOL:
                return (bool) $value;
        }

        return $value;
    }


    /**
     * Given a timestamp or date string, and a column type, returns the correct
     * representation of the date/time for the column type
     * 
     * @param  string     $value
     * @param  string|int $type
     * @return string|int
     */
    protected function formatDate($value, $type)
    {
        //An empty SQL date/time needs to be handled as a special case
        if (
            preg_match(
                '/^(0000-00-00|0000-00-00 00:00:00|00:00:00)$/',
                $value
            ) === 1
        ) {
            switch ($type) {
                case self::TYPE_DATETIME:
                case self::TYPE_TIMESTAMP:
                    return '0000-00-00 00:00:00';
                case self::TYPE_DATE:
                    return '0000-00-00';
                case self::TYPE_TIME:
                    return '00:00:00';
            }

            return $value;
        }

        if (NumberUtils::isOnlyNumbers($value)) {
            $dt = new DateTime();
            $dt->setTimestamp($value);
        } else {
            $dt = new DateTime($value);
        }

        switch ($type) {
            case self::TYPE_DATETIME:
            case self::TYPE_TIMESTAMP:
                return $dt->format('Y-m-d H:i:s');
            case self::TYPE_DATE:
                return $dt->format('Y-m-d');
            case self::TYPE_TIME:
                return $dt->format('H:i:s');
        }

        return $value;
    }


    /**
     * Attempts to find the row with the given id and returns an entity with
     * the row's properties.
     * If a row cannot be found, null is returned.
     * 
     * @param  int                  $id
     * @return EntityInterface|null
     */
    public static function findOne($id)
    {
        $obj = Factory::assemble(get_called_class());

        $adapter = Database::getAdapter();

        $options = Factory::assemble('\\Stratedge\\Engine\\Options')
            ->where($obj->getPrimaryKey() . ' = :' . $obj->getPrimaryKey())
            ->data($obj->getPrimaryKey(), $id);

        $result = $adapter->select(
            $obj->getTable(),
            '*',
            $options
        );

        if ($result->rowCount() < 1) {
            return null;
        }

        $obj->hydrate($result->getArray());

        return $obj;
    }


    /**
     * Attempts to find all the rows for the given ids and return an array of
     * entities with the rows' properties.
     * If no rows can be found, an empty array is returned.
     * 
     * @param  int[]             $ids An array of ids
     * @return EntityInterface[]
     */
    public static function find(array $ids)
    {
        if (empty($ids)) {
            return [];
        }

        $obj = Factory::assemble(get_called_class());

        $adapter = Database::getAdapter();

        $where = sprintf(
            '%s in (%s)',
            $obj->getPrimaryKey(),
            implode(', ', array_pad([], count($ids), '?'))
        );

        $options = Factory::assemble('\\Stratedge\\Engine\\Options')
            ->where($where)
            ->data($ids);

        $results = $adapter->select($obj->getTable(), '*', $options);

        unset($obj);

        if ($results->rowCount() < 1) {
            return [];
        }

        $objs = [];

        while ($data = $results->getArray()) {
            $obj = Factory::assemble(get_called_class());
            $obj->hydrate($data);
            $objs[] = $obj;
        }

        unset($obj);

        return $objs;
    }


    /**
     * Given a query options array, attemtps to find matching rows and returns
     * an array of entities representing matching rows.
     * 
     * @param  Options           $options
     * @return EntityInterface[]
     */
    public static function findBy(Options $options)
    {
        $obj = Factory::assemble(get_called_class());

        $adapter = Database::getAdapter();

        if (is_string($options)) {
            $options = ['conditions' => $options];
        }

        $options->prependOrder($obj->getPrimaryKey(), Options::DIR_ASC);

        $data = $adapter->select(
            $obj->getTable(),
            '*',
            $options
        );

        unset($obj);

        if (empty($data)) {
            return [];
        }

        $objs = [];

        foreach ($data as $obj_data) {
            $obj = Factory::assemble(get_called_class());
            $obj->hydrate($obj_data);
            $objs[] = $obj;
        }

        return $objs;
    }


    /**
     * Given a query options array, attempts to find the first matching row and
     * returns an entity representing the matching row.
     * If a row cannot be found, null will be returned.
     * 
     * @param  Options           $options
     * @return EntityInterface[]
     */
    public static function findOneBy(Options $options)
    {
        $options->max(1);

        $objs = static::findBy($options);

        if (empty($objs)) {
            return null;
        }

        return $objs[0];
    }


    /**
     * Retrieves a related node by relating a property of this object to the id
     * of another.
     * Used for 1-n relationships, where the current node is one of the n.
     * 
     * @param  string          $this_id
     * @param  string          $class
     * @param  string          $that_id
     * @param  Options|null    $options
     * @return EntityInterface
     */
    public function belongsTo(
        $this_id,
        $class,
        $that_id,
        Options $options = null
    ) {
        $id = $this->{$this->toGetter($this_id)}();

        if (is_null($options)) {
            $options = Options::assemble();
        }

        $options->addCond($that_id . ' = :' . $that_id, [$that_id => $id]);

        return $class::findOneBy($options);
    }


    /**
     * Attempts to retrieve all nodes related to the current node by relating
     * the id of this node to a property of the opposite nodes.
     * Used for 1-n relationships, where the current node is the 1.
     * 
     * @param  string            $this_id
     * @param  string            $class
     * @param  string            $that_id
     * @param  Options|null      $options
     * @return EntityInterface[]
     */
    public function hasMany($this_id, $class, $that_id, Options $options = null)
    {
        $id = $this->{$this->toGetter($this_id)}();

        if (is_null($options)) {
            $options = Options::assemble();
        }

        $options->addCond($that_id . ' = :' . $that_id, [$that_id => $id]);

        return $class::findBy($options);
    }


    public static function query()
    {
        return Factory::assemble('\\Stratedge\\Engine\\Query', [get_called_class()]);
    }


    public static function select($columns = '*', OptionsInterface $options = null)
    {
        $obj = Factory::assemble(get_called_class());

        $adapter = Database::getAdapter();

        $result = $adapter->select(
            $obj->getTable(),
            $columns,
            $options
        );

        return $result;
    }
}
