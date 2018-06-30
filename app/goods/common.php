<?php
use think\Db;
use think\Request;

// 获取关系表ID。针对多对多特殊处理
// 不能用empty()判断，有的地方需要值为0填充
function getFmwId($data=[],$fmwId=0)
{
    if (empty($data)) {
        $data = Request::instance()->param();
        // $data = Request()->param('gid',0);
    }
    $selected = [];
    if (isset($data['parent']) && $data['parent']>0) {
        $fmwId = intval($data['parent']);
        $selected = Db::name('goods_category_fmw')->where('id',$fmwId)->value('fmw_path');
        $selected = explode('-',$selected);
    } else {
        if (isset($data['third']) && $data['third']>0) {
            $fmwId = intval($data['third']);
        } else {
            if (isset($data['second']) && $data['second']>0) {
                $fmwId = intval($data['second']);
            } else {
                if (isset($data['gameId']) && $data['gameId']>0) {
                    $fmwId = intval($data['gameId']);
                }
            }
        }
        $selected = [
            0,
            isset($data['gameId'])?$data['gameId']:0,
            isset($data['second'])?$data['second']:0,
            isset($data['third'])?$data['third']:0
        ];
    }

    return [$fmwId,$selected];
}



// 获取关系表ID。针对多对多特殊处理
/*function getFmwId($data=[],$fmwId=0)
{
    $selected = [];
    if (isset($data['parent']) && $data['parent']>0) {
        $fmwId = intval($data['parent']);
        $selected = Db::name('goods_category_fmw')->where('id',$fmwId)->value('fmw_path');
        $selected = explode('-',$selected);
    } else {
        if (isset($data['third']) && $data['third']>0) {
            $fmwId = intval($data['third']);
        } else {
            if (isset($data['second']) && $data['second']>0) {
                $fmwId = intval($data['second']);
            } else {
                if (isset($data['gameId']) && $data['gameId']>0) {
                    $fmwId = intval($data['gameId']);
                }
            }
        }
        $selected = [
            0,
            isset($data['gameId'])?$data['gameId']:0,
            isset($data['second'])?$data['second']:0,
            isset($data['third'])?$data['third']:0
        ];
    }
    return [$fmwId,$selected];
}*/



