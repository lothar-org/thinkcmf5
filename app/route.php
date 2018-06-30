<?php
$runtimeRoutes = [];
$files = \think\Config::get('route_config_file');
foreach ($files as $file) {
    if (file_exists(CMF_ROOT . 'data/conf/'.$file.'.php')) {
        $runtimeRoutes += include CMF_ROOT.'data/conf/'.$file.'.php';
        // $runtimeRoutes = array_merge($runtimeRoutes,include CMF_ROOT.'data/conf/'.$file.'.php');
    }
}

return $runtimeRoutes;
