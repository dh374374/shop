<?php
function urlOrder($route, $param=[],$current, $field=null, $type='asc'){
    #确定排序方式
    $param['order_field'] = $current;
    $param['order_type'] = $current == $field && $type == 'asc'? 'desc':'asc';
    return url($route, $param);
}

function classOrder($current,$order_field,$order_type){
    if($current == $order_field){
        return $order_type;
    }else{
        return '';
    }
}