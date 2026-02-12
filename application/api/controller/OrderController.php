<?php

namespace app\api\controller;

use app\api\model\CartModel;
use app\api\model\EvaluateModel;
use app\api\model\ExpressModel;
use app\api\model\NoticeModel;
use app\api\model\OrderDetailModel;
use app\api\model\OrderExpressModel;
use app\api\model\OrderModel;
use app\api\model\ReduceModel;
use app\api\model\SystemModel;
use app\api\model\UserAddressModel;
use app\api\model\UserBalanceRecordModel;
use app\api\model\UserModel;
use app\api\model\UserTicketModel;
use PhpOffice\PhpWord\Shared\ZipArchive;
use Picqer\Barcode\BarcodeGeneratorPNG;
use setasign\Fpdi\Fpdi;
use think\Db;
use think\Exception;

class OrderController extends BaseController
{
    /**
     * 确认订单
     * @return \think\response\Json
     */
    public function order_confirm()
    {
        
        // return;
        if (!input('?post.ids') || !$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $ids = $this->request->param('ids');
        $data['address'] = UserAddressModel::default_address($this->user_id);
        $data['cart'] = CartModel::order_cart($ids);
        $data['total_price'] = 0;
        $data['commission_price'] = 0;
        $system=SystemModel::get(1);
        $data['share_discount']=0;
        foreach ($data['cart'] as $item) {
            if (!is_file('.'.$item['file'])){
                return json(['code' => 40000, 'msg' => $item['name'].'丢失，请重新上传']);
            }
            if ($item['share_id'] > 0){
                $total_price=$item['price'] * $item['num'];
                $data['share_discount']+=$total_price - $total_price * $system['share_discount'];
                $data['total_price']+=$total_price * $system['share_discount'];

            }else{
                $data['total_price'] += $item['price'] * $item['num'];
            }

            if ($item['commission'] == 1) {
                $data['commission_price'] += $item['price'] * $item['num'];
            }
            $item['reduce_num'] = ReduceModel::where('name', $item['reduction_printing'])->value('num');

        }
        $data['total_price']=round($data['total_price'],2);
        $data['num'] = count($data['cart']);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $data]);
    }


    public function is_taobao()
    {
        if ( !$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $system=SystemModel::get(1);
        $evaluate = EvaluateModel::where('user_id', $this->user_id)->find();
        if (!$evaluate){
            return json(['code' => 20000, 'msg' => '可用优惠券','data'=>$system['taobao_price']]);
        }else{
            return json(['code' => 40000, 'msg' => '不可用优惠券']);
        }
    }





    /**
     * 生成订单
     * @return \think\response\Json
     */
    public function order_add()
    {
  
        if (
            !input('?post.ids')
            || !$this->user_id
            || !input('?post.address_id')
            || !input('?post.balance')
            || !input('?post.pay_type')
            || !input('?post.freight')
        ) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        Db::startTrans();
        try {
            $ids = $this->request->param('ids');
            $order['address_id'] = $this->request->param('address_id');
            $address = UserAddressModel::get($order['address_id']);
            $order['is_taobao']=$this->request->param('is_taobao');
            $order['name'] = $address['name'];
            $order['phone'] = $address['phone'];
            $order['address'] = $address['province'] . $address['city'] . $address['area'] . $address['address'];
            $order['user_id'] = $this->user_id;
            $order['order_num'] = date('YmdHis') . rand(1000, 9999);
            $order['file_num'] = CartModel::where('id', 'in', $ids)->count();
            if ($order['file_num']==0){
                return json(['code' => 40000, 'msg' => '您还未选择文件']);
            }
            $order['order_price'] = 0;
            $order['total_price']=0;
            $user_ticket_id = $this->request->param('user_ticket_id');
            if ($user_ticket_id > 0) {
                $user_ticket = UserTicketModel::get($user_ticket_id);

                $order['ticket_price'] = $user_ticket['deduction_price'];
                UserTicketModel::update(['status' => 1], ['id' => $user_ticket_id]);
            } else {
                $order['ticket_price'] = 0;
            }
            $order['balance'] = $this->request->param('balance');
            if ($order['balance'] > 0) {

                $balance_record['user_id'] = $this->user_id;
                $balance_record['order_num'] = $order['order_num'];
                $balance_record['type'] = '0';
                $balance_record['balance']=$order['balance'];
                $balance_record['remark'] = '余额消费';
                $balance_record['create_time'] = time();
                UserBalanceRecordModel::create($balance_record);
                UserModel::where('id', $this->user_id)->setDec('balance', $order['balance']);
            } else {
                $order['balance'] = 0;
            }
            $order['pay_type'] = $this->request->param('pay_type');
            $order['freight'] = $this->request->param('freight');
            $order['pay_status'] = '0';
            $order['create_time'] = time();
            $cart = CartModel::where('id', 'in', $ids)->select();
            $system=SystemModel::get(1);
            foreach ($cart as $value) {
                if ($value['share_id'] > 0){
                    $total_price=$value['price'] * $value['num'];
                    $order['order_price']+=$total_price * $system['share_discount'];
                }else{
                    $order['order_price'] += $value['price'] * $value['num'];
                }

            }
            $order['total_price'] = $order['order_price'] + $order['freight'] - $order['balance'] - $order['ticket_price'];
            if ($order['total_price'] < 10) {
                $order['total_price'] = 10;
            }
            $res = OrderModel::create($order);

            foreach ($cart as $value) {
                $detail['user_id'] = $value['user_id'];
                $detail['order_id'] = $res->id;
                $detail['information'] = $value['information'];
                $detail['file'] = $value['file'];
                $detail['name'] = $value['name'];
                $detail['paper'] = $value['paper'];
                $detail['brand'] = $value['brand'];
                $detail['page_turning'] = $value['page_turning'];
                $detail['single_double'] = $value['single_double'];
                $detail['colour'] = $value['colour'];
                $detail['color'] = $value['color'];
                $detail['page'] = $value['page'];
                $detail['binding_type'] = $value['binding_type'];
                $detail['film_covering'] = $value['film_covering'];
                $detail['cover_type'] = $value['cover_type'];
                $detail['cover_image'] = $value['cover_image'];
                $detail['reduction_printing'] = $value['reduction_printing'];
                $detail['print_range'] = $value['print_range'];
                $detail['num'] = $value['num'];
                $detail['price'] = $value['price'];
                $detail['commission'] = $value['commission'];
                $detail['create_time'] = time();
                $detail['share_id'] = $value['share_id'];
                $detail['source_file']=$value['source_file'];
                $detail['type']=$value['type'];
                $detail['password']=$value['password'];
                OrderDetailModel::create($detail);

            }
            CartModel::where('id', 'in', $ids)->delete();
            Db::commit();
            return json(['code' => 20000, 'msg' => '订单创建成功', 'data' => $order['order_num']]);
        } catch (Exception $exception) {
            Db::rollback();
            return json(['code' => 40000, 'msg' => $exception->getMessage()]);
        }


    }


    public function pay()
    {
    
        if (!input('?post.order_num') || !$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }

        $user = UserModel::get($this->user_id);
        $order_num = $this->request->param('order_num');
        $order = OrderModel::get(['order_num' => $order_num]);
        OrderModel::update(['pay_type'=>0],['order_num'=>$order_num]);
        $pay = new PayController();
        $action = 'order';//回调方法
        $preData = [
//            'money' => 0.01,
            'money' => $order['total_price'],
            'order_num' => $order['order_num'],            //查询的订单编号
            'openid' => $user['openid'],
        ];
        $body = '订单支付';//商品描述
        $res = $pay->processPay($action, $preData, $body);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $res]);
    }

    /**
     * 订单列表
     * @return \think\response\Json
     */
    public function order_list()
    {
        if (!input('?post.page') || !$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $page = $this->request->param('page');
        $type = $this->request->param('type');
        $list = OrderModel::order_list($page, $this->user_id, $type);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }

    /**
     * 获取订单详情
     * @return \think\response\Json
     */
    public function order_detail()
    {
        if (!input('?post.id')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $id = $this->request->param('id');
        $order = OrderModel::detail($id);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $order]);

    }

    /**
     * 获取物流信息
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function express()
    {
        $id = $this->request->param('id');
        $order = OrderModel::get($id);
        $express = ExpressModel::get($order['express_id']);
        if (!$express) {
            return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => []]);
        }
        $detail = OrderModel::express($order['express_num'], $express['code']);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $detail]);
    }

    /**
     * 确认收货
     * @return \think\response\Json
     */
    public function confirm_order()
    {
        if (!input('?post.id')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }

        $id = $this->request->param('id');
        $order = OrderModel::get($id);
        Db::startTrans();
        try {
            OrderModel::update(['pay_status' => '3'], ['id' => $id]);
            $user = UserModel::get($order['user_id']);
            $user_order = OrderModel::where('user_id', $order['user_id'])->where('pay_status', '>', 0)->count();
            // $system=SystemModel::get(1);
            // if ($user_order == 1 && $system['commission']==1) {
            //     if ($order['total_price'] >= 26) {
            //         $balance=16;
            //         UserModel::where('id', $order['user_id'])->setInc('balance', '16');
            //     } else {
            //         UserModel::where('id',$order['user_id'])->setInc('balance', $order['total_price'] - 10);
            //         $balance=$order['total_price'] - 10;
            //     }
            //     $user_balance['user_id'] = $user['id'];
            //     $user_balance['balance'] =$balance;
            //     $user_balance['source_id'] = $user['id'];
            //     $user_balance['source_name'] = $user['nickname'];
            //     $user_balance['avatar_image'] = $user['avatar_image'];
            //     $user_balance['remark'] = '首单返现';
            //     $user_balance['type'] = '1';
            //     $user_balance['create_time'] = time();
            //     UserBalanceRecordModel::create($user_balance);
            // }
            $system = SystemModel::get(1);

            if ($user['parent_id'] > 0  && $user_order == 1) {
                $parent_balance['user_id'] = $user['parent_id'];
                $parent_balance['balance'] = $system['share_proportion'];
                $parent_balance['source_id'] = $user['id'];
                $parent_balance['type'] = '1';
                $parent_balance['source_name'] = $user['nickname'];
                $parent_balance['avatar_image'] = $user['avatar_image'];
                $parent_balance['remark'] = '下级返佣';
                $parent_balance['create_time'] = time();
                UserBalanceRecordModel::create($parent_balance);
                UserModel::where('id', $user['parent_id'])->setInc('balance', $parent_balance['balance']);

            }
//            if ($order['user_ticket_id'] == null) {
//                $total_price = 0;
//                $order_detail = OrderDetailModel::where('order_id', $order['id'])->where('commission', 1)->select();
//                foreach ($order_detail as $item) {
//                    $total_price += $item['num'] * $item['price'];
//                }

//                $user_balance['user_id'] = $user['id'];
//                $user_balance['balance'] = $total_price * $system['pay_proportion'] / 100;
//                $user_balance['source_id'] = $user['id'];
//                $user_balance['source_name'] = $user['nickname'];
//                $user_balance['avatar_image'] = $user['avatar_image'];
//                $user_balance['remark'] = '自购返佣';
//                $user_balance['type'] = '1';
//                $user_balance['create_time'] = time();
//                UserBalanceRecordModel::create($user_balance);
//                UserModel::where('id', $user['id'])->setInc('balance', $user_balance['balance']);
//            }

            Db::commit();
            return json(['code' => 20000, 'msg' => '确认收货成功']);
        }catch (Exception $exception){
            Db::rollback();
            return json(['code' => 40000, 'msg' => $exception->getMessage()]);

        }

    }

    /**
     * 申请退款
     * @return \think\response\Json
     */
    public function refund()
    {
        if (!input('?post.id') || !input('?post.reason') || !input('?post.image')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }


        $id = $this->request->param('id');
        $image = $this->request->param('image');
        $reason = $this->request->param('reason');
        OrderModel::update(['image' => $image, 'reason' => $reason, 'pay_status' => '4'], ['id' => $id]);
        return json(['code' => 20000, 'msg' => '申请退款成功']);
    }

    /**
     * 取消订单
     * @return \think\response\Json
     */
    public function cancel_order()
    {
        if (!input('?post.id')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $id = $this->request->param('id');
        $order = OrderModel::get($id);
        if ($order['user_ticket_id'] > 0) {
            UserTicketModel::update(['status' => 0], ['id' => $order['user_ticket_id']]);
        }
        if ($order['balance'] > 0){

            $balance_record['user_id'] = $order['user_id'];
            $balance_record['order_num'] = $order['order_num'];
            $balance_record['type'] = '1';
            $balance_record['remark'] = '订单取消';
            $balance_record['create_time'] = time();
            UserBalanceRecordModel::create($balance_record);
            UserModel::where('id', $order['user_id'])->setInc('balance', $order['balance']);
        }
        OrderModel::where('id', $id)->update(['pay_status' => '-1']);
//        OrderDetailModel::where('order_id', $id)->delete();
        return json(['code' => 20000, 'msg' => '取消订单成功']);
    }

    /**
     * 价格说明   /淘口令
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function price_remark()
    {
        $list = SystemModel::where('id', 1)->find();
        $list['image'] = cdnurl($list['image'], true);
        $list['taobao_image'] = cdnurl($list['taobao_image'], true);
        $list['pdd_image'] = cdnurl($list['pdd_image'], true);
        $list['agreement_file']= cdnurl($list['agreement_file'], true);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }


    public function express_edit()
    {
        if (!input('?post.address_id') ||!input('?post.order_id')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $address_id=$this->request->param('address_id');
        $order_id=$this->request->param('order_id');
        $address = UserAddressModel::get($address_id);
        $order_express=OrderExpressModel::get(['order_id'=>$order_id]);
        if ($order_express){
            return json(['code'=>40000,'msg'=>'该笔订单已申请']);
        }
        $order['name'] = $address['name'];
        $order['phone'] = $address['phone'];
        $order['address'] = $address['province'] . $address['city'] . $address['area'] . $address['address'];
        $order['order_id']=$order_id;
        $order['create_time']=time();
        OrderExpressModel::create($order);
        return json(['code' => 20000, 'msg' => '申请成功']);
    }


    public function scan_detail()
    {
        if (!input('?post.express_num')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $express_num=$this->request->param('express_num');
        $order=OrderModel::where('express_num',$express_num)->field('id,order_num,file_num,order_price,zip')->find();
        $order['order_detail']=OrderDetailModel::where('order_id',$order['id'])->select();
        $circle=OrderDetailModel::where('order_id',$order['id'])->where('binding_type','like','%圈装%')->count();
        $glue=OrderDetailModel::where('order_id',$order['id'])->where('binding_type','like','%胶装%')->count();
        $staple=OrderDetailModel::where('order_id',$order['id'])->where('binding_type','like','%订书钉%')->count();
        return json(['code' => 20000, 'msg' => '获取数据成功','data'=>$order,'circle'=>$circle,'glue'=>$glue,'staple'=>$staple]);
    }

    public function scan_detail1()
    {
        if (!input('?post.express_num')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $express_num=$this->request->param('express_num');
        $order=OrderModel::where('express_num',$express_num)->field('id,order_num,file_num,order_price,zip')->select();
        foreach ($order as $value){
            $value['order_detail']=OrderDetailModel::where('order_id',$order['id'])->select();
        }
        $order_ids=OrderModel::where('express_num',$express_num)->column('id');
        $circle=OrderDetailModel::where('order_id','in',$order_ids)->where('binding_type','like','%圈装%')->count();
        $glue=OrderDetailModel::where('order_id','in',$order_ids)->where('binding_type','like','%胶装%')->count();
        $staple=OrderDetailModel::where('order_id','in',$order_ids)->where('binding_type','like','%订书钉%')->count();
        return json(['code' => 20000, 'msg' => '获取数据成功','data'=>$order,'circle'=>$circle,'glue'=>$glue,'staple'=>$staple]);
    }

    public function zip()
    {
        if (!input('?post.order_id') ||!input('?post.zip')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $order_id=$this->request->param('order_id');
        $zip=$this->request->param('zip');

        OrderModel::where('id', $order_id)->update(['zip'=>$zip]);

        return json(['code' => 20000, 'msg' => '打包成功']);
    }


    public function notice()
    {
        $notice=NoticeModel::get(1);
        return json(['code' => 20000, 'msg' => '获取数据成功','data'=>$notice]);
    }


    public function system_notice()
    {
        $system_notice=NoticeModel::where('id','>',0)->column('system_notice');
        $notice=NoticeModel::where('id','>',0)->column('notice');

        return json(['code' => 20000, 'msg' => '获取数据成功','notice'=>$notice,'system_notice'=>$system_notice]);
    }
    
}