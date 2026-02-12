<?php

return [


    //小程序appid
    'appId'=>'wxea64a6f1cf005488',
    //小程序秘钥
    'secretId' =>'ce473bc9bccf931ba743f5be0c20e6ad',

    //公众号appid
    'AppId'=>'wx64f70a1f8b7ac56a',
    //公众号秘钥
    'AppSecret' =>'9de78b5cfc0535c57208acc77ae8f3b2',
    //获取openid接口
    'loginUrl' => 'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code',
    //获取access_token接口
    'accessUrl' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',

];




