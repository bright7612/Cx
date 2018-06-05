<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/31
 * Time: 10:37
 */

namespace Home\Controller;


use Think\Controller;

class WxindexController extends Controller
{
    private  $AppID = 'wxfc2fc1b2423020f7';
    private  $AppSecret = '9133ddaa0104916d4a05c23eb7d4c728';
    private $wxuser;

    function _initialize(){
        $this->wxuser = M('wxuser');
        if($_SESSION['wx_token']['time'] < time()){
            $this->access_token();
        }
    }

    //获取token

    private function access_token(){
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->AppID.'&secret='.$this->AppSecret;
        $token = $this->vget($url);
        $token = (array)json_decode($token);
        $_SESSION['wx_token']['access_token']=$token['access_token'];
        $_SESSION['wx_token']['time']=time() + $token['expires_in']-1000;
    }
    //获取用户信息 并 储存
     public function Wxindex($lurl = null){
        if($_REQUEST['code']){
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->AppID.'&secret='.$this->AppSecret.'&code='.$_REQUEST['code'].'&grant_type=authorization_code';
            $openid = $this->vget($url);
            $openid = (array)json_decode($openid);
            $url2 = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$openid['access_token'].'&openid='.$openid['openid'].'&lang=zh_CN';
            $user = $this->vget($url2);
            $user = (array)json_decode($user);
            if($user['openid']){
                $data['openid'] = $user['openid'];
                $data['nickname'] = $user['nickname'];
                $data['sex'] = $user['sex'];
                $data['province'] = $user['province'];
                $data['city'] = $user['city'];
                $data['headimgurl'] = $user['headimgurl'];
                $wx = $this->wxuser->where(array('openid'=>$data['openid']))->find();
                if($wx['id']){
                    $data['out_time'] = time();
                    $rel = $this->wxuser->where(array('id'=>$wx['id']))->save($data);
                }else{
                    $data['first_time'] = time();
                    $data['out_time'] =   $data['first_time'];
                    $rel = $this->wxuser->add($data);
                }
                if($rel){
                    $co = base64_encode($data['openid']);
                    setcookie("wxuserOpenid",$co, time()+3600*24);

                    if($_REQUEST['url']){
                        $urll = base64_decode($_REQUEST['url']);
                    }else{
                        $urll = 'http://'.$_SERVER['HTTP_HOST'].'/home/wxindex/index';
                    }
//                    dump($urll);exit;
                    echo '<script type="text/javascript">';
                    echo 'window.location.href="'.$urll.'"';
                    echo '</script>';
                }else{
                    echo '<script type="text/javascript">';
                    echo 'window.location.href="'. 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'"';
                    echo '</script>';
                }
            }
            exit;
        }else{
            if($lurl){
                $a = base64_encode($lurl);
                $rurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?url='.$a;
            }else{
                $rurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            }
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->AppID.'&redirect_uri='.$rurl.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
            echo '<script type="text/javascript">';
            echo 'window.location.href="'.$url.'"';
            echo '</script>';
        }
         exit;
     }

     public function userindex(){
        if($_COOKIE['wxuserOpenid']){
            $map['openid'] = base64_decode($_COOKIE['wxuserOpenid']);
            $user = $this->wxuser->where($map)->find();
            $this->assign('user',$user);
            $this->display();
        }else{
            $lurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $this->Wxindex($lurl);
        }
     }

    //curl 模拟get
    private function vget($url) { // 模拟获取内容函数
        $curl = curl_init (); // 启动一个CURL会话
        if (IS_PROXY) {
            //以下代码设置代理服务器
            //代理服务器地址
            curl_setopt ( $curl, CURLOPT_PROXY, $GLOBALS ['proxy'] );
        }
        curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, 0 ); // 对认证证书来源的检查
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 2 ); // 从证书中检查SSL加密算法是否存在
        curl_setopt ( $curl, CURLOPT_USERAGENT, $GLOBALS ['user_agent'] ); // 模拟用户使用的浏览器
        @curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 1 ); // 使用自动跳转
        curl_setopt ( $curl, CURLOPT_AUTOREFERER, 1 ); // 自动设置Referer
        curl_setopt ( $curl, CURLOPT_HTTPGET, 1 ); // 发送一个常规的Post请求
        curl_setopt ( $curl, CURLOPT_COOKIEFILE, $GLOBALS ['cookie_file'] ); // 读取上面所储存的Cookie信息
        curl_setopt ( $curl, CURLOPT_TIMEOUT, 120 ); // 设置超时限制防止死循环
        curl_setopt ( $curl, CURLOPT_HEADER, 0 ); // 显示返回的Header区域内容
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec ( $curl ); // 执行操作
        if (curl_errno ( $curl )) {
            echo 'Errno' . curl_error ( $curl );
        }
        curl_close ( $curl ); // 关闭CURL会话
        return $tmpInfo; // 返回数据
    }

}