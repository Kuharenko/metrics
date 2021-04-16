<?php

require 'classes/DB.php';

class Metric
{
    private $ip_address;
    private $user_agent;
    private $view_date;
    private $page_url;
    private $views_count;

    private static $table = 'metrics';

    protected $columns = [
        'ip_address',
        'user_agent',
        'view_date',
        'page_url',
        'views_count'
    ];

    public function __construct(array $attributes)
    {
        foreach ($this->columns as $column) {
            $this->$column = $attributes[$column] ?? null;
        }
    }

    public static function sync(array $attributes): array
    {
        $conditions = [
            'ip_address' => $attributes['ip_address'],
            'user_agent' => $attributes['user_agent'],
            'page_url' => $attributes['page_url']
        ];

        $model = Metric::where($conditions);

        if ($model) {
            $model->setAttributes($attributes);
            $model->views_count = $model->views_count + 1;
            $model->update($conditions);
        } else {
            $model = new self($attributes);
            $model->views_count = 1;
            $model->save();
        }

        return $model->getAttributes();
    }

    public function save()
    {
        $params = array_map(function () {
            return '?';
        }, $this->getAttributes());

        $table = self::$table;
        $columns = implode(',', $this->columns);
        $params = implode(',', $params);

        $db = DB::getInstance();
        $connection = $db->getConnection();

        $sql = "insert into $table ($columns) values ($params)";
        $statement = $connection->prepare($sql);

        $types = "";
        foreach ($this->getAttributes() as $attribute => $value) {
            $types .= is_string($value) ? "s" : "i";
        }

        $statement->bind_param($types, ...array_values($this->getAttributes()));

        return $statement->execute();
    }

    public function update(array $conditions)
    {
        $table = self::$table;
        $result = self::buildWhereQuery($conditions);
        $query = $result['query'];
        $values = [];
        $types = "";

        foreach ($this->getAttributes() as $column => $value) {
            $types .= is_string($value) ? "s" : "i";
            $values[] = $column . '=?';
        }

        $types .= $result['types'];
        $values = implode(',', $values);

        $sql = "update $table set $values where $query";

        $db = DB::getInstance();
        $connection = $db->getConnection();

        $statement = $connection->prepare($sql);

        $statement->bind_param($types, ...array_merge(array_values($this->getAttributes()), array_values($conditions)));

        return $statement->execute();
    }

    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            if (isset($this->$attribute)) {
                $this->$attribute = $value;
            }
        }
    }

    public function getAttributes(): array
    {
        $attributes = [];
        foreach ($this->columns as $column) {
            $attributes[$column] = $this->$column;
        }

        return $attributes;
    }

    public static function where(array $params = []): ?Metric
    {
        $table = self::$table;
        $result = self::buildWhereQuery($params);
        $query = $result['query'];

        $db = DB::getInstance();
        $connection = $db->getConnection();

        $sql = "select * from $table where $query";
        $statement = $connection->prepare($sql);

        $statement->bind_param($result['types'], ...array_values($params));
        $statement->execute();

        $result = $statement->get_result();
        if ($result->num_rows) {
            $data = (array)$result->fetch_object();
            return new Metric($data);
        }

        return null;
    }

    private static function buildWhereQuery(array $params = [])
    {
        $query = "";
        $types = "";

        foreach ($params as $field => $value) {
            $types .= is_string($value) ? "s" : "i";
            $query .= " and " . $field . " =?";
        }

        $query = trim($query);
        if (strpos($query, 'and') === 0) {
            $query = substr($query, 4);
        }

        return [
            'types' => $types,
            'query' => $query
        ];
    }
}