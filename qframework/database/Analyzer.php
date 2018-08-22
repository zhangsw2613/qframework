<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\database;

/**
 * 分析器
 * Class Analyzer
 * @package qframework\database
 */
class Analyzer
{

    private $compare = ['=', '>=', '<=', '>', '<', '<>', 'IN', 'LIKE', 'NOT IN', 'BETWEEN', 'NOT LIKE', 'NOT BETWEEN'];
    private $joinType = ['JOIN', 'INNER JOIN', 'LEFT JOIN', 'RIGHT JOIN'];

    /**
     * 查询参数
     * @var array
     */
    private $options = [];

    /**
     * 绑定参数
     * @var array
     */
    private $binds = [];

    private $table = '';
    private $sql = '';

    public function __construct($table = '')
    {
        $this->table = $table;
    }

    public function sql()
    {
        return $this->sql;
    }

    public function binds()
    {
        return $this->binds;
    }

    public function options()
    {
        return $this->options;
    }

    /**
     * 增加查询条件 ['where']['AND']['field']['op']['value']
     * @param mixed $expression
     * @param string $op
     * @return $this
     * @throws ModelException
     */
    public function addWhere($expression, $op = 'AND')
    {
        if (is_string($expression)) {
            $this->options['where'][$op][] = $expression;
        } elseif (is_array($expression)) {
            if (1 == count($expression)) {
                $expression = [$expression];
            }
            foreach ($expression as $exp) {
                $field = array_keys($exp)[0];
                $compare = '=';
                $condition = array_shift($exp);
                if (is_array($condition)) {
                    $compare = strtoupper(array_shift($condition));
                    if (!in_array($compare, $this->compare)) {
                        throw new ModelException("不支持的比较运算符{$compare}");
                    }
                    $condition = array_shift($condition);
                }
                $value = $condition;
                $this->options['where'][$op][$field][] = [$compare, $value];
            }
        }
        return $this;
    }

    public function addFields($fields)
    {
        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }
        $this->options['fields'] = implode(',', array_filter([$this->options['fields'], $fields]));
    }

    public function addLimit(int $limit, int $length)
    {
        $this->options['limit'] = $limit . ($length ? ',' . $length : '');
    }

    public function addGroup($group)
    {
        $this->options['group'] = $group;
    }

    public function addHaving($having)
    {
        $this->options['having'] = $having;
    }

    public function addOrder($order)
    {
        $this->options['order'] = $order;
    }

    public function addDistinct($distinct)
    {
        $this->options['distinct'] = "distinct({$distinct})";
    }

    public function addAlias($alias)
    {
        $this->options['alias'] = $alias;
    }

    public function addJoin($join, $type)
    {
        if (!in_array($type, $this->joinType)) {
            throw new ModelException("不支持改{$type}连表方式");
        }
        $this->options[$type][] = $join;
    }

    /**
     * 清空本次查询数据
     * @return $this
     */
    public function clean()
    {
        $this->options = [];
        $this->binds = [];
        $this->sql = '';
        return $this;
    }

    /**
     * 解析query语句
     * @return $this
     */
    public function parseQuerySql()
    {
        $sql = "SELECT %s FROM %s %s %s %s %s %s";
        $this->sql = sprintf($sql, $this->_getFields(),
            $this->_getTable(),
            $this->_getJoin(),
            $this->_getWhere(),
            $this->_getGroupBy(),
            $this->_getOrderBy(),
            $this->_getLimit());
        return $this;
    }

    /**
     * 解析insert语句
     * @param array $fields
     * @return $this
     */
    public function parseInsertSql($fields = [])
    {
        $sql = "INSERT INTO %s (%s) VALUE %s";
        if (is_string(key($fields))) {
            $fields = [$fields];
        }
        $insertFields = array_keys($fields[0]);
        $insertArr = [];
        foreach ($fields as $index => $value) {
            $i = '(';
            $t = [];
            foreach ($value as $fc => $vc) {
                $fk = ':' . $fc . $index;
                $t[] = $fk;
                $this->binds[$fk] = $vc;
            }
            $i .= implode(',', $t);
            $i .= ')';
            $insertArr[] = $i;
        }
        $this->sql = sprintf($sql, $this->_getTable(),
            implode(',', $insertFields),
            implode(',', $insertArr));
        return $this;
    }

    public function parseDeleteSql()
    {
        $sql = "DELETE FROM %s %s %s";
        $this->sql = sprintf($sql, $this->_getTable(),
            $this->_getWhere(),
            $this->_getLimit());
        return $this;
    }

    public function parseUpdateSql($fields = [])
    {
        $sql = "UPDATE %s SET %s %s %s";
        $updateArr = [];
        foreach ($fields as $index => $value) {
            $cp = '=';
            $field = ':' . $index . 'upd';
            if (is_array($value)) {
                switch ($value[0]) {
                    case 'INC':
                        $cp .= ' ' . $index . '+' . $value[1];
                        break;
                    case 'DEC':
                        $cp .= ' ' . $index . '-' . $value[1];
                        break;
                }
            } else {
                $cp .= $field;
                $this->binds[$field] = $value;
            }
            $updateArr[] = $index . $cp;
        }
        $this->sql = sprintf($sql, $this->_getTable(),
            implode(',', $updateArr),
            $this->_getWhere(),
            $this->_getLimit());
        return $this;
    }

    public function _getTable()
    {
        return $this->table . (isset($this->options['alias']) ? ' ' . $this->options['alias'] : '');
    }

    private function _getFields()
    {
        return (isset($this->options['distinct']) ? $this->options['distinct'] . ',' : '') . $this->options['fields'] ?? '*';
    }

    private function _getJoin()
    {
        $joins = '';
        foreach ($this->joinType as $type) {
            if (isset($this->options[$type])) {
                $joins .= ' ' . $type . ' ' . implode(' ' . $type . ' ', $this->options[$type]) . ' ';
            }
        }
        return $joins;
    }

    private function _getWhere()
    {
        $where = array_reduce(['AND', 'OR'], function ($carry, $item) {
            foreach ($this->options['where'][$item] as $key => $condition) {
                if (5 < strlen($carry)) {
                    $carry .= $item;
                }
                if (is_numeric($key)) {
                    $carry .= ' (' . $condition . ') ';
                } elseif (is_string($key)) {
                    $carry .= $this->_parseStringCondition($key, $item, $condition);
                }
            }
            return $carry;
        }, "WHERE");
        if (5 == strlen($where)) {
            $where .= " 1=1 ";
        }
        return $where;
    }

    private function _getGroupBy()
    {
        if (isset($this->options['group'])) {
            return 'GROUP BY ' . $this->options['group'] . (isset($this->options['having']) ? ' HAVING ' . $this->options['having'] : '') . ' ';
        }
    }

    private function _getOrderBy()
    {
        if (isset($this->options['order'])) {
            return 'ORDER BY ' . $this->options['order'] . ' ';
        }

    }

    private function _getLimit()
    {
        if (isset($this->options['limit'])) {
            return 'LIMIT ' . $this->options['limit'];
        }
    }

    private function _parseStringCondition($key, $item, $condition)
    {
        $tmp_w = [];
        foreach ($condition as $idx => $value) {
            $field = $key . ($idx ? $idx : '');
            if (isset($this->options['alias'])) {
                $field = str_replace($this->options['alias'] . '.', '', $field);
            }
            if (isset($this->binds[':' . $field])) {
                $field = $key . $idx;
            }
            $cp = $value[0];
            $va = $value[1];
            if (in_array($cp, ['=', '>=', '<=', '>', '<', '<>'])) {
                $tmp_w[] = ' ' . $key . ' ' . $cp . ' ' . ':' . $field . ' ';
                $this->binds[':' . $field] = $va;
            } elseif (in_array($cp, ['IN', 'NOT IN'])) {
                $vs = explode(',', $value[1]);
                $fd = [];
                foreach ($vs as $ev) {
                    $tmp_fd = ':' . $field . $ev;
                    $this->binds[$tmp_fd] = $ev;
                    $fd[] = $tmp_fd;
                }
                $tmp_w[] = ' ' . $key . ' ' . $cp . ' (' . implode(',', $fd) . ') ';
            } elseif (in_array($cp, ['LIKE', 'NOT LIKE'])) {
                $tmp_w[] = ' ' . $key . ' ' . $cp . ' ' . ':' . $field . ' ';
                $this->binds[':' . $field] = '%' . $va . '%';
            } elseif (in_array($cp, ['BETWEEN', 'NOT BETWEEN'])) {
                $vs = explode(',', $value[1]);
                $fd = [];
                foreach ($vs as $ev) {
                    $tmp_fd = ':' . $field . $ev;
                    $this->binds[$tmp_fd] = $ev;
                    $fd[] = $tmp_fd;
                }
                $tmp_w[] = ' ' . $key . ' ' . $cp . ' ' . implode(' AND ', $fd) . ' ';
            }

        }
        return implode($item, $tmp_w);
    }


}