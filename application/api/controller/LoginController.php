<?php

namespace app\api\controller;

use app\api\model\InvitationRecordModel;
use app\api\model\SystemModel;
use app\api\model\TicketModel;
use app\api\model\UserModel;
use app\api\model\UserNewsTicketModel;
use app\api\model\UserTicketModel;
use think\Db;
use think\Exception;

class LoginController extends BaseController
{
    public function login()
    {
        if (!input('?post.code') ){
            return json(['code' => 40001, 'msg' => '缺少参数']);
        }
        $code=$this->request->param('code');
        $parent_openid=$this->request->param('parent_openid');
        $url = sprintf(config("wxinfo.loginUrl"), config("wxinfo.appId"), config("wxinfo.secretId"), $code);
        $result = file_get_contents($url);
        $wxResult = json_decode($result, true);
        if (empty($wxResult)) {
            return json(['code' => 40003, 'msg' => '获取sessin_key及openID时异常']);
        }
        if (isset($wxResult['errcode']) && $wxResult['errcode'] != 0) {
            return json(['code' => $wxResult['errcode'], 'msg' => $wxResult['errmsg']]);
        }
        Db::startTrans();
        try {
            $user=UserModel::get(['openid'=>$wxResult['openid']]);
            $system=SystemModel::get(1);
            if (!$user){
                if ($parent_openid){
                    $parent_user=UserModel::get(['openid'=>$parent_openid]);
                    UserModel::create(['openid'=>$wxResult['openid'],'create_time'=>time(),'parent_id'=>$parent_user['id'],'unionid'=>$wxResult['unionid']]);
                }else{
                    UserModel::create(['openid'=>$wxResult['openid'],'create_time'=>time(),'unionid'=>$wxResult['unionid']]);
                }
                if ($system['commission']==1){
                    $new=true;
                }else{
                    $new=false;
                }
            }else{
                $new_ticket=UserNewsTicketModel::get(['user_id'=>$user['id']]);
                if (!$new_ticket && $system['commission']==1){
                    $new=true;
                }else{
                    $new=false;
                }
                UserModel::where('openid',$wxResult['openid'])->update(['unionid'=>$wxResult['unionid']]);
            }

            Db::commit();;
            return json(['code'=>20000,'msg'=>'登录成功','data'=>$wxResult['openid'],'new'=>$new]);
        }catch (Exception $exception){
            Db::rollback();
            return json(['code'=>40000,'msg'=>$exception->getMessage()]);

        }


    }



}