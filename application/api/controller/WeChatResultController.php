<?php

namespace app\api\controller;

use app\api\model\EquipmentModel;
use app\api\model\OrderModel;

use app\api\model\PaymentModel;
use app\api\model\SeizeOrdersModel;

use app\api\model\UserBalanceRecordModel;
use app\api\model\UserModel;
use think\Controller;
use Yansongda\Pay\Pay;

class WeChatResultController extends Controller
{
    public function order()
    {

        $wx_pay = new PayController();
        $result = Pay::wechat($wx_pay->processConfig('order'))->callback();
        $data = json_decode($result, true);
        error_log('通信错误:' . print_r($data, true), 3, dirname(__FILE__) . '/notify_order.log');

        if ($data['resource']['ciphertext']['trade_state'] == 'SUCCESS') {
            $out_trade_no = $data['resource']['ciphertext']['out_trade_no'];
            $order = OrderModel::get(['order_num' => $out_trade_no]);
            if ($order['pay_status'] == '1') {
                return Pay::wechat()->success();
            }
            $user = UserModel::get($order['user_id']);
            OrderModel::update(['transaction_id' => $data['resource']['ciphertext']['transaction_id'], 'pay_status' => '1', 'pay_time' => time()], ['order_num' => $out_trade_no]);


        }
        return json(['code' => 'SUCCESS', 'message' => '成功']);
    }


    public function payment()
    {

        $wx_pay = new PayController();
        $result = Pay::wechat($wx_pay->processConfig('payment'))->callback();
        $data = json_decode($result, true);
        error_log('通信错误:' . print_r($data, true), 3, dirname(__FILE__) . '/notify_order.log');

        if ($data['resource']['ciphertext']['trade_state'] == 'SUCCESS') {
            $out_trade_no = $data['resource']['ciphertext']['out_trade_no'];
            $payment = PaymentModel::get(['payment_sn' => $out_trade_no]);
            if ($payment['status'] == '1') {
                return Pay::wechat()->success();
            }
            PaymentModel::update(['transaction_id' => $data['resource']['ciphertext']['transaction_id'], 'status' => '1', 'pay_time' => time()], ['payment_sn' => $out_trade_no]);
            OrderModel::update(['transaction_id' => $data['resource']['ciphertext']['transaction_id'], 'pay_status' => '1', 'pay_time' => time()], ['payment_id' => $payment['id']]);


        }
        return json(['code' => 'SUCCESS', 'message' => '成功']);
    }
}