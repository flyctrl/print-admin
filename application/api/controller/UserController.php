<?php

namespace app\api\controller;

use app\api\model\AccessTokenModel;
use app\api\model\CartModel;
use app\api\model\CommonQuestionModel;
use app\api\model\EvaluateModel;
use app\api\model\InvoiceModel;
use app\api\model\LookUpModel;
use app\api\model\OpinionModel;
use app\api\model\OrderDetailModel;
use app\api\model\OrderModel;
use app\api\model\RankingModel;
use app\api\model\RecoveryModel;
use app\api\model\ShareDetailModel;
use app\api\model\ShareInfoModel;
use app\api\model\ShareModel;
use app\api\model\SignsModel;
use app\api\model\SystemModel;
use app\api\model\UserBalanceRecordModel;
use app\api\model\UserCollectModel;
use app\api\model\UserModel;
use app\api\model\UserTicketModel;
use app\api\model\UserWithdrawalModel;
use fast\Random;

class UserController extends BaseController
{
    /**
     * 获取用户信息
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function userinfo()
    {
        if (!$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $user = UserModel::where('id', $this->user_id)->field('id,nickname,avatar_image,balance,scan')->find();
        if ($user['avatar_image']) {
            $user['avatar_image'] = cdnurl($user['avatar_image'], true);
        }
        $user['pay_num'] = OrderModel::where('pay_status', '0')->where('user_id', $this->user_id)->count();
        $user['print_num'] = OrderModel::where('pay_status', '1')->where('user_id', $this->user_id)->count();
        $user['received_num'] = OrderModel::where('pay_status', '2')->where('user_id', $this->user_id)->count();
        $user['complete_num'] = OrderModel::where('pay_status', '3')->where('user_id', $this->user_id)->count();
        $user['refund_num'] = OrderModel::where('pay_status', '>', '3')->where('user_id', $this->user_id)->count();
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $user]);

    }


    /**
     * 常见问题
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function question()
    {
        $list = CommonQuestionModel::all();
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }

    /**
     * 意见反馈
     * @return \think\response\Json
     */
    public function opinion()
    {
        if (!$this->user_id || !input('?post.question') || !input('?post.images')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $opinion['user_id'] = $this->user_id;
        $opinion['question'] = $this->request->param('question');
        $opinion['images'] = $this->request->param('images');
        $opinion['create_time'] = time();
        OpinionModel::create($opinion);
        return json(['code' => 20000, 'msg' => '反馈成功']);
    }

    /**
     * 修改用户信息
     * @return \think\response\Json
     */
    public function edit_user()
    {
        if (!$this->user_id || !input('?post.nickname')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $nickname = $this->request->param('nickname');
        $avatar_image = $this->request->param('avatar_image');
        UserModel::update(['nickname' => $nickname, 'avatar_image' => $avatar_image], ['id' => $this->user_id]);
        return json(['code' => 20000, 'msg' => '修改用户信息成功']);
    }

    /**
     * 获取用户优惠券列表
     * @return \think\response\Json|void
     */
    public function user_ticket()
    {
        if (!$this->user_id || !input('?post.type')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $type = $this->request->param('type');
        $ticket_type=$this->request->param('ticket_type',0);
        $list = UserTicketModel::user_ticket($this->user_id, $type,  $ticket_type);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }

    /**
     * 用户收藏资料
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function user_collect()
    {
        if (!$this->user_id || !input('?post.information_id')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $information_id = $this->request->param('information_id');

        $collect = UserCollectModel::get(['user_id' => $this->user_id, 'information_id' => $information_id]);
        if ($collect) {
            return json(['code' => 40000, 'msg' => '您已收藏，不可再次收藏']);
        }
        UserCollectModel::create(['user_id' => $this->user_id, 'information_id' => $information_id, 'create_time' => time()]);
        return json(['code' => 20000, 'msg' => '收藏成功']);
    }

    /**
     * 取消收藏
     * @return \think\response\Json
     */
    public function cancel_collect()
    {
        if (!input('?post.ids')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $ids = $this->request->param('ids');
        UserCollectModel::where('id', 'in', $ids)->delete();
        return json(['code' => 20000, 'msg' => '取消收藏成功']);
    }

    /**
     * 用户收藏列表
     * @return \think\response\Json
     */
    public function collect_list()
    {
        if (!$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $list = UserCollectModel::collect_list($this->user_id);

        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);

    }

    /**
     * 设置抬头
     * @return \think\response\Json
     */
    public function set_look_up()
    {
        if (!$this->user_id
            || !input('?post.name')
            || !input('?post.duty_paragraph')
            || !input('?post.company_address')
            || !input('?post.bank')
            || !input('?post.bank_account')
        ) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $data['name'] = $this->request->param('name');
        $data['duty_paragraph'] = $this->request->param('duty_paragraph');
        $data['company_address'] = $this->request->param('company_address');
        $data['bank'] = $this->request->param('bank');
        $data['bank_account'] = $this->request->param('bank_account');
        $data['user_id'] = $this->user_id;
        LookUpModel::create($data);
        return json(['code' => 20000, 'msg' => '设置抬头成功']);


    }

    /**
     * 抬头列表
     * @return \think\response\Json
     */
    public function look_up()
    {
        if (!$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $list = LookUpModel::look_up($this->user_id);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }

    /**
     * 抬头详情
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function look_up_detail()
    {
        if (!input('?post.id')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $id = $this->request->param('id');
        $detail = LookUpModel::get($id);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $detail]);
    }

    /**
     * 修改抬头
     * @return \think\response\Json
     */
    public function look_up_edit()
    {
        if (!input('?post.id')
            || !input('?post.name')
            || !input('?post.duty_paragraph')
            || !input('?post.company_address')
            || !input('?post.bank')
            || !input('?post.bank_account')
        ) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $id = $this->request->param('id');
        $data['name'] = $this->request->param('name');
        $data['duty_paragraph'] = $this->request->param('duty_paragraph');
        $data['company_address'] = $this->request->param('company_address');
        $data['bank'] = $this->request->param('bank');
        $data['bank_account'] = $this->request->param('bank_account');
        LookUpModel::update($data, ['id' => $id]);
        return json(['code' => 20000, 'msg' => '修改抬头成功']);
    }

    /**
     * 删除抬头
     * @return \think\response\Json
     */
    public function look_up_del()
    {
        if (!input('?post.id')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $id = $this->request->param('id');
        LookUpModel::where('id', $id)->delete();
        return json(['code' => 20000, 'msg' => '删除抬头成功']);
    }

    /**
     * 分享
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function share()
    {
        if (!$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $user = UserModel::get($this->user_id);
        if ($user['qr_code']) {
            $qr_code = cdnurl($user['qr_code'], true);
        } else {
            $qr_code = $this->code($user['openid']);
        }

        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $qr_code]);
    }


   

    public function code($openid)
    {
        $res = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=$res";
        $page = 'pages/index/index';
        $processData = [
            'page' => $page,
            'scene' => $openid,   //二维码id,二维码类型
            'env_version' => 'trial',  //二维码id,二维码类型
            'check_path' => false
        ];
        $qrData = json_encode($processData);

        $data = https_request($url, $qrData);
        $tmp = json_decode($data, true);

        if (!empty($tmp['errcode']) == 45009 || !empty($tmp['errcode']) == 41030) {
            return ['code' => $tmp['errcode'], 'msg' => $tmp['errmsg']];
        }
        $filename = date('YmdHis') . rand(10000, 999999) . '.jpg';

        $dir = ROOT_PATH . 'public/uploads/qr/' . date('Ymd');
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $file = $dir . '/' . $filename;
        $retFile = '/uploads/qr/' . date('Ymd') . '/' . $filename;
        file_put_contents($file, $data);
        UserModel::update(['qr_code' => $retFile], ['openid' => $openid]);
        return cdnurl($retFile, true);
    }


    /**
     * 获取AccessToken
     * @return mixed
     */
    public function getAccessToken()
    {
        $appId = Config('wxinfo.appId');
        $secretId = Config('wxinfo.secretId');


        $data=AccessTokenModel::get(1);
        if($data){
            if($data['createtime']+7000  < time()){
                $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appId."&secret=".$secretId;
                $access=file_get_contents($url);
                $access_arr=json_decode($access,true);

                //非网页的access_token
                $access_token=$access_arr['access_token'];
                AccessTokenModel::update(['access_token'=>$access_token,'createtime'=>time()],['id'=>$data['id']]);
            }else{
                $access_token=$data['access_token'];
            }
        }else{
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appId."&secret=".$secretId;
            $access=file_get_contents($url);
            $access_arr=json_decode($access,true);

            //非网页的access_token
            $access_token=$access_arr['access_token'];
            AccessTokenModel::create(['access_token'=>$access_token,'createtime'=>time()]);
        }
        return $access_token;

    }

    /**
     * 申请开票
     * @return \think\response\Json
     */
    public function invoice()
    {
        if (!$this->user_id || !input('?post.order_ids') || !input('?post.look_up_id')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $invoice['user_id'] = $this->user_id;
        $invoice['invoice_time'] = time();
        $invoice['order_ids'] = $this->request->param('order_ids');
        $invoice['look_up_id'] = $this->request->param('look_up_id');
        $invoice['invoice_amount'] = OrderModel::where('id', 'in', $invoice['order_ids'])->sum('order_price');
        $invoice['create_time'] = time();
        InvoiceModel::create($invoice);
        OrderModel::where('id', 'in', $invoice['order_ids'])->update(['invoice' => 1]);
        return json(['code' => 20000, 'msg' => '申请开票成功']);
    }


    /**
     * 获取未开票列表
     * @return \think\response\Json
     */
    public function invoice_list()
    {
        if (!$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $list = OrderModel::invoice_list($this->user_id);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }

    /**
     * 邀请记录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function invitation_record()
    {
        if (!$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $list = UserModel::where('parent_id', $this->user_id)->field('id,nickname,avatar_image,create_time')->select();
        foreach ($list as $item) {
//            $item['phone']=substr_replace($item['phone'],'****',3,4);
            $item['create_time'] = date('Y-m-d H:i', $item['create_time']);
        }
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);

    }


    public function invoice_record()
    {
        if (!$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $list = OrderModel::invoice_record($this->user_id);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }


    public function balance_record()
    {

        if (!$this->user_id || !input('?post.page') || !input('?post.type')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $type = $this->request->param('type');
        $page = $this->request->param('page');
        $list = UserBalanceRecordModel::record_list($this->user_id, $page, $type);
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }


    public function share_info()
    {
        if (!$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $list = ShareInfoModel::where('user_id', $this->user_id)->field('id,name,code,create_time,share_detail_ids')->select();
        foreach ($list as $value) {
            $value['create_time'] = date('Y-m-d H:i', $value['create_time']);
            $value['num'] = ShareDetailModel::where('id', 'in', $value['share_detail_ids'])->count();
        }
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }


    /**
     * 分享详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function share_detail()
    {
        if (!$this->user_id || !input('?post.code')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $code = $this->request->param('code');
        $list = ShareInfoModel::where('id|code', $code)->field('id,name,code,create_time,share_detail_ids')->find();
        if (!$list) {
            return json(['code' => 40000, 'msg' => '分享文件不存在']);
        }
        $list['create_time'] = date('Y-m-d H:i', $list['create_time']);
        $detail = ShareDetailModel::where('id', 'in', $list['share_detail_ids'])->select();
        foreach ($detail as $value) {
            $value['file'] = cdnurl($value['file'], true);
        }
        $list['detail'] = $detail;
        return json(['code' => 20000, 'msg' => '获取数据成功', 'data' => $list]);
    }

    /**
     * 生成分享
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add_share()
    {
        if (!$this->user_id || !input('?post.name') || !input('?post.ids')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $ids = $this->request->param('ids');
        $cart = CartModel::where('id', 'in', $ids)->select();
        $order_ids = [];
        foreach ($cart as $value) {
            $detail['information'] = '2';
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
            $detail['share_id']=$this->user_id;
            $res = ShareDetailModel::create($detail);
            array_push($order_ids, $res->id);
        }
        $info['name'] = $this->request->param('name');
        $info['code'] = Random::alnum(8);
        $info['user_id'] = $this->user_id;
        $info['share_detail_ids'] = implode(',', $order_ids);
        $info['create_time'] = time();
        ShareInfoModel::create($info);
        return json(['code' => 20000, 'msg' => '分享成功', 'data' => $info['code']]);
    }

    /**删除分享清单
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function share_del()
    {
        if (!input('?post.id')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $id = $this->request->param('id');
        $info = ShareInfoModel::get($id);
        ShareInfoModel::where('id', $id)->delete();
        ShareDetailModel::where('id', 'in', $info['share_detail_ids'])->delete();
        return json(['code' => 20000, 'msg' => '删除分享清单成功']);
    }

    /**
     * 修改分享名称
     * @return \think\response\Json
     */
    public function share_edit()
    {
        if (!input('?post.id') || !input('?post.name') || !input('?post.ids')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $ids = $this->request->param('ids');
        $name = $this->request->param('name');
        $id = $this->request->param('id');
        ShareInfoModel::update(['name' => $name, 'share_detail_ids' => $ids], ['id' => $id]);
        return json(['code' => 20000, 'msg' => '修改名称成功']);
    }


    public function order_list()
    {

        $list=OrderDetailModel::where('share_id',$this->user_id)
            ->field('share_id,name,create_time')->select();

        foreach ($list as $value){
            $value['create_time']=date('Y-m-d',$value['create_time']);
        }

        return json(['code' => 20000, 'msg' => '分享成功', 'data' => $list]);
    }


    public function xhs_share()
    {
        if (!$this->user_id || !input('?post.short_url')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $shortUrl = $this->request->param('short_url');
        $pattern = '/http:\/\/xhslink\.com\/[a-zA-Z0-9]+/';
        // 执行匹配
        preg_match($pattern, $shortUrl, $matches);
        $redirectUrl = $this->getRedirectUrl($matches[0]);
        if ($redirectUrl) {
            $postId = $this->parseUrl($redirectUrl);
            $user_share=ShareModel::get(['user_id' => $this->user_id, 'post_id' => $postId]);
            if ($user_share){
                return json(['code' => 40000, 'msg' => '该链接已分享']);
            }
            $share['user_id']=$this->user_id;
            $share['post_id']=$postId;
            $share['url']=$shortUrl;
            $share['create_time']=time();
            $share['status']=0;
            ShareModel::create($share);
            return json(['code' => 20000, 'msg' => '申请成功']);
        } else {
            return json(['code' => 40000, 'msg' => '该链接无法解析']);
        }
    }



    function getRedirectUrl($url) {
        $ch = curl_init($url);

        // 设置cURL选项
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 跟随重定向
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 将返回结果作为字符串
        curl_setopt($ch, CURLOPT_HEADER, true); // 返回header
        curl_setopt($ch, CURLOPT_NOBODY, true); // 不返回body
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        // 执行cURL请求
        curl_exec($ch);

        // 获取最终的重定向URL
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        // 关闭cURL会话
        curl_close($ch);

        return $finalUrl;
    }



   public  function parseUrl($url) {
        // 定义正则表达式来匹配小红书的分享链接
        $pattern = '/https:\/\/www\.xiaohongshu\.com\/discovery\/item\/([a-zA-Z0-9]+)/';

        // 使用preg_match来执行正则表达式匹配
        if (preg_match($pattern, $url, $matches)) {
            // 提取匹配到的内容
            $postId = $matches[1];


            // 返回提取到的postId
            return $postId;
        } else {
            return false;
        }
    }


    public function share_list()
    {
        if (!$this->user_id ) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $list=ShareModel::where('user_id',$this->user_id)->order('create_time','desc')->field('id,url,create_time,status,audit_time,reason,user_id')->select();
        foreach ($list as $value){
            if ($value['audit_time']!=''){
                $value['audit_time']=date('Y-m-d',$value['audit_time']);
            }
            $value['create_time']=date('Y-m-d',$value['create_time']);

            $value['nickname']=UserModel::where('id',$value['user_id'])->value('nickname');

        }
        return json(['code' => 20000, 'msg' => '获取分享列表成功', 'data' => $list]);
    }


    public function user_withdrawal()
    {
        if (!$this->user_id || !input('?post.price')) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $price = $this->request->param('price');
        $user=UserModel::get($this->user_id);
        if ($user['balance']<$price){
            return json(['code' => 40000, 'msg' => '余额不足']);
        }
        $user_withdrawal['user_id']=$this->user_id;
        $user_withdrawal['price']=$price;
        $user_withdrawal['order_num']=date('YmdHis').rand(1000,9999);
        $user_withdrawal['status']=0;
        $user_withdrawal['create_time']=time();
        UserWithdrawalModel::create($user_withdrawal);
        UserModel::update(['balance'=>$user['balance']-$price],['id'=>$this->user_id]);
        return json(['code' => 20000, 'msg' => '提现申请成功']);
    }

    public function withdrawal_list()
    {
        if (!$this->user_id) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $list=UserWithdrawalModel::where('user_id',$this->user_id)->select();
        foreach ($list as $value){
            $value['create_time']=date('Y-m-d H:i:s',$value['create_time']);
        }
        return json(['code' => 20000, 'msg' => '获取成功', 'data' => $list]);
    }


    public function evaluate()
    {
        if (!$this->user_id || !input('?post.order_id') || !input('?post.images') ) {
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $order_id = $this->request->param('order_id');
        $order=OrderModel::get($order_id);
        $user_evaluate=EvaluateModel::where('user_id',$this->user_id)->find();
        if ($user_evaluate){
            return json(['code' => 40000, 'msg' => '您已经评价过了']);
        }
        $config=SystemModel::get(1);
        if ($order){
            $images = $this->request->param('images');
            $evaluate['user_id']=$this->user_id;
            $evaluate['order_id']=$order_id;
            $evaluate['images']=$images;
            $evaluate['create_time']=time();
            $evaluate['price']=$config['taobao_price'];
            EvaluateModel::create($evaluate);
            return json(['code' => 20000, 'msg' => '评价成功']);
        }else{
            return json(['code' => 40000, 'msg' => '订单不存在']);
        }
    }


    public function ranking()
    {
        $list=RankingModel::order('ranking','desc')->select();
        foreach ($list as $value){
           $value['avatar_image']=cdnurl($value['avatar_image'],true);
        }
        return json(['code' => 20000, 'msg' => '获取成功', 'data' => $list]);
    }


    public function recovery_list()
    {

        $list=RecoveryModel::all();

        return json(['code' => 20000, 'msg' => '获取成功', 'data' => $list]);
    }

    public function sign()
    {
        if (!$this->user_id || !input('?post.sign_image') || !input('?post.card_front_image') || !input('?post.card_back_image')){
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $sign_image = $this->request->param('sign_image');
        $card_front_image = $this->request->param('card_front_image');
        $card_back_image = $this->request->param('card_back_image');
        $sign=SignsModel::where('user_id',$this->user_id)->find();
        $signs['user_id']=$this->user_id;
        $signs['sign_image']=$sign_image;
        $signs['card_front_image']=$card_front_image;
        $signs['card_back_image']=$card_back_image;
        if ($sign){
            if ($sign['status']==1 || $sign['status']==0){
                return json(['code' => 40000, 'msg' => '您已经提交过了']);
            }else{
                SignsModel::update($signs,['user_id'=>$this->user_id]);
                return json(['code' => 20000, 'msg' => '提交成功']);
            }
        }else{

            SignsModel::create($signs);
            return json(['code' => 20000, 'msg' => '提交成功']);
        }

    }


    public function sign_detail()
    {
        if (!$this->user_id){
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $sign=SignsModel::where('user_id',$this->user_id)->find();
        return json(['code' => 20000, 'msg' => '获取成功', 'data' => $sign]);

    }



    public function system()
    {
        $list=SystemModel::where('id',1)->field('qq_content,wps_content,button,taobao_remark,rendering')->find();
        $list['rendering']= cdnurl($list['rendering'], true);
        return json(['code' => 20000, 'msg' => '获取成功', 'data' => $list]);


    }







}