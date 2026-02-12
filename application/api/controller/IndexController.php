<?php

namespace app\api\controller;

use app\admin\controller\basic\Ticket;
use app\api\model\BannerModel;
use app\api\model\NewsTicketModel;
use app\api\model\TicketModel;
use app\api\model\UserNewsTicketModel;
use app\api\model\UserTicketModel;

class IndexController extends BaseController
{

    /**
     * 获取新用户优惠券
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function get_news_ticket()
    {
        $list = NewsTicketModel::get(1);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }

    /**
     * 新用户领取优惠券
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function receive_news_ticket()
    {
        if (!$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $ticket = UserNewsTicketModel::get(['user_id' => $this->user_id]);
        if ($ticket) {
            return json(['code' => 40000, 'msg' => '你已领取,不可再次领取']);
        }
        $list = NewsTicketModel::get(1)->toArray();
        unset($list['id']);
        $list['create_time'] = time();
        $list['user_id'] = $this->user_id;
        UserNewsTicketModel::create($list);
        return json(['code' => 20000, 'msg' => '领取成功']);
    }

    /**
     * 获取首页Banner
     * @return \think\response\Json
     */
    public function index()
    {
        $banner = BannerModel::banner_list();
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $banner]);
    }


    public function ticket_list()
    {
        $type = $this->request->param('type',0);
        $list = TicketModel::ticket_list();
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }


    public function receive_ticket()
    {
        if (!$this->user_id || !input('?post.id')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }

        $id = $this->request->param('id');

        $user_ticket_day=UserTicketModel::where('user_id',$this->user_id)->where('ticket_id',$id)->find();
        if ($user_ticket_day){
            return  json(['code' => 40000, 'msg' => '今天你已领取，不可再次领取']);
        }
        $ticket = TicketModel::get($id);
        if ($ticket['stock'] <= 0) {
            return json(['code' => 40000, 'msg' => '该优惠券已被抢光']);
        }
        $user_ticket['ticket_name'] = $ticket['ticket_name'];
        $user_ticket['ticket_id']=$id;
        $user_ticket['type']=$ticket['type'];
        $user_ticket['discount'] = $ticket['discount'];
        $user_ticket['limitation_price'] = $ticket['limitation_price'];
        $user_ticket['deduction_price'] = $ticket['deduction_price'];
        $user_ticket['star_time'] = time();
        $user_ticket['end_time'] = $ticket['end_time'];
        $user_ticket['status'] = 0;
        $user_ticket['user_id'] = $this->user_id;
        UserTicketModel::create($user_ticket);
        $ticket->stock = $ticket->stock - 1;
        $ticket->save();
        return json(['code' => 20000, 'msg' => '领取成功']);
    }
}