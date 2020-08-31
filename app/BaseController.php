<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {}

    

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }


    public function result($msg, $data = [] ,$msgStr){

        switch ($msg) {
            case 1:
                $status = 0; 
                break;

            case 2:
                $status = 1;
                break;    
        }

        $data = [
            'code' => $status,
            'data' => $data,
            'msg' => $msgStr     
        ];  

        return Json($data);
    }


    //本周每天的日期
    public function weekday(){

        $Monday = date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)); 
        $Tuesday = date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 2) * 24 * 3600));
        $Wednesday = date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 3) * 24 * 3600));
        $Thursday = date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 4) * 24 * 3600));
        $Friday = date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 5) * 24 * 3600));
        $Saturday = date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 6) * 24 * 3600));
        $Sunday = date('Y-m-d', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));

        $date = array($Monday,$Tuesday,$Wednesday,$Thursday,$Friday,$Saturday,$Sunday);

        return $date;

    }

    //获取上周
    public function lastweek($day){

        //$day必须为周一日期
        $new_day = strtotime($day);                                 //转为时间戳
                                     
        $Sunday = date('Y-m-d',($new_day - 24 * 3600));             //上周日的时间戳                       
        $Saturday = date('Y-m-d',($new_day - 24 * 3600 * 2));       //周六
        $Friday = date('Y-m-d',($new_day - 24 * 3600 * 3));         //五
        $Thursday = date('Y-m-d',($new_day - 24 * 3600 * 4));       //四  
        $Wednesday = date('Y-m-d',($new_day - 24 * 3600 * 5));      //三
        $Tuesday = date('Y-m-d',($new_day - 24 * 3600 * 6));        //二
        $Monday = date('Y-m-d',($new_day - 24 * 3600 * 7));         //一

        // dump($Friday);
        $date = array($Monday,$Tuesday,$Wednesday,$Thursday,$Friday,$Saturday,$Sunday);

        return $date;
    }


    //获取下周
    public function nextweek($day){

        //$day必须为周一日期
        $new_day = strtotime($day);                                  //转为时间戳
                                     
        $Sunday = date('Y-m-d',($new_day + 24 * 3600 * 13));         //下周日的时间戳                       
        $Saturday = date('Y-m-d',($new_day + 24 * 3600 * 12));       //周六
        $Friday = date('Y-m-d',($new_day + 24 * 3600 * 11));         //五
        $Thursday = date('Y-m-d',($new_day + 24 * 3600 * 10));       //四  
        $Wednesday = date('Y-m-d',($new_day + 24 * 3600 * 9));       //三
        $Tuesday = date('Y-m-d',($new_day + 24 * 3600 * 8));         //二
        $Monday = date('Y-m-d',($new_day + 24 * 3600 * 7));          //一

        //dump($Sunday);
        $date = array($Monday,$Tuesday,$Wednesday,$Thursday,$Friday,$Saturday,$Sunday);

        return $date;
    }


    public function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    public function doDate($data){

        $arr = array(); 
        $len = count($data);
        for ($i=0; $i < $len; $i++) {
            $new_date = explode('-', substr($data[$i]['createTime'], 0,10));
            $month = '';
            switch ($new_date[1]) {
                case '01':
                    $month = 'Jan';
                    break;
                case '02':
                    $month = 'Feb';
                    break;
                case '03':
                    $month = 'Mar';
                    break;
                case '04':
                    $month = 'Apr';
                    break;
                case '05':
                    $month = 'May';
                    break;
                case '06':
                    $month = 'Jun';
                    break;
                case '07':
                    $month = 'Jul';
                    break;
                
                case '08':
                    $month = 'Aug';
                    break;
                case '09':
                    $month = 'Sept';
                    break;
                case '10':
                    $month = 'Oct';
                    break;
                case '11':
                    $month = 'Nov';
                    break;
                case '12':
                    $month = 'Dec';
                    break;
                    
                default:
                    # code...
                    break;
            }
            $arr = [
                'year' => $new_date[0],
                'month'=> $month,
                'day'  => $new_date[2]          
            ];

            $newarr[] = array_merge($data[$i],$arr);
        }

        return $newarr;

    }     
}
