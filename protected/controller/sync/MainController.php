<?php
namespace app\controller\sync;
use app\includes\Img2local;
use app\model\Article;
use app\Model\Upload;

class MainController extends BaseController
{
    public function actionSetPic(){

        $article=new Article();
        $result=$article->getArticleByID(arg('gid'));
        if(!$result||sizeof($result)==0)return;
        if(intval(arg('picToMe'))){
            $img=new Img2local($result['content'],intval($result['ismarkdown'])===0?false:true);
            $result=$img->savePic();
            if($result){

                $article->setOpt(arg('gid'),"content",$result);
                $upload=new Upload('article');
                $upload->setBind(arg('gid'));
            }
        }
    }
}