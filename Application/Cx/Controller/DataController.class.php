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


    function _initialize()
    {
        $this->ajax_dzz = M('ajax_dzz');
        $this->ajax_user = M('ajax_user');
        $this->ajax_volunteer = M('ajax_volunteer');
        $this->ajax_WG = M('ajax_WG');
        $this->ajax_event = M('ajax_event');
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
           $love_w = $this->ajax_volunteer->where(array('sh_state'=>1,'TYPE'=>2,'xSTATE'=>array('in','1,2')))->count();

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
        //网格总数
        $WG = $Model->query('SELECT COUNT(*) as `count` FROM cxdj_ajax_WG ');
        //网格事件
        $event1 = $this->ajax_event->query("SELECT REPORTOR_CARDNUM as IDcard FROM cxdj_ajax_event  WHERE DATE_FORMAT(ACCEPT_TIME,'%Y') =  DATE_FORMAT(NOW(),'%Y') LIMIT 100");

        foreach ($event1 as $k=>&$v){
            $res[] = $this->ajax_user->where(array('IDCARD'=>$v['IDCARD']))->field('STATE')->select();
        }

        //红色网格员/党员
        $dy = count($res);
        //网格事件人员总数
        $wg = count($event1);
        //普通网格员/群众
        $people = $wg - $dy;

        //交通运输
        $jiaotong_y = $this->ajax_event->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '交通运输类' AND DATE_FORMAT(HAPPEN_TIME,'%Y') =  DATE_FORMAT(NOW(),'%Y')");
        $jiaotong_m = $this->ajax_event->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '交通运输类' AND DATE_FORMAT(HAPPEN_TIME,'%Y-%m') =  DATE_FORMAT(NOW(),'%Y-%m')");
        //公共事业类
        $public_y = $this->ajax_event->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '公共事业类' AND DATE_FORMAT(HAPPEN_TIME,'%Y') =  DATE_FORMAT(NOW(),'%Y')");
        $public_m = $this->ajax_event->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '公共事业类' AND DATE_FORMAT(HAPPEN_TIME,'%Y-%m') =  DATE_FORMAT(NOW(),'%Y-%m')");
        //公安类
        $police_y = $this->ajax_event->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '公安类' AND DATE_FORMAT(HAPPEN_TIME,'%Y') =  DATE_FORMAT(NOW(),'%Y')");
        $police_m = $this->ajax_event->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '公安类' AND DATE_FORMAT(HAPPEN_TIME,'%Y-%m') =  DATE_FORMAT(NOW(),'%Y-%m')");

        //消防类
        $xf_y = $this->ajax_event->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '消防类' AND DATE_FORMAT(HAPPEN_TIME,'%Y') =  DATE_FORMAT(NOW(),'%Y')");
        $xf_m = $this->ajax_event->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '消防类' AND DATE_FORMAT(HAPPEN_TIME,'%Y-%m') =  DATE_FORMAT(NOW(),'%Y-%m')");

        //卫计类
        $wj_y = $this->ajax_event->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '卫计类' AND DATE_FORMAT(HAPPEN_TIME,'%Y') =  DATE_FORMAT(NOW(),'%Y')");
        $wj_m = $this->ajax_event->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '卫计类' AND DATE_FORMAT(HAPPEN_TIME,'%Y-%m') =  DATE_FORMAT(NOW(),'%Y-%m')");

        //国土类
        $gt_y = $this->ajax_event->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '国土类' AND DATE_FORMAT(HAPPEN_TIME,'%Y') =  DATE_FORMAT(NOW(),'%Y')");
        $gt_m = $this->ajax_event->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '国土类' AND DATE_FORMAT(HAPPEN_TIME,'%Y-%m') =  DATE_FORMAT(NOW(),'%Y-%m')");

        //城乡建设类
        $city_y = $this->ajax_event->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '城乡建设类' AND DATE_FORMAT(HAPPEN_TIME,'%Y') =  DATE_FORMAT(NOW(),'%Y')");
        $city_m = $this->ajax_event->query("SELECT  COUNT(*) as `count` FROM cxdj_ajax_event  WHERE CATEGORY1 = '国土类' AND DATE_FORMAT(HAPPEN_TIME,'%Y-%m') =  DATE_FORMAT(NOW(),'%Y-%m')");


        $year = array((int)$jiaotong_y[0]['count'],(int)$public_y[0]['count'],(int)$police_y[0]['count'],(int)$xf_y[0]['count'],(int)$wj_y[0]['count'],(int)$gt_y[0]['count'],(int)$city_y[0]['count']);
        $month = array((int)$jiaotong_m[0]['count'],(int)$public_m[0]['count'],(int)$police_m[0]['count'],(int)$xf_m[0]['count'],(int)$wj_m[0]['count'],(int)$gt_m[0]['count'],(int)$city_m[0]['count']);

        echo json_encode(array('wg'=>(int)$WG[0]['count'],'dy'=>$dy,'people'=>$people,'year'=>$year,'month'=>$month));
    }



}
