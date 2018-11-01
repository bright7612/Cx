<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/23
 * Time: 20:48
 */
namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Think\Page;

// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');
class DataController extends AdminController{
    public function content($page = 1, $r = 10,$type='1',$title=1)
    {


        $category = M('data_issue');
        $groupid = session('GroupId');
        $user = session('user_auth');
        $uid = $user['uid'];

        $this->leftnav($type); //左侧栏显示
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $map['status'] = 1;
        if($type){
            $map['issue_id'] = $type;
        }

        $where['status'] = 1;
        switch ($type){
            case 19:
                $count = M('dr_dy_ztdr')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dy_ztdr')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('party_day');
               break;
            case 27:
                $count = M('dr_dy_unztdr')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dy_unztdr')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('unparty_day');
               break;
            case 20:
                $count = M('dr_dy_ztdr')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dy_ztdr')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('party_day');
                break;
            case 21:
                $count = M('dr_dy_late')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dy_late')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('late');
                break;
            case 23:
                $where['result1'] = '不健康';
                $count = M('dr_dy_dxtj')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dy_dxtj')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('dxtj');
                break;
            case 24:
                $where['result1'] = '亚健康';
                $count = M('dr_dy_dxtj')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dy_dxtj')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('dxtj');
                break;
            case 22:
                $where['result1'] = '健康';
                $count = M('dr_dy_dxtj')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dy_dxtj')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('dxtj');
                break;
            case 25:
                $count = M('dr_dy_sgyx')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dy_sgyx')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
                foreach ($data as $k=>&$value){
                    $value['speech']= mb_substr($value['speech'],0,70,'UTF-8').'...';
                }
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('sgyx');
                break;
            case 26:
                $count = M('dr_dzz_honor')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dzz_honor')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('honor');
                break;
            case 6:
                $count = M('dr_dzz_ztdr')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dzz_ztdr')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('dzz_ztdr');
                break;
            case 7:
                $count = M('dr_dzz_no_ztdr')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dzz_no_ztdr')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('unztdr');
                break;
            case 8:
                $count = M('dr_dzz_application_party')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dzz_application_party')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('party');
                break;
            case 9:
                $count = M('dr_dzz_activity_dy')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dzz_activity_dy')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('activity');
                break;
            case 10:
                $count = M('dr_dzz_dxtj')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dzz_dxtj')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('dzz_dxtj');
                break;
            case 11:
                $count = M('dr_dzz_undxtj')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dzz_undxtj')->limit($Page->firstRow.','.$Page->listRows)->where($where)->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('dzz_undxtj');
                break;
            case 12:
                $where['money'] = array('neq',0);
                $count = M('dr_dzz_money')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dzz_money')->limit($Page->firstRow.','.$Page->listRows)->where($where)->field("time,organization,id,money")->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('dzz_money');
                break;
            case 13:
                $where['money'] = array('eq',0);
                $count = M('dr_dzz_money')->where($where)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = M('dr_dzz_money')->limit($Page->firstRow.','.$Page->listRows)->where($where)->field("time,organization,id,money")->order('id DESC')->select();
                $this->assign('issue_id',$type);
                $this->assign('data',$data);
                $this->assign('pagination',$show);
                $this->display('dzz_money');
                break;
        }

    }

    private function leftnav($type){
        $category = M('data_issue');
        $groupid = session('GroupId');
        $user = session('user_auth');
        $uid = $user['uid'];


        if($type){
            $fatherId1 =  $category->where('id='.$type)->getField('pid');

            $fatherId2 =  $category->where('id='.$fatherId1)->getField('pid');

            $fatherId3 =  $category->where('id='.$fatherId2)->getField('pid');

            $this->assign('type',$type);
        }

        $this->assign('actionname',ACTION_NAME);
        $this->assign('accontroller',CONTROLLER_NAME);



        if(CONTROLLER_NAME =='Data'){
            $mapcontent['status']=1;
            if($groupid == 3){
                $mapcontent['group_id'] = array('like','%3%');
            }elseif ($groupid == 4){
                $mapcontent['group_id'] = array('like','%4%');
            }
            $mapcontent['lv']=0;
            $issuemenus = $category->where($mapcontent)->field(true)->order('sort asc')->select();
            $aa = M()->getLastsql();

            foreach($issuemenus as $k=>$v){
                $mapcontent['lv']=1;
                $mapcontent['pid']=$v['id'];
                $issuemenus1 = $category->where($mapcontent)->field(true)->order('sort asc')->select();
                $issuemenus[$k]['issuemenus'] =$issuemenus1;
                $issuemenus[$k]['count'] =count($issuemenus1);
                if($v['id']==$fatherId1||$v['id']==$fatherId2||$v['id']==$fatherId3)$issuemenus[$k]['class'] =' open in ';
                if($v['id']==$type)$issuemenus[$k]['class'] ='active';

                foreach($issuemenus[$k]['issuemenus'] as $k1=>$v1){
                    $mapcontent['lv']=2;
                    $mapcontent['pid']=$v1['id'];
                    $issuemenus2 = $category->where($mapcontent)->field(true)->order('sort asc')->select();
                    $issuemenus[$k]['issuemenus'][$k1]['issuemenus'] =$issuemenus2;
                    $issuemenus[$k]['issuemenus'][$k1]['count'] = count($issuemenus2);
                    if($v1['id']==$fatherId1||$v1['id']==$fatherId2||$v1['id']==$fatherId3){
                        $issuemenus[$k]['issuemenus'][$k1]['class'] =' open in ';
                    }

                    $issuemenus[$k]['issuemenus'][$k1]['url'] ='/admin/cx/content/type/'.$type;

                    if($v1['id']==$type)$issuemenus[$k]['issuemenus'][$k1]['class'] ='active';
                    foreach($issuemenus[$k]['issuemenus'][$k1]['issuemenus'] as $k2=>$v2){
                        $mapcontent['lv']=3;
                        $mapcontent['pid']=$v2['id'];
                        $issuemenus3 = $category->where($mapcontent)->field(true)->order('sort asc')->select();
                        $issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['issuemenus'] =$issuemenus3;
                        $issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['count'] =count($issuemenus3);
                        if($v1['id']==$fatherId1||$v1['id']==$fatherId2||$v1['id']==$fatherId3){
                            $issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['class'] =' open in ';
                        }
                        if($v2['id']==$type)$issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['class'] ='active';
                        foreach($issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['issuemenus'] as $k3=>$v3){
                            if($v3['id']==$type)$issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['issuemenus'][$k3]['class'] ='active';
                        }
                    }
                }

            }

            $this->assign('menutree',1);
            $this->assign('issuemenus1', $issuemenus);
        }
    }
    public function addcontent($type=0){

        $category = M('data_issue');
        $groupid = session('GroupId');
        $user = session('user_auth');
        $uid = $user['uid'];

        $id = I("id", 0, "intval");
        $act = $id ? "编辑" : "新增";
        if (IS_POST) {
            $data = $_POST;
            $data['edit_time'] = time();
            switch ($type){
                case 19:
                    $content = M('dr_dy_ztdr');
                    break;
                case 27:
                    $content = M('dr_dy_unztdr');
                    break;
                case 20:
                    $content = M('dr_dy_ztdr');
                    break;
                case 21:
                    $content = M('dr_dy_late');
                    break;
                case 22:
                    $content = M('dr_dy_dxtj');
                    break;
                case 23:
                    $content = M('dr_dy_dxtj');
                    break;
                case 24:
                    $content = M('dr_dy_dxtj');
                    break;
                case 25:
                    $content = M('dr_dy_sgyx');
                    break;
                case 26:
                    $content = M('dr_dzz_honor');
                    break;
                case 6:
                    $content = M('dr_dzz_ztdr');
                    break;
                case 7:
                    $content = M('dr_dzz_no_ztdr');
                    break;
                case 8:
                    $content = M('dr_dzz_application_party');
                    break;
                case 9:
                    $content = M('dr_dzz_activity_dy');
                    break;
                case 10:
                    $content = M('dr_dzz_dxtj');
                    break;
                case 11:
                    $content = M('dr_dzz_undxtj');
                    break;
                case 12:
                    $content = M('dr_dzz_money');
                    break;
                case 13:
                    $content = M('dr_dzz_money');
                    break;
            }


            if ($data["id"]) {
                if ($content->save($data) !== false) {
                    $this->success(L('_SUCCESS_UPDATE_'), Cookie('__forward__'));
                } else {
                    $this->error(L('_FAIL_UPDATE_'));
                }
            } else {

                $data['create_time'] = time();
                $data['uid'] = $uid;
                session('issue_id_temp',$data['issue_id']);
                if ($content->add($data) !== false) {
                    $this->success("添加成功", Cookie('__forward__'));
                } else {
                    $this->error("添加失败");
                }
            }

        } else {

            $this->leftnav($type); //左侧栏
            $builder = new AdminConfigBuilder();
            switch ($type){
                case 19:
                    $content = M('dr_dy_ztdr');
                    break;
                case 27:
                    $content = M('dr_dy_unztdr');
                    break;
                case 20:
                    $content = M('dr_dy_ztdr');
                    break;
                case 21:
                    $content = M('dr_dy_late');
                    break;
                case 22:
                    $content = M('dr_dy_dxtj');
                    break;
                case 23:
                    $content = M('dr_dy_dxtj');
                    break;
                case 24:
                    $content = M('dr_dy_dxtj');
                    break;
                case 25:
                    $content = M('dr_dy_sgyx');
                    break;
                case 26:
                    $content = M('dr_dzz_honor');
                    break;
                case 6:
                    $content = M('dr_dzz_ztdr');
                    break;
                case 7:
                    $content = M('dr_dzz_no_ztdr');
                    break;
                case 8:
                    $content = M('dr_dzz_application_party');
                    break;
                case 9:
                    $content = M('dr_dzz_activity_dy');
                    break;
                case 10:
                    $content = M('dr_dzz_dxtj');
                    break;
                case 11:
                    $content = M('dr_dzz_undxtj');
                    break;
                case 12:
                    $content = M('dr_dzz_money');
                    break;
                case 13:
                    $content = M('dr_dzz_money');
                    break;
            }

            $data = $content->where(array("id" => $id))->find();
            if($type){
                $data['issue_id'] = $type;
            }elseif(session('issue_id_temp') && !$data['issue_id']){
                $data['issue_id'] = session('issue_id_temp');
                session('issue_id_temp','');
            }

            if($groupid == 3){
                $mapcontent2['group_id'] = array('like','%3%');
            }elseif ($groupid == 4){
                $mapcontent2['group_id'] = array('like','%4%');
            }

            $mapcontent2['status']=1;
            $menus = $category->where($mapcontent2)->field(true)->order('sort asc')->select();
            foreach($menus as $k=>$v){
                if($v['lv']==0){
                    $menus[$k]['title'].='===';
                }
                //└
                if($v['lv']==1){
                    $menus[$k]['title'] = '　└'.$menus[$k]['title'];
                    $menus[$k]['title'].='---';
                }
                if($v['lv']==2){
                    $menus[$k]['title'] = '　　└'.$menus[$k]['title'];
                }
                if($v['lv']==3){
                    $menus[$k]['title'] = '　　　　└'.$menus[$k]['title'];
                }
                if($v['lv']==4){
                    $menus[$k]['title'] = '　　　　　└'.$menus[$k]['title'];
                }
                if($v['lv']==5){
                    $menus[$k]['title'] = '　　　　　　└'.$menus[$k]['title'];
                }
                if($v['lv']==6){
                    $menus[$k]['title'] = '　　　　　　　└'.$menus[$k]['title'];
                }
            }



            $menus = D('Common/Tree')->toFormatTree($menus);
            $options = array_combine(array_column($menus, 'id'), array_column($menus, 'title'));
//            $select = array('0'=>'请选择','1'=>'宣传片','2'=>'合庆专题之路','3'=>'合庆镇环境综合整治治理行动纪实');

            if($type == 19) {
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('name', '姓名')
                    ->keyText('organization', '所属党组织')
                    ->keyText('title', '主题')
                    ->keyText('time', '时间')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 27 ) {
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('name', '姓名')
                    ->keyText('organization', '所属党组织')
                    ->keyText('time', '时间')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 20) {
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('name', '姓名')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('organization', '所属党组织')
                    ->keyText('title', '主题')
                    ->keyText('time', '时间')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 21){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('name', '姓名')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('organization', '所属党组织')
                    ->keyText('ID_CARD', "身份证")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 22 || $type == 23 || $type == 24){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('name', '姓名')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('organization', '所属党组织')
                    ->keyText('result1', "健康状态")
                    ->keyText('result2', "评价")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();

            }elseif($type == 25){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('name', '姓名')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyTextArea('speech', "演讲内容")
                    ->keyText('dy_lv', '等级')
                    ->keyText('time', '时间')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 26){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('name', '姓名')
                    ->keyText('organization', '所属党组织')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('honor', "优秀称号")
                    ->keyText('cre_time', '创建时间')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 26){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('name', '姓名')
                    ->keyText('organization', '所属党组织')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('honor', "优秀称号")
                    ->keyText('cre_time', '创建时间')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 6){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('organization', '所属党组织')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('time', '创建时间')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 7){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('organization', '所属党组织')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('secretary', '书记')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 8){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('name', '姓名')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('sex', '性别')
                    ->keyText('birthday', '出生日期')
                    ->keyText('education', '文化程度')
                    ->keyText('organization', '所属党组织')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 9){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('name', '姓名')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('sex', '性别')
                    ->keyText('education', '文化程度')
                    ->keyText('organization', '所属党组织')
                    ->keyText('phone', '电话')
                    ->keyText('technical_title', '职称')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 10){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('secretary', '书记')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('organization', '所属党组织')
                    ->keyText('time', '时间')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 11){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('secretary', '书记')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('organization', '所属党组织')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 12 || $type == 13){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('organization', '所属党组织')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('money', '交纳党费')
                    ->keyText('time', '缴纳时间')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }

        }
    }



    public  function import(){
        $this->display('import');
    }

    /*
     * 设置状态
     *
     */

    public function setWildcardStatus(){
        $type = $_REQUEST['type'];
        switch ($type){
            case 19:
                $content = M('dr_dy_ztdr');
                break;
            case 27:
                $content = M('dr_dy_unztdr');
                break;
            case 20:
                $content = M('dr_dy_ztdr');
                break;
            case 21:
                $content = M('dr_dy_late');
                break;
            case 22:
                $content = M('dr_dy_dxtj');
                break;
            case 23:
                $content = M('dr_dy_dxtj');
                break;
            case 24:
                $content = M('dr_dy_dxtj');
                break;
            case 25:
                $content = M('dr_dy_sgyx');
                break;
            case 26:
                $content = M('dr_dzz_honor');
                break;
            case 6:
                $content = M('dr_dzz_ztdr');
                break;
            case 7:
                $content = M('dr_dzz_no_ztdr');
                break;
            case 8:
                $content = M('dr_dzz_application_party');
                break;
            case 9:
                $content = M('dr_dzz_activity_dy');
                break;
            case 10:
                $content = M('dr_dzz_dxtj');
                break;
            case 11:
                $content = M('dr_dzz_undxtj');
                break;
            case 12:
                $content = M('dr_dzz_money');
                break;
            case 13:
                $content = M('dr_dzz_money');
                break;
        }
        $ids = I('id');
        $status = $_GET['status'];
        $builder = new AdminListBuilder();
        $builder->doSetStatus($content, $ids, $status);
    }

    /**
     * 还原
     */
    public function setEditStatus(){
        $ids = I('ids');
        $status = I('get.status', 0, 'intval');
        $builder = new AdminListBuilder();
        $builder->doSetStatus('dr_dzz_money', $ids, $status);
    }

    /**
     * 删除
     */
    public function setDeltteStatus(){
        $ids = I('ids');
        $status = -2;
        $builder = new AdminListBuilder();
        $builder->doSetStatus('dk_issue_content', $ids, $status);
    }





}