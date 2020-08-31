<?php
namespace app\controller;
use app\BaseController;
use think\facade\Db;
use think\respoonse\Json;
use think\Request;
use think\facade\Config;
use config\status;


class article extends BaseController{

	
	protected $rule = [
        'currentPage' => 'number|between:1,10000',
        'pageSize'    => 'number|between:1,10000',      
    ];

	public function WxDecode()
    {

		$data = request() -> param();
		// dump($data);


        $appid = Config::get('app.appid');
        $appsecret = Config::get('app.appsecret');
        $grant_type = "authorization_code"; //授权（必填）

        $code = $data['code'];        //有效期5分钟 登录会话
        

        $encryptedData=$data['encryptedData'];
        $iv = $data['iv'];
        $signature = $data['signature'];
        $rawData = $data['rawData'];

        // 拼接url
        $url = "https://api.weixin.qq.com/sns/jscode2session?"."appid=".$appid."&secret=".$appsecret."&js_code=".$code."&grant_type=".$grant_type;
        $res = json_decode($this->httpGet($url),true);



        $sessionKey = $res['session_key']; //取出json里对应的值
        $signature2 =  sha1($rawData.$sessionKey);

        // 验证签名
        if ($signature2 !== $signature){
            return json("验签失败111");
        }


        // 获取解密后的数据
        $pc = new \wxBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );

        
        $new_data = json_decode($data,true);
        $openId = $new_data['openId'];


        $lifeTime = 24 * 3600;
        session_set_cookie_params($lifeTime);
        session_start();
        $_SESSION["access_token"] = true;
        // $new_data["access_token"] = $signature2;

        // dump($new_data);
        // exit;
        if ($errCode == 0) {
            
	        $res = Db::table('wx_user') ->where('openId',$openId)->find();

	        if ($res == null) {
	        	$user_data = [
            	'openId' 		=> $new_data['openId'],
            	'nickName'		=> $new_data['nickName'],
            	'gender'		=> $new_data['gender'],
            	'city'			=> $new_data['city'],
            	'province'		=> $new_data['province'],
            	'country'		=> $new_data['country'],
            	'avatarUrl'		=> $new_data['avatarUrl'],
            ];
	            	
	            $result = Db::table('wx_user')->save($user_data);

	            return $this->result(1,$new_data,'success');

	        }else{

	        	return $this->result(1,$new_data,'success');
	        }
            

        } else {

            return $this->result(2,"请求失败",'error');
        }


    }


	public function list(){
	$pageData = request() -> param();


		
		// $this->validate()
		
		$data = Db::table('article')->limit($pageData['pageSize']*($pageData['currentPage'] - 1),$pageData['pageSize'])->select();

		

		$len = count($data);
		// dump($len);
		// exit();
		if ($len != 0) {

			// $len = count($data);

			$arr = array();	
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

			return $this->result(1,$newarr,"success");
		}else{
			$newarr = [];
			return $this->result(2,$newarr,"error");
		}	
	}


	public function article_detail(){
		$data = request()->param();


		$res = Db::table('article')->where('id',$data['id'])->select();

		

		if($res){

			$new_data = $this->doDate($res);

			return $this->result(1,$new_data[0],"success");
		}else{
			return $this->result(1,$res,'error');
		}

	}
}