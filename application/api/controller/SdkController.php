<?php
/**
 * Created by PhpStorm.
 * User: win10-06-28
 * Date: 2020/7/29
 * Time: 16:27
 */

namespace app\api\controller;


use app\api\model\AccessTokenModel;

class SdkController extends BaseController {

    protected $ticket;

    
    public function get_sdk()
    {
        $url=$this->request->param('url');
        $appid =config('wxinfo.appId');
        $noncestr = $this->getNonceStr();
        $timestamp = time();
        $data= $this->JS_SDK($noncestr, $url, $timestamp);

        return json(['code'=>20000,'msg'=>'获取数据成功','data'=>['appid'=>$appid,'url'=>$url,'noncestr'=>$noncestr,'signature'=>$data['signature'],'timestamp'=>$timestamp,'ticket'=>$data['ticket']]]);
    }
    //生成随机字符串
    protected function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public function scheme()
    {
        $release=$this->request->param('release');
        $path=$this->request->param('path');
        $appid =config('wxinfo.appId');
        $secret = config('wxinfo.secretId');
        $data=AccessTokenModel::get(1);
        if($data){
            if($data['createtime']+7000  < time() ){
                $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
                $access=file_get_contents($url);
                $access_arr=json_decode($access,true);

                //非网页的access_token
                $access_token=$access_arr['access_token'];
                AccessTokenModel::update(['access_token'=>$access_token,'createtime'=>time()],['id'=>$data['id']]);
            }else{
                $access_token=$data['access_token'];
            }
        }else{
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
            $access=file_get_contents($url);
            $access_arr=json_decode($access,true);

            //非网页的access_token
            $access_token=$access_arr['access_token'];
            AccessTokenModel::create(['access_token'=>$access_token,'createtime'=>time()]);
        }

        $url="https://api.weixin.qq.com/wxa/generatescheme?access_token=".$access_token;
        $post_data=[
            'jump_wxa'=>[
                'env_version'=>$release,
                'path'=>$path
            ]
        ];
        $res=https_request($url,json_encode($post_data));
        $result=json_decode($res,true);
        return json(['code'=>20000,'msg'=>'获取数据成','data'=>$result['openlink']]);
    }
}