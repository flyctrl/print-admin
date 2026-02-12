<?php

namespace app\api\controller;

use app\api\controller\BaseController;
use app\api\model\CartModel;
use app\api\model\OrderDetailModel;
use app\api\model\OrderModel;
use app\api\model\UserModel;
use app\api\model\UserWithdrawalModel;
use think\Db;
use think\Exception;

class TaskController extends BaseController
{
    public function del_file()
    {
        $startDate = strtotime("-10 days");

        $order = OrderModel::where('pay_status', 'in', '0,2,3')->limit(300)->where('delete',0)->where('create_time', '<', $startDate)->select();
        Db::startTrans();
        try {
            foreach ($order as $value) {
                
                $order_detail = OrderDetailModel::where('order_id', $value['id'])->select();

                foreach ($order_detail as $item) {
                    if ($item['information'] != 1) {
                         @unlink('.' . $item['file']);
                    }
                    @unlink('.'.$item['template_file']);
                }
                OrderModel::update(['delete'=>1],['id'=>$value['id']]);
                if ($value['pay_status'] == 0) {
                    OrderModel::where('id', $value['id'])->delete();
                    OrderDetailModel::where('order_id', $value['id'])->delete();
                } else {

                   @unlink('.' . $value['zip_file']);
                }
               

            }
            Db::commit();
            echo 1;
        } catch (Exception $exception) {


        }


    }


    public function cart_del()
    {
        $startDate = strtotime("-10 days");

        $cart = CartModel::where('delete', 0)->limit(300)->where('create_time', '<', $startDate)->select();
        Db::startTrans();
        try {
            foreach ($cart as $value) {
                @unlink('.' . $value['file']);
                @unlink('.' . $value['source_file']);
                CartModel::destroy(['id' => $value['id']]);

            }
            Db::commit();
            echo 1;
        } catch (Exception $exception) {


        }
    }


    public function transfer()
    {
        $list=UserWithdrawalModel::where('status', 4)->select();
        $wechat=new WechatController();
        foreach ($list as $value) {
            $result = $wechat->query_transfer($value['order_num']);
            if (!empty($result['code'])){
                if ($result['code']==40000){
                    continue;
                }
            }
            if ($result['state'] == 'SUCCESS'){
                UserWithdrawalModel::update(['status'=>1],['id'=>$value['id']]);
            }
            if ($result['state'] == 'FAIL'){
                UserWithdrawalModel::update(['status'=>2],['id'=>$value['id']]);
                UserModel::where('id',$value['user_id'])->setInc('balance',$value['price']);
            }

        }

    }
}