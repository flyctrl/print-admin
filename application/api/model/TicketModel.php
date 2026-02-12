<?php

namespace app\api\model;

use app\api\model\BaseModel;

class TicketModel extends BaseModel
{
    protected $name='ticket';


    static function ticket_list()
    {
        $list=self::where('end_time','>',time())
            ->select();
        foreach ($list as $value){
            $value['end_time']=date('Y-m-d',$value['end_time']);
            $value['create_time']=date('Y-m-d',$value['create_time']);
        }
        return $list;
    }
}