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
        $this->display('index');
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

       if($res){
           echo json_encode(array('status'=>1,'msg'=>'请求成功','data'=>array('list'=>$res)));
       }else{
           echo json_encode(array('status'=>-1,'msg'=>'请求失败'));
       }


   }
}