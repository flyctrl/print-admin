<?php

namespace app\api\model;

class OrderModel extends BaseModel
{
    protected $name = 'order';

    static function order_list($page, $user_id, $type)
    {
        if ($type == 0){
            $map['pay_status'] = ['in', '0,1,2,3,4,5,6'];
        }
        if ($type == 1) {
            $map['pay_status'] = ['=', '0'];
        }
        if ($type == 2) {
            $map['pay_status'] = ['=', '1'];
        }
        if ($type == 3) {
            $map['pay_status'] = ['=', '2'];
        }
        if ($type == 4) {
            $map['pay_status'] = ['=', '3'];
        }
        if ($type == 5) {
            $map['pay_status'] = ['in', '4,5,6'];
        }
        $map['user_id'] = ['=', $user_id];
        $list = self::where($map)->order('create_time','desc')->field('id,order_num,create_time,file_num,order_price,pay_status,total_price,pay_type,express_id,express_num,is_taobao')
            ->paginate(10, false, ['page' => $page])
            ->each(function ($item) {
                $item['name'] = OrderDetailModel::where('order_id', $item['id'])->value('name');
                $item->create_time = date('Y-m-d H:i:s', $item->create_time);
                $express=ExpressModel::get($item['express_id']);
                if ($express){
                    $item['express_name']=$express['name'];
                }else{
                    $item['express_name']='';
                }

            });
        return $list;

    }


    static function invoice_list($user_id)
    {
        $map['user_id'] = ['=', $user_id];
        $map['invoice']=['=','0'];
        $map['pay_status']=['=','3'];
        $list = self::where($map)->field('id,order_num,create_time,file_num,order_price,pay_status')
            ->select();
        foreach ($list as $item) {
            $item['name'] = OrderDetailModel::where('order_id', $item['id'])->value('name');
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
        }
        return $list;
    }


    static function detail($id)
    {

        $order = self::where('id', $id)
            ->field('id,order_num,create_time,file_num,order_price,pay_status,name,phone,address,order_price,ticket_price,balance,total_price,freight,express_id,express_num,remark')
            ->find();

        if(!$order){
            return [];
        }
        $order['create_time'] = date('Y-m-d H:i:s', $order['create_time']);
        $express=ExpressModel::get($order['express_id']);
        if ($express){
            $order['express'] = self::express($order['express_num'], $express['code'], false);
            $order['express_name']=$express['name'];
        }else{
            $order['express']=[];
            $order['express_name']='';
        }

        $order['cart'] = OrderDetailModel::order_detail($id);

        return $order;


    }


    static function express($express, $com, $state = true)
    {
        $param['com'] = $com;
        $param['num'] = $express;
        $data['customer'] = '806922D8918AFDFE9221124C8C270494';
        $data['param'] = json_encode($param, JSON_UNESCAPED_UNICODE);
        $key = 'cWAiVaog3548';
        $sign = md5($data['param'] . $key . $data['customer']);
        $data['sign'] = strtoupper($sign);
        $url = 'https://poll.kuaidi100.com/poll/query.do';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        // 第二个参数为true，表示格式化输出json
        $res = json_decode($result, true);
        if (!empty($res['status']) == 200) {
            if ($state) {
                return $res['data'];

            } else {
                return $res['data'][0];
            }

        } else {
            return [];
        }


    }



    static function invoice_record($user_id){
        $list=OrderModel::where('invoice','>','0')->where('user_id',$user_id)->field('id,order_num,total_price,invoice_time,look_up_id,invoice')->select();
        foreach ($list as $item){

            if ($item['invoice_time']>0){
                $item['invoice_time']=date('Y-m-d H:i',$item['invoice_time']);
            }
            $item['look_up']=LookUpModel::where('id',$item['look_up_id'])->value('name');
        }
        return $list;
    }

    public static function pc_list($page, $user_id,$where)
    {
        $map['user_id'] = ['=', $user_id];
        $map['is_document']=['=','1'];
        $list = self::where($map)->where($where)->order('create_time','desc')
            ->paginate(10, false, ['page' => $page])
            ->each(function ($item) {

            });

        return $list;
    }
}