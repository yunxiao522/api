<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/24 0024
 * Time: 13:01
 */

namespace App\Http\Controllers\Api;

use App\Model\Article;
use App\Model\Click;
use App\Model\LogVisit;
use Illuminate\Http\Request;

class Visit extends BaseController
{
    private $article_info;
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    /**
     *Description 访问记录接口
     */
    public function visit(){
        $this->article_info = Article::getOne(['id'=>$this->request->id],'*');
        $this->addClick();
        $this->addArticleClick();
        $this->addLogVisit($this->request->session_id,$this->request->user_id,$this->request->url);
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
    private function addLogVisit($session_id ,$user_id ,$url){
        $this->moxorySpider();
        LogVisit::add([
            'session_id'=>$session_id,
            'ip'=>$this->request->getClientIp(),
            'user_id'=>$user_id,
            'column_id'=>$this->article_info['column_id'],
            'article_id'=>$this->article_info['id'],
            'url'=>$url
        ]);
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
            die;
        }
    }
}