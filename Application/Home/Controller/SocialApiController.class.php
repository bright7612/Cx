<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/16
 * Time: 17:53
 */

namespace Home\Controller;


use Think\Controller;
// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

class SocialApiController extends Controller
{
    //社会组织地图接口
    public function map(){
        $table = M('dr_social');
        $map['status'] = array('egt',1);
        $map['display'] = array('egt',1);
        $map['lat'] = array('exp','IS NOT NULL');
        $data = $table->where($map)->select(); //->field('address,lat,lng')->group('address')
        foreach ($data as  $k=>$v){
            $a[$v['address']][] = array('organization'=>$v['organization'],'member'=>$v['member'],'personnel'=>$v['personnel'],'classify'=>$v['classify'],'party'=>$v['party'],'party_name'=>$v['party_name'],'party_int'=>$v['party_int']);
        }
        $da = $table->where($map)->field('address,lat,lng')->group('address')->select();
        foreach ($da as $k=>&$v){
            $v['content'] = $a[$v['address']];
        }
        if(!$da||!$data){
            $this->Apireturn(array(),300,'暂无数据');
        }
        $this->Apireturn($da);


    }
    //社会组织 左上角
    public function SocialOrganization(){
        $map['status'] = 1;
        $da = M('dr_social')->where($map)->field('classify')->select();
        $social = 0;//社会组织总数
        $tuanti = 0; //类型团体
        $jijinhui = 0; //类型基金会
        $minfei = 0; //类型民非
        $qita = 0; //类型其他
        foreach ($da as $k=>$v){
            $social ++;
            if($v['classify'] == '社团'){
                $tuanti++;
            }elseif ($v['classify'] == '基金会'){
                $jijinhui++;
            }elseif ($v['classify'] == '民非'){
                $minfei++;
            }elseif ($v['classify'] == '其他'){
                $qita++;
            }
        }
        $social_party = M('dr_social_party')->where($map)->count('id');  //建立党组织数

        $data['social_organization']['social'] = (int)589;  //社会组织总数
        $data['social_organization']['party'] = (int)$social_party;  //建立党组织数
        $data['social_organization']['employment'] = (int)395;  //从业人员总数
        $data['social_organization']['party_member'] = (int)160;  //从业人员总数

        $map['classify'] = 1;
        $assistant = M('dr_social_worker')->where($map)->count('id'); //助理社会工作师
        $map['classify'] = 2;
        $social_worker = M('dr_social_worker')->where($map)->count('id'); //社会工作师

        $data['social_worker']['assistant_worker'] = (int)$assistant; //助理社会工作师
        $data['social_worker']['social_worker'] = (int)$social_worker; //社会工作师
        $data['social_worker']['senior_worker'] = (int)20; //高级社会工作师

        $data['social'] = array($tuanti,$jijinhui,$minfei,$qita);
        //社工学历
        $map1['status'] = 1;
        $map1['education'] = '高中';
        $gaozhong = M('dr_social_worker')->where($map1)->count('id');
        $map1['education'] = '大专';
        $dazhuan = M('dr_social_worker')->where($map1)->count('id');
        $map1['education'] = '本科';
        $benke = M('dr_social_worker')->where($map1)->count('id');
        $map1['education'] = '研究生';
        $yanjiusheng = M('dr_social_worker')->where($map1)->count('id');
        $data['education'] = array($gaozhong,$dazhuan,$benke,$yanjiusheng);

        $this->Apireturn($data);



    }
    //社会组织 左上角 列表
    public function socialList($classify = null,$page=1,$r=500){
        if(empty($classify)){
            $this->Apireturn(array(),300,'错误请求');
        }
        //社会组织
        $head = array(
            array('name'=>'序号', 'width'=>10),
            array('name'=>'社会组织名称', 'width'=>30),
            array('name'=>'会员人数', 'width'=>10),
            array('name'=>'从业人员', 'width'=>10),
            array('name'=>'地址', 'width'=>30),
            array('name'=>'类别', 'width'=>10),
        );
        //建立党组织数
        $head1 = array(
            array('name'=>'序号', 'width'=>10),
            array('name'=>'党组织名称', 'width'=>70),
            array('name'=>'党员人数', 'width'=>20),
        );
        //助理社会工作师 / 社会工作师
        $head2 = array(
            array('name'=>'姓名', 'width'=>20),
            array('name'=>'性别', 'width'=>10),
            array('name'=>'政治面貌', 'width'=>15),
            array('name'=>'学历', 'width'=>15),
            array('name'=>'工作单位及职务', 'width'=>40),
        );

        switch ($classify){
            //社会组织总数
            case 'social':
                $map['status'] = 1;
                $data = M('dr_social')->where($map)->select();
                $i = 1;
                foreach ($data as $k=>$v) {
                    $ii = $i++;
                    $da[$k] = array(
                        array('value'=>$ii, 'width'=>10),
                        array('value'=>$v['organization'], 'width'=>30),
                        array('value'=>$v['member'], 'width'=>10),
                        array('value'=>$v['personnel'], 'width'=>10),
                        array('value'=>$v['address'], 'width'=>30),
                        array('value'=>$v['classify'], 'width'=>10),
                    );
                }

                $list['title'] = '社会组织';
                $list['head'] = $head;
                $list['list'] = $da;

                break;
            //建立党组织数
            case 'party':
                $map['status'] = 1;
                $data = M('dr_social_party')->where($map)->select();  //建立党组织数
                foreach ($data as $k=>$v) {
                    $da[$k] = array(
                        array('value'=>$v['did'], 'width'=>10),
                        array('value'=>$v['organization'], 'width'=>70),
                        array('value'=>$v['member'], 'width'=>10),
                    );
                }
                $list['title'] = '建立党组织';
                $list['head'] = $head1;
                $list['list'] = $da;
                break;
            //助理社会工作师
            case 'assistant_worker':
                $map['status'] = array('egt',1);
                $map['classify'] = 1;
                $data = M('dr_social_worker')->where($map)->select();
                foreach ($data as $k=>$v) {
                    $da[$k] = array(
                        array('value'=>$v['name'], 'width'=>20),
                        array('value'=>$v['sex'], 'width'=>10),
                        array('value'=>$v['political'], 'width'=>15),
                        array('value'=>$v['education'], 'width'=>15),
                        array('value'=>$v['work'], 'width'=>40),
                    );
                }
                $list['title'] = '助理社会工作师';
                $list['head'] = $head2;
                $list['list'] = $da;
                break;
            //社会工作师
            case 'social_worker':
                $map['status'] = array('egt',1);
                $map['classify'] = 2;
                $data = M('dr_social_worker')->where($map)->select();
                foreach ($data as $k=>$v) {
                    $da[$k] = array(
                        array('value'=>$v['name'], 'width'=>20),
                        array('value'=>$v['sex'], 'width'=>10),
                        array('value'=>$v['political'], 'width'=>15),
                        array('value'=>$v['education'], 'width'=>15),
                        array('value'=>$v['work'], 'width'=>40),
                    );
                }
                $list['title'] = '社会工作师';
                $list['head'] = $head2;
                $list['list'] = $da;
                break;

            //社会组织类别
            case 'shetuan':
                $map['status'] = 1;
                $map['classify'] = '社团';
                $data = M('dr_social')->where($map)->select();
                $i = 1;
                foreach ($data as $k=>$v) {
                    $ii = $i++;
                    $da[$k] = array(
                        array('value'=>$ii, 'width'=>10),
                        array('value'=>$v['organization'], 'width'=>30),
                        array('value'=>$v['member'], 'width'=>10),
                        array('value'=>$v['personnel'], 'width'=>10),
                        array('value'=>$v['address'], 'width'=>30),
                        array('value'=>$v['classify'], 'width'=>10),
                    );
                }
                $list['title'] = '社团';
                $list['head'] = $head;
                $list['list'] = $da;
                break;
            case 'jijinhui':
                $map['status'] = 1;
                $map['classify'] = '基金会';
                $data = M('dr_social')->where($map)->select();
                foreach ($data as $k=>$v) {
                    $da[$k] = array(
                        array('value'=>$v['organization'], 'width'=>20),
                        array('value'=>$v['member'], 'width'=>10),
                        array('value'=>$v['personnel'], 'width'=>20),
                        array('value'=>$v['address'], 'width'=>40),
                        array('value'=>$v['classify'], 'width'=>10),
                    );
                }
                $list['title'] = '基金会';
                $list['head'] = $head;
                $list['list'] = $da;
                break;
            case 'minfei':
                $map['status'] = 1;
                $map['classify'] = '民非';
                $data = M('dr_social')->where($map)->select();
                $i = 1;
                foreach ($data as $k=>$v) {
                    $ii = $i++;
                    $da[$k] = array(
                        array('value'=>$ii, 'width'=>10),
                        array('value'=>$v['organization'], 'width'=>30),
                        array('value'=>$v['member'], 'width'=>10),
                        array('value'=>$v['personnel'], 'width'=>10),
                        array('value'=>$v['address'], 'width'=>30),
                        array('value'=>$v['classify'], 'width'=>10),
                    );
                }
                $list['title'] = '民非';
                $list['head'] = $head;
                $list['list'] = $da;
                break;
            case 'qita':
                $map['status'] = 1;
                $map['classify'] = '其他';
                $data = M('dr_social')->where($map)->select();
                foreach ($data as $k=>$v) {
                    $da[$k] = array(
                        array('value'=>$v['organization'], 'width'=>20),
                        array('value'=>$v['member'], 'width'=>10),
                        array('value'=>$v['personnel'], 'width'=>20),
                        array('value'=>$v['address'], 'width'=>40),
                        array('value'=>$v['classify'], 'width'=>10),
                    );
                }
                $list['title'] = '其他';
                $list['head'] = $head;
                $list['list'] = $da;
                break;

                //社工学历分布
            case 'gaozhong':
                $map['status'] = 1;
                $map['education'] = '高中';
                $data = M('dr_social_worker')->where($map)->select();
                foreach ($data as $k=>$v) {
                    $da[$k] = array(
                        array('value'=>$v['name'], 'width'=>20),
                        array('value'=>$v['sex'], 'width'=>10),
                        array('value'=>$v['political'], 'width'=>15),
                        array('value'=>$v['education'], 'width'=>15),
                        array('value'=>$v['work'], 'width'=>40),
                    );
                }
                $list['title'] = '高中学历社工';
                $list['head'] = $head2;
                $list['list'] = $da;
                break;
            case 'dazhuan':
                $map['status'] = 1;
                $map['education'] = '大专';
                $data = M('dr_social_worker')->where($map)->select();
                foreach ($data as $k=>$v) {
                    $da[$k] = array(
                        array('value'=>$v['name'], 'width'=>20),
                        array('value'=>$v['sex'], 'width'=>10),
                        array('value'=>$v['political'], 'width'=>15),
                        array('value'=>$v['education'], 'width'=>15),
                        array('value'=>$v['work'], 'width'=>40),
                    );
                }
                $list['title'] = '大专学历社工';
                $list['head'] = $head2;
                $list['list'] = $da;
                break;
            case 'benke':
                $map['status'] = 1;
                $map['education'] = '本科';
                $data = M('dr_social_worker')->where($map)->select();
                foreach ($data as $k=>$v) {
                    $da[$k] = array(
                        array('value'=>$v['name'], 'width'=>20),
                        array('value'=>$v['sex'], 'width'=>10),
                        array('value'=>$v['political'], 'width'=>15),
                        array('value'=>$v['education'], 'width'=>15),
                        array('value'=>$v['work'], 'width'=>40),
                    );
                }
                $list['title'] = '本科学历社工';
                $list['head'] = $head2;
                $list['list'] = $da;
                break;
            case 'yanjiusheng':
                $map['status'] = 1;
                $map['education'] = '研究生';
                $data = M('dr_social_worker')->where($map)->select();
                foreach ($data as $k=>$v) {
                    $da[$k] = array(
                        array('value'=>$v['name'], 'width'=>20),
                        array('value'=>$v['sex'], 'width'=>10),
                        array('value'=>$v['political'], 'width'=>15),
                        array('value'=>$v['education'], 'width'=>15),
                        array('value'=>$v['work'], 'width'=>40),
                    );
                }
                $list['title'] = '研究生学历社工';
                $list['head'] = $head2;
                $list['list'] = $da;
                break;
            default:
                $this->Apireturn(array(),300,'错误请求');
                break;
        }
        $this->Apireturn($list);

    }
    //社会组织 右上角 活动列表
    public function social_activity($classify = null,$page=1,$r=200){
        $table = M('dr_social_activity');
        $map['status'] = array('egt',1);
        if($classify=='list'){
            $head = array(
                array('name'=>'序号', 'width'=>10),
                array('name'=>'社会组织名称', 'width'=>30),
                array('name'=>'活动时间', 'width'=>20),
                array('name'=>'活动主题', 'width'=>30),
                array('name'=>'活动等级', 'width'=>10),
            );
            $d = $table->where($map)->order('time desc')->page($page,$r)->field("did,organization,FROM_UNIXTIME(time,'%Y年%m月%d日') as time,title,content,classify")->select();
            $i = 1;
            foreach ($d as $k=>$v) {
                $ii = $i++;
                $da[$k] = array(
                    array('value'=>$ii, 'width'=>10,'content'=>$v['content'],'title'=>$v['title']),
                    array('value'=>$v['organization'], 'width'=>30,'content'=>$v['content'],'title'=>$v['title']),
                    array('value'=>$v['time'], 'width'=>20,'content'=>$v['content'],'title'=>$v['title']),
                    array('value'=>$v['title'], 'width'=>30,'content'=>$v['content'],'title'=>$v['title']),
                    array('value'=>$v['classify'], 'width'=>10,'content'=>$v['content'],'title'=>$v['title']),
                );
            }
            $data['list'] = $da;
            $data['head'] = $head;
            $data['title'] = '社会组织活动';
        }else if(empty($classify)){
            $data = $table->where($map)->order('time desc')->limit(9)->field("did,organization,FROM_UNIXTIME(time,'%Y年%m月%d日') as time,title,content,classify")->select();
        }else{
            $this->Apireturn(array(),300,'错误请求');
        }

        $this->Apireturn($data);
        exit;

    }
    //社会组织互动排名
    public function activity_ranking($classify = null,$page=1,$r=200){
        if($classify=='list'){
            $head = array(
                array('name'=>'序号', 'width'=>10),
                array('name'=>'社会组织名称', 'width'=>70),
                array('name'=>'活动数量', 'width'=>20),
            );
            $table = M('dr_social_activity');
            $map['status'] = array('egt',1);
            $d = $table->where($map)->group('organization')->field("count(id) as count,organization,did,id")->order('count desc')->page($page,$r)->select();
            $i = 1;
            foreach ($d as $k=>$v) {
                $ii = $i++;
                $da[$k] = array(
                    array('value'=>$ii, 'width'=>10,'organization'=>$v['organization']),
                    array('value'=>$v['organization'], 'width'=>70,'organization'=>$v['organization']),
                    array('value'=>$v['count'], 'width'=>20,'organization'=>$v['organization']),
                );
            }
            $data['list'] = $da;
            $data['head'] = $head;
            $data['title'] = '社会组织活动数排名';

        }elseif (empty($classify)){
            $table = M('dr_social_activity');
            $map['status'] = array('egt',1);
            $data = $table->where($map)->group('organization')->field("count(id) as count,organization,did,id")->order('count desc')->limit(5)->select();
        }else{
            $this->Apireturn(array(),300,'错误请求');
        }
        $this->Apireturn($data);

    }
    //社会组织活动列表
    public function social_activity_list($organization =null,$page=1,$r=200){
        if(empty($organization)){
            $this->Apireturn(array(),300,'错误请求');
        }
        $table = M('dr_social_activity');
        $map['status'] = array('egt',1);
        $map['organization'] = $organization;
        $d =  $table->where($map)->page($page,$r)->field("did,organization,FROM_UNIXTIME(time,'%Y年%m月%d日') as time,title,content,classify")->select();
        $head = array(
            array('name'=>'序号', 'width'=>10),
            array('name'=>'社会组织名称', 'width'=>30),
            array('name'=>'活动时间', 'width'=>20),
            array('name'=>'活动主题', 'width'=>30),
            array('name'=>'活动等级', 'width'=>10),
        );
        $i = 1;
        foreach ($d as $k=>$v) {
            $ii = $i++;
            $da[$k] = array(
                array('value'=>$ii, 'width'=>10,'content'=>$v['content']),
                array('value'=>$v['organization'], 'width'=>30,'content'=>$v['content']),
                array('value'=>$v['time'], 'width'=>20,'content'=>$v['content']),
                array('value'=>$v['title'], 'width'=>30,'content'=>$v['content']),
                array('value'=>$v['classify'], 'width'=>10,'content'=>$v['content']),
            );
        }
        if($da){
            $data['list'] = $da;
        }else{
            $data['list'] = array();
        }

        $data['head'] = $head;
        $data['title'] = '社会组织活动';

        $this->Apireturn($data);

    }
    //社会组织社工活跃指数
    public function social_active($classify = null,$page=1,$r=200){
        $map['status'] =1;
        if($classify == 'list'){
            $head = array(
                array('name'=>'序号', 'width'=>10),
                array('name'=>'姓名', 'width'=>45),
                array('name'=>'活跃指数', 'width'=>45),
            );
            $data = M('dr_social_active')->where($map)->group('name')->field('count(id) as count ,name,did')->order('count desc')->page($page,$r)->select();
            foreach ($data as $k=>$v) {
                $da[$k] = array(
                    array('value'=>$v['did'], 'width'=>10,'name'=>$v['name']),
                    array('value'=>$v['name'], 'width'=>45,'name'=>$v['name']),
                    array('value'=>$v['count'], 'width'=>45,'name'=>$v['name']),
                );
            }
            $list['list'] = $da;
            $list['head'] = $head;
            $list['title'] = '社工活跃指数排名';
            $this->Apireturn($list);
        }
        elseif (empty($classify)){
            $data = M('dr_social_active')->where($map)->group('name')->field('count(id) as count ,name')->order('count desc')->limit(2)->select();
            $this->Apireturn($data);
        }
        else{
            $this->Apireturn(array(),300,'错误请求');
        }

    }
    //社会组织社工活跃指数详情
    public function social_active_list($name=null,$page=1,$r=200){
        $head = array(

            array('name'=>'序号', 'width'=>10),
            array('name'=>'姓名', 'width'=>20),
            array('name'=>'时间', 'width'=>20),
            array('name'=>'活动名称', 'width'=>50),

        );
        if($name){
            $map['status'] = 1;
            $map['name'] = $name;
            $data = M('dr_social_active')->where($map)->page($page,$r)->select();
            foreach ($data as $k=>$v) {
                $da[$k] = array(
                    array('value'=>$v['did'], 'width'=>10),
                    array('value'=>$v['name'], 'width'=>20),
                    array('value'=>$v['time'], 'width'=>20),
                    array('value'=>$v['title'], 'width'=>50),
                );
            }
            $list['list'] = $da;
            $list['head'] = $head;
            $list['title'] = $name.'所参与活动';
            $this->Apireturn($list);
        }else{
            $this->Apireturn(array(),300,'错误请求');
        }

    }
    //社会组织服务信息
    public function social_service(){
        $Y = date('Y').'年';
        $map['status'] = 1;
        $map['classify'] = 1;
        $map['time'] = array('like',"%".$Y."%");
        //扶贫
        $data['poverty'] = M('dr_social_service')->where($map)->group('time')->field('sum(num) as count,time')->order('time asc')->select();
        $map['classify'] = 2;
        //关爱老人
        $data['care'] = M('dr_social_service')->where($map)->group('time')->field('sum(num) as count,time')->order('time asc')->select();
        $this->Apireturn($data);

    }
    //社会组织服务信息详情
    public function social_service_list($classify = null){
        $head = array(
            array('name'=>'序号', 'width'=>10),
            array('name'=>'社会组织', 'width'=>40),
            array('name'=>'时间', 'width'=>30),
            array('name'=>'参与次数', 'width'=>20),

        );
        $Y = date('Y').'年';
        $map['status'] = 1;
        $map['time'] = array('like',"%".$Y."%");
        if($classify == 'poverty'){
            $map['classify'] = 1;
            $title = '扶弱济贫';
        }elseif($classify == 'care'){
            $map['classify'] = 2;
            $title = '关爱老人';
        }else{
            $this->Apireturn(array(),300,'错误请求');
        }
        $data = M('dr_social_service')->where($map)->group('organization')->field('organization')->select();
        foreach ($data as $k=>$v){
            $map['organization'] = $v['organization'];
            $das = M('dr_social_service')->where($map)->order('time asc')->select();
            foreach ($das as $kk=>$vv) {
                $da[] = array(
                    array('value'=>$vv['did'], 'width'=>10),
                    array('value'=>$vv['organization'], 'width'=>40),
                    array('value'=>$vv['time'], 'width'=>30),
                    array('value'=>$vv['num'], 'width'=>20),
                );
            }
        }
        $list['list'] = $da;
        $list['head'] = $head;
        $list['title'] = $title;
        $this->Apireturn($list);
    }


    //接口总出口
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

}