<?php

namespace app\controller\sync;
use app\lib\blog\Theme;

class BaseController extends Theme
{
    public $layout = "";
    public function init()
    {
        header("Content-type: text/html; charset=utf-8");
      //  if(!$this->checkToken())exit;
        background('',3600);
    }

    private function checkToken(){
        $header=getHeader();
        if(!file_exists(APP_TMP.'sync_token')) return false;
        $token=json_decode(trim(file_get_contents(APP_TMP.'sync_token')),true);
        unlink(APP_TMP.'sync_token');
        if($token!==null){

            if(intval($token['timeout'])>time()){

               if($header['Token']==$token['token'])return true;
               else return false;
            }else{
                return false;
            }
        }
        return false;


    }

} 