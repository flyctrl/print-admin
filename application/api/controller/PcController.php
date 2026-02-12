<?php

namespace app\api\controller;

use app\api\controller\BaseController;
use app\api\model\CartModel;
use app\api\model\ExpressModel;
use app\api\model\OrderDetailModel;
use app\api\model\OrderModel;
use app\api\model\PaymentModel;
use app\api\model\UserDocumentModel;
use app\api\model\UserModel;
use app\api\model\UserTicketModel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Queue;
header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); // 允许请求的类型
header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
header('Access-Control-Allow-Headers:*');
class PcController extends BaseController
{
    public function login()
    {
        if (!input('?post.code')) {
            return json(['code' => 40001, 'msg' => '缺少参数']);
        }
        $code = $this->request->param('code');
        $appId = config("wxinfo.AppId");
        $secret = config("wxinfo.AppSecret");
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appId&secret=$secret&code=$code&grant_type=authorization_code";
        $result = file_get_contents($url);
        $wxResult = json_decode($result, true);
        if (empty($wxResult)) {
            return json(['code' => 40003, 'msg' => '获取sessin_key及openID时异常']);
        }
        if (isset($wxResult['errcode']) && $wxResult['errcode'] != 0) {
            return json(['code' => $wxResult['errcode'], 'msg' => $wxResult['errmsg']]);
        }
        try {
            $user = UserModel::get(['unionid' => $wxResult['unionid']]);
            if (!$user) {
                return json(['code' => 40000, 'msg' => '用户不存在']);
            }
            if ($user['is_document'] == 0) {
                return json(['code' => 40000, 'msg' => '用户未开通']);
            }
            return json(['code' => 20000, 'msg' => '登录成功', 'data' => $user['openid']]);
        } catch (Exception $exception) {
            return json(['code' => 40000, 'msg' => $exception->getMessage()]);
        }
    }


    public function import()
    {
        if (!input('?post.file') || !input('?post.type')) {
            return json(['code' => 40000, 'msg' => '缺少参数']);
        }
        $result = false;
        Db::startTrans();
        try {
            $file = $this->request->param('file');
            $type = $this->request->param('type');
            if (!$file) {
                $this->error(__('Parameter %s can not be empty', 'file'));
            }
            $filePath = ROOT_PATH . DS . 'public' . DS . $file;
            if (!is_file($filePath)) {
                $this->error(__('No results were found'));
            }
            //实例化reader
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            if (!in_array($ext, ['xls', 'xlsx'])) {
                $this->error(__('Unknown data format'));
            }
            if ($ext === 'xls') {
                $reader = new Xls();
            } else {
                $reader = new Xlsx();
            }
            $success = [];
            $error = [];
            try {
                if (!$PHPExcel = $reader->load($filePath)) {
                    $this->error(__('Unknown data format'));
                }
                $currentSheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表

                $allRow = $currentSheet->getHighestRow(); //取得一共有多少行

                for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                    if ($type == 1) {
                        $order['name'] = $currentSheet->getCell('B' . $currentRow)->getValue();
                        $order['phone'] = $currentSheet->getCell('C' . $currentRow)->getValue();
                        $order['address'] = $currentSheet->getCell('D' . $currentRow)->getValue() . $currentSheet->getCell('E' . $currentRow)->getValue();
                        $order['remark'] = $currentSheet->getCell('K' . $currentRow)->getValue();
                    } else {

                        $order['name'] = $currentSheet->getCell('D' . $currentRow)->getValue();
                        $order['phone'] = $currentSheet->getCell('E' . $currentRow)->getValue();
                        $order['address'] = $currentSheet->getCell('F' . $currentRow)->getValue() . $currentSheet->getCell('G' . $currentRow)->getValue()
                            . $currentSheet->getCell('H' . $currentRow)->getValue() . $currentSheet->getCell('I' . $currentRow)->getValue();
                        $order['remark'] = $currentSheet->getCell('M' . $currentRow)->getValue();
                    }
                    $order['order_num'] = date('YmdHis') . rand(1000, 9999);
                    $order['import'] = $type;
                    $order['ticket_price'] = 0;
                    $order['balance'] = 0;
                    $order['freight'] = 0;
                    $order['user_id'] = $this->user_id;
                    $order['create_time'] = time();
                    $order['is_document'] = 1;
                    $order['pay_type'] = 0;
                    $order['pay_status'] = 0;
                    $res = OrderModel::create($order);
                    if ($res) {
                        $success[] = $order['order_num'];
                    } else {
                        $error[] = $order['order_num'];
                    }
                }
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }
            Db::commit();
            return json(['code' => 20000, 'msg' => '导入成功', 'data' => ['success' => $success, 'error' => $error]]);
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            return json(['code' => 40000, 'msg' => $e->getMessage()]);
        }

    }


    public function order_add()
    {
        if (!input('?post.order_num') || !input('?post.name') || !input('?post.phone') || !input('?post.address') || !input('?post.type') || !input('?post.remark')) {
            return json(['code' => 40000, 'msg' => '缺少参数']);
        }
        try {
            $data['order_num'] = $this->request->param('order_num');
            $data['name'] = $this->request->param('name');
            $data['phone'] = $this->request->param('phone');
            $data['address'] = $this->request->param('address');
            $data['import'] = $this->request->param('type');
            $data['remark'] = $this->request->param('remark');
            $data['ticket_price'] = 0;
            $data['balance'] = 0;
            $data['freight'] = 0;
            $data['user_id'] = $this->user_id;
            $data['is_document'] = 1;
            $data['pay_status'] = 0;
            $data['create_time'] = time();
            $order = OrderModel::create($data);
            return json(['code' => 20000, 'msg' => '添加成功', 'data' => $order]);
        } catch (Exception $exception) {
            return json(['code' => 40000, 'msg' => $exception->getMessage()]);
        }
    }


    public function upload($filePath)
    {

        $uploadMgr = new UploadManager();
        $accessKey = '7Kwq-0ItVC9dcD3E1_C5eq3AQMIStAV2_hRzE8VX';
        $secretKey = 'e-zQjERZh5r7Qhg3XAkL5eU5ch8BdbnYf4jzQwGa';
        $auth = new Auth($accessKey, $secretKey);
        $bucket = 'suyunwenjian';
        $key = $filePath;
        $token = $auth->uploadToken($bucket);
        list($ret, $error) = $uploadMgr->putFile($token, $key, '.' . $filePath);
        if ($error !== null) {
            var_dump($error);
        } else {
            return $ret;
        }
    }


    public function add_document()
    {
        if (!input('?post.cart_id')) {
            return json(['code' => 40000, 'msg' => '缺少参数']);
        }
        $cart_id = $this->request->param('cart_id');
        try {
            $cart = CartModel::get($cart_id);
            if (!$cart) {
                return json(['code' => 40000, 'msg' => '文件不存在']);
            }
            $this->upload($cart['file']);
            if ($cart['source_file']) {
                $this->upload($cart['source_file']);
            }
            if ($cart['cover_image']) {
                $this->upload($cart['cover_image']);
            }
            unset($cart['id']);
            $cart['cart_id'] = $cart_id;
            $res = UserDocumentModel::create($cart->toArray(), true);
            if ($res) {
                return json(['code' => 20000, 'msg' => '添加成功']);
            } else {
                return json(['code' => 40000, 'msg' => '添加失败']);
            }
        } catch (Exception $exception) {
            return json(['code' => 40000, 'msg' => $exception->getMessage()]);
        }
    }


    public function document_list()
    {
        if (!input('?post.page')) {
            return json(['code' => 40000, 'msg' => '缺少参数']);
        }
        $page = $this->request->param('page');
        $list = UserDocumentModel::document_list($page, $this->user_id);
        return json(['code' => 20000, 'msg' => '获取成功', 'data' => $list]);
    }


    public function order_list()
    {
        if (!input('?post.page')) {
            return json(['code' => 40000, 'msg' => '缺少参数']);
        }
        $star_time=$this->request->param('star_time');
        $end_time=$this->request->param('end_time');
        $type=$this->request->param('type');
        $pay_status=$this->request->param('pay_status');
        $file_num=$this->request->param('file_num');
        $document=$this->request->param('document');
        $where=[];
        if ($star_time){
            $where['create_time']=['>=',strtotime($star_time)];
        }
        if ($end_time){
            $where['create_time']=['<=',strtotime($end_time)];
        }
        if ($type){
            $where['import']=$type;
        }
        if ($pay_status){
            $where['pay_status']=$pay_status;
        }
        if ($document){
            $where['pay_status']=0;
        }else{
            $where['pay_status']=['>',0];
        }

        if ($file_num){
            $where['file_num']=['>',0];
        }
        $page = $this->request->param('page');
        $list = OrderModel::pc_list($page, $this->user_id,$where);
        return json(['code' => 20000, 'msg' => '获取成功', 'data' => $list]);

    }


    public function order_detail_add()
    {
        if (!input('?post.order_id') || !input('?post.detail')) {
            return json(['code' => 40000, 'msg' => '缺少参数']);
        }
        try {
            $order_id = $this->request->param('order_id');
            $json = $this->request->param('detail');
            $order = OrderModel::get($order_id);
            if (!$order) {
                return json(['code' => 40000, 'msg' => '订单不存在']);
            }
            $total_price = 0;
            $file_num = 0;
            $json = json_decode($json, true);
            foreach ($json as $v) {
                $user_document = UserDocumentModel::get(['id' => $v['document_id']]);
                if (!$user_document) {
                    return json(['code' => 40000, 'msg' => '文件不存在']);
                }
                $order_detail=OrderDetailModel::where(['user_document_id'=>$v['document_id'],'order_id'=>$order_id])->find();
                $detail['order_id'] = $order_id;
                $detail['user_id'] = $user_document['user_id'];
                $detail['user_document_id'] = $user_document['id'];
                $detail['file'] = $user_document['file'];
                $detail['name'] = $user_document['name'];
                $detail['paper'] = $user_document['paper'];
                $detail['brand'] = $user_document['brand'];
                $detail['page_turning'] = $user_document['page_turning'];
                $detail['single_double'] = $user_document['single_double'];
                $detail['colour'] = $user_document['colour'];
                $detail['color'] = $user_document['color'];
                $detail['page'] = $user_document['page'];
                $detail['binding_type'] = $user_document['binding_type'];
                $detail['film_covering'] = $user_document['film_covering'];
                $detail['cover_type'] = $user_document['cover_type'];
                $detail['cover_image'] = $user_document['cover_image'];
                $detail['reduction_printing'] = $user_document['reduction_printing'];
                $detail['print_range'] = $user_document['print_range'];


                $detail['price'] = $user_document['price'];
                $detail['create_time'] = time();
//                $detail['source_file']=$user_document['source_file'];
                $detail['type'] = $user_document['type'];
                $detail['password'] = $user_document['password'];
                $total_price += $detail['price'] * $v['num'];
                $file_num += $v['num'];
                if ($order_detail){
                    $order_detail->num=$v['num'];
                    $order_detail->save();
                }else{
                    $detail['num']=$v['num'];
                    OrderDetailModel::create($detail);
                }

            }
            $order->file_num = $file_num;
            $order->order_price = $total_price;
            $order->pay_status= 0;
            if ($total_price < 10) {
                $order->total_price = 10;
            } else {
                $order->total_price = $total_price;
            }


            $order->save();
            return json(['code' => 20000, 'msg' => '添加成功']);
        } catch (Exception $exception) {
            return json(['code' => 40000, 'msg' => $exception->getMessage()]);
        }
    }


    public function num_edit()
    {
        if (!input('?post.detail_id') || !input('?post.num')) {
            return json(['code' => 40000, 'msg' => '缺少参数']);
        }
        try {
            $detail_id= $this->request->param('detail_id');
            $num = $this->request->param('num');
            $detail = OrderDetailModel::get($detail_id);
            if (!$detail) {
                return json(['code' => 40000, 'msg' => '订单不存在']);
            }
            $detail->num=$num;
            $detail->save();
            $total_price = 0;
            $file_num = 0;
            $order=OrderModel::get($detail->order_id);
            $list=OrderDetailModel::where('order_id',$detail->order_id)->select();
            foreach ($list as $v) {
                $total_price += $v['price'] * $v['num'];
                $file_num += $v['num'];
            }
            $order->file_num = $file_num;
            $order->order_price = $total_price;
            $order->pay_status= 0;
            if ($total_price < 10) {
                $order->total_price = 10;
            } else {
                $order->total_price = $total_price;
            }

            return json(['code' => 20000, 'msg' => '修改成功']);
        }catch (Exception $exception){
            return json(['code' => 40000, 'msg' => $exception->getMessage()]);
        }


    }


    public function order_confirm()
    {
        if (!input('?post.order_ids')){
            return json(['code' => 40000, 'msg' => '缺少参数']);
        }
        try {
            $order_id = $this->request->param('order_ids');
            $total_price = OrderModel::where('id','in',$order_id)->sum('total_price');
            return json(['code' => 20000, 'msg' => '获取成功','data'=>$total_price]);
        } catch (Exception $exception) {
            return json(['code' => 40000, 'msg' => $exception->getMessage()]);
        }
    }


    public function order_pay()
    {
        if (!input('?post.order_ids')) {
            return json(['code' => 40000, 'msg' => '缺少参数']);
        }
        try {
            $order_id = $this->request->param('order_ids');
            $payment['payment_sn']=date('YmdHis').rand(100000,999999);
            $payment['user_id']=$this->user_id;
            $payment['createtime']=time();
            $model=new PaymentModel();
            $payment['amount']=OrderModel::where('id','in',$order_id)->sum('total_price');
            $user_ticket_id = $this->request->param('user_ticket_id');
            if ($user_ticket_id > 0) {
                $user_ticket = UserTicketModel::get($user_ticket_id);
                if($user_ticket['type']==0){
                    $ticket_price = $user_ticket['deduction_price'];
                }else{
                    $ticket_price =$payment['amount'] *  $user_ticket['discount'] / 100;
                }
                if ($user_ticket['ticket_id'] > 0){
                    UserTicketModel::update(['status' => 1], ['id' => $user_ticket_id]);
                }

            } else {
                $ticket_price = 0;
            }
            $payment['amount'] = 0;
            $order=OrderModel::where('id', 'in', $order_id)->select();
            foreach ($order as $v) {
                $order_ticket_price=$ticket_price/count($order);
                $total_price = $v['order_price'] - $order_ticket_price;
                $v->total_price=$total_price;
                $v->ticket_price=$order_ticket_price;
                $payment['amount']+=$total_price;
                $v->save();
            }
            $payment_id=$model->insertGetId($payment);
            OrderModel::where('id', 'in', $order_id)->update(['payment_id'=>$payment_id]);
            $wx = new WechatController();
            $res = $wx->native($payment['payment_sn'], $payment['amount']);
            return json(['code' => 20000, 'msg' => '获取成功', 'data' => $res,'payment_id'=>$payment_id,'total_price'=>$payment['amount']]);
        } catch (Exception $exception) {
            return json(['code' => 40000, 'msg' => $exception->getMessage()]);
        }

    }


    public function payment_detail()
    {
        $payment_id=$this->request->param('payment_id');
        $payment=PaymentModel::get($payment_id);
        return json(['code' => 20000, 'msg' => '获取成功', 'data' => $payment]);
    }


    public function order_copy()
    {
        if (!input('?post.order_id') || !input('?post.copy_id')) {
            return json(['code' => 40000, 'msg' => '缺少参数']);
        }
        try {
            $order_id = $this->request->param('order_id');
            $copy_id = $this->request->param('copy_id');
            $order = OrderModel::get($order_id);
            if (!$order) {
                return json(['code' => 40000, 'msg' => '订单不存在']);
            }
            $total_price = 0;
            $file_num = 0;
            $order_detail = OrderDetailModel::where(['order_id' => $copy_id])->select();
            foreach ($order_detail as $v) {
                $detail['order_id'] = $order_id;
                $detail['user_id'] = $v['user_id'];
                $detail['user_document_id'] = $v['user_document_id'];
                $detail['file'] = $v['file'];
                $detail['name'] = $v['name'];
                $detail['paper'] = $v['paper'];
                $detail['brand'] = $v['brand'];
                $detail['page_turning'] = $v['page_turning'];
                $detail['single_double'] = $v['single_double'];
                $detail['colour'] = $v['colour'];
                $detail['color'] = $v['color'];
                $detail['page'] = $v['page'];
                $detail['binding_type'] = $v['binding_type'];
                $detail['film_covering'] = $v['film_covering'];
                $detail['cover_type'] = $v['cover_type'];
                $detail['cover_image'] = $v['cover_image'];
                $detail['reduction_printing'] = $v['reduction_printing'];
                $detail['print_range'] = $v['print_range'];
                $detail['price'] = $v['price'];
                $detail['create_time'] = time();
                $detail['type'] = $v['type'];
                $detail['password'] = $v['password'];
                $detail['num']=$v['num'];
                $total_price += $v['price'] * $v['num'];
                $file_num += $v['num'];
                OrderDetailModel::create($detail);
            }
            $order->file_num = $file_num;
            $order->order_price = $total_price;
            $order->total_price = $total_price;
            $order->save();
            return json(['code' => 20000, 'msg' => '添加成功']);
        } catch (Exception $exception) {
            return json(['code' => 40000, 'msg' => $exception->getMessage()]);
        }
    }


    public function export()
    {
        ini_set('memory_limit', '256M');
        set_time_limit(0);
        $star_time=$this->request->param('star_time');
        $end_time=$this->request->param('end_time');
        $type=$this->request->param('type');
        $pay_status=$this->request->param('pay_status');
        $where=[];
        $where['user_id']=['=',$this->user_id];
        $where['is_document']=['=','1'];
        if ($star_time){
            $where['create_time']=['>=',strtotime($star_time)];
        }
        if ($end_time){
            $where['create_time']=['<=',strtotime($end_time)];
        }
        if ($type){
            $where['import']=$type;
        }
        if ($pay_status){
            $where['pay_status']=$pay_status;
        }

        $model=new OrderModel();
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', '收件人')
            ->setCellValue('B1', '手机号')
            ->setCellValue('C1', '收货地址')
            ->setCellValue('D1', '平台订单号')
            ->setCellValue('E1', '商品信息')
            ->setCellValue('F1', '规格信息')
            ->setCellValue('G1', '商品数量')
            ->setCellValue('H1', '重量kg')
            ->setCellValue('I1', '备注');

        $total = $model
            ->where($where)

            ->count();

        $list = $model
            ->where($where)
            ->select();
//        $list = $list->toArray();
        $i = 2;
        $max_id = $model
            ->where($where)

            ->max('id');
        $min_id = $model
            ->where($where)
            ->min('id');
        foreach ($list as $value) {
            // Add data
            $spreadsheet->getActiveSheet()
                ->setCellValue('A' . $i, $value['name'])
                ->setCellValueExplicit('B' . $i, '' . $value['phone'], DataType::TYPE_STRING)
                ->setCellValue('C' . $i, $value['address'])
                ->setCellValue('D' . $i, '')
                ->setCellValue('E' . $i, '')
                ->setCellValue('F' . $i, '')
                ->setCellValue('G' . $i, $value['file_num'])
                ->setCellValue('H' . $i, '')
                ->setCellValueExplicit('I' . $i, '' . $value['order_num'], DataType::TYPE_STRING);
            $i++;
        }
        $file_name = $total . '个订单+开始id:' . $min_id . '-结束id:' . $max_id;
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
        //下载文档
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $file_name . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
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


}