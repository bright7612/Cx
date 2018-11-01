<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/11
 * Time: 10:30
 */

namespace Time\Controller;
use Think\Controller;

class VolunteerController extends Controller
{
    public function volunteer()
    {

        set_time_limit(300);
        $url = 'http://www.dysfz.gov.cn/apiXC/volunteerList.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 300;
        $res = 7;
        for ($i = 1; $i <= $res; $i++) {
            $da['START'] = $i;
            $das = json_encode($da);
            $list = $this->httpjson($url, $das);
            $res = ceil($list['totalCount'] / $da['COUNT']);
            foreach ($list['data'] as $k => $v) {
                $id = M('ajax_volunteer')->where(array('VOLUNTEER_ID' => $v['VOLUNTEER_ID']))->find();
                if ($id) {
                    M('ajax_volunteer')->where(array('VOLUNTEER_ID' => $v['VOLUNTEER_ID']))->save($v);
                } else {
                    M('ajax_volunteer')->add($v);
                }
            }
        }

    }


    public function volunteer_party()
    {
        set_time_limit(300);
        $url = 'http://www.dysfz.gov.cn/apiXC/volunteerList.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 500;
        $res = 7;
        for ($i = 1; $i <=$res; $i++) {
            $da['START'] = $i;
            $das = json_encode($da);
            $list = $this->httpjson($url, $das);
            $res = ceil($list['totalCount'] / $da['COUNT']);

            $p = 0;
            foreach ($list['data'] as $k => &$v) {
                foreach ($v['DetailsRecordList'] as $kk=>$value){
                    $items[$p] = $value;
                    $items[$p]['TYPE'] = $v['TYPE'];
                    $p++;
                }

            }


            foreach ($items as $k=>&$v){
                if(empty($v['RECORDV_ID'])){

                    $id = M('volunteer_party')->where(array('RECORDDONATIONS_ID' => $v['RECORDDONATIONS_ID']))->find();
                }
                elseif(empty($v['RECORDDONATIONS_ID'])){
                    $id = M('volunteer_party')->where(array('RECORDV_ID' => $v['RECORDV_ID']))->find();

                }
                if ($id) {

                    if(empty($v['RECORDV_ID'])){
                        M('volunteer_party')->where(array('RECORDDONATIONS_ID' => $v['RECORDDONATIONS_ID']))->save($v);
                    }
                    elseif(empty($v['RECORDDONATIONS_ID'])){
                        M('volunteer_party')->where(array('RECORDV_ID' => $v['RECORDV_ID']))->save($v);
                    }
                } else {


                    $datas['PARTY_NAME'] = $v['PARTY_NAME'];
                    $datas['VOLUNTEER_ID'] = $v['VOLUNTEER_ID'];
                    $datas['RECORDV_ID'] = $v['RECORDV_ID'];
                    $datas['CREATE_TIME'] = $v['CREATE_TIME'];
                    $datas['MONEY'] = $v['MONEY'];
                    $datas['TYPE'] = $v['TYPE'];
                    $datas['RECORDDONATIONS_ID'] = $v['RECORDDONATIONS_ID'];
                    $datas['PARTY_ID'] = $v['PARTY_ID'];
                    M('volunteer_party')->add($datas);
                    unset($v);
                }
            }

        }


    }


    //党员数据更新
    public function partyUser()
    {
        M('ajax_user')->query("TRUNCATE TABLE cxdj_ajax_user");
        set_time_limit(1000);
        $url = 'http://www.dysfz.gov.cn/apiXC/partyList.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 1000;
        $res = 10;

        for ($i = 1; $i <= $res; $i++) {
            $da['START'] = $i;
            $das = json_encode($da);
            $list = $this->httpjson($url, $das);
            $res = ceil($list['totalCount'] / $da['COUNT']);
            if (empty($list['data'])) {
                dump($i);
                exit;
            }
            echo $i;
//            dump($list);exit;

            foreach ($list['data'] as $k => &$v) {
                if ($v['PHOTO']) {
                    $v['PHOTO'] = 'http://www.dysfz.gov.cn/' . $v['PHOTO'];
                }
                M('ajax_user')->add($v);
            }
        }
    }


    /**
     * 党组织接口
     */
    public function branchList()
    {
        set_time_limit(300);
        $S = M('ajax_dzz')->select();
        S('ajax_dzz'.time(),$S,60*60*24*30);
        $url = 'http://www.dysfz.gov.cn/apiXC/branchList.do';//党组织
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 2500;
        $da['TREE_ORDER'] = true;
        $da['onlyChildren'] = true;
        $da['BRANCH_ID'] = 92;
        $da['START'] = 1;
        $das = json_encode($da);
        $list = $this->httpjson($url, $das);
        if($list['data']){
            M('ajax_dzz')->where('id>100')->delete();
            foreach ($list['data'] as  $v){
                M('ajax_dzz')->add($v);
            }

        }

    }


    //党组织-主题党日（一级）
public function dzz_ztdr_yi()
{
    set_time_limit(3600);
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

    unset($das);
    unset($da);
    unset($url);
    if($data) {
        S('dzz_ztdr', $data);   // 写入缓存，expire'=>600 :  设置有效时间：600秒
        foreach ($data as $v){
            $url = 'http://www.dysfz.gov.cn/apiXC/getHeldZtdrlistDetaiPage.do'; //党员党建
            $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
            $da['BRANCH_ID'] = $v['BRANCH_ID'];
            $da['TYPE'] = 1;    //type=1  已展开  type = 2 未展开
            $da['START'] = 1;
            $da['COUNT'] = 300;
            $das = json_encode($da);
            $data2 = $this->httpjson($url, $das);
            unset($das);
            unset($da);
            unset($url);
            if($data2){
                S('dzz_ztdr_1'.$v['BRANCH_ID'],$data2);
                foreach ($data2['data'] as $vv){
                    $url = 'http://www.dysfz.gov.cn/apiXC/ztdrActivityDetai.do'; //党员党建
                    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
                    $da['ACTIVITY_ID'] = $vv['activity_id'];
                    $das = json_encode($da);
                    $data3 = $this->httpjson($url, $das);
                    if($data3){
                        S('dzz_ztdr_detail_'.$vv['activity_id'],$data3);
                    }

                }

            }
        }
    }


}
    public function dzz_ztdr_wei()
    {

        set_time_limit(3600);
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

        unset($das);
        unset($da);
        unset($url);
        if($data) {
            S('dzz_ztdr_no', $data);   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            foreach ($data as $v){
                $url = 'http://www.dysfz.gov.cn/apiXC/getHeldZtdrlistDetaiPage.do'; //党员党建
                $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
                $da['BRANCH_ID'] = $v['BRANCH_ID'];
                $da['TYPE'] = 2;    //type=1  已展开  type = 2 未展开
                $da['START'] = 1;
                $da['COUNT'] = 300;
                $das = json_encode($da);
                $data2 = $this->httpjson($url, $das);
                unset($das);
                unset($da);
                unset($url);
                if($data2){
                    S('dzz_no_ztdr_1'.$v['BRANCH_ID'],$data2);
                }
            }
        }


    }
//党组织-党费缴纳（二级）
public function dzz_moneyed()
{
    set_time_limit(3600);
    $url = 'http://www.dysfz.gov.cn/apiXC/branchDZZfeeList.do'; //党员党建
    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
    $da['COUNT'] = 150;
    $da['START'] = 1;
    $das = json_encode($da);
    $data = $this->httpjson($url, $das);
    unset($das);
    unset($da);
    unset($url);
    if($data){
        S('dzz_yi_money',$data);   // 写入缓存，expire'=>600
        foreach ($data['data'] as $k=>$v){
            $url = 'http://www.dysfz.gov.cn/apiXC/branchfeeList.do'; //党员党建
            $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
            $da['COUNT'] = 200;
            $da['START'] = 1;
            $da['BRANCH_ID'] = $v['BRANCH_ID'];
            $das = json_encode($da);
            $data2 = $this->httpjson($url, $das);
            unset($das);
            unset($da);
            unset($url);
            if($data2){
                S('dzz_yi_money_'.$v['BRANCH_ID'],$data2);   // 写入缓存，expire'=>600
            }
        }

    }


}

//党组织-党费未缴纳
public function dzz_no_money()
{
    $url = 'http://www.dysfz.gov.cn/apiXC/branchDZZfeeList.do'; //党员党建
    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
    $da['COUNT'] = 150;
    $da['START'] = 1;
    $das = json_encode($da);
    $data = $this->httpjson($url, $das);
    unset($das);
    unset($da);
    unset($url);
    if($data){
        S('dzz_no_money',$data);   // 写入缓存，expire'=>600
        foreach ($data['data'] as $k=>$v){
            $url = 'http://www.dysfz.gov.cn/apiXC/branchnofeeList.do'; //党员党建
            $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
            $da['COUNT'] = 200;
            $da['START'] = 1;
            $da['BRANCH_ID'] = $v['BRANCH_ID'];
            $das = json_encode($da);
            $data2 = $this->httpjson($url, $das);
            unset($das);
            unset($da);
            unset($url);
            if($data2){
                S('dzz_no_money_'.$v['BRANCH_ID'],$data2);   // 写入缓存，expire'=>600
            }
        }

    }

}

//党员-主题党日（一级）
public function dy_ztdr()
{
    set_time_limit(3600);
    $url = 'http://www.dysfz.gov.cn/apiXC/recordPartyDTDRlist.do'; //党员党建
    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
    $da['COUNT'] = 100;
    $da['START'] = 1;
    $das = json_encode($da);
    $data = $this->httpjson($url, $das);
    unset($das);
    unset($da);
    unset($url);
    if($data){
        S('dzz_dy_ztdr_1',$data);   // 写入缓存，expire'=>600 :  设置有效时间：600秒
        foreach ($data['data'] as $k=>$v){
            $url = 'http://www.dysfz.gov.cn/apiXC/zbRecordDTDRlistPage.do'; //党员党建
            $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
            $da['COUNT'] = 100;
            $da['START'] = 1;
            $da['BRANCH_ID'] = $v['BRANCH_ID'];
            $das = json_encode($da);
            $data2 = $this->httpjson($url, $das);
            unset($das);
            unset($da);
            unset($url);
            if($data2){
                S('dzz_dy_ztdr_1_'.$v['BRANCH_ID'],$data2);   // 写入缓存，expire'=>600 :  设置有效时间：600秒
                foreach ($data2['data'] as $k=>$vv){
                    $url = 'http://www.dysfz.gov.cn/apiXC/zbPartyRecordDTDRDetailslistPage.do'; //党员党建
                    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
                    $da['COUNT'] = 100;
                    $da['START'] = 1;
                    $da['BRANCH_ID'] = $vv['BRANCH_ID'];
                    $da['TYPE'] = 1;  //  type =1 已展开
                    $das = json_encode($da);
                    $data3 = $this->httpjson($url, $das);
                    unset($das);
                    unset($da);
                    unset($url);
                    if($data3){
                        S('dzz_dy_ztdr_detail_'.$vv['BRANCH_ID'],$data3);
                    }
                }
            }
        }
    }

}

//党员-未展开主题党日
    public function dy_no_ztdr()
    {
        set_time_limit(3600);
        $url = 'http://www.dysfz.gov.cn/apiXC/recordPartyDTDRlist.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 100;
        $da['START'] = 1;
        $das = json_encode($da);
        $data = $this->httpjson($url, $das);
        unset($das);
        unset($da);
        unset($url);
        if($data){
            S('dzz_dy_no_ztdr_1',$data);   // 写入缓存，expire'=>600 :  设置有效时间：600秒
            foreach ($data['data'] as $k=>$v){
                $url = 'http://www.dysfz.gov.cn/apiXC/zbRecordDTDRlistPage.do'; //党员党建
                $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
                $da['COUNT'] = 100;
                $da['START'] = 1;
                $da['BRANCH_ID'] = $v['BRANCH_ID'];
                $das = json_encode($da);
                $data2 = $this->httpjson($url, $das);
                unset($das);
                unset($da);
                unset($url);
                if($data2){
                    S('dzz_no_dy_ztdr_1_'.$v['BRANCH_ID'],$data2);   // 写入缓存，expire'=>600 :  设置有效时间：600秒
                    foreach ($data2['data'] as $k=>$vv){
                        $url = 'http://www.dysfz.gov.cn/apiXC/zbPartyRecordDTDRDetailslistPage.do'; //党员党建
                        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
                        $da['COUNT'] = 100;
                        $da['START'] = 1;
                        $da['BRANCH_ID'] = $vv['BRANCH_ID'];
                        $da['TYPE'] = 2; //  type =1 未展开
                        $das = json_encode($da);
                        $data3 = $this->httpjson($url, $das);
                        unset($das);
                        unset($da);
                        unset($url);
                        if($data3){
                            S('dzz_no_dy_ztdr_detail_'.$vv['BRANCH_ID'],$data3);
                        }
                    }
                }
            }
        }



    }

//党员-2018报到
public function dy_2018_bd()
{
    $url = 'http://www.dysfz.gov.cn/apiXC/dwPartySignRecordlistPage.do';
    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
    $da['COUNT'] = 150;
    $da['START'] = 1;
    $das = json_encode($da);
    $data = $this->httpjson($url, $das);
    S('dzz_dy_2018_1',$data);   // 写入缓存
}

//动态监测统计
public function dt_count(){
    set_time_limit(3600);
    $where['status'] = 1;
    //入党申请人
    $application_dy = M('dr_dzz_application_party')->where($where)->count();
    //入党积极分子
    $activity_dy = M('dr_dzz_activity_dy')->where($where)->count();
    //发展对象
    $develop_dy = M('dr_dzz_develop_dy')->count();
    //2018年报道
    $sign1 = M()->query("SELECT id,`name`,organization FROM cxdj_dr_dy_ztdr  ztdr WHERE ztdr.`name` NOT IN (SELECT `name` FROM cxdj_dr_dy_late)");
    $sign =count($sign1);
    //不计入到会党员
    $url = 'http://www.dysfz.gov.cn/apiXC/dwPartylistPage.do';
    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
    $da['COUNT'] = 150;
    $da['START'] = 1;
    $das = json_encode($da);
    $list = $this->httpjson($url, $das);
    $arr_bj = array_column($list['data'],'partyNum');
    $late_dy = array_sum($arr_bj);
//        $late_dy = M('dr_dy_late')->where($where)->count();
    //闪光言行
    $url = 'http://www.dysfz.gov.cn/apiXC/getEvaluateByTypeAndStep.do'; //党员党建
    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
    $da['COUNT'] = 150;
    $da['START'] = 1;
    $da['TYPE'] = 1; // 1为评优评先，2为党员处分
    $da['STEP'] = 1; //查询一共分为3步，分别为1、2、3
    $das = json_encode($da);
    $list = $this->httpjson($url, $das);
    $arr_sg = array_column($list['data'],'NUMBER');
    $speech = array_sum($arr_sg);
//        $speech = M('dr_dy_sgyx')->where($where)->count();

    //党员-党性体检-cml

    $dr_dy_dxtj1 = M('ajax_user')->where()->count();
    $where_dxtj['status'] = 1;
    $where_dxtj['result1']='不健康';
    $dr_dy_dxtj2 = M('dr_dy_dxtj')->where($where_dxtj)->count();
    $where_dxtj['result1']='亚健康';
    $dr_dy_dxtj3 = M('dr_dy_dxtj')->where($where_dxtj)->count();
    $dr_dy_dxtj1 = $dr_dy_dxtj1 - $dr_dy_dxtj2 - $dr_dy_dxtj3 - 899;

    //党组织-已展开党性体检
    $dx_check1 = M()->query("SELECT COUNT(*) as `count` FROM cxdj.cxdj_dr_dzz_dxtj WHERE `status` = 1 AND organization NOT LIKE '%党委%' AND organization NOT LIKE '%党总支%'");
    $dx_check = $dx_check1[0]['count'];
    //党组织-未展开党性体检
    $dx_uncheck = M('dr_dzz_undxtj')->where($where)->count();
    //党组织-已展开主题党日
    $url = 'http://www.dysfz.gov.cn/apiXC/getHeldZTDRlistPage.do'; //党员党建
    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
    $da['COUNT'] = 300;
    $da['START'] = 1;
    $das = json_encode($da);
    $list = $this->httpjson($url, $das);
    $arr = array_column($list['data'],'ykz');
    $ztdr = array_sum($arr);
    //党组织-未展开主题党日
    $arr1 = array_column($list['data'],'wkz');
    $ztdr_no = array_sum($arr1);
    //党组织-已经缴纳党费
    $url = 'http://www.dysfz.gov.cn/apiXC/branchDZZfeeList.do'; //党员党建
    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
    $da['COUNT'] = 1500;
    $da['START'] = 1;
    $das = json_encode($da);
    $list = $this->httpjson($url, $das);
    $arr1 = array_column($list['data'],'yjn');
    $money = array_sum($arr1);
//        $data = M('dr_dzz_money')->query("SELECT SUM(money) AS `count`,organization,DATE_FORMAT(time,'%Y-%m-%d') AS `time`,COUNT(*) AS record FROM cxdj_dr_dzz_money WHERE money !=0  AND  status = 1  GROUP BY organization");
    //党组织 - 未缴纳党费
    $url = 'http://www.dysfz.gov.cn/apiXC/branchDZZfeeList.do'; //党员党建
    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
    $da['COUNT'] = 1500;
    $da['START'] = 1;
    $das = json_encode($da);
    $list = $this->httpjson($url, $das);
    $arr2 = array_column($list['data'],'wjn');
    $money_no = array_sum($arr2);
//        $data2 = M('dr_dzz_money')->query("SELECT SUM(money) AS `count`,organization,DATE_FORMAT(time,'%Y-%m-%d') AS `time`,COUNT(*) AS record FROM cxdj_dr_dzz_money WHERE money =0  AND  status = 1  GROUP BY organization");
//        $money_no = (int)$list['totalCount'];
    //党组织- 评优评先
    $url = 'http://www.dysfz.gov.cn/apiXC/getEvaluateByTypeAndStep.do'; //党员党建
    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
    $da['COUNT'] = 150;
    $da['START'] = 1;
    $da['TYPE'] = 1; // 1为评优评先，2为党员处分
    $da['STEP'] = 1; //查询一共分为3步，分别为1、2、3
    $das = json_encode($da);
    $list = $this->httpjson($url, $das);
    $arr_honor = array_column($list['data'],'NUMBER');
    $honor = array_sum($arr_honor);
//        $honor = M('dr_dzz_honor')->where($where)->count();
    //党员- 已展开主题党日
    $url = 'http://www.dysfz.gov.cn/apiXC/recordPartyDTDRlist.do'; //党员党建
    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
    $da['COUNT'] = 100;
    $da['START'] = 1;
    $das = json_encode($da);
    $list = $this->httpjson($url, $das);
    $arr_ztdred = array_column($list['data'],'yjn');
    $dy_ztdr = array_sum($arr_ztdred);
    //党员- 未展开主题党日
    $url = 'http://www.dysfz.gov.cn/apiXC/recordPartyDTDRlist.do'; //党员党建
    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
    $da['COUNT'] = 100;
    $da['START'] = 1;
    $das = json_encode($da);
    $list = $this->httpjson($url, $das);
    $arr_ztdring = array_column($list['data'],'wcj');
    $dy_unztdr = array_sum($arr_ztdring);

    $da['organize'] = array(
        'theme'=>array('start'=>$ztdr, 'unStart'=>$ztdr_no,),
        'partier'=>array('apply'=>$application_dy, 'active'=>$activity_dy,'develop_dy'=>$develop_dy),
        'experience'=>array('num'=>$dx_check, 'unNum'=>$dx_uncheck,),
        'pay'=>array('payed'=>$money, 'unPay'=>$money_no,),

    );
    $da['partier'] = array(
        'theme'=>array('join'=>$dy_ztdr, 'unJoin'=>$dy_unztdr,),
        'experience'=>array('health'=>$dr_dy_dxtj1, 'unHealth'=>$dr_dy_dxtj2,'yaHealth'=>$dr_dy_dxtj3),
        'register'=>array('num'=>$sign, 'unNum'=>$late_dy,),
        'appraise'=>array('sgyx'=>$speech, 'pypx'=>$honor,),
    );
    S('warning2',$da);
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


}