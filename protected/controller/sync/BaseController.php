<?php

namespace app\controller\sync;
use app\lib\blog\Theme;
use app\Sync;

class BaseController extends Theme
{
    public $layout = "";
    public function init()
    {
        header("Content-type: text/html; charset=utf-8");
        Sync::response(3600);//最大运行时间为3600s即60分钟
    }

} 