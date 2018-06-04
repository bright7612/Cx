<?php
namespace Dyuuu\Controller;
use Think\Controller;

// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');


class SmileController extends  Controller
{
    private  $APPID = 'wxae5fd28d839d907d';
    private $APPSECRET = '356ec528af355a9f31484d7a8c06969e';


    public function _initialize()
    {
        if(time()>$_SESSION['wx_access_token']['time']){
            $this->access_token_url();
        }
    }

    /*
    * 获取  access_token
    * */
    public function access_token_url(){

        $token_access_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxae5fd28d839d907d&secret=356ec528af355a9f31484d7a8c06969e";

//        $res = file_get_contents($token_access_url); //获取文件内容或获取网络请求的内容
//
        $res = $this->httpGet($token_access_url);
//        dump($res);die;
        $result = json_decode($res, true); //接受一个 JSON 格式的字符串并且把它转换为 PHP 变量
//        $access_token = $result['access_token'];
        $_SESSION['wx_access_token'] =  $result;
        $_SESSION['wx_access_token']['time'] = time()+$result['expires_in']-1000;

    }

    public function headimg()
    {
        $id = I('id');
        if($id){
            $array_user = array(
                '0'=>array('id'=>'1','width'=>'67','top'=>'435','left'=>'11','img'=>''),
                '1'=>array('id'=>'2','width'=>'40','top'=>'362','left'=>'27','img'=>''),
                '2'=>array('id'=>'3','width'=>'20','top'=>'342','left'=>'52','img'=>''),
                '3'=>array('id'=>'4','width'=>'40','top'=>'376','left'=>'73','img'=>''),
                '4'=>array('id'=>'5','width'=>'35','top'=>'416','left'=>'82','img'=>''),
                '5'=>array('id'=>'6','width'=>'74','top'=>'408','left'=>'120','img'=>''),
                '6'=>array('id'=>'7','width'=>'28','top'=>'473','left'=>'190','img'=>''),
                '7'=>array('id'=>'8','width'=>'55','top'=>'417','left'=>'200','img'=>''),
                '8'=>array('id'=>'9','width'=>'43','top'=>'466','left'=>'239','img'=>''),
                '9'=>array('id'=>'10','width'=>'35','top'=>'415','left'=>'263','img'=>''),
                '10'=>array('id'=>'11','width'=>'55','top'=>'447','left'=>'287','img'=>''),
                '11'=>array('id'=>'12','width'=>'31','top'=>'405','left'=>'302','img'=>''),
                '12'=>array('id'=>'13','width'=>'22','top'=>'390','left'=>'338','img'=>''),
                '13'=>array('id'=>'14','width'=>'60','top'=>'416','left'=>'341','img'=>''),
                '14'=>array('id'=>'15','width'=>'69','top'=>'346','left'=>'361','img'=>''),
                '15'=>array('id'=>'16','width'=>'18','top'=>'418','left'=>'402','img'=>''),
                '16'=>array('id'=>'17','width'=>'25','top'=>'318','left'=>'405','img'=>''),
                '17'=>array('id'=>'18','width'=>'51','top'=>'333','left'=>'439','img'=>''),
                '18'=>array('id'=>'19','width'=>'76','top'=>'253','left'=>'425','img'=>''),
                '19'=>array('id'=>'20','width'=>'56','top'=>'188','left'=>'426','img'=>''),
                '20'=>array('id'=>'21','width'=>'36','top'=>'219','left'=>'481','img'=>''),
                '21'=>array('id'=>'22','width'=>'21','top'=>'185','left'=>'486','img'=>''),
                '22'=>array('id'=>'23','width'=>'28','top'=>'158','left'=>'416','img'=>''),
                '23'=>array('id'=>'24','width'=>'51','top'=>'128','left'=>'443','img'=>''),
                '24'=>array('id'=>'25','width'=>'40','top'=>'112','left'=>'402','img'=>''),
                '25'=>array('id'=>'26','width'=>'30','top'=>'87','left'=>'428','img'=>''),
                '26'=>array('id'=>'27','width'=>'53','top'=>'51','left'=>'376','img'=>''),
                '27'=>array('id'=>'28','width'=>'40','top'=>'23','left'=>'337','img'=>''),
                '28'=>array('id'=>'29','width'=>'27','top'=>'12','left'=>'312','img'=>''),
                '29'=>array('id'=>'30','width'=>'18','top'=>'7','left'=>'295','img'=>''),
                '30'=>array('id'=>'31','width'=>'38','top'=>'185','left'=>'58','img'=>''),
                '31'=>array('id'=>'32','width'=>'60','top'=>'205','left'=>'94','img'=>''),
                '32'=>array('id'=>'33','width'=>'45','top'=>'153','left'=>'88','img'=>''),
                '33'=>array('id'=>'34','width'=>'36','top'=>'121','left'=>'122','img'=>''),
                '34'=>array('id'=>'35','width'=>'48','top'=>'153','left'=>'160','img'=>''),
                '35'=>array('id'=>'36','width'=>'56','top'=>'75','left'=>'161','img'=>''),
                '36'=>array('id'=>'37','width'=>'40','top'=>'114','left'=>'221','img'=>''),
                '37'=>array('id'=>'38','width'=>'40','top'=>'67','left'=>'234','img'=>''),
                '38'=>array('id'=>'39','width'=>'20','top'=>'93','left'=>'274','img'=>''),
                '39'=>array('id'=>'40','width'=>'60','top'=>'208','left'=>'188','img'=>''),
                '40'=>array('id'=>'41','width'=>'27','top'=>'176','left'=>'224','img'=>''),
                '41'=>array('id'=>'42','width'=>'42','top'=>'66','left'=>'225','img'=>''),
                '42'=>array('id'=>'43','width'=>'48','top'=>'296','left'=>'273','img'=>''),
                '43'=>array('id'=>'44','width'=>'30','top'=>'270','left'=>'316','img'=>''),
                '44'=>array('id'=>'45','width'=>'39','top'=>'340','left'=>'309','img'=>''),
                '45'=>array('id'=>'46','width'=>'48','top'=>'300','left'=>'340','img'=>''),
                '46'=>array('id'=>'47','width'=>'24','top'=>'450','left'=>'407','img'=>''),
                '47'=>array('id'=>'48','width'=>'44','top'=>'394','left'=>'432','img'=>''),
                '48'=>array('id'=>'49','width'=>'24','top'=>'479','left'=>'433','img'=>''),
                '49'=>array('id'=>'50','width'=>'34','top'=>'447','left'=>'448','img'=>''),
                '49'=>array('id'=>'51','width'=>'29','top'=>'424','left'=>'477','img'=>''),

            );

            $Model = M('qd');
            if($id <= 51){
                $headimgs = $Model->field('id,headimgurl')->limit(0,$id)->select();

                foreach ($array_user as $k=>&$v){
                    if($v['id'] == $headimgs[$k]['id']){
                        $v['img'] = $headimgs[$k]['headimgurl'] ;
                    }
                }

            }elseif($id > 50){
                $start = $id -51;
                $headimgs = $Model->field('id,headimgurl')->limit($start,51)->order('id ASC')->select();

                foreach ($array_user as $k=>&$v){
                    $v['img'] = $headimgs[$k]['headimgurl'] ;
                    $v['id'] = $headimgs[$k]['id'] ;
                }
            }

            echo json_encode(array('status'=>1,'msg'=>'请求成功','data'=>$array_user));

        }else{
            echo json_encode(array('status'=>-1,'msg'=>'请求失败'));
        }

    }

    public function index()
    {

        if(!$_REQUEST['id']){
            $this->getcode();
        }


        $user = M('qd')->where(array('id'=>$_REQUEST['id']))->find();
        $this->assign('user',$user);
        $this->display('indexMobile');
    }

    //大屏微笑墙首页
    public function pc_index()
    {
        $this->display('index');
    }

    //检测是不是最新一条
    public function check_new()
    {
        $Model = M('qd');
       $id = I('id');
        if($id){
            $user = $Model->field('id,headimgurl,nickname')->limit(0,1)->order('id DESC')->find();
            if($user['id'] > $id){
                echo json_encode(array('status'=>1,'data'=>true));
            }else{
                echo json_encode(array('status'=>1,'data'=>false));
            }
        }else{
            echo json_encode(array('status'=>1,'data'=>true));
        }
    }

    public function pc_headimg()
    {
        $Model = M('qd');
        $users = array(
            '0'=>array('id'=>'1','width'=>'94','top'=>'628','left'=>'18','img'=>''),
            '1'=>array('id'=>'2','width'=>'56','top'=>'522','left'=>'41','img'=>''),
            '2'=>array('id'=>'3','width'=>'27','top'=>'493','left'=>'77','img'=>''),
            '3'=>array('id'=>'4','width'=>'56','top'=>'543','left'=>'106','img'=>''),
            '4'=>array('id'=>'5','width'=>'50','top'=>'600','left'=>'119','img'=>''),
            '5'=>array('id'=>'6','width'=>'105','top'=>'589','left'=>'175','img'=>''),
            '6'=>array('id'=>'7','width'=>'39','top'=>'682','left'=>'275','img'=>''),
            '7'=>array('id'=>'8','width'=>'79','top'=>'601','left'=>'289','img'=>''),
            '8'=>array('id'=>'9','width'=>'60','top'=>'672','left'=>'346','img'=>''),
            '9'=>array('id'=>'10','width'=>'50','top'=>'598','left'=>'380','img'=>''),
            '10'=>array('id'=>'11','width'=>'77','top'=>'644','left'=>'415','img'=>''),
            '11'=>array('id'=>'12','width'=>'43','top'=>'584','left'=>'437','img'=>''),
            '12'=>array('id'=>'13','width'=>'30','top'=>'562','left'=>'489','img'=>''),
            '13'=>array('id'=>'14','width'=>'86','top'=>'600','left'=>'492','img'=>''),
            '14'=>array('id'=>'15','width'=>'98','top'=>'499','left'=>'521','img'=>''),
            '15'=>array('id'=>'16','width'=>'24','top'=>'603','left'=>'580','img'=>''),
            '16'=>array('id'=>'17','width'=>'35','top'=>'459','left'=>'583','img'=>''),
            '17'=>array('id'=>'18','width'=>'72','top'=>'481','left'=>'634','img'=>''),
            '18'=>array('id'=>'19','width'=>'108','top'=>'366','left'=>'614','img'=>''),
            '19'=>array('id'=>'20','width'=>'79','top'=>'272','left'=>'615','img'=>''),
            '20'=>array('id'=>'21','width'=>'51','top'=>'317','left'=>'694','img'=>''),
            '21'=>array('id'=>'22','width'=>'29','top'=>'267','left'=>'701','img'=>''),
            '22'=>array('id'=>'23','width'=>'40','top'=>'229','left'=>'600','img'=>''),
            '23'=>array('id'=>'24','width'=>'72','top'=>'185','left'=>'639','img'=>''),
            '24'=>array('id'=>'25','width'=>'55','top'=>'163','left'=>'580','img'=>''),
            '25'=>array('id'=>'26','width'=>'42','top'=>'126','left'=>'618','img'=>''),
            '26'=>array('id'=>'27','width'=>'75','top'=>'74','left'=>'541','img'=>''),
            '27'=>array('id'=>'28','width'=>'58','top'=>'35','left'=>'487','img'=>''),
            '28'=>array('id'=>'29','width'=>'37','top'=>'19','left'=>'451','img'=>''),
            '29'=>array('id'=>'30','width'=>'25','top'=>'12','left'=>'426','img'=>''),
            '30'=>array('id'=>'31','width'=>'53','top'=>'268','left'=>'86','img'=>''),
            '31'=>array('id'=>'32','width'=>'85','top'=>'297','left'=>'138','img'=>''),
            '32'=>array('id'=>'33','width'=>'62','top'=>'221','left'=>'130','img'=>''),
            '33'=>array('id'=>'34','width'=>'51','top'=>'175','left'=>'178','img'=>''),
            '34'=>array('id'=>'35','width'=>'67','top'=>'222','left'=>'233','img'=>''),
            '35'=>array('id'=>'36','width'=>'80','top'=>'109','left'=>'235','img'=>''),
            '36'=>array('id'=>'37','width'=>'56','top'=>'165','left'=>'321','img'=>''),
            '37'=>array('id'=>'38','width'=>'56','top'=>'98','left'=>'340','img'=>''),
            '38'=>array('id'=>'39','width'=>'28','top'=>'135','left'=>'397','img'=>''),
            '39'=>array('id'=>'40','width'=>'86','top'=>'300','left'=>'274','img'=>''),
            '40'=>array('id'=>'41','width'=>'38','top'=>'254','left'=>'325','img'=>''),
            '41'=>array('id'=>'42','width'=>'94','top'=>'325','left'=>'358','img'=>''),
            '42'=>array('id'=>'43','width'=>'68','top'=>'428','left'=>'396','img'=>''),
            '43'=>array('id'=>'44','width'=>'42','top'=>'390','left'=>'457','img'=>''),
            '44'=>array('id'=>'45','width'=>'55','top'=>'490','left'=>'447','img'=>''),
            '45'=>array('id'=>'46','width'=>'67','top'=>'435','left'=>'492','img'=>''),
            '46'=>array('id'=>'47','width'=>'33','top'=>'651','left'=>'588','img'=>''),
            '47'=>array('id'=>'48','width'=>'61','top'=>'568','left'=>'625','img'=>''),
            '48'=>array('id'=>'49','width'=>'33','top'=>'691','left'=>'626','img'=>''),
            '49'=>array('id'=>'50','width'=>'47','top'=>'644','left'=>'648','img'=>''),
            '50'=>array('id'=>'51','width'=>'40','top'=>'612','left'=>'690','img'=>''),
        );

        //获取51条最新数据
        $headimgs = $Model->field('id,headimgurl')->limit(0,51)->order('id DESC')->select();

        foreach ($users as $k=>&$v){
            $v['id'] = $headimgs[$k]['id'];
            $v['img'] = $headimgs[$k]['headimgurl'];
        }
        $user = $Model->field('id,headimgurl,nickname')->limit(0,1)->order('id DESC')->find();

        echo json_encode(array('status'=>1,'msg'=>'请求成功','data'=>array('list'=>$users,'user'=>$user)));
    }



    /*
   * 获取code值
   * 若未授权则跳转授权页面
   * 最终返回值为 用户 OpenID*/
    public function getcode(){

        $redirect_uri = 'http://hqdj.cmlzjz.com/Dyuuu/Smile/user/';
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='. $this->APPID .'&redirect_uri='. $redirect_uri .'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect ' ;
//        $url = ' https://open.weixin.qq.com/connect/oauth2/authorize?appid='. $this->APPID .'&redirect_uri='.$redirect_uri.'&response_type=code&scope=SCOPE&state=STATE#wechat_redirect' ;
        echo "<script language=\"javascript\">";
        echo "document.location=\"" . $url . "\"";
        echo "</script>";
        exit;
    }


    /*
 * 获取用户信息添加用户*/
    public function user(){
        if($_GET['code']){
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->APPID.'&secret='.$this->APPSECRET.'&code='.$_GET['code'].'&grant_type=authorization_code';
            $res = file_get_contents($url); //获取文件内容或获取网络请求的内容
            $result = json_decode($res, true); //接受一个 JSON 格式的字符串并且把它转换为 PHP 变量

//            //获取用用户信息
//            $user = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$result['access_token']."&openid=".$result['openid']."&lang=zh_CN";
//            $user = $this->httpGet($user);
//            $users = json_decode($user,true);
//
//            //判断用户是否关注公共号 0 未关注 1 关注
//            if($users['subscribe'] == 0){
//
//                echo "<script language=\"javascript\">";
//                echo "document.location='https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzIyMDA1ODYyMg==&scene=124#wechat_redirect'";
//                echo "</script>";
//                exit;
//            }

            if($result['openid']){
                $url2 = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$result['access_token'].'&openid='.$result['openid'].'&lang=zh_CN';
                $res2 = file_get_contents($url2); //获取文件内容或获取网络请求的内容
                $result2 = json_decode($res2,true); //接受一个 JSON 格式的字符串并且把它转换为 PHP 变量
                $ids =   M('qd')->where(array('openid'=>$result2['openid']))->find()  ;
                if($ids){
                    echo "<script language=\"javascript\">";
                    echo "document.location=\"http://hqdj.cmlzjz.com/Dyuuu/Smile/index/id/".$ids['id']."\"";
                    echo "</script>";
                    exit;
//
                }else{
                    $data['openid'] = $result2['openid'];
                    $data['headimgurl'] = $result2['headimgurl'];
                    $data['sex'] = $result2['sex'];
                    $data['nickname'] = $result2['nickname'];
                    $id =   M('qd')->add($data)  ;
                    if($id){
                        echo "<script language=\"javascript\">";
                        echo "document.location=\"http://hqdj.cmlzjz.com/Dyuuu/Smile/index/id/".$id."\"";
                        echo "</script>";
                        exit;
                    }
                }
            } else{

                $this->getcode();
            }
        }else{
            $this->getcode();
        }
    }


    public function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }


}