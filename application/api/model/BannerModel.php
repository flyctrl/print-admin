<?php

namespace app\api\model;

class BannerModel extends BaseModel
{
    protected $name='banner';



    static function banner_list(){

        $list=self::field('id,image')->select();
        foreach ($list as $item){
            $item['image']=cdnurl($item['image'],true);
        }
        return $list;
    }
}