<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/11
 * Time: 16:56
 */

namespace Home\Controller;


use Addons\Mail\MailAddon;
use Admin\Model\Wxuser_integralModel;
use Think\Controller;

// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

class WxapiController extends Controller
{
    function _initialize(){

    }
    public function dxtd($telephone=null,$classify=null,$option=null,$examination=null){
        $a = send($telephone,$classify,$option,$examination);
        return $a;
    }
    //活动报名接口
    public function activity(){
        if($_REQUEST['openid']){$user = $this->identitys($_REQUEST['openid']);}else{$this->Apireturn(array(),300,'用户尚未登陆');}  //党员限定
        if($_REQUEST['content_id']){$data['content_id'] = $_REQUEST['content_id'];}else{$this->Apireturn(array(),300,'申请活动错误');}  //活动编号
        if($_REQUEST['name']){$data['name'] = $_REQUEST['name'];}else{$this->Apireturn(array(),300,'申请姓名错误');}  //申请姓名
        if($_REQUEST['phone']){$data['phone'] = $_REQUEST['phone'];}else{$this->Apireturn(array(),300,'申请电话错误');}  //申请姓名
        if($_REQUEST['party']){$data['organization'] = $_REQUEST['party'];}else{$this->Apireturn(array(),300,'申请党组织错误');}  //申请党组织
        if($_REQUEST['number']){
            $data['bespoke_num'] = $_REQUEST['number'];



        }else{$this->Apireturn(array(),300,'申请人数错误');}  //申请人数
        if($_REQUEST['way'] == 0){
            if($_REQUEST['theme']){$data['company'] = $_REQUEST['theme'];}else{$this->Apireturn(array(),300,'申请单位错误');}  //申请单位
        }
        $data['types'] = $_REQUEST['way'];
        $data['cre_time'] = time(); //申请时间
        $data['source'] = 1 ; //微信端添加
        $data['openid'] = base64_decode($_REQUEST['openid']);
        $data['identity'] = $user['identity'];
        $data['text'] = $_REQUEST['item'];
        $id = M('sign_bespoke')->add($data);
        if($id){
            $aa['telephone'] = $data['phone'];
            $aa['classify'] = 1;
            $aa['option'] = '活动报名';
            _httpClient($aa,'http://183.131.86.64:8620/home/wxapi/dxtd');
            $this->Apireturn($id,200,'申请成功');
        }else{
            $this->Apireturn(array(),300,'服务器繁忙！请稍后！！！');
        }
    }
    //志愿者报名接口
    public function volunteers(){
        if($_REQUEST['openid']){$data['openid'] = base64_decode($_REQUEST['openid']);}else{$this->Apireturn(array(),300,'用户尚未登陆');}  //党员限定
        if($_REQUEST['content_id']){$data['content_id'] = $_REQUEST['content_id'];}else{$this->Apireturn(array(),300,'申请活动错误');}  //活动编号
        if($_REQUEST['name']){$data['name'] = $_REQUEST['name'];}else{$this->Apireturn(array(),300,'申请姓名错误');}  //申请姓名
        if($_REQUEST['phone']){$data['phone'] = $_REQUEST['phone'];}else{$this->Apireturn(array(),300,'申请电话错误');}  //申请姓名
        if($_REQUEST['number']){$data['bespoke_num'] = $_REQUEST['number'];}else{$this->Apireturn(array(),300,'申请人数错误');}  //申请人数
        if($_REQUEST['way'] == 0){
            if($_REQUEST['theme']){$data['company'] = $_REQUEST['theme'];}else{$this->Apireturn(array(),300,'申请单位错误');}  //申请单位
        }
        $data['types'] = $_REQUEST['way'];
        $data['cre_time'] = time(); //申请时间
        $data['source'] = 1 ; //微信端添加
        $data['identity'] = $_REQUEST['identitys'];
        $data['organization'] = $_REQUEST['partys'];
        $data['text'] = $_REQUEST['remark'];
        $id = M('sign_volunteer')->add($data);
        if($id){
            $aa['telephone'] = $data['phone'];
            $aa['classify'] = 1;
            $aa['option'] = '志愿者报名';
            _httpClient($aa,'http://183.131.86.64:8620/home/wxapi/dxtd');
            $this->Apireturn($id,200,'申请成功');
        }else{
            $this->Apireturn(array(),300,'服务器繁忙！请稍后！！！');
        }
    }
    //场地报名接口
    public function direct(){
        if($_REQUEST['openid']){$user = $this->identitys($_REQUEST['openid']);}else{$this->Apireturn(array(),300,'用户尚未登陆');}  //党员限定
        if($_REQUEST['content_id']){$data['content_id'] = $_REQUEST['content_id'];}else{$this->Apireturn(array(),300,'申请活动错误');}  //活动编号
        if($_REQUEST['name']){$data['name'] = $_REQUEST['name'];}else{$this->Apireturn(array(),300,'申请姓名错误');}  //申请姓名
        if($_REQUEST['phone']){$data['phone'] = $_REQUEST['phone'];}else{$this->Apireturn(array(),300,'申请电话错误');}  //申请姓名
        if($_REQUEST['party']){$data['organization'] = $_REQUEST['party'];}else{$this->Apireturn(array(),300,'申请党组织错误');}  //申请党组织
        if($_REQUEST['number']){$data['bespoke_num'] = $_REQUEST['number'];}else{$this->Apireturn(array(),300,'申请人数错误');}  //申请人数
        if($_REQUEST['way'] == 0){
            if($_REQUEST['theme']){$data['company'] = $_REQUEST['theme'];}else{$this->Apireturn(array(),300,'申请单位错误');}  //申请单位
        }
        $data['start_time'] = strtotime($_REQUEST['star_date']);
        $data['end_time'] = strtotime($_REQUEST['end_date']);
        $d1 = date('d',$data['start_time']);
        $d2 = date('d',$data['end_time']);
        if($d1 != $d2){
            $this->Apireturn(array(),300,'申请开始时间与结束时间必须再同一天');
        }
        $data['types'] = $_REQUEST['way'];
        $data['cre_time'] = time(); //申请时间
        $data['source'] = 1 ; //微信端添加
        $data['openid'] = base64_decode($_REQUEST['openid']);
        $data['identity'] = $user['identity'];
        $data['text'] = $_REQUEST['item'];
        $id = M('sign_field')->add($data);
        if($id){
            $aa['telephone'] = $data['phone'];
            $aa['classify'] = 1;
            $aa['option'] = '场地报名';
            _httpClient($aa,'http://183.131.86.64:8620/home/wxapi/dxtd');
            $this->Apireturn($id,200,'申请成功');
        }else{
            $this->Apireturn(array(),300,'服务器繁忙！请稍后！！！');
        }
    }
    //党课报名接口
    public function lecture(){
        if($_REQUEST['openid']){$user = $this->identitys($_REQUEST['openid']);}else{$this->Apireturn(array(),300,'用户尚未登陆');}  //党员限定
        if($_REQUEST['content_id']){$data['content_id'] = $_REQUEST['content_id'];}else{$this->Apireturn(array(),300,'申请活动错误');}  //活动编号
        if($_REQUEST['name']){$data['name'] = $_REQUEST['name'];}else{$this->Apireturn(array(),300,'申请姓名错误');}  //申请姓名
        if($_REQUEST['phone']){$data['phone'] = $_REQUEST['phone'];}else{$this->Apireturn(array(),300,'申请电话错误');}  //申请姓名
        if($_REQUEST['organization']){$data['organization'] = $_REQUEST['organization'];}else{$this->Apireturn(array(),300,'申请党组织错误');}  //申请党组织
        if($_REQUEST['number']){$data['bespoke_num'] = $_REQUEST['number'];}else{$this->Apireturn(array(),300,'申请人数错误');}  //申请人数
        if($_REQUEST['way'] == 0){
            if($_REQUEST['theme']){$data['company'] = $_REQUEST['theme'];}else{$this->Apireturn(array(),300,'申请单位错误');}  //申请单位
        }
        $data['types'] = $_REQUEST['way'];
        $data['cre_time'] = time(); //申请时间
        $data['source'] = 1 ; //微信端添加
        $data['openid'] = base64_decode($_REQUEST['openid']);
        $data['identity'] = $user['identity'];
        $id = M('sign_lecture')->add($data);
        if($id){
            $aa['telephone'] = $data['phone'];
            $aa['classify'] = 1;
            $aa['option'] = '党课报名';
            _httpClient($aa,'http://183.131.86.64:8620/home/wxapi/dxtd');
            $this->Apireturn($id,200,'申请成功');
        }else{
            $this->Apireturn(array(),300,'服务器繁忙！请稍后！！！');
        }
    }
    //项目直通车报名接口
    public function project(){
        if($_REQUEST['openid']){$data['openid'] = base64_decode($_REQUEST['openid']);}else{$this->Apireturn(array(),300,'用户尚未登陆');}  //党员限定
        if($_REQUEST['content_id']){$data['direct_id'] = $_REQUEST['content_id'];}else{$this->Apireturn(array(),300,'申请活动错误');}  //活动编号
        if($_REQUEST['name']){$data['name'] = $_REQUEST['name'];}else{$this->Apireturn(array(),300,'申请姓名错误');}  //申请姓名
        if($_REQUEST['phone']){$data['telephone'] = $_REQUEST['phone'];}else{$this->Apireturn(array(),300,'申请电话错误');}  //申请姓名
        if($_REQUEST['way'] == 0){
            if($_REQUEST['theme']){$data['company'] = $_REQUEST['theme'];}else{$this->Apireturn(array(),300,'申请单位错误');}  //申请单位
        }
        $data['types'] = $_REQUEST['way'];
        $data['cer_time'] = time(); //申请时间
        $data['source'] = 1 ; //微信端添加
        $data['identity'] = trim($_REQUEST['identitys']);
        $data['organization'] = trim($_REQUEST['partys']);
        $data['content'] = $_REQUEST['item'];
        $id = M('sign_direct_apply')->add($data);
        if($id){
            $aa['telephone'] = $data['telephone'];
            $aa['classify'] = 1;
            $aa['option'] = '项目直通车报名';
            _httpClient($aa,'http://183.131.86.64:8620/home/wxapi/dxtd');
            $this->Apireturn($id,200,'申请成功');
        }else{
            $this->Apireturn(array(),300,'服务器繁忙！请稍后！！！');
        }
    }
    //项目直通车需求接口
    public function demand(){
        if($_REQUEST['openid']){$data['openid'] = base64_decode($_REQUEST['openid']);}else{$this->Apireturn(array(),300,'用户尚未登陆');}  //党员限定
        if($_REQUEST['content_id']){$data['direct_id'] = $_REQUEST['content_id'];}else{$this->Apireturn(array(),300,'申请活动错误');}  //活动编号
        if($_REQUEST['name']){$data['name'] = $_REQUEST['name'];}else{$this->Apireturn(array(),300,'申请姓名错误');}  //申请姓名
        if($_REQUEST['phone']){$data['telephone'] = $_REQUEST['phone'];}else{$this->Apireturn(array(),300,'申请电话错误');}  //申请姓名
        $data['cer_time'] = time(); //申请时间
        $data['source'] = 1 ; //微信端添加
        $data['content'] = $_REQUEST['remarks'];
        $id = M('sign_direct_demand')->add($data);
        if($id){
            $aa['telephone'] = $data['telephone'];
            $aa['classify'] = 1;
            $aa['option'] = '项目直通车需求';
            _httpClient($aa,'http://183.131.86.64:8620/home/wxapi/dxtd');
            $this->Apireturn($id,200,'提交成功');
        }else{
            $this->Apireturn(array(),300,'服务器繁忙！请稍后！！！');
        }
    }
    //项目众筹报名接口
    public function raise(){
        if($_REQUEST['openid']){$data['openid'] = base64_decode($_REQUEST['openid']);}else{$this->Apireturn(array(),300,'用户尚未登陆');}  //党员限定
        if($_REQUEST['content_id']){$data['zhch_id'] = $_REQUEST['content_id'];}else{$this->Apireturn(array(),300,'申请活动错误');}  //活动编号
        if($_REQUEST['name']){$data['name'] = $_REQUEST['name'];}else{$this->Apireturn(array(),300,'申请姓名错误');}  //申请姓名
        if($_REQUEST['phone']){$data['telephone'] = $_REQUEST['phone'];}else{$this->Apireturn(array(),300,'申请电话错误');}  //申请姓名
        if($_REQUEST['way'] == 0){
            if($_REQUEST['theme']){$data['company'] = $_REQUEST['theme'];}else{$this->Apireturn(array(),300,'申请单位错误');}  //申请单位
        }
        if($_REQUEST['number']||$_REQUEST['money']){
            $data['count'] = $_REQUEST['number'];
            $data['money'] = $_REQUEST['money'];
        }else{
            $this->Apireturn(array(),300,'参与金额或人数错误');
        }
        $data['types'] = $_REQUEST['way'];
        $data['cre_time'] = time(); //申请时间
        $data['source'] = 1 ; //微信端添加
        $data['identity'] = trim($_REQUEST['identitys']);
        $data['organization'] = trim($_REQUEST['partys']);
        $data['content'] = $_REQUEST['remark'];
        $id = M('sign_zhch_apply')->add($data);
        if($id){
            $aa['telephone'] = $data['telephone'];
            $aa['classify'] = 1;
            $aa['option'] = '众筹报名';
            _httpClient($aa,'http://183.131.86.64:8620/home/wxapi/dxtd');
            $this->Apireturn($id,200,'申请成功');
        }else{
            $this->Apireturn(array(),300,'服务器繁忙！请稍后！！！');
        }
    }
    //项目众筹申报接口
    public function raiseapply(){
        if($_REQUEST['openid']){$data['openid'] = base64_decode($_REQUEST['openid']);}else{$this->Apireturn(array(),300,'用户尚未登陆');}  //党员限定
        $data['title'] = $_REQUEST['title'];//标题
        $data['applyname'] = $_REQUEST['name'];//姓名
        $data['applytelephone'] = $_REQUEST['phone'];//电话
        $data['classif'] = $_REQUEST['way'];//众筹种类
        $data['count'] = $_REQUEST['ry'];//众筹种类
        $data['money'] = $_REQUEST['je'];//众筹种类
        $data['state_time'] = strtotime($_REQUEST['star_date']);
        $data['end_time'] = strtotime($_REQUEST['end_date']);
        $data['cer_time'] = time();
        $data['source'] = 1;
        $data['content'] = $_REQUEST['content_z'];
        $data['address'] = $_REQUEST['address'];
        $data['img'] = $_REQUEST['img_id'];
        $id = M('sign_zhch')->add($data);
        if($id){
            $aa['telephone'] = $data['applytelephone'];
            $aa['classify'] = 1;
            $aa['option'] = '众筹申报';
            _httpClient($aa,'http://183.131.86.64:8620/home/wxapi/dxtd');
            $this->Apireturn($id,200,'申请成功');
        }else{
            $this->Apireturn(array(),300,'服务器繁忙！请稍后！！！');
        }
    }
    //微心愿报名接口
    public function wishapply(){
        if($_REQUEST['openid']){$data['openid'] = base64_decode($_REQUEST['openid']);}else{$this->Apireturn(array(),300,'用户尚未登陆');}  //党员限定
        if($_REQUEST['content_id']){$data['wish_id'] = $_REQUEST['content_id'];}else{$this->Apireturn(array(),300,'申请活动错误');}  //活动编号
        if($_REQUEST['name']){$data['name'] = $_REQUEST['name'];}else{$this->Apireturn(array(),300,'申请姓名错误');}  //申请姓名
        if($_REQUEST['phone']){$data['telephone'] = $_REQUEST['phone'];}else{$this->Apireturn(array(),300,'申请电话错误');}  //申请姓名
        if($_REQUEST['way'] == 0){
            if($_REQUEST['theme']){$data['company'] = $_REQUEST['theme'];}else{$this->Apireturn(array(),300,'申请单位错误');}  //申请单位
        }
        $data['types'] = $_REQUEST['way'];
        $data['cer_time'] = time(); //申请时间
        $data['source'] = 1 ; //微信端添加
        $data['identity'] = $_REQUEST['identitys'];
        $data['organization'] = $_REQUEST['partys'];
        if($data['organization']!=='非党员'&&$data['organization']){
            $data['party'] = 1;
        }
        $data['content'] = $_REQUEST['item'];
        $id = M('sign_wish_apply')->add($data);
        if($id){
            $aa['telephone'] = $data['applytelephone'];
            $aa['classify'] = 1;
            $aa['option'] = '微心愿报名';
            _httpClient($aa,'http://183.131.86.64:8620/home/wxapi/dxtd');
            $this->Apireturn($id,200,'申请成功');
        }else{
            $this->Apireturn(array(),300,'服务器繁忙！请稍后！！！');
        }
    }
    //微心愿申请接口
    public function wishrelease(){
        if($_REQUEST['openid']){$data['openid'] = base64_decode($_REQUEST['openid']);}else{$this->Apireturn(array(),300,'用户尚未登陆');}  //党员限定
        if($_REQUEST['name']){$data['username'] = $_REQUEST['name'];}else{$this->Apireturn(array(),300,'申请姓名错误');}  //申请姓名
        if($_REQUEST['phone']){$data['telephone'] = $_REQUEST['phone'];}else{$this->Apireturn(array(),300,'申请电话错误');}  //申请姓名
        if($_REQUEST['title']){$data['title'] = $_REQUEST['title'];}else{$this->Apireturn(array(),300,'心愿不能为空');}  //申请姓名
        $data['identity'] = $_REQUEST['identity'];
        $data['obj'] = $_REQUEST['obj'];
        $data['form'] = $_REQUEST['form'];
        $data['cer_time'] = time(); //申请时间
        $data['cre_time'] = time(); //申请时间
        $data['source'] = 1 ; //微信端添加
        $data['organization'] = $_REQUEST['organization'];
        $data['start_time'] =strtotime($_REQUEST['start_time']);
        $data['address'] =$_REQUEST['address'];
        if($data['organization']!=='非党员'&&$data['organization']){
            $data['party'] = 1;
        }
        $data['content'] = $_REQUEST['content_z'];
        $data['img'] = $_REQUEST['img_id'];
//        dump($data);exit;
        $id = M('sign_wish')->add($data);
//        dump($id);exit;
        if($id){
            $aa['telephone'] = $data['telephone'];
            $aa['classify'] = 1;
            $aa['option'] = '微心愿申请';
            _httpClient($aa,'http://183.131.86.64:8620/home/wxapi/dxtd');
            $this->Apireturn($id,200,'申请成功');
        }else{
            $this->Apireturn(array(),300,'服务器繁忙！请稍后！！！');
        }
    }
//党员心声
    public function heart(){
        $data['cre_time'] = time();
        $data['openid'] = base64_decode($_REQUEST['openid']);
        $data['content'] = $_REQUEST['message'];
        $data['organization'] = $_REQUEST['addr'];
        $data['name'] = $_REQUEST['name'];
        $id =M('sign_heart')->add($data);
        if($id){
            $this->Apireturn($id,200,'提交成功');
        }else{
            $this->Apireturn(array(),300,'服务器繁忙！请稍等再次提交！');
        }
        exit;
    }
    //map接口
    public function mapApi(){
        $data = array();
        $map['status'] =1;
        $map['lat'] =array('exp','IS NOT NULL');
        $map['issue_id'] =82;
        $data['activity'] = M('issue_content')->where($map)->field('id,title,addr,time,content,lat,lng')->select();
        foreach ($data['activity'] as $k=>&$v){
            $v['time'] =$v['time']*1000;
            $v['href'] = '/home/wxindex/activity_det/id/'.$v['id'];
        }
        $map['issue_id'] =84;
        $data['volunteers'] = M('issue_content')->where($map)->field('id,title,addr,time,content,lat,lng')->select();
        foreach ($data['volunteers'] as $k=>&$v){
            $v['time'] =     $v['time']*1000;
            $v['href'] ='/home/wxindex/volunteer_det/id/'.$v['id'];
        }
        $map['issue_id'] =86;
        $data['direct'] = M('issue_content')->where($map)->field('id,title,addr,content,lat,lng')->select();
        foreach ($data['direct'] as $k=>&$v){
            $v['time'] =     $v['time']*1000;
            $v['href'] ='/home/wxindex/direct_det/id/'.$v['id'];
        }
        $map['issue_id'] =88;
        $data['lecture'] = M('issue_content')->where($map)->field('id,title,addr,time,content,lat,lng')->select();
        foreach ($data['lecture'] as $k=>&$v){
            $v['time'] = $v['time']*1000;
            $v['href'] ='/home/wxindex/lecture_from/content_id/'.$v['id'];
        }
        $this->Apireturn($data);
    }
    //党员心得
    public function gain(){
        $data['name'] = $_REQUEST['name'];
        $data['telephone'] = $_REQUEST['phone'];
        $data['age'] = $_REQUEST['age'];
        $data['organizations'] = $_REQUEST['organizations'];
        $data['openid'] = base64_decode($_REQUEST['openid']);
        $data['content'] = $_REQUEST['content_x'];
        $data['identity'] = $_REQUEST['identity'];
        $data['title'] = $_REQUEST['title'];
        $data['cre_time'] = time();
        $data['time'] = time();
        $id = M('sign_gain')->add($data);
        if($id){
            $this->Apireturn($id,200,'提交成功');
        }else{
            $this->Apireturn(array(),300,'服务器繁忙！请稍等再次提交！');
        }
        exit;
    }
    //党员心得大屏
    public function gains(){
        if($_REQUEST['name']){$data['name'] = $_REQUEST['name'];}else{$this->Apireturn(array(),300,'用户名错误！！');}
        if(preg_match("/^1[345678]{1}\d{9}$/",$_REQUEST['phone'])){$data['telephone'] = $_REQUEST['phone'];}else{$this->Apireturn(array(),300,'手机号错误！！');}
//        if($_REQUEST['identity']){$data['identity'] = $_REQUEST['identity'];}else{$this->Apireturn(array(),300,'身份证必填！！');}
        if($_REQUEST['title']){$data['title'] = $_REQUEST['title'];}else{$this->Apireturn(array(),300,'标题不能为空！！');}
        if($_REQUEST['content']){$data['content'] = $_REQUEST['content'];}else{$this->Apireturn(array(),300,'心得不能为空！！');}
        $data['age'] = $_REQUEST['age'];
        $data['organizations'] = $_REQUEST['organizations'];
        $data['identity'] = $_REQUEST['identity'];
        $data['cre_time'] = time();
        $data['time'] = time();
        $id = M('sign_gain')->add($data);
        if($id){
            $this->Apireturn($id,200,'提交成功');
        }else{
            $this->Apireturn(array(),300,'服务器繁忙！请稍等再次提交！');
        }
        exit;
    }
    //微心愿接口
    public function wish($obj=0,$form=0,$state=0,$page=1,$r=200){
        $a = array(
            1=>'个人',
            2=>'团体',
            3=>'群众',
        );
        $b = array(
            1=>'物质',
            2=>'精神',
        );
        $c = array(
            1=>'待认领',
            2=>'已认领',
            3=>'已完成',
        );
        if($obj){
            $map['obj'] = $obj;
        }
        if($form){
            $map['form'] = $form;
        }
        if($state){
            $map['state'] = $state;
        }else{
            $map['state'] = array('in','1,2,3');
        }
        $map['status'] =1;

        $data =array();
        $data['list'] = array();
        $data['list'] = M('sign_wish')->where($map)->order('cre_time desc')->field('id,title,organization,obj,form,start_time,img,state')->page($page, $r)->select();
        $data['count'] = ceil(M('sign_wish')->where($map)->count()/$r);
        foreach ($data['list'] as $k=>&$v){
            $v['obj'] = $a[$v['obj']];
            $v['form'] = $b[$v['form']];
            $v['state'] = $c[$v['state']];
            $v['href'] = '/home/wxindex/wish_det/content_id/'.$v['id'];
            $v['start_time'] = date("Y-m-d H:i:s",$v['start_time']);
            $v['img'] =  "http://" . $_SERVER ['HTTP_HOST'].pic($v['img']);
        }
        $this->Apireturn($data);

    }
    //个人微心愿接口(发布)
    public function wishuser($obj=0,$form=0,$state=0,$page=1,$r=200){
        $openid = $_REQUEST['openid'];
        if(!$openid){
            $this->Apireturn(array(),300,'用户未登录！！！！');
        }else{
            $map['openid'] = base64_decode($openid);
        }
        $a = array(
            1=>'个人',
            2=>'团体',
            3=>'群众',
        );
        $b = array(
            1=>'物质',
            2=>'精神',
        );
        $c = array(
            -1=>'审批未通过',
            0=>'待审批',
            1=>'审批通过',
            2=>'已认领',
            3=>'已完成',
            4=>'已取消',
            5=>'已评价',
        );
        if($obj){
            $map['obj'] = $obj;
        }
        if($form){
            $map['form'] = $form;
        }
        if($state){
            if($state == 6){
                $map['state'] = 0;
            }else{
                $map['state'] = $state;
            }
        }else{
            $map['state'] = array('in','-1,0,1,2,3,4,5');
        }

        $map['status'] =1;

        $data =array();
        $data['list'] = array();
        $data['list'] = M('sign_wish')->where($map)->order('cre_time desc,id desc')->field('id,title,organization,obj,form,start_time,img,state')->page($page, $r)->select();
        $data['count'] = ceil(M('sign_wish')->where($map)->count()/$r);
        foreach ($data['list'] as $k=>&$v){
            $v['obj'] = $a[$v['obj']];
            $v['form'] = $b[$v['form']];
            $v['state'] = $c[$v['state']];
            $v['href'] = '/home/wxindex/wish_det/content_id/'.$v['id'];
            $v['start_time'] = date("Y-m-d H:i:s",$v['start_time']);
            $v['img'] =  "http://" . $_SERVER ['HTTP_HOST'].pic($v['img']);
        }
        $this->Apireturn($data);

    }
    //个人微心愿接口（申请）
    public function applywishuser($obj=0,$form=0,$state=0,$page=1,$r=200){
        $openid = $_REQUEST['openid'];
//        $openid = 'b19qSHV2NEVlZXlBOUMzUzVnQzJqdy1rUnNRZw==';
        if(!$openid){
            $this->Apireturn(array(),300,'用户未登录！！！！');
        }else{
            $mapp['openid'] = base64_decode($openid);
            if($state){
                if($state == 6){
                    $mapp['state'] = 0;
                }else{
                    $mapp['state'] = $state;
                }
            }else{
                $mapp['state'] = array('in','-1,0,1');
            }
            $mapp['statue'] = 1;
            $da = M('sign_wish_apply')->where($mapp)->select();
            if($da){
                $map = array();
                $idsa = '';
                foreach ($da as  $k=>$v){
                    $idsa[] = $v['wish_id'];
                    $stesa[$v['wish_id']] = $v['state'];
                }
                $idsa = implode(',',$idsa);
                $map['id'] = array('in',$idsa);
            }else{
                $this->Apireturn(array());
            }
        }
        $a = array(
            1=>'个人',
            2=>'团体',
            3=>'群众',
        );
        $b = array(
            1=>'物质',
            2=>'精神',
        );
        $c = array(
            -1=>'审批未通过',
            0=>'待审批',
            1=>'审批通过',
            2=>'已认领',
            3=>'已完成',
            4=>'已取消',
            5=>'已评价',
        );
        if($obj){
            $map['obj'] = $obj;
        }
        if($form){
            $map['form'] = $form;
        }
//        if($state){
//            if($state == 6){
//                $map['state'] = 0;
//            }else{
//                $map['state'] = $state;
//            }
//        }else{
            $map['state'] = array('in','2,3,5');
//        }
        $map['status'] =1;

//        dump($map);exit;
        $data =array();
        $data['list'] = array();
        $data['list'] = M('sign_wish')->where($map)->order('cre_time desc,id desc')->field('id,title,organization,obj,form,start_time,img,state,applyPL')->page($page, $r)->select();
        $data['count'] = ceil(M('sign_wish')->where($map)->count()/$r);
        foreach ($data['list'] as $k=>&$v){
            if($stesa[$v['id']] == -1){
                $v['state'] = -1;
            }
            if($stesa[$v['id']] == 0){
                $v['state'] = 0;
            }
            if($v['applyPL'] == 0){
                if($v['state'] == 5){
                    $v['state'] = 3;
                }
            }elseif($v['applyPL'] == 1){
                if($v['state'] == 3){
                    $v['state'] = 5;
                }
            }
            $v['obj'] = $a[$v['obj']];
            $v['form'] = $b[$v['form']];
            $v['state'] = $c[$v['state']];
            $v['href'] = '/home/wxindex/wish_det/content_id/'.$v['id'];
            $v['start_time'] = date("Y-m-d H:i:s",$v['start_time']);
            $v['img'] =  "http://" . $_SERVER ['HTTP_HOST'].pic($v['img']);

        }
        $this->Apireturn($data);

    }
    //检测是否为党员
    private function identitys($openid=null,$identity =null){
        if($openid){
            $openid = base64_decode($openid);
            $user = M('wxuser')->where(array('openid'=>$openid))->find();
            if(!$user['identity']){
                $this->Apireturn(array(),300,'对不起！您尚未进行实名认证！无法参与该活动');
            }
            $ajaxuser = M('ajax_user')->where(array('IDCARD'=>$user['identity']))->find();
            if(!$ajaxuser['id']){
                $this->Apireturn(array(),300,'对不起！您不是党员！无法参与该活动');
            }
            return $user;
        }elseif ($identity){
            $user =  M('ajax_user')->where(array('IDCARD'=>$identity))->find();
            if(!$user['id']){
                $this->Apireturn(array(),300,'对不起！您不是党员！无法参与该活动');
            }
            return  $user;
        }

    }
    //项目众筹列表
    public function zhch(){
        $map['status'] = 1;
        $map['state'] = 1;
        $data = M('sign_zhch')->where($map)->select();
        $da = array();
        foreach ($data as $k=>&$v){
            $da[$k]['pic'] ='http://'.$_SERVER['HTTP_HOST'].pic($v['img']);
            $da[$k]['title'] = $v['title'];
            $da[$k]['id'] = $v['id'];
            $da[$k]['text'] = $v['content'];
            $da[$k]['money'] = $v['money'];
            $da[$k]['count'] = $v['count'];
            $da[$k]['href'] = 'http://'.$_SERVER['HTTP_HOST'].'/home/wxindex/raise_det/id/'.$v['id'];
            if($v['classif'] == 1){
                $da[$k]['speed'] = M('sign_zhch_apply')->where(array('zhch_id'=>$v['id'],'state'=>3))->sum('money');
                $da[$k]['progress'] = number_format( $da[$k]['speed']/$v['money'],4)*100 .'%';
            }else{
                $da[$k]['speed'] = M('sign_zhch_apply')->where(array('zhch_id'=>$v['id'],'state'=>3))->sum('count');
                $da[$k]['progress'] = number_format( $da[$k]['speed']/$v['count'],4)*100 .'%';
            }
        }
        $this->Apireturn($da);
    }
    //志愿者活动列表
    public function volunteer(){
        $map['issue_id'] =84;
        $map['status'] =1;
        $data = M('issue_content')->where($map)->order('sort desc ,id desc')->select();
        $da = array();
        foreach ($data as $k=>&$v){
            $da[$k]['title'] = $v['title']; //标题
            $da[$k]['addr'] = $v['addr']; //活动地址
            $da[$k]['gather'] = $v['gather'];//集合地址
            $da[$k]['time'] = $v['time']; //活动开始时间
            $da[$k]['duration'] = $v['duration'];//活动持续时间
            $da[$k]['num'] = $v['num'];//计划招募人数
            $da[$k]['num_var'] = M('sign_volunteer')->where(array('content_id'=>$v['id'],'status'=>1,'state'=>1))->count('bespoke_num');//计划招募人数
        }
        $this->Apireturn($da);
    }
    //数据大屏左上角 党组织
    public function information(){

        $cx['PARENT_IDS'] = array('like',"%,92,%");
        $data = array();
        $data['organization']['count'] =(int) M('ajax_dzz')->where($cx)->count();
        //党委
//        $map['PARENT_IDS'] =  array('like',"%,92,%");
//        $map['TYPE'] = 0;
//        $data['organization']['committee']['count'] = (int)M('ajax_dzz')->where($map)->count();  //党总支统计
//        $data['organization']['committee']['name'] = '党委';  //党总支统计
//        $data['organization']['committee']['Percentage'] = number_format( $data['organization']['committee']['count']/$data['organization']['count'],4)*100;

        $map['PARENT_IDS'] =  array('like',"%,92,%");
        $map['TYPE'] = 0;
        $data['organization']['general']['count'] = (int)M('ajax_dzz')->where($map)->count();  //党总支统计
        $data['organization']['general']['name'] = '党委';  //党总支统计
        $data['organization']['general']['Percentage'] = number_format( $data['organization']['general']['count']/$data['organization']['count'],4)*100;
        //党总支部2
//        $map1['PARENT_IDS'] =  array('like',"%,92,%");
//        $map1['TYPE'] = 1;
//        $data['organization']['general']['count'] = (int)M('ajax_dzz')->where($map1)->count();  //党总支统计
//        $data['organization']['general']['name'] = '党总支';  //党总支统计
//        $data['organization']['general']['Percentage'] = number_format( $data['organization']['general']['count']/$data['organization']['count'],4)*100 ;

        $map1['PARENT_IDS'] =  array('like',"%,92,%");
        $map1['TYPE'] = 1;
        $data['organization']['branch']['count'] = (int)M('ajax_dzz')->where($map1)->count();  //党总支统计
        $data['organization']['branch']['name'] = '党总支';  //党总支统计
        $data['organization']['branch']['Percentage'] = number_format( $data['organization']['branch']['count']/$data['organization']['count'],4)*100 ;
        //党支部
//        $map2['PARENT_IDS'] = array('like',"%,92,%");
//        $map2['TYPE'] = 2;
//        $data['organization']['branch']['count'] = (int)M('ajax_dzz')->where($map2)->count();  //党支部统计
//        $data['organization']['branch']['name'] = '党支部';  //党支部统计
//        $data['organization']['branch']['Percentage'] = number_format( $data['organization']['branch']['count']/$data['organization']['count'],4)*100 ;

        $map2['PARENT_IDS'] = array('like',"%,92,%");
        $map2['TYPE'] = 2;
        $data['organization']['committee']['count'] = (int)M('ajax_dzz')->where($map2)->count();  //党支部统计
        $data['organization']['committee']['name'] = '党支部';  //党支部统计
        $data['organization']['committee']['Percentage'] = number_format( $data['organization']['committee']['count']/$data['organization']['count'],4)*100 ;
        //联合党支部
        $map3['PARENT_IDS'] = array('like',"%,92,%");
        $map3['TYPE'] = 3;
        $data['organization']['union']['count'] = (int)M('ajax_dzz')->where($map3)->count();  //党委统计
        $data['organization']['union']['name'] = '联合党支部';  //党委统计
        $data['organization']['union']['Percentage'] = number_format( $data['organization']['union']['count']/$data['organization']['count'],4)*100 ;
        //网络e支部
        $map3['PARENT_IDS'] = array('like',"%,92,%");
        $map3['TYPE'] = 4;
        $data['organization']['network']['count'] = (int)M('ajax_dzz')->where($map3)->count();  //党委统计
        $data['organization']['network']['name'] ='网格e支部';  //党委统计
        $data['organization']['network']['Percentage'] = number_format( $data['organization']['network']['count']/$data['organization']['count'],4)*100 ;

        //党组织类别
        $data['type']['count'] =$data['organization']['count'] ;
        //部门机关
        $map4['PARENT_IDS'] = array('like',"%,92,%");
        $map4['NATURE'] = array('like',"%部门机关%");
        $data['type']['office']['count'] =(int) M('ajax_dzz')->where($map4)->count();
        $data['type']['office']['name'] ='部门机关';
        $data['type']['office']['Percentage'] =number_format( $data['type']['office']['count']/$data['organization']['count'],4)*100 ;
        //国有企业
        $map5['PARENT_IDS'] = array('like',"%,92,%");
        $map5['NATURE'] = array('like',"%国有企业%");
        $data['type']['enterprise']['count'] = (int)M('ajax_dzz')->where($map5)->count();
        $data['type']['enterprise']['name'] = '国有企业';
        $data['type']['enterprise']['Percentage'] =number_format( $data['type']['enterprise']['count']/$data['organization']['count'],4)*100 ;
        //事业单位
        $map6['PARENT_IDS'] = array('like',"%,92,%");
        $map6['NATURE'] = array('like',"%事业单位%");
        $data['type']['undertaking']['count']=(int) M('ajax_dzz')->where($map6)->count();
        $data['type']['undertaking']['name']='事业单位';
        $data['type']['undertaking']['Percentage'] =number_format( $data['type']['undertaking']['count']/$data['organization']['count'],4)*100 ;
        //村社
        $map7['PARENT_IDS'] = array('like',"%,92,%");
        $map7['NATURE'] = array('like',"%村社%");
        $data['type']['community']['count']=(int) M('ajax_dzz')->where($map7)->count();
        $data['type']['community']['name']='村社';
        $data['type']['community']['Percentage'] =number_format( $data['type']['community']['count']/$data['organization']['count'],4)*100 ;
        //两新组织
        $map8['PARENT_IDS'] = array('like',"%,92,%");
        $map8['NATURE'] = array('like',"%两新%");
        $data['type']['new']['count']=(int) M('ajax_dzz')->where($map8)->count();
        $data['type']['new']['name']='两新';
        $data['type']['new']['Percentage'] =number_format( $data['type']['new']['count']/$data['organization']['count'],4)*100 ;
        $da['dzz'] = $data;
        unset($data);unset($map);unset($map2);unset($map3);unset($map4);unset($map5);unset($map6);
        $this->Apireturn($da);

    }
    //数据大屏左上角 党组织
    public function details($type=1,$subtype=2,$classify=1,$page = 1, $r = 200){
        $head1 = array(  //党组织
            array('name'=>'党组织名称', 'width'=>30),
            array('name'=>'党组织书记', 'width'=>15),
            array('name'=>'党组织地址', 'width'=>30),
            array('name'=>'人数', 'width'=>15),
            array('name'=>'到会率', 'width'=>10),
        );

        switch ($type){
            case 1:
                if($subtype == 1){
                    $map3['PARENT_IDS'] = array('like',"%,92,%");
                    if($classify == 1){
                        $map3['TYPE'] = 0;
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] =ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '基层党委信息';
                    }
                    elseif ($classify == 2) {
                        $map3['TYPE'] = 1;
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '党总支信息';
                    }
                    elseif ($classify == 3) {
                        $map3['TYPE'] = 2;
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '党支部信息';
                    }
                    elseif ($classify == 4) {
                        $map3['TYPE'] = 3;
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '联合党支部信息';
                    }
                    elseif ($classify == 5) {
                        $map3['TYPE'] = 4;
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '网络e支部';
                    }
                    else{
                        $this->apiReturn(array(),300,'错误信息');
                    }
                }
                elseif ($subtype == 2){
                    $map3['PARENT_IDS'] = array('like',"%,92,%");
                    if($classify == 1){
                        $map3['NATURE'] = array('like',"%部门机关%");
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '部门机关组织信息';
                    }elseif($classify == 2){
                        $map3['NATURE'] = array('like',"%国有企业%");
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '国有企业组织信息';
                    }elseif($classify == 3){
                        $map3['NATURE'] = array('like',"%事业单位%");
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '事业单位组织信息';
                    }elseif($classify == 4){
                        $map3['NATURE'] = array('like',"%村社%");
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '村社组织信息';
                    }elseif($classify == 5){
                        $map3['NATURE'] = array('like',"%两新%");
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '两新组织信息';
                    }else{
                        $this->apiReturn(array(),300,'错误信息');
                    }
                }
                else{
                    $this->apiReturn(array(),300,'错误信息');
                }
//                $li = M('ajax_user_st')->select();
//                foreach ($li as $k2=>$v2){
//                    $li2[$v2['BRANCH_ID']] =  $v2['count'];
//                }
                $dzzUser = $this->dzzUser();
                $dzzDhl = $this->dzzDHL();

                foreach ($data as $k=>$v) {
                    $da[$k] = array(
                        array('value'=>$v['NAME'], 'width'=>30,'ID'=>$v['BRANCH_ID'],'type'=>'partyMember'),
                        array('value'=>$v['SECRETARY'], 'width'=>15,'ID'=>$v['BRANCH_ID'],'type'=>'partyMember'),
                        array('value'=>$v['ADDRESS'], 'width'=>30,'ID'=>$v['BRANCH_ID'],'type'=>'partyMember'),
                        array('value'=>$dzzUser[$v['BRANCH_ID']]?$dzzUser[$v['BRANCH_ID']]:'暂无数据', 'width'=>15,'ID'=>$v['BRANCH_ID'],'type'=>'partyMember'),
                        array('value'=>$dzzDhl[$v['BRANCH_ID']]?$dzzDhl[$v['BRANCH_ID']].'%':mt_rand(70,95).'%', 'width'=>10,'ID'=>$v['BRANCH_ID'],'type'=>'partyMember'),
                    );
                }
                $list['head'] = $head1;
                if($da){
                    $list['list'] = $da;
                }else{
                    $list['list'] = array();
                }
                $this->apiReturn($list);
                break;
            default:
                $this->apiReturn(array(),300,'错误请求');
                break;
        }

    }
    public function details2($order = null,$type=1,$subtype=1,$classify=1,$page = 1, $r = 200){
        $head1 = array(  //党组织
            array('name'=>'党组织名称', 'width'=>30),
            array('name'=>'党组织书记', 'width'=>15),
            array('name'=>'党组织地址', 'width'=>30),
            array('name'=>'人数', 'width'=>15),
            array('name'=>'到会率', 'width'=>10),
        );

        switch ($type){
            case 1:
                if($subtype == 1){
                    $map3['PARENT_IDS'] = array('like',"%,92,%");
                    if($classify == 1){
                        $map3['TYPE'] = 0;
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] =ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '基层党委信息';
                    }
                    elseif ($classify == 2) {
                        $map3['TYPE'] = 1;
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '党总支信息';
                    }
                    elseif ($classify == 3) {
                        $map3['TYPE'] = 2;
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '党支部信息';
                    }
                    elseif ($classify == 4) {
                        $map3['TYPE'] = 3;
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '联合党支部信息';
                    }
                    elseif ($classify == 5) {
                        $map3['TYPE'] = 4;
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '网络e支部';
                    }
                    else{
                        $this->apiReturn(array(),300,'错误信息');
                    }
                }
                elseif ($subtype == 2){
                    $map3['PARENT_IDS'] = array('like',"%,92,%");
                    if($classify == 1){
                        $map3['NATURE'] = array('like',"%部门机关%");
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '部门机关组织信息';
                    }elseif($classify == 2){
                        $map3['NATURE'] = array('like',"%国有企业%");
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '国有企业组织信息';
                    }elseif($classify == 3){
                        $map3['NATURE'] = array('like',"%事业单位%");
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '事业单位组织信息';
                    }elseif($classify == 4){
                        $map3['NATURE'] = array('like',"%村社%");
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '村社组织信息';
                    }elseif($classify == 5){
                        $map3['NATURE'] = array('like',"%两新%");
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '两新组织信息';
                    }else{
                        $this->apiReturn(array(),300,'错误信息');
                    }
                }
                else{
                    $this->apiReturn(array(),300,'错误信息');
                }
//                $li = M('ajax_user_st')->select();
//                foreach ($li as $k2=>$v2){
//                    $li2[$v2['BRANCH_ID']] =  $v2['count'];
//                }
                $dzzUser = $this->dzzUser();
                $dzzDhl = $this->dzzDHL2();

                foreach ($data as $k=>$v) {
//                    if($dzzDhl[$v['BRANCH_ID']]['rcount'] ==0){
//                        $DHL = 100;
//                    }else{
                        $DHL = $dzzDhl[$v['BRANCH_ID']]['count'];
//                    }
                    $da[$k] = array(
                        array('value'=>$v['NAME'], 'width'=>30,'ID'=>$v['BRANCH_ID'],'type'=>'partyMember'),
                        array('value'=>$v['SECRETARY'], 'width'=>15,'ID'=>$v['BRANCH_ID'],'type'=>'partyMember'),
                        array('value'=>$v['ADDRESS'], 'width'=>30,'ID'=>$v['BRANCH_ID'],'type'=>'partyMember'),
                        array('value'=>$dzzUser[$v['BRANCH_ID']]?$dzzUser[$v['BRANCH_ID']]:'暂无数据', 'width'=>15,'ID'=>$v['BRANCH_ID'],'type'=>'partyMember'),
                        array(
//                            'value'=>$dzzDhl[$v['BRANCH_ID']]?$dzzDhl[$v['BRANCH_ID']].'%':mt_rand(70,95).'%',
                            'value'=>$DHL.'%',
                            'width'=>10,
                            'ID'=>$v['BRANCH_ID'],
                            'type'=>'partyMember',
                            'order'=>$dzzDhl[$v['BRANCH_ID']]['count'],
                        ),
                    );
                }

                if($order == 1){
                    foreach($da as $vals){
                        $key_arrays[]=$vals[4]['order'];
                    }
                    array_multisort($key_arrays,SORT_ASC,SORT_NUMERIC,$da);
                }
                elseif($order == -1){
                    foreach($da as $vals){
                        $key_arrays[]=$vals[4]['order'];
                    }
                    array_multisort($key_arrays,SORT_DESC,SORT_NUMERIC,$da);
                }
                $list['head'] = $head1;
                if($da){
                    $list['list'] = $da;
                }else{
                    $list['list'] = array();
                }
                $this->apiReturn($list);
                break;
            default:
                $this->apiReturn(array(),300,'错误请求');
                break;
        }

    }

    //数据大屏左上角 党组织  党员数据
    public function partyMember($id=null,$page = 1, $r = 200){
        $herf = array(
            array('name'=>'姓名', 'width'=>20),
            array('name'=>'性别', 'width'=>10),
            array('name'=>'身份证号', 'width'=>30),
            array('name'=>'党员类别', 'width'=>20),
            array('name'=>'党员卡状态', 'width'=>20),
        );
        $dyk = array(
          1=>'正常',
          2=>'锁定',
          3=>'注销',
          4=>'特殊无限制',
          5=>'不计到会率',
        );
        $dy=array(
            1=>'正式党员',
            2=>'预备党员',
            3=>'死亡',
            4=>'开除党籍',
            5=>'保留党籍',
        );
        if(!$id){
            $this->Apireturn(array(),300,'错误请求！！！！');
        }
//        $map['BRANCH_ID'] = $id;
//        $data = M('ajax_user')->where($map)->page($page,$r)->select();
        $tit = M('ajax_dzz')->where(array('BRANCH_ID'=>$id))->getField('NAME');
        $map['PARENT_IDS'] = array('like',"%,".$id.",%");
        $a = M('ajax_dzz')->where($map)->field('BRANCH_ID')->select();
        foreach ($a as $k2=>&$v2){
            $b[] = $v2['BRANCH_ID'] ;
        }
        $b = implode(',',$b).','.$id;
        $map2['BRANCH_ID'] = array('in',$b);
        $data = M('ajax_user')->where($map2)->page($page,$r)->select();
        foreach ($data as $k=>$v) {
            $da[$k] = array(
                array('value'=>$v['NAME'], 'width'=>20),
                array('value'=>$v['SEX'], 'width'=>10),
                array('value'=>$v['IDCARD'], 'width'=>30),
                array('value'=>$dy[$v['STATE']], 'width'=>20),
                array('value'=>$dyk[$v['CARD_STATE']], 'width'=>20),
            );
        }
        $list['head'] = $herf;
        $list['list'] = $da;
        $list['title'] = $tit.'党员信息';
        $this->Apireturn($list);

    }
    //微信用户认证党员
    public function userinformation(){
        $tableuser = M('wxuser');
        $ajaxuser = M('ajax_user');
        if($_REQUEST['openid']){$map['openid'] = base64_decode($_REQUEST['openid']);}else{$this->Apireturn(array(),300,'用户尚未登陆');}  //微信
        if($_REQUEST['identity']){$save['identity'] = $_REQUEST['identity'];}else{$this->Apireturn(array(),300,'用户尚未登陆');}  //微信
        $user =$tableuser->where($map)->find(); //微信用户
        $userif =$tableuser->where(array('identity'=>$_REQUEST['identity']))->find(); //身份证是否已绑定
        if(!$user['id']){
            $this->Apireturn(array(),300,'用户不存在');
        }
        if($userif['id']){
            $this->Apireturn(array(),300,'身份证已认证其他微信用户');
        }
            $id = $tableuser ->where($map)->save($save);
            if($id){
                $this->Apireturn(array(),200,'认证成功');
            }else{
                $this->Apireturn(array(),300,'服务器玩忙请稍后！！！！！！');
            }




    }

    //个人页面  签到接口
    public function userIntegral(){
        $tableuser = M('wxuser');
        $tableintegral = M('wxuser_integral');
        if($_REQUEST['openid']){$map['openid'] = $_REQUEST['openid'];}else{$this->Apireturn(array(),300,'用户尚未登陆');}  //党员限定
        $user = $tableuser->where($map)->find();
        $t = date('d',time()) - date('d',$user['sign_time']);
        if($t&&$user['id']){
            $tableuser->startTrans();
            $tableintegral->startTrans();
            $sid = $tableuser->where($map)->save(array('sign_time'=>time(),'integral'=>$user['integral']+1));
            $da['time'] = time();
            $da['user_id'] = $user['id'];
            $da['openid'] = $user['openid'];
            $da['integral'] = 1;
            $da['classif'] = 1;
            $da['text'] = '签到';
            $iid = $tableintegral->add($da);
            if($sid&&$iid){
                $tableuser->commit();
                $tableintegral->commit();
                $this->Apireturn(array(),200,'签到成功');
            }else{
                $tableintegral->rollback();
                $tableuser->rollback();
                $this->Apireturn(array(),300,'服务器繁忙请稍后~~！！！！！！');
            }
        }else{
            $this->Apireturn(array(),300,'您今日已签到~—~');
        }
    }

    //个人页面  取消预约
    public function useractivity(){
        $openid = base64_decode($_REQUEST['openid']);
        $id = $_REQUEST['id'];
        $classif = $_REQUEST['classif'];
        switch($classif){
            case 1: //活动
                $table = M('sign_bespoke');
                $map['openid'] = $openid;
                $map['id'] =  $id;
                $id = $table->where($map)->save(array('state'=>3));
                if($id){
                    $this->Apireturn($id,200,'取消成功');
                }else{
                    $this->Apireturn(array(),300,'取消失败');
                }
                break;
            case 2: //场地
                $table = M('sign_field');
                $map['openid'] = $openid;
                $map['id'] =  $id;
                $id = $table->where($map)->save(array('state'=>3));
                if($id){
                    $this->Apireturn($id,200,'取消成功');
                }else{
                    $this->Apireturn(array(),300,'取消失败');
                }
                break;
            case 3: //党课
                $table = M('sign_lecture');
                $map['openid'] = $openid;
                $map['id'] =  $id;
                $id = $table->where($map)->save(array('state'=>3));
                if($id){
                    $this->Apireturn($id,200,'取消成功');
                }else{
                    $this->Apireturn(array(),300,'取消失败');
                }
                break;
            case 4: //志愿者
                $table = M('sign_volunteer');
                $map['openid'] = $openid;
                $map['id'] =  $id;
                $id = $table->where($map)->save(array('state'=>3));
                if($id){
                    $this->Apireturn($id,200,'取消成功');
                }else{
                    $this->Apireturn(array(),300,'取消失败');
                }
                break;
            case 5: //微心愿
                $table = M('sign_wish');
                $map['openid'] = $openid;
                $map['id'] =  $id;
                $id = $table->where($map)->save(array('state'=>4));
                if($id){
                    $this->Apireturn($id,200,'取消成功');
                }else{
                    $this->Apireturn(array(),300,'取消失败');
                }
                break;

        }
    }

    //积分商城  列表页
    public function goodsList($page=1,$r=100){
        $goods = M('goods');
        $map['state'] = 1;
        $map['status'] = 1;
        $data = $goods->where($map)->page($page,$r)->select();
        foreach ($data as $k=>$v){
            $da['pic'] = pic($v['prod_pic']);
            $da['title'] =$v['prod_name'];
            $da['amount'] = $v['prod_num'];
            $da['price'] = $v['price'];
            $da['href'] = '/home/wxindex/goods_det/goodsid/'.$v['id'];
            $list['list'][] = $da;
            unset($da);
        }
        $count = $goods->where($map)->count();
        $list['count'] =(int)ceil($count / $r);
        $this->Apireturn($list);
    }

    //积分商城兑换
    public function exchange(){
        $goods = M('goods');
        $goodsorder = M('goods_order');
        $tableuser = M('wxuser');
        $tableintegral = M('wxuser_integral');
//        dump($_REQUEST);exit;
        if($_REQUEST['openid']){$user = $this->identitys($_REQUEST['openid']);}else{$this->Apireturn(array(),300,'用户尚未登陆');}  //党员限定
        if($_REQUEST['goodsid']){$data['goods_id'] = $_REQUEST['goodsid'];}else{$this->Apireturn(array(),300,'尚未选择商品');}  //商品选择
        if($_REQUEST['num']){$data['num'] = $_REQUEST['num'];}else{$this->Apireturn(array(),300,'请正确选择商品数量！！');}  //商品数量
        $map['id'] =$data['goods_id'];
        $map['status'] =1;
        $map['state'] =1;
        $good = $goods->where($map)->find(); //商品是否正式上架
        if(!$good){$this->Apireturn(array(),300,'您兑换的商品不存在');}
        $integral = $good['price']*$data['num'];  //所需要总积分
        if($integral>$user['integral']){$this->Apireturn(array(),300,'积分不足');}
        if($data['num']>$good['prod_num']){$this->Apireturn(array(),300,'商品剩余数量不足！！！');}

        //实务开始
        $goodsorder->startTrans();
        $goods->startTrans();
        $tableuser->startTrans();
        $tableintegral->startTrans();

        $goodssave = $goods->where($map)->setDec('prod_num',$data['num']);  //商品剩余数量减少
        $goodssave2 = $goods->where($map)->setInc('prod_sale',$data['num']);  //商品总销量增加
        $tableusersave = $tableuser->where(array('id'=>$user['id']))->setDec('integral',$integral);  //用户积分减少
        $order['goods_id']  = $good['id'];
        $order['integral']  = $integral;
        $order['user_id']  = $user['id'];
        $order['userIntegral']  = $user['integral'];
        $order['endIntegral']  = $user['integral']-$integral;
        $order['cre_time']  = time();
        $order['order']  = uniqid('JF');
        $order['num']  = $data['num'];
        $order['price']  = $good['price'];
        $tableorder = $goodsorder->add($order);  //订单生成
        //积分记录
        $da['user_id'] = $user['id'];;
        $da['openid'] = $user['openid'];
        $da['integral'] = -$integral;
        $da['classif'] = 4;
        $da['time'] = time();
        $da['text'] = '商品兑换：'.$good['prod_name'].'。';
        $daid = $tableintegral->add($da);

        if($goodssave&&$tableusersave&&$tableorder&&$goodssave2&&$daid){  //实务执行
            $goodsorder->commit();
            $goods->commit();
            $tableuser->commit();
            $tableintegral->commit();
            $this->Apireturn(array(),200,'下单成功');
        }else{
            $goodsorder->rollback();
            $goods->rollback();
            $tableuser->rollback();
            $tableintegral->rollback();
            $this->Apireturn(array(),300,'下单失败');
        }






    }
    //公共图片接口
    public function uploadImg(){
        $Picture = D('Admin/Picture');
        $driver = modC('PICTURE_UPLOAD_DRIVER','local','config');
        $driver = check_driver_is_exist($driver);
        $uploadConfig = get_upload_config($driver);
        $info = $Picture->upload(
            $_FILES,
            C('PICTURE_UPLOAD'),
            $driver,
            $uploadConfig
        );
        //TODO:上传到远程服务器
        /* 记录图片信息 */
        if ($info) {
            $return['status'] = 1;
            if ($info['Filedata']) {
                $return = array_merge($info['Filedata'], $return);
            }
            if ($info['download']) {
                $return = array_merge($info['download'], $return);
            }
            /*适用于自动表单的图片上传方式*/
            if ($info['file'] || $info['files']) {
                $return['data']['file'] = $info['file']?$info['file']:$info['files'];
            }
            /*适用于自动表单的图片上传方式end*/
            $aWidth= I('get.width',0,'intval');
            $aHeight=   I('get.height',0,'intval');
            if($aHeight<=0){
                $aHeight='auto';
            }
            if($aWidth>0){
                $return['path_self']=getThumbImageById($return['id'],$aWidth,$aHeight);
            }
        } else {
            $return['status'] = 0;
            $return['info'] = $Picture->getError();
        }
        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }
    //api总出口
    private function Apireturn($data,$code = 200,$msg = '请求成功'){
        if(!$data){
            $data= array();
        }
        $da['data'] = $data;
        $da['code'] = $code;
        $da['msg'] = $msg;
        echo json_encode($da);
        exit;
    }

    //genjson
    public function geoMap(){


        $da = M('ajax_map')->select();
        foreach ($da as $k=>$v){
            $list[] =array(
                'type'=>'Feature',
                'id'=>$v['id'],
                'geometry'=>array(
                    'type'=>'Point',
                    'coordinates'=>array(
                        0=> number_format($v['y'], 6),
                        1=>number_format($v['x'], 6),
                    )
                ),
                'properties'=>array(
                    '标题'=>$v['title'],
                    '地址'=>$v['address']

                ),
            );
        }

        $data = array(
            'type'=>'FeatureCollection',
            'features'=>$list,
            'name'=>'Transportation - DC',
            'updated_at'=>1405035068000
        );
        $this->Apireturn($data);

    }

    //视频接口
    public function videos_dp(){
        $herf = array(
            array('name'=>'编号', 'width'=>10),
            array('name'=>'名称', 'width'=>40),
            array('name'=>'地址', 'width'=>40),
            array('name'=>'状态', 'width'=>10),
        );
        $s = array(
            0=>'待机',
            1=>'正在开会',
        );
        $map['TDID']= array('exp','IS NOT NULL');
        $data = M('ajax_map')->where($map)->select();
        foreach ($data as $k=>$v) {
            $da[$k] = array(
                array('value'=>$v['id'], 'width'=>10,'tdid'=>$v['TDID'].'$1$0$0'),
                array('value'=>$v['title'], 'width'=>40,'tdid'=>$v['TDID'].'$1$0$0'),
                array('value'=>$v['address'], 'width'=>40,'tdid'=>$v['TDID'].'$1$0$0'),
                array('value'=>$s[$v['status']], 'width'=>10,'tdid'=>$v['TDID'].'$1$0$0'),
            );
        }
        $list['head'] = $herf;
        $list['list'] = $da;
        $list['title'] = '视频监控';
        $this->Apireturn($list);

    }

    //党组织所属党员信息
    private function dzzUser(){
        $data =  S('ajax_dzz_user');
        if(!$data){
        $c = M('ajax_dzz')->field('BRANCH_ID')->group('BRANCH_ID')->order('BRANCH_ID asc')->select();
        foreach ($c as $k=>$v){
            $map['PARENT_IDS'] = array('like',"%,".$v['BRANCH_ID'].",%");
            $a = M('ajax_dzz')->where($map)->field('BRANCH_ID')->select();
            foreach ($a as $k2=>&$v2){
                $b[] = $v2['BRANCH_ID'] ;
            }
            $b = implode(',',$b).','.$v['BRANCH_ID'];

            $map2['BRANCH_ID'] = array('in',$b);
            $list[$v['BRANCH_ID']] = M('ajax_user')->where($map2)->count();

            unset($b);

        }
        S('ajax_dzz_user',$list,60*60*24*7);
//        dump($list);
        }else{
            return $data;
        }
    }

    //党组织到会率
    private function dzzDHL2(){
//        S('dzz_DHL',null);
        $da = S('dzz_DHLS');
//        if(!$da){
//            $data = M('dr_dzz_dhl')->group('BRANCH_ID')->select();
//            foreach ($data as $k=>$v) {
//                if($v['pcount']==0 || $v['pcount_ydh']==0){
//                    $list[$v['BRANCH_ID']] = 100;
//                }
//                else{
//                    $list[$v['BRANCH_ID']] =  number_format( $v['rcount']/$v['pcount_ydh'],4)*100;
//                }
//                if($list[$v['BRANCH_ID']] > 100){
//                    $list[$v['BRANCH_ID']]=100;
//                }
//            }
//            S('dzz_DHL',$list);
//            $da = $list;
//        }

        if(!$da) {
            $maps['PARENT_IDS'] = array('like', "%,92,%");
            $das = M('ajax_dzz')->where($maps)->group('BRANCH_ID')->field('BRANCH_ID,PARENT_IDS')->select();
            foreach ($das as $k => $v) {
                $map['PARENT_IDS'] = array('like', "%," . $v['BRANCH_ID'] . ",%");
                $a = M('ajax_dzz')->where($map)->field('BRANCH_ID')->select();
                foreach ($a as $k2 => &$v2) {
                    $b[] = $v2['BRANCH_ID'];
                }
                $b = implode(',', $b) . ',' . $v['BRANCH_ID'];

                $map2['BRANCH_ID'] = array('in', $b);
                $list[$v['BRANCH_ID']]['rcount'] = M('dr_dzz_dhl')->where($map2)->sum('rcount');
                $list[$v['BRANCH_ID']]['pcount_ydh'] = M('dr_dzz_dhl')->where($map2)->sum('pcount_ydh');
//                if ($as == 0 || $bs == 0) {
//                    $list[$v['BRANCH_ID']] = 100;
//                } else {
                    $list[$v['BRANCH_ID']]['count'] = number_format($list[$v['BRANCH_ID']]['rcount'] / $list[$v['BRANCH_ID']]['pcount_ydh'], 4) * 100;
//                }
                if ($list[$v['BRANCH_ID']]['count'] > 100) {
                    $list[$v['BRANCH_ID']]['count'] = 100;
                }
                unset($b);
            }
            S('dzz_DHLS', $list);
            return $list;
        }
        return $da;

    }
    private function dzzDHL(){
        $da = S('dzz_DHL');
        if(!$da){
            $data = M('dr_dzz_dhl')->group('BRANCH_ID')->select();
            foreach ($data as $k=>$v) {
                $list[$v['BRANCH_ID']] =  number_format( $v['rcount']/$v['pcount_ydh'],4)*100;
                if($list[$v['BRANCH_ID']] > 100){
                    $list[$v['BRANCH_ID']]=100;
                }
            }
            S('dzz_DHL',$list);
            $da = $list;
        }
        return $da;

    }



    //红色资源
    public function redResource($classif = null){
        $map['classif'] = 1;
        $map['status'] = array('egt',1);
        $data['party'] = M('ajax_map')->where($map)->field('address,x,y,title,TDID,ip')->select();
        foreach ($data['party'] as $k=>&$v){
            if($v['TDID']){
                $v['tdid'] = $v['TDID'].'$1$0$0';
            }
            $v['lat'] = $v['x'];
            $v['lng'] = $v['y'];
        }
        $map['classif'] = array('in','1,2');
        $data['school'] = M('ajax_map')->where($map)->field('address,x,y,title,TDID,ip')->select();
        foreach ($data['school'] as $k1=>&$v1){
            if($v1['TDID']){
                $v1['tdid'] = $v1['TDID'].'$1$0$0';
            }
            $v1['lat'] = $v1['x'];
            $v1['lng'] = $v1['y'];
        }
        $map['classif'] = 4;
        $data['base'] = M('ajax_map')->where($map)->field('address,x,y,title,TDID,ip')->select();
        foreach ($data['base'] as $k2=>&$v2){
            if($v2['TDID']){
                $v2['tdid'] = $v2['TDID'].'$1$0$0';
            }
            $v2['lat'] = $v2['x'];
            $v2['lng'] = $v2['y'];
        }
        $map['classif'] = 3;
        $da = M('ajax_map')->where($map)->field('title')->group('title')->select();
        foreach ($da as $k3=>&$v3){
            $map['title'] = $v3['title'];
            $data['belt'][$k3] =  M('ajax_map')->where($map)->field('address,x,y,title,TDID,ip,status')->order('status asc')->select();
            foreach ($data['belt'][$k3] as $kk=>&$vv){
                if($vv['TDID']){
                    $vv['tdid'] = $vv['TDID'].'$1$0$0';
                }
                $vv['lat'] = $vv['x'];
                $vv['lng'] = $vv['y'];
            }
        }
        if($classif){
            $this->Apireturn($data[$classif]);
        }
        $this->Apireturn($data);
    }


    //预警信息
    public function warning(){
      $da['organizational_life'] = array(
          'party'=>array('count'=>1543,),
          'personnel'=>array('count'=>11940,),
      );
      $da['report'] = array(
            'sign'=>array('count'=>17398,),
            'nosign'=>array('count'=>1974,),
        );
        $da['money'] = array(
            'nomoney'=>array('count'=>11388,),
        );
        $this->Apireturn($da);
    }
    public function warning2(){
        $where['status'] = 1;
        //入党申请人
        $application_dy = M('dr_dzz_application_party')->where($where)->count();
        //入党积极分子
        $activity_dy = M('dr_dzz_activity_dy')->where($where)->count();
        //2018年报道
        $sign1 = M()->query("SELECT id,`name`,organization FROM cxdj_dr_dy_ztdr  ztdr WHERE ztdr.`name` NOT IN (SELECT `name` FROM cxdj_dr_dy_late)");
        $sign =count($sign1);
        //不计入到会党员
        $late_dy = M('dr_dy_late')->where($where)->count();
        //闪光言行
        $speech = M('dr_dy_sgyx')->where($where)->count();

        //党员-党性体检-cml

        $dr_dy_dxtj1 = M('ajax_user')->where()->count();
        $where_dxtj['status'] = 1;
        $where_dxtj['result1']='不健康';
        $dr_dy_dxtj2 = M('dr_dy_dxtj')->where($where_dxtj)->count();
        $where_dxtj['result1']='亚健康';
        $dr_dy_dxtj3 = M('dr_dy_dxtj')->where($where_dxtj)->count();
        $dr_dy_dxtj1 = $dr_dy_dxtj1 - $dr_dy_dxtj2 - $dr_dy_dxtj3;

        //党组织-已展开党性体检
        $dx_check = M('dr_dzz_dxtj')->where($where)->count();
        //党组织-未展开党性体检
        $dx_uncheck = M('dr_dzz_undxtj')->where($where)->count();
        //党组织-已展开主题党日
        $ztdr = M('dr_dzz_ztdr')->where($where)->count();
        //党组织-未展开主题党日
        $ztdr_no = M('dr_dzz_no_ztdr')->where($where)->count();
        //党组织-已经缴纳党费
        $data = M('dr_dzz_money')->query("SELECT SUM(money) AS `count`,organization,DATE_FORMAT(time,'%Y-%m-%d') AS `time`,COUNT(*) AS record FROM cxdj_dr_dzz_money WHERE money !=0  AND  status = 1  GROUP BY organization");
        $money = count($data);
        //党组织 - 未缴纳党费
        $data2 = M('dr_dzz_money')->query("SELECT SUM(money) AS `count`,organization,DATE_FORMAT(time,'%Y-%m-%d') AS `time`,COUNT(*) AS record FROM cxdj_dr_dzz_money WHERE money =0  AND  status = 1  GROUP BY organization");
        $money_no = count($data2);
        //党组织- 评优评先
        $honor = M('dr_dzz_honor')->where($where)->count();
        //党员- 已展开主题党日
        $dy_ztdr = M('dr_dy_ztdr')->where($where)->count();
        //党员- 未展开主题党日
        $dy_unztdr = M('dr_dy_unztdr')->where($where)->count();
        $da['organize'] = array(
            'theme'=>array('start'=>$ztdr, 'unStart'=>$ztdr_no,),
            'partier'=>array('apply'=>$application_dy, 'active'=>$activity_dy,),
            'experience'=>array('num'=>$dx_check, 'unNum'=>$dx_uncheck,),
            'pay'=>array('payed'=>$money, 'unPay'=>$money_no,),

     );
     $da['partier'] = array(
         'theme'=>array('join'=>$dy_ztdr, 'unJoin'=>$dy_unztdr,),
         'experience'=>array('health'=>$dr_dy_dxtj1, 'unHealth'=>$dr_dy_dxtj2,'yaHealth'=>$dr_dy_dxtj3),
         'register'=>array('num'=>$sign, 'unNum'=>$late_dy,),
         'appraise'=>array('sgyx'=>$speech, 'pypx'=>$honor,),
     );
        $this->Apireturn($da);
    }
    //预警信息详情
    public function warningList($types=1,$classify=1,$page=1,$r=200){
        $head1 = array(  //党员
            array('name'=>'序号', 'width'=>15),
            array('name'=>'姓名', 'width'=>20),
            array('name'=>'性别', 'width'=>15),
            array('name'=>'身份证', 'width'=>35),
            array('name'=>'民族', 'width'=>15),
        );
        $head2 = array(  //党员
            array('name'=>'序号', 'width'=>10),
            array('name'=>'姓名', 'width'=>20),
            array('name'=>'性别', 'width'=>10),
            array('name'=>'身份证', 'width'=>35),
            array('name'=>'签到时间', 'width'=>25),
        );
        $head = array(  //党员
            array('name'=>'序号', 'width'=>10),
            array('name'=>'党组织名称', 'width'=>35),
            array('name'=>'党组织类别', 'width'=>20),
            array('name'=>'党组织地址', 'width'=>35),
        );
        switch ($types){
            case 1:
                $list['title'] = '组织生活';
                $map['PARENT_IDS'] = array('like','%,92,%');
                if($classify ==1 ){  //5月
                    $map['classify'] = 5;
                    $data = M('dr_noactivity')->where($map)->order('id asc')->page($page,$r)->select();

                }
                elseif ($classify==2){//4月
                    $map['classify'] = 4;
                    $data = M('dr_noactivity')->where($map)->order('id desc')->page($page,$r)->select();
                }
                elseif ($classify==3){//3月
                    $map['classify'] = 3;
                    $data = M('dr_noactivity')->where($map)->order('NAME asc')->page($page,$r)->select();
                }
                else{
                    exit;
                }
                $list['head'] = $head;
                foreach($data as $k=>&$v){
                    $da[$k] = array(
                        array('value'=>$k+1, 'width'=>10),
                        array('value'=>$v['NAME'], 'width'=>35),
                        array('value'=>$v['NATURE'], 'width'=>20),
                        array('value'=>$v['ADDRESS'], 'width'=>35),
                    );
                }
                $list['list'] = $da;
                break;
            case 2:
                $list['title'] = '组织生活';
                if($classify ==1 ){  //5月
                    $map['classify'] = 5;
                    $data = M('dr_notime')->where($map)->order('id asc')->page($page,$r)->select();
                }
                elseif ($classify==2){//4月
                    $map['classify'] = 4;
                    $data = M('dr_notime')->where($map)->order('id desc')->page($page,$r)->select();
                }
                elseif ($classify==3){//3月
                    $map['classify'] = 3;
                    $data = M('dr_notime')->where($map)->order('NAME asc')->page($page,$r)->select();
                }else{
                    exit;
                }
                $list['head'] = $head1;
                foreach($data as $k=>&$v){
                    $da[$k] = array(
                        array('value'=>$k+1, 'width'=>15),
                        array('value'=>$v['NAME'], 'width'=>20),
                        array('value'=>$v['SEX'], 'width'=>15),
                        array('value'=>$v['IDCARD'], 'width'=>35),
                        array('value'=>$v['NATION'], 'width'=>15),
                    );
                }
                $list['list'] = $da;
                break;
            case 3:
                $list['title'] = '发展党员';
                $list['head'] = $head1;
                $list['list'] = array();
                break;
            case 4:
                $data =  M('dr_2018sign')->order('lastSign desc')->page($page,$r)->select();
                $list['head'] = $head2;
                foreach($data as $k=>&$v){
                    $da[$k] = array(
                        array('value'=>$k+1, 'width'=>10),
                        array('value'=>$v['NAME'], 'width'=>20),
                        array('value'=>$v['SEX'], 'width'=>10),
                        array('value'=>$v['IDCARD'], 'width'=>35),
                        array('value'=>$v['lastSign'], 'width'=>25),
                    );
                }
                $list['list'] = $da;
                $list['title'] = '报到登记';
                break;
            case 5:
                $map['CARD_STATE'] = 5;
                $data =  M('ajax_user')->where($map)->page($page,$r)->select();

                $list['head'] = $head1;
                foreach($data as $k=>&$v){
                    $da[$k] = array(
                        array('value'=>$k+1, 'width'=>15),
                        array('value'=>$v['NAME'], 'width'=>20),
                        array('value'=>$v['SEX'], 'width'=>15),
                        array('value'=>$v['IDCARD'], 'width'=>35),
                        array('value'=>$v['NATION'], 'width'=>15),
                    );
                }
                $list['list'] = $da;
                $list['title'] = '报到登记';
                break;
            case 6:
                $data = M('dr_nomoney')->order('lastFeeTime desc')->page($page,$r)->select();
                $list['head'] = $head1;
                foreach($data as $k=>&$v){
                    $da[$k] = array(
                        array('value'=>$k+1, 'width'=>15),
                        array('value'=>$v['NAME'], 'width'=>20),
                        array('value'=>$v['SEX'], 'width'=>15),
                        array('value'=>$v['IDCARD'], 'width'=>35),
                        array('value'=>$v['NATION'], 'width'=>15),
                    );
                }
                $list['list'] = $da;
                $list['title'] = '党费缴纳';
                break;
        }




        $this->Apireturn($list);

    }
    public function warningList2($origin,$types=1,$classify=1,$page=1,$r=200){
        $Model = M();
        $list = array();
        $head1 = array(  //党员
            array('name'=>'序号', 'width'=>10),
            array('name'=>'党组织名称', 'width'=>40),
            array('name'=>'活动主题', 'width'=>30),
            array('name'=>'活动时间', 'width'=>20),
        );
        $head2 = array(  //党员
            array('name'=>'序号', 'width'=>5),
            array('name'=>'姓名', 'width'=>10),
            array('name'=>'性别', 'width'=>5),
            array('name'=>'出生日期', 'width'=>15),
            array('name'=>'学历', 'width'=>10),
            array('name'=>'所在党支部', 'width'=>25),
            array('name'=>'联系电话', 'width'=>15),
            array('name'=>'职务名称', 'width'=>15),
        );
        $head3 = array(  //党员
            array('name'=>'序号', 'width'=>20),
            array('name'=>'党组织名称', 'width'=>40),
            array('name'=>'党组织书记', 'width'=>20),
            array('name'=>'体检时间', 'width'=>20),

        );
        $head4 = array(  //党费缴纳
            array('name'=>'序号', 'width'=>20),
            array('name'=>'党组织名称', 'width'=>40),
            array('name'=>'缴费金额', 'width'=>20),
            array('name'=>'缴费时间', 'width'=>20),
        );
        $head5 = array(  //党员
            array('name'=>'序号', 'width'=>10),
            array('name'=>'党员姓名', 'width'=>15),
            array('name'=>'所属党组织', 'width'=>30),
            array('name'=>'活动主题', 'width'=>30),
            array('name'=>'活动时间', 'width'=>15),
        );
        $head6 = array(  //党员
            array('name'=>'序号', 'width'=>20),
            array('name'=>'党员姓名', 'width'=>30),
            array('name'=>'所属党组织', 'width'=>50),
        );
        $head7 = array(  //党员
            array('name'=>'序号', 'width'=>10),
            array('name'=>'党员姓名', 'width'=>15),
            array('name'=>'所属党组织', 'width'=>35),
            array('name'=>'体检结果', 'width'=>20),
            array('name'=>'民主评议结果', 'width'=>20),
        );
        $head8 = array(  //党员
            array('name'=>'序号', 'width'=>10),
            array('name'=>'党员姓名', 'width'=>10),
            array('name'=>'闪光言行', 'width'=>50),
            array('name'=>'评定时间', 'width'=>20),
            array('name'=>'评定等级', 'width'=>10),
        );

        $head9 = array(  //党员
            array('name'=>'序号', 'width'=>10),
            array('name'=>'姓名', 'width'=>15),
            array('name'=>'性别', 'width'=>10),
            array('name'=>'出生日期', 'width'=>20),
            array('name'=>'学历', 'width'=>15),
            array('name'=>'所在党支部', 'width'=>30),
        );

        $head10 = array(  //党员
            array('name'=>'序号', 'width'=>20),
            array('name'=>'姓名', 'width'=>30),
            array('name'=>'所属党组织', 'width'=>50),
        );

        $head11 = array(  //未展开主题党日
            array('name'=>'序号', 'width'=>20),
            array('name'=>'党组织名称', 'width'=>50),
            array('name'=>'党组织书记', 'width'=>30),
        );

        $head13 = array(  //未缴纳党费
            array('name'=>'序号', 'width'=>20),
            array('name'=>'党组织名称', 'width'=>50),
            array('name'=>'缴费金额', 'width'=>30),
        );

        $head14 = array(  //评优评先数据
            array('name'=>'序号', 'width'=>20),
            array('name'=>'姓名', 'width'=>20),
            array('name'=>'所属党组织', 'width'=>40),
            array('name'=>'所获荣誉', 'width'=>20),
        );

        $head15 = array(  //未展开主题党日
            array('name'=>'序号', 'width'=>10),
            array('name'=>'党员姓名', 'width'=>20),
            array('name'=>'所属党组织', 'width'=>50),
            array('name'=>'活动时间', 'width'=>20),
        );

        $head16 = array(  //未展开党性体检
            array('name'=>'序号', 'width'=>20),
            array('name'=>'书记', 'width'=>30),
            array('name'=>'党组织名称', 'width'=>50),
        );

        if($origin == 'organize'){
            switch ($types){
                case 1:
                    $list['title'] = '已开展主题党日';
                    $list['head'] = $head1;
                    $data = $Model->query("SELECT
                                                (@i :=@i + 1) id,
                                                organization,
                                                title,
                                                DATE_FORMAT(time, '%Y-%m-%d') AS time
                                            FROM
                                                cxdj_dr_dzz_ztdr,
                                                (SELECT @i := 0) AS i
                                            WHERE
                                                DATE_FORMAT(time, '%Y') = DATE_FORMAT(NOW(), '%Y')
                                                AND  status = 1
                                            LIMIT 1000

                                            ");


                    foreach ($data as $k=>&$v){
                        $list['list'][] = array(
                            array('value'=>$v['id'], 'width'=>10),
                            array('value'=>$v['organization'], 'width'=>40),
                            array('value'=>$v['title'], 'width'=>30),
                            array('value'=>$v['time'], 'width'=>20),

                        );
                    }


                    break;
                case 2:
                    $list['title'] = '未开展主题党日';
                    $list['head'] = $head11;
                    if($classify ==1 ){  //5月
//                        $data = $Model->query("SELECT
//                                                    (@i :=@i + 1) id,
//                                                    organization,
//                                                    secretary,
//                                                    title,
//                                                    date_format(start_time, '%Y-%m-%d') AS `time`
//                                                FROM
//                                                    cxdj_dr_dzz_ztdr,
//                                                    (SELECT @i := 0) AS i
//                                                WHERE
//                                                    start_time BETWEEN date_sub(now(), INTERVAL 5 MONTH)
//                                                AND now()
//                                                AND date_format(start_time, '%Y-%m') != date_format(now(), '%Y-%m')
//                                                ORDER BY
//                                                    start_time DESC
//                                                    LIMIT 100");

                        $data = $Model->query('SELECT * FROM cxdj.cxdj_dr_dzz_no_ztdr WHERE status = 1 LIMIT 1000');
                        foreach ($data as $k=>&$v){
                            $list['list'][] = array(
                                array('value'=>$v['id'], 'width'=>20),
                                array('value'=>$v['organization'], 'width'=>50),
                                array('value'=>$v['secretary'], 'width'=>30),


                            );
                        }


                    }
                    elseif ($classify==2){//4月
                        $data = $Model->query("SELECT
                                                    (@i :=@i + 1) id,
                                                    organization,
                                                    secretary,
                                                    title,
                                                     date_format(start_time, '%Y-%m-%d') AS `time`
                                                FROM
                                                    cxdj_dr_dzz_ztdr,
                                                    (SELECT @i := 0) AS i
                                                WHERE
                                                    start_time BETWEEN date_sub(now(), INTERVAL 4 MONTH)
                                                AND now()
                                                AND date_format(start_time, '%Y-%m') != date_format(now(), '%Y-%m')
                                                ORDER BY
                                                    start_time ASC
                                                    LIMIT 1000");

                        foreach ($data as $k=>&$v){
                            $list['list'][] = array(
                                array('value'=>$v['id'], 'width'=>10),
                                array('value'=>$v['organization'], 'width'=>30),
                                array('value'=>$v['secretary'], 'width'=>15),
                                array('value'=>$v['title'], 'width'=>30),
                                array('value'=>$v['time'], 'width'=>15),

                            );
                        }
                    }
                    elseif ($classify==3){//3月
                        $data = $Model->query("SELECT
                                                    (@i :=@i + 1) id,
                                                    organization,
                                                    secretary,
                                                    title,
                                                      date_format(start_time, '%Y-%m-%d') AS `time`
                                                FROM
                                                    cxdj_dr_dzz_ztdr,
                                                    (SELECT @i := 0) AS i
                                                WHERE
                                                    start_time BETWEEN date_sub(now(), INTERVAL 3 MONTH)
                                                AND now()
                                                AND date_format(start_time, '%Y-%m') != date_format(now(), '%Y-%m')
                                                ORDER BY
                                                    start_time ASC
                                                    LIMIT 1000");

                        foreach ($data as $k=>&$v){
                            $list['list'][] = array(
                                array('value'=>$v['id'], 'width'=>10),
                                array('value'=>$v['organization'], 'width'=>30),
                                array('value'=>$v['secretary'], 'width'=>15),
                                array('value'=>$v['title'], 'width'=>30),
                                array('value'=>$v['time'], 'width'=>15),

                            );
                        }
                    }
                    else{
                        exit;
                    }

                    break;
                case 3:
                    $list['title'] = '入党申请';
                    $list['head'] = $head9;
                    $data = $Model->query("SELECT * FROM cxdj_dr_dzz_application_party LIMIT 1000");
                    foreach ($data as $k=>&$v){
                        $list['list'][] = array(
                            array('value'=>$v['id'], 'width'=>10),
                            array('value'=>$v['name'], 'width'=>15),
                            array('value'=>$v['sex'], 'width'=>10),
                            array('value'=>$v['birthday'], 'width'=>20),
                            array('value'=>$v['education'], 'width'=>15),
                            array('value'=>$v['organization'], 'width'=>30),
                        );
                    }

                    break;
                case 4:
                    $list['title'] = '入党积极分子';
                    $list['head'] = $head2;
                    $data = $Model->query("SELECT  (@i:=@i+1) id,`name`,sex,birthday,education,organization,phone,technical_title FROM cxdj_dr_dzz_activity_dy,(SELECT @i:=0) AS i LIMIT 1000");
                    foreach ($data as $k=>&$v){
                        $list['list'][] = array(
                            array('value'=>$v['id'], 'width'=>5),
                            array('value'=>$v['name'], 'width'=>10),
                            array('value'=>$v['sex'], 'width'=>5),
                            array('value'=>$v['birthday'], 'width'=>15),
                            array('value'=>$v['education'], 'width'=>10),
                            array('value'=>$v['organization'], 'width'=>25),
                            array('value'=>$v['phone'], 'width'=>15),
                            array('value'=>$v['technical_title'], 'width'=>15),
                        );
                    }
                    break;
                case 5:
                    $list['title'] = '已开展党性体检';
                    $list['head'] = $head3;
                    $data = $Model->query("SELECT * FROM cxdj.cxdj_dr_dzz_dxtj WHERE `status` = 1 LIMIT 1000");
                    foreach ($data as $k=>&$v){
                        $list['list'][] = array(
                            array('value'=>$v['id'], 'width'=>20),
                            array('value'=>$v['organization'], 'width'=>40),
                            array('value'=>$v['secretary'], 'width'=>20),
                            array('value'=>$v['time'], 'width'=>20),
                        );
                    }
                    break;
                case 6:
                    $list['title'] = '未开展党性体检';
                    $list['head'] = $head16;
                    $data = $Model->query("SELECT * FROM cxdj_dr_dzz_undxtj WHERE status = 1 LIMIT 1000");
                    foreach ($data as $k=>&$v){
                        $list['list'][] = array(
                            array('value'=>$v['id'], 'width'=>20),
                            array('value'=>$v['secretary'], 'width'=>30),
                            array('value'=>$v['organization'], 'width'=>50),
                        );
                    }
                    break;
                case 7:
                    $list['title'] = '已经缴纳';
                    $list['head'] = $head4;
                    $data = $Model->query("SELECT SUM(money) AS `count`,organization,DATE_FORMAT(time,'%Y-%m-%d') AS `time`,COUNT(*) AS record FROM cxdj_dr_dzz_money WHERE money !=0  AND  status = 1  GROUP BY organization LIMIT 1000");

                    $i = 1;
                    foreach ($data as $k=>&$v){
                        $ii = $i++;
                        $list['list'][] = array(
                            array('value'=>$ii, 'width'=>20),
                            array('value'=>$v['organization'], 'width'=>40),
                            array('value'=>$v['count'], 'width'=>20),
                            array('value'=>$v['time'], 'width'=>20),
                        );
                    }
                    break;
                case 8:
                    $list['title'] = '未缴纳党费';

                    if($classify ==1 ){  //5月
                        $list['head'] = $head13;
                        $data = $Model->query("SELECT
                                                    SUM(money) AS `count`,
                                                    organization,
                                                    DATE_FORMAT(time, '%Y-%m-%d') AS `time`,
                                                    COUNT(*) AS record
                                                FROM
                                                    cxdj_dr_dzz_money
                                                WHERE
                                                    money = 0
                                                AND  status = 1                                      
                                                GROUP BY
                                                    organization
                                                LIMIT 200");
                        $i = 1;
                        foreach ($data as $k=>&$v){
                            $ii = $i++;
                            $list['list'][] = array(
                                array('value'=>$ii, 'width'=>20),
                                array('value'=>$v['organization'], 'width'=>50),
                                array('value'=>$v['count'], 'width'=>30),
                            );
                        }

                    }
                    elseif ($classify==2){//4月

                    }
                    elseif ($classify==3){//3月

                    }
                    else{
                        exit;
                    }
//                    $list['head'] = $head4;
//                    $list['list'] = array();
                    break;
            }
        }
        elseif ($origin == 'partier'){
            switch ($types){
                case 1:
                    $list['title'] = '已开展主题党日';
                    $list['head'] = $head5;
                    $data = $Model->query("SELECT id,name,organization,title,time FROM cxdj_dr_dy_ztdr WHERE `status` = 1 LIMIT 1000");
                    foreach ($data as $k=>&$v){
                        $list['list'][] = array(
                            array('value'=>$v['id'], 'width'=>10),
                            array('value'=>$v['name'], 'width'=>15),
                            array('value'=>$v['organization'], 'width'=>30),
                            array('value'=>$v['title'], 'width'=>30),
                            array('value'=>$v['time'], 'width'=>15),
                        );
                    }
                    break;
                case 2:
                    $list['title'] = '未开展主题党日';
                    $list['head'] = $head15;
                    $data = $Model->query("SELECT id,name,organization,time FROM cxdj_dr_dy_unztdr WHERE status = 1 LIMIT 1000");
                    foreach ($data as $k=>&$v){
                        $list['list'][] = array(
                            array('value'=>$v['id'], 'width'=>10),
                            array('value'=>$v['name'], 'width'=>20),
                            array('value'=>$v['organization'], 'width'=>50),
                            array('value'=>$v['time'], 'width'=>20),
                        );
                    }
                    break;
                case 3:
                    $list['title'] = '2018年报到';
                    $list['head'] = $head6;
                    $data = $Model->query("SELECT id,`name`,organization FROM cxdj_dr_dy_ztdr  ztdr WHERE ztdr.`name` NOT IN (SELECT `name` FROM cxdj_dr_dy_late) AND `status` = 1 LIMIT 1000");
                    foreach ($data as $k=>&$v){
                        $list['list'][] = array(
                            array('value'=>$v['id'], 'width'=>20),
                            array('value'=>$v['name'], 'width'=>30),
                            array('value'=>$v['organization'], 'width'=>50),
                        );
                    }

                    break;
                case 4:
                    $list['title'] = '不计入到会党员';
                    $list['head'] = $head10;
                    $data = $Model->query("SELECT id,`name`,organization FROM cxdj_dr_dy_late LIMIT 1000");
                    foreach ($data as $k=>&$v){
                        $list['list'][] = array(
                            array('value'=>$v['id'], 'width'=>20),
                            array('value'=>$v['name'], 'width'=>30),
                            array('value'=>$v['organization'], 'width'=>50),
                        );
                    }
                    break;
                case 5:
                    $list['title'] = '体检健康';
                    $list['head'] = $head7;
                    $data = $Model->query("SELECT * FROM cxdj_dr_dy_dxtj where result1 = '健康' LIMIT 1000");
                    foreach ($data as $k=>&$v){
                        $list['list'][] = array(
                            array('value'=>$k+1, 'width'=>10),
                            array('value'=>$v['name'], 'width'=>15),
                            array('value'=>$v['organization'], 'width'=>35),
                            array('value'=>$v['result1'], 'width'=>20),
                            array('value'=>$v['result2'], 'width'=>20),
                        );
                    }
                    break;
                case 6:
                    $list['title'] = '体检亚健康';
                    $list['head'] = $head7;
                    $data = $Model->query("SELECT * FROM cxdj_dr_dy_dxtj where result1 = '亚健康' LIMIT 1000");
                    foreach ($data as $k=>&$v){
                        $list['list'][] = array(
                            array('value'=>$k+1, 'width'=>10),
                            array('value'=>$v['name'], 'width'=>15),
                            array('value'=>$v['organization'], 'width'=>35),
                            array('value'=>$v['result1'], 'width'=>20),
                            array('value'=>$v['result2'], 'width'=>20),
                        );
                    }
                    break;
                case 7:
                    $list['title'] = '体检不健康';
                    $list['head'] = $head7;
                    $data = $Model->query("SELECT * FROM cxdj_dr_dy_dxtj where result1 = '不健康' LIMIT 1000");
                    foreach ($data as $k=>&$v){
                        $list['list'][] = array(
                            array('value'=>$k+1, 'width'=>10),
                            array('value'=>$v['name'], 'width'=>15),
                            array('value'=>$v['organization'], 'width'=>35),
                            array('value'=>$v['result1'], 'width'=>20),
                            array('value'=>$v['result2'], 'width'=>20),
                        );
                    }
                    break;
                case 8:
                    $list['title'] = '闪光言行';
                    $list['head'] = $head8;
                    $data = $Model->query("SELECT  id, `name`, speech,DATE_FORMAT(time,'%Y-%m-%d') AS time,dy_lv FROM cxdj_dr_dy_sgyx ORDER BY `time` DESC LIMIT 1000");
                    foreach ($data as $k=>&$v){
                        $list['list'][] = array(
                            array('value'=>$v['id'], 'width'=>10),
                            array('value'=>$v['name'], 'width'=>10),
                            array('value'=>$v['speech'], 'width'=>50),
                            array('value'=>$v['time'], 'width'=>20),
                            array('value'=>$v['dy_lv'], 'width'=>10),

                        );
                    }

                    break;
                case 9:
                    $list['title'] = '评优评先数据';
                    $list['head'] = $head14;
                    $data = $Model->query("SELECT id,name,organization,honor FROM cxdj_dr_dzz_honor WHERE `status` = 1 LIMIT 1000");
                    foreach ($data as $k=>&$v){
                        $list['list'][] = array(
                            array('value'=>$v['id'], 'width'=>20),
                            array('value'=>$v['name'], 'width'=>20),
                            array('value'=>$v['organization'], 'width'=>40),
                            array('value'=>$v['honor'], 'width'=>20),

                        );
                    }
                    break;
            }

        }





        $this->Apireturn($list);

    }

    ////////////////////////////////////////服务预约大屏/////////////////////////////////////////////////////////////

    //服务预约列表页
    public function applylist($classif=null){
        $c= array(
            1=>'待认领',
            2=>'已认领',
            3=>'已完成',
            5=>'已完成',
        );
        $data = array();
        $map['status'] =1;
        $map['lat'] =array('exp','IS NOT NULL');
        $map['issue_id'] =82;
        $data['activity'] = M('issue_content')->where($map)->field('id,title,addr,time,content,lat,lng,host')->order('sort desc,id desc')->select();
        foreach ($data['activity'] as $k=>&$v){
            $v['time_var'] =date("Y-m-d H:i:s",$v['time']);
        }
//        dump($data);;exit;
        $map['issue_id'] =84;
        $data['volunteers'] = M('issue_content')->where($map)->field('id,title,addr,time,content,lat,lng')->order('sort desc,id desc')->select();
        foreach ($data['volunteers'] as $k=>&$v){
            $v['time_var'] =date("Y-m-d H:i:s",$v['time']);
        }
        $map['issue_id'] =86;
        $data['direct'] = M('issue_content')->where($map)->field('id,title,addr,content,lat,lng')->order('sort desc,id desc')->select();
        foreach ($data['direct'] as $k=>&$v){
            $v['time_var'] =date("Y-m-d H:i:s",$v['time']);
        }
        $map['issue_id'] =88;
        $data['lecture'] = M('issue_content')->where($map)->field('id,title,addr,time,content,lat,lng')->order('sort desc,id desc')->select();
        foreach ($data['lecture'] as $k=>&$v){
            $v['time_var'] =date("Y-m-d H:i:s",$v['time']);

        }
        $map2['status'] = 1;
        $map2['state'] = array('in','1,2,3,5');
        $data['wish'] = M('sign_wish')->where($map2)->field('id,title,content,obj,form,start_time,state')->order('sort desc,id desc')->select();
        foreach ($data['wish'] as $k=>&$v){
            $v['time_var'] =date("Y-m-d H:i:s",$v['start_time']);
            $v['state_var'] = $c[$v['state']];
        }
        $map3['status'] = 1;
        $map3['state'] = 1;
        $data['raise'] = M('sign_zhch')->where($map3)->field('')->order('sort desc,id desc')->select();
        if($classif){
            $this->Apireturn($data[$classif]);
        }
        $this->Apireturn($data);

    }

    //服务预约大屏 身份证所属党组织
    public function party_identity(){
        $identity = I('identity');
        if(!$identity){
            $this->Apireturn(array(),300,'错误的请求！！！！');
        }
        $map['IDCARD'] = $identity;
        $user = M('ajax_user')->where($map)->field('BRANCH_ID')->find();
        if(!$user){
            $this->Apireturn(array(),202,'非党员');
        }
        $data = M('ajax_dzz')->where(array('BRANCH_ID'=>$user['BRANCH_ID']))->field('NAME')->find();
        if($data){
            $this->Apireturn($data,200,'党员');
        }else{
            $this->Apireturn(array(),300,'服务器繁忙请稍后！！！！！');
        }
    }


}