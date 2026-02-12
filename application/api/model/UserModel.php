<?php

namespace app\api\model;

class UserModel extends BaseModel
{
    protected $name='user';



    static function user_info($user_id){
        $user=self::where('id',$user_id)
            ->field('id,avatar_image,nickname,balance,integral,type,total_price,give_price,id_card,name,phone')
            ->find();
        return $user;
    }
}