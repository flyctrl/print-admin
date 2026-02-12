<?php

namespace app\admin\controller\order;

use app\api\model\OrderDetailModel;
use app\api\model\OrderModel;
use app\common\controller\Backend;
use PhpOffice\PhpWord\Shared\ZipArchive;
use Picqer\Barcode\BarcodeGeneratorPNG;
use setasign\Fpdi\Fpdi;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 订单列管理
 *
 * @icon fa fa-circle-o
 */
class Order extends Backend
{
    protected $noNeedRight = ['detail', 'zip', 'download'];
    /**
     * Order模型对象
     * @var \app\admin\model\order\Order
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\order\Order;
        $this->view->assign("payTypeList", $this->model->getPayTypeList());
        $this->view->assign("payStatusList", $this->model->getPayStatusList());
        $this->view->assign("invoiceList", $this->model->getInvoiceList());
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
                ->where('pay_status', '0')
                // ->where('pay_type','neq',0)
                ->order($sort, $order)
                ->paginate($limit);

            foreach ($list as $row) {


            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }


    public function detail()
    {
        $ids = $this->request->param('ids');
        $order = $this->model->get($ids);
        $order_detail = OrderDetailModel::where('order_id', $ids)->select();
        $ding = 0;
        $total = 0;
        foreach ($order_detail as $item) {
            if ($item['binding_type'] != '不装订') {
                $ding += $item['num'];
            }
            $total += $item['num'];
        }
        $this->assign('order_detail', $order_detail);
        $this->assign('order', $order);
        $this->assign('ding', $ding);
        $this->assign('total', $total);
        return $this->view->fetch();
    }

    /**
     * 压缩
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function zip()
    {
        $ids = $this->request->param('ids');
        $order = $this->model->get($ids);

        if ($order['express_num'] == '') {
            $this->error('请先输入快递单号');
        }
        $order_detail = OrderDetailModel::where('order_id', $ids)->select();
        // foreach ($order_detail as $item) {
        //     $this->get_pdf($item['id']);
        // }
        if ($order['import']===0){
            $import='小程序导入';
        }else if ($order['import']===1){
            $import='拼多多导入';
        }else{
            $import='淘宝导入';
        }
        $zipFileName = ROOT_PATH . 'public/uploads/zip/'   .$import.'+' . $ids.'+'.$order['express_num'] . '+' . $order['order_price'].'+'.count($order_detail).'.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // 要压缩的文件列表
            foreach ($order_detail as $key=>$file) {
                if ($file['cover_type']=='上传封面'){
                    $cover_image='';
                }else{
                    $cover_image=$file['cover_image'].'、';
                }
                $single_double = $file['single_double'] == 0 ? '单面' : '双面';
                $password=$file['password']==1 ? '密码' : '';
                if ($file['type']==2){
                    $type='检查方向';
                }else{
                    $type='';
                }
                $file_name = $password.$file['brand'] . '、' . $file['paper'] .'、'.$type.
                    '、' . $file['colour'] . '、' . $single_double . '、'
                    . $file['print_range'] .'、'. $file['num'].'、'. $file['reduction_printing'] . '、' . $file['binding_type'] . '(' . $file['cover_type'] .'、'. $cover_image . $file['color'] . '、' . $file['film_covering'] . ')'.$key;
                // 将文件添加到压缩包中，第二个参数是在压缩包中的文件名（可以和原文件名不同）
                $zip->addFile('.' . $file['file'], $file_name.'.pdf');

                if (is_file('.'.$file['cover_image'])){
                    $zip->addFile('.'.$file['cover_image'], $file_name.'.png');
                }
                $json_array[$key]['file_name']=$file_name;
                // $json_array[$key]['page_turning']=$file['page_turning'];
                $json_array[$key]['paper']=$file['paper'];
                $json_array[$key]['brand']=$file['brand'].$file['colour'];
                $json_array[$key]['single_double']=$file['single_double'] == 0 ? '单面' : '双面';
                $json_array[$key]['colour']=$file['colour'];
                $json_array[$key]['color']=$file['color'];
                $json_array[$key]['binding_type']=$file['binding_type'];
                $json_array[$key]['film_covering']=$file['film_covering'];
                $json_array[$key]['cover_type']=$file['cover_type'];
                $json_array[$key]['reduction_printing']=$file['reduction_printing'];
                $json_array[$key]['print_range']=$file['print_range'];
                $json_array[$key]['num']=$file['num'];
                $json_array[$key]['express_num']=$order['express_num'];
                @unlink(ROOT_PATH . 'public/uploads/json/'.$order['order_num'].'.json');
                file_put_contents(ROOT_PATH . 'public/uploads/json/'.$order['order_num'].'.json',json_encode($json_array));
                if (is_file('.'.'/uploads/json/'.$order['order_num'].'.json')){
                    $zip->addFile('.'.'/uploads/json/'.$order['order_num'].'.json', $order['order_num'].'.json');
                }
            }
            // 关闭压缩包
            $zip->close();
        }

        $this->model->where('id', $ids)->update(['zip_file' => '/uploads/zip/'.$import.'+'.$ids.'+'. $order['express_num'] . '+' .$order['order_price'] .'+'.count($order_detail). '.zip', 'pay_status' => $order['pay_status']]);
        $this->success('操作成功');
    }


    public function get_pdf($id)
    {

        $order_detail = OrderDetailModel::get($id);
        $order = OrderModel::get($order_detail['order_id']);
        $code_file = ROOT_PATH . 'public/uploads/image/' . $id . '-bar-code.png';
        $this->barcode($order['express_num'], $code_file);
        // 输入PDF文件路径
        $pdfFilePath = '.' . $order_detail['file'];

        // 输出图片文件路径
        $imageFilePath = ROOT_PATH . 'public/uploads/image/' . $id . '.png';
        $imageFilePath_type = ROOT_PATH . 'public/uploads/image/' . $id . '-1.png';
        // 创建Imagick对象
        $imagick = new \Imagick();
        // 读取PDF文件的第一页
        $imagick->readImage($pdfFilePath . '[0]');
        $wight = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();
        if ($wight > $height) {
            // 设置图片格式和质量
            $imagick->setImageFormat('png');
            $imagick->setImageDepth(8);
            $imagick->setImageCompressionQuality(100);
            // 设置页面的分辨率（可选）
            $imagick->setResolution(300, 300);
            // 将页面转换为图片
            $image = clone $imagick;
            $image->setImageFormat('png');
            // 保存图片
            $image->writeImage($imageFilePath_type);
            $imagick->rotateImage(new \ImagickPixel(), 90);

            $type = '横版';
        } else {
            $imagick->setImageFormat('png');
            $imagick->setImageDepth(8);
            $imagick->setImageCompressionQuality(100);
            // 设置页面的分辨率（可选）
            $imagick->setResolution(300, 300);
            // 将页面转换为图片
            $image = clone $imagick;
            $image->setImageFormat('png');
            // 保存图片
            $image->writeImage($imageFilePath_type);
            $imagick->rotateImage(new \ImagickPixel(), 90);
            $type = '竖版';
        }


        // 设置图片格式和质量
        $imagick->setImageFormat('png');
        $imagick->setImageCompressionQuality(100);
        // 设置页面的分辨率（可选）
        $imagick->setResolution(300, 300);
        // 将页面转换为图片
        $image = clone $imagick;
        $image->setImageFormat('png');
        // 保存图片
        $image->writeImage($imageFilePath);
        // 清理Imagick对象
        $imagick->clear();
        $imagick->destroy();

        $image = '/uploads/image/' . $id . '.png';
        $image_type = '/uploads/image/' . $id . '-1.png';
        $pdf = new Fpdi();
        $template_pdf = '/uploads/template/template.pdf';
        $page_count = $pdf->setSourceFile('.' . $template_pdf);
        // 增加新的空白PDF页面
        $pdf->AddPage();
        // 导入页面
        $tplId = $pdf->importPage($page_count);
        // 填充页面
        $pdf->useTemplate($tplId);
        if ($type == '横版') {
            $pdf->Image('.' . $image, 155.5, 180, 40, 58);
        } else {
            $pdf->Image('.' . $image_type, 155.5, 180, 40, 58);
        }

        $barcode = '/uploads/image/' . $id . '-bar-code.png';

        $pdf->Image('.' . $barcode, 68, 35, 80, 25);
        // Set font
        $pdf->SetFont('Arial', '', 30);
        // Add text
        $pdf->SetXY(15, 20);
        $pdf->Cell(0, 10, $order['express_num'], 0, 1, 'C');
        $pdf->SetXY(15, 70);
        $pdf->Cell(0, 10, $order['order_num'], 0, 1, 'C');
        $pdf->AddGBFont('simhei', '黑体');
        $pdf->SetFont('simhei', '', 40);
        $pdf->SetXY(-310, 95);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Cell(0, 10, iconv("UTF-8", "gbk", $type), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 20);
        $pdf->SetXY(-340, 126);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 10, $order_detail['page'], 0, 1, 'C');
        $pdf->SetFont('Arial', '', 30);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->SetXY(-286, 124);
        $pdf->Cell(0, 10, $order_detail['num'], 0, 1, 'C');

        $single_double = $order_detail['single_double'] == 0 ? '单面' : '双面';
        $pdf->SetFont('simhei', '', 25);
        $pdf->SetXY(55, 90);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 10, iconv("UTF-8", "gbk", $order_detail['brand'] . " $single_double " . $order_detail['paper']), 0, 1, 'C');
        $pdf->SetXY(50, 102);
        $pdf->Cell(0, 10, iconv("UTF-8", "gbk", $order_detail['colour']), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 30);
        $pdf->SetTextColor(0, 0, 255);
        $pdf->SetXY(115, 124);
        $pdf->Cell(0, 10, $order_detail['num'], 0, 1, 'C');

        $pdf->SetFont('simhei', '', 20);
        $pdf->SetXY(-260, 150);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 10, iconv("UTF-8", "gbk", $order_detail['binding_type'] . '(' . $order_detail['cover_type'] . ')'), 0, 1, 'C');
        $pdf->SetXY(-100, 150);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Cell(0, 10, iconv("UTF-8", "gbk", $order_detail['color']), 0, 1, 'C');
        $pdf->SetFont('simhei', 'B', 15);
        $pdf->SetXY(-310, 225);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 10, iconv("UTF-8", "gbk", $order_detail['name']), 0, 1, 'C');

        if ($order_detail['cover_type'] != '') {
            if ($order_detail['cover_type'] == '首页为封面') {
                if ($type == '横版') {
                    $pdf->Image('.' . $image_type, 91, 176, 60, 40);
                } else {
                    $pdf->Image('.' . $image, 91, 176, 60, 40);
                }

            } else {

                if ($order_detail['cover_image'] != '') {
                    $pdf->Image('.' . $order_detail['cover_image'], 91, 176, 60, 40);
                }
            }
        }
        $str = '';
        $print_range = explode(',', $order_detail['print_range']);
        foreach ($print_range as $item) {
            $str .= $item . '页，';
        }

        $pdf->SetFont('simhei', 'B', 15);
        $pdf->SetXY(-310, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 10, iconv("UTF-8", "gbk", $str), 0, 1, 'C');
        $pages_count = $pdf->setSourceFile('.' . $order_detail['file']);
//
//        dump($pages_count);exit;
        if ($single_double == '双面') {
            $pdf->AddPage();
            // 导入页面
            $tplId = $pdf->importPage(2);
            // 填充页面

            for ($i = 1; $i <= $pages_count; $i++) {
                // 增加新的空白PDF页面
                $pdf->AddPage();
                // 导入页面
                $tplId = $pdf->importPage($i);
                // 填充页面
                $pdf->useTemplate($tplId);
            }
        } else {
            for ($i = 1; $i <= $pages_count; $i++) {
                // 增加新的空白PDF页面
                $pdf->AddPage();
                // 导入页面
                $tplId = $pdf->importPage($i);
                // 填充页面
                $pdf->useTemplate($tplId);
            }
        }

        if ($order_detail['cover_type'] == '上传封面') {
            $cover_image = '';
        } else {
            $cover_image = $order_detail['cover_image'] . '-';
        }
        $file_name = $order_detail['brand'] . '-' . $order_detail['paper'] .
            '-' . $order_detail['colour'] . '-' . $single_double . '-'
            . $type . '-' . $order_detail['reduction_printing'] . '-' . $order_detail['binding_type'] . '(' . $order_detail['cover_type'] . '-' . $cover_image . $order_detail['color'] . '-' . $order_detail['film_covering'] . ')' . $order_detail['name'];
        $destDir = ROOT_PATH . 'public/uploads/template/' . $file_name . '.pdf';
        OrderDetailModel::update(['template_file' => '/uploads/template/' . $file_name . '.pdf'], ['id' => $id]);
        $pdf->Output("F", $destDir);
    }


    /**
     * 生成条形码
     * @param $barcodeValue
     * @param $outputFile
     * @return void
     */
    public function barcode($barcodeValue, $outputFile)
    {
        $generator = new BarcodeGeneratorPNG();
        $barcodePngData = $generator->getBarcode($barcodeValue, $generator::TYPE_CODE_128, 2, 70);
        file_put_contents($outputFile, $barcodePngData);
    }


    public function download()
    {
        $ids = $this->request->param('ids');
        $order=$this->model->get($ids);
        $this->model->where('id', $ids)->update(['download' => 1]);
       $this->success('成功','',cdnurl($order['zip_file'],true));
    }



    public function remark($ids=null)
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
            $result = $row->allowField(true)->save($params);
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




    public function discount($ids=null)
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
        $params['total_price']=$row['total_price']-$params['discount'];
        $params['order_num']=date('YmdHis').rand(1000,9999);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
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

}
