<?php
namespace Cx\Controller;
use Think\Controller;
// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

class CxController extends Controller
{
    public function index()
    {
      $this->display('index');
    }

    //地图数据接口
    public function mapData()
    {
        $Model = M('issue_content');
        $id = I('category_id');
        $where['issue_id'] = $id;
        $where['status'] = 1;
        $result = $Model->where($where)->field('id,time,title,host,lat,lng,teacher,content,addr')->select();


        foreach ($result as $k=>&$v){
            $v['content'] =   preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",strip_tags($v['content'],""));  //过滤标签和空格
            $v['time'] = date('Y-m-d H:i');
        }

        if($result){
            echo json_encode(array('status'=>1,'msg'=>'请求成功','data'=>$result));
        }else{
            echo json_encode(array('status'=>0,'msg'=>'请求失败'));
        }

    }

    public function activityList()
    {

        $id = I('category_id');
        $result = D('Cx')->mapCommon($id);
        foreach ($result as $k=>&$v){
            $v['content'] =   preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",strip_tags($v['content'],""));  //过滤标签和空格
            $v['time'] = date('Y-m-d H:i');
         }
        $this->assign('mapList',$result[0]);
        $this->display('activity_order');

    }

    public function classList()
    {

        $id = I('category_id');
        $result = D('Cx')->mapCommon($id);
        foreach ($result as $k=>&$v){
            $v['content'] =   preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",strip_tags($v['content'],""));  //过滤标签和空格
            $v['time'] = date('Y-m-d H:i');
        }
        $this->assign('classList',$result[0]);
        $this->display('theme_party');

    }

    //地图详情展示
    public function mapDetail()
    {
        $Model = M('issue_content');
        $id = I('id');
        $where['id'] = $id;
        $where['status'] = 1;

        $res = $Model->where($where)->field('id,issue_id,time,title,host,teacher,num,content')->find();

        $res['content'] = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",strip_tags($res['content'],""));

        $this->assign('detail',$res);
        $this->display();
    }


    //预约页面
    public function bespoke_index()
    {
        $id = I('dataId');
        $res = D('Cx')->mapCommon($id);

        $y_num = $res[0]['y_num']; //已经预约数

        if($y_num == ''){
            $k_num = $res[0]['num'];
        }else{
            $k_num = $res[0]['k_num']; //剩余可预约数
        }



        $this->assign('k_num',$k_num);
        $this->display('order');
    }

    //预约接口
    public function bespoke()
    {
        $Model = M('sign_bespoke');
        $id = I('id');
        $data['content_id'] = $id;
        $data['phone'] = I('phone');
        $data['name'] = I('name');
        $data['organization'] = I('organization');
        $data['company'] = I('company');
        $data['bespoke_num'] = I('bespoke_num');
        $data['types'] = I('type');
        $data['source'] = 2;   //2代表大屏预约
        $data['identity'] = I('ID_card');
        $data['text'] = I('text');
        $data['cre_time'] = date('Y-m-d H:i:s',time());
        $record = $Model->add($data);

        if($record){
            echo json_encode(array('status'=>1,'msg'=>'预约成功'));
        }else{
            echo json_encode(array('status'=>0,'msg'=>'预约失败'));
        }
    }

    //服务预约系统查询
    public function search()
    {
        $Model = M('issue_content');
        $title = I('title');
        $where['title'] = array('like',"%$title%");
        $where['status'] = 1;
        $res = $Model->where($where)->field('id,title,addr,host,time,content')->select();

        foreach ($res as $k=>&$v){
            $v['content'] = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",strip_tags($res['content'],"")); // 过滤标签和空格
        }

        $this->display();
    }


    public function wxInfo()
    {
        $Model = M('qd','ljz_','DB_M');
        $res = $Model->field('id,headimgurl')->limit(0,50)->select();
        echo json_encode(array('status'=>1,'msg'=>'请求成功','data'=>$res));
    }

    public function wxUser()
    {
        $Model = M('qd','ljz_','DB_M');
        $id = I('id');
        $where['id'] = $id;
        $res = $Model->where($where)->field('id,headimgurl')->find();

        echo json_encode(array('status'=>1,'msg'=>'请求成功','data'=>$res));
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
        $data['volunteers'] = M('issue_content')->where($map)->field('id,title,addr,time,content,lat,lng,host')->order('sort desc,id desc')->select();
        foreach ($data['volunteers'] as $k=>&$v){
            $v['time_var'] =date("Y-m-d H:i:s",$v['time']);
        }
        $map['issue_id'] =86;
        $data['direct'] = M('issue_content')->where($map)->field('id,title,addr,content,lat,lng,telphone,num')->order('sort desc,id desc')->select();
        foreach ($data['direct'] as $k=>&$v){
            $v['time_var'] =date("Y-m-d H:i:s",$v['time']);
        }
        $map['issue_id'] =88;
        $data['lecture'] = M('issue_content')->where($map)->field('id,title,addr,time,content,lat,lng,teacher,host')->order('sort desc,id desc')->select();
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

        //
        $map3['status'] = 1;
        $map3['state'] = 1;
        $data['raise'] = M('sign_zhch')->where($map3)->field('id,title,content,img')->order('sort desc,id desc')->select();
        foreach ($data['raise'] as $k=>&$v){
            $item = get_cover($v['img']);
            $v['path'] = 'http://183.131.86.64:8620'.$item['path'];
        }

        $map4['status'] = 1;
        $map4['state'] = array('eq','1');
        $data['ztc'] = M('sign_direct')->where($map4)->field('id,title,desc,img')->order('sort desc,id desc')->select();

        foreach ($data['ztc'] as $k=>&$v){
           $item = get_cover($v['img']);
           $v['path'] = 'http://183.131.86.64:8620'.$item['path'];
        }



        if($classif){
            $this->Apireturn($data[$classif]);
        }
        $this->Apireturn($data);

    }

    public function wxyDetail()
    {
        $id = I('id');
        $Model = M();
        $detail = $Model->query("SELECT
                                        wish.img,
                                        wish.telephone,
                                        username,
                                        `title`,
                                        wish.`content`,
                                        `obj`,
                                        `form`,
                                        `start_time`,
                                        wish.`state`,
                                        `name`,
                                        apply.telephone AS phone
                                    FROM
                                        `cxdj_sign_wish` AS wish JOIN cxdj_sign_wish_apply AS apply ON wish.id = apply.wish_id
                                    WHERE
                                        wish.`status` = 1
                                    AND wish.`state` IN ('1', '2', '3', '5')
                                    AND  wish.id = $id
                                    ORDER BY
                                        sort DESC,
                                        wish.id DESC");


        if($detail[0]['obj'] == 1){
            $detail[0]['obj'] = '个人';
        }elseif ($detail[0]['obj'] == 2){
            $detail[0]['obj'] = '团体';
        }elseif (   $detail[0]['obj'] == '3'){
            $detail[0]['obj'] ='群众';
        }

        if($detail[0]['form'] == 1){
            $detail[0]['form'] = '物质';
        }elseif($detail[0]['form'] == 2){
            $detail[0]['form'] = '精神';
        }

        //心愿状态  0未审核  1审核通过  -1审核失败  2已认领 3已完成 4用户取消申请 5已评价
        if($detail[0]['state'] == 0){
            $detail[0]['state'] = '未审核';
        }elseif ($detail[0]['state'] == 1 ){
            $detail[0]['state'] = '审核通过';
        }elseif ($detail[0]['state'] == -1){
            $detail[0]['state'] = '审核失败';
        }elseif ( $detail[0]['state'] == 2){
            $detail[0]['state'] = '已认领';
        }elseif ($detail[0]['state'] == 3){
            $detail[0]['state'] = '已完成';
        }elseif ( $detail[0]['state'] == 4){
            $detail[0]['state'] = '用户取消申请';
        }elseif ( $detail[0]['state'] == 5){
            $detail[0]['state'] = '已评价';
        }

        $item = get_cover($detail[0]['img']);
        $detail[0]['path'] = 'http://183.131.86.64:8620'. $item['path'];
        $detail[0]['start_time'] = date('Y-m-d',$detail[0]['start_time']);


        $this->assign('detail',$detail[0]);
        $this->display('heart_detail');
    }


    //场地预约页面
    public function venueIndex()
    {
        $this->display('place_order');
    }


    //场地预约
    public function venue()
    {
        $data = $_POST;
        $data['content_id'] = $data['id'];
        $data['source'] = 2;
        $data['organization'] = $data['organization'];
        $data['phone'] = $data['phone'];
        $data['text'] = $data['text'];
        $data['bespoke_num'] = $data['num'];
        $data['start_time'] = $data['start_time'];
        $data['end_time'] = $data['end_time'];
        $data['identity'] = $data['identity'];
        $data['facility'] = $data['facility'];

        $res = M('sign_field')->add($data);

        if($res){
            echo json_encode(array('code'=>200,'msg'=>'预约成功'));
        }else{
            echo json_encode(array('code'=>404,'msg'=>'预约失败'));
        }

    }

    //志愿活动预约详情
    public function volunteer_activity()
    {
        $id = I('id');
        $map['id'] = $id;
        $data = M('issue_content')->where($map)->field('id,title,addr,time,content,lat,lng,host')->order('sort desc,id desc')->select();
        foreach ($data as $k=>&$v){
            $v['time_var'] =date("Y-m-d H:i:s",$v['time']);
        }

        $this->assign('list',$data[0]);
        $this->display('volunteer');
    }

    //众筹服务
    public function zhongchou()
    {
        $id = I('id');
        $this->display('crowd_funding');
    }




    /****************************************************智能导览************************************************/

    public function service_center()
    {
        $this->display('detail');
    }

    public function zd_fw()
    {
        $this->display('position_list');
    }

    public function organization()
    {
        $this->display('organize_bulid');
    }

    public function brand()
    {
        $this->display('excellent_party');
    }











}