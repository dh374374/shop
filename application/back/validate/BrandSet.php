<?php


namespace app\back\validate;


use think\Validate;

class BrandSet extends Validate
{
    //定义的规则
    protected $rule = [
        'title' => 'require|unique:brand,title',
        'site' => 'url',
        'sort' => 'require|integer',
    ];

    //定义的错误信息的名字翻译
    protected $field = [
        'title' => '品牌123',
        'site' => '官网',
        'sort' => '排序',
    ];

}