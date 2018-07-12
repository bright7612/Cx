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
        $this->time = 3600 * 72;
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

    public function memberRecord()
    {
        $head = array(  //党员详情
            array('name'=>'序号', 'width'=>15),
            array('name'=>'党员姓名', 'width'=>20),
            array('name'=>'所属党组织', 'width'=>30),
            array('name'=>'活动记录数', 'width'=>15),
            array('name'=>'党费缴纳时间', 'width'=>20),
        );


            if(!S('meb_record')){
                $meb = $this->ajax_user->query("SELECT (@i:=@i+1) id,`NAME`,PARTY_ID,BRANCH_NAME,'' AS `time` FROM cxdj_ajax_user,(SELECT @i:=0) AS i LIMIT 100");
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


            foreach ($meb as $k=>$v){
                $data[] = array(
                    array('value'=>$v['id'],'width'=>15,'ID'=>$v['PARTY_ID'],'type'=>'activity'),
                    array('value'=>$v['NAME'],'width'=>20,'ID'=>$v['PARTY_ID'],'type'=>'activity'),
                    array('value'=>$v['BRANCH_NAME'],'width'=>30,'ID'=>$v['PARTY_ID'],'type'=>'activity'),
                    array('value'=>$v['COUNT'],'width'=>15,'ID'=>$v['PARTY_ID'],'type'=>'activity'),
                    array('value'=>$v['time'],'width'=>20,'ID'=>$v['PARTY_ID'],'type'=>'activity'),
                );
            }


        $ds['data']['list']=$data;
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
            $zcList = $this->ajax_volunteer->where($where)->field('VOLUNTEER_ID,NAME as title,CONTENT as text,STATE,rCount,PEOPLENUMBER')->order('id DESC')->select();
            foreach ($zcList as $k=>&$v){
                $v['Percentage'] = (round(($v['rCount']/$v['PEOPLENUMBER']),2)*100);
            }


            echo json_encode(array('data'=>array('complete'=>(int)$love_y,'implement'=>(int)$love_w,'list'=>$zcList),));


    }


    //网格事件
    public function WG($name)
    {
        $Model = M('ajax_jiedao_total');
        $Model1 = M('jiedao_dy');
        if($name == 1){
            $Model = M();
            //统计每个街道七大网格事件
            /***************************************************************县当年数据************************************************************************/
            if(!S('event_year')){
                $event_count = M('event_year')->select();
                $time = $this->time;  //缓存三天
                S('event_year',$event_count,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $event_year = S('event_year');// 获取缓存
            }

            foreach ($event_year[0] as $k=>&$v){
                $data[] = (int)$v;
            }

            $event_year = $data;
            /**********************************************************************县当月数据*****************************************************************************************************/
            if(!S('event_month')){
                $event_count2 = M('event_month')->select();
                $time = $this->time;  //缓存三天
                S('event_month',$event_count2,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
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

            //（红色网格员数处理事件数）
            $Red_dy = $Model->query("SELECT COUNT(*) AS `count` FROM cxdj_ajax_event WHERE DATE_FORMAT(HAPPEN_TIME, '%Y') = DATE_FORMAT(NOW(), '%Y')  AND DEAL_USER__IS_DY = 1");

            //普通网格员/群众处理事件数
            $people = (int)$WG[0]['count'] - (int)$Red_dy[0]['count'];
            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$event_year,'event_month'=>$event_month,'total'=>(int)$WG[0]['count'],'dy'=>(int)$Red_dy[0]['count'],'fdy'=>$people)));
        }elseif($name == '雉城街道'){

            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total');// 获取缓存
            }

            if(!S('jd_dy')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '和平镇'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_1')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_1',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_1');// 获取缓存
            }

            if(!S('jd_dy_1')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_1',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_1');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_1',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_1');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '虹星桥镇'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_2')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_2',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_2');// 获取缓存
            }

            if(!S('jd_dy_2')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_2',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_2');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_2',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_2');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '洪桥镇'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_3')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_3',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_3');// 获取缓存
            }

            if(!S('jd_dy_3')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_3',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_3');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_3',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_3');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '画溪街道'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_4')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_4',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_4');// 获取缓存
            }

            if(!S('jd_dy_4')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_4',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_4');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_4',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_4');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '小浦镇'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_5')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_5',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_5');// 获取缓存
            }

            if(!S('jd_dy_5')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_5',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_5');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_5',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_5');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '夹浦镇'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_6')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_6',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_6');// 获取缓存
            }

            if(!S('jd_dy_6')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_6',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_6');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_6',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_6');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '李家巷镇'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_7')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_7',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_7');// 获取缓存
            }

            if(!S('jd_dy_7')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_7',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_7');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_7',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_7');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '林城镇'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_8')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_8',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_8');// 获取缓存
            }

            if(!S('jd_dy_8')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_8',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_8');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_8',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_8');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '图影管委会'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_9')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_9',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_9');// 获取缓存
            }

            if(!S('jd_dy_9')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_9',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_9');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_9',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_9');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '太湖街道'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_10')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_10',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_10');// 获取缓存
            }

            if(!S('jd_dy_10')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_10',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_10');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_10',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_10');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif($name == '龙山街道'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_11')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_11',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_11');// 获取缓存
            }

            if(!S('jd_dy_11')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_11',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_11');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_11',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_11');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif($name == '吕山乡'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_12')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_12',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_12');// 获取缓存
            }

            if(!S('jd_dy_12')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_12',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_12');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_12',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_12');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '煤山镇'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_13')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_13',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_13');// 获取缓存
            }

            if(!S('jd_dy_13')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_13',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_13');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_13',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_13');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '南太湖管委会'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_14')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_14',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_14');// 获取缓存
            }

            if(!S('jd_dy_14')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_14',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_14');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_14',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_14');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '水口乡'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_15')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_15',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_15');// 获取缓存
            }

            if(!S('jd_dy_15')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_15',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_15');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_15',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_15');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }elseif ($name == '泗安镇'){
            /********************************************************************街道当年数据*****************************************************************************/
            if(!S('jd_total_16')){
                $jd_total = $Model->where(array('town'=>$name))->field('count')->find(); //街道事件总数
                $time = $this->time;  //缓存三天
                S('jd_total_16',$jd_total,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_total = S('jd_total_16');// 获取缓存
            }

            if(!S('jd_dy_16')){
                $jd_dy = $Model1->where(array('town'=>$name))->field('count')->find();   //街道红色网格员处理事件
                $time = $this->time;  //缓存三天
                S('jd_dy_16',$jd_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $jd_dy = S('jd_dy_16');// 获取缓存
            }

            $jd_fdy = (int)$jd_total['count'] - (int)$jd_dy['count'];

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
                                                date_format(`event`.`HAPPEN_TIME`, '%Y') = date_format(now(), '%Y')
                                            )
                                        )
                                    AND XZ_DEPARTMENTNAME = '$name'
                                ) AS city_year
                            FROM
                                cxdj_ajax_event
                            LIMIT 1");
                $time = $this->time;  //缓存三天
                S('town_event_year_16',$town_event_year,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $town_event_year = S('town_event_year_16');// 获取缓存
            }

            foreach ($town_event_year[0] as $k1=>$value){
                $item[] = (int)$value;
            }
            $town_event_year = $item;

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

            echo json_encode(array('code'=>200,'data'=>array('event_year'=>$town_event_year,'event_month'=>$town_event_month,'total'=>(int)$jd_total['count'],'dy'=>(int)$jd_dy['count'],'fdy'=>(int)$jd_fdy)));
        }


    }


    //网格事件当年100条记录
    public function wgRecord($classif=null,$name)
    {
        $Model = M();
        if($name == 1){
            $sql = "   AND 1=1 ";
        }else{
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
                                LIMIT 100
                                            ");
        }elseif ($classif == 1){
            //街道红色网格员记录数
            if(!S('jd_dy_record')){
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
                                LIMIT 100
                                            ");
                $time = 3600*72;  //缓存三天
                S('jd_dy_record',$event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $event_month = S('jd_dy_record');// 获取缓存
            }

        }elseif ($classif == 2){
            //非红色网格员记录数
            if(!S('jd_fdy_record')){
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
                                AND REPORTOR_CARDNUM = ''
                                    $sql
                                LIMIT 100
                                            ");
                $time = 3600*72;  //缓存三天
                S('jd_fdy_record',$event_month,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $event_month = S('jd_fdy_record');// 获取缓存
            }

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
            //县一级数据
        if($name == 1){
            //县户籍人口
            $count_HJ = $this->ajax_jiedao_people->sum('HJ');
            //县流动人口
            $count_LD = $this->ajax_jiedao_people->sum('LD');

             //办结事件总数
            if(!S('event_count')){
                $event_count1 = $this->ajax_jiedao->sum('count');
                $time = $this->time;  //缓存三天
                S('event_count',$event_count1,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $event_count = S('event_count');// 获取缓存
            }

            //党员办结事件
            if(!S('event_dy')){
                $event_dy = $this->event_dy->sum('count');
                $time = $this->time;  //缓存三天
                S('event_dy',$event_dy,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $event_dy = S('event_dy');// 获取缓存
            }


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
            if(!S('event_dy_jd')){
                $event_dy_town = $this->event_dy->where(array('town'=>$name))->field('count')->find();
                $time = $this->time;  //缓存三天
                S('event_dy_jd',$event_dy_town,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $event_dy_town = S('event_dy_jd');// 获取缓存
            }


            //非党员办结总数
            $event_town_fdy = (int)$event['count'] - (int)$event_dy_town['count'];

            //便民服务
            if(!S('event_bm')){
                $event_bm = $this->event_bm->where(array('town'=>$name))->field('count')->find();
                $time = $this->time;  //缓存三天
                S('event_bm',$event_bm,array('type'=>'file','expire'=>$time));   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            }else{
                $event_bm = S('event_bm');// 获取缓存
            }

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


            echo  json_encode(array('code'=>200,'data'=>array('zhzl'=>array('hj'=>(int)$people['HJ'],'ld'=>(int)$people['LD']),'zhzf'=>array('event_dy'=>(int)$event_dy_town['count'],'event_fdy'=>(int)$event_town_fdy['count']),'bmfw'=>(int)$event_bm['count'],'event_lastyear'=>$jd_lastyear_data,'event_current'=>$jd_currentyear_data)));

        }

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
