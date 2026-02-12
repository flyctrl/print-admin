<?php

namespace app\api\model;

class UserBalanceRecordModel extends BaseModel
{
    protected $name='user_balance_record';




    static function record_list($user_id,$page,$type){
        $list=self::where('user_id',$user_id)
            ->where('type',$type)
            ->order('create_time desc')
            ->paginate(10,false,['page'=>$page])
            ->each(function ($item){
                $item['create_time']=date('Y-m-d H:i',$item['create_time']);
                if ($item['avatar_image']){
                    $item['avatar_image']=cdnurl($item['avatar_image'],true);
                }

            });
        return $list;
    }
}