<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/11
 * Time: 10:42
 */
use Think\Controller;

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
function volunteer()
{
    $aa = 333;
    $url = 'http://www.dysfz.gov.cn/apiXC/volunteerList.do'; //党员党建
    $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
    $da['COUNT'] = 300;
//        $da['START'] = 1;
//        $das = json_encode($da);
//        $list = httpjson($url, $das);
//        dump($list);die;
    for ($i = 1; $i < 147; $i++) {
        $da['START'] = $i;
        $das = json_encode($da);
        $list = httpjson($url, $das);
        foreach ($list['data'] as $k => $v) {
            $id = M('ajax_volunteer_copy1')->where(array('VOLUNTEER_ID' => $v['VOLUNTEER_ID']))->find();
            if ($id) {
                M('ajax_volunteer_copy1')->where(array('VOLUNTEER_ID' => $v['VOLUNTEER_ID']))->save($v);
            } else {
                M('ajax_volunteer_copy1')->add($v);
            }
        }
    }



    $fp = @fopen("C:/xampp/www/timelog.txt", "a+");
    date_default_timezone_set(PRC);
    $data = date("Y-m-d H:i:s",time());
    fwrite($fp, $aa. " 更新成功！\n");
    fclose($fp);

}

volunteer();

