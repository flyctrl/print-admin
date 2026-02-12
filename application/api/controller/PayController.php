<?php

namespace app\api\controller;

use Pimple\Psr11\Container;
use think\Controller;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class PayController extends Controller
{


    public function processConfig($action=null)
    {
        $base_url = $this->request->domain();
        $config = [
            'alipay' => [
                'default' => [
                    // 必填-支付宝分配的 app_id
                    'app_id' => '',
                    // 必填-应用私钥 字符串或路径
                    // 在 https://open.alipay.com/develop/manage 《应用详情->开发设置->接口加签方式》中设置
                    'app_secret_cert' => '',
                    // 必填-应用公钥证书 路径
                    // 设置应用私钥后，即可下载得到以下3个证书
                    'app_public_cert_path' => '',
                    // 必填-支付宝公钥证书 路径
                    'alipay_public_cert_path' => '',
                    // 必填-支付宝根证书 路径
                    'alipay_root_cert_path' => '',
                    'return_url' => 'https://yansongda.cn/alipay/return',
                    'notify_url' => 'https://yansongda.cn/alipay/notify',
                    // 选填-第三方应用授权token
                    'app_auth_token' => '',
                    // 选填-服务商模式下的服务商 id，当 mode 为 Pay::MODE_SERVICE 时使用该参数
                    'service_provider_id' => '',
                    // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SANDBOX, MODE_SERVICE
                    'mode' => Pay::MODE_NORMAL,
                ]
            ],
            'wechat' => [
                'default' => [
                    // 必填-商户号，服务商模式下为服务商商户号
                    // 可在 https://pay.weixin.qq.com/ 账户中心->商户信息 查看
                    'mch_id' => config('wxpay.mch_id'),
                    // 必填-商户秘钥
                    // 即 API v3 密钥(32字节，形如md5值)，可在 账户中心->API安全 中设置
                    'mch_secret_key' => config('wxpay.key'),
                    // 必填-商户私钥 字符串或路径
                    // 即 API证书 PRIVATE KEY，可在 账户中心->API安全->申请API证书 里获得
                    // 文件名形如：apiclient_key.pem
                    'mch_secret_cert' => '../application/config/apiclient_key.pem',
                    // 必填-商户公钥证书路径
                    // 即 API证书 CERTIFICATE，可在 账户中心->API安全->申请API证书 里获得
                    // 文件名形如：apiclient_cert.pem
                    'mch_public_cert_path' =>  '../application/config/apiclient_cert.pem',
                    // 必填-微信回调url
                    // 不能有参数，如?号，空格等，否则会无法正确回调
                    'notify_url' => $base_url . '/api/WeChatResult/' . $action,
                    // 选填-公众号 的 app_id
                    // 可在 mp.weixin.qq.com 设置与开发->基本配置->开发者ID(AppID) 查看
                    'mp_app_id' => '',
                    // 选填-小程序 的 app_id
                    'mini_app_id' => config('wxpay.miniapp_id'),
                    // 选填-app 的 app_id
                    'app_id' => '',
                    // 选填-微信平台公钥证书路径, optional，强烈建议 php-fpm 模式下配置此参数
                    'wechat_public_cert_path' => [
                        '621465A08DC15524BD6FCE0C494986EEFBEFBE3B'=>'../application/config/wechatpay.pem'
                    ],
                    // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
                    'mode' => Pay::MODE_NORMAL,
                ]
            ],
            'unipay' => [
                'default' => [
                    // 必填-商户号
                    'mch_id' => '777290058167151',
                    // 必填-商户公私钥
                    'mch_cert_path' => __DIR__ . '/Cert/unipayAppCert.pfx',
                    // 必填-商户公私钥密码
                    'mch_cert_password' => '000000',
                    // 必填-银联公钥证书路径
                    'unipay_public_cert_path' => __DIR__ . '/Cert/unipayCertPublicKey.cer',
                    // 必填
                    'return_url' => 'https://yansongda.cn/unipay/return',
                    // 必填
                    'notify_url' => 'https://yansongda.cn/unipay/notify',
                ],
            ],
            'logger' => [
                'enable' => false,
                'file' => './logs/pay.log',
                'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
            'http' => [ // optional
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
                // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
            ],
        ];
        return $config;
    }


    /**
     * 统一下单
     * @param $action           回调方法
     * @param $money            订单金额
     * @param $ordersn          订单号
     * @param $openid           openid
     * @return \yansongda\supports\Collection   支付成功携带验签的数据,客户端使用该数据拉起支付页面
     */
    public function processPay($action, $preData, $body = '订单支付')
    {
        $total_fee = intval($preData['money'] * 100);
        try{
            $order = [

                'out_trade_no' => $preData['order_num'],
                'description' => $body,
                'amount' => [
                    'total' => $total_fee,
                    'currency' => 'CNY',
                ],
                'payer' => [
                    'openid' => $preData['openid'],
                ]
            ];
            $config = $this->processConfig($action);
            Pay::config($config);
            return Pay::wechat()->mini($order);
        }catch(Exception $e){
          dump($e);
        }



//

    }


    public function refund($data)
    {
        $config = $this->processConfig();
        Pay::config($config);

        $order = [
            'out_trade_no' => $data['out_trade_no'],
            'out_refund_no' =>$data['out_refund_no'],
            'amount' => [
                'refund' => $data['refund'],
                'total' => $data['total'],
                'currency' => 'CNY',
            ],
        ];
        $result = Pay::wechat()->refund($order);
        return $result;
    }

    




}