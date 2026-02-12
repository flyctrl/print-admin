<?php

namespace app\api\model;

class UserDocumentModel extends BaseModel
{

    protected $name = 'user_document';

    public static function document_list($page, $user_id)
    {
        $list=self::where('user_id',$user_id)
            ->order('id desc')
            ->paginate(10, false, ['page'=>$page])
            ->each(function ($item, $key) {

            });
        return $list;
    }


}