<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/9
 * Time: 13:24
 */
namespace  Cx\Controller;
use Think\Controller;
class WelfareController extends Controller
{
    public function index()
    {
        $Model = M('sign_zhch');
        $res = $Model->where(array('state'=>1,'status'=>1))->field('id,content,img')->order('cer_time DESC')->find();
        $items = get_cover($res['img']);
        $res['path'] ='http://183.131.86.64:8620'.$items['path'];

        $zh = $Model->query('SELECT
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
                                AND zhch.`status` = 1');

        foreach ($zh as $k=>&$v)
        {
            if($v['count'] == $v['num']){
                $v['status'] = '已完成';
            }else{
                $v['status'] = '未完成';
            }
        }

        //积分排行榜
        $integral = M('wxuser')->where('integral IS NOT NULL')->field('id,headimgurl,nickname,integral')->order('integral DESC')->limit(5)->select();


        $this->assign(array(
            'integral'=>$integral,
            'data'=>$zh,
            'res'=>$res
        ));
        $this->display('index');
    }

    //互助进行时列表
    public function hz_list()
    {
        $Model = M();
        $zh = $Model->query('SELECT
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
                                AND zhch.`status` = 1');

       foreach ($zh as $k=>&$v)
       {
           if($v['count'] == $v['num']){
               $v['status'] = '已完成';
           }else{
               $v['status'] = '未完成';
          }
       }

        $this->assign('data',$zh);
        $this->display('interacting_list');
    }

    //互助进行时详情
    public function hz_detail()
    {
        $Model = M();
        $id = I('id');
        $detail = $Model->query("SELECT
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
        foreach ($detail as $k=>&$v){
            $already_rate =  (round(($v['num']/$v['count']),2)*100).'%';
        }

        $rate = $already_rate;
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

        $this->assign(array(
            'detail'=>$detail[0],
            'rate' =>$rate,
            'headimg'=>$headimg
        ));
        $this->display('interacting_detail');
    }

    //党员积分列表
    public function interacting_list()
    {
        $integral = M('wxuser')->where('integral IS NOT NULL')->field('id,headimgurl,nickname,integral')->order('integral DESC')->limit(12)->select();
        $this->assign('integral',$integral);
        $this->display('party_van');
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
    public function qrcodefor($id=null)
    {
        $this->qrcode('http://cxdj.cmlzjz.com/home/wxindex/goods_det/goodsid/' . $id);
    }

    //积分商城
    public function market()
    {
        $Model = M('goods');
        $goods = $Model->where(array('status'=>1))->field('id,prod_name,prod_pic,prod_intr,prod_num,price')->select();
        foreach ($goods as $k=>&$v){
            $items = get_cover($v['prod_pic']);
            $v['path'] = 'http://183.131.86.64:8620'.$items['path'];
            $v['ewm']  = "http://183.131.86.64:8620/cx/welfare/qrcodefor/id/".$v['id'];
        }


        $this->assign('goods',$goods);
        $this->display('market');
    }
}