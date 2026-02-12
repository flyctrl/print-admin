<?php

namespace app\api\controller;

use app\api\controller\BaseController;
use PhpOffice\PhpWord\Shared\ZipArchive;

class ZipController extends BaseController
{
    public function zip_list()
    {
        $file=$this->request->param('file');
        $zip=new ZipArchive();
        $file_list=[];
        if ($zip->open('.'.$file)===true){
            $zip->extractTo( ROOT_PATH . 'public/uploads/'.date('Ymd'));
            for($i=0; $i<$zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $file_list[$i]['file'] = '/uploads/' . date('Ymd') . '/' . $filename;
                $file_list[$i]['name'] = $filename;
            }
        }
        return json(['code'=>20000,'msg'=>'获取数据成功','data'=>$file_list]);
    }
}