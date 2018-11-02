<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/30
 * Time: 15:37
 */

namespace Home\Controller;


use Think\Controller;


set_time_limit(3600);

class TimingController extends Controller
{

    //党员 -- 动态监测--党员考评--闪光言行
    //S('light')  第一层
    //S('light_###')  第二层
    //S('light_light_###')  第三层
    public function light(){
        set_time_limit(3600);

        $url = 'http://www.dysfz.gov.cn/apiXC/getEvaluateByTypeAndStep.do'; //党员党建
            $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
            $da['COUNT'] = 150;
            $da['START'] = 1;
            $da['TYPE'] = 1; // 1为评优评先，2为党员处分
            $da['STEP'] = 1; //查询一共分为3步，分别为1、2、3
            $das = json_encode($da);
            $data = $this->httpjson($url, $das);
            unset($das);
            unset($da);
            unset($url);
            if($data){
                S('light',$data);
                foreach ($data['data'] as $v){
                    $url = 'http://www.dysfz.gov.cn/apiXC/getEvaluateByTypeAndStep.do'; //党员党建
                    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
                    $da['COUNT'] = 500;
                    $da['START'] = 1;
                    $da['BRANCH_ID'] = $v['BRANCH_ID'];
                    $da['TYPE'] = 1; // 1为评优评先，2为党员处分
                    $da['STEP'] = 2; //查询一共分为3步，分别为1、2、3
                    $das = json_encode($da);
                    $data2 = $this->httpjson($url, $das);
                    unset($das);
                    unset($da);
                    unset($url);
                    if($data2){
                        S('light_'.$v['BRANCH_ID'],$data2);
                        foreach ($data2['data'] as $vv){
                            $url = 'http://www.dysfz.gov.cn/apiXC/getEvaluateByTypeAndStep.do'; //党员党建
                            $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
                            $da['COUNT'] = 500;
                            $da['START'] = 1;
                            $da['BRANCH_ID'] = $vv['BRANCH_ID'];
                            $da['TYPE'] = 1; // 1为评优评先，2为党员处分
                            $da['STEP'] = 3; //查询一共分为3步，分别为1、2、3
                            $das = json_encode($da);
                            $data3 = $this->httpjson($url, $das);
                            if($data3){
                                S('light_light_'.$vv['BRANCH_ID'],$data3);
                            }
                        }
                    }
                }
            }
    }

    //党员 -- 动态监测--报到登记--2018
    //S('report')  第一层
    //S('report_###')  第二层
    //S('report_report_###')  第三层
    public function report(){
        set_time_limit(3600);

        $url = 'http://www.dysfz.gov.cn/apiXC/dwPartySignRecordlistPage.do';
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 500;
        $da['START'] = 1;
        $das = json_encode($da);
        $data = $this->httpjson($url, $das);
        unset($das);
        unset($da);
        unset($url);
        if($data){
            S('report',$data);
            foreach ($data['data'] as $v){
                $url = 'http://www.dysfz.gov.cn/apiXC/dzPartySignRecordlistPage.do';
                $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
                $da['START'] = 1;
                $da['COUNT'] = 4000;
                $da['BRANCH_ID'] = $v['BRANCH_ID'];
                $das = json_encode($da);
                $data2 = $this->httpjson($url,$das);
                unset($das);
                unset($da);
                unset($url);
                if($data2){
                    S('report_'.$v['BRANCH_ID'],$data2);
                    foreach ($data2['data'] as $vv){
                        $url = 'http://www.dysfz.gov.cn/apiXC/dzPartySignRecordDetaillistPage.do';
                        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
                        $da['START'] = 1;
                        $da['COUNT'] = 4000;
                        $da['BRANCH_ID'] = $vv['BRANCH_ID'];
                        $das = json_encode($da);
                        $data3 = $this->httpjson($url,$das);
                        if($data3){
                            S('report_report_'.$vv['BRANCH_ID'],$data3);
                        }
                    }
                }
            }
        }
    }

    //党员 -- 动态监测--报到登记--不计到会
    //S('NoRecord')  第一层
    //S('NoRecord_###')  第二层
    //S('NoRecord_NoRecord_###')  第三层
    public function NoRecord(){
        set_time_limit(3600);
        $url = 'http://www.dysfz.gov.cn/apiXC/dwPartylistPage.do';
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 500;
        $da['START'] = 1;
        $das = json_encode($da);
        $data = $this->httpjson($url, $das);
        unset($das);
        unset($da);
        unset($url);
        if($data){
            S('NoRecord',$data);
            foreach ($data['data'] as $v){
                $url = 'http://www.dysfz.gov.cn/apiXC/dzPartylistPage.do';
                $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
                $da['START'] = 1;
                $da['COUNT'] = 4000;
                $da['BRANCH_ID'] = $v['BRANCH_ID'];
                $das = json_encode($da);
                $data2 = $this->httpjson($url,$das);
                unset($das);
                unset($da);
                unset($url);
                if($data2){
                    S('NoRecord_'.$v['BRANCH_ID'],$data2);
                    foreach ($data2['data'] as $vv){
                        $url = 'http://www.dysfz.gov.cn/apiXC/dzPartyDetaillistPage.do';
                        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
                        $da['START'] = 1;
                        $da['COUNT'] = 4000;
                        $da['BRANCH_ID'] = $vv['BRANCH_ID'];
                        $das = json_encode($da);
                        $data3 = $this->httpjson($url,$das);
                        if($data3){
                            S('NoRecord_NoRecord_'.$vv['BRANCH_ID'],$data3);
                        }
                    }
                }
            }
        }
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