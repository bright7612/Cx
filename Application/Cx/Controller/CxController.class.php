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

        $id = I('id');


        $result = D('Cx')->mapCommon($id);
        foreach ($result as $k=>&$v){
            $v['content'] =   preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",strip_tags($v['content'],""));  //过滤标签和空格
            $v['time'] = date('Y-m-d H:i',$v['time']);
            $v['ewm'] = "http://36.26.83.105:8620/cx/cx/qrcodefor/id/".$v['id'].'?type=1';
         }

        $this->assign('mapList',$result[0]);
        $this->display('activity');

    }


    //党课预约
    public function classList()
    {

        $id = I('category_id');
        $result = D('Cx')->mapCommon($id);
        foreach ($result as $k=>&$v){
            $v['content'] =   preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",strip_tags($v['content'],""));  //过滤标签和空格
            $v['time'] = date('Y-m-d H:i',$v['time']);
            $v['ewm'] = "http://36.26.83.105:8620/cx/cx/qrcodefor/id/".$v['id'].'?type=4';
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

        $where['id'] = $id;
        $title = M('issue_content')->where($where)->field('title')->find();

        $this->assign('title',$title);
        $this->assign('k_num',$k_num);
        $this->display('activity_order');
    }

    //党课预约界面
    public function bespoke_index2()
    {
        $id = I('dataId');
        $res = D('Cx')->classCommon($id);

        $y_num = $res[0]['y_num']; //已经预约数

        if($y_num == ''){
            $k_num = $res[0]['num'];
        }else{
            $k_num = $res[0]['k_num']; //剩余可预约数
        }

        $where['id'] = $id;
        $title = M('issue_content')->where($where)->field('title')->find();

        $this->assign('title',$title);
        $this->assign('k_num',$k_num);
        $this->display('theme_order');
    }


    //党建活动预约接口
    public function bespoke()
    {
        $Model = M('sign_bespoke');
        $id = I('id');
        $data['content_id'] = $id;
        $data['phone'] = I('phone');
        $data['name'] = I('name');
        $data['organization'] = I('party');
        $data['company'] = I('company');
        $data['bespoke_num'] = I('order_num');
        $data['types'] = I('type');
        $data['source'] = 2;   //2代表大屏预约
        $data['identity'] = I('card');
        $data['text'] = I('remark');
        $data['cre_time'] = time();
        $record = $Model->add($data);

        if($record){
            //预约成功短信提醒
            send($data['phone'],$classify=1,$option='党建活动');
            echo json_encode(array('status'=>1,'msg'=>'预约成功'));
        }else{
            echo json_encode(array('status'=>0,'msg'=>'预约失败'));
        }
    }

    //主题党课
    public function classBespoke()
    {
        $Model = M('sign_lecture');
        $id = I('id');
        $name = I('name');
        if($name != ''){
            $data['content_id'] = $id;
            $data['phone'] = I('phone');
            $data['name'] = $name;
            $data['organization'] = I('party');
            $data['company'] = I('company');
            $data['bespoke_num'] = I('order_num');
            $data['types'] = I('type');
            $data['source'] = 2;   //2代表大屏预约
            $data['identity'] = I('card');
            $data['text'] = I('remark');
            $data['cre_time'] = time();
        }

        $record = $Model->add($data);

        if($record){
            //预约成功短信提醒
            send($data['phone'],$classify=1,$option='主题党课');
            echo json_encode(array('status'=>1,'msg'=>'预约成功'));
        }else{
            echo json_encode(array('status'=>0,'msg'=>'预约失败'));
        }
    }

    //志愿活动预约
    public function VolunteerBespoke()
    {
        $Model = M('sign_volunteer');
        $id = I('id');
        $name = I('name');
        if($name != ''){
            $data['content_id'] = $id;
            $data['phone'] = I('phone');
            $data['name'] = $name;
            $data['organization'] = I('party');
            $data['company'] = I('company');
            $data['bespoke_num'] = I('order_num');
            $data['types'] = I('type');
            $data['source'] = 2;   //2代表大屏预约
            $data['identity'] = I('card');
            $data['text'] = I('remark');
            $data['cre_time'] = time();
        }

        $record = $Model->add($data);

        if($record){
            //预约成功短信提醒
            send($data['phone'],$classify=1,$option='志愿活动');
            echo json_encode(array('status'=>1,'msg'=>'预约成功'));
        }else{
            echo json_encode(array('status'=>0,'msg'=>'预约失败'));
        }
    }



    //党建活动查询
    public function activity_search()
    {
        $Model = M('issue_content');
        $title = I('title');
        $where['title'] = array('like',"%$title%");
        $where['status'] = 1;
        $where['issue_id'] = 82;
        $res = $Model->where($where)->field('id,title,addr,time,content,lat,lng,host')->order('sort desc,id desc')->select();
        foreach ($res as $k=>&$v){
            $v['time_var'] = date('Y-m-d h:i',$v['time']);
        }
        $this->Apireturn($res);
    }

    //场地查询
    public function direct_search()
    {
        $Model = M('issue_content');
        $title = I('title');
        $where['title'] = array('like',"%$title%");
        $where['status'] = 1;
        $where['issue_id'] = 86;
        $res = $Model->where($where)->field('id,title,addr,content,lat,lng,telphone,num')->select();
        $this->Apireturn($res);
    }

    //场地查询
    public function ztc_search()
    {
        $Model = M('sign_direct');
        $title = I('title');
        $where['title'] = array('like',"%$title%");
        $where['status'] = 1;
        $where['issue_id'] = 86;
        $res = $Model->where($where)->field('id,title,desc,img')->select();
        foreach ($res as $k=>&$v){
            $item = get_cover($v['img']);
            $v['path'] = 'http://36.26.83.105:8620'.$item['path'];
        }
        $this->Apireturn($res);
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
            $v['time_var'] =date("Y-m-d H:i",$v['time']);
        }
//        dump($data);;exit;
        $map['issue_id'] =84;
        $data['volunteers'] = M('issue_content')->where($map)->field('id,title,addr,time,content,lat,lng,host')->order('sort desc,id desc')->select();
        foreach ($data['volunteers'] as $k=>&$v){
            $v['time_var'] =date("Y-m-d H:i",$v['time']);
        }
        $map['issue_id'] =86;
        $data['direct'] = M('issue_content')->where($map)->field('id,title,addr,content,lat,lng,telphone,num')->order('sort desc,id desc')->select();
        foreach ($data['direct'] as $k=>&$v){
            $v['time_var'] =date("Y-m-d H:i",$v['time']);
        }
        $map['issue_id'] =88;
        $data['lecture'] = M('issue_content')->where($map)->field('id,title,addr,time,content,lat,lng,teacher,host')->order('sort desc,id desc')->select();
        foreach ($data['lecture'] as $k=>&$v){
            $v['time_var'] =date("Y-m-d H:i",$v['time']);

        }
        $map2['status'] = 1;
        $map2['state'] = array('in','1,2,3,5');
        $data['wish'] = M('sign_wish')->where($map2)->field('id,title,content,obj,form,start_time,state')->order('sort desc,id desc')->select();
        foreach ($data['wish'] as $k=>&$v){
            $v['time_var'] =date("Y-m-d H:i",$v['start_time']);
            $v['state_var'] = $c[$v['state']];
        }

        //
//        $map3['status'] = 1;
//        $map3['state'] = 1;
//        $data['raise'] = M('sign_zhch')->where($map3)->field('id,title,content,img')->order('sort desc,id desc')->select();
//        foreach ($data['raise'] as $k=>&$v){
//            $item = get_cover($v['img']);
//            $v['path'] = 'http://36.26.83.105:8620'.$item['path'];
//        }

        $Model = M();
        $data['raise'] = M('sign_zhch')->where(array('state'=>1,'status'=>1))->field('id,title,content,img')->select();
        $items = $Model->query("
                            SELECT
                                zhch.id,
                                zhch.title,
                                zhch.content,
                                zhch.count,
                                zhch.img,
                                zhch.state_time AS `time`,
                                SUM(apply.count) AS num
                            FROM
                                cxdj_sign_zhch AS zhch
                            JOIN cxdj_sign_zhch_apply AS apply ON zhch.id = apply.zhch_id
                            AND apply.state IN (1, 2, 3)
                            AND apply. STATUS = 1
                            WHERE
                                zhch.state = 1
                            AND zhch.`status` = 1");

        foreach ($data['raise'] as $k=>&$value){
            $item = get_cover($value['img']);
            $value['path'] = 'http://36.26.83.105:8620' . $item['path'];
            if($value['id'] == $items[$k]['id']){
                $already_rate =  (round(($items[$k]['num']/$items[$k]['count']),2)*100).'%';
                $value['rate'] =  $already_rate;
            }else{
                $value['rate'] = '0%';
            }

        }
        //完成率
//        foreach ($items as $k=>&$v){
//            if($v['id'] == $data['raise'][$k]['id']){
//                $already_rate =  (round(($v['num']/$v['count']),2)*100).'%';
//                $data['raise'][$k]['rate'] =  $already_rate;
//            }else{
//                $data['raise'][$k]['rate'] = '0%';
//            }
//
//        }


        $map4['status'] = 1;
        $map4['state'] = array('eq','1');
        $data['ztc'] = M('sign_direct')->where($map4)->field('id,title,desc,img')->order('sort desc,id desc')->select();

        foreach ($data['ztc'] as $k=>&$v){
           $item = get_cover($v['img']);
           $v['path'] = 'http://36.26.83.105:8620'.$item['path'];
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
        $detail[0]['path'] = 'http://36.26.83.105:8620'. $item['path'];
        $detail[0]['start_time'] = date('Y-m-d',$detail[0]['start_time']);


        $this->assign('detail',$detail[0]);
        $this->display('heart_detail');
    }


    //微心愿-我要认领
    public function wxy_apply()
    {
        $id = I('dataId');
        $where['id'] = $id;
        $title = M('sign_wish')->where($where)->field('id,title')->find();
        $this->assign('title',$title);
        $this->display('heart_renling');
    }

    //微心愿-我的心愿
    public function wxy_receive()
    {
        $this->display('heart_order');
    }


    //微心愿-我的心愿
    public function receive_apply()
    {
        $Model = M('sign_wish');
        $name = I('name');
        if($name != ''){
            $data['addr'] = I('addr');
            $data['name'] = $name;
            $data['telephone'] = I('phone');
            $data['organization'] = I('party');
            $data['form'] = I('obj');
            $data['obj'] = I('type');
            $data['source'] = 2;   //2代表大屏预约
            $data['identity'] = I('card');
            $data['remark'] = I('remark');
            $data['cer_time'] = time();
        }
        $record = $Model->add($data);
        if($record){
            //预约成功短信提醒
            send($data['telephone'],$classify=1,$option='我的心愿');
            echo json_encode(array('status'=>1,'msg'=>'预约成功'));
        }else{
            echo json_encode(array('status'=>0,'msg'=>'预约失败'));
        }
    }

    //微心愿接口
    public function wxy_bespoke()
    {
        $Model = M('sign_wish_apply');
        $id = I('id');
        $name = I('name');
        if($name != ''){
            $data['wish_id'] = $id;
            $data['telephone'] = I('phone');
            $data['name'] = $name;
            $data['organization'] = I('party');
            $data['company'] = I('company');
            $data['types'] = I('type');
            $data['source'] = 2;   //2代表大屏预约
            $data['identity'] = I('card');
            $data['content'] = I('remark');
            $data['cer_time'] = time();
        }
        $record = $Model->add($data);
        if($record){
            //预约成功短信提醒
            send($data['telephone'],$classify=1,$option='我要认领');
            echo json_encode(array('status'=>1,'msg'=>'预约成功'));
        }else{
            echo json_encode(array('status'=>0,'msg'=>'预约失败'));
        }
    }
    //场地预约页面
    public function venueIndex()
    {
        $id = I('dataId');
        $where['id'] = $id;
        $title = M('issue_content')->where($where)->field('title')->find();
//        dump($title);die;

        $this->assign('title',$title);
        $this->display('place_order');
    }


    //场地预约接口
    public function place_bespoke()
    {
        $Model = M('sign_field');
        $id = I('id');
        $st_tmie = I('start_time');
        $end_tmie = I('end_time');
        $name = I('name');
        if($name != ''){
            $data['content_id'] = $id;
            $data['phone'] = I('phone');
            $data['name'] = I('theme');
            $data['facility'] = I('sb');
            $data['bespoke_num'] = I('place_num');
            $data['organization'] = I('party');
            $data['start_time'] = strtotime($st_tmie);
            $data['end_time'] = strtotime($end_tmie);
            $data['source'] = 2;   //2代表大屏预约
            $data['identity'] = I('card');
            $data['text'] = I('remark');
            $data['cre_time'] = time();
        }
        $record = $Model->add($data);
        if($record){
            //预约成功短信提醒
            send($data['phone'],$classify=1,$option='场地预约');
            echo json_encode(array('status'=>1,'msg'=>'预约成功'));
        }else{
            echo json_encode(array('status'=>0,'msg'=>'预约失败'));
        }
    }



    public function qrcode($url='',$level=3,$size=20){

        Vendor('phpqrcode.phpqrcode');
        $errorCorrectionLevel =intval($level) ;//容错级别
        $matrixPointSize = intval($size);//生成图片大小
        //生成二维码图片
        //echo $_SERVER['REQUEST_URI'];
        $object = new \QRcode();
        $object->png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);
    }

    //获取二维码
    public function qrcodefor($id=null,$type=null)
    {
        switch ($type) {
            case 1: //活动
                $this->qrcode('http://cxdj.cmlzjz.com/home/wxindex/activity_det/id/' . $id);
                break;
            case 2: //志愿者
                $this->qrcode('http://cxdj.cmlzjz.com/home/wxindex/volunteer_det/id/' . $id);
                break;
            case 3: //场地
                $this->qrcode('http://cxdj.cmlzjz.com/home/wxindex/direct_det/id/' . $id);
                break;
            case 4: //党课
                $this->qrcode('http://cxdj.cmlzjz.com/home/wxindex/lecture_from/content_id/' . $id);
                break;
            case 5: // 微心愿
                $this->qrcode('http://cxdj.cmlzjz.com/home/wxindex/wish_det/content_id/' . $id);
                break;
            case 6: // 众筹
                $this->qrcode('http://cxdj.cmlzjz.com/home/wxindex/raise_det/id/' . $id);
                break;
            case 7:
                $this->qrcode('http://cxdj.cmlzjz.com/home/wxindex/project_det/id/' . $id);
        }
    }

    //场地预约-旧的接口
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
            $v['ewm'] = "http://36.26.83.105:8620/cx/cx/qrcodefor/id/".$v['id'].'?type=2';
        }


        $this->assign('list',$data[0]);
        $this->display('volunteer');
    }

    public function volunteer_apply()
    {
        $id = I('dataId');
        $where['id'] = $id;
        $title = M('issue_content')->where($where)->field('title')->find();

        $this->assign('title',$title);
        $this->display('volunteer_order');
    }

    //

    public function zc_apply()
    {
        $Model = M('sign_zhch_apply');
        $name = I('name');
        if($name != ''){
            $data['telephone'] = I('phone');
            $data['name'] = I('name');
            $data['identity'] = I('card');
            $data['organization'] = I('party');
            $data['source'] = 2;   //2代表大屏预约
            $data['types'] = I('type');
            $data['company'] = I('company');
            $data['count'] = I('order_num');
            $data['cre_time'] = time();
        }
        $record = $Model->add($data);
        if($record){
            send($data['telephone'],$classify=1,$option='众筹预约');
            echo json_encode(array('status'=>1,'msg'=>'预约成功'));
        }else{
            echo json_encode(array('status'=>0,'msg'=>'预约失败'));
        }
    }

    //众筹服务
    public function zhongchou()
    {
        $Model = M();
        $id = I('id');
        $res = $Model->query("SELECT
                                zhch.id,
                                zhch.title,
                                zhch.content,
                                zhch.count,
                                zhch.img,
                                zhch.state_time AS `time`,
                                SUM(apply.count) AS num
                            FROM
                                cxdj_sign_zhch AS zhch
                            JOIN cxdj_sign_zhch_apply AS apply ON zhch.id = apply.zhch_id
                            AND apply.state IN (1, 2, 3)
                            AND apply. STATUS = 1
                            AND apply.zhch_id = $id
                            WHERE
                                zhch.state = 1
                            AND zhch.`status` = 1");
        //完成率
        foreach ($res as $k=>&$v){
            $already_rate =  (round(($v['num']/$v['count']),2)*100).'%';
            $item = get_cover($v['img']);
            $v['path'] = 'http://36.26.83.105:8620' . $item['path'];
            $v['ewm'] =  "http://36.26.83.105:8620/cx/cx/qrcodefor/id/".$v['id'].'?type=6';
        }

        $appraise_ewm = "http://36.26.83.105:8620/cx/cx/qrcodefor/id/".$v['id'].'?type=6';
        $res[0]['rate'] = $already_rate;

        //微信头像
        $headimg = $Model->query("SELECT
                                    `user`.headimgurl
                                FROM
                                    cxdj_sign_zhch AS zhch
                                JOIN cxdj_sign_zhch_apply AS apply ON zhch.id = apply.zhch_id
                                JOIN cxdj_wxuser AS `user` ON apply.openid = `user`.openid
                                AND apply.state IN (1, 2, 3)
                                AND apply. STATUS = 1
                                AND apply.zhch_id = $id
                                WHERE
                                    zhch.state = 1
                                AND zhch.`status` = 1");


        //统计评价
        $pj = $Model->query("SELECT
                                eval.pj,
                                count(pj) AS `count`
                            FROM
                                cxdj_sign_zhch AS zc
                            JOIN cxdj_sign_zhch_evaluate AS eval ON zc.id = eval.zhch_id
                            AND eval.state = 1
                            AND  eval.`status` = 1
                            WHERE zc.state = 1 AND zc.`status` = 1
                            AND  zc.id = $id
                            GROUP BY pj
                            ");

        foreach ($pj as $k=>&$v){
            if($v['pj'] == 1){
                $perfect = $v['count'];
            }elseif ($v['pj'] == 2){
                $satisfied = $v['count'];
            }elseif ($v['pj'] == 3){
                $commonly = $v['count'];
            }elseif (($v['pj'] == 4)){
                $dissatisfied = $v['count'];
            }
        }


        $this->assign('appraise_ewm',$appraise_ewm);
        $this->assign('headimg',$headimg);
        $this->assign('perfect',$perfect);
        $this->assign('satisfied',$satisfied);
        $this->assign('commonly',$commonly);
        $this->assign('dissatisfied',$dissatisfied);
        $this->assign('res',$res[0]);
        $this->display('crowd_funding');
    }


    //我要参与 众筹服务
    public function zc_applyIndex()
    {
        $id = I('dataId');
        $where['id'] = $id;
        $title = M('sign_zhch')->where($where)->field('id,title')->find();

        $this->assign('title',$title);

        $this->display('crowd_order_new');
    }

    //项目直通车-我要参与页面
    public function ztc_join_view()
    {
        $id = I('dataId');
        $where['id'] = $id;
        $title = M('sign_direct')->where($where)->field('title')->find();

        $this->assign('title',$title);
        $this->display('item_party_in');
    }

    //项目直通车-我有需求
    public function ztc_demand_view()
    {
        $id = I('dataId');
        $where['id'] = $id;
        $title = M('sign_direct')->where($where)->field('title')->find();

        $this->assign('title',$title);
        $this->display('item_need');
    }

    //项目直通车-我要参与
    public function ztc_join()
    {
        $Model = M('sign_direct_apply');
        $name = I('name');
        if($name != ''){
            $data['telephone'] = I('phone');
            $data['name'] = I('name');
            $data['direct_id'] = I('id');
            $data['identity'] = I('card');
            $data['organization'] = I('party');
            $data['source'] = 2;   //2代表大屏预约
            $data['types'] = I('type');
            $data['remark'] = I('remark');
            $data['company'] = I('company');
            $data['cer_time'] = time();
        }
        $record = $Model->add($data);
        if($record){
            send($data['telephone'],$classify=1,$option='直通车认领');
            echo json_encode(array('status'=>1,'msg'=>'预约成功'));
        }else{
            echo json_encode(array('status'=>0,'msg'=>'预约失败'));
        }
    }

    //项目直通车-我有需求
    public function ztc_demand()
    {
        $Model = M('sign_direct_demand');
        $name = I('name');
        if($name != ''){
            $data['telephone'] = I('phone');
            $data['name'] = I('name');
            $data['source'] = 2;   //2代表大屏预约
            $data['content'] = I('remark');
            $data['cer_time'] = time();
        }
        $record = $Model->add($data);
        if($record){
            send($data['telephone'],$classify=1,$option='需求提交');
            echo json_encode(array('status'=>1,'msg'=>'提交成功'));
        }else{
            echo json_encode(array('status'=>0,'msg'=>'提交失败'));
        }
    }

    //项目直通车详情
    public function ztc_detail()
    {
        $Model = M();
        $id = I('id');
        $ztc_detail = M('sign_direct')->where("state=1 and status=1 and id=$id")->field('id,title,desc,img')->select();
        foreach ($ztc_detail as $k=>&$v){
            $items = get_cover($v['img']);
            $v['path'] ='http://36.26.83.105:8620'. $items['path'];
            $v['ewm'] = "http://36.26.83.105:8620/cx/cx/qrcodefor/id/".$v['id'].'?type=7';
        }

        $detail = $Model->query("SELECT
                                    direct.title,
                                    direct.img,
                                    direct.`desc`,
                                    apply.`name`                                    
                                    FROM
                                        cxdj_sign_direct AS direct
                                    JOIN cxdj_sign_direct_apply AS apply ON direct.id = apply.direct_id                           
                                    WHERE
                                        direct.state = 1
                                    AND direct.`status` = 1
                                    AND apply.state = 1
                                    AND apply.`status` = 1   
                                    AND direct.id = $id
                                    ");


        //统计评价
        $pj = $Model->query("SELECT
                                eval.pj,
                                count(pj) AS `count`
                            FROM
                                cxdj_sign_direct AS direct
                            JOIN cxdj_sign_direct_evaluate AS eval ON direct.id = eval.direct_id
                            AND eval.state = 1
                            AND  eval.`status` = 1
                            WHERE direct.state = 1 AND direct.`status` = 1
                            AND  direct.id = $id
                            GROUP BY pj
                            ");


        foreach ($pj as $k=>&$v){
            if($v['pj'] == 1){
                $perfect = $v['count'];
            }elseif ($v['pj'] == 2){
                $satisfied = $v['count'];
            }elseif ($v['pj'] == 3){
                $commonly = $v['count'];
            }elseif (($v['pj'] == 4)){
                $dissatisfied = $v['count'];
            }
        }

        $item = get_cover($detail[0]['img']);
        $detail[0]['path'] = 'http://36.26.83.105:8620'. $item['path'];

        $this->assign(array(
            'perfect'=>$perfect,
            'satisfied'=>$satisfied,
            'commonly'=>$commonly,
            'dissatisfied'=>$dissatisfied,
        ));
        $this->assign('detail',$detail[0]);
        $this->assign('ztc_detail',$ztc_detail[0]);
        $this->display('item_detail');
    }




    /****************************************************智能导览************************************************/

    public function articleList()
    {
        $Model = M('issue_content');
        $id = I('id');
        $where['cxdj_issue_content.status'] = 1;
        $where['cxdj_issue_content.issue_id'] = $id;
        $list = $Model->join('cxdj_issue as issue on cxdj_issue_content.issue_id = issue.id')
            ->where($where)
            ->order('cxdj_issue_content.create_time desc')
            ->field('cxdj_issue_content.id,cxdj_issue_content.title,cxdj_issue_content.content,issue.title as stitle')
            ->select();

        $count = count($list);
        if ($count == 1) {
            $this->assign('detail', $list['0']);
            $this->display('zd_detail');
        } else {
            $this->assign('list', $list);
            $this->display('list1');
        }

    }


    //工作人员
    public function member()
    {
        $Model = M('issue_content');
        $where['issue_id'] = I('id');
        $where['status'] = 1;
        $list = $Model ->where($where)->field('id,name,content,cover_id')->select();
        foreach ($list as $k=>&$v){
            $items = get_cover($v['cover_id']);
            $v['path'] = $items['path'];
        }

        $this->assign('list',$list);
        $this->display('head_list');
    }

    public function service_center()
    {
//        $Model = M('issue_content');
//        $where['status'] = 1;
//        $where['issue_id'] = 170;
//        $list = $Model ->where($where)->field('id,title,content')->select();
//        $count = count($list);
//        if($count == 1){
//            $this->assign('detail',$list[0]);
//            $this->display('detail');
//        }else{
//
//            $this->assign('list',$list);
//            $this->display('service_center');
//        }

        $Model = M('issue');
        $where['status'] = 1;
        $where['pid'] = 172;
        $title_id = $Model->where($where)->field('id,title')->select();

        $this->assign('title_id',$title_id);
        $this->display('organize_bulid');

    }

    public function service_detail()
    {
        $Model = M('issue_content');
        $where['id'] = I('id');
        $where['status'] = 1;
        $list = $Model ->where($where)->field('id,title,content')->find();

        $this->assign('detail',$list);
        $this->display('detail');
    }


    //阵地服务
    public function zd_fw()
    {
        $Model = M('issue');
        $where['status'] = 1;
        $where['pid'] = 170;
        $title_id = $Model->where($where)->field('id,title')->select();

        $this->assign('title_id',$title_id);
        $this->display('party_build_intro5');


    }


    public function zd_detail()
    {
        $Model = M('issue_content');
        $id = I('id');

        $where['status'] = 1;
        $where['id'] = $id;
        $detail = $Model ->where($where)->field('id,title,content')->find();

        $this->assign('detail',$detail);
        $this->display('zd_detail');
    }

    public function organization()
    {
        $Model = M('issue_content');
        $where['status'] = 1;
        $where['issue_id'] = 171;
        $list = $Model ->where($where)->field('id,title,content,cover_id')->select();

        $count = count($list);
        if($count == 1){
            $this->assign('detail',$list[0]);
            $this->display('zd_detail');
        }else{

            $this->assign('list',$list);
            $this->display('position_list');
        }
    }

    public function brand1()
    {
        $Model = M('issue_content');
        $where['status'] = 1;
        $where['issue_id'] = 173;
        $list = $Model ->where($where)->field('id,title,content')->select();
        $count = count($list);
        if($count == 1){
            $this->assign('detail',$list[0]);
            $this->display('brand_detail');
        }else{

            $this->assign('list',$list);
            $this->display('list1');
        }

    }

    //优秀党建品牌
    public function brand()
    {
        $this->display('brand');
    }


    public function brand_detail()
    {
        $Model = M('issue_content');
        $id = I('id');

        $where['status'] = 1;
        $where['id'] = $id;
        $detail = $Model ->where($where)->field('id,title,content')->find();

        $this->assign('detail',$detail);
        $this->display('zd_detail');
    }

    public function  brand_videoList()
    {
        $Model = M();
        $id = I('id');
        $res = $Model->query("SELECT
                                    CONCAT(
                                        file.savepath,
                                        file.savename
                                    ) AS path,
                                    content.id,
                                    content.title,
                                    content.cover_id
                                FROM
                                     cxdj_issue_content AS content
                                JOIN cxdj_file AS file ON content.video_id = file.id
                                WHERE
                                    `status` = 1
                                AND issue_id = $id                           
                                ORDER BY
                                    content.create_time DESC
                                    ");

            foreach ($res as $k=>&$v){
                $items = get_cover($v['cover_id']);
                $v['img_url'] = $items['path'];
            }

            $this->assign('result',$res);
            $this->display("video_list");


    }

    public function brand_video()
    {
        $id = I('id');
        $Model = M();
        $video = $Model->query("SELECT
                                        CONCAT(
                                            file.savepath,
                                            file.savename
                                        ) AS path,
                                        content.id,
                                        content.title
                                    FROM
                                         cxdj_issue_content AS content
                                    JOIN cxdj_file AS file ON content.video_id = file.id
                                    WHERE
                                        `status` = 1
                                    AND content.id = $id
                                    ORDER BY
                                        content.create_time DESC
                                        ");


        $this->assign('video',$video['0']);
        $this->display('brand_video');
    }


    public function organization_detail()
    {
        $this->display('organize_construct');
    }












}