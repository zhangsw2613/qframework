<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */

namespace qframework\database;

use qframework;
use qframework\database\DatabaseException;


/**
 * 结果集
 * Class DataSet
 * @package qframework\database
 */
class DataSet extends \ArrayIterator
{

    private $rowCount = 0;

    public function add($entity)
    {
        parent::append($entity);
        $this->rowCount++;
    }

    /**
     * 获取数据总数
     * @return int
     */
    public function rowCount()
    {
        return $this->rowCount;
    }

    /**
     * 获取某一字段的所有数据
     * @param string $column_name 列名
     * @return array
     */
    public function getColumn($column_name = '')
    {
        $result = [];
        $this->rewind();
        while ($this->valid()) {
            $arr = $this->current();
            $result[] = $arr[$column_name];
            $this->next();
        }
        return $result;
    }

    /**
     * 获取某一列的数据,使用时从1开始
     * @param int $row_no
     * @return array
     */
    public function getRow(int $row_no = 1)
    {
        --$row_no;
        if ($this->rowCount <= $row_no) {
            return [];
        }
        $this->seek($row_no);
        $result = $this->current();
        return $result->toArray();
    }
}