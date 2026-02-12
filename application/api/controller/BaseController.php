<?php

namespace app\api\controller;

use app\api\model\UserModel;
use think\Controller;
header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); // 允许请求的类型
header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
header('Access-Control-Allow-Headers:*');
class BaseController extends Controller
{
    protected $user_id;


    public function _initialize()
    {
        $openid=$this->request->param('openid');
       
        if(!empty($openid)){
            $user=UserModel::get(['openid'=>$openid]);
            if ($user){
                $this->user_id=$user['id'];
            }

        }

    }
}