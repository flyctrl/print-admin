<?php

namespace app\api\model;

class UserCollectModel extends BaseModel
{
    protected $name='user_collect';

    static function collect_list($user_id){

       $information_id=self::where('user_id',$user_id)->field('information_id')->column('information_id');

        $list=InformationModel::where('id','in',$information_id)->field('id,name,image')->select();
        foreach ($list as $value){
            $value['image']=cdnurl($value['image'],true);
            $value['file_num']=FileModel::where('information_id',$value['id'])->count();
        }
        return $list;

    }


}