<?php

namespace app\admin\controller\order;

use app\api\model\AccessTokenModel;
use app\api\model\ExpressModel;
use app\api\model\OrderDetailModel;
use app\common\controller\Backend;
use DateTime;
use DateTimeZone;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\Shared\ZipArchive;
use think\Db;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Queue;

/**
 * 订单列管理
 *
 * @icon fa fa-circle-o
 */
class Prints extends Backend
{
    protected $noNeedRight = ['export', 'import'];

    /**
     * Prints模型对象
     * @var \app\admin\model\order\Prints
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\order\Prints;
        $this->view->assign("payTypeList", $this->model->getPayTypeList());
        $this->view->assign("payStatusList", $this->model->getPayStatusList());
        $this->view->assign("invoiceList", $this->model->getInvoiceList());
        $this->view->assign("ImportList", $this->model->getImportList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->with(['user'])
                ->where($where)
                ->where('pay_status', '1')
                // ->where('pay_type','neq',0)
                ->order($sort, $order)
                ->paginate($limit);
            foreach ($list as $row) {
                $row['info'] = $row->name . '-' . $row['phone'] . '-' . $row['address'];
                $express = \app\admin\model\order\Express::get(['order_id' => $row['id']]);
                if ($express) {
                    $row['address_status'] = $express['status'];
                } else {
                    $row['address_status'] = '-1';
                }


            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }


    public function getAccessToken()
    {
        $appid =config('wxinfo.appId');
        $secret = config('wxinfo.secretId');
        $data = AccessTokenModel::get(1);
        if ($data) {
            if ($data['createtime'] + 7000 < time()) {
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret;
                $access = file_get_contents($url);
                $access_arr = json_decode($access, true);
                //非网页的access_token
                $access_token = $access_arr['access_token'];
                AccessTokenModel::update(['access_token' => $access_token, 'createtime' => time()], ['id' => $data['id']]);
            } else {
                $access_token = $data['access_token'];
            }
        } else {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret;
            $access = file_get_contents($url);
            $access_arr = json_decode($access, true);

            //非网页的access_token
            $access_token = $access_arr['access_token'];
            AccessTokenModel::create(['access_token' => $access_token, 'createtime' => time()]);
        }
        return $access_token;
    }


    public function wechat_express($order_id, $express_id,$express_num)
    {

        $order=\app\admin\model\order\Order::get($order_id);
        $express=ExpressModel::get($express_id);
        $user=\app\admin\model\User::get($order['user_id']);
        $access_token = $this->getAccessToken();
        $data['order_key']['order_number_type']='2';
        $data['order_key']['transaction_id'] = $order['transaction_id'];
        $data['logistics_type']='1';
        $data['delivery_mode']='UNIFIED_DELIVERY';
        $data['shipping_list'][0]=[
            'tracking_no'=>$express_num,
            'express_company'=>$express['wechat_code'],
            'item_desc'=>'打印商品-'.$order['order_num'],
            'contact'=>[
                'receiver_contact'=>substr($order['phone'], 0, 3) . '****' . substr($order['phone'], 7),
            ]
        ];
        $dateTime = new DateTime('now', new DateTimeZone('Asia/Shanghai'));
        $data['upload_time']=$dateTime->format('Y-m-d\TH:i:s.vP');;
        $data['payer']['openid']=$user['openid'];
        $url="https://api.weixin.qq.com/wxa/sec/order/upload_shipping_info?access_token=".$access_token;
        $data_json=json_encode($data, JSON_UNESCAPED_UNICODE);
        $res=https_request($url,$data_json);



    }


    /**
     * 编辑
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $params['pay_status'] = '2';
            if ($row['pay_type']==0){
                $this->wechat_express($ids,$params['express_id'],$params['express_num']);
            }

            $result = $row->allowField(true)->save($params);
            if (!$row['zip_file']) {
                $this->zip($ids);
            }
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }


    /**
     * 压缩
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function zip($ids)
    {
        $order = $this->model->get($ids);
        if ($order['express_num'] == '') {
            $this->error('请先输入快递单号');
        }
        $order_detail = OrderDetailModel::where('order_id', $ids)->select();
        // foreach ($order_detail as $item) {
        //     $this->get_pdf($item['id']);
        // }
        if ($order['import'] === 0) {
            $import = '小程序导入';
        } else if ($order['import'] === 1) {
            $import = '拼多多导入';
        } else {
            $import = '';
        }
        $zipFileName = ROOT_PATH . 'public/uploads/zip/' . $import . '+' . $ids . '+' . $order['express_num'] . '+' . $order['order_price'] . '+' . count($order_detail) . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // 要压缩的文件列表
            foreach ($order_detail as $key => $file) {
                if ($file['cover_type'] == '上传封面') {
                    $cover_image = '';
                } else {
                    $cover_image = $file['cover_image'] . '、';
                }
                $single_double = $file['single_double'] == 0 ? '单面' : '双面';
                $password = $file['password'] == 1 ? '密码' : '';
                if ($file['type'] == 2) {
                    $type = '检查方向';
                } else {
                    $type = '';
                }
                $file_name = $password . $file['brand'] . '、' . $file['paper'] . '、' . $type .
                    '、' . $file['colour'] . '、' . $single_double . '、'
                    . $file['print_range'] . '、' . $file['num'] . '、' . $file['reduction_printing'] . '、' . $file['binding_type'] . '(' . $file['cover_type'] . '、' . $cover_image . $file['color'] . '、' . $file['film_covering'] . ')' . $key;
                // 将文件添加到压缩包中，第二个参数是在压缩包中的文件名（可以和原文件名不同）
                $zip->addFile('.' . $file['file'], $file_name . '.pdf');

                if (is_file('.' . $file['cover_image'])) {
                    $zip->addFile('.' . $file['cover_image'], $file_name . '.png');
                }
                $json_array[$key]['file_name'] = $file_name;
                // $json_array[$key]['page_turning']=$file['page_turning'];
                $json_array[$key]['paper'] = $file['paper'];
                $json_array[$key]['brand'] = $file['brand'] . $file['colour'];
                $json_array[$key]['single_double'] = $file['single_double'] == 0 ? '单面' : '双面';
                $json_array[$key]['colour'] = $file['colour'];
                $json_array[$key]['color'] = $file['color'];
                $json_array[$key]['binding_type'] = $file['binding_type'];
                $json_array[$key]['film_covering'] = $file['film_covering'];
                $json_array[$key]['cover_type'] = $file['cover_type'];
                $json_array[$key]['reduction_printing'] = $file['reduction_printing'];
                $json_array[$key]['print_range'] = $file['print_range'];
                $json_array[$key]['num'] = $file['num'];
                $json_array[$key]['express_num'] = $order['express_num'];
                @unlink(ROOT_PATH . 'public/uploads/json/' . $order['order_num'] . '.json');
                file_put_contents(ROOT_PATH . 'public/uploads/json/' . $order['order_num'] . '.json', json_encode($json_array));
                if (is_file('.' . '/uploads/json/' . $order['order_num'] . '.json')) {
                    $zip->addFile('.' . '/uploads/json/' . $order['order_num'] . '.json', $order['order_num'] . '.json');
                }
            }
            // 关闭压缩包
            $zip->close();
        }

        $this->model->where('id', $ids)->update(['zip_file' => '/uploads/zip/' . $import . '+' . $ids . '+' . $order['express_num'] . '+' . $order['order_price'] . '+' . count($order_detail) . '.zip', 'pay_status' => $order['pay_status']]);
    }


    public function export()
    {
        ini_set('memory_limit', '256M');
        if ($this->request->isPost()) {
            set_time_limit(0);
            $search = $this->request->post('search');
            $ids = $this->request->post('ids');
            $filter = $this->request->post('filter');
            $op = $this->request->post('op');
            $columns = $this->request->post('columns');

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
            $whereIds = $ids == 'all' ? '1=1' : ['id' => ['in', explode(',', $ids)]];
            $this->request->get(['search' => $search, 'ids' => $ids, 'filter' => $filter, 'op' => $op]);
            //设置过滤方法
            $this->request->filter(['strip_tags']);

            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($whereIds)
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($whereIds)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $list = collection($list)->toArray();
            $i = 2;
            $max_id = $this->model
                ->where($whereIds)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->max('id');
            $min_id = $this->model
                ->where($whereIds)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
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
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }
    }


    public function import()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }

        $result = false;
        Db::startTrans();
        try {
            $file = $params['file'];
            $type = $params['type'];
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
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }

            //加载文件
            $insert = 0;
            $success = [];
            $error = [];
            try {
                if (!$PHPExcel = $reader->load($filePath)) {
                    $this->error(__('Unknown data format'));
                }
                $currentSheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表

                $allRow = $currentSheet->getHighestRow(); //取得一共有多少行

                for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                    if ($type == 0) {
                        $order_num = $currentSheet->getCell('R' . $currentRow)->getValue();
                        $express = $currentSheet->getCell('S' . $currentRow)->getValue();
                        $express_num = $currentSheet->getCell('T' . $currentRow)->getValue();
                    } else if ($type == 1){
                        $order_num = $currentSheet->getCell('J' . $currentRow)->getValue();
                        $order_num=preg_replace("/[^0-9]/", "", $order_num);
                        $express = $currentSheet->getCell('F' . $currentRow)->getValue();
                        $express_num = $currentSheet->getCell('G' . $currentRow)->getValue();
                    }else{
                        $order_num = (string)$currentSheet->getCell('N' . $currentRow)->getValue();
                        $K=$currentSheet->getCell('K' . $currentRow)->getValue();
                        $express_arr=explode('-',$K);
                        $express_num=$express_arr[1];
                        $express=$express_arr[0];

                    }
                    $order = $this->model->get(['order_num' => trim($order_num)]);
                    if ($express == '中通') {
                        $express = '中通快递';
                    }
                    $express_id = ExpressModel::where('name', trim($express))->value('id');

                    if ($type==0){
                        $this->wechat_express($order['id'],$express_id,trim($express_num));
                    }
                    if ($order) {
                        $this->model->update(['express_id' => $express_id, 'express_num' => trim($express_num), 'import' => $type], ['order_num' => trim($order_num)]);
                        $jobHandlerClassName = 'app\admin\job\Zip';
                        // 2.当前任务归属的队列名称，如果为新队列，会自动创建
                        $jobQueueName = "ZipJobQueue";
                        $jobData['order_id'] = $order['id'];
                        Queue::push($jobHandlerClassName, $jobData, $jobQueueName);
                        $insert++;
                        $success[] = $order_num;

                    }
                    if (!$order) {
                        $error[] = $order_num;
                    }
                }
            } catch (Exception $exception) {
                $this->error($exception->getMessage());
            }

            if ($insert == 0) {
                $this->error(__('No rows were updated'));
            }
            Db::commit();
            $result = true;
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success(',本次修改' . $insert . '条', '', ['success' => $success, 'error' => $error]);
    }


}
