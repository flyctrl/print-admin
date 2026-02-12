<?php

namespace app\api\model;

class LookUpModel extends BaseModel
{
    protected $name='look_up';


    static function look_up($user_id){

        $list=self::where('user_id',$user_id)->field('id,name')->select();
        return $list;


    }
}