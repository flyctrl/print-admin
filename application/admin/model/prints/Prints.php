<?php

namespace app\admin\model\prints;

use think\Model;


class Prints extends Model
{

    

    

    // 表名
    protected $name = 'prints';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'single_double_text',
        'create_time_text'
    ];
    

    
    public function getSingleDoubleList()
    {
        return ['0' => __('Single_double 0'), '1' => __('Single_double 1')];
    }


    public function getSingleDoubleTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['single_double']) ? $data['single_double'] : '');
        $list = $this->getSingleDoubleList();
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


    public function brand()
    {
        return $this->belongsTo('app\admin\model\Brand', 'brand_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function paper()
    {
        return $this->belongsTo('app\admin\model\Paper', 'paper_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function colour()
    {
        return $this->belongsTo('app\admin\model\Colour', 'colour_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
