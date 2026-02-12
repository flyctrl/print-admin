<?php

namespace app\admin\model\order;

use think\Model;


class Detail extends Model
{

    

    

    // 表名
    protected $name = 'order_detail';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'information_text',
        'single_double_text',
        'type_text',
        'create_time_text'
    ];
    

    
    public function getInformationList()
    {
        return ['0' => __('Information 0'), '1' => __('Information 1')];
    }

    public function getSingleDoubleList()
    {
        return ['0' => __('Single_double 0'), '1' => __('Single_double 1')];
    }

    public function getTypeList()
    {
        return ['0' => __('Type 0'), '1' => __('Type 1'), '2' => __('Type 2')];
    }


    public function getInformationTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['information']) ? $data['information'] : '');
        $list = $this->getInformationList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getSingleDoubleTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['single_double']) ? $data['single_double'] : '');
        $list = $this->getSingleDoubleList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function order()
    {
        return $this->belongsTo('app\admin\model\Order', 'order_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
