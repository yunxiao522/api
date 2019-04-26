<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/24 0024
 * Time: 13:01
 */

namespace App\Http\Controllers\Api;

use App\Model\Article;
use App\Model\ArticleHot;
use App\Model\Click;
use App\Model\LogVisit;
use Illuminate\Http\Request;

class Visit extends BaseController
{
    private $article_info;
    private $visit_type = [1=>'文档',2=>'列表',3=>'首页',4=>'其他'];
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     *Description 访问记录接口
     */
    public function visit(){
        $id = $this->request->id;
        if(empty($id) || !is_numeric($id)){
            Response::fail('');
        }
        $type = $this->request->type;
        if(!empty($type) && !isset($this->visit_type[$type])){
            $type = 3;
        }
        $url = $this->request->url;
        if(empty($url)){
            Response::fail('');
        }
        $source = $this->request->source;
        $device = $this->request->device;
        if(empty($device)){
            $device = getDeviceModel();
        }
//        $this->addClick();

        //处理参数数据
        if($type == 1){
            $this->article_info = Article::getOne(['id'=>$id],['id','column_id','pubdate']);
//            $this->addArticleClick();
            $this->addArticleHotClick();
            dump($type);die;
            $this->addLogVisit($this->request->session_id,$this->article_info['column_id'],$this->request->id,$url,$source,$device,$type);
        }else if($type == 2){
            $this->addLogVisit($this->request->session_id,0,$this->request->id,$url,$source,$device,$type);
        }else if($type == 3){
            $this->addLogVisit($this->request->session_id,0,0,$url,$source,$device,$type);
        }else if($type == 4){
            $this->addLogVisit($this->request->session_id,0,0,$url,$source,$device,$type);
        }


    }
    /**
     * Description 修改网站点击数
     */
    private function addClick(){
        $day = date('Y-m-d');
        $where = [
            'day'=>$day
        ];
        $click_info = Click::getOne($where,'id');
        if(empty($click_info)){
            Click::add([
                'day'=>$day,
                'create_time'=>strtotime($day),
                'click'=>1
            ]);
        }else{
            Click::incr(['id'=>$click_info->id],'click',1);
        }
    }

    /**
     * @param $id
     * Description 修改文档点击量
     */
    private function addArticleClick(){
        $where = ['id'=>$this->article_info['id']];
        Article::incr($where,'click',1);
    }

    /**
     * @param $session_id
     * @param $user_id
     * @param $url
     * Description 添加访问记录
     */
    private function addLogVisit($session_id ,$column_id,$article_id,$url,$source,$device,$type){
        $uid = Auth::getUserId() ? Auth::getUserId() : 0;
        if($this->moxorySpider()){
            LogVisit::add([
                'session_id'=>$session_id,
                'ip'=>$this->request->getClientIp(),
                'user_id'=>$uid,
                'column_id'=>$column_id,
                'article_id'=>$article_id,
                'url'=>$url,
                'source'=>$source,
                'device'=>$device,
                'type'=>$type,
                'create_time'=>time()
            ]);
        }
    }

    /**
     *Description 过滤蜘蛛的访问
     */
    private function moxorySpider(){
        $useragent = addslashes(strtolower($_SERVER['HTTP_USER_AGENT']));
        if (strpos($useragent, 'googlebot')!== false){$bot = 'Google';}
        elseif (strpos($useragent,'mediapartners-google') !== false){$bot = 'Google Adsense';}
        elseif (strpos($useragent,'baiduspider') !== false){$bot = 'Baidu';}
        elseif (strpos($useragent,'sogou spider') !== false){$bot = 'Sogou';}
        elseif (strpos($useragent,'sogou web') !== false){$bot = 'Sogou web';}
        elseif (strpos($useragent,'sosospider') !== false){$bot = 'SOSO';}
        elseif (strpos($useragent,'360spider') !== false){$bot = '360Spider';}
        elseif (strpos($useragent,'yahoo') !== false){$bot = 'Yahoo';}
        elseif (strpos($useragent,'msn') !== false){$bot = 'MSN';}
        elseif (strpos($useragent,'msnbot') !== false){$bot = 'msnbot';}
        elseif (strpos($useragent,'sohu') !== false){$bot = 'Sohu';}
        elseif (strpos($useragent,'yodaoBot') !== false){$bot = 'Yodao';}
        elseif (strpos($useragent,'twiceler') !== false){$bot = 'Twiceler';}
        elseif (strpos($useragent,'ia_archiver') !== false){$bot = 'Alexa_';}
        elseif (strpos($useragent,'iaarchiver') !== false){$bot = 'Alexa';}
        elseif (strpos($useragent,'slurp') !== false){$bot = '雅虎';}
        elseif (strpos($useragent,'bot') !== false){$bot = '其它蜘蛛';}
        if(isset($bot)){
            return false;
        }
        return true;
    }

    /**
     *Description 修改文档分布点击量数据
     */
    private function addArticleHotClick(){
        $pubdate = $this->article_info['pubdate'];
        $time = date('Y',$pubdate);
        $where = [
            'type'=>1,
            'time'=>$time
        ];
        $p_id = ArticleHot::getField($where,'id');
        if(empty($hot_id)){
            $p_id = ArticleHot::add([
                'type'=>1,
                'time'=>$time,
                'click'=>1,
                'create_time'=>time(),
                'parent_id'=>0
            ]);
        }else{
            ArticleHot::incr($where,'click',1);
        }
        $where = [
            'type'=>2,
            'time'=>date('M',$pubdate),
            'parent_id'=>$p_id
        ];
        $hot_id = ArticleHot::getField($where,'id');
        if(empty($hot_id)){
            ArticleHot::add([
                'type'=>2,
                'time'=>$time,
                'click'=>1,
                'create_time'=>time(),
                'parent_id'=>$p_id
            ]);
        }else{
            ArticleHot::incr($where,'click',1);
        }
    }
}