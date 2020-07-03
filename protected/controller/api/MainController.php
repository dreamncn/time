<?php
/**
 * MainController.php
 * Created By Dreamn.
 * Date : 2020/7/3
 * Time : 3:18 下午
 * Description :
 */
namespace app\controller\api;
use app\lib\blog\Plugin;

class MainController extends BaseController{
    public function actionPlugin(){
        $plugin=new Plugin(arg('p'));
        if($plugin->isEnable()){
            $result=Plugin::hook('Do',$this->arg,true,null,arg('p'),true);
            if($result!==null){
                foreach ($result as $val){
                    echo $val;
                }
            }
        }
    }
}