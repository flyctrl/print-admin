<?php

namespace app\api\model;

class InformationModel extends BaseModel
{
    protected $name='information';



    static function information($category_one_id,$category_two_id,$keywords){
        $map=[];
        if ($category_one_id>0){
            $map['category_one_id']=['=',$category_one_id];
        }

        if ($category_two_id>0){
            $map['category_two_id']=['=',$category_two_id];
        }
        if ($keywords){
            $map['name']=['like','%'.$keywords.'%'];
        }
        $list=self::where($map)->field('id,name,image,download')->select();
        foreach ($list as $value){
            $value['image']=cdnurl($value['image'],true);
            $value['file_num']=FileModel::where('information_id',$value['id'])->count();
        }
        return $list;

    }


    static function detail($id,$user_id){
        $list=self::where('id',$id)->field('id,name,images,content,download')->find();
        $file=FileModel::where('information_id',$id)->field('id,paper_id,single_double,colour_id,binding_type')->find();
        $list['paper']=PaperModel::where('id',$file['paper_id'])->value('name');
        $list['colour']=ColourModel::where('id',$file['colour_id'])->value('name');
        $list['file_num']=FileModel::where('information_id',$id)->count();
        $list['single_double']=$file['single_double'];
        $list['binding_type']=$file['binding_type'];
        $files=FileModel::where('information_id',$id)->field('id,file,name,page')->select();
        foreach ($files as $value){
            $value['file']=cdnurl($value['file'],true);
        }
        $collect=UserCollectModel::get(['user_id'=>$user_id,'information_id'=>$id]);
        if ($collect){
            $list['collect']=true;
        }else{
            $list['collect']=false;
        }
        $list['file']=$files;
        $images=explode(',',$list['images']);
        foreach ($images as &$image){
            $image=cdnurl($image,true);
        }
        $list['images']=$images;
        return $list;

    }
}