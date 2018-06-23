<?php
namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Think\Page;

header("Content-Type:text/html; charset=UTF-8");
class CxController extends AdminController{
    private $bespoke; //活动报名表
    private $volunteer; //志愿者报名表
    private $field; //场地报名表
    private $lecture; //党课报名表
    private $tutor; //讲师报名表
    private $issue_content; //内容表
    private $wish; //微心愿内容表
    private $wishapply; //微心愿申请
    private $wishevaluate; //微心愿评价
    private $wxuser; //微信用户表
    private $zhch; //众筹申请
    private $zhchapply; //众筹参与
    private $zhchevaluate; //众筹评价
    private $direct; //项目直通车管理
    private $directapply; //项目直通车参与管理
    private $directevaluate; //项目直通车参与管理
    function _initialize(){
        $this->bespoke = M('sign_bespoke');
        $this->issue_content = M('issue_content');
        $this->volunteer = M('sign_volunteer');
        $this->lecture = M('sign_lecture');
        $this->tutor = M('sign_tutor');
        $this->field = M('sign_field');
        $this->wish = M('sign_wish');
        $this->wishapply = M('sign_wish_apply');
        $this->wishevaluate = M('sign_wish_evaluate');
        $this->wxuser = M('wxuser');
        $this->zhch = M('sign_zhch');
        $this->zhchapply = M('sign_zhch_apply');
        $this->zhchevaluate = M('sign_zhch_evaluate');
        $this->direct = M('sign_direct');
        $this->directapply = M('sign_direct_apply');
        $this->directevaluate = M('sign_direct_evaluate');
    }

    /*
     * 数据状态*/
    private   $status = array(
        -1 =>'已删除',
        1 =>'正常',
        0 =>'已禁止',
    );
    /*
     * 审核状态*/
    private  $state = array(
        -1 => '审核未通过',
        0 => '待审核',
        1 => '审核通过',
        3 => '用户已取消',
    );
    /*
     * 数据来源*/
    private $source = array(
        1 =>'微信申请',
        2 =>'大屏申请',
        3 =>'后台添加',
    );
    /*
     * 申请方式*/
    private $company = array(
        0 =>'单位申请',
        1 =>'个人申请',
    );
    private $party = array(
        0 =>'非党员',
        1 =>'党员',
    );

    public function heart($page = 1, $r = 10){
        //读取列表
        $map['status'] = array('egt',0);
        $model = M('sign_heart');
        $list = $model->where($map)->page($page, $r)->order("cre_time desc")->select();
        $totalCount = $model->where($map)->count();

        $sta = array(
            1 => '正常',
            0 => '禁止',
        );
        foreach ($list as &$v) {
            $v['cre_time'] = date('Y-m-d H:i:s', $v['cre_time']);
            $v['status_var'] = $sta[$v['status']];
        }
        $builder = new AdminListBuilder();
        $builder->title('党员心声')
            ->setStatusUrl(U('heartStatus'))
            ->buttonEnable()->buttonDisable()->buttonDelete()
            ->keyId()
            ->keyText('name', '姓名')
            ->keyText('organization', '党组织')
            ->keyText('content', '心声')
            ->keyText('status_var', '状态')
            ->keyDoAction("heartStatus?ids=###&status=0", "禁用", "操作", array('class' => 'ajax-get'))
            ->keyDoAction("heartStatus?ids=###&status=1", "启用", "操作", array('class' => 'ajax-get'))
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }


    /*列表*/
    public function content($page = 1, $r = 10,$type=82,$title=1)
    {
        $category = M('issue');
        $groupid = session('GroupId');
        $user = session('user_auth');
        $uid = $user['uid'];

        $this->leftlist($type);
        if($type == 99 || $type == 100 || $type == 101){
            $builder  = new AdminConfigBuilder();
            $this->assign('issue_id',$type);
            $map['status'] = array('egt',1);
            $sta = array(
                100=>'全部',
                -1 => '审核未通过',
                0 => '待审核',
                1 => '审核通过',
                4=> '用户已取消',
            );
            if($type == 99 ){
                unset($sta[4]);
            }
            $stt = I('types');
            if($stt!=''&&$stt!=100){
                $map['state'] = $stt;
            }else{
                $stt =100;
            }

            $this->assign('sta',$sta); //状态显示
            $this->assign('stt',$stt);//当前状态

            switch ($type){
                case 99://项目直通车管理
                    $this->assign('title','项目管理');
                    $count =$this->direct->where($map)->count();
                    $Page = new Page($count,$r);
                    $show = $Page->show();
                    $data = $this->direct->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('sore desc,id desc')->select();
                    foreach ($data as $k=>&$v) {
                        $v['img2'] = explode(',',$v['img']);
                        foreach ($v['img2'] as $k2=>$v2){
                            $v['img_var'][] = pic($v2);
                        }
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                    }
//                    dump($data);exit;
                    $this->assign('data',$data); //申报信息
                    $this->assign('pagination',$show);
                    //dump($data);exit;
                    $this->display('direct');
                    break;
                case 100: //项目直通车参与管理
                    $this->assign('title','项目报名管理');
                    $count =$this->directapply->where($map)->count();
                    $Page = new Page($count,$r);
                    $show = $Page->show();
                    $data = $this->directapply->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
                    foreach ($data as $k=>&$v) {
                        $v['direct_id_var'] = $this->direct->where(array('id'=>$v['direct_id']))->getField('title');
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['source_var'] = $this->source[$v['source']];
                        $v['party_var'] =$this->party[$v['party']];
                        $v['types_var'] =$this->company[$v['types']];
                    }
                    $this->assign('data',$data); //报名信息
                    $this->assign('pagination',$show);
                    $this->display('directapply');
                    break;
                case 101: //项目直通车评价
                    $pj = array(
                        1=>'非常满意',
                        2=>'满意',
                        3=>'一般',
                        4=>'不够满意',
                    );
                    $this->assign('title','众筹评价管理');
                    $count =$this->directevaluate->where($map)->count();
                    $Page = new Page($count,$r);
                    $show = $Page->show();
                    $data = $this->directevaluate->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
                    foreach ($data as $k=>&$v) {
                        $v['direct_id_var'] = $this->direct->where(array('id'=>$v['direct_id']))->field('title')->find();
                        $v['openid_var'] = $this->wxuser->where(array('openid'=>$v['openid']))->field('nickname,headimgurl')->find();
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['pj_var'] = $pj[$v['pj']];
                    }
                    $this->assign('data',$data); //报名信息
                    $this->assign('pagination',$show);
                    $this->display('directevaluate');
                    break;
            }
        }
        elseif($type == 96 || $type == 97 || $type == 98){
            $builder  = new AdminConfigBuilder();
            $this->assign('issue_id',$type);
            $map['status'] = array('egt',1);
            $sta = array(
                100=>'全部',
                -1 => '审核未通过',
                0 => '待审核',
                1 => '审核通过',
                2 => '已认领',
                3 => '已完成',
                4=> '用户已取消',
            );
            $typ = array(
                1=>'金钱众筹',
                2=>'人员众筹',
            );
            if($type == 96 ){
                unset($sta[2]);
            }
            if($type == 97 ){
               $sta[2]='已联系';
            }
            if($type == 98 ){
                unset($sta[2]);
                unset($sta[3]);
            }
            $stt = I('types');
            if($stt!=''&&$stt!=100){
                $map['state'] = $stt;
            }else{
                $stt =100;
            }

            $this->assign('sta',$sta); //状态显示
            $this->assign('stt',$stt);//当前状态


            switch ($type){
                case 96://众筹服务申报管理
                    $this->assign('title','众筹服务申报管理');
                    $count =$this->zhch->where($map)->count();
                    $Page = new Page($count,$r);
                    $show = $Page->show();
                    $data = $this->zhch->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('sore desc,id desc')->select();
                    foreach ($data as $k=>&$v) {
                        $v['img_var'] = pic($v['img']);
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['source_var'] = $this->source[$v['source']];
                        $v['party_var'] =$this->party[$v['party']];
                        $v['type_var'] =$typ[$v['classif']];
                    }
//                    dump($data);exit;
                    $this->assign('data',$data); //申报信息
                    $this->assign('pagination',$show);
                    $this->display('zhch');
                    break;
                case 97: //众筹服务参与管理
                    $this->assign('title','众筹服务参与管理');
                    $count =$this->zhchapply->where($map)->count();
                    $Page = new Page($count,$r);
                    $show = $Page->show();
                    $data = $this->zhchapply->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
                    foreach ($data as $k=>&$v) {
                        $v['zhch_id_var'] = $this->zhch->where(array('id'=>$v['zhch_id']))->getField('title');
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['source_var'] = $this->source[$v['source']];
                        $v['party_var'] =$this->party[$v['party']];
                        $v['types_var'] =$this->company[$v['types']];
                    }
                    $this->assign('data',$data); //报名信息
                    $this->assign('pagination',$show);
                    $this->display('zhchapply');
                    break;
                case 98: //众筹评价管理
                    $pj = array(
                        //1非常满意2满意3一般4不够满意
                        1=>'非常满意',
                        2=>'满意',
                        3=>'一般',
                        4=>'不够满意',
                    );
                    $this->assign('title','众筹评价管理');
                    $count =$this->zhchevaluate->where($map)->count();
                    $Page = new Page($count,$r);
                    $show = $Page->show();
                    $data = $this->zhchevaluate->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
                    foreach ($data as $k=>&$v) {
                        $v['zhch_id_var'] = $this->zhch->where(array('id'=>$v['zhch_id']))->field('title')->find();
                        $v['openid_var'] = $this->wxuser->where(array('openid'=>$v['openid']))->field('nickname,headimgurl')->find();
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['pj_var'] = $pj[$v['pj']];
                    }
//                    dump($data);exit;
                    $this->assign('data',$data); //报名信息
                    $this->assign('pagination',$show);
                    $this->display('zhchevaluate');
                    break;
            }



        }  //众筹
        elseif($type == 92 || $type == 93 || $type == 94 || $type == 95){
            $builder  = new AdminConfigBuilder();
            $this->assign('issue_id',$type);
            $map['status'] = array('egt',1);
            $sta = array(
                100=>'全部',
                -1 => '审核未通过',
                0 => '待审核',
                1 => '审核通过',
                2 => '已认领',
                3 => '已完成',
                4=> '用户已取消',
            );
            $obj = array(
                1=>'个人',
                2=>'团体',
                3=>'群众',
            );
            $form = array(
                1 => '物质',
                2 => '精神',
            );
            //按状态查询
            $stt = I('types');
            if($stt!=''&&$stt!=100){
                $map['state'] = $stt;
            }else{
                $stt =100;
            }
            if($type == 93 ){
                unset($sta[2]);
                unset($sta[3]);
            }
            if($type == 94 ){
                unset($sta[2]);
                unset($sta[3]);
                unset($sta[4]);
            }
            $this->assign('sta',$sta); //状态显示
            $this->assign('stt',$stt);//当前状态
            switch ($type){
                case 92://微心愿申报管理
                    $this->assign('title','微心愿申报管理');
                    $count =$this->wish->where($map)->count();
                    $Page = new Page($count,$r);
                    $show = $Page->show();
                    $data = $this->wish->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
                    foreach ($data as $k=>&$v) {
                        $v['img_var'] = pic($v['img']);
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['source_var'] = $this->source[$v['source']];
                        $v['obj_var'] = $obj[$v['obj']];
                        $v['form_var'] = $form[$v['form']];
                        $v['party_var'] =$this->party[$v['party']];
                    }
                    $this->assign('data',$data); //报名信息
                    $this->assign('pagination',$show);
                    $this->display('wxylist');
                    break;
                case 93: //微心愿认领管理
                    $this->assign('title','微心愿认领管理');
                    $count =$this->wishapply->where($map)->count();
                    $Page = new Page($count,$r);
                    $show = $Page->show();
                    $data = $this->wishapply->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
                    foreach ($data as $k=>&$v) {
                        $v['wish_id_var'] = $this->wish->where(array('id'=>$v['wish_id']))->getField('title');
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['source_var'] = $this->source[$v['source']];
                        $v['party_var'] =$this->party[$v['party']];
                        $v['company_var'] =$this->company[$v['party']];
                        $v['types_var'] =$this->company[$v['types']];
                    }
                    $this->assign('data',$data); //报名信息
                    $this->assign('pagination',$show);
                    $this->display('wxyapply');
                    break;
                case 94: //微心愿评价管理
                    $pj = array(
                        //1非常满意2满意3一般4不够满意
                        1=>'非常满意',
                        2=>'满意',
                        3=>'一般',
                        4=>'不够满意',
                    );
                    $this->assign('title','微心愿评价管理');
                    $count =$this->wishevaluate->where($map)->count();
                    $Page = new Page($count,$r);
                    $show = $Page->show();
                    $data = $this->wishevaluate->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('id desc')->select();
                    foreach ($data as $k=>&$v) {
                        $v['wish_id_var'] = $this->wish->where(array('id'=>$v['wish_id']))->field('title')->find();
                        $v['openid_var'] = $this->wxuser->where(array('openid'=>$v['openid']))->field('nickname,headimgurl')->find();
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['pj_var'] = $pj[$v['pj']];

                    }
//                    dump($data);exit;
                    $this->assign('data',$data); //报名信息
                    $this->assign('pagination',$show);
                    $this->display('wxyevaluate');
                    break;
            }
        } //微心愿
        elseif ($type == 104){
            $builder  = new AdminConfigBuilder();
            $this->assign('issue_id',$type);
            $map['status'] = array('egt',1);
            $sta = array(
                100=>'全部',
                -1 => '审核未通过',
                0 => '待审核',
                1 => '审核通过',
                4=> '用户已取消',
            );

            $stt = I('types');
            if($stt!=''&&$stt!=100){
                $map['state'] = $stt;
            }else{
                $stt =100;
            }

            $this->assign('sta',$sta); //状态显示
            $this->assign('stt',$stt);//当前状态`
            switch ($type) {
                case 104://微心愿申报管理
                    $tab = M('sign_heart');
                    $this->assign('title', '党员心声');
                    $count = $tab->where($map)->count();
                    $Page = new Page($count, $r);
                    $show = $Page->show();
                    $data = $tab->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->select();
                    foreach ($data as $k => &$v) {
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                    }
                    $this->assign('data', $data); //报名信息
                    $this->assign('pagination', $show);
                    $this->display('heart');
                    break;
            }
        }  //党员心声
        elseif($type == 107||$type == 109||$type == 110||$type == 111){
            //读取列表
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
            $map['status'] = 1;
            if($type){
                $map['issue_id'] = $type;
            }

            $model = M('Issue_content');
            $map['status'] = $title;
            $map['uid'] = $uid;
            $list = $model->where($map)->page($page, $r)->order("sort desc,id desc")->select();
            foreach ($list as $k=>$v){
                $issueInfo = $category->where('id = '.$v['issue_id'])->find();
                $list[$k]['issue_name'] =$issueInfo['title'] ;
                $list[$k]['link'] =$v['title'];
            }
            unset($li);
            $totalCount = $model->where($map)->count();
            //显示页面
            $builder = new AdminListBuilder();
            $attr['class'] = 'btn ajax-post';
            $attr['target-form'] = 'ids';

            header("Content-type:text/html;charset=UTF-8");
            if($title==1)$titlename = '已发布列表';
            if($title==0)$titlename = '编辑中列表';
            if($title==-1)$titlename = '已删除列表';

            $builder->title($titlename);
            $builder->setStatusUrl(U('setWildcardStatus'));
            if($title !=1){
                $builder->buttonEnable('', L('_AUDIT_SUCCESS_'));
            }

            if($title == -1){
                $builder->setStatusUrl(U('setEditStatus'));
                $builder->buttonRestore();
                $builder->setStatusUrl(U('setDeltteStatus'));
                $builder->buttonDelete();
            }

            if($title !=-1){
                $builder->buttonDelete();
            }
            //        dump($list);exit;
            $builder->button('新增', array('class' => 'btn btn-success','href' => U('admin/Cx/addcontent?type='.$type)));
            $builder->button('已发布列表', array('class' => 'btn btn-info','href' => U('admin/Cx/content?title=1&type='.$type)))
                ->button('编辑中列表', array('class' => 'btn btn-warning','href' => U('admin/Cx/content?title=0&type='.$type)))
                ->button('已删除列表', array('class' => 'btn btn-danger','href' => U('admin/Cx/content?title=-1&type='.$type)));
            $builder->keyId()
                ->keyText('link','标题')
                ->keyImage('cover_id', '封面',array('width'=>'25px'))
                ->keyCreateTime('time','显示时间')
                ->keyDoActionEdit("admin/Cx/addcontent/type/".$type.'?id=###')
                ->data($list)
                ->pagination($totalCount, $r)
                ->display();
        }
        else{
            //读取列表
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
            $map['status'] = 1;
            if($type){
                $map['issue_id'] = $type;
            }

            $model = M('Issue_content');
            $map['status'] = $title;
            $map['uid'] = $uid;
            $list = $model->where($map)->page($page, $r)->order("sort desc,id desc")->select();
    //        dump($map);
    //        dump($list);exit;
           $appointment = array(
              1 => '网页预约',
              2 => '电话预约'
           ) ;
            foreach ($list as $k=>$v){
                $issueInfo = $category->where('id = '.$v['issue_id'])->find();
                $list[$k]['issue_name'] =$issueInfo['title'] ;
                $list[$k]['link'] =$v['title'];
                $list[$k]['mode'] =$appointment[$v['mode']];
            }
            unset($li);
            $totalCount = $model->where($map)->count();


            //显示页面
            $builder = new AdminListBuilder();
            $attr['class'] = 'btn ajax-post';
            $attr['target-form'] = 'ids';

            header("Content-type:text/html;charset=UTF-8");
            if($title==1)$titlename = '已发布列表';
            if($title==0)$titlename = '编辑中列表';
            if($title==-1)$titlename = '已删除列表';

            $builder->title($titlename);
            $builder->setStatusUrl(U('setWildcardStatus'));
            if($title !=1){
                $builder->buttonEnable('', L('_AUDIT_SUCCESS_'));
            }

            if($title == -1){
                $builder->setStatusUrl(U('setEditStatus'));
                $builder->buttonRestore();
                $builder->setStatusUrl(U('setDeltteStatus'));
                $builder->buttonDelete();
            }

            if($title !=-1){
                $builder->buttonDelete();
            }


    //        dump($list);exit;
            $builder->button('新增', array('class' => 'btn btn-success','href' => U('admin/Cx/addcontent?type='.$type)));
            $builder->button('已发布列表', array('class' => 'btn btn-info','href' => U('admin/Cx/content?title=1&type='.$type)))
                ->button('编辑中列表', array('class' => 'btn btn-warning','href' => U('admin/Cx/content?title=0&type='.$type)))
                ->button('已删除列表', array('class' => 'btn btn-danger','href' => U('admin/Cx/content?title=-1&type='.$type)));

            if($type == 82){ //党建活动预约管理
                $builder->keyId()
                    ->keyText('link','标题')
                    ->keyText('addr','活动地址')
                    ->keyCreateTime('time','活动开始时间')
                    ->keyText('num','人数')
                    ->keyText('host','组织单位')
                    ->keyDoActionEdit("admin/Cx/addcontent/type/".$type.'?id=###')
                    ->keyDoActionEdit("admin/Cx/sign/type/".$type.'?content_id=###','查看报名人员')
                    ->data($list)
                    ->pagination($totalCount, $r)
                    ->display();
            }elseif ($type == 84){ //志愿者报名管理
                $builder->keyText('link','标题')
                    ->keyText('addr','活动地址')
                    ->keyText('gather','活动集合地点')
                    ->keyText('service_object','服务对象')
                    ->keyText('time','活动开始时间')
                    ->keyText('duration','活动持续时间')
                    ->keyCreateTime()
                    ->keyText('num','计划招募人数')
                    ->keyText('host','组织单位')
                    ->keyDoActionEdit("admin/Cx/addcontent/type/".$type.'?id=###')
                    ->keyDoActionEdit("admin/Cx/sign/type/".$type.'?content_id=###','查看报名人员')
                    ->data($list)
                    ->pagination($totalCount, $r)
                    ->display();
            }elseif ($type == 86){ //场地预约管理
                $builder->keyText('link','标题')
                    ->keyText('addr','活动地址')
                    ->keyText('mode','预约方式')
                    ->keyImage('cover_id','封面图片',array('width'=>25))
                    ->keyCreateTime()
                    ->keyText('num','最大容纳人数')
                    ->keyText('host','组织单位')
                    ->keyText('telphone','预约电话')
                    ->keyText('sort','排序')
                    ->keyDoActionEdit("admin/Cx/addcontent/type/".$type.'?id=###')
                    ->keyDoActionEdit("admin/Cx/sign/type/".$type.'?content_id=###','查看报名人员')
                    ->data($list)
                    ->pagination($totalCount, $r)
                    ->display();
            }elseif ($type == 88){ //党课预约管理
                $builder->keyText('link','党课名称')
                    ->keyText('addr','授课地址')
                    ->keyText('host','举办单位')
                    ->keyCreateTime('time','举办时间')
                    ->keyText('teacher','授课老师')
                    ->keyText('user','联系人')
                    ->keyText('telphone','联系电话')
                    ->keyText('num','教室最大人数')
                    ->keyText('sort','排序')
                    ->keyDoActionEdit("admin/Cx/addcontent/type/".$type.'?id=###')
                    ->keyDoActionEdit("admin/Cx/sign/type/".$type.'?content_id=###','查看报名人员')
                    ->data($list)
                    ->pagination($totalCount, $r)
                    ->display();
            }elseif ($type == 90){ //讲师预约管理
                $builder
                    ->keyImage('cover_id','讲师头像',array('width'=>40))
                    ->keyText('link','讲师姓名')
                    ->keyText('host','所属组织')
                    ->keyText('content','讲师简介')
                    ->keyText('sort','排序')
                    ->keyDoActionEdit("admin/Cx/addcontent/type/".$type.'?id=###')
                    ->keyDoActionEdit("admin/Cx/sign/type/".$type.'?content_id=###','查看报名人员')
                    ->data($list)
                    ->pagination($totalCount, $r)
                    ->display();
            }elseif ($type == 105){ //讲师预约管理
                $builder
                    ->keyImage('cover_id','党员头像',array('width'=>40))
                    ->keyText('link','姓名')
                    ->keyText('host','所属组织')
                    ->keyText('content','讲师简介')
                    ->keyText('sort','排序')
                    ->keyDoActionEdit("admin/Cx/addcontent/type/".$type.'?id=###')
                    ->data($list)
                    ->pagination($totalCount, $r)
                    ->display();
            }
        } //预约服务
    }

    /*新增*/
    public function addcontent($type=0){

//        dump($_POST);die;
        $category = M('Issue');
        $groupid = session('GroupId');
        $user = session('user_auth');
        $uid = $user['uid'];

        $id = I("id", 0, "intval");
        $act = $id ? "编辑" : "新增";

        if (IS_POST) {
            $data = $_POST;

//            dump($data);exit;
            $data['update_time'] = time();
            $Model = M('Issue_content');

            if($groupid == 10){
                $data['project_category'] = $uid;
            }


            if ($data["id"]) {
                if ($Model->save($data) !== false) {
                    $this->success(L('_SUCCESS_UPDATE_'), Cookie('__forward__'));
                } else {
                    $this->error(L('_FAIL_UPDATE_'));
                }
            } else {

                $data['create_time'] = time();
                $data['uid'] = $uid;
                session('issue_id_temp',$data['issue_id']);
                if ($Model->add($data) !== false) {
                    $this->success("添加成功", Cookie('__forward__'));
                } else {
                    $this->error("添加失败");
                }
            }

        } else {

            if($type){
                $fatherId1 =  $category->where('id='.$type)->getField('pid');
                $fatherId2 =  $category->where('id='.$fatherId1)->getField('pid');
                $fatherId3 =  $category->where('id='.$fatherId2)->getField('pid');
                $this->assign('type',$type);
            }

            $this->assign('actionname',ACTION_NAME);  //方法名
            $this->assign('accontroller',CONTROLLER_NAME);  //控制器名

            if(CONTROLLER_NAME =='Cx'){
                $mapcontent['status']=1;
                if($groupid == 3){
                    $mapcontent['group_id'] = array('like','%3%');
                }elseif ($groupid == 10){
                    $mapcontent['group_id'] = array('like','%10%');
                }
                $mapcontent['lv']=0;
//            $mapcontent['project_category'] = 0;
                $issuemenus = $category->where($mapcontent)->field(true)->order('sort asc')->select();
//            dump( $issuemenus);die;
                foreach($issuemenus as $k=>$v){
                    $mapcontent['lv']=1;
//                if($groupid == 11){
//                    $mapcontent['group_id']=11;
//                }elseif ($groupid == 10){
//                    $mapcontent['has_jw']=1;
//                }
                    $mapcontent['pid']=$v['id'];
//                $mapcontent['project_category'] = 0;
                    $issuemenus1 = $category->where($mapcontent)->field(true)->order('sort asc')->select();
                    $issuemenus[$k]['issuemenus'] =$issuemenus1;
//                dump( $issuemenus[$k]['issuemenus']);
                    $issuemenus[$k]['count'] =count($issuemenus1);
                    if($v['id']==$fatherId1||$v['id']==$fatherId2||$v['id']==$fatherId3)$issuemenus[$k]['class'] =' open in ';
                    if($v['id']==$type)$issuemenus[$k]['class'] ='active';

                    foreach($issuemenus[$k]['issuemenus'] as $k1=>$v1){
                        $mapcontent['lv']=2;
//                    if($groupid == 11){
//                        $mapcontent['group_id']=11;
//                    }elseif ($groupid == 10){
//                        $mapcontent['has_jw']=1;
//                    }
                        $mapcontent['pid']=$v1['id'];
//                    $mapcontent['project_category'] = 0;
                        $issuemenus2 = $category->where($mapcontent)->field(true)->order('sort asc')->select();
                        $issuemenus[$k]['issuemenus'][$k1]['issuemenus'] =$issuemenus2;
//                    dump($issuemenus[$k]['issuemenus'][$k1]['issuemenus']);
                        $issuemenus[$k]['issuemenus'][$k1]['count'] = count($issuemenus2);
                        if($v1['id']==$fatherId1||$v1['id']==$fatherId2||$v1['id']==$fatherId3){
                            $issuemenus[$k]['issuemenus'][$k1]['class'] =' open in ';
                        }
                        if($v1['id']==$type)$issuemenus[$k]['issuemenus'][$k1]['class'] ='active';
                        foreach($issuemenus[$k]['issuemenus'][$k1]['issuemenus'] as $k2=>$v2){
                            $mapcontent['lv']=3;
//                        if($groupid == 11){
//                            $mapcontent['group_id']=11;
//                        }elseif ($groupid == 10){
//                            $mapcontent['has_jw']=1;
//                        }
                            $mapcontent['pid']=$v2['id'];
//                        $mapcontent['project_category'] = 0;
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

//            dump($issuemenus);
//            die;
//            dump($issuemenus[0]['issuemenus'][1]);exit;
                $this->assign('menutree',1);
                $this->assign('issuemenus1', $issuemenus);
            }

            $content = M('Issue_content');
            $data = $content->where(array("id" => $id))->find();
            $builder = new AdminConfigBuilder();
            if($type){
                $data['issue_id'] = $type;
            }elseif(session('issue_id_temp') && !$data['issue_id']){
                $data['issue_id'] = session('issue_id_temp');
                session('issue_id_temp','');

            }
            if($groupid == 3){
                $mapcontent2['group_id'] = array('like','%3%');
            }elseif ($groupid == 10){
                $mapcontent2['group_id'] = array('like','%10%');
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
            if($type == 82){   //党建活动预约管理
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id','分类编号')

                    ->keyText('title', '标题')
                    ->keyText('host','组织单位')
//                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('sort', '排序','数值越大越靠前')
                    ->keyBDMap('addr|lat|lng','活动地址')
                    ->keyText('teacher','授课老师')
                    ->keyTime('time', '活动开始时间')
                    ->keyText('duration', "活动持续时间")
                    ->keyText('num','活动人数')
                    ->keyTextArea('content', "内容")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();
            }elseif ($type == 84){ //志愿者报名管理
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id','分类编号')

                    ->keyText('title', '标题')
                    ->keyText('host','实施队伍')
//                    ->keySelect("issue_id", "分类", "", $options)

                    ->keyText('sort', '排序','数值越大越靠前')
                    ->keyText('gather','活动集合地点')
                    ->keyBDMap('addr|lat|lng','活动地址')
                    ->keyText('service_object','服务对象')
                    ->keyTime('time', '活动开始时间')
                    ->keyText('duration', "活动持续时间")
                    ->keyText('num','计划招募人数')
                    ->keyText('telphone', "联系电话")
                    ->keyTextArea('condition', "志愿者报名条件说明")
                    ->keyTextArea('content', "活动内容")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();
            }elseif ($type == 86){ //场地预约管理
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id','分类编号')

                    ->keyText('title', '标题')
                    ->keyText('host','组织单位')
//                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('sort', '排序','数值越大越靠前')
                    ->keySelect("mode", "预约方式", "决定微信页面显示预约按钮还是预约电话【不选择默认网页】", array(1=>'网络预约',2=>'电话预约'))
                    ->keySingleImage('cover_id','封面图片')
                    ->keyBDMap('addr|lat|lng','场地地址')
                    ->keyTime('time', '时间')
                    ->keyText('num','最大容纳人数')
                    ->keyTextArea('content', "内容")
                    ->keyText('telphone', "预约电话")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();

            }elseif ($type == 88){ //党课预约管理
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id','分类编号')
                    ->keyText('title', '党课名称')
                    ->keyText('host','举办单位')
//                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('sort', '排序','数值越大越靠前')
                    ->keyTime('time', '举办时间')
                    ->keyText('teacher','授课老师')
                    ->keyTextArea('teacher_brief','授课老师简介')
                    ->keyBDMap('addr|lat|lng','授课地址')
                    ->keyText('user','联系人')
                    ->keyText('telphone','联系电话')
                    ->keyText('num','教师最大人数')
                    ->keyTextArea('content', "党课内容")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();
            }elseif ($type == 90){ //讲师预约管理
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id','分类编号')
                    ->keyText('title', '讲师姓名')
                    ->keyText('host','讲述所属组织')
//                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('sort', '排序','数值越大越靠前')
                    ->keySingleImage('cover_id','讲师头像')
                    ->keyTextArea('content', "讲师简介")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();
            }elseif ($type == 105){ //优秀党员
                $dzz = $this->cxdzz();
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id','分类编号')
                    ->keySingleImage('cover_id','党员头像')
                    ->keyText('title', '姓名')
                    ->keySelectdzz('host', "党组织",'',$dzz)
                    ->keyText('sort', '排序','数值越大越靠前')
                    ->keyTextArea("content", "简介")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();
            }
            elseif($type == 107||$type == 109||$type == 110||$type == 111){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id','分类编号')
                    ->keyText('title', '标题')
                    ->keySingleImage('cover_id', '封面')
                    ->keyTime('time', '显示时间')
                    ->keyRichText('content', '内容','')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();


            }
            else{
                echo '<script>';
                echo"location.href='/admin/cx/content/type/".$type."'";
                echo '</script>';
            }
        }
    }

    /*
     * 设置状态
     */

    public function setWildcardStatus(){
        $ids = I('ids');
        $status = I('get.status', 0, 'intval');
        $builder = new AdminListBuilder();
        $builder->doSetStatus('Issue_content', $ids, $status);
    }

    /**
     * 还原
     */
    public function setEditStatus(){
        $ids = I('ids');
        $status = 0;
        $builder = new AdminListBuilder();
        $builder->doSetStatus('Issue_content', $ids, $status);
    }

    /**
     * 删除
     */
    public function setDeltteStatus(){
        $ids = I('ids');
        $status = -2;
        $builder = new AdminListBuilder();
        $builder->doSetStatus('Issue_content', $ids, $status);
    }

    /*
     * 查看报名人员*/
    public function sign($page = 1, $r = 10,$type=82,$title=1,$content_id=null){

        $this->leftlist($type);  //左侧菜单栏
        $builder = new AdminListBuilder();
        $map['status'] = array('egt',1);
        $sta = array(
            100=>'全部',
            -1 => '审核未通过',
            0 => '待审核',
            1 => '审核通过',
            3 => '用户已取消',
        );
        //按状态查询
        $stt = I('types');
        if($stt!=''&&$stt!=100){
            $map['state'] = $stt;
        }else{
            $stt =100;
        }
        $this->assign('sta',$sta); //状态显示
        $this->assign('stt',$stt);//当前状态
        switch ($type){
            case 82:
                $map['content_id'] = $content_id;
                $count =$this->bespoke->where($map)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = $this->bespoke->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
                foreach ($data as $k=>&$v) {
                    $v['status_var'] = $this->status[$v['status']];
                    $v['state_var'] = $this->state[$v['state']];
                    $v['source_var'] = $this->source[$v['source']];
                    $v['company_var'] = $this->company[$v['type']];
                }
                $tit = $this->issue_content->where(array('id'=>$content_id))->find();
                $this->assign('tit',$tit);  //活动信息
                $this->assign('data',$data); //报名信息
                $this->assign('pagination',$show);
                $this->display('bespoke');
                break;
            case 84: //志愿者报名管理
                $map['content_id'] = $content_id;
                $count = $this->volunteer->where($map)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = $this->volunteer->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
                foreach ($data as $k=>&$v) {
                    $v['status_var'] = $this->status[$v['status']];
                    $v['state_var'] = $this->state[$v['state']];
                    $v['source_var'] = $this->source[$v['source']];
                    $v['company_var'] = $this->company[$v['type']];
                }
                $tit = $this->issue_content->where(array('id'=>$content_id))->find();
                $count = $this->volunteer->where(array('status'=>1,'state'=>1))->sum('bespoke_num');
                $this->assign('tit',$tit);  //活动信息
                $this->assign('data',$data); //报名信息
                $this->assign('pagination',$show); //分页
                $this->display('volunteer');

                break;
            case 86: //场地预约管理
                $map['content_id'] = $content_id;
                $count = $this->field->where($map)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = $this->field->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
                foreach ($data as $k=>&$v) {
                    $v['status_var'] = $this->status[$v['status']];
                    $v['state_var'] = $this->state[$v['state']];
                    $v['source_var'] = $this->source[$v['source']];
                    $v['company_var'] = $this->company[$v['type']];
                }
                $tit = $this->issue_content->where(array('id'=>$content_id))->find();
                $count = $this->field->where(array('status'=>1,'state'=>1))->sum('bespoke_num');
                $this->assign('tit',$tit);  //活动信息
                $this->assign('data',$data); //报名信息
                $this->assign('pagination',$show); //分页
                $this->display('field');
                break;
            case 88: //党课预约管理
                $map['content_id'] = $content_id;
                $count = $this->lecture->where($map)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = $this->lecture->where($map)->page($page, $r)->select();
                foreach ($data as $k=>&$v) {
                    $v['status_var'] = $this->status[$v['status']];
                    $v['state_var'] = $this->state[$v['state']];
                    $v['source_var'] = $this->source[$v['source']];
                    $v['company_var'] = $this->company[$v['type']];
                }
                $tit = $this->issue_content->where(array('id'=>$content_id))->find();
                $count = $this->lecture->where(array('status'=>1,'state'=>1))->sum('bespoke_num');
                $this->assign('tit',$tit);  //活动信息
                $this->assign('data',$data); //报名信息
                $this->assign('pagination',$show); //分页
                $this->display('lecture');
                break;
            case 90:
                $map['content_id'] = $content_id;
                $count = $this->tutor->where($map)->count();
                $Page = new Page($count,$r);
                $show = $Page->show();
                $data = $this->tutor->where($map)->page($page, $r)->select();
                foreach ($data as $k=>&$v) {
                    $v['status_var'] = $this->status[$v['status']];
                    $v['state_var'] = $this->state[$v['state']];
                    $v['source_var'] = $this->source[$v['source']];
                    $v['company_var'] = $this->company[$v['type']];
                }
                $tit = $this->issue_content->where(array('id'=>$content_id))->find();
                $count = $this->tutor->where(array('status'=>1,'state'=>1))->sum('bespoke_num');
                $this->assign('tit',$tit);  //活动信息
                $this->assign('data',$data); //报名信息
                $this->assign('pagination',$show); //分页
                $this->display('tutor');
                break;
        }
    }

    /*
     * 预约报名新添编辑*/
    public function signedit(){
        $id = I('id');
        $examine = I('examine');
        $content_id = I('content_id');
        $title = $id ? "编辑" : "新增";

        $issud_id = $this->issue_content->where(array('id'=>$content_id))->getField('issue_id');
        $this->leftlist($issud_id);

        switch($issud_id){
            case 82:
                $tab = $this->bespoke;
                break;
            case 84:
                $tab = $this->volunteer;
                break;
            case 86:
                $tab = $this->field;
                break;
            case 88:
                $tab = $this->lecture;
                break;
            case 90:
                $tab = $this->tutor;
                break;
        }
        if (IS_POST) {
            $data = $_POST;
            switch($issud_id){
                case 86:
                    if($data['start_time'] > $data['end_time']){
                        $this->error("结束时间不能小于开始时间");
                    }
                    break;
                case 88:
                    break;
                case 90:
                    if($data['start_time'] > $data['end_time']){
                        $this->error("结束时间不能小于开始时间");
                    }
                    break;
            }

//            dump($data);exit;
            if ($data["id"]) {
                if ($tab->where(array('id'=>$data["id"]))->save($data) !== false) {
                    $this->success(L('_SUCCESS_UPDATE_'),'/admin/cx/sign/type/'.$issud_id.'/content_id/'.$content_id);
                } else {
                    $this->error(L('_FAIL_UPDATE_'));
                }
            }else {
                $data['source'] = 3;
                $data['cre_time'] = date('Y-m-d H:i:s',time());
                if ($tab->add($data) !== false) {
                    $this->success("添加成功",'/admin/cx/sign/type/'.$issud_id.'/content_id/'.$content_id);
                } else {
                    $this->error("添加失败");
                }
            }

        }else{
            $content = $this->issue_content->where(array('id'=>$content_id))->find();
            if ($id) {
                $map['id'] = $id;
                $map['content_id'] = $content_id;
                $data =$tab->where($map)->find();
            }
            $data['content_id'] = $content_id;
//            dump($data);exit;
            $builder = new  AdminConfigBuilder();
            $builder->title($content['title'].$title);

            $dzz = $this->cxdzz();
            $this->assign('dzz',$dzz);
            switch ($issud_id){
                case 82: //党建活动预约管理
                    $builder->keyId()
                        ->keyId('content_id','活动编号')
                        ->keyText('name', "姓名")
                        ->keyText('phone', "电话")
                        ->keyText('identity', "身份证")
                        ->keySelectdzz('organization', "党组织",'',$dzz)
                        ->keyText('company', "所属单位")
                        ->keyText('bespoke_num', "预约人数")
                        ->keySelect('types',"预约方式",'',array(0=>'请选择',1=> '单位预约',2=>'个人预约'))
                        ->keyText('text', "备注")
                        ->data($data);
                    break;
                case 84: //志愿者报名管理
                    $builder->keyId()
                        ->keyId('content_id','活动编号')
                        ->keyText('name', "姓名")
                        ->keyText('phone', "电话")
                        ->keyText('identity', "身份证")
                        ->keySelectdzz('organization', "党组织",'',$dzz)
                        ->keyText('company', "所属单位")
                        ->keyText('bespoke_num', "预约人数")
                        ->keySelect('types',"预约方式",'',array(0=>'请选择',1=> '单位预约',2=>'个人预约'))
                        ->keyText('text', "备注")
                        ->data($data);
                    break;
                case 86: //场地预约管理
                    $builder->keyId()
                        ->keyId('content_id','场地编号')
                        ->keyText('name', "姓名")
                        ->keyText('phone', "电话")
                        ->keyTime('start_time', "开始时间")
                        ->keyTime('end_time', "结束时间")
                        ->keyText('identity', "身份证")
                        ->keySelectdzz('organization', "党组织",'',$dzz)

                        ->keyText('company', "所属单位")
                        ->keyText('bespoke_num', "预约人数")
                        ->keySelect('types',"预约方式",'',array(0=>'请选择',1=> '单位预约',2=>'个人预约'))
                        ->keyText('text', "备注")
                        ->data($data);
                    break;
                case 88: //党课预约管理
                    $builder->keyId()
                        ->keyId('content_id','党课编号')
                        ->keyText('name', "姓名")
                        ->keyText('phone', "电话")
                        ->keyText('identity', "身份证")
                        ->keySelectdzz('organization', "党组织",'',$dzz)

                        ->keyText('company', "所属单位")
                        ->keyText('bespoke_num', "预约人数")
                        ->keySelect('types',"预约方式",'',array(0=>'请选择',1=> '单位预约',2=>'个人预约'))
                        ->keyText('text', "备注")
                        ->data($data);
                    break;
                case 90: //讲师预约管理
                    $builder->keyId()
                        ->keyId('content_id','场地编号')
                        ->keyText('name', "姓名")
                        ->keyText('phone', "电话")
                        ->keyTime('start_time', "开始时间")
                        ->keyTime('end_time', "结束时间")
                        ->keyText('identity', "身份证")
                        ->keySelectdzz('organization', "党组织",'',$dzz)
                        ->keyText('company', "所属单位")
                        ->keyText('bespoke_num', "预约人数")
                        ->keySelect('types',"预约方式",'',array(0=>'请选择',1=> '单位预约',2=>'个人预约'))
                        ->keyText('text', "备注")
                        ->data($data);
                    break;
            }
            if($examine == 1){
                $builder->buttonSubmit()
                    ->buttonBack();
            }elseif($examine == 2){
                $builder->keySelect('state',"审批",'',$this->state)
                    ->buttonSubmit()
                    ->buttonBack();
            }else{
                $builder
                    ->buttonBack();
            }
            $builder->display();


//            $this->display();

        }


    }

    /*
     * 预约报名删除报名*/
    public function signdel(){
        $type = I('type');
        switch ($type){
            case 82 : //党建活动预约管理
                $table = 'bespoke';
                break;
            case 84: //志愿者报名管理
                $table = 'volunteer';
                break;
            case 86 : //场地预约管理
                $table = 'field';
                break;
            case 88 : //党课预约管理
                $table = 'lecture';
                break;
            case 90 : //讲师预约管理
                $table = 'bespoke';
                break;
        }
        $ids = I('ids');
        $status = I('get.status', 0, 'intval');
        $builder = new AdminListBuilder();
        $builder->doSetStatus($table, $ids, $status);
    }


    /*
     * 微心愿管理新增
     * 众筹新增管理*/
    public function wxyedit(){
        $id = I('id');
        $examine = I('examine');
        $type = $_REQUEST['type'];

        $this->leftlist($type);

        $title = $id ? "编辑" : "新增";
        $sta = array(
            100=>'全部',
            -1 => '审核未通过',
            0 => '待审核',
            1 => '审核通过',
            2 => '已认领',
            3 => '已完成',
            4=> '用户已取消',
        );
        $obj = array(
            1=>'个人',
            2=>'团体',
            3=>'群众',
        );
        $form = array(
            1 => '物质',
            2 => '精神',
        );
        switch($type){
            case 92:
                $tab = $this->wish;
                break;
            case 93:
                $tab = $this->wishapply;
                break;
            case 94:
                $tab = $this->wishevaluate;
                break;
            case 96:
                $tab = $this->zhch;
                break;
            case 97:
                $tab = $this->zhchapply;
                break;
            case 98:
                $tab = $this->zhchevaluate;
                break;
            case 99:
                $tab = $this->direct;
                break;
            case 100:
                $tab = $this->directapply;
                break;
            case 101:
                $tab = $this->directevaluate;
                break;
        }
        $map['status'] = 1;
        if (IS_POST) {
            $data = $_POST;
            if ($data["id"]) {
                if ($tab->where(array('id'=>$data["id"]))->save($data) !== false) {
                    $this->success(L('_SUCCESS_UPDATE_'),'/admin/cx/content/type/'.$type);
                } else {
                    $this->error(L('_FAIL_UPDATE_'));
                }
            }else {
                $data['source'] = 3;
                $data['cre_time'] = date('Y-m-d H:i:s',time());
                if ($tab->add($data) !== false) {
                    $this->success("添加成功",'/admin/cx/content/type/'.$type);
                } else {
                    $this->error("添加失败");
                }
            }

        }else{
            $map['id']=$id;
            $data = $tab->where($map)->find();
            $data['issue_id'] = $type;
            $builder = new AdminConfigBuilder();
            $dzz = $this->cxdzz();
            $dzz = array_merge(array(0=>array('NAME'=>'未选择')),$dzz);

            $this->assign('dzz',$dzz);
            switch ($type){
                case 92: //微心愿申报管理
                    $builder->title('微心愿'.$title)
                        ->keyId()
//                        ->keyHidden('issue_id','')
                        ->keyText('title','心愿')
                        ->keySingleImage('img','图片')
                        ->keyText('username', "姓名")
                        ->keyText('telephone', "电话")
                        ->keyText('identity', "身份证")
                        ->keyText('address', "地址")
//                        ->keySelectdzz('organization', "党组织",'',$dzz)
                        ->keyTime('start_time', "用户选择时间")
                        ->keySelect('obj',"心愿对象",'',$obj)
                        ->keySelect('form',"心愿形式",'',$form)
                        ->keySelect('party', "党员",'',$this->party)
                        ->keyTextArea('content', "小故事")
                        ->data($data);
                    break;
                case 93: //微心愿认领
                    $da = $this->wish->where(array('id'=>$data['wish_id']))->find();
                    $wishs[$da['id']]= $da['title'];
                    $mapwish['status'] =1;
                    $mapwish['state'] =1;
                    $wish = $this->wish->where($mapwish)->select();  //当前可用微心愿
                    $wish =array_unique($wish);
                    foreach ($wish as $k=>$v){
                        $wishs[$v['id']]=$v['title'];
                    }
                    $builder->title('微心愿认领'.$title)
                        ->keyId()
//                        ->keyHidden('issue_id','')
                        ->keySelect('wish_id','申请心愿','',$wishs)
                        ->keyText('name','姓名')
                        ->keyText('telephone','电话')
                        ->keyText('company', "单位")
                        ->keySelect('types', "申请类型",'',$this->company)
                        ->keyText('identity', "身份证")
                        ->keyText('address', "地址")
                        ->keySelect('party', "党员",'',$this->party)
                        ->keyTextArea('content', "备注")
                        ->data($data);
                    break;
                case 94: //微心愿评价
                    $pj = array(
                        1=>'非常满意',
                        2=>'满意',
                        3=>'一般',
                        4=>'不够满意',
                    );
                    $builder->title('微心愿评价'.$title)
                        ->keySelect('pj', "评价",'',$pj)
                        ->keyTextArea('content', "评价")
                        ->data($data);
                    break;
                case 96: //众筹服务申报管理
                    $typ = array(
                        1=>'金钱众筹',
                        2=>'人员众筹',
                    );
                    unset($sta[2]);
                    $builder->title('项目众筹'.$title)
                        ->keyId()
//                        ->keyHidden('issue_id','')
                        ->keyText('title','标题')
                        ->keySelect('classif','众筹类别','',$typ)
                        ->keyText('money','金额')
                        ->keyText('count','人员')
                        ->keySingleImage('img','图片')
                        ->keyTime('state_time','开始参与时间')
                        ->keyTime('end_time','终止参与时间')
                        ->keyText('applyname','申请人')
                        ->keyText('applytelephone','电话')
                        ->keyText('identity', "身份证")
                        ->keyText('address', "地址")
                        ->keySelect('party', "党员",'',$this->party)
                        ->keyTextArea('content', "备注")
                        ->data($data);
                    break;
                case 97: //项目众筹参与
                    $sta[2] = '已联系';
                    $da = $this->zhch->where(array('id'=>$data['zhch_id']))->find();
                    $wishs[$da['id']]= $da['title'];
                    $mapwish['status'] =1;
                    $mapwish['state'] =1;
                    $wish = $this->zhch->where($mapwish)->select();  //当前可用微心愿
                    $wish =array_unique($wish);
                    foreach ($wish as $k=>$v){
                        $wishs[$v['id']]=$v['title'];
                    }
                    $builder->title('项目众筹'.$title)
                        ->keyId()
//                        ->keyHidden('issue_id','')
                        ->keySelect('zhch_id','申请心愿','',$wishs)
                        ->keySelect('types','参与方式','',$this->company)
                        ->keyText('money','意向金额')
                        ->keyText('count','申请人数')
                        ->keyText('company','单位')
                        ->keyText('name','姓名')
                        ->keyText('telephone','电话')
                        ->keyText('identity', "身份证")
                        ->keySelect('party', "党员",'',$this->party)
                        ->keyTextArea('content', "备注")
                        ->data($data);

                    break;
                case 98:
                    $pj = array(
                        1=>'非常满意',
                        2=>'满意',
                        3=>'一般',
                        4=>'不够满意',
                    );
                    $builder->title('众筹评价'.$title)
                        ->keySelect('pj', "评价",'',$pj)
                        ->keyTextArea('content', "评价")
                        ->data($data);
                    break;
                case 99:
                    unset($sta[2]);
                    unset($sta[3]);
                    unset($sta[4]);
                    $builder->title('项目直通车'.$title)
                        ->keyText('title','标题')
                        ->keyTextArea('desc','简介')
                        ->keyMultiImage('img','图片')
                        ->data($data);
                    break;
                case 100:
                    unset($sta[2]);
                    unset($sta[3]);
                    $da = $this->direct->where(array('id'=>$data['direct_id']))->find();
                    $wishs[$da['id']]= $da['title'];
                    $mapwish['status'] =1;
                    $mapwish['state'] =1;
                    $wish = $this->direct->where($mapwish)->select();  //当前可用微心愿
                    $wish =array_unique($wish);
                    foreach ($wish as $k=>$v){
                        $wishs[$v['id']]=$v['title'];
                    }
                    $builder->title('项目直通车报名'.$title)
                        ->keySelect('direct_id','申请心愿','',$wishs)
                        ->keySelect('types','参与方式','',$this->company)
                        ->keyText('company','单位')
                        ->keyText('name','姓名')
                        ->keyText('telephone','电话')
                        ->keyText('identity', "身份证")
                        ->keySelect('party', "党员",'',$this->party)
                        ->keyTextArea('content', "备注")
                        ->data($data);
                    break;
                case 101:
                    $pj = array(
                        1=>'非常满意',
                        2=>'满意',
                        3=>'一般',
                        4=>'不够满意',
                    );
                    $builder->title('项目直通车评价'.$title)
                        ->keySelect('pj', "评价",'',$pj)
                        ->keyTextArea('content', "评价")
                        ->data($data);
                    break;
            }
            if($examine == 1){
                $builder->buttonSubmit()
                    ->buttonBack();
            }elseif($examine == 2){
                $builder->keySelect('state',"审批",'',$sta)
                    ->buttonSubmit()
                    ->buttonBack();
            }else{
                $builder
                    ->buttonBack();
            }
            $builder->display();

        }


    }

    /*
     * 微心愿删除*/
    public function wxydel(){
        $type = $_REQUEST['type'];
        switch ($type){
            case 92 : //党建活动预约管理
                $table = 'sign_wish';
                break;
            case 93: //志愿者报名管理
                $table = 'sign_wish_apply';
                break;
            case 94 : //场地预约管理
                $table = 'sign_wish_evaluate';
                break;
            case 96 : //场地预约管理
                $table = 'sign_zhch';
                break;
            case 97 : //场地预约管理
                $table = 'sign_zhch_apply';
                break;
            case 98 : //场地预约管理
                $table = 'sign_zhch_evaluate';
                break;
            case 99 : //场地预约管理
                $table = 'sign_direct';
                break;
            case 100 : //场地预约管理
                $table = 'sign_direct_apply';
                break;
            case 101 : //场地预约管理
                $table = 'sign_direct_evaluate';
                break;
            case 104 : //场地预约管理
                $table = 'sign_heart';
                break;
        }
        $status = I('get.status', 0, 'intval');
        $state = I('get.state', 0, 'intval');
        $ids = I('id');
        $builder = new AdminListBuilder();
        if($status){
            $builder->doSetStatus($table, $ids, $status);
        }elseif ($state){
            $builder->doSetState($table, $ids, $state);

        }


    }


    /*左侧菜单栏*/
    private function leftlist($type){
        $category = M('issue');
        $groupid = session('GroupId');
        $user = session('user_auth');
        $uid = $user['uid'];


        if($type){
            $fatherId1 =  $category->where('id='.$type)->getField('pid');

            $fatherId2 =  $category->where('id='.$fatherId1)->getField('pid');

            $fatherId3 =  $category->where('id='.$fatherId2)->getField('pid');

            $this->assign('type',$type);
        }

        $this->assign('actionname','content');
        $this->assign('accontroller',CONTROLLER_NAME);



        if(CONTROLLER_NAME =='Cx'){
            $mapcontent['status']=1;
            if($groupid == 3){
                $mapcontent['group_id'] = array('like','%3%');
            }elseif ($groupid == 10){
                $mapcontent['group_id'] = array('like','%10%');
            }
            $mapcontent['lv']=0;
//            $mapcontent['project_category'] = 0;
            $issuemenus = $category->where($mapcontent)->field(true)->order('sort asc')->select();
//            $aa = M()->getLastsql();
//            dump($aa);die;
//            dump( $issuemenus);die;
            foreach($issuemenus as $k=>$v){
                $mapcontent['lv']=1;
//                if($groupid == 11){
//                    $mapcontent['group_id']=11;
//                }elseif ($groupid == 10){
//                    $mapcontent['has_jw']=1;
//                }
                $mapcontent['pid']=$v['id'];
//                $mapcontent['project_category'] = 0;
                $issuemenus1 = $category->where($mapcontent)->field(true)->order('sort asc')->select();
                $issuemenus[$k]['issuemenus'] =$issuemenus1;
//                dump( $issuemenus[$k]['issuemenus']);
                $issuemenus[$k]['count'] =count($issuemenus1);
                if($v['id']==$fatherId1||$v['id']==$fatherId2||$v['id']==$fatherId3)$issuemenus[$k]['class'] =' open in ';
                if($v['id']==$type)$issuemenus[$k]['class'] ='active';

                foreach($issuemenus[$k]['issuemenus'] as $k1=>$v1){
                    $mapcontent['lv']=2;
//                    if($groupid == 11){
//                        $mapcontent['group_id']=11;
//                    }elseif ($groupid == 10){
//                        $mapcontent['has_jw']=1;
//                    }
                    $mapcontent['pid']=$v1['id'];
//                    $mapcontent['project_category'] = 0;
                    $issuemenus2 = $category->where($mapcontent)->field(true)->order('sort asc')->select();
                    $issuemenus[$k]['issuemenus'][$k1]['issuemenus'] =$issuemenus2;
//                    dump($issuemenus[$k]['issuemenus'][$k1]['issuemenus']);
                    $issuemenus[$k]['issuemenus'][$k1]['count'] = count($issuemenus2);
                    if($v1['id']==$fatherId1||$v1['id']==$fatherId2||$v1['id']==$fatherId3){
                        $issuemenus[$k]['issuemenus'][$k1]['class'] =' open in ';
                    }

                    $issuemenus[$k]['issuemenus'][$k1]['url'] ='/admin/cx/content/type/'.$type;


                    if($v1['id']==$type)$issuemenus[$k]['issuemenus'][$k1]['class'] ='active';
                    foreach($issuemenus[$k]['issuemenus'][$k1]['issuemenus'] as $k2=>$v2){
                        $mapcontent['lv']=3;
//                        if($groupid == 11){
//                            $mapcontent['group_id']=11;
//                        }elseif ($groupid == 10){
//                            $mapcontent['has_jw']=1;
//                        }
                        $mapcontent['pid']=$v2['id'];
//                        $mapcontent['project_category'] = 0;
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

//            dump($issuemenus);
//            die;
//            dump($issuemenus[0]['issuemenus'][1]);exit;
            $this->assign('menutree',1);
            $this->assign('issuemenus1', $issuemenus);
//            dump($issuemenus[0]);exit;
        }
    }

    /*
     * 党组织树形数组
     * */
   private function cxdzz(){
        $data = M('ajax_dzz')->field('BRANCH_ID,NAME,PARENT_ID')->order('PARENT_ID asc')->select();
        $datas = $this->make_tree1($data,'BRANCH_ID','PARENT_ID');
        $list = $datas[0]['_child'][0]['_child'][1]['_child'][0]['_child'];
//        $list = json_encode($datas[0]['_child'][0]['_child'][1]['_child'][0]);
//        echo json_encode($list);
        return $list;
    }
    /*
     * 无限极分类*/
    private function make_tree1($list,$pk='id',$pid='pid',$child='_child',$root=0){
        $tree=array();
        foreach($list as $key=> $val){
            if($val[$pid]==$root){
                //获取当前$pid所有子类
                unset($list[$key]);
                if(! empty($list)){
                    $child=$this->make_tree1($list,$pk,$pid,$child,$val[$pk]);
                    if(!empty($child)){
                        $val['_child']=$child;
                    }
                }
                $tree[]=$val;
            }
        }
        return $tree;
    }

    /*
     * 党组织接口*/
    private function branchList(){
        $url = 'http://www.dysfz.gov.cn/apiXC/branchList.do';//党组织
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 1500;
        for ($i=1;$i<10;$i++) {
            $da['START'] = $i;
            $das = json_encode($da);
            $list = httpjson($url,$das);
            foreach ($list['data'] as $k=>&$v) {
                $dzz = M('ajax_dzz')->where(array('BRANCH_ID'=>$v['BRANCH_ID']))->find();
                if($v['PICTURE']){
                    $v['PICTURE'] =  'http://www.dysfz.gov.cn/'.$v['PICTURE'];
                }
                if($dzz['id']){
                    M('ajax_dzz')->where(array('BRANCH_ID'=>$v['BRANCH_ID']))->save($v);
                }else{
                    M('ajax_dzz')->add($v);
                }
            }
            echo $i.'+++++';
        }

    }

    /*
     * 党员接口更新*/
    private function partyList(){
        $url = 'http://www.dysfz.gov.cn/apiXC/partyList.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 1500;

        for ($i = 1 ;$i < 50 ; $i++){
            $da['START'] = $i;
            $das = json_encode($da);
            $list = httpjson($url,$das);
            foreach ($list['data'] as $k=>&$v) {
                $user = M('ajax_user')->where(array('PARTY_ID'=>$v['PARTY_ID']))->find();
                if($v['PHOTO']){
                    $v['PHOTO'] =  'http://www.dysfz.gov.cn/'.$v['PHOTO'];
                }
                if($user['id']){
                    M('ajax_user')->where(array('PARTY_ID'=>$v['PARTY_ID']))->save($v);
                }else{
                    M('ajax_user')->add($v);
                }
            }
        }
    }


    public function activity()
    {
        $url = 'http://www.dysfz.gov.cn/apiXC/activityRecordList.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 200;
        $da['ACTIVITYID'] = 33594;

        $da['START'] = 1;
        $das = json_encode($da);
        $list = httpjson($url,$das);
        dump($list);die;

        for ($i = 1 ;$i < 50 ; $i++){
            $da['START'] = $i;
            $das = json_encode($da);
            $list = httpjson($url,$das);
            foreach ($list['data'] as $k=>&$v) {
                $user = M('ajax_volunteer')->where(array('VOLUNTEER_ID'=>$v['VOLUNTEER_ID']))->find();
                if($v['PICTURE']){
                    $v['PICTURE'] =  'http://www.dysfz.gov.cn/'.$v['PICTURE'];
                }
                if($user['id']){
                    M('ajax_volunteer')->where(array('VOLUNTEER_ID'=>$user['VOLUNTEER_ID']))->save($v);
                }else{
                    M('ajax_volunteer')->add($v);
                }
            }
        }

    }


}