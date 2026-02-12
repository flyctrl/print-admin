<?php

namespace app\api\model;

class CategoryModel extends BaseModel
{
    protected $name='category';


    static function category_one(){

        $list=self::where('parent_id',0)->field('id,name')->select();
        array_unshift($list,['id'=>0,'name'=>'全部']);
        return $list;
    }



    static function category_two($parent_id){

        $list=self::where('parent_id',$parent_id)->field('id,name')->select();
        foreach ($list as $value){
            $value['count']=InformationModel::where('category_two_id',$value['id'])->count();
        }
        array_unshift($list,['id'=>0,'name'=>'全部']);
        return $list;
    }

}