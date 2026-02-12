<?php

namespace app\api\model;

class UserTicketModel extends BaseModel
{
    protected $name='user_ticket';


    static function user_ticket($user_id,$type,$ticket_type){
        $map['type']=$ticket_type;
        if ($type==0){
            $map['status']=['=','0'];
            $map['end_time']=['>',time()];
        }else{
            $map['status']=['>',0];
        }
        $list=self::field('id,ticket_name,limitation_price,deduction_price,star_time,end_time,status,type,discount')
            ->where('user_id',$user_id)
            ->where($map)
            ->select();

        foreach ($list as $item){
            $item['star_time']=date('Y-m-d',$item['star_time']);
            $item['end_time']=date('Y-m-d',$item['end_time']);
        }
        return $list;


    }
}