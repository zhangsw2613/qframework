<?php
namespace demo\models\users;

use qframework\base\Model;

/**
 * 驼峰命名方式对应表名user_info
 * Class UserInfo
 * @package demo\models\users
 */
class UserInfo2 extends Model
{

    public $table = 'users';

    protected function setAttribute(){}

    public static function test()
    {
        $res = self::query()->where(['name'=>'aaa'])->find();
        return $res;


    }

}