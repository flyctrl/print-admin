<?php

namespace app\api\model;

class OrderDetailModel extends BaseModel
{
    protected $name='order_detail';


    static function order_detail($id){
        $detail=self::where('order_id',$id)->select();

        foreach ($detail as $value){
            $value['file']=cdnurl($value['file'],true);
        }

        return $detail;
    }
}