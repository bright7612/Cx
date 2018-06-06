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
}