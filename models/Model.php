<?php

/**
 * Simple ORM
 */
abstract class Model
{
    /**
     * Model name
     * @var string
     */
    public $_class;

    /**
     * @var bool
     */
    protected $isNew = true;

    public function __construct()
    {
        $this->_class = self::class();
    }

    /**
     * @return Database
     */
    public static function getDb(): Database
    {
        return App::getDb();
    }

    /**
     * Table name
     * @return string
     */
    abstract public static function tableName(): string;

    /**
     * Column name with primary key
     * @return string
     */
    abstract public function primaryKey(): string;

    /**
     * Table columns
     * @return array
     */
    abstract public function attributes(): array;

    /**
     * Model name
     * @return string
     */
    public static function class()
    {
        return get_called_class();
    }

    public function __set(string $name, $value): void
    {
        $this->$name = $value;
    }

    public function __get(string $name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * Find one row
     * @param array $params
     * @return mixed|null
     * @throws Exception
     */
    public static function findOne(array $params = [])
    {
        $query = 'SELECT * FROM ' . static::tableName();
        $i = 0;
        foreach ($params as $key => $param) {
            $query .= ($i == 0) ? ' WHERE ' : ' AND ';
            $query .= ' ' . static::tableName() . '.' . $key . ' = "' . $param . '" ';
            $i++;
        }

        $result = self::getDb()::query($query);
        $result = $result->fetch_assoc();
        $model = static::createModel();
        if ($result) {
            $model->setAttributes($result);
            $model->isNew = false;
        }

        return !empty($result) ? $model : null;
    }

    /**
     * Find all rows
     * @param array $params
     * @return array
     * @throws Exception
     */
    public static function findAll(array $params = []): array
    {
        $query = 'SELECT * FROM ' . static::tableName();
        if (!empty($params)) {
            $i = 0;
            foreach ($params as $key => $param) {
                $query .= ($i == 0) ? ' WHERE ' : ' AND ';
                $query .= ' ' . static::tableName() . '.' . $key . ' = "' . $param . '" ';
                $i++;
            }
        }

        $result = self::getDb()::query($query);
        $data = [];
        while (($row = $result->fetch_assoc())) {
            $model = static::createModel();
            $model->setAttributes($row);
            $model->isNew = false;
            $data[] = $model;
        }
        return $data;
    }

    /**
     * @param string $query
     * @return array
     * @throws Exception
     */
    public static function findQuery(string $query)
    {
        $result = self::getDb()::query($query);
        $data = [];
        while (($row = $result->fetch_assoc())) {
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Populate model
     * @param array $data
     */
    public function setAttributes(array $data)
    {
        $primaryKey = $this->primaryKey();
        if (isset($data[$primaryKey])) {
            $this->$primaryKey = $data[$primaryKey];
        }
        foreach ($data as $key => $item) {
            if (in_array($key, $this->attributes())) {
                $this->$key = $item;
            }
        }
    }

    /**
     * Get attributes
     * @return array
     */
    public function getAttributes(): array
    {
        $result = [];
        foreach ($this as $key => $item) {
            if (in_array($key, $this->attributes())) {
                $result[$key] = $item;
            }
        }
        return $result;
    }

    /**
     * Create object
     * @return mixed
     */
    public static function createModel()
    {
        $class = static::class();
        return new $class;
    }

    /**
     * Insert row in table
     * @return int|false
     * @throws Exception
     */
    public function insert()
    {
        $query = 'INSERT INTO `' . static::tableName() . '` ' . ' ( ';
        $attrArr = [];
        foreach ($this->attributes() as $attribute) {
            $method = 'get'.ucfirst($attribute);
            $val = isset($this->$attribute) ? $this->$attribute :
                ( method_exists(static::class(),$method) ? $this->$method() : null);
            if ($val) {
                $attrArr[] = ' `' . $attribute . '`';
            }
        }
        $query .= implode(',', $attrArr);
        $query .= ' ) VALUES (';
        $attrArr = [];
        foreach ($this->attributes() as $attribute) {
            $method = 'get'.ucfirst($attribute);
            $val = isset($this->$attribute) ? $this->$attribute :
                ( method_exists(static::class(),$method) ? $this->$method() : null);
            if ($val) {
                $attrArr[] = ' "' . mysqli_real_escape_string(self::getDb()::getInstance(), $val) . '" ';
            }
        }
        $query .= implode(',', $attrArr);
        $query .= ' ) ';
        $result = self::getDb()::query($query);
        return $result ? self::getDb()::getInstance()->insert_id : false;
    }

    /**
     * Update row in table
     * @return bool|mysqli_result
     * @throws Exception
     */
    public function update()
    {
        $query = 'UPDATE `' . static::tableName() . '` SET ';
        $hasValue = null;
        $attrArr = [];
        foreach ($this->attributes() as $attribute) {
            $method = 'get'.ucfirst($attribute);
            $val = isset($this->$attribute) ? $this->$attribute :
                ( method_exists(static::class(),$method) ? $this->$method() : null);
            if ($val) {
                $attrArr[] = ' `' . $attribute . '`' . '=' . '"' . mysqli_real_escape_string(self::getDb()::getInstance(),
                        $val) . '" ';
            }
        }
        $query .= implode(',', $attrArr);
        $primaryKey = $this->primaryKey();
        if (!isset($this->$primaryKey)) {
            die('Error: empty primary key. Table: ' . static::tableName());
        }
        $query .= ' WHERE ' . '`' . $primaryKey . '` = "' . $this->$primaryKey . '" ';

        return self::getDb()::query($query);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function delete()
    {
        $query = 'DELETE FROM `' . static::tableName() . '` ';
        $primaryKey = $this->primaryKey();
        if (!isset($this->$primaryKey)) {
            die('Error: empty primary key. Table: ' . static::tableName());
        }
        $query .= ' WHERE ' . '`' . $primaryKey . '` = "' . $this->$primaryKey . '" ';

        return self::getDb()::query($query);
    }


}