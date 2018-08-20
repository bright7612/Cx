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

    //获取用户信息
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
                    cookie("wxuserOpenid",$co);

                    if(cookie('wxurl')){
                        $urll = cookie('wxurl');
                    }else{
                        $urll = 'http://'.$_SERVER['HTTP_HOST'].'/home/wxindex/index';
                    }
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
                cookie('wxurl',$lurl);
            }
            $rurl = 'http://'.$_SERVER['HTTP_HOST'].'/home/wxindex/wxindex';
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->AppID.'&redirect_uri='.$rurl.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
            echo '<script type="text/javascript">';
            echo 'window.location.href="'.$url.'"';
            echo '</script>';
        }
        exit;
    }

    /*
     * 个人页面*/
    public function userindex(){
        if(cookie('wxuserOpenid')){
            $map['openid'] = base64_decode(cookie('wxuserOpenid'));
            $user = M('wxuser')->where($map)->find();
            $this->assign('user',$user);
            $this->display('userindex');
        }else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/userindex';
            $this->Wxindex($lurl);
        }
    }
    //我的订单
    public function orderlist(){
        if(cookie('wxuserOpenid')){
            $map['openid'] = base64_decode(cookie('wxuserOpenid'));
            $user = M('wxuser')->where($map)->find();
            $map2['user_id'] = $user['id'];
            $map2['status'] = 1;
            $data = M('goods_order')->where($map2)->select();
            foreach ($data as $k=>&$v) {
                $v['goods_var'] = M('goods')->where(array('id'=>$v['goods_id']))->find();
            }
            $this->assign('user',$user);
            $this->assign('data',$data);
            $this->display('orderlist');
        }else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/orderlist';
            $this->Wxindex($lurl);
        }


    }
    //签到页面
    public function signUser(){
        if(cookie('wxuserOpenid')){
            $map['openid'] = base64_decode(cookie('wxuserOpenid'));
            $user = M('wxuser')->where($map)->find();
            $t = date('d',time()) - date('d',$user['sign_time']);
            $count = 0;
            if($t == 1){
                $map['classif'] = 1;
                $integral = M('wxuser_integral')->where($map)->limit(5)->order('id desc,time desc')->select();
                foreach ($integral as $k=>&$v){
                    $startTime=mktime(0,0,0,date('m'),date('d')-($k+1),date('Y'));
                    $endTime=mktime(0,0,0,date('m'),date('d')-$k,date('Y'))-1;


                    if($startTime<=$v['time']&&$endTime>=$v['time']){
                        $count++;
                        $t1[$k] = 1;
                    }else{
                        $t1[$k] = 0;
                    }
                }
                $this->assign('ti',$t1);
                $this->assign('qd',0);

            }
            elseif($t ==0) {
                $map['classif'] = 1;
                $integral = M('wxuser_integral')->where($map)->limit(5)->order('id desc ,time desc')->select();
                foreach ($integral as $k => &$v) {
                    $startTime = mktime(0, 0, 0, date('m'), date('d')-$k , date('Y'));
                    $endTime = mktime(0, 0, 0, date('m'), date('d')+1-$k, date('Y'))-1 ;
                    if ($startTime <= $v['time'] && $endTime >= $v['time']) {
                        $count++;
                        $t1[$k] = 1;
                    } else {
                        $t1[$k] = 0;
                        break;
                    }
                }
                $this->assign('ti', $t1);
                $this->assign('qd', 1);
            }

            $this->assign('user',$user);
            $this->assign('count',$count);
            $this->display('signUser');
        }
        else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/signUser';

            $this->Wxindex($lurl);
        }
    }
    //积分流向
    public function signin_det(){
        if(cookie('wxuserOpenid')){
            $map['openid'] = base64_decode(cookie('wxuserOpenid'));
            $user = M('wxuser')->where($map)->find();
            $data = M('wxuser_integral')->where($map)->order('time desc')->limit(500)->select();

            $this->assign('user',$user);
            $this->assign('data',$data);
            $this->display('signin_det');
        }
        else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/signin_det';

            $this->Wxindex($lurl);
        }
    }
    //完善个人信息
    public function userInformation(){
        if(cookie('wxuserOpenid')){
            $map['openid'] = base64_decode(cookie('wxuserOpenid'));
            $identity = M('wxuser')->where($map)->getField('identity');
            if($identity){
//                $identity = mb_substr($identity,0,5,'utf-8').'*************';
            }
            $this->assign('openid',cookie('wxuserOpenid'));
            $this->assign('identity',$identity);
            $this->display('userInformation');
        }
        else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/userInformation';

            $this->Wxindex($lurl);
        }

    }
    //我的微心愿(发布)
    public function userWish(){
            if(cookie('wxuserOpenid')) {
                if(IS_POST){
                    $evaluate = M('sign_wish_evaluate');
                    $wish = M('sign_wish');
                    $data['pj'] = $_REQUEST['pj'];
                    $data['content'] = $_REQUEST['content'];
                    $data['openid'] = base64_decode(cookie('wxuserOpenid'));
                    $data['wish_id'] = $_REQUEST['id'];
                    $evaluate->startTrans();
                    $wish->startTrans();
                    $sid = M('sign_wish_evaluate')->add($data);
                    $map['id'] = $data['wish_id'];
                    $map['state'] = 3;
                    $did = M('sign_wish')->where($map)->save(array('state'=>5));
                    if ($sid&&$did) {
                        $evaluate->commit();
                        $wish->commit();
                        echo json_encode(array('code' => 200, 'msg' => '提交成功', 'data' => array()));exit;
                    } else {
                        $evaluate->rollback();
                        $wish->rollback();
                        echo json_encode(array('code' => 300, 'msg' => '服务器繁忙！请稍等再次提交！', 'data' => array()));
                    }
                }else {
                    $this->assign('openid', cookie('wxuserOpenid'));
                    $this->display('userWish');
                }
            }
            else{
                $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/userWish';

                $this->Wxindex($lurl);
            }

    }
    //我的微心愿(认领)
    public function userWishApply(){
        if(cookie('wxuserOpenid')) {
            if(IS_POST){
                $evaluate = M('sign_wish_evaluate');
                $wish = M('sign_wish');
                $data['pj'] = $_REQUEST['pj'];
                $data['content'] = $_REQUEST['content'];
                $data['openid'] = base64_decode(cookie('wxuserOpenid'));
                $data['wish_id'] = $_REQUEST['id'];
                $evaluate->startTrans();
                $wish->startTrans();
                $sid = M('sign_wish_evaluate')->add($data);
                $map['id'] = $data['wish_id'];
                $did = M('sign_wish')->where($map)->save(array('applyPL'=>1));
                if ($sid&&$did) {
                    $evaluate->commit();
                    $wish->commit();
                    echo json_encode(array('code' => 200, 'msg' => '提交成功', 'data' => array()));exit;
                } else {
                    $evaluate->rollback();
                    $wish->rollback();
                    echo json_encode(array('code' => 300, 'msg' => '服务器繁忙！请稍等再次提交！', 'data' => array()));exit;
                }
            }else {
                $this->assign('openid', cookie('wxuserOpenid'));
                $this->display('userWishApply');
            }
        }
        else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/userWish';

            $this->Wxindex($lurl);
        }

    }

    //我的预约
    public function myactivity(){
        if(cookie('wxuserOpenid')){
            $this->display('myactivity');
        }
        else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/myactivity';
            $this->Wxindex($lurl);
        }
    }

    //我的预约 列表
    public function myactivityList($classif =1){
        if(cookie('wxuserOpenid')){
            $c = array(
                -1 =>'未通过审核',
                0=>'待审核',
                1=>'审核通过',
                3=>'已取消',
            );
            $map['openid'] = base64_decode(cookie('wxuserOpenid'));
            $map['status'] = 1;
            $map['state'] = array('in','-1,0,1,3');
            switch ($classif){
                case 1 :  //活动预约列表
                    $table = M('sign_bespoke');
                    $list = $table->where($map)->order('cre_time desc')->select();
                    foreach ($list as $k=>&$v){
                        $v['state_var'] = $c[$v['state']];
                        $v['content_id_var'] = M("issue_content")->where(array('id'=>$v['content_id']))->find();
                    }
                    break;
                case 2 : //场地
                    $table = M('sign_field');
                    $list = $table->where($map)->order('cre_time desc')->select();
                    foreach ($list as $k=>&$v){
                        $v['state_var'] = $c[$v['state']];
                        $v['content_id_var'] = M("issue_content")->where(array('id'=>$v['content_id']))->find();
                    }
                    break;
                case 3 :
                    $table = M('sign_lecture');
                    $list = $table->where($map)->order('cre_time desc')->select();
                    foreach ($list as $k=>&$v){
                        $v['state_var'] = $c[$v['state']];
                        $v['content_id_var'] = M("issue_content")->where(array('id'=>$v['content_id']))->find();
                    }
                    break;
                case 4 :
                    $table = M('sign_volunteer');
                    $list = $table->where($map)->order('cre_time desc')->select();
                    foreach ($list as $k=>&$v){
                        $v['state_var'] = $c[$v['state']];
                        $v['content_id_var'] = M("issue_content")->where(array('id'=>$v['content_id']))->find();
                    }
                    break;
            }

            $this->assign('list',$list);
            $this->assign('openid',cookie('wxuserOpenid'));
            $this->assign('classif',$classif);
            switch ($classif){
                case 1 :
                    $this->display('myactivityList');
                    break;
                case 2 :
                    $this->display('myactivityListTwo');
                    break;
                case 3 :
                    $this->display('myactivityListThree');
                    break;
                case 4 :
                    $this->display('myactivityListFour');
                    break;
            }
        }
        else {
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/myactivityList';
            $this->Wxindex($lurl);
        }
    }


    /***
     * 我的项目直通车
     */
    public function myprojects(){

        if(cookie('wxuserOpenid')){
            $map['openid'] = base64_decode(cookie('wxuserOpenid'));
            $map['status'] = 1;

            $data = D('My')->myprojects($map);
            $this->assign('data',$data);
//            dump($data);exit;
            $this->display('myprojects');
        }
        else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/myprojects';
            $this->Wxindex($lurl);
        }

    }
    /***
     * 项目直通车,我的详情页
     */
    public function myproject_det($id)
    {
        if(cookie('wxuserOpenid')){
            if(IS_POST){
                $data['pj']= $_REQUEST['pj'];
                $data['openid'] = base64_decode(cookie('wxuserOpenid'));
                $data['content']= $_REQUEST['content'];
                $data['direct_id']= $id;
                $sid = M('sign_direct_evaluate')->add($data);
                if($sid){
                    echo json_encode(array('code'=>200,'msg'=>'提交成功','data'=>array()));
                }else{
                    echo json_encode(array('code'=>300,'msg'=>'服务器繁忙！请稍等再次提交！','data'=>array()));
                }

            }
            else{
                $map['status'] = 1;
                $map['id'] = $id;
                $data = M('sign_direct')->where($map)->find();
                $data['pic'] = explode(',',$data['img']);
                $pj = M('sign_direct_evaluate')->where(array('status'=>1,'state'=>1))->field("pj,count(*) as num")->group('pj')->order('pj asc')->select();
                $apply = M('sign_direct_apply')->where(array('status'=>1,'state'=>1))->select();
                $this->assign('data',$data);
                $this->assign('apply',$apply);
                $this->assign('pj',$pj);
//        dump($pj);exit;
                $this->display('myproject_det');
            }
        }else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/myproject_det/id/'.$id;
            $this->Wxindex($lurl);
        }


    }

    /***
     * 我的众筹
     */
    public function myraise(){

        if(cookie('wxuserOpenid')){
            $map['openid'] = base64_decode(cookie('wxuserOpenid'));
            $this->display('myraise');
        }
        else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/myraise';
            $this->Wxindex($lurl);
        }

    }
    /***
     * 项目众筹申请
     */
    public function myraiserequest(){

        if(cookie('wxuserOpenid')){
            $map['openid'] = base64_decode(cookie('wxuserOpenid'));
            $map['status'] = 1;
            $data = D('My')->myraiserequest($map);
            $this->assign('data',$data);
            $this->assign('title','我的众筹申请');
            $this->display('myraiselist');
        }
        else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/myraiserequest';
            $this->Wxindex($lurl);
        }

    }

    /***
     * 我参加的众筹项目
     */
    public function myraisejoin(){

        if(cookie('wxuserOpenid')){
            $map['openid'] = base64_decode(cookie('wxuserOpenid'));
            $map['status'] = 1;
            $data = D('My')->myraisesjoin($map);


            $this->assign('data',$data);
            $this->assign('title','我参加的众筹项目');
            $this->display('myraiselist');
        }
        else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/myraisejoin';
            $this->Wxindex($lurl);
        }

    }

    /***
     * 项目众筹详情
     * */
    public function myraise_det($id)
    {
        if (IS_POST) {
            $data['pj'] = $_REQUEST['pj'];
            $data['content'] = $_REQUEST['content'];
            $data['openid'] = base64_decode(cookie('wxuserOpenid'));
            $data['zhch_id'] = $id;
            $sid = M('sign_zhch_evaluate')->add($data);
            if ($sid) {
                echo json_encode(array('code' => 200, 'msg' => '提交成功', 'data' => array()));
            } else {
                echo json_encode(array('code' => 300, 'msg' => '服务器繁忙！请稍等再次提交！', 'data' => array()));
            }

        }
        else {
            $map['status'] = 1;
            $map['id'] = $id;
            $data = M('sign_zhch')->where($map)->find();
            $map2['zhch_id'] = $data['id'];
            $map2['state'] = 3;
            if ($data['classif'] == 1) {
                $data['speed'] = M('sign_zhch_apply')->where($map2)->sum('money');
                $data['progress'] = number_format($data['speed'] / $data['money'], 4) * 100;
            } else {
                $data['speed'] = M('sign_zhch_apply')->where($map2)->sum('count');
                $data['progress'] = number_format($data['speed'] / $data['count'], 4) * 100;
            }
            $this->assign('data', $data); //项目数据
            $list = M('sign_zhch_apply')->where($map2)->select();
            foreach ($list as $k => $v) {
                $li[] = M('wxuser')->where(array('openid' => $v['openid']))->getField('headimgurl');
            }
            $this->assign('list', $li); //项目参与
            $map3['zhch_id'] = $data['id'];
            $map3['status'] = 1;
            $map3['state'] = 1;
            $info = M('sign_zhch_evaluate')->where(array('status' => 1, 'state' => 1))->field("pj,count(*) as num")->group('pj')->order('pj asc')->select();
            $this->assign('pj', $info); //项目参与
            $this->display('myraise_det');
        }
    }

    public function myintegrallist(){
        if(cookie('wxuserOpenid')){
            $map['openid'] = base64_decode(cookie('wxuserOpenid'));
            $data = D('My')->myintegrallist($map);

            $this->assign('data',$data);
            $this->display('myintegrallist');
        }
        else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/myprojects';
            $this->Wxindex($lurl);
        }

    }


    //我的对党说说心里话
    public function userheart(){
        if(cookie('wxuserOpenid')){
            $map['openid'] = base64_decode(cookie('wxuserOpenid'));
            $data = M('sign_heart')->where($map)->order('cre_time desc,id desc')->select();
            $this->assign('data',$data);
//            dump($data);exit;
            $this->display('userheart');
        }
        else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/myactivity';
            $this->Wxindex($lurl);
        }

    }

    //积分商城
    public function goodlist(){
        if(cookie('wxuserOpenid')){
//        $map['status'] = 1;
//        $map['state'] = 1;
//        $data = M("goods")->where($map)->select();
//        $this->assign('data',$data);
        $this->display('goodlist');
        }
        else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/goodlist';
            $this->Wxindex($lurl);
        }
    }

    //积分商城 详情页面
    public function goods_det($goodsid){
        if(cookie('wxuserOpenid')){
        $this->assign('openid',cookie('wxuserOpenid'));
        $map['openid'] = base64_decode(cookie('wxuserOpenid'));
        $integral = M('wxuser')->where($map)->getField('integral');
        $goods = M('goods');
        $map['id'] = $goodsid;
        $map['status'] = 1;
        $map['state'] = 1;
        $data = $goods->where($map)->find();
        if(!$data){
            $this->goodlist();exit;
        }
        $this->assign('data',$data);
        $this->assign('integral',$integral);
//        dump($data);exit;
        $this->display('goods_det');
        }
        else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/goods_det/goodsid/'.$goodsid;
            $this->Wxindex($lurl);
        }
    }



    //长兴党建首页 党建之窗
    public function index(){
        $this->display('index');
    }

    //党建工作
    public function work(){
        $this->display('work');
    }

    //服务预约
    public function service(){
        $this->display('service');
    }

    //优秀党员
    public function party_member()
    {
        $map['status'] = 1;
        $map['issue_id'] = 105;
        $data = M('issue_content')->where($map)->order('sort desc,id desc')->limit(500)->field('title,id,host,content,cover_id')->select();
        $this->assign('data',$data);
        $this->display('party_member');
    }
    //优秀党员详情
    public function party_member_det($id)
    {
        $map['status'] = 1;
        $map['state'] = 1;
        $map['id'] = $id;
        $data = M('issue_content')->where($map)->find();
        $this->assign('data',$data);
        $this->display('party_member_det');
    }


    //党员服务
    public function party_service(){
        $this->display('party_service');
    }
    //长兴党建
    public function changxing(){
        $this->display('changxing');
    }

    //公共列表页
    public function public_list()
    {
        $classif = I('classif');
        $issueid = I('issueid');
        $map['status'] = 1;
        switch ($classif){
            case 1:  //党建工作信息
                $this->assign('title','党建工作');
                $tab = M('issue_content');
                $map['issue_id'] = 107;
                break;
            case 2:  //党建品牌
                $this->assign('title','党建品牌');
                $tab = M('issue_content');
                $map['issue_id'] = 108;
                break;
            case 3:  //党建+1+8+N+1+X
                $tab = M('issue_content');
                $map['issue_id'] = 110;
                break;
            case 4:  //三述两测一考
                $tab = M('issue_content');
                $map['issue_id'] = 111;
                break;
            case 5:  //三述两测一考
                $this->display('overview');
                exit;
                break;
            case 6:  //党课学习活动
                $this->assign('title','党课学习');

                $tab = M('issue_content');
                $map['issue_id'] = 119;
                break;
            case 7:  //党务实务
                $this->assign('title','党务实务');

                $tab = M('issue_content');
                $map['issue_id'] = 121;
                break;
            case 8:  //两学一做
                $this->assign('title','两学一做');

                $tab = M('issue_content');
                $map['issue_id'] = 123;
                break;
            case 9:  //党史回顾
                $this->assign('title','党史回顾');

                $tab = M('issue_content');
                $map['issue_id'] = 124;
                break;
            case 10:  //常见问题
                $this->assign('title','常见问题');

                $tab = M('issue_content');
                $map['issue_id'] = 126;
                break;

        }
        $data = $tab->where($map)->order('sort desc ,id desc')->select();
        switch ($classif){
            case 1:  //党建工作信息
                foreach ($data as $k=>&$v){
                    $v['cover_id_var'] = pic($v['cover_id']);
                }
                break;
            case 2:  //村民自治1+1+X
                foreach ($data as $k=>&$v){
                    $v['cover_id_var'] = pic($v['cover_id']);
                }
//                $this->assign('bottoms',1);
                break;
            case 3:  //党建+1+8+N+1+X
                foreach ($data as $k=>&$v){
                    $v['cover_id_var'] = pic($v['cover_id']);
                }
//                $this->assign('bottoms',2);
                break;
            case 4:  //三述两测一考
                foreach ($data as $k=>&$v){
                    $v['cover_id_var'] = pic($v['cover_id']);
                }
//                $this->assign('bottoms',3);
                break;
            case 6:  //党课学习活动
                foreach ($data as $k=>&$v){
                    $v['cover_id_var'] = pic($v['cover_id']);
                }
                break;
            case 7:  //党务实务
                foreach ($data as $k=>&$v){
                    $v['cover_id_var'] = pic($v['cover_id']);
                }
                break;
            case 8:  //两学一做
                foreach ($data as $k=>&$v){
                    $v['cover_id_var'] = pic($v['cover_id']);
                }
                break;
            case 9:  //党史回顾
                foreach ($data as $k=>&$v){
                    $v['cover_id_var'] = pic($v['cover_id']);
                }
                break;
            case 10:  //常见问题
                foreach ($data as $k=>&$v){
                    $v['cover_id_var'] = pic($v['cover_id']);
                }
                break;

        }

        $this->assign('data',$data);

//        dump($data);exit;

        $this->display('public_list');
    }
    //公共详情页
    public function public_det()
    {
        $id = I('id');
        if(!$id){
            exit('未知页面！！！');
        }
        $map['status'] = 1;
        $map['id'] = $id;
        $data = M('issue_content')->where($map)->order('sort desc ,id desc')->find();
        if(!$data){
            exit('未知页面！！！');
        }
//          dump($data);exit;
        $this->assign('data',$data);
        $this->display('public_det');
    }


    //党建知识
    public function knowledge(){
        $map['status'] = 1;
        $map['issue_id'] = 122;
        $data = M('issue_content')->where($map)->select();
        $this->assign('data',$data);
        $this->display('knowledge');

    }
    //党课在线
    public function videos(){
//        $tab = M('issue_content');
        $map['issue_id'] = 118;
        $map['status'] = 1;
        $data = M('issue_content')->where($map)->select();
        $this->assign('data',$data);
        $this->display('video');
    }

    //党课在线详情
    public function video_det($id=null){
        if(!$id){
            exit('错误！！！');
        }
        $map['status'] =1;
        $map['id'] =$id;
        $data = M('issue_content')->where($map)->find();
        $data['video_var'] =M('file')->where(array('id'=>$data['video_id']))->find();
        $this->assign('data',$data);
        $this->display('video_det');

    }

    //活动预约列表
    public function activity_list()
    {
        $map['status'] = 1;
        $map['issue_id'] = 82; //党建活动
        $data = M('issue_content')->where($map)->order('sort desc,id desc')->limit(500)->select();
        $this->assign('data',$data);
        $this->display('activity_list');
    }
    //活动预约列表project_det
    public function activity_det()
    {
        $map['id'] = $_REQUEST['id'];
        $map['status'] = 1;
        $data = M('issue_content')->where($map)->find();
        $this->assign('data',$data);
        $this->display('activity_det');
    }
    //活动预约列表
    public function activity_form($content_id)
    {
        if(cookie('wxuserOpenid')){
//            $dzz = $this->cxdzz();
            $this->assign('openid',cookie('wxuserOpenid'));
            $this->assign('content_id',$content_id);
//            $this->assign('dzz',$dzz);
            $this->display('activity_form');
        }else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/activity_form/content_id/'.$content_id;

            $this->Wxindex($lurl);
        }
    }
    //志愿者列表
    public function volunteer_list()
    {
        $map['status'] = 1;
        $map['issue_id'] = 84; //党建活动
        $data = M('issue_content')->where($map)->order('sort desc,id desc')->limit(500)->select();
        foreach ($data as $k=>&$v){
            $da[$k]['title'] = $v['title']; //标题
            $da[$k]['id'] = $v['id']; //编号
            $da[$k]['content'] = $v['content']; //内容
            $da[$k]['addr'] = $v['addr']; //活动地址
            $da[$k]['gather'] = $v['gather'];//集合地址
            $da[$k]['time'] = $v['time']; //活动开始时间
            $da[$k]['duration'] = $v['duration'];//活动持续时间
            $da[$k]['num'] = $v['num'];//计划招募人数
            $da[$k]['num_var'] = M('sign_volunteer')->where(array('content_id'=>$v['id'],'status'=>1,'state'=>1))->count('bespoke_num');//计划招募人数
        }
        $this->assign('data',$da);
        $this->display('volunteer_list');
    }
    //志愿者活动详情
    public function volunteer_det()
    {
        $map['id'] = $_REQUEST['id'];
        $map['status'] = 1;
        $data = M('issue_content')->where($map)->find();
        $data['num_var'] = M('sign_volunteer')->where(array('content_id'=>$data['id'],'status'=>1,'state'=>1))->count('bespoke_num');//计划招募人数
        $this->assign('data',$data);
        $this->display('volunteer_det');
    }
    //志愿者活动报名
    public function volunteer_form($content_id)
    {
        if(cookie('wxuserOpenid')){

//            $dzz = $this->cxdzz();
//            $dzz = array_merge(array(array('NAME'=>'')),$dzz);
            $this->assign('openid',cookie('wxuserOpenid'));
            $this->assign('content_id',$content_id);
//            $this->assign('dzz',$dzz);
            $this->display('volunteer_form');
        }else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/volunteer_form/content_id/'.$content_id;

            $this->Wxindex($lurl);
        }
    }

    //场馆列表
    public function direct_list()
    {
        $map['status'] = 1;
        $map['issue_id'] = 86; //场馆
        $data = M('issue_content')->where($map)->order('sort desc,id desc')->limit(500)->select();
        foreach ($data as $k=>&$v){
            $da[$k]['title'] = $v['title']; //标题
            $da[$k]['id'] = $v['id']; //编号
            $da[$k]['content'] = $v['content']; //内容
            $da[$k]['addr'] = $v['addr']; //活动地址
            $da[$k]['gather'] = $v['gather'];//集合地址
            $da[$k]['time'] = $v['time']; //活动开始时间
            $da[$k]['duration'] = $v['duration'];//活动持续时间
            $da[$k]['num'] = $v['num'];//计划招募人数
            $da[$k]['cover_id'] = $v['cover_id'];//计划招募人数
        }
        $this->assign('data',$da);
        $this->display('direct_list');
    }
    //场馆详情
    public function direct_det()
    {
        $map['id'] = $_REQUEST['id'];
        $map['status'] = 1;
        $data = M('issue_content')->where($map)->find();
        $this->assign('data',$data);
//        dump($data);exit;
        $this->display('direct_det');
    }
    //场馆预约
    public function direct_form($content_id)
    {
        if(cookie('wxuserOpenid')){
//            $dzz = $this->cxdzz();
            $this->assign('openid',cookie('wxuserOpenid'));
            $this->assign('content_id',$content_id);
//            $this->assign('dzz',$dzz);
            $this->display('direct_form');
        }else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/direct_form/content_id/'.$content_id;
            $this->Wxindex($lurl);
        }
    }

    //党课列表
    public function lecture_list()
    {
        $map['status'] = 1;
        $map['issue_id'] = 88; //党课预约
        $data = M('issue_content')->where($map)->order('sort desc,id desc')->limit(500)->select();
        foreach ($data as $k=>&$v){
            $da[$k]['title'] = $v['title']; //标题
            $da[$k]['id'] = $v['id']; //编号
            $da[$k]['content'] = $v['content']; //内容
            $da[$k]['addr'] = $v['addr']; //活动地址
            $da[$k]['gather'] = $v['gather'];//集合地址
            $da[$k]['time'] = $v['time']; //活动开始时间
            $da[$k]['host'] = $v['host']; //活动开始时间
            $da[$k]['duration'] = $v['duration'];//活动持续时间
            $da[$k]['num'] = $v['num'];//计划招募人数
            $da[$k]['num_var'] = M('sign_lecture')->where(array('content_id'=>$v['id'],'status'=>1,'state'=>1))->count('bespoke_num');//已招募人数
        }
        $this->assign('data',$da);
        $this->display('lecture_list');
    }
    //党课详情预约页面
    public function lecture_from($content_id)
    {
        if(cookie('wxuserOpenid')){
            $data = M('issue_content')->where(array('id'=>$content_id))->find();
            $data['num_var'] = M('sign_lecture')->where(array('content_id'=>$data['id'],'status'=>1,'state'=>1))->count('bespoke_num');//已招募人数
//            $dzz = $this->cxdzz();
            $this->assign('openid',cookie('wxuserOpenid'));
            $this->assign('content_id',$content_id);
//            $this->assign('dzz',$dzz);
            $this->assign('data',$data);
            $this->display('lecture_from');
        }else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/lecture_from/content_id/'.$content_id;

            $this->Wxindex($lurl);
        }
    }
    //党员心声
    public function aspirations(){
        if(cookie('wxuserOpenid')){

//            $dzz = $this->cxdzz();
//            $this->assign('dzz',$dzz);
            $map['status'] =1;
            $map['state'] =1;
            $data = M("sign_heart")->where($map)->order('id desc')->limit(10)->select();
            $this->assign('openid',cookie('wxuserOpenid'));
            $this->assign('data',$data);
            $this->display('aspirations');
        }else{
            $this->Wxindex(__ACTION__);
        }
    }
    //微心愿
    public function wish(){
        $this->display('wish');
    }
    //微心愿发布
    public function wish_release(){
        if(cookie('wxuserOpenid')){
            $this->assign('openid',cookie('wxuserOpenid'));
            $this->display('wish_release');
        }else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/wish_release';
            $this->Wxindex($lurl);
        }
    }
    //微心愿详情
    public function wish_det($content_id){
        $c = array(
            1=>'待认领',
            2=>'已认领',
            3=>'已完成',
        );
        $a = array(
            1=>'个人',
            2=>'团体',
            3=>'群众',
        );
        $b = array(
            1=>'物质',
            2=>'精神',
        );
        $map['status'] = 1;
        $map['state'] = array('in','1,2,3');
        $map['id'] = $content_id;
        $data = M('sign_wish')->where($map)->find();
        $data['state_var'] = $c[$data['state']];
        $data['username'] = mb_substr($data['username'],0,1,'utf-8');
        $data['obj'] = $a[$data['obj']];
        $data['form'] = $b[$data['form']];


        $map2['status'] = 1;
        $map2['state'] = 1;
        $map2['wish_id'] = $content_id;
        $data2 = M('sign_wish_apply')->where($map2)->find();
        $data2['name'] = mb_substr($data2['name'],0,1,'utf-8');
        $data2['telephone'] =substr($data2['telephone'],0,2);


        $this->assign('data',$data);
        $this->assign('data2',$data2);
        $this->display('wish_det');
    }
    //微心愿详情 申请
    public function wish_form($content_id){
        if(cookie('wxuserOpenid')){
//            $dzz = $this->cxdzz();
//            $dzz = array_merge(array(array('NAME'=>'')),$dzz);
            $this->assign('openid',cookie('wxuserOpenid'));
            $this->assign('content_id',$content_id);
//            $this->assign('dzz',$dzz);
            $this->display('wish_form');
        }else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/wish_form/content_id/'.$content_id;
            $this->Wxindex($lurl);
        }
    }
    //项目众筹
    public function raise()
    {
        $this->display('raise');
    }
    //项目众筹申报
    public function raise_apply()
    {
        if(cookie('wxuserOpenid')){
            $this->assign('openid',cookie('wxuserOpenid'));
            $this->display('raise_apply');
        }
        else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/raise_apply';
            $this->Wxindex($lurl);
        }
    }
    //项目众筹详情
    public function raise_det($id)
    {
        if (IS_POST) {
            $data['pj'] = $_REQUEST['pj'];
            $data['content'] = $_REQUEST['content'];
            $data['openid'] = base64_decode(cookie('wxuserOpenid'));
            $data['zhch_id'] = $id;
            $sid = M('sign_zhch_evaluate')->add($data);
            if ($sid) {
                echo json_encode(array('code' => 200, 'msg' => '提交成功', 'data' => array()));
            } else {
                echo json_encode(array('code' => 300, 'msg' => '服务器繁忙！请稍等再次提交！', 'data' => array()));
            }

        }
        else {
            $map['status'] = 1;
            $map['id'] = $id;
            $data = M('sign_zhch')->where($map)->find();
            $data['pic'] ='http://'.$_SERVER['HTTP_HOST'].pic($data['img']);
            $map2['zhch_id'] = $data['id'];
            $map2['state'] = 3;
            if ($data['classif'] == 1) {
                $data['speed'] = M('sign_zhch_apply')->where($map2)->sum('money');
                $data['progress'] = number_format($data['speed'] / $data['money'], 4) * 100;
            } else {
                $data['speed'] = M('sign_zhch_apply')->where($map2)->sum('count');
                $data['progress'] = number_format($data['speed'] / $data['count'], 4) * 100;
            }
            $this->assign('data', $data); //项目数据
            $list = M('sign_zhch_apply')->where($map2)->select();
            foreach ($list as $k => $v) {
                $li[] = M('wxuser')->where(array('openid' => $v['openid']))->getField('headimgurl');
            }
            $this->assign('list', $li); //项目参与
            $map3['zhch_id'] = $data['id'];
            $map3['status'] = 1;
            $map3['state'] = 1;
            $info = M('sign_zhch_evaluate')->where(array('status' => 1, 'state' => 1))->field("pj,count(*) as num")->group('pj')->order('pj asc')->select();
            $this->assign('pj', $info); //项目参与
            $this->display('raise_det');
        }
    }
    //项目众筹申请
    public function raise_form($content_id){
        if(cookie('wxuserOpenid')){

//            $dzz = $this->cxdzz();
//            $dzz = array_merge(array(array('NAME'=>'')),$dzz);
            $this->assign('openid',cookie('wxuserOpenid'));
            $data = M('sign_zhch')->where(array('id'=>$content_id,'status'=>1,'state'=>1))->find();
            $this->assign('content_id',$content_id);
//            $this->assign('dzz',$dzz);
            $this->assign('data',$data);
            $this->display('raise_form');
        }else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/raise_form/content_id/'.$content_id;
            $this->Wxindex($lurl);
        }
    }
    //项目直通车
    public function project()
    {
        $map['status']=1;
        $map['state']=array('egt',1);
        $data = M('sign_direct')->where($map)->select();
        foreach ($data as $k=>&$v){
            $v['pic'] =explode(',',$v['img']);
        }
        $this->assign('data',$data);
        $this->display('project');
    }
    //项目直通车
    public function project_det($id)
    {
        if(IS_POST){
            $data['pj']= $_REQUEST['pj'];
            $data['openid'] = base64_decode(cookie('wxuserOpenid'));
            $data['content']= $_REQUEST['content'];
            $data['direct_id']= $id;
            $sid = M('sign_direct_evaluate')->add($data);
            if($sid){
                echo json_encode(array('code'=>200,'msg'=>'提交成功','data'=>array()));
            }else{
                echo json_encode(array('code'=>300,'msg'=>'服务器繁忙！请稍等再次提交！','data'=>array()));
            }

        }else{
            $map['status'] = 1;
            $map['id'] = $id;
            $data = M('sign_direct')->where($map)->find();
            $data['pic'] = explode(',',$data['img']);
            $pj = M('sign_direct_evaluate')->where(array('status'=>1,'state'=>1))->field("pj,count(*) as num")->group('pj')->order('pj asc')->select();
            $apply = M('sign_direct_apply')->where(array('status'=>1,'state'=>1))->select();
            $this->assign('data',$data);
            $this->assign('apply',$apply);
            $this->assign('pj',$pj);
//        dump($pj);exit;
            $this->display('project_det');
        }

    }

    //项目直通车参与
    public function project_form($content_id)
    {
        if(cookie('wxuserOpenid')){
//            $dzz = $this->cxdzz();
//            $dzz = array_merge(array(array('NAME'=>'')),$dzz);
            $this->assign('openid',cookie('wxuserOpenid'));
            $this->assign('content_id',$content_id);
//            $this->assign('dzz',$dzz);
            $this->display('project_form');
        }else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/project_form/content_id/'.$content_id;

            $this->Wxindex($lurl);
        }
    }
    //项目直通车需求
    public function project_demand($content_id)
    {
        if(cookie('wxuserOpenid')){
//            $dzz = $this->cxdzz();
//            $dzz = array_merge(array(array('NAME'=>'')),$dzz);
            $this->assign('openid',cookie('wxuserOpenid'));
            $this->assign('content_id',$content_id);
//            $this->assign('dzz',$dzz);
            $this->display('project_demand');
        }else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/project_demand/content_id/'.$content_id;
            $this->Wxindex($lurl);
        }
    }

    //讲师列表
    public function lecturerList(){
        $map['issue_id'] = 90;
        $map['status'] = 1;
        $map['state'] = 1;
        $data = M('issue_content')->where($map)->order('sort desc,id desc')->select();
        $this->assign('data',$data);
        $this->display('lecturerList');
    }
    public function lecturer_det($id=null){
        $map['issue_id'] = 90;
        $map['status'] = 1;
        $map['state'] = 1;
        $map['id'] = $id;
        $data = M('issue_content')->where($map)->find();
        $map2['status'] =1;
        $map2['state'] =1;
        $map2['issue_id'] = 88;
        $map2['teacher'] = $data['title'];
        $data2 = M('issue_content')->where($map2)->order('sort desc,time desc')->select();
        foreach ($data2 as $k=>&$v){
            $v['content']  = mb_substr($v['content'],0,12);
            $v['num_var'] = M('sign_lecture')->where(array('content_id'=>$v['id'],'status'=>1,'state'=>1))->count('bespoke_num');//已招募人数
        }
        $this->assign('data',$data);
        $this->assign('data2',$data2);
        $this->display('lecturer_det');
    }


    //学习风采
    public function xxfc(){
        $this->display('xxfc');
    }
    //党员心得
    public function gain_list(){
        $map['status'] =1;
        $map['state'] =1;
        $data = M('sign_gain')->where($map)->select();
        $this->assign('data',$data);
        $this->display('gain_list');
    }
    //党员心得
    public function gain_det($content_id){
        $map['status'] =1;
        $map['state'] =1;
        $map['id'] =$content_id;
        $data = M('sign_gain')->where($map)->find();
        $this->assign('data',$data);
        $this->display('gain_det');
    }
    //党员心得
    public function gain_form(){
        if(cookie('wxuserOpenid')){
            $this->assign('openid',cookie('wxuserOpenid'));
            $this->display('gain_form');
        }else{
            $lurl = 'http://cxdj.cmlzjz.com/home/wxindex/gain_form';
            $this->Wxindex($lurl);
        }
    }

    //党建百科
    public function encyclopedias(){
        $this->display('encyclopedias');
    }


    //党群之家
    public function party_masses(){
        $this->display('party_masses');
    }


    /***
     * 我的项目直通车申请
     */
    public function userprojects(){

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

    //党组织
    private function cxdzz(){
        if($_SESSION['cxdzzJSON']){
            return $_SESSION['cxdzzJSON'];
        }
        $data = M('ajax_dzz')->field('BRANCH_ID,NAME,PARENT_ID')->order('PARENT_ID asc')->select();

        $datas = $this->make_tree1($data,'BRANCH_ID','PARENT_ID');

        $list = $datas[0]['_child'][0]['_child'][0]['_child'][0]['_child'];
        $_SESSION['cxdzzJSON'] = $list ;
//        $list = json_encode($datas[0]['_child'][0]['_child'][1]['_child'][0]);
//        echo json_encode($list);
//       dump($list);exit;
        return $list;
    }
    /*
 * 无限极分类*/
    private function make_tree1($list,$pk='id',$pid='pid',$child='_child',$root=0){
        $tree=array();
        foreach($list as $key=> $val){
            if($val[$pid]==$root){
                //获取当前$pid所有子类
                unset($list[$key]);
                if(! empty($list)){
                    $child=$this->make_tree1($list,$pk,$pid,$child,$val[$pk]);
                    if(!empty($child)){
                        $val['_child']=$child;
                    }
                }
                $tree[]=$val;
            }
        }
        return $tree;
    }

}