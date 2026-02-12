<?php

namespace app\api\controller;

use AlibabaCloud\SDK\Imageenhan\V20190930\Models\GenerateDynamicImageRequest;
use AlibabaCloud\SDK\Imageenhan\V20190930\Models\MakeSuperResolutionImageRequest;
use AlibabaCloud\Tea\Exception\TeaError;
use app\common\exception\UploadException;
use app\common\library\Upload;
use AlibabaCloud\SDK\Imageenhan\V20190930\Imageenhan;
use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); // 允许请求的类型
header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
header('Access-Control-Allow-Headers:*');
class UploadController extends BaseController
{

    public function upload()
    {

        $attachment = null;
        //默认普通上传文件
        $file = $this->request->file('file');
        try {
            $upload = new Upload($file);
            $attachment = $upload->upload();
        } catch (UploadException $e) {
            $this->error($e->getMessage());
        }
        return json(['code' => 20000, 'msg' => '图片上传成功', 'data' => ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]]);
    }









    public function upload_url()
    {
        

        $url=$this->request->param('url');
        $name=$this->request->param('name');
        $type=$this->request->param('type');
        // 本地保存文件的路径
        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch,CURLOPT_URL,$url);
        $headers = array(
            'User-Agent: pan.baidu.com'
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/20100101 Firefox/17.0');
        $file_content = curl_exec($ch);
        curl_close($ch);
        $localFilePath = ROOT_PATH . 'public/uploads/'.date('Ymd').'/'.md5($name).'.'.$type;
        file_put_contents($localFilePath, $file_content);
//        var_dump($file_content);exit;

//        $downloaded_file = fopen($localFilePath, 'w');
//
//        fwrite($localFilePath, $file_content);
//        fclose($localFilePath);

//        // 使用file_get_contents()获取远程文件的内容

//        // 将获取到的文件内容保存到本地文件



        return json(['code' => 20000, 'msg' => '图片上传成功', 'data' => ['url' => '/uploads/'.date('Ymd').'/'.md5($name).'.'.$type, 'fullurl' => cdnurl('/uploads/'.date('Ymd').'/'.md5($name).'.'.$type, true)]]);
    }


    public function word_to_pdf()
    {



        $wordFile=$this->request->param('file');
        if (!$wordFile){
            return json(['code' => 40001, 'msg' => '请按照接口文档，规范传参']);
        }
        $pathInfo = pathinfo($wordFile);

        // 新的文件路径（将后缀替换为新后缀）
        $pdfFile = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.pdf';
        $file=ROOT_PATH.'public'.$wordFile;
        $str="soffice --headless --convert-to pdf $file  --outdir  /www/wwwroot/print/public".$pathInfo['dirname'] . '/' ;
        exec($str, $output, $return_var);
        if ($return_var == 0) {
             return json(['code' => 20000, 'msg' => '图片上传成功', 'data' => ['url' => $pdfFile, 'fullurl' => cdnurl($pdfFile, true)]]);
         } else {
             return json(['code' => 40000, 'msg' => '失败']);
             
         }
    }






    public static function createClient($accessKeyId, $accessKeySecret){
        $config = new Config([
            // 必填，您的 AccessKey ID
            "accessKeyId" => $accessKeyId,
            // 必填，您的 AccessKey Secret
            "accessKeySecret" => $accessKeySecret
        ]);
        // Endpoint 请参考 https://api.aliyun.com/product/imageenhan
        $config->endpoint = "imageenhan.cn-shanghai.aliyuncs.com";
        return new Imageenhan($config);
    }



    public  function image(){

        $generateSuperResolutionImageRequest = new MakeSuperResolutionImageRequest([
            "url" => $this->request->param('image')
        ]);
        $runtime = new RuntimeOptions([]);
        try {
            // 复制代码运行请自行打印 API 的返回值
            $res=$client->makeSuperResolutionImageWithOptions($generateSuperResolutionImageRequest, $runtime);
            dump($res);
        }
        catch (TeaError $error) {
            dump($error->getMessage());
        }
    }




}