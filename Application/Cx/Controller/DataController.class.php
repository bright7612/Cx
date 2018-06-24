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

           $loveList = $this->ajax_volunteer->where($where)->field('VOLUNTEER_ID,NAME as title,CONTENT as text,STATE')->order('id DESC')->select();

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
        $volunteerList = $this->ajax_volunteer->where(array('sh_state'=>1))->field('NAME,BEGIN_TIME')->order('id DESC')->select();

        foreach ($volunteerList as $k=>&$v){
            $v['BEGIN_TIME'] = date("Y-m-d",strtotime($v['BEGIN_TIME']));
        }

        echo json_encode(array('status'=>1,'msg'=>'请求成功','data'=>array('sum'=>$volunteer,'list'=>$volunteerList)));
    }


    public function wxy()
    {

        //微心愿总数
        $wxy_count = $this->ajax_volunteer->where(array('TYPE'=>3,'sh_state'=>1))->count();

        //已认领
        $already = $this->ajax_volunteer->where(array('TYPE'=>3,'sh_state'=>1,'STATE1'=>'结束'))->count();
        //未认领
        $wei = (int)$wxy_count - (int)$already;

        //正在进行
        $wxy_ing = $this->ajax_volunteer->where(array('TYPE'=>3,'sh_state'=>1,'STATE'=>1))->count();
        //已达标
        $wxy_db = $this->ajax_volunteer->where(array('TYPE'=>3,'sh_state'=>1,'STATE'=>2))->count();
        //已完成
        $wxy_ed = $this->ajax_volunteer->where(array('TYPE'=>3,'sh_state'=>1,'STATE'=>3))->count();



        echo json_encode(array('status'=>200,'data'=>array('count'=>(int)$wxy_count,'already'=>(int)$already,'wei'=>(int)$wei,'wxy_ing'=>(int)$wxy_ing,'wxy_db'=>(int)$wxy_db,'wxy_ed'=>(int)$wxy_ed)));

    }

    public function wxyRecord()
    {
        //微心愿记录
        $wxyList = $this->ajax_volunteer->where(array('TYPE'=>3,'sh_state'=>1))->select();
        $head = array(
            array('name'=>'主题','width'=>20),
            array('name'=>'发布单位','width'=>20),
            array('name'=>'联系人','width'=>20),
            array('name'=>'电话','width'=>20),
            array('name'=>'地址','width'=>20),
        );

        foreach ($wxyList as $k=>&$v){
            $data[] = array(
                array('value'=>$v['NAME'],'width'=>20),
                array('value'=>$v['BRANCH_NAME'],'width'=>20),
                array('value'=>$v['FROM_NAME'],'width'=>20),
                array('value'=>$v['PHONE'],'width'=>20),
                array('value'=>$v['ADDRESS'],'width'=>20),
            );
        }

        echo json_encode(array('status'=>200,'data'=>array('title'=>'微心愿','head'=>$head,'list'=>$data)));

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
            $zcList = $this->ajax_volunteer->where($where)->field('VOLUNTEER_ID,NAME as title,CONTENT as text,STATE')->order('id DESC')->select();
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


        echo json_encode(array('code'=>200,'wg'=>(int)$WG[0]['count'],'dy'=>(int)$Red_dy[0]['count'],'people'=>$people,'year'=>$event_year,'month'=>$event_month));

    }


    //网格事件当年100条记录
    public function wgRecord($classif=null)
    {
        //当月网格数100条记录
        if($classif == 1){
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
                                                        DATE_FORMAT(HAPPEN_TIME, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m') AND REPORTOR_CARDNUM <>''
                                                    LIMIT 200 ");
        } //红色网格
        elseif($classif == 2){
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
                                                        DATE_FORMAT(HAPPEN_TIME, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m') AND REPORTOR_CARDNUM =''
                                                    LIMIT 200 ");
        }//非红色网格
        else{
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
        }

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
        else{
            echo json_encode(array('code'=>200,'data'=>array('title'=>'网格事件','head'=>$head,'list'=>$data)));
        }


    }


    //四个平台数据
    public function platform()
    {
            //县一级数据
//        if($type == 0){
            //县户籍人口
            $count_HJ = $this->ajax_jiedao_people->sum('HJ');
            //县流动人口
            $count_LD = $this->ajax_jiedao_people->sum('LD');

             //办结事件总数
            if(!S('event_count')){
                $event_count1 = $this->ajax_jiedao->sum('count');
                $time = 3600 * 72;  //缓存三天
                S('event_count',$event_count1,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $event_count = S('event_count');// 获取缓存
            }

            //党员办结事件
            if(!S('event_dy')){
                $event_dy = $this->event_dy->sum('count');
                $time = 3600 * 72;  //缓存三天
                S('event_dy',$event_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $event_dy = S('event_dy');// 获取缓存
            }

                 // 非党员办结数
                $event_fdy = (int)$event_count - (int)$event_dy;

                //申请正在受理
                $SL = $this->ajax_event->where("CATEGORY1 = '公共事业类' AND date_format(`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')")->count();


           echo  json_encode(array('code'=>200,'data'=>array('zhzl'=>array('hj'=>(int)$count_HJ,'ld'=>(int)$count_LD),'zhzf'=>array('event_dy'=>(int)$event_dy,'event_fdy'=>(int)$event_fdy),'bmfu'=>(int)$SL)));

//        }


    }

    public function warning_information()
    {
        $life = array(15,15);
        $report = array(6,7);
       echo json_encode(array('code'=>200,'msg'=>'请求成功','data'=>array('life'=>$life,'dev'=>0,'report'=>$report,'pay'=>7,'progress'=>100)));
    }

    public function warning_Record($type)
    {
        $num = I('num');

        //三个月未开展
        if($type == 1 && $num == 'five'){

            $head = array(
                array('name'=>'序号','width'=>10),
                array('name'=>'姓名','width'=>20),
                array('name'=>'性别','width'=>10),
                array('name'=>'身份证','width'=>30),
                array('name'=>'时间','width'=>20),
                array('name'=>'状态','width'=>10),
            );

            $item = array(
                array('id'=>1,'name'=>'臧国平','sex'=>'男','IDCARD'=>'330522199005114713','time'=>'2018-3-03','status'=>'一般'),
                array('id'=>2,'name'=>'董粉华','sex'=>'女','IDCARD'=>'330522198902191028','time'=>'2018-4-12','status'=>'一般'),
                array('id'=>3,'name'=>'吴建良','sex'=>'男','IDCARD'=>'33052219810112292X','time'=>'2018-3-13','status'=>'一般'),
                array('id'=>4,'name'=>'汪书杰','sex'=>'男','IDCARD'=>'411323199201121116','time'=>'2018-03-18','status'=>'一般'),
                array('id'=>5,'name'=>'蒋舒兴','sex'=>'男','IDCARD'=>'330522199305042520','time'=>'2018-05-08','status'=>'一般'),
                array('id'=>6,'name'=>'孔德余','sex'=>'男','IDCARD'=>'330522198001101028','time'=>'2018-05-12','status'=>'一般'),
            );

            foreach ($item as $k=>&$v){
                $data[] = array(
                    array('value'=>$v['id'], 'width'=>10),
                    array('value'=>$v['name'], 'width'=>20),
                    array('value'=>$v['sex'], 'width'=>10),
                    array('value'=>$v['IDCARD'], 'width'=>30),
                    array('value'=>$v['time'], 'width'=>20),
                    array('value'=>$v['status'], 'width'=>10),

                );
            }

            echo json_encode(array('code'=>200,'data'=>array('title'=>'组织生活','head'=>$head,'list'=>$data)));
        }elseif($type == 1 && $num == "four"){
            $head = array(
                array('name'=>'序号','width'=>10),
                array('name'=>'姓名','width'=>20),
                array('name'=>'性别','width'=>10),
                array('name'=>'身份证','width'=>30),
                array('name'=>'时间','width'=>20),
                array('name'=>'状态','width'=>10),
            );

            $item = array(

                array('id'=>1,'name'=>'庄良城','sex'=>'女','IDCARD'=>'33052219641214027X','time'=>'2018-04-12','status'=>'一般'),
                array('id'=>2,'name'=>'章建南','sex'=>'男','IDCARD'=>'330522199304161026','time'=>'2018-01-26','status'=>'一般'),
                array('id'=>3,'name'=>'殷金茂','sex'=>'男','IDCARD'=>'330522197208140437','time'=>'2018-03-12','status'=>'一般'),
                array('id'=>4,'name'=>'唐阿如','sex'=>'男','IDCARD'=>'330522199002151914','time'=>'2018-03-26','status'=>'一般'),
                array('id'=>5,'name'=>'周振方','sex'=>'男','IDCARD'=>'330522199103053715','time'=>'2018-04-18','status'=>'一般'),
            );

            foreach ($item as $k=>&$v){
                $data[] = array(
                    array('value'=>$v['id'], 'width'=>10),
                    array('value'=>$v['name'], 'width'=>20),
                    array('value'=>$v['sex'], 'width'=>10),
                    array('value'=>$v['IDCARD'], 'width'=>30),
                    array('value'=>$v['time'], 'width'=>20),
                    array('value'=>$v['status'], 'width'=>10),

                );
            }

            echo json_encode(array('code'=>200,'data'=>array('title'=>'组织生活','head'=>$head,'list'=>$data)));
        }elseif ($type == 1 && $num == "three"){
            $head = array(
                array('name'=>'序号','width'=>10),
                array('name'=>'姓名','width'=>20),
                array('name'=>'性别','width'=>10),
                array('name'=>'身份证','width'=>30),
                array('name'=>'时间','width'=>20),
                array('name'=>'状态','width'=>10),
            );

            $item = array(
                array('id'=>1,'name'=>'陆首石','sex'=>'男','IDCARD'=>'330522199204061044','time'=>'2018-03-01','status'=>'一般'),
                array('id'=>2,'name'=>'郑娇','sex'=>'女','IDCARD'=>'330522198902191028','time'=>'2018-04-12','status'=>'一般'),
                array('id'=>3,'name'=>'范利新','sex'=>'男','IDCARD'=>'33052219810112292X','time'=>'2018-05-01','status'=>'一般'),
                array('id'=>4,'name'=>'吴文超','sex'=>'男','IDCARD'=>'330522198902191028','time'=>'2018-11-15','status'=>'一般'),
                array('id'=>5,'name'=>'徐仙法','sex'=>'男','IDCARD'=>'330522194701116111','time'=>'2018-02-21','status'=>'一般'),
                array('id'=>6,'name'=>'吴欣欣','sex'=>'男','IDCARD'=>'330522197710115712','time'=>'2018-02-12','status'=>'一般'),
                array('id'=>7,'name'=>'董佩华','sex'=>'男','IDCARD'=>'330522199304166716','time'=>'2018-04-25','status'=>'一般'),
            );

            foreach ($item as $k=>&$v){
                $data[] = array(
                    array('value'=>$v['id'], 'width'=>10),
                    array('value'=>$v['name'], 'width'=>20),
                    array('value'=>$v['sex'], 'width'=>10),
                    array('value'=>$v['IDCARD'], 'width'=>30),
                    array('value'=>$v['time'], 'width'=>20),
                    array('value'=>$v['status'], 'width'=>10),

                );
            }

            echo json_encode(array('code'=>200,'data'=>array('title'=>'组织生活','head'=>$head,'list'=>$data)));
        }



        //三个月未参加
        if($type == 2 && $num == 'five'){

            $head = array(
                array('name'=>'序号','width'=>10),
                array('name'=>'姓名','width'=>20),
                array('name'=>'性别','width'=>10),
                array('name'=>'身份证','width'=>30),
                array('name'=>'时间','width'=>20),
                array('name'=>'状态','width'=>10),
            );

            $item = array(
                array('id'=>1,'name'=>'朱金龙','sex'=>'男','IDCARD'=>'33052219641112151X','time'=>'2018-03-01','status'=>'一般'),
                array('id'=>2,'name'=>'张启龙','sex'=>'男','IDCARD'=>'33052218890219102X','time'=>'2018-03-12','status'=>'一般'),
                array('id'=>3,'name'=>'王世雄','sex'=>'男','IDCARD'=>'33052219810112292X','time'=>'2018-05-01','status'=>'一般'),
                array('id'=>4,'name'=>'汪书杰','sex'=>'男','IDCARD'=>'411323199201121116','time'=>'2018-03-15','status'=>'一般'),
                array('id'=>5,'name'=>'高学伟','sex'=>'男','IDCARD'=>'330522199305042520','time'=>'2018-04-01','status'=>'一般'),
                array('id'=>6,'name'=>'计国权','sex'=>'男','IDCARD'=>'330522198001101028','time'=>'2018-04-12','status'=>'一般'),
            );

            foreach ($item as $k=>&$v){
                $data[] = array(
                    array('value'=>$v['id'], 'width'=>10),
                    array('value'=>$v['name'], 'width'=>20),
                    array('value'=>$v['sex'], 'width'=>10),
                    array('value'=>$v['IDCARD'], 'width'=>30),
                    array('value'=>$v['time'], 'width'=>20),
                    array('value'=>$v['status'], 'width'=>10),

                );
            }

            echo json_encode(array('code'=>200,'data'=>array('title'=>'组织生活','head'=>$head,'list'=>$data)));
        }elseif($type == 2 && $num == "four"){
            $head = array(
                array('name'=>'序号','width'=>10),
                array('name'=>'姓名','width'=>20),
                array('name'=>'性别','width'=>10),
                array('name'=>'身份证','width'=>30),
                array('name'=>'时间','width'=>20),
                array('name'=>'状态','width'=>10),
            );

            $item = array(
                array('id'=>1,'name'=>'秦振','sex'=>'男','IDCARD'=>'330522197902021534','time'=>'2018-03-01','status'=>'一般'),
                array('id'=>2,'name'=>'庄良城','sex'=>'女','IDCARD'=>'33052219641214027X','time'=>'2018-03-12','status'=>'一般'),
                array('id'=>3,'name'=>'苏立富','sex'=>'男','IDCARD'=>'33052219641112151X','time'=>'2018-04-01','status'=>'一般'),
                array('id'=>4,'name'=>'汪书杰','sex'=>'男','IDCARD'=>'330522198902191028','time'=>'2018-01-15','status'=>'一般'),

            );

            foreach ($item as $k=>&$v){
                $data[] = array(
                    array('value'=>$v['id'], 'width'=>10),
                    array('value'=>$v['name'], 'width'=>20),
                    array('value'=>$v['sex'], 'width'=>10),
                    array('value'=>$v['IDCARD'], 'width'=>30),
                    array('value'=>$v['time'], 'width'=>20),
                    array('value'=>$v['status'], 'width'=>10),

                );
            }

            echo json_encode(array('code'=>200,'data'=>array('title'=>'组织生活','head'=>$head,'list'=>$data)));
        }elseif ($type == 2 && $num == "three"){
            $head = array(
                array('name'=>'序号','width'=>10),
                array('name'=>'姓名','width'=>20),
                array('name'=>'性别','width'=>10),
                array('name'=>'身份证','width'=>30),
                array('name'=>'时间','width'=>20),
                array('name'=>'状态','width'=>10),
            );

            $item = array(

                array('id'=>1,'name'=>'王涛','sex'=>'男','IDCARD'=>'33052219810112292X','time'=>'2018-04-01','status'=>'一般'),
                array('id'=>2,'name'=>'吴文超','sex'=>'男','IDCARD'=>'330522198902191028','time'=>'2018-03-15','status'=>'一般'),
                array('id'=>3,'name'=>'魏巍','sex'=>'男','IDCARD'=>'330522194512013711','time'=>'2018-04-01','status'=>'一般'),
                array('id'=>4,'name'=>'徐仙法','sex'=>'男','IDCARD'=>'330522194701116111','time'=>'2018-04-21','status'=>'一般'),
                array('id'=>5,'name'=>'吴欣欣','sex'=>'男','IDCARD'=>'330522197710115712','time'=>'2018-02-12','status'=>'一般'),

            );

            foreach ($item as $k=>&$v){
                $data[] = array(
                    array('value'=>$v['id'], 'width'=>10),
                    array('value'=>$v['name'], 'width'=>20),
                    array('value'=>$v['sex'], 'width'=>10),
                    array('value'=>$v['IDCARD'], 'width'=>30),
                    array('value'=>$v['time'], 'width'=>20),
                    array('value'=>$v['status'], 'width'=>10),

                );
            }

            echo json_encode(array('code'=>200,'data'=>array('title'=>'组织生活','head'=>$head,'list'=>$data)));
        }

        //发展党员
        if($type == 3 && $num == 'five'){

            $head = array(
                array('name'=>'序号','width'=>10),
                array('name'=>'姓名','width'=>20),
                array('name'=>'性别','width'=>10),
                array('name'=>'身份证','width'=>30),
                array('name'=>'时间','width'=>20),
                array('name'=>'状态','width'=>10),
            );

            $item = array(
                array('id'=>1,'name'=>'朱自强','sex'=>'男','IDCARD'=>'330522197912191510','time'=>'2018-03-16','status'=>'一般'),
                array('id'=>2,'name'=>'郑娇','sex'=>'女','IDCARD'=>'330522198902191028','time'=>'2018-03-12','status'=>'一般'),
                array('id'=>3,'name'=>'胡俊勇','sex'=>'男','IDCARD'=>'33052219810112292X','time'=>'2018-05-04','status'=>'一般'),
                array('id'=>4,'name'=>'汪书杰','sex'=>'男','IDCARD'=>'411323199201121116','time'=>'2018-04-15','status'=>'一般'),
                array('id'=>5,'name'=>'魏巍','sex'=>'男','IDCARD'=>'330522199305042520','time'=>'2018-03-01','status'=>'一般'),
                array('id'=>6,'name'=>'孔德余','sex'=>'男','IDCARD'=>'330522198001101028','time'=>'2018-02-12','status'=>'一般'),
            );

            foreach ($item as $k=>&$v){
                $data[] = array(
                    array('value'=>$v['id'], 'width'=>10),
                    array('value'=>$v['name'], 'width'=>20),
                    array('value'=>$v['sex'], 'width'=>10),
                    array('value'=>$v['IDCARD'], 'width'=>30),
                    array('value'=>$v['time'], 'width'=>20),
                    array('value'=>$v['status'], 'width'=>10),

                );
            }

            echo json_encode(array('code'=>200,'data'=>array('title'=>'发展党员','head'=>$head,'list'=>'')));
        }elseif($type == 3 && $num == "four"){
            $head = array(
                array('name'=>'序号','width'=>10),
                array('name'=>'姓名','width'=>20),
                array('name'=>'性别','width'=>10),
                array('name'=>'身份证','width'=>30),
                array('name'=>'时间','width'=>20),
                array('name'=>'状态','width'=>10),
            );

            $item = array(

                array('id'=>1,'name'=>'庄良城','sex'=>'女','IDCARD'=>'33052219641214027X','time'=>'2018-02-12','status'=>'一般'),
                array('id'=>2,'name'=>'章建南','sex'=>'男','IDCARD'=>'330522199304161026','time'=>'2018-04-02','status'=>'一般'),
                array('id'=>3,'name'=>'汪书杰','sex'=>'男','IDCARD'=>'330522198902191028','time'=>'2018-03-15','status'=>'一般'),
                array('id'=>4,'name'=>'魏巍','sex'=>'男','IDCARD'=>'330522199305042520','time'=>'2018-02-16','status'=>'一般'),
                array('id'=>5,'name'=>'殷金茂','sex'=>'男','IDCARD'=>'330522197208140437','time'=>'2018-04-12','status'=>'一般'),
                array('id'=>6,'name'=>'唐阿如','sex'=>'男','IDCARD'=>'330522199002151914','time'=>'2018-03-26','status'=>'一般'),
                array('id'=>7,'name'=>'周振方','sex'=>'男','IDCARD'=>'330522199103053715','time'=>'2018-03-18','status'=>'一般'),
            );

            foreach ($item as $k=>&$v){
                $data[] = array(
                    array('value'=>$v['id'], 'width'=>10),
                    array('value'=>$v['name'], 'width'=>20),
                    array('value'=>$v['sex'], 'width'=>10),
                    array('value'=>$v['IDCARD'], 'width'=>30),
                    array('value'=>$v['time'], 'width'=>20),
                    array('value'=>$v['status'], 'width'=>10),

                );
            }

            echo json_encode(array('code'=>200,'data'=>array('title'=>'发展党员','head'=>$head,'list'=>'')));
        }elseif ($type == 3 && $num == "three"){
            $head = array(
                array('name'=>'序号','width'=>10),
                array('name'=>'姓名','width'=>20),
                array('name'=>'性别','width'=>10),
                array('name'=>'身份证','width'=>30),
                array('name'=>'时间','width'=>20),
                array('name'=>'状态','width'=>10),
            );

            $item = array(

                array('id'=>1,'name'=>'郑娇','sex'=>'女','IDCARD'=>'330522198902191028','time'=>'2018-02-12','status'=>'一般'),
                array('id'=>2,'name'=>'范利新','sex'=>'男','IDCARD'=>'33052219810112292X','time'=>'2018-3-01','status'=>'一般'),
                array('id'=>3,'name'=>'吴文超','sex'=>'男','IDCARD'=>'330522198902191028','time'=>'2018-2-15','status'=>'一般'),
                array('id'=>4,'name'=>'魏巍','sex'=>'男','IDCARD'=>'330522194512013711','time'=>'2018-04-01','status'=>'一般'),
                array('id'=>5,'name'=>'徐仙法','sex'=>'男','IDCARD'=>'330522194701116111','time'=>'2018-03-21','status'=>'一般'),
                array('id'=>6,'name'=>'吴欣欣','sex'=>'男','IDCARD'=>'330522197710115712','time'=>'2018-05-12','status'=>'一般'),
                array('id'=>7,'name'=>'徐仙法','sex'=>'男','IDCARD'=>'330522199212100824','time'=>'2018-02-15','status'=>'一般'),
                array('id'=>8,'name'=>'董佩华','sex'=>'男','IDCARD'=>'330522199304166716','time'=>'2018-0425','status'=>'一般'),
            );

            foreach ($item as $k=>&$v){
                $data[] = array(
                    array('value'=>$v['id'], 'width'=>10),
                    array('value'=>$v['name'], 'width'=>20),
                    array('value'=>$v['sex'], 'width'=>10),
                    array('value'=>$v['IDCARD'], 'width'=>30),
                    array('value'=>$v['time'], 'width'=>20),
                    array('value'=>$v['status'], 'width'=>10),

                );
            }

            echo json_encode(array('code'=>200,'data'=>array('title'=>'发展党员','head'=>$head,'list'=>'')));
        }


        //报道登记
        if($type == 4){

            $head = array(
                array('name'=>'序号','width'=>10),
                array('name'=>'姓名','width'=>20),
                array('name'=>'性别','width'=>10),
                array('name'=>'身份证','width'=>30),
                array('name'=>'时间','width'=>20),
                array('name'=>'状态','width'=>10),
            );

            $item = array(
                array('id'=>1,'name'=>'王强','sex'=>'男','IDCARD'=>'330522199005114713','time'=>'2018-03-01','status'=>'一般'),
                array('id'=>2,'name'=>'郑娇','sex'=>'女','IDCARD'=>'330522198902191028','time'=>'2018-03-12','status'=>'一般'),
                array('id'=>3,'name'=>'胡俊勇','sex'=>'男','IDCARD'=>'33052219810112292X','time'=>'2018-04-03','status'=>'一般'),
                array('id'=>4,'name'=>'汪书杰','sex'=>'男','IDCARD'=>'411323199201121116','time'=>'2018-03-15','status'=>'一般'),
                array('id'=>5,'name'=>'魏巍','sex'=>'男','IDCARD'=>'330522199305042520','time'=>'2018-03-05','status'=>'一般'),
                array('id'=>6,'name'=>'孔德余','sex'=>'男','IDCARD'=>'330522198001101028','time'=>'2018-04-12','status'=>'一般'),
            );

            foreach ($item as $k=>&$v){
                $data[] = array(
                    array('value'=>$v['id'], 'width'=>10),
                    array('value'=>$v['name'], 'width'=>20),
                    array('value'=>$v['sex'], 'width'=>10),
                    array('value'=>$v['IDCARD'], 'width'=>30),
                    array('value'=>$v['time'], 'width'=>20),
                    array('value'=>$v['status'], 'width'=>10),

                );
            }

            echo json_encode(array('code'=>200,'data'=>array('title'=>'报到登记','head'=>$head,'list'=>$data)));
        }elseif($type == 5 ){
            $head = array(
                array('name'=>'序号','width'=>10),
                array('name'=>'姓名','width'=>20),
                array('name'=>'性别','width'=>10),
                array('name'=>'身份证','width'=>30),
                array('name'=>'时间','width'=>20),
                array('name'=>'状态','width'=>10),
            );

            $item = array(

                array('id'=>1,'name'=>'庄良城','sex'=>'女','IDCARD'=>'33052219641214027X','time'=>'2018-04-12','status'=>'一般'),
                array('id'=>2,'name'=>'章建南','sex'=>'男','IDCARD'=>'330522199304161026','time'=>'2018-05-01','status'=>'一般'),
                array('id'=>3,'name'=>'汪书杰','sex'=>'男','IDCARD'=>'330522198902191028','time'=>'2018-03-15','status'=>'一般'),
                array('id'=>4,'name'=>'魏巍','sex'=>'男','IDCARD'=>'330522199305042520','time'=>'2018-02-06','status'=>'一般'),
                array('id'=>5,'name'=>'殷金茂','sex'=>'男','IDCARD'=>'330522197208140437','time'=>'2018-03-12','status'=>'一般'),
                array('id'=>6,'name'=>'唐阿如','sex'=>'男','IDCARD'=>'330522199002151914','time'=>'2018-04-26','status'=>'一般'),
                array('id'=>7,'name'=>'周振方','sex'=>'男','IDCARD'=>'330522199103053715','time'=>'2018-03-18','status'=>'一般'),
            );

            foreach ($item as $k=>&$v){
                $data[] = array(
                    array('value'=>$v['id'], 'width'=>10),
                    array('value'=>$v['name'], 'width'=>20),
                    array('value'=>$v['sex'], 'width'=>10),
                    array('value'=>$v['IDCARD'], 'width'=>30),
                    array('value'=>$v['time'], 'width'=>20),
                    array('value'=>$v['status'], 'width'=>10),

                );
            }

            echo json_encode(array('code'=>200,'data'=>array('title'=>'报到登记','head'=>$head,'list'=>$data)));
        }

            //党费缴纳

            if ($type == 6 ){
            $head = array(
                array('name'=>'序号','width'=>10),
                array('name'=>'姓名','width'=>20),
                array('name'=>'性别','width'=>10),
                array('name'=>'身份证','width'=>30),
                array('name'=>'时间','width'=>20),
                array('name'=>'状态','width'=>10),
            );

            $item = array(
                array('id'=>1,'name'=>'郑娇','sex'=>'女','IDCARD'=>'330522198902191028','time'=>'2018-04-12','status'=>'一般'),
                array('id'=>2,'name'=>'陆首石','sex'=>'男','IDCARD'=>'330522199204061044','time'=>'2018-05-06','status'=>'一般'),
                array('id'=>3,'name'=>'范利新','sex'=>'男','IDCARD'=>'33052219810112292X','time'=>'2018-03-05','status'=>'一般'),
                array('id'=>4,'name'=>'吴文超','sex'=>'男','IDCARD'=>'330522198902191028','time'=>'2018-04-15','status'=>'一般'),
                array('id'=>5,'name'=>'徐仙法','sex'=>'男','IDCARD'=>'330522194701116111','time'=>'2018-03-21','status'=>'一般'),
                array('id'=>6,'name'=>'吴欣欣','sex'=>'男','IDCARD'=>'330522197710115712','time'=>'2018-04-12','status'=>'一般'),
                array('id'=>7,'name'=>'董佩华','sex'=>'男','IDCARD'=>'330522199304166716','time'=>'2018-03-25','status'=>'一般'),
            );

            foreach ($item as $k=>&$v){
                $data[] = array(
                    array('value'=>$v['id'], 'width'=>10),
                    array('value'=>$v['name'], 'width'=>20),
                    array('value'=>$v['sex'], 'width'=>10),
                    array('value'=>$v['IDCARD'], 'width'=>30),
                    array('value'=>$v['time'], 'width'=>20),
                    array('value'=>$v['status'], 'width'=>10),

                );
            }

            echo json_encode(array('code'=>200,'data'=>array('title'=>'党费缴纳','head'=>$head,'list'=>$data)));
        }


    }




}
