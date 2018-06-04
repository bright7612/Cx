<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Dyuuu\Controller;

use Think\Controller;


/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class LjzController extends Controller
{
    protected function _initialize()
    {
        /*读取站点配置*/
        $config = api('Config/lists');
        C($config); //添加配置

        if (!C('WEB_SITE_CLOSE')) {
            $this->error(L('_ERROR_WEBSITE_CLOSED_'));
        }

        $this->appId = 'wx29f2633773d38559';
        $this->appSecret = '5bf7710698eb2801e86f81f571e724e7';
//        $server_host = '/';
//        C('SERVER_HOST',$server_host);
        //echo getCurrentUrl();
        $this->wxindex(ACTION_NAME);
    }

    public function test()
    {
        $userInfo = cookie('userInfo');
        $this->assign('nick', $userInfo['nickname']);
        $head = $userInfo['headimgurl'];
        $this->assign('head', str_split($head, strlen($head) - 1)[0] . "132");
        $this->display();
    }


    public function xlzy()
    {

        $redirect_url = "https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzUzNDA1NTY5MQ==&scene=124#wechat_redirect";
        echo "<script language=\"javascript\">";
        echo "document.location=\"" . $redirect_url . "\"";
        echo "</script>";

    }

    public function djffz()
    {
        //党建服务站
        $redirect_url = "http://mp.weixin.qq.com/s/ORHTjGjNzjCUnqcSdDmEIw";
        echo "<script language=\"javascript\">";
        echo "document.location=\"" . $redirect_url . "\"";
        echo "</script>";

    }

    public function wxindex($type = 1,$action = "wxindex")
    {
        if ($_GET['code']) {
            $accessToken = $this->accessToken($_GET['code']);
            if ($accessToken['expires_in']) {
                $accessToken['expire_time'] = time() + 7000;
                session("accessToken", $accessToken);
            }
            $userInfo = $this->getUserInfo($accessToken['access_token'], $accessToken['openid']);
        } else {
            $accessToken = session("accessToken");
            $userInfo = $this->getUserInfo($accessToken['access_token'], $accessToken['openid']);
        }
        cookie('wxOpenId', $userInfo['openid']);
        cookie('userInfo', $userInfo);
        if (!cookie('wxOpenId')) {
            $link = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            if (strstr($link, "?")) {
                $redirect_uri = urlencode("/home/ljz/" . $action);
            } else {
                $redirect_uri = urlencode($link);
            }

            $goindex = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->appId . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';

            echo "<script language=\"javascript\">";
            echo "document.location=\"" . $goindex . "\"";
            echo "</script>";
            exit;
        } else {
//            dump(cookie("userInfo"));
//            exit;
            $map['openid'] = $userInfo['openid'];
            $user = M("wxmember")->where($map)->find();
            if (!$user) {
                $data['openid'] = $userInfo['openid'];
                $data['sex'] = $userInfo['sex'];
                $data['nick'] = $userInfo['nickname'];
                $data['headimage'] = $userInfo['headimgurl'];
                M("wxmember")->add($data);
            }

            $mapinfo['openid'] = $userInfo['openid'];
            $user = M("wxmember")->where($mapinfo)->find();
            cookie('wxmember', $user);
            if($type == 1){
                $urls = 'http://hqdj.cmlzjz.com/wxindex/pages/blockWindow.html';
                echo "<script language=\"javascript\">";
                echo "document.location=\"" . $urls . "\"";
                echo "</script>";
                exit;
            }else if( $type == 2){
                $urls = 'http://hqdj.cmlzjz.com/wxindex/pages/blockLesson.html';
                echo "<script language=\"javascript\">";
                echo "document.location=\"" . $urls . "\"";
                echo "</script>";
                exit;
            }else if( $type == 3){
                $urls = 'http://hqdj.cmlzjz.com/home/ljz/my';
                echo "<script language=\"javascript\">";
                echo "document.location=\"" . $urls . "\"";
                echo "</script>";
                exit;
            }
        }
    }

    public function getUserInfo($access_token, $openid)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN";
        $res = json_decode($this->httpGet($url), true);
        return $res;
    }

    public function accessToken($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appId&secret=$this->appSecret&code=" . $code . "&grant_type=authorization_code";
        $res = json_decode($this->httpGet($url), true);
        dump($res);exit;
        $url2 = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=$this->appId&grant_type=refresh_token&refresh_token=" . $res['refresh_token'];
        $res2 = json_decode($this->httpGet($url2), true);

        return $res2;
    }

    public function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    //数据整理
    public function cml()
    {
//  	echo 'asdf';exit;
        $data = M('zzjxm')->field("id,pic1,pic")->select();
        foreach ($data as $k => $v) {
            $dataadd = '';
            $savedata = '';
            if ($v['pic'] == 0) {
                $dataadd['path'] = '/Public/ljz/xm/' . $v['pic1'];
                $dataadd['type'] = 'loca';
                $dataadd['status'] = 1;
                $dataadd['create_time'] = time();
                $addid = M("picture")->add($dataadd);
                $savedata['pic'] = $addid;
                $save = M('zzjxm')->where('id=' . $v['id'])->save($savedata);
                echo $addid . '-';
            }

        }
    }

    //党员报到
    public function dybd1()
    {
        $this->assign('toptitle', '党员报到');

        $this->display();
    }

    //党员报到
    public function dybd2()
    {
        $this->assign('toptitle', '党员报到');
        $map['status'] = 1;
        $map["type"] = "居民区";
        $data = M('dzz')->where($map)->select();
        $this->assign('data', $data);
        $this->display();
    }

    //党员报到
    public function dybd3($id = 0, $name = "", $addr = "", $mobile = "", $dzz = "")
    {
        $wxmember = cookie('wxmember');
        $this->assign('wxmember', $wxmember);
        if (IS_POST) {
            $data['name'] = $name;
            $data['addr'] = $addr;
            $data['mobile'] = $mobile;
            $data['dzz'] = $dzz;

            $map['openid'] = $wxmember['openid'];
            $save = M("wxmember")->where($map)->save($data);
            $this->success('报到成功');
        } else {
            $dzz = M('dzz')->where('id=' . $id)->find();

            $this->assign('dzz', $dzz);
            $this->assign('toptitle', '党员报到');

            $this->display();
        }
    }

    //党员双报到
    public function dysbd1()
    {
        $this->assign('toptitle', '党员双报到');
        $this->display();
    }

    //党员双报到
    public function dysbd2()
    {
        $this->assign('toptitle', '党员双报到');
        $map['status'] = 1;
        $map["type"] = "居民区";
        $data = M('dzz')->where($map)->select();
        $this->assign('data', $data);
        $this->display();
    }

    //党员双报到
    public function dysbd3($id = 0)
    {
        $wxmember = cookie('wxmember');
        $this->assign('wxmember', $wxmember);
        if (IS_POST) {
            $data["name"] = $_POST["name"];
            $data["tel"] = $_POST["mobile"];
            $data["sex"] = $_POST["sex"];
            $data["gzdw"] = $_POST["gzdw"];
            $data["ydzz"] = $_POST["ydzz"];
            $data["addr"] = $_POST["addr"];
            $data["dzz"] = $_POST["sbddzz"];
            $data["sbdstate"] = 0;

            if (!($data["name"] && $data["tel"] && $data["dzz"])) {
                $this->error('参数有误,请填写完整！');
            }

            $map['name'] = $data["name"];
            $map['status'] = 1;
            $map['tel'] = $data["tel"];
            $map['dzz'] = $data["dzz"];
            $map['sbd'] = 1;
            $partymember = M("party_member")->where($map)->find();
            $sql = M()->getlastsql();
            if ($partymember) {
                $data["id"] = $partymember["id"];
                $sbdpmid = M("party_member")->save($data);
            } else {
                $data["sbd"] = 1;
                $sbdpmid = M("party_member")->add($data);
            }
            if (!$sbdpmid) {
                $this->error("报道失败,请稍后再试");
            }

            $data2["sbdpmid"] = $sbdpmid;
            $map2['openid'] = $wxmember['openid'];
            $save = M("wxmember")->where($map2)->save($data2);
            if ($save) {
                $this->success('报到成功');
            } else {
                $this->error("报道失败,请稍后再试" . $sbdpmid);
            }
        } else {
            $dzz = M('dzz')->where('id=' . $id)->find();

            $this->assign('dzz', $dzz);
            $this->assign('toptitle', '党员双报到');

            $this->display();
        }
    }


    //长城家园
    public function ccjy1()
    {
        $this->assign('toptitle', '长城家园');

        $this->display();
    }

    //长城家园
    public function ccjy2()
    {
        $this->assign('toptitle', '长城家园');

        $this->display();
    }

    //红色生活
    public function index()
    {
        $this->redirect('/wxindex/pages/blockWindow', '', 5, '页面跳转中...');
        $this->assign('toptitle', '红色生活');

        $this->display();
    }

    //组织管理
    public function manage()
    {
        $this->assign('toptitle', '组织管理');

        $this->display();
    }

    //组织管理
    public function dzz($type = 1)
    {
        $role = SESSION("role");
        if (!$role) {
            $toptitle = '权限不足';
            $this->assign('toptitle', $toptitle);
            $this->display('permissiondenied');
            exit;
        }
        if ($type == 1) {
            $toptitle = '居民区党组织';
            $map['type'] = "居民区";
        }
        if ($type == 2) {
            $toptitle = '两新党组织';
            $map['type'] = "“两新”党组织";
        }
        if ($type == 3) {
            $toptitle = '机关中心党组织';
            $map['type'] = "机关、中心阵地";
        }
        if ($type == 4) {
            $toptitle = '驻区单位党组织';
            $map['type'] = "驻区单位党组织";
        }

        $map['status'] = 1;
        $data = M('dzz')->where($map)->select();

        $this->assign('toptitle', $toptitle);
        $this->assign('data', $data);
        $this->display();
    }

    public function adminlogin($name = '', $pass = '')
    {
        $this->assign("toptitle", "党组管理员登录");
        if ($_POST) {
            $map['username'] = $name;
            $user = M("ucenter_member")->where($map)->find();
            if (think_ucenter_md5($pass, 'IkaMrDGLw2lW3XNTpZohmOHFU9ug7A4Qy5iJ68Ec') === $user['password']) {
                SESSION("role", $user['dzz']);
                $this->success('登录成功！', 'manage');
            } else {
                $this->error('用户名密码有误！');
            }

        } else {
            $this->display();
        }

    }

    public function dzzxq($id)
    {
        $role = SESSION("role");
        if (!$role) {
            $toptitle = '权限不足';
            $this->assign('toptitle', $toptitle);
            $this->display('permissiondenied');
            exit;
        }
        $map['id'] = $id;
        $data = M('dzz')->where($map)->find();
        $this->assign('toptitle', $data['name']);
        $this->assign('data', $data);

        $pydata = $this->getPyData($id);
        if ($pydata && count($pydata) > 2) {
            $pydata = array_slice($pydata, 0, 2);
        }
        $this->assign("pydata", $pydata);
        $this->assign('dzz', $id);
        $this->display();
    }

    public function peopleList()
    {
        $role = SESSION("role");
        if (!$role) {
            $toptitle = '权限不足';
            $this->assign('toptitle', $toptitle);
            $this->display('permissiondenied');
            exit;
        }
        $dzz = I("dzz", 0, "intval");

        if ($dzz != $role) {
            $toptitle = '权限不足';
            $this->assign('toptitle', $toptitle);
            $this->display('permissiondenied2');
            exit;
        }


        $type = I("type", 0, "intval");
        $this->assign("dzz", $dzz);
        $this->assign("type", $type);
        $title = "党员名册";
        switch ($type) {
            case 1;
                $title = "党员名册";
                break;
            case 2;
                $title = "双报到党员";
                break;
            case 3;
                $title = "免过组织生活党员";
                break;
            case 4;
                $title = "出国保留党籍";
                break;
        }
        $this->assign("toptitle", "党员名册");
        $this->assign("title", $title);
        $this->display();
    }

    public function listPeople($page = 1, $r = 10)
    {
        $dzz = I("dzz", 0, "intval");
        $type = I("type", 0, "intval");
        $map = array();
        if ($dzz) {
            $map["dzz"] = $dzz;
        }
        switch ($type) {
            case 1;
                $map['dymc'] = 1;
                break;
            case 2;
                $map['sbd'] = 1;
                break;
            case 3;
                $map['mgzzsh'] = 1;
                break;
            case 4;
                $map['cgbldj'] = 1;
                break;
        }
        $data = M("party_member")->where($map)->page($page, $r)->select();
        $res["result"] = 1;
        $res["data"] = $data;
        $this->ajaxReturn($res);
    }


    //组织管理=-党员信息
    public function people($type = 1, $id = '')
    {
        $role = SESSION("role");
        if (!$role) {
            $toptitle = '权限不足';
            $this->assign('toptitle', $toptitle);
            $this->display('permissiondenied');
            exit;
        }

//        if ($type == 1) $toptitle = '免过组织生活党员';
//        if ($type == 2) $toptitle = '出国保留党籍党员';
//        if ($type == 3) $toptitle = '双报到党员';
//        if (!$type) $toptitle = '党员';
        $toptitle = '党员信息';

        $this->assign('toptitle', $toptitle);

//        if ($type == 1) {
////			echo 'asdfasdf';exit;
//            $this->display("partierFree");
//        } elseif ($type == 2) {
//            $this->display("partierOverseas");
//        } elseif ($type == 3) {
//            $this->display("partierDouble");
//        } else {
//            $this->display("partierCatalog");
//        }

        $info = M("party_member")->find($id);
        header('Content-Type:text/html; charset=utf-8');
        $sexopt = array("1" => "男", "2" => "女");
        $info["sexstr"] = $sexopt[$info["sex"]];
        $this->assign('data', $info);
        $this->display("partierCatalog");
    }

    /**
     * 党员民主评议
     */
    public function py($dzz)
    {
        $role = SESSION("role");
        if (!$role) {
            $toptitle = '权限不足';
            $this->assign('toptitle', $toptitle);
            $this->display('permissiondenied');
            exit;
        }
        if ($dzz != $role) {
            $toptitle = '权限不足';
            $this->assign('toptitle', $toptitle);
            $this->display('permissiondenied2');
            exit;
        }
        $dzzinfo = M("dzz")->find($dzz);
        $this->assign("toptitle", $dzzinfo["namestr"] . "民主评议");
        $this->assign("data", $this->getPyData($dzz));
        $this->display();
    }

    protected function getPyData($dzz)
    {
        $map["status"] = 1;
        $map["dzzid"] = $dzz;
        //哪些年有数据
        $years = M("data_mzpy")->where($map)->distinct(true)->field("year")->order("year desc")->select();
        $data = array();
        foreach ($years as $li) {
            $year = $li['year'];
            $map["year"] = $year;
            $count = M("data_mzpy")->where($map)->group("suggest")->field("suggest,count(*)")->select();
            $count = array_combine(array_column($count, "suggest"), array_column($count, "count(*)"));
            $data[$year]["yx"] = $count["优秀"];
            $data[$year]["hg"] = $count["合格"];
            $data[$year]["jbhg"] = $count["基本合格"];
            $data[$year]["bhg"] = $count["不合格"];
            $data[$year]["mp"] = $count["免评"];
            $data[$year]["gzr"] = $count["刚转入"];
            $data[$year]["bldj"] = $count["保留党籍"];
            $data[$year]["wz"] = $count[""];//未知
            $data[$year]["total"] = $count["优秀"] + $count["合格"] + $count["基本合格"] + $count["不合格"] + $count["免评"] + $count["刚转入"] + $count["保留党籍"] + $count[""];
        }
        return $data;
    }


    //红色生活--组织生活
    public function life()
    {
        $this->assign('toptitle', '组织生活');
        $data = M("dzz")->where($map)->field('id,name')->select();
        $this->assign('data', $data);
        $this->display();
    }

    //红色生活--组织生活
    public function zzlife($id)
    {
        $this->assign('toptitle', '组织生活');
        $this->assign('dzzid', $id);
        $dzzdata = M("dzz")->where('id=' . $id)->field('id,name')->find();
        $this->assign('dzzdata', $dzzdata);
        $this->display();
    }

    //红色生活--组织生活
    public function orglife($id)
    {
        $this->assign('toptitle', '组织生活');
        $map["ljz_data_zzshh.id"] = $id;
        $map["ljz_data_zzshh.status"] = 1;
        $data = M('data_zzshh')->field('ljz_data_zzshh.*,ljz_dzz.name as dzzname ')->where($map)->join("left join ljz_dzz on ljz_data_zzshh.dzzid = ljz_dzz.id")->find();
        $this->assign('data', $data);
        $this->display();
    }

    public function listZzshh($page = 1, $rows = 10)
    {
        $result['status'] = 1;
        $map['ljz_data_zzshh.status'] = 1;
        $result['result'] = M('data_zzshh')->field('ljz_data_zzshh.*,ljz_dzz.name as dzzname ')->where($map)->page($page, $rows)->join("left join ljz_dzz on ljz_data_zzshh.dzzid = ljz_dzz.id")->select();
        if ($result['result'] === false) {
            $result['status'] = -1;
        } else {
            foreach ($result['result'] as &$v) {
                $v['datetime'] = date("Y-m-d", $v['datetime']);
            }
        }

        echo json_encode($result);
    }


    //红色生活-组织关系转接
    public function zzgx()
    {
        if ($_POST) {
            $data = $_POST;
//			dump($data);
            header('Content-Type:text/html; charset=utf-8');
            $data['create_time'] = time();
            $map['mobile'] = $data['mobile'];
            $map['create_time'] = array("gt", time() - 5);
            $search = M("trans_org_relation")->where($map)->find();
            if ($search['id']) {
                $this->error('此手机段时间内不能再预约了，请耐心等待！', 'zzgx');
                exit;
            }

            if ($data['order_time'] && $data['name'] && $data['mobile'] && $data['order_date']) {
                $adddata = M("trans_org_relation")->add($data);
                if ($adddata) {

                  //  smsljz($data['mobile'], $msg = '您好:' . $data['name'] . ',党组织关系转入转出预约成功！日期：' . $data['order_date'] . ' ' . $data['order_time'] );
                    $this->success('添加成功', 'zzgx');
                } else {
                    $this->error('数据异常', 'zzgx');
                }
            } else {
                $this->error('参数有误,请填写完整！', 'zzgx');
            }


        } else {
            for ($x = 0; $x <= 30; $x++) {
                $datetime = date("Y-m-d", strtotime($x . " day"));
                $timestamp = date("w", strtotime($x . " day"));
                if ($timestamp != 0 && $timestamp != 6) {
                    $datelist[] = $datetime;
                }
            }
            $this->assign('datelist', $datelist);
            $this->display();
        }

    }

    /**
     * 我的预约
     */
    public function myorder()
    {
        $this->assign('toptitle', '我的预约');
        $this->display();
    }

    //红色生活-意见反馈
    public function dyxs()
    {
        if (IS_POST) {

            if ($_POST["anonymous"]) {
                $data["voice"] = $_POST["voice"];
                $data["anonymous"] = 1;
            } else {
                $data = $_POST;
                $data["anonymous"] = 0;
            }

            $res = M('voice')->add($data);
            if ($res === false) {
                $result['result'] = -1;
                $result['msg'] = "提交失败";
            } else {
                $result['result'] = 1;
                $result['msg'] = "提交成功";
            }
            echo json_encode($result);
        } else {
            $dzz = M("dzz")->select();
            $this->assign('dzz', $dzz);

            $map['status'] = 1;
            $data = M('voice')->limit(8)->order('id desc')->where($map)->select();
            $this->assign('data', $data);
            $this->assign('toptitle', '党员心声');
            $this->display();
        }
    }

    //红色生活-党费缴纳
    public function dfjn()
    {
        if (IS_POST) {
            $data = $_POST;
            $data['sum'] = $data['sum'] * 100;
            if ($data['sum'] && $data['idnum'] && $data['name']) {
                $res = M("dues_pay")->add($data);
                if ($res > 0) {
                    $result['result'] = 1;
                    $result['id'] = $res;
                } else {
                    $result['result'] = -1;
                    $result['msg'] = "数据异常";
                }
            } else {
                $result['result'] = -1;
                $result['msg'] = "参数错误";
            }
            echo json_encode($result);
        } else {
            $dzztype = M("dzz")->field('type')->group('type')->select();
            foreach ($dzztype as $v) {
                $map['type'] = $v['type'];
                $dzz[$v['type']] = M('dzz')->where($map)->select();
            }

            $this->assign("dzz", json_encode($dzz));
            $this->assign("dzztype", $dzztype);
            $this->assign('toptitle', '党费缴纳在线计算');
            $this->display();
        }
    }

    //区域党建
    public function qydj()
    {
        $this->assign('toptitle', '区域党建');
        $this->display();
    }

    //区域党建--单位党建
    public function dwdj()
    {
        $map['status'] = array('egt',1);
        $list = M("link")->where($map)->select();
        foreach($list as $k=>$v){
            $list[$k]['pic'] = pic($v['pic']);
        }
        $this->assign('data', $list);
        $this->assign('toptitle', '单位党建');
        $this->display();
    }

    //区域党建--资源清单
    public function zyqd()
    {
        $this->assign('toptitle', '资源清单');
        $map['issue_id'] = 62;
        $map['status'] = array('egt',1);
        $data = M('issue_content')->where($map)->order('id desc')->field('id,title,content')->find();
        $this->assign('data', $data);
        $this->display();
    }


    //区域党建--资源清单
    public function zymap()
    {
        $typeopt = array(1 => "居民区与驻区单位联建清单",
//            2 => "合庆党建服务站点清单",
            3 => "合庆街道活动场地清单",
//            4 => "“合庆公益城”驻区单位资源清单",
//            5 => "点亮心愿 社区单位认领心愿",
//            6 => "公益进工地认领清单",
//            7 => "白领课堂",
            8 => "社会组织服务清单",
//            9 => "合庆街道自治金项目清单",
            10 => "社区文化活动清单",
//            11 => "金融服务进社区清单",
//            12 => "群团服务项目清单",
//            13 => "社区事务受理事项清单",
//            14 => "拥军优属服务项目清单"
        );
        $type = I("type", 1, "intval");
        $this->assign('type', $type);
        if ($type == 2 || $type == 3 || $type == 7 || $type == 8 || $type == 10 || $type == 14) {
            $this->assign('toptitle', '资源清单');
            $this->display();
        } else {
            $this->assign('title', $typeopt[$type]);
            $this->display("zylist");
        }
    }

    public function zylist1($type)
    {
        $map['type'] = $type;
        $map['status'] = 1;
        $list = M('data_zyqd')->where($map)->select();
        $res['result'] = 1;
        $res['list'] = $list;
        header("Content-Type:text/html; charset=utf-8");
        foreach ($list as &$v) {
            if (!$v['lat'] && $v['addr']) {
                $geores = geoEncode($v['addr']);
                if ($geores) {
                    $v['lat'] = $geores['lat'];
                    $v['lng'] = $geores['lng'];
                    $geores['id'] = $v['id'];
                    M("data_zyqd")->save($geores);
                }
            }
        }
        echo json_encode($res);
        exit;
    }

    public function zylist2($type, $page = 1, $rows = 10)
    {
        $map['type'] = $type;
        $map['status'] = 1;

        $list = M('data_zyqd')->where($map)->page($page, $rows)->select();
        if (!$list) {
            $list = array();
        }
        $res['result'] = 1;
        $res['data'] = $list;
        header("Content-Type:text/html; charset=utf-8");
        foreach ($list as &$v) {
            if (!$v['lat'] && $v['addr']) {
                $geores = geoEncode($v['addr']);
                if ($geores) {
                    $v['lat'] = $geores['lat'];
                    $v['lng'] = $geores['lng'];
                    $geores['id'] = $v['id'];
                    M("data_zyqd")->save($geores);
                }
            }
        }
        echo json_encode($res);
        exit;
    }

    //区域党建--公益城
    public function gyc()
    {
        $map = array();
        $map["status"] = 1;
        $list = M("project_claim")->where($map)->select();
        foreach ($list as &$v) {
            if ($v["pic1"]) {
                $pics = explode(",", $v["pic1"]);
                $v["picpath"] = get_cover($pics[0], "path");
                $v["picpath2"] = get_cover($pics[1], "path");
            }
        }
        unset($v);

        $this->assign('list', $list);
        $this->assign('toptitle', '项目直通车');
        $this->display();
    }

    /**
     * @see  gycDetail
     * @deprecated
     * @param int $id
     */
//    public function gycxq($id = 0)
//    {
//        $map["id"] = $id;
//        $data = M("project_claim")->where($map)->find();
//        if ($data["pic1"]) {
//            $pics = explode(",", $data["pic1"]);
//            $this->assign("pics", $pics);
//        }
//        $this->assign("data", $data);
//        $this->assign('toptitle', '项目详情');
//        $map2["status"] = 1;
//        $map2["aid"] = $id;
//        $map2["state"] = 1;
//        $rldata = M("data_gycrl")->where($map2)->select();
//        foreach ($rldata as &$v) {
//            $v["rltype_str"] = M("data_rltype")->getFieldById($v["rltype"], "name");
//        }
//        unset($v);
//        $this->assign('rldata', $rldata);
//        if ($id == 14) {
//            $rltypelist = M("data_rltype")->where(array("status" => 1,"aid"=>$id))->select();
//            $this->assign('rltypelist', $rltypelist);
//            $this->display("gycBuild");
//        } else if ($id == 15) {
//            $this->display("gycStudy");
//        } else {
//            $this->display();
//        }
//    }


    public function gycDetail($id)
    {
        if(IS_POST){
            $data["aid"] = $id;
            $data["pj"] =$_POST["pj"];
            $data["content"] =$_POST["content"];
            $data["createtime"] =time();

            if ($data['aid'] && $data['pj'] && $data['content']) {
                $res = M("data_pj")->add($data);
                if ($res > 0) {
                    $this->success('评价成功，谢谢您的参与！');
                } else {
                    $this->error('数据异常,请稍后再试！');
                }
            } else {
                $this->error('参数有误,请填写完整！');
            }
        }else {
            $map["id"] = $id;
            $data = M("project_claim")->where($map)->find();
            if ($data["pic1"]) {
                $pics = explode(",", $data["pic1"]);
                $this->assign("pics", $pics);
            }
            $this->assign("data", $data);
            $this->assign('toptitle', '项目详情');
            $map2["status"] = 1;
            $map2["aid"] = $id;
            $map2["state"] = 1;
            $rldata = M("data_gycrl")->where($map2)->select();
            foreach ($rldata as &$v) {
                $map3["id"] = array("in", $v["rltype"]);
                $types = M("data_rltype")->where($map3)->select();
                $v["rltype_str"] = implode(",", array_column($types, "name"));
                if ($v['qt']) {
                    if ($v["rltype_str"]) {
                        $v["rltype_str"] .= "," . $v['qt'];
                    } else {
                        $v["rltype_str"] = $v['qt'];
                    }
                }
            }
            unset($v);
            $this->assign('rldata', $rldata);

            $pjcount['pj1']=M("data_pj")->where(array("status"=>1,"state"=>1,"aid"=>$id,"pj"=>1))->count();
            $pjcount['pj2']=M("data_pj")->where(array("status"=>1,"state"=>1,"aid"=>$id,"pj"=>2))->count();
            $pjcount['pj3']=M("data_pj")->where(array("status"=>1,"state"=>1,"aid"=>$id,"pj"=>3))->count();
            $pjcount['pj4']=M("data_pj")->where(array("status"=>1,"state"=>1,"aid"=>$id,"pj"=>4))->count();

            $pjdata = M("data_pj")->where(array("status"=>1,"state"=>1,"aid"=>$id))->select();
            $this->assign('pjcount', $pjcount);
            $this->assign('pjdata', $pjdata);

//        if($id==14){
//            $rltypelist=M("data_rltype")->where(array("status"=>1))->select();
//            $this->assign('rltypelist',$rltypelist);
//            $this->display("gycBuild");
//        }else if($id==15){
//            $this->display("gycStudy");
//        }else{
            $this->display("gycDetail");
//        }
        }
    }


    public function gycClaim($id)
    {
        $data = M("project_claim")->find($id);
        $map2["status"] = 1;
        $map2["aid"] = $id;
        $rldata = M("data_gycrl")->where($map2)->select();
        $this->assign('rldata', $rldata);
        $this->assign("data", $data);
        $rltypelist = M("data_rltype")->where(array("status" => 1,"aid"=>$id))->select();
        $this->assign('rltypelist', $rltypelist);
//        if (in_array($id, array(15, 14, 28))) {
//            $this->display("gycClaim");
//        } elseif (in_array($id, array(13, 17, 18,20))) {
//            $this->display("gycClaim2");
//        }else{
            $this->display("gycClaim");
//        }
    }

    //项目直通车认领
    public function gycyy($id = 0)
    {
        if (IS_POST) {
            $data = $_POST;
            header('Content-Type:text/html; charset=utf-8');
            $data['create_time'] = time();
            $map['mobile'] = $data['mobile'];
            $map['create_time'] = array("gt", time() - 5);
            $search = M("data_gycrl")->where($map)->find();
            if ($search['id']) {
                $this->error('此手机短时间内不能再认领了，请耐心等待！', 'gyc');
                exit;
            }

            if ($data['name'] && $data['mobile']) {
                $adddata = M("data_gycrl")->add($data);
                if ($adddata) {
                    $mapx['id'] = $data['aid'];
                    $datainfo = M("project_claim")->where($mapx)->find();

               //    smsljz($data['mobile'], $msg = '您好:' . $data['name'] . ',项目：' . $datainfo['name'] . ' 认领成功！ 联系电话：68871363');
                    $this->success('项目认领成功！', '/home/ljz/gyc');
                } else {
                    $this->error('数据异常,请稍后再试！', 'gyc');
                }
            } else {
                $this->error('参数有误,请填写完整！', 'gyc');
            }
            exit;
        } else {
            $map['id'] = $id;
            $datainfo = M("project_claim")->where($map)->find();
//			dump($datainfo);echo M()->getlastsql();
            $this->assign('data', $datainfo);
            $this->assign('toptitle', '项目认领');
            $this->display();
        }


    }


    //活动预告
    public function hdyg()
    {
        redirect("hdlist");
        $this->assign('toptitle', '活动预告');
        $this->display();
    }

    //活动预告
    public function hdyglist($type = 1)
    {
        if ($type == 1) $toptitle = '文体活动';
        if ($type == 2) $toptitle = '群团活动';
        if ($type == 3) $toptitle = '公益活动';
        if ($type == 4) $toptitle = '书记带教';
        if ($type == 5) $toptitle = '红色电影';
        if ($type == 6) $toptitle = '场地预约';
        if ($type == 6) {
            $toptitle = '场馆预约';
            $submittitle = '我要预约';
        }

        $this->assign('submittitle', $submittitle);
        $this->assign('toptitle', $toptitle);
        $this->assign('type', $type);
        if ($type == 2) {
            $this->display("qthd");
        } elseif ($type == 4) {
            $this->display("sjdj");
        } else {
            $this->display();
        }

    }

    public function hdlist($type=1)
    {
        $this->assign('toptitle', "活动预约");
        $this->assign('type', $type);
         $this->display();
    }

    /*
     * 活动展示*/
    public function hdlists($type=1)
    {
        $this->assign('toptitle', "活动展示");
        $this->assign('type', $type);
        $this->display();
    }
    /*
    * 党群之家 活动展示*/
    public function hdlists2($type=1)
    {
        $this->assign('toptitle', "活动展示");
        $this->assign('type', $type);
        $this->display();
    }

    /*
     * 合心圆*/
    public function hxylists($type=10)
    {
        $this->assign('toptitle', "合心圆");
        $this->assign('type', $type);
        $this->display('hxylists');
    }

    /**
     * 活动列表
     */
    public function listhd($page = 1, $rows = 10, $type = 0)
    {
        if ($type != 6) {
           // $map["date"] = array("gt", time());
        }


        $map["status"] = 1;
        if ($type) {
            $map["type"] = $type;
        }
        $dq = I('get.dq');
        if($dq){
            $map["dq"] = $dq;
        }
        if($type == 6){
            $data = M("data_hdyg")->page($page, $rows)->order('sort desc')->where($map)->select();
        }else{
            $data = M("data_hdyg")->page($page, $rows)->order('date desc')->where($map)->select();
        }
//		echo M()->getlastsql();
        $result['result'] = -1;
        if ($data !== false) {
            foreach ($data as &$v) {
                $v['submittitle'] = '详情';
                $v['date'] = date("Y-m-d ", $v['date']);
                if ($v['type'] == 1) $v['type'] = '文体活动';
                if ($v['type'] == 2) $v['type'] = '群团活动';
                if ($v['type'] == 3) $v['type'] = '公益活动';
                if ($v['type'] == 4) $v['type'] = '书记带教';
                if ($v['type'] == 5) $v['type'] = '红色电影';
                if ($v['type'] == 6) {
                    $v['type'] = '场地预约';
                    $v['submittitle'] = '我要预约';
                }
                $v["picpath"] = get_cover($v["pic1"], "path");

            }
            unset($v);

            $result['result'] = 1;
            $result['data'] = $data;
        }
        echo json_encode($result);
        exit;
    }

    //活动预告
    public function hdxq($id = 0)
    {
        $map['id'] = $id;
        $data = M("data_hdyg")->where($map)->find();
        $submittitle = '我要报名';
        if ($data['type'] == 1) $data['type'] = '文体活动';
        if ($data['type'] == 2) $data['type'] = '群团活动';
        if ($data['type'] == 3) $data['type'] = '公益活动';
        if ($data['type'] == 4) $data['type'] = '书记带教';
        if ($data['type'] == 5) $data['type'] = '红色电影';
        if ($data['type'] == 6) {
            $data['type'] = '场地预约';
            $submittitle = '我要预约';
        }

        $this->assign('data', $data);
        $this->assign('toptitle', $data['type'] . '详情');
        $this->assign('submittitle', $submittitle);
        $v["canbm"] = $data['date']>time()?1:0;

        $this->display();
    }
      /*
       * 报名页面
       * type
       * 6  活动场地预约
       * 3 活动报名*/
    public function hdbm($id = 0, $type = 0)
    {
        if (IS_POST) {
            $data = $_POST;
            header('Content-Type:text/html; charset=utf-8');
            $data['create_time'] = time();
            $data['openid'] = cookie('wxOpenId');
            $map['mobile'] = $data['mobile'];
            //活动报名传递参数
            $types = $_REQUEST['types'];

            if (!preg_match("/^1[34578]{1}\d{9}$/", $data['mobile'])) {
                $this->error('请输入正确的手机号！');
                exit;
            }


            $map['create_time'] = array("gt", time() - 5);
            $search = M("data_hdbm")->where($map)->find();
            if ($search['id']) {
                $this->error('此手机短时间内不能再预约了，请耐心等待！', 'zzgx');
                exit;
            }

            $map3["openid"] = cookie('wxOpenId');
            $map3["status"] = 1;
            $map3["aid"] = $data["aid"];
            $map3["state"] = array("in", array(0, 1));
            $map3["starttime"] = array("gt", time());
            $yy = M("data_hdbm")->where($map3)->select();
            unset($map3);
            if ($yy) {
                $this->error('您已有一个相同场地的预约，不能重复预约！');
                exit;
            }


            $data["starttime"] = strtotime($data["start"]);
            $data["endtime"] = strtotime($data["end"]);

            //活动报名必须传递
            if($types==9527){
                $mapx['id'] = $data['aid'];
                $datainfo = M("data_hdyg")->where($mapx)->find();
                $adddata = M("data_hdbm")->add($data);
                if ($adddata) {
//                 //       smsljz($data['mobile'], $msg = '您好:' . $data['name'] . ',活动：' . $datainfo['name'] . ' 预约成功！ '); //联系电话：68871363
                    if($datainfo['type'] == 10){
                        $this->success('活动预约成功！', '/home/ljz/hxylists');   // 联系电话：68871363
                    }
                    if($datainfo['dq'] == 2){
                        $this->success('活动预约成功！', '/home/ljz/hdlists2');   // 联系电话：68871363
                    }
                        $this->success('活动预约成功！', '/home/ljz/hdyg');   // 联系电话：68871363
                } else {
                    $this->error('数据异常,请稍后再试！');
                }
            }
            if ($data['name'] && $data['mobile'] && $data['aid'] && $data["starttime"] && $data["endtime"]
                && $data["count"] && $data["company"] && $data["content"]
            ) {
                $mapx['id'] = $data['aid'];
                $datainfo = M("data_hdyg")->where($mapx)->find();
                if ($datainfo['type'] != 6) {
                    $map1["aid"] = $data['aid'];
                    $map1["status"] = 1;
                    $applied = M("data_hdbm")->where($map1)->count();
                    if ($applied >= $datainfo['count']) {
                        $this->error('预约已满，请选择其他活动！', 'hdyg');
                        exit;
                    }
                } else {
                    //场地预约    判断时间段是否合规，

                    //$time1 为3个工作日
                    $time1 = strtotime(date("Y-m-d"));
                    $i = 0;
                    while ($i < 3) {
                        $w = date("w", $time1);
                        if ($w != 6 && $w != 0) {
                            $i++;
                        }
                        $time1 += 3600 * 24;
                    }

                    $time2 = $time1 + 45 * 3600 * 24;
                    if ($data["starttime"] < $time1 || $data["starttime"] > strtotime(date("Y-m-d", time() + 52 * 3600 * 24))) {
                        $this->error('只能预约' . date("Y年m月d日", $time1) . '到' . date("Y年m月d日", $time2) . '之间的日期！', 'hdyg');
                        exit;
                    }


                    if ($data["starttime"] >= $data["endtime"]) {
                        $this->error('活动开始时间不能晚于结束时间！', 'hdyg');
                        exit;
                    }

                    if (date("Y-m-d", $data["starttime"]) != date("Y-m-d", $data["endtime"])) {
                        $this->error('活动开始和结束只能在同一天!');
                        exit;
                    }

                    $map2["status"] = 1;
                    $map2["aid"] = $data['aid'];
                    $map2["state"] = 1;
                    $map2["_string"] = "(starttime <= " . $data["starttime"] . "  and  endtime >= " . $data["starttime"] . ")  or (starttime <=  " . $data['endtime'] . " and endtime >= " . $data['endtime'] . ")  or  (" . $data["starttime"] . " < starttime  and " . $data["endtime"] . "> endtime  )";
                    $applied = M("data_hdbm")->where($map2)->count();
                    if ($applied > 0) {
                        $this->error('此时段已被预约，请选择其他时间，谢谢！', 'hdyg');
                        exit;
                    }
                }
                $adddata = M("data_hdbm")->add($data);
                if ($adddata) {
                    if ($datainfo['type'] == 6) {
                       // smsljz($data['mobile'], $msg = '您好:' . $data['name'] . ',活动场地：' . $datainfo['name'] . ' 后台已收到您的预约，审核结果会通过短信告知，请注意查收。谢谢！');
                        $this->success('后台已收到您的预约。谢谢！', '/home/ljz/hdyg');
                    } else {
                      //  smsljz($data['mobile'], $msg = '您好:' . $data['name'] . ',活动：' . $datainfo['name'] . ' 预约成功！ '); //联系电话：68871363
                        $this->success('活动预约成功！', '/home/ljz/hdyg');   // 联系电话：68871363
                    }

                } else {
                    $this->error('数据异常,请稍后再试！');
                }
            } else {
                $this->error('参数有误,请填写完整1！');
            }

            exit;
        } else {
            $wxmember = cookie('wxmember');
            $this->assign('wxmember', $wxmember);
            $map['id'] = $id;
            $datainfo = M("data_hdyg")->where($map)->find();
            $this->assign('data', $datainfo);
            $this->assign('toptitle', '活动报名');
            if (!$type) {
                $type = $datainfo['type'];
            }

            if ($type == 4) {
                $this->assign('toptitle', '书记带教预约');

                $this->display("sjdjyy");
            } elseif ($type == 6) {
                $sb = M("data_hdsb")->where(array("status" => 1, "aid" => $id))->select();
                $this->assign("hdsb", $sb);//活动设备

                $this->assign('toptitle', '活动场地预约');
                $this->display("hdcdyy");
            } else {
                $this->display();
            }
        }

    }

    public function listCdyy($id)
    {
        $map['aid'] = $id;
        $map['status'] = 1;
        $map['state'] = 1;
        $map["starttime"] = array("gt", time());
        $list = M("data_hdbm")->where($map)->order("starttime asc")->select();
        if ($list) {
            foreach ($list as &$v) {
                $v["yytime"] = date("Y-m-d H:i:s", $v["starttime"]) . " 至 " . date("Y-m-d H:i:s", $v["endtime"]);
            }
            unset($v);
        }
        $this->assign('toptitle', '活动场地已预约');
        $this->assign("cdinfo", M("data_hdyg")->find($id));
        $this->assign("data", $list);
        $this->display();
    }


    //身边党课
    public function sbdk()
    {
        $this->assign('toptitle', '身边党课');
        $this->display();
    }


    public function sbdk1($jsID=1)
    {
        if($jsID){
            $this->assign('jsID',$jsID)  ;
        }
        $this->assign('toptitle', '党课报名');
        $this->display();
    }

    public function sbdk2()
    {
        $this->assign('toptitle', '书记带教');
        $map['ljz_data_js.mtype'] = 2;
        $map['ljz_data_js.status'] = 1;
        $list = M('data_js')->field('ljz_data_js.*,ljz_picture.path as path ')->where($map)->join("left join ljz_picture on ljz_data_js.avatar = ljz_picture.id")->select();

        $this->assign('list', $list);
//		dump($list);exit;
        $this->display();
    }

    public function shuji($id = 0)
    {
        if (IS_POST) {
            $data = $_POST;
            header('Content-Type:text/html; charset=utf-8');
            $data['create_time'] = time();
            $data["date"] =strtotime($data["date"]);
            $data['openid'] = cookie('wxOpenId');
            if ($data['name']) {
                $adddata = M("data_sjyy")->add($data);
                if ($adddata) {
                    $this->success('书记预约成功');
                } else {
//					echo M()->getlastsql();
                    $this->error('数据异常,请稍后再试！');
                }
            } else {
                $this->error('参数有误,请填写完整！');
            }
            exit;

        } else {
            $this->assign('toptitle', '书记带教');
            $map['ljz_data_js.id'] = $id;
            $data = M('data_js')->where($map)->join("left join ljz_picture on ljz_data_js.pic = ljz_picture.id")->find();

            $dzz = M('dzz')->where(array('status' => 1))->order('type,id')->select();
            //$dzzopt = array_comb(array_column($dzz,"id"),)

            $this->assign('dzzopt', $dzz);

            $this->assign('data', $data);
            $times = array(date("Y-m-d"), date("Y-m-d", time() + 24 * 3600), date("Y-m-d", time() + 24 * 3600 * 2), date("Y-m-d", time() + 24 * 3600 * 3), date("Y-m-d", time() + 24 * 3600 * 4), date("Y-m-d", time() + 24 * 3600 * 5), date("Y-m-d", time() + 24 * 3600 * 6));
            $this->assign("times", $times);

//		dump($data);exit;
            $this->display();
        }
    }

    public function sbdk3()
    {
        header('Content-Type:text/html; charset=utf-8');
        $this->assign('toptitle', '讲师风采');
        $map['ljz_data_js.type'] = 1;
        $map['ljz_data_js.mtype'] = 1;
        $map['ljz_data_js.status'] = 1;
        $list = M('data_js')->field('ljz_data_js.*,ljz_picture.path as path ')->where($map)->join("left join ljz_picture on ljz_data_js.avatar = ljz_picture.id")->select();
        $this->assign('list', $list);

        $map2['status'] = 1;
        $map2['type'] = 2;
        $map2['mtype'] = 1;
        $map2['company'] = array("neq", "");
        $company = M('data_js')->field("company")->where($map2)->group('company')->select();
        foreach ($company as $k => $v) {
            $map3['ljz_data_js.status'] = 1;
            $map3['ljz_data_js.type'] = 2;
            $map3['ljz_data_js.mtype'] = 1;
            $map3['ljz_data_js.company'] = $v['company'];
            $company[$k]['list'] = M('data_js')->field('ljz_data_js.*,ljz_picture.path as path ')->where($map3)->join("left join ljz_picture on ljz_data_js.avatar = ljz_picture.id")->select();
//			echo M()->getlastsql();
        }
        $this->assign('company', $company);
//		dump($company);exit;

        $this->display();
    }

    public function sbdk4(){
        header('Content-Type:text/html; charset=utf-8');
        $this->assign('title', '讲师风采');
        $maps['status'] =array('egt',1);
        $maps['mtype'] =1;
        $company = M('data_js')->where($maps)->field('id,desc,name,pic')->select();
        foreach($company as $k=>$v){
            $company[$k]['time'] =  date('Y-m-d',$v['time']);
            $company[$k]['pic'] = thumb($v['pic'],200,200) ;
            $company[$k]['desc'] =strip_tags (  $v['desc'] .'...' );
        }
        $this->assign('data', $company);
//        dump($company);exit;
        $this->display('sbdk4');
    }

    public function jsfc($id = 0)
    {
        $this->assign('toptitle', '讲师风采');
        $data = M('data_js')->field('ljz_data_js.*,ljz_picture.path as path ')->where('ljz_data_js.id=' . $id)->join("left join ljz_picture on ljz_data_js.avatar = ljz_picture.id")->find();
        $this->assign('data', $data);
        $this->assign('jsID', $id);
        $this->display();
    }

    //上墙书记
    public function sqsj($id = 0)
    {
        $this->assign('toptitle', '带教书记');
        $this->display();
    }

    //上墙书记
    public function sqsj2($id = 0)
    {
        $this->assign('toptitle', '书记风采');
        $this->display();
    }

    public function dkyy($id = 0)
    {

        if (IS_POST) {
            $data = $_POST;
            header('Content-Type:text/html; charset=utf-8');
            $data['create_time'] = time();
            $data['openid'] = cookie('wxOpenId');
            $map['mobile'] = $data['mobile'];
            $map['create_time'] = array("gt", time() - 5);
            $search = M("data_dkbm")->where($map)->find();
            if ($search['id']) {
                $this->error('此手机段时间内不能再预约了，请耐心等待！', 'zzgx');
                exit;
            }

            if ($data['name'] && $data['mobile'] && $data['aid'] && $data['num']) {
                $adddata = M("data_dkbm")->add($data);
                if ($adddata) {
                    $mapx['id'] = $data['aid'];
                    $datainfo = M("apply_djkc")->where($mapx)->find();

                    $count = M('data_dkbm')->where('aid=' . $datainfo['id'])->sum("num");
                    $datainfo['count'] = $datainfo['count'] - $count;

                    if ($datainfo['count'] - $data['num'] <= 0) {
                        $this->error('课程已经全部被预约了，请耐心等待下次课程！');
                        exit;
                    }


                  //  smsljz($data['mobile'], $msg = '您好:' . $data['name'] . ',课程：' . $datainfo['course_name'] . ' 预约成功！');
                    $this->success('党课预约成功！', '/home/ljz/sbdk1');
                } else {
//					echo M()->getlastsql();
                    $this->error('数据异常,请稍后再试！');
                }
            } else {
                $this->error('参数有误,请填写完整！');
            }
            exit;
        } else {
            $mapp['status'] =1;
            $dzz = M("dzz")->where($mapp)->select();
            $this->assign('dzz', $dzz);
            $map['id'] = $id;
            $datainfo = M("apply_djkc")->where($map)->find();
            $count = M('data_dkbm')->where('aid=' . $datainfo['id'])->sum("num");
            $datainfo['count'] = ($datainfo['count'] - $count) . '/' . $datainfo['count'];
            $this->assign('data', $datainfo);
            $this->assign('toptitle', '党课预约');
            $wxmember = cookie('wxmember');
            $this->assign('wxmember', $wxmember);

        }


        $this->display();
    }

    /**
     * 党课列表
     */
    public function listdk($page = 1, $rows = 10,$jsID=null)
    {
//        $map['datetime'] = array("gt", time() - 86400);
        if($jsID)  {
            $map['jsID'] = $jsID;
        }
        $map['status'] = 1;
        $data = M("apply_djkc")->where($map)->page($page, $rows)->order('datetime desc,id desc')->select();
        foreach ($data as $k => $v) {
            $count = M('data_dkbm')->where('aid=' . $v['id'])->sum("num");
            $data[$k]['count'] = ($v['count'] - $count) . '/' . $v['count'];
            $data[$k]["canyy"] = $v["datetime"]>time()?1:0;
        }
        if(!$data || count($data) == 0){
            echo "nomore";exit;
        }

        $this->assign("data", $data);
        $this->display();
    }


    //自治金
    public function zzj()
    {

        $this->assign('toptitle', '自治金');
        $this->display();
    }

    //关于自治金
    public function zzj2()
    {
        $this->assign('toptitle', '关于自治金');
        $this->display();
    }

    //自治金--自治项目
    public function zzxm()
    {
        $map['type'] = '自治项目';
        $data = M("zzjxm")->where($map)->limit(0, 20)->select();
        foreach ($data as $k => $v) {

        }

        $this->assign("data", $data);
        $this->assign('toptitle', '自治项目');
        $this->display();
    }


    public function listJwh($page = 1, $rows = 99)
    {

        header("Access-Control-Allow-Origin:*");
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
        $data = M("zzjxm")->field('juwei as name')->group('juwei')->select();
        $out['result'] = $data;
        echo json_encode($out);
    }

    /**
     * 自治项目列表
     */
    public function listProject($page = 1, $rows = 20, $jwhName = "", $projectId = "")
    {
        header("Access-Control-Allow-Origin:*");
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
        $jwhName = urldecode($jwhName);
        if ($projectId) {
            $map['id'] = $projectId;
        } else {
            if ($jwhName) $map['juwei'] = $jwhName;
        }
        $data = M("zzjxm")->field('id as proId,title as name,juwei,content,zan,pic1,pic2')->where($map)->page($page, $rows)->select();
//		echo M()->getlastsql();
        if (count($data) == 0) $data = "";
        $out['result'] = $data;
        echo json_encode($out);
    }

    //自治金--单位共建
    public function dwgj()
    {
        $map['type'] = '单位共建';
        $data = M("zzjxm")->where($map)->limit(0, 20)->select();
        foreach ($data as $k => $v) {

        }

        $this->assign("data", $data);
        $this->assign('toptitle', '单位共建');
        $this->display();
    }

    public function xmbm($id = "")
    {
        $map['id'] = $id;
        $datainfo = M("zzjxm")->where($map)->find();

        $this->assign("data", $datainfo);
        $this->assign('toptitle', '项目报名');
        $this->display();
    }

    public function xmbm2($id = "", $type = 0)
    {
        if (IS_POST) {
            $data = $_POST;
            header('Content-Type:text/html; charset=utf-8');
            $data['create_time'] = time();
            $data['openid'] = cookie('wxOpenId');
            $map['mobile'] = $data['mobile'];
            $map['create_time'] = array("gt", time() - 5);
            $search = M("data_xmbm")->where($map)->find();
//			echo M()->getlastsql();exit;
            if ($search['id']) {
                $this->error('此手机短时间内不能再预约了，请耐心等待！', 'zzgx');
                exit;
            }


            if ($data['name'] && $data['mobile'] && $data['aid']) {
                $adddata = M("data_xmbm")->add($data);
                $data2['name'] = $data['name'];
                $data2['mobile'] = $data['mobile'];
                $mapwx['openid'] = cookie('wxOpenId');
                $saveinfo = M("wxmember")->where($mapwx)->save($data2);
                if ($adddata) {
                    $mapx['id'] = $data['aid'];
                    $datainfo = M("zzjxm")->where($mapx)->find();

                   // smsljz($data['mobile'], $msg = '您好:' . $data['name'] . ',项目：' . $datainfo['title'] . ' 报名成功！ 联系电话：68871363');
                    $this->success('项目报名成功！');
                } else {
//					echo M()->getlastsql();
                    $this->error('数据异常,请稍后再试！', 'hdyg');
                }
            } else {
                $this->error('参数有误,请填写完整！', 'hdyg');
            }
            exit;
        } else {
            $wxmember = cookie('wxmember');
            $this->assign('wxmember', $wxmember);

//			dumP($wxmember);exit;
            $map['id'] = $id;
            $datainfo = M("zzjxm")->where($map)->find();
//			dump($datainfo);echo M()->getlastsql();
            $this->assign('data', $datainfo);


            $this->assign('toptitle', '项目报名');

        }
        if ($type) {
            $this->display('xmbm3');
        } else {
            $this->display();
        }

    }

    public function zan($id = "")
    {
        $map['id'] = $id;
        M("zzjxm")->where($map)->setInc('zan', 1);
//		 echo M()->getlastsql();exit;
        $this->success('谢谢您的点赞');

    }


    public function listdzz($type = false)
    {
        if ($type) {
            $map['type'] = $type;
            $data = M('dzz')->where($map)->select();
        } else {
            $data = M("dzz")->field('type')->group('type')->select();
        }
        echo json_encode($data);

    }

    public function listdzz2()
    {
        $res['status'] = 1;
        $map['status'] = 1;
        $res['result'] = M("dzz")->where($map)->select();
        echo json_encode($res);
    }

    public function my()
    {
        $map['status'] = 1;
        $map["signtime"] = 0;
        $map['openid'] = cookie('wxOpenId');

        $wxmember = cookie('wxmember');
        $this->assign('wxmember', $wxmember);
        $this->assign('toptitle', '我的信息');
        $this->display();

    }


    public function myRegister()
    {

        $map['openid'] = cookie('wxOpenId');
        $map['status'] = 1;
        $map["signtime"] = 0;
        $list = M("data_hdbm")->where($map)->select();
        foreach ($list as $k => $v) {
            $info = M("data_hdyg")->where('id=' . $v['aid'])->find();
            $list[$k]['name'] = $info['name'];
            $list[$k]['date'] = $info['date'];
            $list[$k]['addr'] = $info['addr'];
            $list[$k]['count'] = $info['count'];
            $list[$k]['type'] = $info['type'];
            $list[$k]['content'] = $info['content'];
            if ($list[$k]['type'] == '1') $list[$k]['typename'] = '文体活动';
            if ($list[$k]['type'] == '2') $list[$k]['typename'] = '群团活动';
            if ($list[$k]['type'] == '3') $list[$k]['typename'] = '公益活动';
            if ($list[$k]['type'] == '4') $list[$k]['typename'] = '红色电影';
            if ($list[$k]['type'] == '5') $list[$k]['typename'] = '书记代教';
            if ($list[$k]['type'] == '6') $list[$k]['typename'] = '场地预约';
            if ($info['date'] <= (time() - 86400)) {
                $list[$k]['guoqi'] = 1;
            }

        }

        $this->assign('list', $list);
        $list = M("data_dkbm")->where($map)->select();
//		echo M()->getlastsql();
        foreach ($list as $k => $v) {
            $info = M("apply_djkc")->where('id=' . $v['aid'])->find();
            $list[$k]['course_name'] = $info['course_name'];
            $list[$k]['datetime'] = $info['datetime'];
            $list[$k]['teacher'] = $info['teacher'];
            $list[$k]['addr'] = $info['addr'];
            $list[$k]['count'] = $info['count'];
            if ($info['datetime'] <= (time() - 86400)) {
                $list[$k]['guoqi'] = 1;
            }
        }
//		dump($list);
        $this->assign('list2', $list);
        $this->assign('toptitle', '我的签到');
        $this->display();

    }

    public function myApply($type = null)
    {
        if($type){
            $this->assign('toptitle', '我的场地预约');
        }else{
            $this->assign('toptitle', '我的活动报名');
        }
        $map['openid'] = cookie('wxOpenId');
        if( !$map['openid']){ //无信息跳转从新获取
            $urls = 'http://hqdj.cmlzjz.com/home/ljz/wxindex/type/3';
            echo "<script language=\"javascript\">";
            echo "document.location=\"" . $urls . "\"";
            echo "</script>";
            exit;
        }
        $list = M("data_hdbm")->where($map)->order('id desc')->select();
        $list2 = M("data_gycrl")->where($map)->order('id desc')->select();
        foreach ($list as $k => $v) {
            $info = M("data_hdyg")->where('id=' . $v['aid'])->find();
            $list[$k]['name'] = $info['name'];
            $list[$k]['date'] = $info['date'];
            $list[$k]['addr'] = $info['addr'];
            $list[$k]['count'] = $info['count'];
            $list[$k]['type'] = $info['type'];
            $list[$k]['content'] = $info['content'];
            if ($list[$k]['type'] == '1') $list[$k]['typename'] = '文体活动';
            if ($list[$k]['type'] == '2') $list[$k]['typename'] = '群团活动';
            if ($list[$k]['type'] == '3') $list[$k]['typename'] = '公益活动';
            if ($list[$k]['type'] == '4') $list[$k]['typename'] = '红色电影';
            if ($list[$k]['type'] == '5') $list[$k]['typename'] = '书记代教';
            if ($list[$k]['type'] == '6') $list[$k]['typename'] = '场地预约';
//			1文化体育活动2群团活动3公益活动4红色电影5书记代教6场地预约7 (56月活动，竖屏)
            if($type){
                if($list[$k]['type'] == '6') {
                }else{
                    unset($list[$k]);
                }
            }else{
                if($list[$k]['type'] == '6') {
                    unset($list[$k]);
                }
            }
        }
//        dump($list);exit;
        $this->assign('list', $list);
        $this->display();

    }
    public function myApply2()
    {
            $this->assign('toptitle', '我的项目预约');
        $map['openid'] = cookie('wxOpenId');
        if( !$map['openid']){ //无信息跳转从新获取
            $urls = 'http://hqdj.cmlzjz.com/home/ljz/wxindex/type/3';
            echo "<script language=\"javascript\">";
            echo "document.location=\"" . $urls . "\"";
            echo "</script>";
            exit;
        }
        $list = M("data_gycrl")->where($map)->order('id desc')->select();
        foreach ($list as $k => $v) {
            $info = M("project_claim")->where('id=' . $v['aid'])->find();
            $list[$k]['name'] = $info['name'];
            $list[$k]['typename'] = '项目预约';
//			1文化体育活动2群团活动3公益活动4红色电影5书记代教6场地预约7 (56月活动，竖屏)
//            if($type){
//                if($list[$k]['type'] == '6') {
//                }else{
//                    unset($list[$k]);
//                }
//            }else{
//                if($list[$k]['type'] == '6') {
//                    unset($list[$k]);
//                }
//            }
        }
//        dump($list);exit;
        $this->assign('list', $list);
        $this->display('myapply2');

    }

    public function myApplydel($id){
        if(IS_POST){
            $ids = M('data_hdbm')->where(array('id'=>$id))->save(array('state'=>3));
            if($ids){
                echo json_encode(array('code'=>200,'data'=>array(),'msg'=>'取消成功'));exit;
            }else{
                echo json_encode(array('code'=>300,'data'=>array(),'msg'=>'取消失败'));exit;
            }
        }
    }

    public function myAppoint()
    {

        $this->assign('toptitle', '我的党课预约');
        $map['openid'] = cookie('wxOpenId');
        $list = M("data_dkbm")->where($map)->order('id desc')->select();
        foreach ($list as $k => $v) {
            $info = M("apply_djkc")->where('id=' . $v['aid'])->find();
            $list[$k]['course_name'] = $info['course_name'];
            $list[$k]['datetime'] = $info['datetime'];
            $list[$k]['teacher'] = $info['teacher'];
            $list[$k]['addr'] = $info['addr'];
            $list[$k]['count'] = $info['count'];
        }
        $this->assign('list', $list);
        $this->display();

    }

    public function myClaim()
    {
        $this->assign('toptitle', '我的自治金项目认领');
        $map['openid'] = cookie('wxOpenId');
        $list = M("data_xmbm")->where($map)->select();
        foreach ($list as $k => $v) {
            $info = M("zzjxm")->where('id=' . $v['aid'])->find();
            $list[$k]['title'] = $info['title'];
            $list[$k]['content'] = $info['content'];
        }

        $this->assign('list', $list);
        $this->display();
    }

    /*
     * 积分页*/
    public function myGrade()
    {
        $map['openid'] = cookie('wxOpenId');
        $pmid =M("wxmember")->where($map)->getField("id");
        if($pmid){
            $sum = M("data_pfrecord")->where(array("uid"=>$pmid))->sum("fen");
            $data = M("data_pfrecord")->where(array("uid"=>$pmid))->select();

            foreach ($data as &$item){
                $item["jftype_name"] = M("data_jfsd")->getFieldById($item["jftype"],"title");
            }
            $this->assign('data', $data);
            $this->assign('sum', $sum);
        }

        $this->assign('pmid', $pmid);//有没有报道
        $this->assign('toptitle', '我的积分');
        $this->display();

    }

    public function map($type = "", $id = "")
    {
        $map['id'] = $id;
        if ($type == "dk") {
            $bmInfo = M("data_dkbm")->where($map)->find();
            $info = M("apply_djkc")->where('id=' . $bmInfo['aid'])->find();
            $info['name'] = $info['course_name'];
        }
        if ($type == "hd") {
            $bmInfo = M("data_hdbm")->where($map)->find();
            $info = M("data_hdyg")->where('id=' . $bmInfo['aid'])->find();

        }


        $this->assign('type', $type);
        $this->assign('id', $id);

        $this->assign('bmInfo', $bmInfo);
        $this->assign('info', $info);
        $this->assign('toptitle', '地图');
        $this->display();

    }

    /*
     * 签到积分口*/
    public function sign($type = "", $id = "")
    {

        if (!$type || !$id) {
            $res["result"] = -1;
            $res["msg"] = "参数错误";
            echo json_encode($res);
            exit;
        }
        if ($type == "dk") {
            $Model = M("data_dkbm");
            $typeid = 1;
        } else {
            $Model = M("data_hdbm");
            $typeid = 2;
        }
        $record = $Model->find($id);
        if ($record['signtime']) {
            $res["result"] = -1;
            $res["msg"] = "已签到";
            echo json_encode($res);
            exit;
        }

        $data['id'] = $id;
        $data["signtime"] = time();
        $r = $Model->save($data);

        if ($r === false) {
            $res["result"] = -1;
            $res["msg"] = "签到失败" . M()->getlastsql();
            echo json_encode($res);
            exit;
        } else {
            $res["result"] = 1;
            action_log('signGetScore', 'signGetScore', $typeid, $id);
            $res["msg"] = "签到成功";

            $da['uid'] = $record['id'];
            $da['uname'] = $record['name'];
            $da['jftype'] = 1;
            $da['create_time'] = time();
            $da['fen'] = 1;
            M('data_pfrecord') ->add($da);
            echo json_encode($res);
            exit;
        }

    }

    public function test1()
    {
        $this->display();
    }

    public function tes()
    {
        echo "===" . $_SERVER["QUERY_STRING"];
        dump($_REQUEST);
        exit;
    }

//    public function gycStudy(){
//        $map["id"] = 15;
//        $data = M("project_claim")->where($map)->find();
//        if ($data["pic1"]) {
//            $pics = explode(",", $data["pic1"]);
//            $this->assign("pics", $pics);
//        }
//        $this->assign("data", $data);
//
//        $map2["status"] = 1;
//        $map2["aid"] = 15;
//        $rldata = M("data_gycrl")->where($map2)->select();
//        $this->assign('rldata', $rldata);
//        dump($rldata);exit;
//
//        $this->assign('toptitle', '爱心助学');
//        $this->display("gycStudy");
//    }
//
//    public function gycBuild(){
//        $map["id"] = 14;
//        $data = M("project_claim")->where($map)->find();
//        if ($data["pic1"]) {
//            $pics = explode(",", $data["pic1"]);
//            $this->assign("pics", $pics);
//        }
//        $this->assign("data", $data);
//
//        $map2["status"] = 1;
//        $map2["aid"] = 14;
//        $rldata = M("data_gycrl")->where($map2)->select();
//        $this->assign('rldata', $rldata);
//        dump($rldata);exit;
//        $this->assign('toptitle', '公益进工地');
//        $this->display("gycBuild");
//    }

    public function gycStudyPerson($id)
    {
        $this->assign('toptitle', '个人联系方式');
        $this->assign('data', M("project_claim")->find($id));
        $this->display("gycStudyPerson");
    }

    public function gycStudyCompany($id)
    {
        $this->assign('toptitle', '联系方式');
        $this->assign('data', M("project_claim")->find($id));
        $this->display("gycStudyCompany");
    }

    public function gycBuildPerson($id, $rltype)
    {
        $this->assign('toptitle', '个人联系方式');
        $this->assign('data', M("project_claim")->find($id));
        $this->assign('rltype', M("data_rltype")->find($rltype));
        $this->display("gycBuildPerson");
    }

    public function gycBuildCompany($id, $rltype)
    {
        $this->assign('toptitle', '联系方式');
        $this->assign('data', M("project_claim")->find($id));
        $this->assign('rltype', M("data_rltype")->find($rltype));
        $this->display("gycBuildCompany");
    }


    public function gycrl($aid)
    {
        if (IS_POST) {
            $data = $_POST;
            $data["create_time"] = time();
            $data['openid'] = cookie('wxOpenId');
            $project = M("project_claim")->find($data["aid"]);

            if (14) {
                //公益进工地
                if (!is_phone_number($data["mobile"])) {
                    $this->error('请输入正确的手机号！');
                }
                if ($data["rllx"] == 1) {//个人
                    $data["name"] = $data["name2"];
                    if ($data["name"] && $data["mobile"]&& $data["aid"]) {
                        $res = M("data_gycrl")->add($data);
                        if ($res) {
                            $this->success('认领成功', "gyc");
                        } else {
                            $this->error('数据异常,请稍后再试！');
                        }
                    } else {
                        $this->error('参数有误,请填写完整！');
                    }
                } elseif ($data["rllx"] == 2) {//单位
                    $data["name"] = $data["name1"];
                    if ($data["dw"] && $data["name"] && $data["mobile"] && $data["aid"]) {
                        $res = M("data_gycrl")->add($data);
                        if ($res) {
                            $this->success('认领成功', "gyc");
                        } else {
                            $this->error('数据异常,请稍后再试！');
                        }
                    } else {
                        $this->error('参数有误,请填写完整！');
                    }
                } else {
                    $this->error('参数有误,请填写完整！');
                }
            }
//            elseif ($project["id"] == 15) {
//                //爱心助学
//                if (!is_phone_number($data["mobile"])) {
//                    $this->error('请输入正确的手机号！');
//                }
//                if ($data["rllx"] == 1) {//个人
//                    $data["sum"] = intval($data["sum"]);
//                    $data["name"] = $data["name2"];
//                    if ($data["name"] && $data["mobile"] && $data["sum"] && $data["aid"]) {
//                        $res = M("data_gycrl")->add($data);
//                        if ($res) {
//                            $this->success('认领成功', "gyc");
//                        } else {
//                            $this->error('数据异常,请稍后再试！');
//                        }
//                    } else {
//                        $this->error('参数有误,请填写完整！');
//                    }
//                } elseif ($data["rllx"] == 2) {//单位
//                    $data["sum"] = intval($data["sum"]);
//                    $data["name"] = $data["name1"];
//                    if ($data["dw"] && $data["name"] && $data["mobile"] && $data["sum"] && $data["aid"]) {
//                        $res = M("data_gycrl")->add($data);
//                        if ($res) {
//                            $this->success('认领成功', "gyc");
//                        } else {
//                            $this->error('数据异常,请稍后再试！');
//                        }
//                    } else {
//                        $this->error('参数有误,请填写完整！');
//                    }
//                } else {
//                    $this->error('参数有误,请填写完整！');
//                }
//
//            } elseif ($project["id"] == 18) {
//                if (!is_phone_number($data["mobile"])) {
//                    $this->error('请输入正确的手机号！');
//                }
//                //青丝行动
//                if ($data["name"] && $data["sex"] && $data["mobile"] && $data["aid"]) {
//                    $res = M("data_gycrl")->add($data);
//                    if ($res) {
//                        $this->success('认领成功', "gyc");
//                    } else {
//                        $this->error('数据异常,请稍后再试！');
//                    }
//                } else {
//                    $this->error('参数有误,请填写完整！');
//                }
//            }elseif ($project["id"] == 13) {
//                if (!is_phone_number($data["mobile"])) {
//                    $this->error('请输入正确的手机号！');
//                }
//                //户外公益赛
//                if ($data["name"] && $data["sex"] && $data["mobile"] && $data["aid"] && $data["idnum"]) {
//                    $res = M("data_gycrl")->add($data);
//                    if ($res) {
//                        $this->success('认领成功', "gyc");
//                    } else {
//                        $this->error('数据异常,请稍后再试！');
//                    }
//                } else {
//                    $this->error('参数有误,请填写完整！');
//                }
//            }elseif ($project["id"] == 17) {
//                if (!is_phone_number($data["mobile"])) {
//                    $this->error('请输入正确的手机号！');
//                }
//                //青丝行动
//                if ($data["name"] && $data["dw"] && $data["mobile"] && $data["aid"]) {
//                    $res = M("data_gycrl")->add($data);
//                    if ($res) {
//                        $this->success('认领成功', "gyc");
//                    } else {
//                        $this->error('数据异常,请稍后再试！');
//                    }
//                } else {
//                    $this->error('参数有误,请填写完整！');
//                }
//            } elseif ($project["id"] == 20) {
//                if (!is_phone_number($data["mobile"])) {
//                    $this->error('请输入正确的手机号！');
//                }
//                //独居老人服务
//                if ($data["name"] &&( $data["rltype"] || $data['qt']) && $data["mobile"] && $data["aid"]) {
//                    $res = M("data_gycrl")->add($data);
//                    if ($res) {
//                        $this->success('认领成功', "gyc");
//                    } else {
//                        $this->error('数据异常,请稍后再试！');
//                    }
//                } else {
//                    $this->error('参数有误,请填写完整！');
//                }
//            }  elseif ($project["id"] == 28) {
//                //余香ROSE
//                if (!is_phone_number($data["mobile"])) {
//                    $this->error('请输入正确的手机号！');
//                }
//                if ($data["rllx"] == 1) {//个人
//                    $data["name"] = $data["name2"];
//                    if ($data["name"] && $data["mobile"]&& $data["aid"]) {
//                        $res = M("data_gycrl")->add($data);
//                        if ($res) {
//                            $this->success('认领成功', "gyc");
//                        } else {
//                            $this->error('数据异常,请稍后再试！');
//                        }
//                    } else {
//                        $this->error('参数有误,请填写完整！');
//                    }
//                } elseif ($data["rllx"] == 2) {//单位
//                    $data["name"] = $data["name1"];
//                    if ($data["dw"] && $data["name"] && $data["mobile"] && $data["aid"]) {
//                        $res = M("data_gycrl")->add($data);
//                        if ($res) {
//                            $this->success('认领成功', "gyc");
//                        } else {
//                            $this->error('数据异常,请稍后再试！');
//                        }
//                    } else {
//                        $this->error('参数有误,请填写完整！');
//                    }
//                } else {
//                    $this->error('参数有误,请填写完整！');
//                }

//            } else {
//                $this->error('待完善');
//            }

        }
    }


    public function xq()
    {
        if (IS_POST) {
            $data = $_POST;
            header('Content-Type:text/html; charset=utf-8');
            $data['create_time'] = time();
            $data['openid'] = cookie('wxOpenId');

            $data["enddate"] = strtotime($data["enddate"]);
            $data["age"] = intval($data["age"], 0);
            if ($data["xqf"] == 1) {
                if ($data["name"] && $data["enddate"] && $data["xqlb"] && $data["tel"] && $data["sex"] && $data["age"] && $data["content"]) {
                    $res = M("data_xq")->add($data);
                    if ($res) {
                        $this->success('需求提交成功', '/wxindex/pages/blockConstruct.html');
                    } else {
                        $this->error('数据异常,请稍后再试！');
                    }
                } else {
                    $this->error('参数有误,请填写完整！');
                }

            } elseif ($data["xqf"] == 2) {
                if ($data["name"] && $data["enddate"] && $data["xqlb"] && $data["tel"] && $data["dw"] && $data["content"]) {
                    $res = M("data_xq")->add($data);
                    if ($res) {
                        $this->success('需求提交成功', '/wxindex/pages/blockConstruct.html');
                    } else {
                        $this->error('数据异常,请稍后再试！');
                    }
                } else {
                    $this->error('参数有误,请填写完整！');
                }
            } else {
                $this->error('参数有误,请填写完整！');
            }
        } else {

            $this->assign("toptitle", "需求传声筒");
            $this->display("demand");
        }
    }

    public function dangqun() {
        $this->display();
    }
    /*
     * 党建工作*/
    public function djgz(){
        $map['status'] = array('egt',1);
        $map['type'] = 1; //党建服务站
        $djfwz = M('data_djgz')->where($map)->order('time desc')->field('id,title,wxlink')->select();
        $map['type'] = 2; //党建+
        $dj = M('data_djgz') -> where($map)->order('time desc') ->field('id,title,wxlink')->select();
        $this->assign('djfwz',$djfwz);
        $this->assign('dj',$dj);
        $this->display('djgz');
    }
    /*
     * 党建工作详情页*/
    public function djgList($id){
        $map['status'] = array('egt',1);
        $map['id'] = $id;
        $data = M('data_djgz')->where($map)->field('id,title,time,content')->find();
        $data['time'] = date('Y-m-d',$data['time'])  ;
//        $data['content'] = "<style>img{max-width:90%;}</style>".$data['content'];
        $this->assign('data',$data);
        $this->display('djgList');
    }
    /*
     * 公共列表页*/
    public function publicList($type=1){
        /*
         * 分类*/
        $types= array(
            1 =>'美丽合庆一路有你风采',
            2 =>'我身边的优秀党员',
            3 =>'团委征文' ,
            4 =>'工青妇活动展示' ,
            5 =>'政策解答' ,
            6 =>'活动展示' ,
            7 =>'党务实务' ,
            8 =>'党建知识' ,
            9 =>'两学一做' ,
            10 =>'党史回顾' ,
            11 =>'故事党课' ,
            12 =>'党课回顾' ,
            13 =>'党建活动' ,
            14 =>'活动概况' ,
        );
        /*
        * 表*/
        $tables = array(
            1 =>'data_djfc',
            2 =>'data_djfc',
            3 =>'data_dqzj',
            4 =>'data_dqzj',
            5 =>'data_dqzj',
            6 =>'data_dqzj',
            7 =>'data_dqzj',
            8 =>'data_dqzj',
            9 =>'data_dqzj',
            10 =>'data_dqzj',
            11 =>'data_dkzx',
            12 =>'data_dkzx',
            13 =>'data_dqzj',
            14 =>'data_dqzj',
        );
        /*
        * 视图*/
        $dis = array(
             1=>'commonList' ,
             2=>'commonList' ,
             3=>'commonList' ,
             4=>'commonList' ,
             5=>'commonList' ,
             6=>'commonList' ,
             7=>'commonList' ,
             8=>'commonList' ,
             9=>'commonList' ,
             10=>'commonList' ,
             11=>'commonList' ,
             12=>'commonList' ,
             13=>'commonList' ,
             14=>'commonList' ,
        )   ;
        /*
        * 表中type类*/
        $maptype = array(
            1=>1 ,             //美丽合庆一路有你风采
            2=>2 ,             //我身边的优秀党员
            3=>1 ,             //团委征文
            4=>2 ,             //工青妇活动展示
            5=>3 ,             //政策解答
            6=>4 ,             //活动展示
            7=>5 ,             //党务实务
            8=>6 ,             //党建知识
            9=>7 ,             //两学一做
            10=>8 ,            //党史回顾
            11=>1 ,            //故事党课
            12=>2 ,            //党课回顾
            13=>9,            //党课回顾
            14=>10,            //党课回顾
        )   ;
        $this->assign('title',$types[$type]);
        $this->assign('type',$type);
        $map['status'] = array('egt',1);
        $map['type'] = $maptype[$type];
        $data = M($tables[$type])->where($map)->field('id,title,wxlink')->order('id desc')->select();
        foreach( $data as $k=>$v){
            if (!preg_match('/(http:\/\/)|(https:\/\/)/i', $v['wxlink'])) {
                $data[$k]['wxlink'] = 'http://'.$v['wxlink'];
            }
        }
        $this->assign('data',$data);
        $this->display($dis[$type]);
    }
    /*
     *公用详情页*/
    public function publicDetail($id,$type=1){
        $tables = array(
            1 =>'data_djfc',
            2 =>'data_djfc',
            3 =>'data_dqzj',
            4 =>'data_dqzj',
            5 =>'data_dqzj',
            6 =>'data_dqzj',
            7 =>'data_dqzj',
            8 =>'data_dqzj',
            9 =>'data_dqzj',
            10 =>'data_dqzj',
            11 =>'data_dkzx',
            12 =>'data_dkzx',
            13 =>'data_dqzj',
            14 =>'data_dqzj',
        );
        $dis = array(
            1=>'detail' ,
            2=>'detail' ,
            3=>'detail' ,
            4=>'detail' ,
            5=>'detail' ,
            6=>'detail' ,
            7=>'detail' ,
            8=>'detail' ,
            9=>'detail' ,
            10=>'detail' ,
            11=>'detail' ,
            12=>'detail' ,
            13=>'detail' ,
            14=>'detail' ,
        );
        $map['status'] = array('egt',1);
        $map['id'] = $id;
        $data = M($tables[$type])->where($map)->field('id,title,time,content')->find();
        $data['time'] = date('Y-m-d',$data['time']);
        $this->assign('data',$data);
        $this->display($dis[$type]);
    }

    /*
     * 故事征集*/
    public function gszj(){
        $emil = M('back')->order('id desc')->find();
        $this->assign('email',$emil['a'])     ;
        $this->display('gszj');
    }
}