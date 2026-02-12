<?php

namespace app\api\model;

class CartModel extends BaseModel
{
    protected $name='cart';



    static function cart_list($user_id){

        $list=self::where('user_id',$user_id)
            ->order('create_time','desc')
            ->select();
        $result=[];
        foreach ($list as $key=>&$value){
            $user_document=UserDocumentModel::where('user_id',$value['user_id'])->where('cart_id',$value['id'])->find();
            if ($user_document){
                $value['user_document']=1;
            }else{
                $value['user_document']=0;
            }
            if (!is_file('.'.$value['file'])){
                unset($list[$key]);
            }else{
                $value['file']=cdnurl($value['file'],true);
                array_push($result,$value);
            }


        }
        return $result;
    }



    static function order_cart($ids){
        $list=self::where('id','in',$ids)
            ->select();
        return $list;




    }
}