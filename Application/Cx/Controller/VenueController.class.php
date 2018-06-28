<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/12
 * Time: 18:39
 */
namespace Cx\Controller;
use  Think\Controller;
class VenueController extends Controller
{
    public function index()
    {
        $res = M("dp_team")->where()->field('name,num,cre_time')->order('cre_time desc')->select();
//        echo M()->getlastsql();exit;
        foreach ($res as $k=>$v){
            $res[$k]['cre_time'] = msubstr((string)$v['cre_time'],0,10, "utf-8",  0);
        }
        $this->assign("list",$res);

        $map1['cre_time'] =  array('between',array('2018-'.date("m").'-01 00:00:00','2018-'.date("m").'-30 00:00:00'));

        $map2['cre_time'] =  array('between',array(date("Y").'-01-01 00:00:00',date("Y").'-12-31 00:00:00'));

        $count1 = M("dp_team")->where($map1)->sum('num');

        $count2 = M("dp_team")->where($map2)->sum('num');

// echo M()->getlastsql();exit;
        $this->assign("count1",$count1);
        $this->assign("count2",$count2);
//        dump($res);exit;
        $this->display('index');
    }
    public function lists($date="")
    {
        if($date){
            $map['cre_time'] =  array('between',array($date.' 00:00:00',$date.' 23:59:59'));
        }

        $res = M("dp_team")->where($map)->field('name,num,cre_time')->order('cre_time desc')->select();
        foreach ($res as $k=>$v){
            $res[$k]['cre_time'] = msubstr((string)$v['cre_time'],0,10, "utf-8",  0);
        }
        $this->assign("list",$res);

        $this->display();
    }
    //场馆数据展示
   public function ajax_view()
   {
       $Model = M('dp_team');
       $date = I('date');

       if($date){
           $where['date'] = $date;
       }

       $res = $Model->where($where)->field('name,num,date')->order('date DESC')->select();
       foreach ($res as $k=>$v){
           $res[$k]['cre_time'] = msubstr((string)$v['cre_time'],0,10, "utf-8",  0);
       }
       if($res){
           echo json_encode(array('status'=>1,'msg'=>'请求成功','data'=>array('list'=>$res)));
       }else{
           echo json_encode(array('status'=>-1,'msg'=>'请求失败'));
       }


   }
}