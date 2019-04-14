<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/29 0029
 * Time: 12:02
 */

namespace App\Model;


use Illuminate\Support\Facades\DB;

class SearchHistory extends Base
{
    public $table = 'search_history';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * @param string $keyword
     * @param int $uid
     * @return bool
     * Description 添加搜索记录
     */
    public static function addHistory($keyword = '',$uid = 0){
        DB::beginTransaction();
        $search_id = Search::getField(['keyword'=>$keyword],'id');
        if(!$search_id){
            $search_id = Search::add([
                'keyword'=>$keyword,
                'num'=>1,
                'create_time'=>time(),
                'alter_time'=>time()
            ]);
            if(!$search_id){
                DB::rollBack();
                return false;
            }
        }else{
            $result = Search::incr(['id'=>$search_id],'num',1);
            if(!$result){
                DB::rollBack();
                return false;
            }
        }
        $history_id = self::getField(['uid'=>$uid,'sid'=>$search_id],'id');
        if(!$history_id){
            $history_id = self::add([
                'uid'=>$uid,
                'sid'=>$search_id,
                'keyword'=>$keyword,
                'create_time'=>time(),
                'alter_time'=>0
            ]);
        }
        if($history_id){
            DB::commit();
            return true;
        }else{
            DB::rollBack();
            return false;
        }
    }
}