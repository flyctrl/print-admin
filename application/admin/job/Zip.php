<?php

namespace app\admin\job;


use app\api\model\OrderDetailModel;
use app\api\model\OrderModel;
use PhpOffice\PhpWord\Shared\ZipArchive;
use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;
use Qiniu\Storage\UploadManager;
use think\Controller;
use think\queue\Job;

class Zip extends Controller
{
    public function fire(Job $job, $data)
    {

        // 有些消息在到达消费者时,可能已经不再需要执行了
        $isJobStillNeedToBeDone = $this->checkDatabaseToSeeIfJobNeedToBeDone($data);
        if (!$isJobStillNeedToBeDone) {
            $job->delete();
            return;
        }
        $ids=$data['order_id'];
        $order =  OrderModel::get($ids);
        if ($order['is_document']==1){
            $isJobDone = $this->qiniu_zip($data);
        }else{
            $isJobDone = $this->doZipJob($data);
        }


        if ($isJobDone) {
            // 如果任务执行成功， 记得删除任务
            $job->delete();

            print("<info>Hello Job has been done and deleted" . "</info>\n");

        }
    }

    /**
     * 有些消息在到达消费者时,可能已经不再需要执行了
     * @param array|mixed $data 发布任务时自定义的数据
     * @return boolean                 任务执行的结果
     */
    private function checkDatabaseToSeeIfJobNeedToBeDone($data)
    {
        return true;
    }

    /**
     * 根据消息中的数据进行实际的业务处理...
     */
    private function doZipJob($data)
    {

        $ids=$data['order_id'];
        $order =  OrderModel::get($ids);
        if ($order['express_num'] == '') {
            $this->error('请先输入快递单号');
        }
        $order_detail = OrderDetailModel::where('order_id', $ids)->select();
        // foreach ($order_detail as $item) {
        //     $this->get_pdf($item['id']);
        // }
        if ($order['import']==0){
            $import='小程序导入';
        }else if ($order['import']==1){
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
                var_dump($file_name);
                if ($file['source_file']=='' ){
                    $zip->addFile(ROOT_PATH .'public' . $file['file'], $file_name.'.pdf');
                }else{
                    //获取文件后缀
                    $fileInfo = pathinfo(ROOT_PATH .'public' .$file['source_file']);
                    $extension = $fileInfo['extension'];
                    $zip->addFile(ROOT_PATH .'public' .$file['source_file'], $file_name.'.'.$extension);

                }


                if (is_file(ROOT_PATH .'public'.$file['cover_image'])){
                    $zip->addFile(ROOT_PATH .'public' .$file['cover_image'], $file_name.'.png');
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
                file_put_contents(ROOT_PATH . 'public/uploads/json/'.$order['order_num'].'.json',json_encode($json_array));
                if (is_file(ROOT_PATH .'public/uploads/json/'.$order['order_num'].'.json')){
                    $zip->addFile(ROOT_PATH .'public/uploads/json/'.$order['order_num'].'.json', $order['order_num'].'.json');
                }
            }
            // 关闭压缩包
            $zip->close();
        }
        OrderModel::where('id', $ids)->update(['zip_file' => '/uploads/zip/'  .$import.'+' .$ids.'+'. $order['express_num'] . '+' .$order['order_price'] .'+'.count($order_detail). '.zip', 'pay_status' => '2']);




        return true;
    }



    public function failed($jobData){

        print("Warning: Job failed after max retries. job data is :".var_export($jobData,true));
    }


    public function qiniu_zip($data)
    {
        $ids=$data['order_id'];
        $order =  OrderModel::get($ids);
        if ($order['express_num'] == '') {
            $this->error('请先输入快递单号');
        }
        $order_detail = OrderDetailModel::where('order_id', $ids)->select();
        // foreach ($order_detail as $item) {
        //     $this->get_pdf($item['id']);
        // }
        if ($order['import']==0){
            $import='小程序导入';
        }else if ($order['import']==1){
            $import='拼多多导入';
        }else{
            $import='';
        }
        $str='';
        $zipFileName ='/uploads/zip/'   .$import.'+' . $ids.'+'.$order['express_num'] . '+' . $order['order_price'].'+'.count($order_detail).'.zip';
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
            if ($file['source_file']==''){
                $str .= '/url/' . \Qiniu\base64_urlSafeEncode(cdnurl($file['file'], true));
                $str.='/alias/'.\Qiniu\base64_urlSafeEncode($file_name.'.pdf')."\r\n";

            }else{
                //获取文件后缀
                $fileInfo = pathinfo(ROOT_PATH .'public' .$file['source_file']);
                $extension = $fileInfo['extension'];
                $str .= '/url/' . \Qiniu\base64_urlSafeEncode(cdnurl($file['source_file'], true));
                $str.='/alias/'.\Qiniu\base64_urlSafeEncode( $file_name.'.'.$extension)."\r\n";
            }


            if (is_file(ROOT_PATH .'public'.$file['cover_image'])){
                $str .= '/url/' . \Qiniu\base64_urlSafeEncode(cdnurl($file['cover_image'], true));
                $str.='/alias/'.\Qiniu\base64_urlSafeEncode( $file_name.'.png')."\r\n";
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
            file_put_contents(ROOT_PATH . 'public/uploads/json/'.$order['order_num'].'.json',json_encode($json_array));
            if (is_file(ROOT_PATH .'public/uploads/json/'.$order['order_num'].'.json')){
                $this->upload('/uploads/json/'.$order['order_num'].'.json');
                $str .= '/url/' . \Qiniu\base64_urlSafeEncode(cdnurl('/uploads/json/'.$order['order_num'].'.json', true));
                $str.='/alias/'.\Qiniu\base64_urlSafeEncode( '/uploads/json/'.$order['order_num'].'.json')."\r\n";
            }
        }
        $this->zip($order['id'],$zipFileName,$str);
        return true;
    }


    public function upload($filePath)
    {

        $uploadMgr = new UploadManager();
        $accessKey ='7Kwq-0ItVC9dcD3E1_C5eq3AQMIStAV2_hRzE8VX';
        $secretKey = 'e-zQjERZh5r7Qhg3XAkL5eU5ch8BdbnYf4jzQwGa';
        $auth = new Auth($accessKey, $secretKey);
        $bucket ='suyunwenjian';
        $key=$filePath;
        $token = $auth->uploadToken($bucket);
        list($ret, $error) = $uploadMgr->putFile($token, $key, '.'.$filePath);
        if ($error !== null) {
            var_dump($error);
        } else {
            return $ret;
        }
    }



    public function zip($id,$zipKey,$str)
    {



        $accessKey = 'putW16OS737Up5pcPpzmW4p4GCikcgFwdXM39uwf';
        $secretKey = 'Iq_U5nlfgtauC5DgD-TPpvoMV0lCvokquyRDES7D';
        $pipeline = 'mkzip';
        $bucket = 'tupiancunk';
        $auth = new Auth($accessKey, $secretKey);
        $upload=new UploadManager();
        $pfop = new PersistentFop($auth);
        $token = $auth->uploadToken($bucket);
        $file_name = md5(time() . rand(10000,99999)).'.txt'; // 文件名

        $dir = 'zips'; // 保存到指定七牛云空间的文件夹路径（按照自己的空间路径设置即可）
        $key = $dir . '/' . $file_name;
        file_put_contents($file_name, $str, FILE_APPEND);

        $file_content = file_get_contents($file_name);
        $bool = $upload->put($token,$key,$file_content); //上传该文件到七牛云
        if ($bool){
            $fops= 'mkzip/4|saveas/' . \Qiniu\base64_urlSafeEncode($bucket . ":" . $zipKey);
            $notify_url = $this->request->domain() . '/api/Result/image';
            $force = false;
            unlink($file_name);
            list($id, $err) = $pfop->execute($bucket, $key, $fops, $pipeline, $notify_url, $force);
            OrderModel::update(['zip_id'=>$id],['id'=>$id]);
        }
        return true;


    }



}