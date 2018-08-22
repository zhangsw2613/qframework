<?php
namespace demo\models\users;

use qframework\base\Model;

/**
 * 驼峰命名方式对应表名user_info
 * Class UserInfo
 * @package demo\models\users
 */
class UserInfo extends Model
{

    public $table = 'test';

    protected function setAttribute()
    {
        /**
         * 1、设置关联：
         * 使用find自动获取关联表所有字段（例如：$result->comments()），
         * 同时可进行链式操作，如：$result->comments()->delete()
         * 2、添加触发器，[insert,delete,update]自动触发执行相应函数
         */
        $this->relation(Model::HAS_ONE,'test2_info',__NAMESPACE__.'\\test2','fd');
        $this->relation(Model::HAS_MANY,'test3_info',__NAMESPACE__.'\\test3','ed');
        $this->addTrigger('beforeInsert','testbefore');
        $this->addTrigger('beforeInsert','testbefore2');
        $this->addTrigger('beforeDelete','testbefore3');
    }

    public function testbefore()
    {
        $res =  self::query()->where(['name'=>'zsw'])->orWhere(['name'=>'zsw'])->find();
        $res1 =  UserInfo2::test();
    }

    public static function test()
    {
        $res = self::query()->alias("a")->where(['age'=>['between','2,121']])->where(['a.id'=>['>',1]])->where(['a.id'=>['<=',7]])->leftJoin("test2 t2 on a.id=t2.fd")->leftJoin("test3 t3 on a.id=t3.ed")->field('a.*,t2.shoot_screen AS t_shoot_screen,t3.title AS title_t3')->select();print_r($res);exit;
        self::query()->alias("a")->rightJoin("table2 on a.id=table2.uid")->field('a.*,table2.name AS t_name')->select();


        self::query()->beginTrans();
        self::query()->where(['id'=>['=',10]])->delete();
        self::query()->commit();exit;
        self::query()->beginTrans();
        self::query()->where(['id'=>['=',5]])->delete();
        self::query()->rollback();exit;
        $test14 = self::query()->exec("SELECT * FROM test WHERE id IN (:id1,:id2)",[':id1'=>5,':id2'=>6]);print_r($test14);exit;
        $res = self::query()->where(['id'=>['in','5,6,7']])->field('id,name')->field(['url','age'])->distinct('id')->select();
        $res = self::query()->where(['id'=>['in','5,6,7']])->order('id DESC')->select()->getRow(1);print_r($res);exit;
        self::query()->where(['id'=>['in','5,6,7']])->orWhere(['id'=>11])->increment('age',3);;exit;
        self::query()->where(['id'=>11])->update(['name'=>'tttt1111','url'=>'wwww1111']);exit;

        $res = self::query()
            ->where(['id'=>['<',15]])
            ->where(['name'=>['like','zsw']])
            ->where(['age'=>['between','2,121']])
            ->update(['name'=>'tttt','title'=>'aaaaaaa']);print_r($res);exit;
        $test2 = self::query()->where("dig_num=2")->field("id,name,age")->select();print_r($test2);exit;
        $res =  self::query()->where(['name'=>'zsw'])->orWhere(['name'=>'zsw'])->find(); print_r($res);exit;
        $test2 = self::query()->order("id DESC")->group('age')->having('count(age) >= 1')->limit(3)->field("id,name,age")->where([['id'=>['>',1]],['id'=>['<',5]],['name'=>'zsw']])->orWhere(['name'=>'ff'])->find();
        print_r($test2);exit;
        $res = self::query()->insert([['name'=>'test1','age'=>1,'url'=>'www.baidu1.com'],['name'=>'test2','age'=>2,'url'=>'www.baidu2.com']]);var_dump($res);exit;
        $res = self::query()->insert(['name'=>'test2','age'=>121,'url'=>'www.baidu1111.com']);var_dump($res);EXIT;
        $test2 = self::query()->where("dig_num=2")->field("id,name,age")->select();

        $res =  self::query()->where(['name'=>'zsw'])->orWhere(['name'=>'zsw'])->find();
        $res1 =  UserInfo2::test();print_r($res1);exit;
        print_r($test2);exit;
        $test2 = self::query()->order("id DESC")->group('age')->having('count(age) >= 1')->limit(3)->field("id,name,age")->where([['id'=>['>',1]],['id'=>['<',5]],['name'=>'zsw']])->orWhere(['name'=>'ff'])->find();
        $res1 =  UserInfo2::test();
        $res =  self::query()->where(['name'=>'zsw'])->orWhere(['name'=>'zsw'])->find();print_r($res);exit;

        self::query()->insert([['name'=>'test2','age'=>1],['name'=>'test2','age'=>1]]);exit;
        self::query()->where(['id'=>1])->update(['name'=>'tttt']);
        self::query()->where(['id'=>1])->increment('age');
        self::query()->where(['id'=>1])->increment('age',2);
        self::query()->where(['id'=>1])->decrement('age');
        self::query()->where(['id'=>1])->decrement('age',2);
        $test1 = self::query()->where(['name'=>'zsw'])->field("id,name,age")->find();
        $test2 = self::query()->where("name='zsw' and id <> 1")->field("id,name,age")->select()->getRow(1);
        $test3 = self::query()->where(['id'=>['>',3]])->field("id,name,age")->find();
        $test4 = self::query()->where(['name'=>'zsw'])->field("id,name,age")->find()->toArray();
        $test5 = self::query()->where(['name'=>'zsw'])->find();
        $test6 = self::query()->count();
        $test7 = self::query()->count("id");
        $test8 = self::query()->find();
        $test9 = self::query()->field("name")->distinct(true)->select();
        $test10 = self::query()->limit(3)->find();
        $test11 = self::query()->limit(3,5)->find();
        $test12 = self::query()->limit(3,5)->order("id DESC")->find();
        $test13 = self::query()->limit(3,5)->order("id")->orderBy("id DESC")->find();
        self::query()->alias("a")->join("table2 t on a.id=t.uid")->field('a.*,t.name AS t_name')->select();
        self::query()->alias("a")->rightJoin("table2 on a.id=table2.uid")->field('a.*,table2.name AS t_name')->select();
        self::query()->alias("a")->leftJoin("table2 on a.id=table2.uid")->field('a.*,table2.name AS t_name')->select();
        print_r($parms);
    }
    public static function teststatic()
    {
        echo 12;
    }
}