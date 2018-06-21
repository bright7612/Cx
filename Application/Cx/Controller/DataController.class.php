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
    private  $ajax_persion;  //事件


    function _initialize()
    {
        $this->ajax_dzz = M('ajax_dzz');
        $this->ajax_user = M('ajax_user');
        $this->ajax_volunteer = M('ajax_volunteer');
        $this->ajax_WG = M('ajax_wg');
        $this->ajax_event = M('ajax_event');
        $this->ajax_persion = M('ajax_persion');
    }


    public function index()
    {
        $this->display();
    }


    //党员信息
    public function member()
    {
        //党员总数
        $meb = $this->ajax_user->count();
       //男性所占比率
        $man = $this->ajax_user->where(array('SEX'=>'男'))->count();
//        $man_rate = (round(($man/$meb),2)*100).'%';

        //女性所占比率
        $woman = $this->ajax_user->where(array('SEX'=>'女'))->count();
//        $woman_rate = (round(($woman/$meb),2)*100).'%';

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
        //党员年纪分布
        $age1 = $this->ajax_user->where('0 < age18 < 30')->count(); //30岁以下
        $age2 = $this->ajax_user->where('age18>=30 AND age18 < 40')->count(); //30岁到40岁
        $age3 = $this->ajax_user->where('age18>=40 AND age18 < 50')->count(); //40岁到50岁
        $age4 = $this->ajax_user->where('age18>=50 AND age18 < 60')->count(); //50岁到60岁
        $age5 = $this->ajax_user->where('age18>60 ')->count(); //60岁以上

        $age = array((int)$age1,(int)$age2,(int)$age3,(int)$age4,(int)$age5);

        $num =array($num4,$num3,$num2,$num1);


        echo json_encode(array('status'=>1,'msg'=>1,
            'data'=>array('meb'=>(int)$meb,
                'man'=>(int)$man,
                'woman'=>(int)$woman,
                'degree'=>array('num'=>$num),
                'age'=>array('num'=>$age)
            )));
    }

    //爱心众筹
    public function loveList()
    {
        $where['TYPE'] = 2;
        $where['sh_state'] = 1;


           //爱心众筹总数
           $count = $this->ajax_volunteer->where($where)->count();
           //众筹已完成
           $love_y = $this->ajax_volunteer->where(array('sh_state'=>1,'STATE'=>3,'TYPE'=>2))->count();
           //众筹未完成
           $love_w = $this->ajax_volunteer->where(array('sh_state'=>1,'TYPE'=>2,'STATE'=>array('in','1,2')))->count();

           $loveList = $this->ajax_volunteer->where($where)->field('VOLUNTEER_ID,NAME as title,CONTENT as text,STATE')->select();

            foreach ($loveList as $k=>&$v){
                $v['Percentage'] = 60;
            }

          echo json_encode(array('data'=>array('complete'=>(int)$love_y,'implement'=>(int)$love_w,'list'=>$loveList),));



    }


    //志愿服务
    public function volunteer()
    {
        //志愿服务总数
        $volunteer = $this->ajax_volunteer->where(array('sh_state'=>1))->count();
        //志愿者服务记录
        $volunteerList = $this->ajax_volunteer->where(array('sh_state'=>1))->field('NAME,BEGIN_TIME')->select();

        foreach ($volunteerList as $k=>&$v){
            $v['BEGIN_TIME'] = date("Y-m-d",strtotime($v['BEGIN_TIME']));
        }

        echo json_encode(array('status'=>1,'msg'=>'请求成功','data'=>array('sum'=>$volunteer,'list'=>$volunteerList)));
    }


    public function wxy()
    {

        //微心愿总数
        $wxy = $this->ajax_volunteer->where(array('TYPE'=>3,'sh_state'=>1))->select();

    }

    //党员众创互助
    public function zc_help()
    {
        $where['TYPE'] = 4;
        $where['sh_state'] = 1;

            //众创互助总数
            $count = $this->ajax_volunteer->where($where)->count();
            //众创互助完成
            $love_y = $this->ajax_volunteer->where(array('sh_state'=>1,'STATE'=>3,'TYPE'=>4))->count();
            //众创互助未完成
            $love_w = $this->ajax_volunteer->where(array('sh_state'=>1,'STATE'=>array('in','1,2'),'TYPE'=>4))->count();

//            echo json_encode(array('status'=>1,'msg'=>'请求成功','data'=>array('count'=>$count,'y_count'=>$love_y,'w_count'=>$love_w)));
            //众创互助记录
            $zcList = $this->ajax_volunteer->where($where)->field('VOLUNTEER_ID,NAME as title,CONTENT as text,STATE')->select();
            foreach ($zcList as $k=>&$v){
                $v['Percentage'] = 80;
            }

            echo json_encode(array('data'=>array('complete'=>(int)$love_y,'implement'=>(int)$love_w,'list'=>$zcList),));


    }


    //网格事件
    public function WG()
    {

        $Model = M();
        if(!S('event_year')){
            $event_count = M('event_year')->select();
            $time = 3600 * 72;  //缓存三天
            S('event_year',$event_count,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
        }else{
            $event_year = S('event_year');// 获取缓存
        }

    foreach ($event_year[0] as $k=>&$v){
        $data[] = (int)$v;
    }

          $event_year = $data;

        if(!S('event_month')){
            $event_count2 = M('event_month')->select();
            $time = 3600 * 72;  //缓存三天
            S('event_month',$event_count2,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
        }else{
            $event_month = S('event_month');// 获取缓存
        }

        foreach ($event_month[0] as $k=>&$v){
            $item[] = (int)$v;
        }

        $event_month = $item;


        //网格事件总数
        $WG = $Model->query("SELECT
                                COUNT(*) AS `count`
                            FROM
                                cxdj_ajax_event
                            WHERE
                             DATE_FORMAT(HAPPEN_TIME, '%Y') = DATE_FORMAT(NOW(), '%Y')");

        //上报事件为党员的事件数（红色网格员数）
        $Red_dy = $Model->query("SELECT COUNT(*) AS `count` FROM cxdj_ajax_event WHERE DATE_FORMAT(HAPPEN_TIME, '%Y') = DATE_FORMAT(NOW(), '%Y')  AND REPORTOR_CARDNUM <>''");


        //普通网格员/群众
        $people = (int)$WG[0]['count'] - (int)$Red_dy[0]['count'];

//
//        //交通运输
//        $jiaotong_y = $Model->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '交通运输类' AND DATE_FORMAT(HAPPEN_TIME,'%Y') =  DATE_FORMAT(NOW(),'%Y')");
//        $jiaotong_m = $Model->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '交通运输类' AND DATE_FORMAT(HAPPEN_TIME,'%Y-%m') =  DATE_FORMAT(NOW(),'%Y-%m')");
//        //公共事业类
//        $public_y = $Model->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '公共事业类' AND DATE_FORMAT(HAPPEN_TIME,'%Y') =  DATE_FORMAT(NOW(),'%Y')");
//        $public_m = $Model->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '公共事业类' AND DATE_FORMAT(HAPPEN_TIME,'%Y-%m') =  DATE_FORMAT(NOW(),'%Y-%m')");
//        //公安类
//        $police_y = $Model->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '公安类' AND DATE_FORMAT(HAPPEN_TIME,'%Y') =  DATE_FORMAT(NOW(),'%Y')");
//        $police_m = $Model->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '公安类' AND DATE_FORMAT(HAPPEN_TIME,'%Y-%m') =  DATE_FORMAT(NOW(),'%Y-%m')");
//
//        //消防类
//        $xf_y = $Model->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '消防类' AND DATE_FORMAT(HAPPEN_TIME,'%Y') =  DATE_FORMAT(NOW(),'%Y')");
//        $xf_m = $Model->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '消防类' AND DATE_FORMAT(HAPPEN_TIME,'%Y-%m') =  DATE_FORMAT(NOW(),'%Y-%m')");
//
//        //卫计类
//        $wj_y = $Model->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '卫计类' AND DATE_FORMAT(HAPPEN_TIME,'%Y') =  DATE_FORMAT(NOW(),'%Y')");
//        $wj_m = $Model->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '卫计类' AND DATE_FORMAT(HAPPEN_TIME,'%Y-%m') =  DATE_FORMAT(NOW(),'%Y-%m')");
//
//        //国土类
//        $gt_y = $Model->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '国土类' AND DATE_FORMAT(HAPPEN_TIME,'%Y') =  DATE_FORMAT(NOW(),'%Y')");
//        $gt_m = $Model->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '国土类' AND DATE_FORMAT(HAPPEN_TIME,'%Y-%m') =  DATE_FORMAT(NOW(),'%Y-%m')");
//
//        //城乡建设类
//        $city_y = $Model->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '城乡建设类' AND DATE_FORMAT(HAPPEN_TIME,'%Y') =  DATE_FORMAT(NOW(),'%Y')");
//        $city_m = $Model->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '城乡建设类' AND DATE_FORMAT(HAPPEN_TIME,'%Y-%m') =  DATE_FORMAT(NOW(),'%Y-%m')");
//
//
//        $year = array((int)$jiaotong_y[0]['count'],(int)$public_y[0]['count'],(int)$police_y[0]['count'],(int)$xf_y[0]['count'],(int)$wj_y[0]['count'],(int)$gt_y[0]['count'],(int)$city_y[0]['count']);
//        $month = array((int)$jiaotong_m[0]['count'],(int)$public_m[0]['count'],(int)$police_m[0]['count'],(int)$xf_m[0]['count'],(int)$wj_m[0]['count'],(int)$gt_m[0]['count'],(int)$city_m[0]['count']);

        echo json_encode(array('code'=>200,'wg'=>(int)$WG[0]['count'],'dy'=>(int)$Red_dy[0]['count'],'people'=>$people,'year'=>$event_year,'month'=>$event_month));

    }

    //网格事件当年100条记录
    public function wgRecord()
    {
        //当月网格数100条记录
        $event_month = $this->ajax_event->query("SELECT
                                                        ADDRESS AS address,
                                                        CATEGORY1 AS category,
                                                        REPORTOR AS person,
                                                        DESCRIPTION AS content,
                                                        UNIX_TIMESTAMP(ACCEPT_TIME) AS st_time,
                                                        UNIX_TIMESTAMP(END_TIME) AS end_time
                                                    
                                                    FROM
                                                        cxdj_ajax_event
                                                    WHERE
                                                        DATE_FORMAT(HAPPEN_TIME, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
                                                    LIMIT 100 ");
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

        echo json_encode(array('code'=>200,'data'=>array('title'=>'网格事件','head'=>$head,'list'=>$data)));

    }

    //四个平台数据
    public function platform()
    {
        //县四个平台数据
        $Model = M();
        $town = $Model->query("SELECT
                                    XZ_DEPARTMENTID,
                                    XZ_DEPARTMENTNAME,
                                    sum(TOTAL_HJ) AS HJ,
                                    sum(TOTAL_HJ_DY) AS HJ_DY,
                                    SUM(TOTAL_LD) AS LD,
                                    SUM(TOTAL_LD_DY) LD_DY
                                FROM 
                                    cxdj_ajax_wg AS wg
                                INNER JOIN cxdj_ajax_persion AS persion ON wg.DEPARTMENTID =  persion.G_ID
                                GROUP BY
                                    XZ_DEPARTMENTID,
                                    XZ_DEPARTMENTNAME");

        $HJ = array_column($town,'HJ');
        $HJ_DY = array_column($town,'HJ_DY');
        $LD = array_column($town,'LD');
        $LD_DY = array_column($town,'LD_DY');

        $HJ_SUM = array_sum($HJ);  //户籍人口
        $HJ_DY_SUM = array_sum($HJ_DY);  //户籍党员
        $LD_SUM = array_sum($LD);  //流动人口
        $LD_DY_SUM = array_sum($LD_DY);  //流动党员


        //村四个平台数据
        $cun = $Model->query("SELECT
                                    SQ_DEPARTMENTID,
                                    SQ_DEPARTMENTNAME,
                                    sum(TOTAL_HJ) AS HJ,
                                    sum(TOTAL_HJ_DY) AS HJ_DY,
                                    SUM(TOTAL_LD) AS LD,
                                    SUM(TOTAL_LD_DY) LD_DY
                                FROM 
                                    cxdj_ajax_wg AS wg
                                INNER JOIN cxdj_ajax_persion AS persion ON wg.DEPARTMENTID =  persion.G_ID
                                GROUP BY
                                    SQ_DEPARTMENTID,
                                    SQ_DEPARTMENTNAME");

        $CHJ = array_column($cun,'HJ');
        $CHJ_DY = array_column($cun,'HJ_DY');
        $CLD = array_column($cun,'LD');
        $CLD_DY = array_column($cun,'LD_DY');

        $CHJ_SUM = array_sum($CHJ);  //户籍人口
        $CHJ_DY_SUM = array_sum($CHJ_DY);  //户籍党员
        $CLD_SUM = array_sum($CLD);  //流动人口
        $CLD_DY_SUM = array_sum($CLD_DY);  //流动党员


    }




}
