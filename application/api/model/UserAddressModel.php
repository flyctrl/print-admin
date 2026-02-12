<?php
/**
 * Created by PhpStorm.
 * User: win10-06-28
 * Date: 2021/5/6
 * Time: 10:15
 */


namespace app\api\model;

/**
 * 用户地址模型
 * Class AddressModel
 * @package app\api\model
 */
class UserAddressModel extends BaseModel{

    protected $name='user_address';


    /**
     * 获取用户地址详情
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function detail($id){
        $list=self::where('id',$id)->field('id,name,phone,province,city,area,address,default')
            ->find();
        $list->default = $list->default == 0 ?false :true;
        return $list;
    }


    /**
     * 获取用户地址列表
     * @param $user_id
     * @param $page
     * @param $limit
     * @return array
     * @throws \think\exception\DbException
     */
    static function address_list($user_id,$page){

        $list=self::where('user_id',$user_id)
            ->order('default','desc')
            ->field('id,name,phone,province,city,area,address,default')
            ->paginate(10,false,['page'=>$page])->each(function ($item){
                $item->default = $item->default == 0 ?false :true;
            });

        return $list;
    }


    /**
     * 获取用户默认地址
     * @param $user_id
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static function default_address($user_id){
       $address=self::where('user_id',$user_id)
            ->where('default','1')
            ->field('id,name,phone,province,city,area,address')
            ->find();

        return $address;
    }




}