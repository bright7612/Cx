<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/11
 * Time: 16:56
 */

namespace Home\Controller;


use Addons\Mail\MailAddon;
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
    //活动报名接口
    public function activity(){
        if($_REQUEST['openid']){$user = $this->identitys($_REQUEST['openid']);}else{$this->Apireturn(array(),300,'用户尚未登陆');}  //党员限定
        if($_REQUEST['content_id']){$data['content_id'] = $_REQUEST['content_id'];}else{$this->Apireturn(array(),300,'申请活动错误');}  //活动编号
        if($_REQUEST['name']){$data['name'] = $_REQUEST['name'];}else{$this->Apireturn(array(),300,'申请姓名错误');}  //申请姓名
        if($_REQUEST['phone']){$data['phone'] = $_REQUEST['phone'];}else{$this->Apireturn(array(),300,'申请电话错误');}  //申请姓名
        if($_REQUEST['party']){$data['organization'] = $_REQUEST['party'];}else{$this->Apireturn(array(),300,'申请党组织错误');}  //申请党组织
        if($_REQUEST['number']){$data['bespoke_num'] = $_REQUEST['number'];}else{$this->Apireturn(array(),300,'申请人数错误');}  //申请人数
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
        $data['content'] = $_REQUEST['item'];
        $id = M('sign_wish_apply')->add($data);
        if($id){
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
    //党员新的
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
                $this->Apireturn(array(),300,'对不清！您尚未进行实名认证！无法参与该活动');
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
        $map['PARENT_IDS'] =  array('like',"%,92,%");
        $map['TYPE'] = 0;
        $data['organization']['committee']['count'] = (int)M('ajax_dzz')->where($map)->count();  //党总支统计
        $data['organization']['committee']['Percentage'] = number_format( $data['organization']['committee']['count']/$data['organization']['count'],4)*100 ;
        //党总支部2
        $map['PARENT_IDS'] =  array('like',"%,92,%");
        $map['TYPE'] = 1;
        $data['organization']['general']['count'] = (int)M('ajax_dzz')->where($map)->count();  //党总支统计
        $data['organization']['general']['Percentage'] = number_format( $data['organization']['general']['count']/$data['organization']['count'],4)*100 ;
        //党支部
        $map2['PARENT_IDS'] = array('like',"%,92,%");
        $map2['TYPE'] = 2;
        $data['organization']['branch']['count'] = (int)M('ajax_dzz')->where($map2)->count();  //党支部统计
        $data['organization']['branch']['Percentage'] = number_format( $data['organization']['branch']['count']/$data['organization']['count'],4)*100 ;
        //联合党支部
        $map3['PARENT_IDS'] = array('like',"%,92,%");
        $map3['TYPE'] = 3;
        $data['organization']['union']['count'] = (int)M('ajax_dzz')->where($map3)->count();  //党委统计
        $data['organization']['union']['Percentage'] = number_format( $data['organization']['union']['count']/$data['organization']['count'],4)*100 ;
        //网络e支部
        $map3['PARENT_IDS'] = array('like',"%,92,%");
        $map3['TYPE'] = 4;
        $data['organization']['network']['count'] = (int)M('ajax_dzz')->where($map3)->count();  //党委统计
        $data['organization']['network']['Percentage'] = number_format( $data['organization']['network']['count']/$data['organization']['count'],4)*100 ;

        //党组织类别
        $data['type']['count'] =$data['organization']['count'] ;
        //部门机关
        $map4['PARENT_IDS'] = array('like',"%,92,%");
        $map4['NATURE'] = array('like',"%部门机关%");
        $data['type']['office']['count'] =(int) M('ajax_dzz')->where($map4)->count();
        $data['type']['office']['Percentage'] =number_format( $data['type']['office']['count']/$data['organization']['count'],4)*100 ;
        //国有企业
        $map5['PARENT_IDS'] = array('like',"%,92,%");
        $map5['NATURE'] = array('like',"%国有企业%");
        $data['type']['enterprise']['count'] = (int)M('ajax_dzz')->where($map5)->count();
        $data['type']['enterprise']['Percentage'] =number_format( $data['type']['enterprise']['count']/$data['organization']['count'],4)*100 ;
        //事业单位
        $map6['PARENT_IDS'] = array('like',"%,92,%");
        $map6['NATURE'] = array('like',"%事业单位%");
        $data['type']['undertaking']['count']=(int) M('ajax_dzz')->where($map6)->count();
        $data['type']['undertaking']['Percentage'] =number_format( $data['type']['undertaking']['count']/$data['organization']['count'],4)*100 ;
        //村社
        $map7['PARENT_IDS'] = array('like',"%,92,%");
        $map7['NATURE'] = array('like',"%村社%");
        $data['type']['community']['count']=(int) M('ajax_dzz')->where($map7)->count();
        $data['type']['community']['Percentage'] =number_format( $data['type']['community']['count']/$data['organization']['count'],4)*100 ;
        //两新组织
        $map8['PARENT_IDS'] = array('like',"%,92,%");
        $map8['NATURE'] = array('like',"%两新%");
        $data['type']['new']['count']=(int) M('ajax_dzz')->where($map8)->count();
        $data['type']['new']['Percentage'] =number_format( $data['type']['new']['count']/$data['organization']['count'],4)*100 ;
        $da['dzz'] = $data;
        unset($data);unset($map);unset($map2);unset($map3);unset($map4);unset($map5);unset($map6);
        $this->Apireturn($da);

    }
    //数据大屏左上角 党组织
    public function details($type=1,$subtype=2,$classify=1,$page = 1, $r = 200){
        $head1 = array(  //党组织
            array('name'=>'党组织名称', 'width'=>35),
            array('name'=>'党组织书记', 'width'=>15),
            array('name'=>'党组织地址', 'width'=>35),
            array('name'=>'人数', 'width'=>15),
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
                    }elseif ($classify == 2) {
                        $map3['TYPE'] = 1;
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '党总支信息';
                    }elseif ($classify == 3) {
                        $map3['TYPE'] = 2;
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '党支部信息';
                    }elseif ($classify == 4) {
                        $map3['TYPE'] = 3;
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '联合党支部信息';
                    }elseif ($classify == 5) {
                        $map3['TYPE'] = 4;
                        $data = M('ajax_dzz')->where($map3)->page($page, $r)->select();
                        $list['count'] = ceil(M('ajax_dzz')->where($map3)->count()/$r);
                        $list['title'] = '网络e支部';
                    }else{
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
                foreach ($data as $k=>$v) {
                    $da[$k] = array(
                        array('value'=>$v['NAME'], 'width'=>35,'ID'=>$v['BRANCH_ID'],'type'=>'partyMember'),
                        array('value'=>$v['SECRETARY'], 'width'=>15,'ID'=>$v['BRANCH_ID'],'type'=>'partyMember'),
                        array('value'=>$v['ADDRESS'], 'width'=>35,'ID'=>$v['BRANCH_ID'],'type'=>'partyMember'),
                        array('value'=>$dzzUser[$v['BRANCH_ID']]?$dzzUser[$v['BRANCH_ID']]:'暂无数据', 'width'=>15,'ID'=>$v['BRANCH_ID'],'type'=>'partyMember'),
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
        $map['PARENT_IDS'] = array('like',"%,".$id.",%");
        $a = M('ajax_dzz')->where($map)->field('BRANCH_ID')->select();
        foreach ($a as $k2=>&$v2){
            $b[] = $v2['BRANCH_ID'] ;
        }
        $b = implode(',',$b).$id;
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
        $list['title'] = '党员信息';
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
            $b = implode(',',$b).$v['BRANCH_ID'];

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


    ////////////////////////////////////////服务预约大屏/////////////////////////////////////////////////////////////

    //服务预约列表页
    public function applylist($classif=null){
        $c= array(
            1=>'待人领',
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


}