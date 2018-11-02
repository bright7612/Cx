<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/12
 * Time: 19:27
 */
namespace Cx\Controller;
use Think\Controller;
// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');
class DataController extends Controller
{

    private  $ajax_dzz;  //党组织
    private  $ajax_user;  //党员
    private  $ajax_volunteer;  //志愿服务
    private  $ajax_WG;  //网格
    private  $ajax_event;  //事件
    private  $ajax_persion;  //人口
    private  $ajax_jiedao_people;  //街道 人口统计视图
    private  $ajax_jiedao;  //街道 人口统计视图
    private  $event_dy;  //党员办结事件
    private  $event_bm;  //便民服务
    private  $event_lastyear;  //过去一年县事件十二个月的数量
    private  $event_current;  //当年县事件十二个月的数量
    private  $time ;



    function _initialize()
    {
        $this->ajax_dzz = M('ajax_dzz');
        $this->ajax_user = M('ajax_user');
        $this->ajax_volunteer = M('ajax_volunteer');
        $this->ajax_WG = M('ajax_wg');
        $this->ajax_event = M('ajax_event');
        $this->ajax_persion = M('ajax_persion');
        $this->ajax_jiedao_people = M('jiedao_people');
        $this->ajax_jiedao = M('jiedao');
        $this->event_dy = M('event_dy');
        $this->event_bm = M('event_bm');
        $this->event_lastyear = M('event_lastyear');
        $this->event_current = M('event_current');
        $this->time = 3600 * 24;
    }


    //党员 报到登记 入口
    public function report_registration($type=null,$page=1,$r=500){
        if($type == 2){
            if($_REQUEST['lateId']){
                $this -> dzPartylistPage($_REQUEST['lateId'],$page,$r);
            }
            elseif ($_REQUEST['late2Id']){
                $this -> dzPartyDetaillistPage($_REQUEST['late2Id'],$page,$r);
            }
            else{
                echo json_encode(array('data'=>array(),'msg'=>'错误请求！！','code'=>300));
                exit;
            }
        }
        elseif($type == 1){
            if($_REQUEST['lateId']){
                $this -> dzPartySignRecordlistPage($_REQUEST['lateId'],$page,$r);
            }
            elseif ($_REQUEST['late2Id']){
                $this -> dzPartySignRecordDetaillistPage($_REQUEST['late2Id'],$page,$r);
            }
            else{
                echo json_encode(array('data'=>array(),'msg'=>'错误请求！！','code'=>300));
                exit;
            }
        }
        else{
            echo json_encode(array('data'=>array(),'msg'=>'错误请求！！','code'=>300));
            exit;
        }
    }

    //党员 报到登记 不计二层
    public function dzPartylistPage($lateId=null,$page = 1 ,$r = 500){
        if(empty($lateId)){
            echo json_encode(array('data'=>array(),'msg'=>'错误请求！！','code'=>300));
            exit;
        }
        if(!S('NoRecord_'.$lateId)){
            $url = 'http://www.dysfz.gov.cn/apiXC/dzPartylistPage.do';
            $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
            $da['START'] = $page;
            $da['COUNT'] = $r;
            $da['BRANCH_ID'] = (int)$lateId;
            $das = json_encode($da);
            $list = $this->httpjson($url,$das);
            S('NoRecord_'.$lateId,$list);
        }else{
            $list = S('NoRecord_'.$lateId);
        }

        $data['totalCount'] = (int)ceil($list['totalCount']/$r);
        foreach ($list['data'] as $k=>&$v){

            $data['list'][] = array(
                array('value'=>$k+1,'width'=>10,'type'=>2),
                array('value'=>$v['NAME'],'width'=>40,'type'=>2),
                array('value'=>$v['SECRETARY'],'width'=>20,'type'=>2),
                array('value'=>$v['num'],'width'=>30,'late2Id'=>$v['BRANCH_ID'],'type'=>2,'clickable'=>true),
            );
        }
        $data['title'] = '不计入到会党员';
        $data['head'] = array(
            array('name'=>'序号','width'=>10),
            array('name'=>'党组织名称','width'=>40),
            array('name'=>'书记','width'=>20),
            array('name'=>'党员不计到入会总数量','width'=>30),
        );
//        $data = json_encode($data);
        echo json_encode(array('data'=>$data,'msg'=>'请求成功','code'=>200));
        exit;





    }
    //党员 报到登记 不计三层
    public function dzPartyDetaillistPage($lateId=null,$page = 1 ,$r = 500){
        if(empty($lateId)){
            echo json_encode(array('data'=>array(),'msg'=>'错误请求！！','code'=>300));
            exit;
        }
        if(!S('NoRecord_NoRecord_'.$lateId)){
        $url = 'http://www.dysfz.gov.cn/apiXC/dzPartyDetaillistPage.do';
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['START'] = $page;
        $da['COUNT'] = $r;
        $da['BRANCH_ID'] = (int)$lateId;
        $das = json_encode($da);
        $list = $this->httpjson($url,$das);
            S('NoRecord_NoRecord_'.$lateId,$list);
        }else{
            $list = S('NoRecord_NoRecord_'.$lateId);
        }
        $data['totalCount'] = (int)ceil($list['totalCount']/$r);
        foreach ($list['data'] as $k=>&$v){
            $data['list'][] = array(
                array('value'=>$k+1,'width'=>10,'type'=>2),
                array('value'=>$v['PARTYNAME'],'width'=>15,'type'=>2),
                array('value'=>$v['BRANCHNAME'],'width'=>30,'type'=>2),
                array('value'=>$v['ADDRESS'],'width'=>30,'type'=>2),
                array('value'=>$v['SECRETARY'],'width'=>15,'type'=>2),
            );
        }
        $data['title'] = '不计入到会党员';
        $data['head'] = array(
            array('name'=>'序号','width'=>10),
            array('name'=>'党员名称','width'=>15),
            array('name'=>'党组织名称','width'=>30),
            array('name'=>'党组织地址','width'=>30),
            array('name'=>'书记','width'=>15),
        );
//        $data = json_encode($data);
        echo json_encode(array('data'=>$data,'msg'=>'请求成功','code'=>200));

        exit;





    }
    //党员 报到登记 2018二层
    public function dzPartySignRecordlistPage($lateId=null,$page = 1 ,$r = 500){
        if(empty($lateId)){
            echo json_encode(array('data'=>array(),'msg'=>'错误请求！！','code'=>300));
            exit;
        }
        if(!S('report_'.$lateId)){
            $url = 'http://www.dysfz.gov.cn/apiXC/dzPartySignRecordlistPage.do';
            $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
            $da['START'] = $page;
            $da['COUNT'] = $r;
            $da['BRANCH_ID'] = (int)$lateId;
            $das = json_encode($da);
            $list = $this->httpjson($url,$das);
            S('report_'.$lateId,$list);
        }else{
            $list = S('report_'.$lateId);
        }

        $data['totalCount'] = (int)ceil($list['totalCount']/$r);
        foreach ($list['data'] as $k=>&$v){
            $data['list'][] = array(
                array('value'=>$k+1,'width'=>10,'type'=>1,),
                array('value'=>$v['NAME'],'width'=>30,'type'=>1,),
                array('value'=>$v['ADDRESS'],'width'=>30,'type'=>1,),
                array('value'=>$v['SECRETARY'],'width'=>10,'type'=>1,),
                array('value'=>$v['num'],'width'=>20,'type'=>1,'late2Id'=>$v['BRANCH_ID'],'clickable'=>true),
            );
        }
        $data['title'] = '2018年报到';
        $data['head'] = array(
            array('name'=>'序号','width'=>10),
            array('name'=>'党组织名称','width'=>30),
            array('name'=>'党组织地址','width'=>30),
            array('name'=>'书记','width'=>10),
            array('name'=>'党员报到总数量','width'=>20),
        );
//        $data = json_encode($data);
        echo json_encode(array('data'=>$data,'msg'=>'请求成功','code'=>200));

        exit;





    }
    //党员 报到登记 2018三层
    public function dzPartySignRecordDetaillistPage($lateId=null,$page = 1 ,$r = 500){
        if(empty($lateId)){
            echo json_encode(array('data'=>array(),'msg'=>'错误请求！！','code'=>300));
            exit;
        }
        if(!S('report_report_'.$lateId)){
            $url = 'http://www.dysfz.gov.cn/apiXC/dzPartySignRecordDetaillistPage.do';
            $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
            $da['START'] = $page;
            $da['COUNT'] = $r;
            $da['BRANCH_ID'] = (int)$lateId;
            $das = json_encode($da);
            $list = $this->httpjson($url,$das);
            S('report_report_'.$lateId,$list);
        }else{
            $list = S('report_report_'.$lateId);
        }
        $data['totalCount'] = (int)ceil($list['totalCount']/$r);
        foreach ($list['data'] as $k=>&$v){
            $data['list'][] = array(
                array('value'=>$k+1,'width'=>10,'type'=>1,'classify'=>3),
                array('value'=>$v['PARTYNAME'],'width'=>15,'type'=>1,'classify'=>3),
                array('value'=>$v['BRANCHNAME'],'width'=>30,'type'=>1,'classify'=>3),
                array('value'=>$v['ADDRESS'],'width'=>30,'type'=>1,'classify'=>3),
                array('value'=>$v['SECRETARY'],'width'=>15,'type'=>1,'classify'=>3),
            );
        }
        $data['title'] = '2018年报到';
        $data['head'] = array(
            array('name'=>'序号','width'=>10),
            array('name'=>'党员名称','width'=>15),
            array('name'=>'党组织名称','width'=>30),
            array('name'=>'党组织地址','width'=>30),
            array('name'=>'书记','width'=>15),
        );
//        $data = json_encode($data);
        echo json_encode(array('data'=>$data,'msg'=>'请求成功','code'=>200));
        exit;





    }







    function httpjson($url, $data = NULL, $json = true)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($data)) {
            if($json && is_array($data)){
                $data = json_encode( $data );
            }
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            if($json){ //发送JSON数据
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_HTTPHEADER,
                    array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($data))
                );
            }
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);
        $errorno = curl_errno($curl);
        if ($errorno) {
            return array('errorno' => false, 'errmsg' => $errorno);
        }
        curl_close($curl);
        return json_decode($res, true);

    }


    public function index()
    {

        set_time_limit(3600);
        $url = 'http://www.dysfz.gov.cn/apiXC/branchDZZfeeList.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 150;
        $da['START'] = 1;
        $das = json_encode($da);
        $data = $this->httpjson($url, $das);
        dump($data);die;


    }

    //党组织 主题党日
    public function dzz_ztdr()
    {
            set_time_limit(0);
        if(!S('dzz_ztdr')){
            $url = 'http://www.dysfz.gov.cn/apiXC/getHeldZTDRlistPage.do'; //党员党建
            $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
            $da['COUNT'] = 300;
            $da['START'] = 1;
            $das = json_encode($da);
            $list1[] = $this->httpjson($url, $das);
            $count = $list1['0']['totalCount'];
            $pagenum = ceil($count/$da['COUNT']);
            for ($i = 1; $i <= $pagenum; $i++) {
                $da['START'] = $i;
                $das = json_encode($da);
                $list[] = $this->httpjson($url, $das);
                foreach ($list[$i-1]['data'] as $k=>$v){
                    $data[] = $v;

                }
            }

            S('dzz_ztdr',$data);   // 写入缓存，expire'=>600 :  设置有效时间：600秒
        }else{
            $data = S('dzz_ztdr');// 获取缓存
        }
        foreach ($data as $k2=>&$v2){
            $v2['ykz_num'] = (round(($v2['ykz']/$v2['dzbNum']),4)*100);
        }
        array_multisort(array_column($data,'ykz_num'),SORT_DESC,$data);


        foreach ($data as $k=>&$v){
                $item[] = array(
                    array('value'=>$v['name'], 'width'=>20,'ztdrType'=>2),
                    array('value'=>$v['dzbNum'], 'width'=>12,'ztdrType'=>2),
                    array('value'=>$v['ykz'], 'width'=>25,'ztdrId'=>$v['BRANCH_ID'],'clickable'=>true,'ztdrType'=>1),
                    array('value'=>$v['wkz'], 'width'=>25,'ztdrId'=>$v['BRANCH_ID'],'clickable'=>true,'ztdrType'=>2),
                    array('value'=>$v['ykz_num'].'%', 'width'=>18,'ztdrType'=>2),
                );
            }



        $head = array(
            array('name'=>'党组织名称', 'width'=>20),
            array('name'=>'党支部数量', 'width'=>12),
            array('name'=>'已开展主题党日党支部数', 'width'=>25),
            array('name'=>'未开展主题党日党支部数', 'width'=>25),
            array('name'=>'已开展支部占比', 'width'=>18),
        );

        echo json_encode(array('status'=>200,'data'=>array('title'=>'本月主题党日开展情况','head'=>$head,'list'=>$item)));

    }

    //主题党点开二级
    public function ztdr_activity()
    {
        set_time_limit(0);

        $id = I('BRANCH_ID');
        $type = I('TYPE');
        if($type == 1){
            if(!S('dzz_ztdr_1'.$id)){
                $url = 'http://www.dysfz.gov.cn/apiXC/getHeldZtdrlistDetaiPage.do'; //党员党建
                $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
                $da['BRANCH_ID'] = $id;
                $da['TYPE'] = $type;    //type=1  已展开  type = 2 未展开
                $da['START'] = 1;
                $da['COUNT'] = 300;
                $das = json_encode($da);
                $list = $this->httpjson($url, $das);
                S('dzz_ztdr_1'.$id,$list);
            }else{
                $list =     S('dzz_ztdr_1'.$id);;// 获取缓存
            }
        }
        elseif ($type == 2){
            if(!S('dzz_no_ztdr_1'.$id)){

                $url = 'http://www.dysfz.gov.cn/apiXC/getHeldZtdrlistDetaiPage.do'; //党员党建
                $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
                $da['BRANCH_ID'] = $id;
                $da['TYPE'] = $type;    //type=1  已展开  type = 2 未展开
                $da['START'] = 1;
                $da['COUNT'] = 300;
                $das = json_encode($da);
                $list = $this->httpjson($url, $das);
                S('dzz_no_ztdr_1'.$id,$list);
            }else{
                $list = S('dzz_no_ztdr_1'.$id);;// 获取缓存
            }
        }

        if($type == 1){
            $head = array(
                array('name'=>'党支部名称', 'width'=>12),
                array('name'=>'活动主题名称', 'width'=>14),
                array('name'=>'总计到会人数', 'width'=>8),
                array('name'=>'应到会人数', 'width'=>8),
                array('name'=>'不计到会率人数', 'width'=>14),
                array('name'=>'隶属本组织党员签到记录', 'width'=>14),
                array('name'=>'非隶属本党组织签到人数', 'width'=>14),
                array('name'=>'未签到人数', 'width'=>8),
                array('name'=>'到会率', 'width'=>8),

            );

            foreach($list['data'] as $k=>$v){
                $dh_rate = (round(($v['rcount_sjdh']/$v['pcount_ydh']),4)*100).'%';
                $late_count =  $v['pcount_ydh'] - $v['rcount_sjdh'];
                $item[] = array(
                    array('value'=>$v['branch_name'], 'width'=>12),
                    array('value'=>$v['name'], 'width'=>14,'clickable'=>true,'ztdr2Id'=>$v['activity_id']),
                    array('value'=>$v['pcount'], 'width'=>8,),
                    array('value'=>$v['pcount_ydh'], 'width'=>8,),
                    array('value'=>$v['pcount_bjdhl'], 'width'=>14,),
                    array('value'=>$v['rcount_sjdh'], 'width'=>14,),
                    array('value'=>$v['rcount_ydqd'], 'width'=>14,),
                    array('value'=>$late_count, 'width'=>8,),
                    array('value'=> $dh_rate, 'width'=>8,),

                );
            }

            echo json_encode(array('status'=>200,'data'=>array('title'=>'本月主题党日开展情况','head'=>$head,'list'=>$item)));
        }elseif ($type == 2){
            $head = array(
                array('name'=>'党支部名称', 'width'=>30),
                array('name'=>'党组织书记', 'width'=>20),
                array('name'=>'联系电话', 'width'=>20),
                array('name'=>'党组织地址', 'width'=>30),

            );

            foreach($list['data'] as $k=>$v){
                $item[] = array(
                    array('value'=>$v['NAME'], 'width'=>30),
                    array('value'=>$v['SECRETARY'], 'width'=>20),
                    array('value'=>$v['PHONE'], 'width'=>20),
                    array('value'=>$v['ADDRESS'], 'width'=>30),
                );
            }

            echo json_encode(array('status'=>200,'data'=>array('title'=>'本月未开展主题党日','head'=>$head,'list'=>$item)));
        }

    }

    //第三层详情
    public function dzz_detail()
    {

        set_time_limit(0);
        $id = I('ACTIVITY_ID');
        if(!S('dzz_ztdr_detail_'.$id)){
        $url = 'http://www.dysfz.gov.cn/apiXC/ztdrActivityDetai.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['ACTIVITY_ID'] = $id;
        $das = json_encode($da);
        $list1 = $this->httpjson($url, $das);
            S('dzz_ztdr_detail_'.$id,$list1);
        }else{
            $list1 = S('dzz_ztdr_detail_'.$id);// 获取缓存
        }

            $item['name'] = $list1['data']['NAME'];
            $item['BEGIN_TIME'] = $list1['data']['BEGIN_TIME'];
            $item['END_TIME'] = $list1['data']['END_TIME'];
            $item['address'] = $list1['data']['DEVICEPLACE_PLACE'];
            $item['BRANCH_NAME'] = $list1['data']['BRANCH_NAME'];
            $item['MODERATOR'] = $list1['data']['MODERATOR'];
            $item['RECORDER'] = $list1['data']['RECORDER'];
            $item['bCount'] = $list1['data']['bCount'];
            $item['rCount'] = $list1['data']['rCount'];
            $item['ABSENTEE'] = $list1['data']['ABSENTEE'];
            $item['CONTENT'] = $list1['data']['CONTENT'];
            $item['IMGS'] = $list1['data']['imgs'];



         echo json_encode(array('status'=>200,'data'=>$item));
    }

    public function developList()
    {
        $id = I('branch_id');
        $where['branch_id'] = $id;
        $where['status'] = 1;
        $meb = M('dr_dzz_develop_dy')->where($where)->select();

        $head = array(
            array('name'=>'序号', 'width'=>15),
            array('name'=>'姓名', 'width'=>15),
            array('name'=>'性别', 'width'=>10),
            array('name'=>'民族', 'width'=>10),
            array('name'=>'出生日期', 'width'=>20),
            array('name'=>'所属党支部', 'width'=>30),
        );

        $i = 1;
        foreach ($meb as $k=>$v){
            $ii = $i++;
            $item[] = array(
                array('value'=>$ii, 'width'=>15,'idCard'=>true),
                array('value'=>$v['name'], 'width'=>15,'idCard'=>true),
                array('value'=>$v['sex'], 'width'=>10,'idCard'=>true),
                array('value'=>$v['nation'], 'width'=>10,'idCard'=>true),
                array('value'=>$v['birthday'], 'width'=>20,'idCard'=>true),
                array('value'=>$v['party_branch'], 'width'=>30,'idCard'=>true),

            );
        }

        echo json_encode(array('status'=>200,'data'=>array('title'=>'党员信息','head'=>$head,'list'=>$item)));

    }

    public function dxtjList($type)
    {
        if ($type == 1){
            $where['town_id'] = I('town_id');
            $where['status'] = 1;
            $organization = M('dr_dzz_dxtj')->where($where)->field('organization,secretary,time')->select();

            $head = array(
                array('name'=>'序号', 'width'=>20),
                array('name'=>'党支部', 'width'=>40),
                array('name'=>'书记', 'width'=>20),
                array('name'=>'时间', 'width'=>20),
            );

            $i = 1;
            foreach ($organization as $k=>$v){
                $ii = $i++;
                $item[] = array(
                    array('value'=>$ii, 'width'=>20),
                    array('value'=>$v['organization'], 'width'=>40),
                    array('value'=>$v['secretary'], 'width'=>20),
                    array('value'=>$v['time'], 'width'=>20),

                );
            }

            echo json_encode(array('status'=>200,'data'=>array('title'=>'已开展党支部','head'=>$head,'list'=>$item)));
        }elseif ($type == 2){

            $where['town_id'] = I('town_id');
            $where['status'] = 1;
            $organization = M('dr_dzz_undxtj')->where($where)->field('organization,secretary')->select();

            $head = array(
                array('name'=>'序号', 'width'=>25),
                array('name'=>'党支部', 'width'=>50),
                array('name'=>'书记', 'width'=>25),
            );

            $i = 1;
            foreach ($organization as $k=>$v){
                $ii = $i++;
                $item[] = array(
                    array('value'=>$ii, 'width'=>25),
                    array('value'=>$v['organization'], 'width'=>50),
                    array('value'=>$v['secretary'], 'width'=>25),

                );
            }

            echo json_encode(array('status'=>200,'data'=>array('title'=>'未开展党支部','head'=>$head,'list'=>$item)));
        }


    }


    //已缴纳党费
    public function moneyList($type)
    {

        header("Content-type:text/html;charset=utf-8");
        if($type == 1){
            $id = I('BRANCH_ID');
//            if(!S('dzz_yi_money_'.$id)){
            $url = 'http://www.dysfz.gov.cn/apiXC/branchfeeList.do'; //党员党建
            $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
            $da['COUNT'] = 200;
            $da['START'] = 1;
            $da['BRANCH_ID'] = $id;
            $das = json_encode($da);
            $list = $this->httpjson($url, $das);
            dump($list);die;
//                S('dzz_yi_money_'.$id,$list);
//            }else{
//                $list = S('dzz_yi_money_'.$id);;// 获取缓存
//            }

            $i = 1;
            foreach ($list['data'] as $k=>$v){
                $ii = $i++;
                $item[] = array(
                    array('value'=>$ii, 'width'=>25),
                    array('value'=>$v['name'], 'width'=>50),
                    array('value'=>$v['money'], 'width'=>25),
                );
            }

            $head = array(
                array('name'=>'序号', 'width'=>25),
                array('name'=>'党支部', 'width'=>50),
                array('name'=>'缴纳费用(元)', 'width'=>25),
            );

            echo json_encode(array('status'=>200,'data'=>array('title'=>'已缴纳党费','head'=>$head,'list'=>$item)));
        }elseif ($type == 2){
            $id = I('BRANCH_ID');
            if(!S('dzz_no_money_'.$id)){
            $url = 'http://www.dysfz.gov.cn/apiXC/branchnofeeList.do'; //党员党建
            $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
            $da['COUNT'] = 200;
            $da['START'] = 1;
            $da['BRANCH_ID'] = $id;
            $das = json_encode($da);
            $list = $this->httpjson($url, $das);
                S('dzz_no_money_'.$id,$list);
            }else{
                $list = S('dzz_no_money_'.$id);;// 获取缓存
            }

            $i = 1;
            foreach ($list['data'] as $k=>$v){
                $ii = $i++;
                $item[] = array(
                    array('value'=>$ii, 'width'=>25),
                    array('value'=>$v['NAME'], 'width'=>50),
                    array('value'=>0, 'width'=>25),
                );
            }


            $head = array(
                array('name'=>'序号', 'width'=>25),
                array('name'=>'党支部', 'width'=>50),
                array('name'=>'缴纳费用(元)', 'width'=>25),
            );

            echo json_encode(array('status'=>200,'data'=>array('title'=>'未缴纳党费','head'=>$head,'list'=>$item)));
        }

    }


    //入党申请人二级列表
    public function  applyList()
    {
        $where['town_id'] = I('town_id');
        $where['status'] = 1;
        $data = M('dr_dzz_application_party')->where($where)->select();

        $head = array(
            array('name'=>'序号', 'width'=>10),
            array('name'=>'姓名', 'width'=>10),
            array('name'=>'性别', 'width'=>10),
            array('name'=>'出生日期', 'width'=>15),
            array('name'=>'学历', 'width'=>15),
            array('name'=>'手机号码', 'width'=>15),
            array('name'=>'所属党组织', 'width'=>25),
        );

        $i = 1;
        foreach ($data as $k=>$v){
            $ii = $i++;
            $item[] = array(
                array('value'=>$ii, 'width'=>10,'idCard'=>342401),
                array('value'=>$v['name'], 'width'=>10,'idCard'=>342401),
                array('value'=>$v['sex'], 'width'=>10,'idCard'=>342401),
                array('value'=>$v['birthday'], 'width'=>15,'idCard'=>342401),
                array('value'=>$v['education'], 'width'=>15,'idCard'=>342401),
                array('value'=>$v['phone'], 'width'=>15,'idCard'=>342401),
                array('value'=>$v['organization'], 'width'=>25,'idCard'=>342401),
            );
        }

        echo json_encode(array('status'=>200,'data'=>array('title'=>'党员详情','head'=>$head,'list'=>$item)));

    }

    //入党积极分子二级列表
    public function apply_activity()
    {
        $where['town_id'] = I('town_id');
        $where['status'] = 1;
        $data = M('dr_dzz_activity_dy')->where($where)->select();
        $head = array(
            array('name'=>'序号', 'width'=>15),
            array('name'=>'姓名', 'width'=>15),
            array('name'=>'性别', 'width'=>10),
            array('name'=>'出生日期', 'width'=>20),
            array('name'=>'学历', 'width'=>15),
            array('name'=>'所属党组织', 'width'=>25),
        );

        $i = 1;
        foreach ($data as $k=>$v){
            $ii = $i++;
            $item[] = array(
                array('value'=>$ii, 'width'=>15,'idCards'=>342401,'idCard'=>true),
                array('value'=>$v['name'], 'width'=>15,'idCards'=>342401,'idCard'=>true),
                array('value'=>$v['sex'], 'width'=>10,'idCards'=>342401,'idCard'=>true),
                array('value'=>$v['birthday'], 'width'=>20,'idCards'=>342401,'idCard'=>true),
                array('value'=>$v['education'], 'width'=>15,'idCards'=>342401,'idCard'=>true),
                array('value'=>$v['organization'], 'width'=>25,'idCards'=>342401,'idCard'=>true),
            );
        }

        echo json_encode(array('status'=>200,'data'=>array('title'=>'党员详情','head'=>$head,'list'=>$item)));
    }

    //党员
    public function  dy_ztdr()
    {
        $url = 'http://www.dysfz.gov.cn/apiXC/recordPartyDTDRlist.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 100;
        $da['START'] = 1;
        $das = json_encode($da);
        $data = $this->httpjson($url, $das);
        dump($data);die;
    }

    //党员---主题党日二级列表
    public function ztdrList()
    {
        set_time_limit(0);
        $type = I('type');
        $id = I('branch_id');
        if($type == 1){
            if(!S('dzz_dy_ztdr_1_'.$id)){
            $url = 'http://www.dysfz.gov.cn/apiXC/zbRecordDTDRlistPage.do'; //党员党建
            $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
            $da['COUNT'] = 100;
            $da['START'] = 1;
            $da['BRANCH_ID'] = $id;
            $das = json_encode($da);
            $list = $this->httpjson($url, $das);
                S('dzz_dy_ztdr_1_'.$id,$list);   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $list = S('dzz_dy_ztdr_1_'.$id);// 获取缓存
            }
            $head = array(
                array('name'=>'序号', 'width'=>15),
                array('name'=>'党支部', 'width'=>30),
                array('name'=>'书记', 'width'=>15),
                array('name'=>'人数', 'width'=>15),
                array('name'=>'地址', 'width'=>25),
            );

            $i = 1;
            foreach ($list['data'] as $k=>&$v){
                $ii = $i++;
                $item[] = array(
                    array('value'=>$ii, 'width'=>15,'type'=>1,),
                    array('value'=>$v['NAME'], 'width'=>30,'type'=>1,),
                    array('value'=>$v['SECRETARY'], 'width'=>15,'type'=>1,),
                    array('value'=>$v['num'], 'width'=>15,'type'=>1,'dyztdr2Id'=>$v['BRANCH_ID'],'clickable'=>true),
                    array('value'=>$v['ADDRESS'], 'width'=>25,'type'=>1,),
                );
            }

            echo json_encode(array('status'=>200,'data'=>array('title'=>'本月主题党日开展情况','head'=>$head,'list'=>$item)));
        }elseif ($type == 2){
            set_time_limit(0);
            if(!S('dzz_no_dy_ztdr_1_'.$id)){
            $url = 'http://www.dysfz.gov.cn/apiXC/zbRecordDTDRlistPage.do'; //党员党建
            $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
            $da['COUNT'] = 100;
            $da['START'] = 1;
            $da['BRANCH_ID'] = $id;
            $das = json_encode($da);
            $list = $this->httpjson($url, $das);
                S('dzz_no_dy_ztdr_1_'.$id,$list);   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $list = S('dzz_no_dy_ztdr_1_'.$id);// 获取缓存
            }
            $head = array(
                array('name'=>'序号', 'width'=>15),
                array('name'=>'党支部', 'width'=>30),
                array('name'=>'书记', 'width'=>15),
                array('name'=>'人数', 'width'=>15),
                array('name'=>'地址', 'width'=>25),
            );

            $i = 1;
            foreach ($list['data'] as $k=>&$v){
                $ii = $i++;
                $item[] = array(
                    array('value'=>$ii, 'width'=>15,'type'=>2,),
                    array('value'=>$v['NAME'], 'width'=>30,'type'=>2,),
                    array('value'=>$v['SECRETARY'], 'width'=>15,'type'=>2,),
                    array('value'=>$v['partyNum']-$v['num'], 'width'=>15,'type'=>2,'dyztdr2Id'=>$v['BRANCH_ID'],'clickable'=>true),
                    array('value'=>$v['ADDRESS'], 'width'=>25,'type'=>2,),
                );
            }

            echo json_encode(array('status'=>200,'data'=>array('title'=>'本月主题党日未开展情况','head'=>$head,'list'=>$item)));
        }


    }

    //党员---主题党日第三层

    public function dt_ztdr_detail()
    {
        set_time_limit(0);
        $id = I('branch_id');
        $type = I('type');
        if($type == 1){
            if(!S('dzz_dy_ztdr_detail_'.$id)){
            $url = 'http://www.dysfz.gov.cn/apiXC/zbPartyRecordDTDRDetailslistPage.do'; //党员党建
            $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
            $da['COUNT'] = 100;
            $da['START'] = 1;
            $da['BRANCH_ID'] = $id;
            $da['TYPE'] = $type;
            $das = json_encode($da);
            $list = $this->httpjson($url, $das);
            S('dzz_dy_ztdr_detail_'.$id,$list);   // 写入缓存，expire'=>600 :  设置有效时间：600秒
        }else{
            $list = S('dzz_dy_ztdr_detail_'.$id);// 获取缓存
        }
        }elseif ($type == 2){
            if(!S('dzz_no_dy_ztdr_detail_'.$id)){
                $url = 'http://www.dysfz.gov.cn/apiXC/zbPartyRecordDTDRDetailslistPage.do'; //党员党建
                $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
                $da['COUNT'] = 100;
                $da['START'] = 1;
                $da['BRANCH_ID'] = $id;
                $da['TYPE'] = $type;
                $das = json_encode($da);
                $list = $this->httpjson($url, $das);
                S('dzz_no_dy_ztdr_detail_'.$id,$list);   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $list = S('dzz_no_dy_ztdr_detail_'.$id);// 获取缓存
            }
        }

        $head = array(
            array('name'=>'序号', 'width'=>15),
            array('name'=>'姓名', 'width'=>15),
            array('name'=>'参与活动所在党组织', 'width'=>30),
            array('name'=>'书记', 'width'=>15),
            array('name'=>'地址', 'width'=>25),
        );

        $i = 1;
        foreach ($list['data'] as $k=>$v){
            $ii = $i++;
            $item[] = array(
                array('value'=>$ii, 'width'=>15),
                array('value'=>$v['PARTYNAME'], 'width'=>15),
                array('value'=>$v['BRANCHNAME'], 'width'=>30),
                array('value'=>$v['SECRETARY'], 'width'=>15),
                array('value'=>$v['ADDRESS'], 'width'=>25),
            );
        }

        if($type == 1){
            echo json_encode(array('status'=>200,'data'=>array('title'=>'本月已参加主题党日党员信息','head'=>$head,'list'=>$item)));
        }elseif ($type == 2)
        echo json_encode(array('status'=>200,'data'=>array('title'=>'本月未参加主题党日党员信息','head'=>$head,'list'=>$item)));
    }


    public function party_money()
    {
        $url = 'http://www.dysfz.gov.cn/apiXC/getHeldZTDRlistPage.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 300;
        $da['START'] = 1;
        $das = json_encode($da);
        $list = $this->httpjson($url, $das);
        $arr = array_column($list['data'],'ykz');
        $ztdr = array_sum($arr);
        dump($ztdr);die;
    }

    public function honor1()
    {
        $url = 'http://www.dysfz.gov.cn/apiXC/dwPartylistPage.do';
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 150;
        $da['START'] = 1;
        $das = json_encode($da);
        $list = $this->httpjson($url, $das);
        dump($list);die;

    }


    /**
     * 根据步骤不同，data具体信息的描述如下：
    步骤1、2时：
    返回值为branch_id 组织id，name 组织名，number 获得评优的总数
    步骤3时：
    NAME指的是党员的名字，
    对于type=1的评优来说，下面是返回字段的详细解释
    TYPE：评优类别1.优秀共产党员 2先进党务工作者 3其他
    LEVEL：评级等级 1全国 2省 3市 4县
    FLASHDETAIL：闪光言论
    FLASHWORDS：闪光言行记录 1.一季度 2.二季度 3.三季度 4.四季度
    STAR：星级
     */
    public function honorList()
    {
        set_time_limit(3600);
        $id = I('BRANCH_ID');
        $type = I('type');  //1、闪光言行 2、评优评先

        if(!S('light_'.$id)){
        $url = 'http://www.dysfz.gov.cn/apiXC/getEvaluateByTypeAndStep.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 500;
        $da['START'] = 1;
        $da['BRANCH_ID'] = $id;
        $da['TYPE'] = 1; // 1为评优评先，2为党员处分
        $da['STEP'] = 2; //查询一共分为3步，分别为1、2、3
        $das = json_encode($da);
        $list = $this->httpjson($url, $das);
            S('light_'.$id,$list);
        }else{
            $list = S('light_'.$id);
        }
        $head = array(
            array('name'=>'序号', 'width'=>20),
            array('name'=>'党支部', 'width'=>50),
            array('name'=>'获得评优数', 'width'=>30),
        );

        if($type == 1){
            $i = 1;
            foreach ($list['data'] as $k=>$v){
                $ii = $i++;
                $item[] = array(
                    array('value'=>$ii, 'width'=>20,'type'=>1),
                    array('value'=>$v['NAME'], 'width'=>50,'type'=>1),
                    array('value'=>$v['NUMBER'], 'width'=>30,'honorId'=>$v['BRANCH_ID'],'type'=>1,'clickable'=>true),

                );
            }
        }
        elseif ($type == 2){
            $i = 1;
            foreach ($list['data'] as $k=>$v){
                $ii = $i++;
                $item[] = array(
                    array('value'=>$ii, 'width'=>20,'type'=>2),
                    array('value'=>$v['NAME'], 'width'=>50,'type'=>2),
                    array('value'=>$v['NUMBER'], 'width'=>30,'honorId'=>$v['BRANCH_ID'],'type'=>2,'clickable'=>true),

                );
            }
        }
            echo json_encode(array('status'=>200,'data'=>array('title'=>'党支部列表','head'=>$head,'list'=>$item)));
      }

      public function  honorDetail()
      {
          set_time_limit(3600);

          $id = I('BRANCH_ID');
          $type = I('type');  //1、闪光言行 2、评优评先
          if(!S('light_light_'.$id)) {
              $url = 'http://www.dysfz.gov.cn/apiXC/getEvaluateByTypeAndStep.do'; //党员党建
              $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
              $da['COUNT'] = 500;
              $da['START'] = 1;
              $da['BRANCH_ID'] = $id;
              $da['TYPE'] = 1; // 1为评优评先，2为党员处分
              $da['STEP'] = 3; //查询一共分为3步，分别为1、2、3
              $das = json_encode($da);
              $list = $this->httpjson($url, $das);
              S('light_light_'.$id,$list);

          }else{
              $list = S('light_light_'.$id);
          }

          if($type == 1){
              $head = array(
                  array('name'=>'序号', 'width'=>10),
                  array('name'=>'姓名', 'width'=>10),
                  array('name'=>'闪光言论', 'width'=>40),
                  array('name'=>'闪光言行记录', 'width'=>15),
                  array('name'=>'星级', 'width'=>10),
                  array('name'=>'时间', 'width'=>15),
              );

              $i = 1;
              foreach ($list['data'] as $k=>&$v){
                  $v['TIME'] = date('Y-m-d',strtotime($v['TIME']));
                  if($v['FLASHWORDS'] == 1){
                      $v['FLASHWORDS'] = '一季度' ;
                  }elseif ($v['FLASHWORDS'] == 2){
                      $v['FLASHWORDS'] = '二季度' ;
                  }elseif ($v['FLASHWORDS'] == 3){
                      $v['FLASHWORDS'] = '三季度' ;
                  }elseif ($v['FLASHWORDS'] == 4){
                      $v['FLASHWORDS'] = '四季度' ;
                  }elseif ($v['FLASHWORDS'] == 5){
                      $v['FLASHWORDS'] = '年度' ;
                  }
                  $ii = $i++;
                  $item[] = array(
                      array('value'=>$ii, 'width'=>10),
                      array('value'=>$v['NAME'], 'width'=>10),
                      array('value'=>$v['FLASHDETAIL'], 'width'=>40),
                      array('value'=>$v['FLASHWORDS'], 'width'=>15),
                      array('value'=>$v['STAR'].'星', 'width'=>10),
                      array('value'=>$v['TIME'], 'width'=>15),
                  );
              }
          }
          elseif ($type == 2){

              $head = array(
                  array('name'=>'序号', 'width'=>10),
                  array('name'=>'姓名', 'width'=>20),
                  array('name'=>'评优类别', 'width'=>30),
                  array('name'=>'评级等级', 'width'=>20),
                  array('name'=>'时间', 'width'=>20),
              );

              $i = 1;
              foreach ($list['data'] as $k=>&$v){
                  $v['TIME'] = date('Y-m-d',strtotime($v['TIME']));
                  if($v['TYPE'] == 1){
                        $v['TYPE'] = '优秀共产党员';
                  }elseif ($v['TYPE'] == 2){
                        $v['TYPE'] = '先进党务工作者';
                  }elseif ($v['TYPE'] == 3){
                      $v['TYPE'] = '其他';
                  }

                  if($v['LEVEL'] == 1){
                      $v['LEVEL'] = '全国';
                  }elseif ($v['LEVEL'] == 2){
                      $v['LEVEL'] = '省';
                  }elseif ($v['LEVEL'] == 3){
                      $v['LEVEL'] = '市';
                  }elseif ($v['LEVEL'] == 4){
                      $v['LEVEL'] = '县';
                  }elseif ($v['LEVEL'] == 5){
                      $v['LEVEL'] = '县级以下';
                  }
                  $ii = $i++;
                  $item[] = array(
                      array('value'=>$ii, 'width'=>10),
                      array('value'=>$v['NAME'], 'width'=>20),
                      array('value'=>$v['TYPE'], 'width'=>30),
                      array('value'=>$v['LEVEL'], 'width'=>20),
                      array('value'=>$v['TIME'], 'width'=>20),
                  );
              }
          }

          echo json_encode(array('status'=>200,'data'=>array('title'=>'人员名单','head'=>$head,'list'=>$item)));
      }


    public function partyList()
    {
        set_time_limit(0);

        $url = 'http://www.dysfz.gov.cn/apiXC/branchnofeeList.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 1500;
        $da['START'] = 1;
//        $da['BRANCH_ID'] = 1145;
        $das = json_encode($da);
        $list = $this->httpjson($url, $das);
        dump($list);die;
    }

    //党员信息
    public function member()
    {
        //党员总数
//        $meb = $this->ajax_user->count();
        $meb = 35814;
       //男性所占比率
        $man = $this->ajax_user->where(array('SEX'=>'男'))->count();
//        $man_rate = (round(($man/$meb),2)*100).'%';

        //女性所占比率
//        $woman = $this->ajax_user->where(array('SEX'=>'女'))->count();
//        $woman_rate = (round(($woman/$meb),2)*100).'%';
        $woman = $meb-$man;

        //党员学历分布人数
        $degree = $this->ajax_user->query("SELECT DEGREE, COUNT(*) as num  FROM cxdj_ajax_user GROUP BY DEGREE");
        foreach ($degree as $k=>&$v){

            if($v['DEGREE'] == 2){
                $v['DEGREE'] = '研究生';
                $num1 = (int)$v['num'];
            }elseif ($v['DEGREE'] == 3){
                $v['DEGREE'] = '本科';
                $num2 = (int)$v['num'];
            }elseif ($v['DEGREE'] == 4){
                $v['DEGREE'] = '大专';
                $num3 = (int)$v['num'];
            }elseif ($v['DEGREE'] == 5){
                $v['DEGREE'] = '高中';
                $num4 = (int)$v['num'];
            }


        }
        $num4 = $meb-$num1-$num2-$num3;

        //党员年纪分布
//        $age1 = $this->ajax_user->where('0 < age18 AND age18 < 35')->count(); //30岁以下
        $age1 =8813; //35岁以下
//        $age2 = $this->ajax_user->where('age18>=35 AND age18 < 50')->count(); //30岁到40岁
        $age2 =$meb-$age1-$age4-$age5; //30岁到40岁
//        $age4 = $this->ajax_user->where('age18>=50 AND age18 < 60')->count(); //50岁到60岁
        $age4 = 6902; //50岁到60岁
//        $age5 = $this->ajax_user->where('age18>=60')->count(); //60岁以上
        $age5 = 9742; //60岁以上

        $age = array((int)$age1,(int)$age2,(int)$age4,(int)$age5);

        $num =array($num4,$num3,$num2,$num1);


        echo json_encode(array('status'=>1,'msg'=>1,
            'data'=>array('meb'=>(int)$meb,
                'man'=>(int)$man,
                'woman'=>(int)$woman,
                'degree'=>array('num'=>$num),
                'age'=>array('num'=>$age)
            )));
    }

    public function memberRecord()
    {
        $head = array(  //党员详情
            array('name'=>'序号', 'width'=>20),
            array('name'=>'党员姓名', 'width'=>20),
            array('name'=>'所属党组织', 'width'=>40),
            array('name'=>'活动记录数', 'width'=>20),

        );


            if(!S('meb_record')){
                $meb = $this->ajax_user->query("SELECT DISTINCT (@i:=@i+1) id,`NAME`,PARTY_ID,BRANCH_NAME,'' AS `time` FROM cxdj_ajax_user,(SELECT @i:=0) AS i LIMIT 1,100");
                $time = $this->time;  //缓存三天
                S('meb_record',$meb,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $meb = S('meb_record');// 获取缓存
            }


            foreach ($meb as $k=>&$v) {
                $url = 'http://www.dysfz.gov.cn/apiXC/ativityRecordCount.do';
                $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
                $da['PARTY_ID'] = $v['PARTY_ID'];

                $das = json_encode($da);
                $list = $this->httpjson($url, $das);
                $v['COUNT'] = $list['data'][0]['count(ACTIVITY_ID)'];
                if (empty($v['COUNT'])) {
                    $v['COUNT'] = 0;
                }
            }

            array_multisort(array_column($meb,'COUNT'),SORT_DESC,$meb); //数组排序

            $i = 1;
            foreach ($meb as $k=>$v){
                $ii = $i++;
                $data[] = array(
                    array('value'=>$ii,'width'=>20,'ID'=>$v['PARTY_ID'],'type'=>'activity'),
                    array('value'=>$v['NAME'],'width'=>20,'ID'=>$v['PARTY_ID'],'type'=>'activity'),
                    array('value'=>$v['BRANCH_NAME'],'width'=>40,'ID'=>$v['PARTY_ID'],'type'=>'activity'),
                    array('value'=>$v['COUNT'],'width'=>20,'ID'=>$v['PARTY_ID'],'type'=>'activity'),
                );
            }


        $ds['data']['list']= $data;
        $ds['data']['title']='党员基本信息';
        $ds['data']['head']=$head;
        $ds['status']=200;
        echo json_encode($ds);
    }

    ///党员活动详情记录
    public function memberRecord2($id)
    {
        $head1 = array(  //活动详情
            array('name'=>'序号', 'width'=>5),
            array('name'=>'党组织名称', 'width'=>20),
            array('name'=>'活动主题', 'width'=>20),
            array('name'=>'活动内容', 'width'=>40),
            array('name'=>'开始时间', 'width'=>15)
        );

        $url = 'http://www.dysfz.gov.cn/apiXC/ativityRecordlist.do';
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['PARTY_ID'] = $id;
        $da['COUNT'] = 1500;
        $da['START'] = 1;

        $das = json_encode($da);
        $list = $this->httpjson($url,$das);
        $i = 1;
        foreach ($list['data'] as $k=>$v){
            $ii = $i++;
            $v['BEGINTIME'] = date('Y-m-d',strtotime($v['BEGINTIME']));
            $data[] = array(
                array('value'=>$ii,'width'=>5),
                array('value'=>$v['BRANCHNAME'],'width'=>20),
                array('value'=>$v['ACTIVITYNAME'],'width'=>20),
                array('value'=>$v['ACTIVITYCONTENT'],'width'=>40),
                array('value'=>$v['BEGINTIME'],'width'=>15),
            );
        }
        echo json_encode(array('status'=>200,'data'=>array('title'=>'活动详细记录','head'=>$head1,'list'=>$data)));

    }


    //爱心众筹
    public function loveList()
    {
        $where['TYPE'] = 2;
        $where['sh_state'] = 1;


           //爱心众筹总数
           $count = $this->ajax_volunteer->where($where)->count();

           $love_y = $this->ajax_volunteer->where(array('sh_state'=>1,'TYPE'=>2))->field('UNIX_TIMESTAMP(END_TIME) as time,MONEY,sMoney,VOLUNTEER_ID,BEGIN_TIME')->select();
           //众筹已完成
            $i = 1;
            foreach($love_y as $k=>&$v){
                $v['rate'] = ($v['sMoney']/$v['MONEY']);
                if( time() > $v['time'] && $v['rate'] != 0){
                    $ii = $i++;
                }
                $v['BEGIN_TIME'] = substr($v['BEGIN_TIME'],0,10);
            }

        //众筹未完成
         $love_w = (int)$count - (int)$ii;


//           $love_w = $this->ajax_volunteer->where(array('sh_state'=>1,'TYPE'=>2,'STATE'=>array('in','1,2')))->count();

           $loveList = $this->ajax_volunteer->where($where)->field("VOLUNTEER_ID,NAME as title,CONTENT as text,STATE,MONEY,sMoney,BEGIN_TIME,BRANCH_NAME")->order('id DESC')->select();

            foreach ($loveList as $k=>&$v){
                if(($v['sMoney']/$v['MONEY']) > 1){
                    $v['Percentage'] =100;
                }else{
                    $v['Percentage'] = (round(($v['sMoney']/$v['MONEY']),2)*100);
                }
                $v['zcType'] =2;
                $v['BEGIN_TIME'] = substr($v['BEGIN_TIME'],0,10);

            }

          echo json_encode(array('data'=>array('complete'=>(int)$ii,'implement'=>(int)$love_w,'list'=>$loveList),));
//          echo json_encode(array('data'=>array('complete'=>18,'implement'=>0,'list'=>$loveList),));



    }


    //志愿服务
    public function volunteer()
    {
        //志愿服务总数
        $volunteer = $this->ajax_volunteer->where(array('TYPE'=>1))->count();
        //志愿者服务记录
        $volunteerList = $this->ajax_volunteer->where(array('TYPE'=>1))->field('NAME,BEGIN_TIME')->order('BEGIN_TIME DESC')->limit(10)->select();

        foreach ($volunteerList as $k=>&$v){
            $v['BEGIN_TIME'] = date("Y-m-d",strtotime($v['BEGIN_TIME']));
        }

        echo json_encode(array('status'=>1,'msg'=>'请求成功','data'=>array('sum'=>$volunteer,'list'=>$volunteerList)));
    }

    public function volunteerRecord()
    {
        $volunteerList = $this->ajax_volunteer->where(array('TYPE'=>1))->field('VOLUNTEER_ID,NAME,BRANCH_NAME,CONTENT,PHONE')->order('BEGIN_TIME DESC')->limit(300)->select();
        $head = array(
            array('name'=>'主题','width'=>20),
            array('name'=>'发布单位','width'=>20),
            array('name'=>'发布内容','width'=>40),
            array('name'=>'电话','width'=>20),
        );

        foreach($volunteerList as $k=>$v)
        {
            $data[] = array(
                array('value'=>$v['NAME'],'width'=>20,'wxyId'=>$v['VOLUNTEER_ID']),
                array('value'=>$v['BRANCH_NAME'],'width'=>20,'wxyId'=>$v['VOLUNTEER_ID']),
                array('value'=>$v['CONTENT'],'width'=>40,'wxyId'=>$v['VOLUNTEER_ID']),
                array('value'=>$v['PHONE'],'width'=>20,'wxyId'=>$v['VOLUNTEER_ID']),
            );
        }

        echo json_encode(array('status'=>200,'data'=>array('title'=>'志愿服务','head'=>$head,'list'=>$data)));

    }

    public function wxy()
    {

        //微心愿总数
        $wxy_count = $this->ajax_volunteer->where(array('TYPE'=>3,'sh_state'=>1))->count();

        //已认领
        $already = $this->ajax_volunteer->where(array('TYPE'=>3,'STATE1'=>'结束'))->count();

        //正在进行
        $wxy_ing = $this->ajax_volunteer->where(array('TYPE'=>3,'STATE1'=>'正在进行中'))->count();
        //未实现
        $wxy_wei = $this->ajax_volunteer->where(array('TYPE'=>3,'STATE1'=>'未开始'))->count();

        echo json_encode(array('status'=>200,'data'=>array('count'=>(int)$wxy_count,'already'=>(int)$already,'wei'=>(int)$wxy_wei,'wxy_ing'=>(int)$wxy_ing)));

    }

    public function wxyRecord()
    {
        //微心愿记录
        $wxyList = $this->ajax_volunteer->where(array('TYPE'=>3,'sh_state'=>1))->order('END_TIME DESC')->select();
        $head = array(
            array('name'=>'主题','width'=>20),
            array('name'=>'发布单位','width'=>20),
            array('name'=>'联系人','width'=>20),
            array('name'=>'电话','width'=>20),
            array('name'=>'地址','width'=>20),
        );

        foreach ($wxyList as $k=>&$v){
            $data[] = array(
                array('value'=>$v['NAME'],'width'=>20,'wxyId'=>$v['VOLUNTEER_ID']),
                array('value'=>$v['BRANCH_NAME'],'width'=>20,'wxyId'=>$v['VOLUNTEER_ID']),
                array('value'=>$v['FROM_NAME'],'width'=>20,'wxyId'=>$v['VOLUNTEER_ID']),
                array('value'=>$v['PHONE'],'width'=>20,'wxyId'=>$v['VOLUNTEER_ID']),
                array('value'=>$v['ADDRESS'],'width'=>20,'wxyId'=>$v['VOLUNTEER_ID']),
            );
        }
        echo json_encode(array('status'=>200,'data'=>array('title'=>'微心愿','head'=>$head,'list'=>$data)));

    }

    //微心愿
    public function wxy_party()
    {
        $type = I('type');
        if($type == 2){
            $where['VOLUNTEER_ID'] = I('VOLUNTEER_ID');
            $head = array(
                array('name'=>'序号','width'=>30),
                array('name'=>'姓名','width'=>20),
                array('name'=>'金额','width'=>20),
                array('name'=>'参与时间','width'=>30),
            );
            $wxy_party = M('volunteer_party')->where($where)->select();
            $i = 1;
            foreach ($wxy_party as $k=>&$v){
                $ii = $i++;
                $v['time'] = date('Y-m-d',strtotime($v['CREATE_TIME']));
                $data[] = array(
                    array('value'=>$ii,'width'=>30),
                    array('value'=>$v['PARTY_NAME'],'width'=>20),
                    array('value'=>$v['MONEY'],'width'=>20),
                    array('value'=>$v['time'],'width'=>30),
                );
            }
            echo json_encode(array('status'=>200,'data'=>array('title'=>'参加人员','head'=>$head,'list'=>$data)));
        }else{
            $where['VOLUNTEER_ID'] = I('VOLUNTEER_ID');
            $head = array(
                array('name'=>'序号','width'=>30),
                array('name'=>'姓名','width'=>40),
                array('name'=>'参与时间','width'=>30),
            );
            $wxy_party = M('volunteer_party')->where($where)->select();
            $i = 1;
            foreach ($wxy_party as $k=>&$v){
                $ii = $i++;
                $v['time'] = date('Y-m-d',strtotime($v['CREATE_TIME']));
                $data[] = array(
                    array('value'=>$ii,'width'=>30),
                    array('value'=>$v['PARTY_NAME'],'width'=>40),
                    array('value'=>$v['time'],'width'=>30),
                );
            }
            echo json_encode(array('status'=>200,'data'=>array('title'=>'参加人员','head'=>$head,'list'=>$data)));
        }

    }
    //党员众创互助
    public function zc_help()
    {
        $where['TYPE'] = 4;
        $where['sh_state'] = 1;

            //众创互助总数
            $count = $this->ajax_volunteer->where($where)->count();
//            //众创互助完成
//            $love_y = $this->ajax_volunteer->where(array('sh_state'=>1,'STATE'=>3,'TYPE'=>4))->count();
//            //众创互助未完成
//            $love_w = $this->ajax_volunteer->where(array('sh_state'=>1,'STATE'=>array('in','1,2'),'TYPE'=>4))->count();

//            echo json_encode(array('status'=>1,'msg'=>'请求成功','data'=>array('count'=>$count,'y_count'=>$love_y,'w_count'=>$love_w)));
            //众创互助记录
            $zcList = $this->ajax_volunteer->where($where)->field('VOLUNTEER_ID,NAME as title,BRANCH_NAME,CONTENT as text,STATE,rCount,FROM_NAME,PEOPLENUMBER,UNIX_TIMESTAMP(END_TIME) as time')->order('id DESC')->select();

            //众创互助已完成
            $i =  1;
            foreach ($zcList as $k=>&$v){
                $v['Percentage'] = (round(($v['rCount']/$v['PEOPLENUMBER']),2)*100);
                if(time() > $v['time'] && $v['Percentage'] !=0){
                    $ii = $i++;

                }
            }

            //众创互助未完成
            $zh_w = (int)$count - (int)$ii;

           echo json_encode(array('data'=>array('complete'=>(int)$ii,'implement'=>(int)$zh_w,'list'=>$zcList),));

    }


    //网格事件
    public function WG()
    {
        set_time_limit(900);
        $name = I('name');
        $Model = M('ajax_jiedao_total');

        //获取街道每年的事件总数
        if(!S('event_all')){
            $event_all = $Model->select();
            $time = $this->time;  //缓存三天
            S('event_all',$event_all,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
        }else{
            $event_all = S('event_all');// 获取缓存
        }

//        //获取分类每月红色网格员处理事件总数
//        if(!S('event_all_month')){
//            $event_all_month = M('jiedao_dy_month')->select();
//            $time = $this->time;  //缓存三天
//            S('event_all_month',$event_all_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//        }else{
//            $event_all_month = S('event_all_month');// 获取缓存
//        }

        //获取分类每年红色网格员处理事件总数
        if(!S('event_all_year')){
            $event_all_year = M('jiedao_dy')->select();
            $time = $this->time;  //缓存三天
            S('event_all_year',$event_all_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
        }else{
            $event_all_year = S('event_all_year');// 获取缓存
        }

        $Model1 = M('jiedao_dy');
        if($name == 1){
            $Model = M();
            //统计每个街道七大网格事件
            /***************************************************************县当年数据************************************************************************/
            if(!S('event_year')){
                $event_year = M('event_year')->select();
                $time = $this->time;  //缓存三天
                S('event_year',$event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $event_year = S('event_year');// 获取缓存
            }


            foreach ($event_year[0] as $k=>&$v){
                $data[] = (int)$v;
            }

            $event_year = $data;
            /**********************************************************************县当月数据*****************************************************************************************************/
            if(!S('event_month')){
                $event_month = M('event_month')->select();
                $time = $this->time;  //缓存三天
                S('event_month',$event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $event_month = S('event_month');// 获取缓存
            }

            foreach ($event_month[0] as $k=>&$v){
                $item[] = (int)$v;
            }

            $event_month = $item;

            /****************************************************************************网格事件当年总数***************************************************************************************/
            $WG = $Model->query("SELECT
                                COUNT(*) AS `count`
                            FROM
                                cxdj_ajax_event
                            WHERE
                             DATE_FORMAT(HAPPEN_TIME, '%Y') = DATE_FORMAT(NOW(), '%Y')");

            //查询交通、公共、公安、消防、卫计、国土，每年事件总和
            $category_count = $Model->query("SELECT (jt_year+public_year+police_year+xf_year+wj_year+gt_year) AS other FROM cxdj_event_year ");

            //（红色网格员数处理事件数）
            $Red_dy = $Model->query("SELECT COUNT(*) AS `count` FROM cxdj_ajax_event WHERE DATE_FORMAT(HAPPEN_TIME, '%Y') = DATE_FORMAT(NOW(), '%Y')  AND DEAL_USER__IS_DY = 1");
            //其他分类每年红色网格员处理事件总数
            $other_year = (int)$Red_dy[0]['count'] - (int)$category_count[0]['other'];
            $event_year[] = $other_year;
            //事件当月总数
            $month_count = $Model->query("SELECT COUNT(*) AS `count` FROM cxdj_ajax_event WHERE DATE_FORMAT(HAPPEN_TIME, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')  AND DEAL_USER__IS_DY = 1");
           //查询交通、公共、公安、消防、卫计、国土，每月事件总和
            $category_month_count = $Model->query("SELECT (jt_moth+public_month+police_month+xf_month+wj_month+gt_month) AS other FROM cxdj_event_month");
            //其他分类每月红色网格员处理事件总数
            $other_month = $month_count[0]['count'] - $category_month_count[0]['count'];
            $event_month[] = $other_month;

            //普通网格员/群众处理事件数
            $people = (int)$WG[0]['count'] - (int)$Red_dy[0]['count'];
            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$event_year,'event_month'=>$event_month,'total'=>(int)$WG[0]['count'],'dy'=>(int)$Red_dy[0]['count'],'fdy'=>$people)));
        }elseif($name == '雉城街道'){

            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total');// 获取缓存
//            }

//            if(!S('jd_dy')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[0]['count'] - $event_all_year[0]['count'];

            if(!S('town_event_year')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[0]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town; //将其他红色网格员处理事件数重新赋给数组中

            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[0]['count'],'dy'=>(int)$event_all_year[0]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '和平镇'){
            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total_1')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total_1',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total_1');// 获取缓存
//            }

//            if(!S('jd_dy_1')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_1',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_1');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[1]['count'] - $event_all_year[1]['count'];

            if(!S('town_event_year_1')){
                $town_event_year = $Model->query("SELECT
                                            (
                                                SELECT
                                                    count(0) AS `count`
                                                FROM
                                                    `cxdj_ajax_event` AS `event`
                                                JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                WHERE
                                                    (
                                                        (
                                                            `event`.`CATEGORY1` = '交通运输类'
                                                        )
                                                        AND (
                                                            date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                        )
                                                        AND (
                                                            `event`.`DEAL_USER__IS_DY` = 1
                                                        )
                                                    )
                                                AND XZ_DEPARTMENTNAME = '$name'
                                            ) AS jt_year,
                                            (
                                                SELECT
                                                    count(0) AS `count`
                                                FROM
                                                    `cxdj_ajax_event` AS `event`
                                                JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                WHERE
                                                    (
                                                        (
                                                            `event`.`CATEGORY1` = '公共事业类'
                                                        )
                                                        AND (
                                                            date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                        )
                                                        AND (
                                                            `event`.`DEAL_USER__IS_DY` = 1
                                                        )
                                                    )
                                                AND XZ_DEPARTMENTNAME = '$name'
                                            ) AS public_year,
                                            (
                                                SELECT
                                                    count(0) AS `count`
                                                FROM
                                                    `cxdj_ajax_event` AS `event`
                                                JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                WHERE
                                                    (
                                                        (
                                                            `event`.`CATEGORY1` = '公安类'
                                                        )
                                                        AND (
                                                            date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                        )
                                                        AND (
                                                            `event`.`DEAL_USER__IS_DY` = 1
                                                        )
                                                    )
                                                AND XZ_DEPARTMENTNAME = '$name'
                                            ) AS police_year,
                                            (
                                                SELECT
                                                    count(0) AS `count`
                                                FROM
                                                    `cxdj_ajax_event` AS `event`
                                                JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                WHERE
                                                    (
                                                        (
                                                            `event`.`CATEGORY1` = '消防类'
                                                        )
                                                        AND (
                                                            date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                        )
                                                        AND (
                                                            `event`.`DEAL_USER__IS_DY` = 1
                                                        )
                                                    )
                                                AND XZ_DEPARTMENTNAME = '$name'
                                            ) AS xf_year,
                                            (
                                                SELECT
                                                    count(0) AS `count`
                                                FROM
                                                    `cxdj_ajax_event` AS `event`
                                                JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                WHERE
                                                    (
                                                        (
                                                            `event`.`CATEGORY1` = '卫计类'
                                                        )
                                                        AND (
                                                            date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                        )
                                                        AND (
                                                            `event`.`DEAL_USER__IS_DY` = 1
                                                        )
                                                    )
                                                AND XZ_DEPARTMENTNAME = '$name'
                                            ) AS wj_year,
                                            (
                                                SELECT
                                                    count(0) AS `count`
                                                FROM
                                                    `cxdj_ajax_event` AS `event`
                                                JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                WHERE
                                                    (
                                                        (
                                                            `event`.`CATEGORY1` = '国土类'
                                                        )
                                                        AND (
                                                            date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                        )
                                                        AND (
                                                            `event`.`DEAL_USER__IS_DY` = 1
                                                        )
                                                    )
                                                AND XZ_DEPARTMENTNAME = '$name'
                                            ) AS gt_year
                                        FROM
                                            cxdj_ajax_event
                                        LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_1',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_1');// 获取缓存
            }
//            dump($town_event_year);die;
            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[1]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

            $town_event_year[] = $other_year_town; //将其他红色网格员处理事件数重新赋给数组中
            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_1')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_1',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_1');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[1]['count'],'dy'=>(int)$event_all_year[1]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '虹星桥镇'){
            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total_2')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total_2',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total_2');// 获取缓存
//            }

//            if(!S('jd_dy_2')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_2',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_2');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[2]['count'] - (int)$event_all_year[2]['count'];

            if(!S('town_event_year_2')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_2',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_2');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[2]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town; //将其他红色网格员处理事件数重新赋给数组中
            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_2')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_2',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_2');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[2]['count'],'dy'=>(int)$event_all_year[2]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '洪桥镇'){
            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total_3')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total_3',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total_3');// 获取缓存
//            }

//            if(!S('jd_dy_3')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_3',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_3');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[3]['count'] - (int)$event_all_year[3]['count'];

            if(!S('town_event_year_3')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_3',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_3');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[3]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town;
            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_3')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_3',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_3');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[3]['count'],'dy'=>(int)$event_all_year[3]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '画溪街道'){
            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total_4')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total_4',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total_4');// 获取缓存
//            }

//            if(!S('jd_dy_4')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_4',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_4');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[4]['count'] - (int)$event_all_year[4]['count'];

            if(!S('town_event_year_4')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_4',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_4');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[4]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town;
            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_4')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_4',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_4');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[4]['count'],'dy'=>(int)$event_all_year[4]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '小浦镇'){
            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total_5')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total_5',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total_5');// 获取缓存
//            }

//            if(!S('jd_dy_5')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_5',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_5');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[5]['count'] - (int)$event_all_year[5]['count'];

            if(!S('town_event_year_5')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_5',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_5');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[5]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town;
            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_5')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_5',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_5');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[5]['count'],'dy'=>(int)$event_all_year[5]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '夹浦镇'){
            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total_6')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total_6',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total_6');// 获取缓存
//            }

//            if(!S('jd_dy_6')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_6',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_6');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[6]['count'] - (int)$event_all_year[6]['count'];

            if(!S('town_event_year_6')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_6',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_6');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[6]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town;

            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_6')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_6',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_6');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[6]['count'],'dy'=>(int)$event_all_year[6]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '李家巷镇'){
            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total_7')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total_7',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total_7');// 获取缓存
//            }

//            if(!S('jd_dy_7')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_7',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_7');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[7]['count'] - (int)$event_all_year[7]['count'];

            if(!S('town_event_year_7')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_7',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_7');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[7]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town;

            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_7')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_7',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_7');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[7]['count'],'dy'=>(int)$event_all_year[7]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '林城镇'){
            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total_8')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total_8',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total_8');// 获取缓存
//            }

//            if(!S('jd_dy_8')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_8',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_8');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[8]['count'] - (int)$event_all_year[8]['count'];

            if(!S('town_event_year_8')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_8',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_8');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[8]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town;

            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_8')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_8',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_8');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[8]['count'],'dy'=>(int)$event_all_year[8]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '图影管委会'){
            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total_9')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total_9',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total_9');// 获取缓存
//            }

//            if(!S('jd_dy_9')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_9',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_9');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[9]['count'] - (int)$event_all_year[9]['count'];

            if(!S('town_event_year_9')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_9',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_9');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[9]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town;

            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_9')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_9',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_9');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[9]['count'],'dy'=>(int)$event_all_year[9]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '开发区(太湖街道)'){
            $name = '太湖街道';
            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total_10')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total_10',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total_10');// 获取缓存
//            }

//            if(!S('jd_dy_10')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_10',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_10');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[10]['count'] - (int)$event_all_year[10]['count'];

            if(!S('town_event_year_10')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_10',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_10');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[10]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town;
            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_10')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_10',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_10');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[10]['count'],'dy'=>(int)$event_all_year[10]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif($name == '龙山街道'){
            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total_11')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total_11',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total_11');// 获取缓存
//            }

//            if(!S('jd_dy_11')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_11',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_11');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[11]['count'] - (int)$event_all_year[11]['count'];

            if(!S('town_event_year_11')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_11',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_11');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[11]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town;

            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_11')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_11',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_11');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[11]['count'],'dy'=>(int)$event_all_year[11]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif($name == '吕山乡'){
            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total_12')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total_12',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total_12');// 获取缓存
//            }

//            if(!S('jd_dy_12')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_12',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_12');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[12]['count'] - (int)$event_all_year[12]['count'];

            if(!S('town_event_year_12')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_12',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_12');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[12]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town;

            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_12')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_12',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_12');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[12]['count'],'dy'=>(int)$event_all_year[12]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '煤山镇'){


//            if(!S('jd_dy_13')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_13',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_13');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[13]['count'] - (int)$event_all_year[13]['count'];

            if(!S('town_event_year_13')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_13',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_13');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[13]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town;

            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_13')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_13',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_13');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[13]['count'],'dy'=>(int)$event_all_year[13]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '南太湖管委会'){
            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total_14')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total_14',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total_14');// 获取缓存
//            }

//            if(!S('jd_dy_14')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_14',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_14');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[14]['count'] - (int)$event_all_year[14]['count'];

            if(!S('town_event_year_14')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_14',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_14');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[14]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town;

            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_14')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_14',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_14');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[14]['count'],'dy'=>(int)$event_all_year[14]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '水口乡'){
            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total_15')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total_15',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total_15');// 获取缓存
//            }

//            if(!S('jd_dy_15')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_15',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_15');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[15]['count'] - (int)$event_all_year[15]['count'];

            if(!S('town_event_year_15')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_15',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_15');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[15]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town;

            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_15')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_15',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_15');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[15]['count'],'dy'=>(int)$event_all_year[15]['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '泗安镇'){
            /********************************************************************街道当年数据*****************************************************************************/
//            if(!S('jd_total_16')){
//                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
//                $time = $this->time;  //缓存三天
//                S('jd_total_16',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_total = S('jd_total_16');// 获取缓存
//            }

//            if(!S('jd_dy_16')){
//                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
//                $time = $this->time;  //缓存三天
//                S('jd_dy_16',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_dy = S('jd_dy_16');// 获取缓存
//            }

            $jd_fdy = (int)$event_all[16]['count'] - (int)$event_all_year[16]['count'];

            if(!S('town_event_year_16')){
                $town_event_year = $Model->query("SELECT
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '交通运输类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS jt_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公共事业类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS public_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '公安类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS police_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '消防类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS xf_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '卫计类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS wj_year,
                                                (
                                                    SELECT
                                                        count(0) AS `count`
                                                    FROM
                                                        `cxdj_ajax_event` AS `event`
                                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                                    WHERE
                                                        (
                                                            (
                                                                `event`.`CATEGORY1` = '国土类'
                                                            )
                                                            AND (
                                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                                            )
                                                            AND (
                                                                `event`.`DEAL_USER__IS_DY` = 1
                                                            )
                                                        )
                                                    AND XZ_DEPARTMENTNAME = '$name'
                                                ) AS gt_year
                                            FROM
                                                cxdj_ajax_event
                                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_16',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_16');// 获取缓存
            }

            $category_town = $town_event_year[0]['jt_year'] + $town_event_year[0]['public_year'] + $town_event_year[0]['police_year'] + $town_event_year[0]['xf_year'] + $town_event_year[0]['wj_year'] + $town_event_year[0]['gt_year'];
            //其他分类红色网格员处理事件数
            $other_year_town = $event_all_year[16]['count'] - $category_town;

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;
            $town_event_year[] = $other_year_town;

            /**************************************************************街道当月数据*********************************************************************/
            if(!S('town_event_month_16')){
                $town_event_month = $Model->query("SELECT
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '交通运输类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS jt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公共事业类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS public_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '公安类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS police_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '消防类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS xf_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '卫计类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS wj_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '国土类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS gt_year,
                                (
                                    SELECT
                                        count(0) AS `count`
                                    FROM
                                        `cxdj_ajax_event` AS `event`
                                    JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                    WHERE
                                        (
                                            (
                                                `event`.`CATEGORY1` = '城乡建设类'
                                            )
                                            AND (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y-%m') = date_format(now(), '%Y-%m')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_month_16',$town_event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_month = S('town_event_month_16');// 获取缓存
            }

            foreach ($town_event_month[0] as $k1=>$val){
                $items[] = (int)$val;
            }
            $town_event_month = $items;

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$event_all[16]['count'],'dy'=>(int)$event_all_year[16]['count'],'fdy'=>(int)$jd_fdy)));
        }


    }


    //网格事件当年100条记录
    public function wgRecord($classif=null,$name)
    {
        $Model = M();
        if($name == 1){
            $sql = "   AND 1=1 ";
        }else{
            if($name == '开发区(太湖街道)'){
                $name = '太湖街道';
            }
            $sql = "   AND wg.XZ_DEPARTMENTNAME = '$name'";
        }
        //红色网格
       if ($classif == 3){
            //街道事件记录数
            $event_month = $Model->query("
                                SELECT
                                    ADDRESS AS address,
                                    CATEGORY1 AS category,
                                    REPORTOR AS person,
                                    DESCRIPTION AS content,
                                    UNIX_TIMESTAMP(ACCEPT_TIME) AS st_time,
                                    UNIX_TIMESTAMP(END_TIME) AS end_time
                                FROM
                                    cxdj_ajax_event AS `event` JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                WHERE
                                    DATE_FORMAT(HAPPEN_TIME, '%Y') = DATE_FORMAT(NOW(), '%Y')
                                    $sql 
                                LIMIT 300
                                            ");
        }elseif ($classif == 1){
            //街道红色网格员记录数
//            if(!S('jd_dy_record')){
                $event_month = $Model->query("
                                SELECT
                                    ADDRESS AS address,
                                    CATEGORY1 AS category,
                                    REPORTOR AS person,
                                    DESCRIPTION AS content,
                                    UNIX_TIMESTAMP(ACCEPT_TIME) AS st_time,
                                    UNIX_TIMESTAMP(END_TIME) AS end_time
                                FROM
                                    cxdj_ajax_event AS `event` JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                WHERE
                                    DATE_FORMAT(HAPPEN_TIME, '%Y') = DATE_FORMAT(NOW(), '%Y')
                                AND REPORTOR_CARDNUM <>'' 
                                 $sql 
                                LIMIT 300
                                            ");
//                $time = 3600*72;  //缓存三天
//                S('jd_dy_record',$event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $event_month = S('jd_dy_record');// 获取缓存
//            }

        }elseif ($classif == 2){
            //非红色网格员记录数
//            if(!S('jd_fdy_record')){
                $event_month = $Model->query("
                                SELECT
                                    ADDRESS AS address,
                                    CATEGORY1 AS category,
                                    REPORTOR AS person,
                                    DESCRIPTION AS content,
                                    UNIX_TIMESTAMP(ACCEPT_TIME) AS st_time,
                                    UNIX_TIMESTAMP(END_TIME) AS end_time
                                FROM
                                    cxdj_ajax_event AS `event` JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                WHERE
                                    DATE_FORMAT(HAPPEN_TIME, '%Y') = DATE_FORMAT(NOW(), '%Y')
                                AND REPORTOR_CARDNUM < '0'
                                    $sql 
                                LIMIT 300
                                            ");
//                $time = 3600*72;  //缓存三天
//                S('jd_fdy_record',$event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $event_month = S('jd_fdy_record');// 获取缓存
//            }

        }

//            $aa = M()->getLastsql();
//        dump($aa);die;

        //end_time 事件解决日期
        foreach ($event_month as $k=>&$v){
            if($v['end_time'] < time()){
                $v['status'] = '已解决';
            }elseif ($v['end_time'] == ''){
                $v['status'] = '未解决';
            }
        }

        $head = array(
            array('name'=>'上报人','width'=>20),
            array('name'=>'事件类别','width'=>20),
            array('name'=>'事件描述','width'=>30),
            array('name'=>'地址','width'=>20),
            array('name'=>'状态','width'=>10),
        );

        foreach ($event_month as $k=>&$v){
            $data[] = array(
                array('value'=>$v['person'], 'width'=>20),
                array('value'=>$v['category'], 'width'=>20),
                array('value'=>$v['content'], 'width'=>30),
                array('value'=>$v['address'], 'width'=>20),
                array('value'=>$v['status'], 'width'=>10),

            );
        }
        if($classif == 1){
            echo json_encode(array('code'=>200,'data'=>array('title'=>'红色网格事件','head'=>$head,'list'=>$data)));
        }
        elseif($classif == 2){
            echo json_encode(array('code'=>200,'data'=>array('title'=>'普通网格事件','head'=>$head,'list'=>$data)));
        }
        elseif($classif == 3){
            echo json_encode(array('code'=>200,'data'=>array('title'=>'网格事件','head'=>$head,'list'=>$data)));
        }



    }


    //四个平台数据
    /**
     * @param $name
     */
    public function platform($name)
    {
        set_time_limit(900);
        if($name == '开发区(太湖街道)'){
            $name = '太湖街道';
        }
            //县一级数据
        if($name == 1){
            //县户籍人口
            $count_HJ = $this->ajax_jiedao_people->sum('HJ');
            //县流动人口
            $count_LD = $this->ajax_jiedao_people->sum('LD');

             //办结事件总数
            if(!S('event_count')){
                $event_count = $this->ajax_jiedao->sum('count');
                $time = $this->time;  //缓存三天
                S('event_count',$event_count,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $event_count = S('event_count');// 获取缓存
            }

            //党员办结事件
//            if(!S('event_dy')){
                $event_dy = $this->event_dy->sum('count');
//                $time = $this->time;  //缓存三天
//                S('event_dy',$event_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $event_dy = S('event_dy');// 获取缓存
//            }


                 // 非党员办结数
                $event_fdy = (int)$event_count - (int)$event_dy;

                //申请正在受理
                $SL = $this->ajax_event->where("CATEGORY1 = '公共事业类' AND date_format(`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')")->count();

            //去年十二个月事件
            $items = array(
                array('m'=>'01','count'=>0),
                array('m'=>'02','count'=>0),
                array('m'=>'03','count'=>0),
                array('m'=>'04','count'=>0),
                array('m'=>'05','count'=>0),
                array('m'=>'06','count'=>0),
                array('m'=>'07','count'=>0),
                array('m'=>'08','count'=>0),
                array('m'=>'09','count'=>0),
                array('m'=>'10','count'=>0),
                array('m'=>'11','count'=>0),
                array('m'=>'12','count'=>0),
            );

            $event_lastyear = $this->event_lastyear->select();
            foreach ($event_lastyear as $k=>$v){
              if($v['month'] == $items[$v['month']-1]['m']){
                  $items[$v['month']-1]['count'] = $v['count'];
              }
            }

            foreach ($items as $k=>$value){
                $event_lastyear_data[] = (int)$value['count'];
            }

            //县今年十二个月数据
            $item = array(
                array('m'=>'01','count'=>0),
                array('m'=>'02','count'=>0),
                array('m'=>'03','count'=>0),
                array('m'=>'04','count'=>0),
                array('m'=>'05','count'=>0),
                array('m'=>'06','count'=>0),
                array('m'=>'07','count'=>0),
                array('m'=>'08','count'=>0),
                array('m'=>'09','count'=>0),
                array('m'=>'10','count'=>0),
                array('m'=>'11','count'=>0),
                array('m'=>'12','count'=>0),
            );

            $event_current = $this->event_current->select();
            foreach ($event_current as $k=>$v){
                if($v['month'] == $item[$v['month']-1]['m']){
                    $item[$v['month']-1]['count'] = $v['count'];
                }
            }

            foreach ($item as $k=>$value){
                $event_currentyear_data[] = (int)$value['count'];
            }


            echo  json_encode(array('code'=>200,'data'=>array('zhzl'=>array('hj'=>(int)$count_HJ,'ld'=>(int)$count_LD),'zhzf'=>array('event_dy'=>(int)$event_dy,'event_fdy'=>(int)$event_fdy),'bmfw'=>(int)$SL,'event_lastyear'=>$event_lastyear_data,'event_current'=>$event_currentyear_data)));

        }else{

        /*****************************************************街道信息**********************************************************************/

            //户籍人口，流动人口
            $people = $this->ajax_jiedao_people->where(array('XZ_DEPARTMENTNAME'=>$name))->field('HJ,LD')->find();
            //街道事件总数
            if(!S('event_jd')){
                $event = $this->ajax_jiedao->where(array('town'=>$name))->field('count')->find();
                $time = $this->time;  //缓存三天
                S('event_jd',$event,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $event = S('event_jd');// 获取缓存
            }




            //党员办结事件总数
//            if(!S('event_dy_jd')){
                $event_dy_town = $this->event_dy->where(array('town'=>$name))->field('count')->find();
//                $time = $this->time;  //缓存三天
//                S('event_dy_jd',$event_dy_town,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $event_dy_town = S('event_dy_jd');// 获取缓存
//            }


            //非党员办结总数
            $event_town_fdy = (int)$event['count'] - (int)$event_dy_town['count'];
            //便民服务
//            if(!S('event_bm')){
                $event_bm = $this->event_bm->where(array('town'=>$name))->field('count')->find();
//                $time = $this->time;  //缓存三天
//                S('event_bm',$event_bm,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $event_bm = S('event_bm');// 获取缓存
//            }

            //街道去年数据
//            if(!S('jd_lastyear_1')){
                $jd_lastyear = M()->query("SELECT
                                            date_format(`event`.`HAPPEN_TIME`, '%Y') AS `year`,
                                            date_format(`event`.`HAPPEN_TIME`, '%m') AS `month`,
                                            count(`event`.`id`) AS `count`
                                        FROM
                                            `cxdj_ajax_event` `event` JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                        WHERE
                                            (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = (date_format(now(), '%Y') - 1)
                                            )
                                        AND XZ_DEPARTMENTNAME = '$name'
                                        GROUP BY
                                            date_format(`event`.`HAPPEN_TIME`, '%m')");
//                $time = 3600 * 72;  //缓存三天
//                S('jd_lastyear_1',$jd_lastyear,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_lastyear = S('jd_lastyear_1');// 获取缓存
//            }


            //县去年十二个月数据
            $item = array(
                array('m'=>'01','count'=>0),
                array('m'=>'02','count'=>0),
                array('m'=>'03','count'=>0),
                array('m'=>'04','count'=>0),
                array('m'=>'05','count'=>0),
                array('m'=>'06','count'=>0),
                array('m'=>'07','count'=>0),
                array('m'=>'08','count'=>0),
                array('m'=>'09','count'=>0),
                array('m'=>'10','count'=>0),
                array('m'=>'11','count'=>0),
                array('m'=>'12','count'=>0),
            );

            foreach ($jd_lastyear as $k=>&$v){
                if($v['month'] == $item[$v['month']-1]['m']){
                   $item[$v['month']-1]['count'] = $v['count'];
                }

            }

            foreach ($item as $k=>$value){
                $jd_lastyear_data[] = (int)$value['count'];
            }



            //街道今年十二个月数据
//            if(!S('jd_currentyear_1')){
                $jd_currentyear = M()->query("SELECT
                                            date_format(`event`.`HAPPEN_TIME`, '%Y') AS `year`,
                                            date_format(`event`.`HAPPEN_TIME`, '%m') AS `month`,
                                            count(`event`.`id`) AS `count`
                                        FROM
                                            `cxdj_ajax_event` `event` JOIN cxdj_ajax_wg AS wg ON `event`.G_ID = wg.DEPARTMENTID
                                        WHERE
                                            (
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        AND XZ_DEPARTMENTNAME = '$name'
                                        GROUP BY
                                            date_format(`event`.`HAPPEN_TIME`, '%m')");
//                $time = 3600 * 72;  //缓存三天
//                S('jd_currentyear_1',$jd_currentyear,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
//            }else{
//                $jd_currentyear = S('jd_currentyear_1');// 获取缓存
//            }

            $items = array(
                array('m'=>'01','count'=>0),
                array('m'=>'02','count'=>0),
                array('m'=>'03','count'=>0),
                array('m'=>'04','count'=>0),
                array('m'=>'05','count'=>0),
                array('m'=>'06','count'=>0),
                array('m'=>'07','count'=>0),
                array('m'=>'08','count'=>0),
                array('m'=>'09','count'=>0),
                array('m'=>'10','count'=>0),
                array('m'=>'11','count'=>0),
                array('m'=>'12','count'=>0),
            );


            foreach ($jd_currentyear as $k=>&$v){

                    if($v['month'] == $items[$v['month']-1]['m']){
                        $items[$v['month']-1]['count'] = $v['count'];
                }

            }


            foreach ($items as $k=>$value){
                $jd_currentyear_data[] = (int)$value['count'];
            }


            echo  json_encode(array('code'=>200,'data'=>array('zhzl'=>array('hj'=>(int)$people['HJ'],'ld'=>(int)$people['LD']),'zhzf'=>array('event_dy'=>(int)$event_dy_town['count'],'event_fdy'=>(int)$event_town_fdy),'bmfw'=>(int)$event_bm['count'],'event_lastyear'=>$jd_lastyear_data,'event_current'=>$jd_currentyear_data)));

        }

    }






}
