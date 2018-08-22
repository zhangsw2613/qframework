<?php
namespace demo\models\users;

use qframework\base\Model;

/**
 * 驼峰命名方式对应表名user_info
 * Class UserInfo
 * @package demo\models\users
 */
class Test2 extends Model
{

    public $table = 'test2';

    protected function setAttribute(){}

    public static function test()
    {
        $res = self::query()->where(['name'=>'zsw'])->find();
        return $res;


    }

}