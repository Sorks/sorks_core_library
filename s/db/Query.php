<?php

namespace s\db;

use s\Db;
use PDO;

class Query
{
    /**
     * @var string $connection  当前数据库连接对象
     * @var string $table       当前表名称
     * @var string $field       查询字段
     * @var string $join        关联表
     * @var array  $where       where条件
     * @var string $group       group条件
     * @var string $having      having条件
     * @var string $order       排序条件
     * @var string $limit       结果条数条件
     * @var string $data        插入|修改data数据
     * @var string $selectSql   查询语句准备sql
     * @var string $insertSql   插入语句准备sql
     * @var string $updateSql   修改语句准备sql
     * @var string $deleteSql   删除语句准备sql
     * @var string $viewSql     查看准备好的sql语句
     * @var string $resultSet   结果集返回格式
     */
    protected $connection;
    protected $table        = '';
    protected $field        = '*';
    protected $join         = '';
    protected $where        = '';
    protected $group        = '';
    protected $having       = '';
    protected $order        = '';
    protected $limit        = '';
    protected $data   = '';
    protected $selectSql    = 'select {#field#} from {#table#}{#join#}{#where#}{#group#}{#having#}{#order#}{#limit#}';
    protected $insertSql    = 'insert into {#table#}({#field#}) values{#data#}';
    protected $updateSql    = 'update {#table#} set {#data#} {#where#}';
    protected $deleteSql    = 'delete from {#table#} {#where#}';
    protected $viewSql      = false;
    protected $resultSet    = [
        'array'     => PDO::FETCH_ASSOC,
        'object'    => PDO::FETCH_CLASS 
    ];

    public function __construct($connection = null) {
        if (!is_null($connection)) {
            $this->connection = $connection;
        } else {
            $this->connection = Db::connect();
        }
    }

    /**
     * query 原生sql语句查询
     * @param   string $sql sql语句
     * @param   string $res 结果集返回数据格式
     * @return  结果集或产生的sql语句
     */
    public function query($sql = '', $res = 'array')
    {
        $stmt = $this->connection->query($sql);
        return $stmt->fetchAll($this->resultSet[$res]);
    }

    /**
     * find 单条数据查询
     * @param   string $res 结果集返回数据格式
     * @return  结果集或产生的sql语句
     */
    public function find($res = 'array')
    {
        $this->praseWhere();
        $this->praseSql();
        $stmt = $this->connection->query($this->selectSql);
        if (!$this->viewSql) {
            return $stmt->fetch($this->resultSet[$res]);
        }
        return $stmt->queryString.' limit 1';
    }

    /**
     * select 数据查询
     * @param   string $res 结果集返回数据格式
     * @return  结果集或产生的sql语句
     */
    public function select($res = 'array')
    {
        $this->praseWhere();
        $this->praseSql();
        $stmt = $this->connection->query($this->selectSql);
        if (!$this->viewSql) {
            return $stmt->fetchAll($this->resultSet[$res]);
        }
        return $stmt->queryString;
    }

    /**
     * insert 单条数据插入
     * @param   array $data 要插入的数据
     * @param   bool  $getLastId 最后插入ID
     * @return  结果、最后插入ID或产生的sql语句
     */
    public function insert($data = [], $getLastId = false)
    {
        $this->data($data, 'insert');
        $this->praseSql('insert');
        $stmt = $this->connection->prepare($this->insertSql);
        if (!$this->viewSql) {
            $res = $stmt->execute();
            if ($res && $getLastId) {
                return $this->connection->lastInsertId();
            } else {
                return $res;
            }
        }
        return $stmt->queryString;
    }

    /**
     * insertAll 批量插入
     * @param   array $data 要插入的数据
     * @return  结果或产生的sql语句
     */
    public function insertAll($data = [])
    {
        $this->data($data, 'insertAll');
        $this->praseSql('insert');
        $stmt = $this->connection->prepare($this->insertSql);
        if (!$this->viewSql) {
            return $stmt->execute();
        }
        return $stmt->queryString;
    }

     /**
     * update   修改写入
     * @param   array $data 要写入的数据
     * @return  结果或产生的sql语句
     */
    public function update($data = [])
    {
        $this->data($data, 'update');
        $this->praseWhere();
        $this->praseSql('update');
        $stmt = $this->connection->prepare($this->updateSql);
        if (!$this->viewSql) {
            return $stmt->execute();
        }
        return $stmt->queryString;
    }

    /**
     * delete   删除
     * @return  结果或产生的sql语句
     */
    public function delete()
    {
        $this->praseWhere();
        $this->praseSql('delete');
        $stmt = $this->connection->prepare($this->deleteSql);
        if (!$this->viewSql) {
            return $stmt->execute();
        }
        return $stmt->queryString;
    }

    /**
     * 指定当前操作的数据表
     * @param string $table 表名
     */
    public function table($table = '')
    {
        $this->table = $table;
        return $this;
    }

    /**
     * 指定当前操作的数据表字段
     * @param string $field 字段
     */
    public function field($field = '*')
    {
        $this->field = $field ?? '*';
        return $this;
    }

    /**
     * 关联查询
     * @param string $join 关联表
     * @param string $condition 关联条件
     * @param string $type 关联方式
     */
    public function join($join, $condition, $type = 'left')
    {
        $this->join = ' '.$type.' join '.$join.' on '.$condition;
        return $this;
    }

    /**
     * @param string|array              $key         [只传入key时 是字符串或数组条件 传入另外两个参数时 是条件的字段]
     * @param string                    $operator    [逻辑运算符]
     * @param string|float|int|bool     $val         [值]
     */
    public function where($key = null, $operator = null, $val = null)
    {
        if (is_null($operator)) {
            switch (is_array($key)) {
                case true:
                    foreach ($key as $v) {
                        if (is_string($v[2])) {
                            $this->where .= ' and '.$v[0].' '.$v[1].' "'.$v[2].'"';
                        } else {
                            $this->where .= ' and '.$v[0].' '.$v[1].' '.$v[2];
                        }
                    }
                break;
                case false:
                    $this->where .= ' and '.$key;
                break;
            }
        } else {
            if (is_string($val)) {
                $this->where .= ' and '.$key.' '.$operator.' "'.$val.'"';
            } else {
                $this->where .= ' and '.$key.' '.$operator.' '.$val;
            }
        }
        return $this;
    }

    /**
     * @param string|array              $key         [只传入key时 是字符串或数组条件 传入另外两个参数时 是条件的字段]
     * @param string                    $operator    [逻辑运算符]
     * @param string|float|int|bool     $val         [值]
     */
    public function whereOr($key, $operator = null, $val = null)
    {
        if (is_null($operator)) {
            switch (is_array($key)) {
                case true:
                    foreach ($key as $v) {
                        if (is_string($v[2])) {
                            $this->where .= ' or '.$v[0].' '.$v[1].' "'.$v[2].'"';
                        } else {
                            $this->where .= ' or '.$v[0].' '.$v[1].' '.$v[2];
                        }
                    }
                break;
                case false:
                    $this->where .= ' or '.$key;
                break;
            }
        } else {
            if (is_string($val)) {
                $this->where .= ' or '.$key.' '.$operator.' "'.$val.'"';
            } else {
                $this->where .= ' or '.$key.' '.$operator.' '.$val;
            }
        }
        return $this;
    }

    /**
     * 分组条件
     * @param string $group 分组条件
     */
    public function group($group = null)
    {
        if (!is_null($group)) {
            $this->group = 'group by '.$group;
        }
        return $this;
    }

    /**
     * 排序条件
     * @param string $key 排序字段或条件
     * @param string $pos 正序或倒序
     */
    public function order($key = null, $pos = null)
    {
        if (!is_null($key)) {
            if (is_null($pos)) {
                $this->order = ' order by '.$key;
            } else {
                $this->order = ' order by '.$key.' '.$pos;
            }
        }
        return $this;
    }

    /**
     * 指定条数查询
     * @param int|string $numStart 开始条数或条数条件
     * @param int        $num       查询条数
     */
    public function limit($numStart = 0, $num = null)
    {
        if (is_null($num)) {
            $this->limit = ' limit '.$numStart;
        } else {
            $this->limit = ' limit '.$numStart.','.$num;
        }
        return $this;
    }

    /**
     * 分页方法
     * @param int $page  页码
     * @param int $limit 每页查询条数
     */
    public function page($page = 1, $limit = 10)
    {
        $start = ($page - 1) * $limit;
        $this->limit = ' limit '.$start.','.$limit;
        return $this;
    }

    /**
     * 分组后查询条件
     * @param string|array              $key         [只传入key时 是字符串或数组条件 传入另外两个参数时 是条件的字段]
     * @param string                    $operator    [逻辑运算符]
     * @param string|float|int|bool     $val         [值]
     */
    public function having($key = null, $operator = null, $val = null)
    {
        if (is_null($operator)) {
            switch (is_array($key)) {
                case true:
                    $having = '';
                    foreach ($key as $v) {
                        $having .= ' and '.$v[0].' '.$v[1].' '.$v[2];
                    }
                    $having = substr($having, strpos($this->where, ' and') + 4);
                break;
                case false:
                    $having = $key;
                break;
            }
        } else {
            $having = $key.' '.$operator.' '.$val;
        }
        $this->having = ' having '.$having;
        return $this;
    }

    /**
     * 查看产生的sql语句
     */
    public function viewSql()
    {
        $this->viewSql = true;
        return $this;
    }

    /**
     * 开启事务
     */
    public function transaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * 事务回滚
     */
    public function rollBack()
    {
        return $this->connection->rollBack();
    }
    
    /**
     * 准备写入的数据
     */
    private function data($data, $type = 'insert')
    {
        switch ($type) {
            case 'insert':
                $this->field = implode(',', array_keys($data));
                $prepareData = '';
                foreach ($data as $v) {
                    if (!is_string($v)) {
                        $prepareData .= $v;
                    } else {
                        $prepareData .= '"'.$v.'"';
                    }
                }
                $this->data  = '('.$prepareData.')';
            break;
            case 'insertAll':
                $this->field = implode(',', array_keys($data[0]));
                $prepareData = '';
                foreach ($data as $v) {
                    $prepareData .= '(';
                    foreach ($v as $val) {
                        if (!is_string($val)) {
                            $prepareData .= $val;
                        } else {
                            $prepareData .= '"'.$val.'"';
                        }
                    }
                    $prepareData .= '),';
                }
                $this->data = trim($prepareData, ',');
            break;
            case 'update':
                $prepareData = '';
                foreach ($data as $k=>$v) {
                    $prepareData .= $k.'='.$v.',';
                }
                $this->data = trim($prepareData, ',');
            break;
        }
        return $this;
    }

    /**
     * 准备where语句
     */
    private function praseWhere()
    {
        if ($this->where != '') {
            $this->where = ' where '.substr($this->where, strpos($this->where, ' and') + 4);
        }
        return $this;
    }

    /**
     * 准备sql语句
     */
    private function praseSql($type = 'select')
    {
        switch ($type) {
            case 'select':
                $this->selectSql = str_replace('{#table#}', $this->table, $this->selectSql);
                $this->selectSql = str_replace('{#field#}', $this->field ?: '*', $this->selectSql);
                $this->selectSql = str_replace('{#join#}', $this->join, $this->selectSql);
                $this->selectSql = str_replace('{#where#}', $this->where, $this->selectSql);
                $this->selectSql = str_replace('{#group#}', $this->group, $this->selectSql);
                $this->selectSql = str_replace('{#having#}', $this->having, $this->selectSql);
                $this->selectSql = str_replace('{#order#}', $this->order, $this->selectSql);
                $this->selectSql = str_replace('{#limit#}', $this->limit, $this->selectSql);
            break;
            case 'insert':
                 $this->insertSql = str_replace('{#table#}', $this->table, $this->insertSql);
                 $this->insertSql = str_replace('{#field#}', $this->field, $this->insertSql);
                 $this->insertSql = str_replace('{#data#}', $this->data, $this->insertSql);
            break;
            case 'update':
                $this->updateSql = str_replace('{#table#}', $this->table, $this->updateSql);
                $this->updateSql = str_replace('{#data#}', $this->data, $this->updateSql);
                $this->updateSql = str_replace('{#where#}', $this->where, $this->updateSql);
            break;
            case 'delete':
                $this->deleteSql = str_replace('{#table#}', $this->table, $this->deleteSql);
                $this->deleteSql = str_replace('{#where#}', $this->where, $this->deleteSql);
            break;
        }
        return $this;
    }
}
