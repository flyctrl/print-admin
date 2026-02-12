<?php

namespace app\api\model;

class CommentModel extends BaseModel
{
    protected $name='comment';



    static function comment_list($page){
        $list=self::field('id,nickname,avatar_image,star,label_ids,remark,images,like_num,create_time')
            ->paginate(10,false,['page'=>$page])
            ->each(function ($item){
                $item->avatar_image=cdnurl($item->avatar_image,true);
                $item->label=LabelModel::where('id','in',$item->label_ids)->column('name');
                $image=explode(',',$item->images);
                foreach ($image as &$value){
                    $value=cdnurl($value,true);
                }
                $item->images=$image;
                $item->create_time=date('Y-m-d',$item->create_time);
            });

        return $list;

    }
}