<?php

namespace app\api\controller;

use app\api\model\AttachmentModel;
use app\api\model\BankModel;
use app\api\model\CityModel;
use app\api\model\MeatOrderModel;
use app\api\model\StoreCategoryModel;
use app\api\model\StoreModel;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use think\Exception;
use WeChatPay\Builder;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Exception\InvalidArgumentException;
use WeChatPay\Transformer;
use WeChatPay\Util\MediaUtil;
use WeChatPay\Util\PemUtil;

class WechatController extends BaseController
{

    static $platformCertificateSerial;
    static $platformPublicKeyInstance;

    public static function createClient()
    {
        $merchantId = '1659550367';
        $merchantPrivateKeyFilePath = 'file://../application/config/apiclient_key.pem';
        $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath);

        // 「商户API证书」的「证书序列号」
        $merchantCertificateSerial = '70F20524B52A7A49C127E6C680EB95AAA9B28CC4';

        // 从本地文件中加载「微信支付平台证书」，用来验证微信支付应答的签名
        $platformCertificateFilePath = 'file://../application/config/wechatpay.pem';
        self::$platformPublicKeyInstance = Rsa::from($platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);

        // 从「微信支付平台证书」中获取「证书序列号」
        self::$platformCertificateSerial = PemUtil::parseCertificateSerialNo($platformCertificateFilePath);
        // 构造一个 APIv3 客户端实例
        return Builder::factory([
            'mchid' => $merchantId,
            'serial' => $merchantCertificateSerial,
            'privateKey' => $merchantPrivateKeyInstance,
            'certs' => [
                self::$platformCertificateSerial => self::$platformPublicKeyInstance,
            ],
        ]);
    }








    /**
     * 添加分账接收方
     * @param $type
     * @param $account
     * @param $relation_type
     * @param $name
     * @return \think\response\Json|void
     */
    public static function transfer($data)
    {

        try {
            $instance = self::createClient();
            $resp = $instance->chain("/v3/fund-app/mch-transfer/transfer-bills")->post(
                [
                    'json' => $data,
                    'headers' => [
                        // $platformCertificateSerial 见初始化章节
                        'Wechatpay-Serial' => self::$platformCertificateSerial,
                    ],
                ]
            );
            $res = json_decode($resp->getBody()->getContents(), true);
           return $res;
        } catch (InvalidArgumentException $e) {
            dump($e);
            return json(['code' => 40000, 'msg' => '接口异常']);
        }
    }


    public function query_transfer($out_bill_no)
    {
        try {
            $instance = self::createClient();
            $resp = $instance->chain("/v3/fund-app/mch-transfer/transfer-bills/out-bill-no/$out_bill_no")->get();
            $res = json_decode($resp->getBody()->getContents(), true);
            return $res;
        } catch (InvalidArgumentException|ClientException | GuzzleException $e) {
            return ['code' => 40000, 'msg' => '接口异常'];
        }
    }


    public function native($order_num,$money)
    {
        try {
            $data = [
                'appid' => config('wxpay.miniapp_id'),
                'mchid' => config('wxpay.mch_id'),
                'description' => '订单支付',
                'out_trade_no' => $order_num,
                'notify_url' => $this->request->domain(). '/api/WeChatResult/payment',
                'amount' => [
                    'total' => (int)($money *100),
                    'currency' => 'CNY',
                ],
            ];
            $instance = self::createClient();
            $resp = $instance->chain("/v3/pay/transactions/native")->post(
                [
                    'json' => $data
                ]
            );
            return json_decode($resp->getBody()->getContents(), true);
        } catch (InvalidArgumentException $e) {
            dump($e);
            return json(['code' => 40000, 'msg' => '接口异常']);
        }
        
    }



}