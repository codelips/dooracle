<?php

namespace Doooracle;

/**
 * 基础模型器
 */
class Model
{
    protected        $tableName = null;
    protected static $callClass = null;
    protected static $table     = null;

    public function __construct()
    {
        if (empty($this->tableName)) {
            $class           = get_class($this);
            $classname       = substr($class, strrpos($class, '\\') + 1);
            $this->tableName = config('database.prefix') .
                strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $classname));
        }
    }

    protected static function db()
    {
        return Dooracle::getIntance();
    }

    public static function getTableName()
    {
        $callClass = get_called_class();
        if (self::$callClass !== $callClass) {
            $classname       = substr($callClass, strrpos($callClass, '\\') + 1);
            $table           = config('database.prefix') .
                strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $classname));
            self::$callClass = $callClass;
            self::$table     = $table;
        }
        return self::$table;
    }

    /**
     * 添加
     */
    public function add($opt)
    {
        $result = self::save($opt);
        if (null === $result) {
            $last_id = self::find(['ORDER' => ['id' => 'DESC']])['id'];
            return [1, "添加成功", $last_id];
        }
        return [0, self::db()->error()[2]];
    }

    /**
     * 修改
     */
    public function modify($opt, $id)
    {
        $result = self::update(['id' => $id], $opt);
        if (null === $result) {
            return [1, "更新成功"];
        }
        return [0, self::db()->error()[2]];
    }

    /**
     * 删除
     */
    public function remove($id)
    {
        $result = self::destroy($id);
        if (null === $result) {
            return [1, "删除成功"];
        }
        return [0, self::db()->error()[2]];
    }


    public function count(Array $where = [], $column = '*')
    {
        self::db()->count(self::getTableName(), $column, $where);
    }

    /**
     * 根据ID查询
     */
    public function getById($id)
    {
        return (Object)self::find(['id' => $id]);
    }

    public function getAll(Array $where = [])
    {
        return self::db()->select(self::getTableName(), '*', array_merge($where));
    }

    public static function find(Array $where = [], $field = '*')
    {
        $rs = self::db()->select(self::getTableName(), $field, array_merge(['LIMIT' => 1], $where));
        return count($rs) > 0 ? $rs[0] : null;
    }

    public static function select(Array $where = [], $field = '*')
    {
        return self::db()->select(self::getTableName(), $field, array_merge($where));
    }

    public static function save(Array $data = [])
    {
        $rs = self::db()->insert(self::getTableName(), $data);
        return $rs->errorInfo()[1];
    }

    public function getLastInsID()
    {
        return self::db()->id();
    }

    public static function update(Array $where = [], Array $data = [], $field = null)
    {
        $insertData = [];
        if ($field !== null) {
            if (is_string($field)) {
                $field = explode(',', $field);
            }
            foreach ($data as $key => $val) {
                if (in_array($key, $field)) {
                    $insertData[$key] = $val;
                }
            }
        }
        $data = empty($insertData) ? $data : $insertData;
        $rs   = self::db()->update(self::getTableName(), $data, $where);
        return $rs->errorInfo()[1];
    }

    public static function destroy($data)
    {
        if (empty($data) && 0 !== $data) {
            return 0;
        }
        if (!is_array($data)) {
            $data = ['id' => $data];
        }
        $rs = self::db()->delete(self::getTableName(), $data);
        return $rs->errorInfo()[1];
    }

    public function browse($page, $size, Array $where = [], Array $order = [], $fields = '*')
    {

        $where        = array_merge($where, $this->_where());
        $obj          = [];
        $obj['count'] = self::db()->count(self::getTableName(), $where);
        $obj['size']  = $size ?: 10;
        $obj['page']  = $page ?: 1;
        $obj['list']  = self::db()->select(
            self::getTableName(),
            $fields,
            array_merge(
                $where,
                [
                    'LIMIT' => [\Dooracle\helper\pagination_start($obj['count'], $obj['size'], $obj['page']), $obj['size']],
                    'ORDER' => $order ?: ['id' => 'DESC']
                ]
            )
        );
        return $obj;

    }

    public function _where()
    {
        $where = [];
        if (input('get.key/a')) {
            foreach (input('get.key/a') as $k => $v) {
                if (!empty($v)) {
                    switch ($k) {
                        case "title":
                            $where["{$k}[~]"] = $v;
                            break;
                        case "cid":
                            $where["{$k}"] = $v;
                            break;
                        case "date":
                            $applydate         = explode("--", $v);
                            $start             = date('Y-m-d H:i:s', strtotime($applydate[0]));
                            $end               = date('Y-m-d H:i:s', strtotime($applydate[1]));
                            $where['date[>=]'] = \Medoo\Medoo::raw("TO_DATE(':rawDate','YYYY-MM-DD HH24:MI:SS')", ['rawDate' => $start]);
                            $where['date[<=]'] = \Medoo\Medoo::raw("TO_DATE(':rawDate','YYYY-MM-DD HH24:MI:SS')", ['rawDate' => $end]);
                            break;
                    }
                }
            }
        }
        return $where;
    }

}