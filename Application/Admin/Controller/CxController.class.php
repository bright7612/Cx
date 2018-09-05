<?php

namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Think\Model;
use Think\Page;

header("Content-Type:text/html; charset=UTF-8");

class CxController extends AdminController
{
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
    private $demand; //项目需求表
    private $gain; //党员心得

    function _initialize()
    {
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
        $this->demand = M('sign_direct_demand');
        $this->gain = M('sign_gain');
    }

    /*
     * 数据状态*/
    private $status = array(
        -1 => '已删除',
        1 => '正常',
        0 => '已禁止',
    );
    /*
     * 审核状态*/
    private $state = array(
        -1 => '审核未通过',
        0 => '待审核',
        1 => '审核通过',
        3 => '用户已取消',
    );
    /*
     * 数据来源*/
    private $source = array(
        1 => '微信申请',
        2 => '大屏申请',
        3 => '后台添加',
    );
    /*
     * 申请方式*/
    private $company = array(
        0 => '单位申请',
        1 => '个人申请',
    );
    private $party = array(
        0 => '非党员',
        1 => '党员',
    );

    public function heart($page = 1, $r = 10)
    {
        //读取列表
        $map['status'] = array('egt', 0);
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
    public function content($page = 1, $r = 10, $type = 82, $title = 1)
    {
        $category = M('issue');
        $groupid = session('GroupId');
        $user = session('user_auth');
        $uid = $user['uid'];

        $this->leftlist($type);
        if ($type == 99 || $type == 100 || $type == 101) {
            $builder = new AdminConfigBuilder();
            $this->assign('issue_id', $type);
            $map['status'] = array('egt', 1);
            $sta = array(
                100 => '全部',
                -1 => '审核未通过',
                0 => '待审核',
                1 => '审核通过',
                4 => '用户已取消',
            );
            if ($type == 99) {
                unset($sta[4]);
            }
            $stt = I('types');
            if ($stt != '' && $stt != 100) {
                $map['state'] = $stt;
            } else {
                $stt = 100;
            }

            $this->assign('sta', $sta); //状态显示
            $this->assign('stt', $stt);//当前状态

            switch ($type) {
                case 99://项目直通车管理
                    $this->assign('title', '项目管理');
                    $count = $this->direct->where($map)->count();
                    $Page = new Page($count, $r);
                    $show = $Page->show();
                    $data = $this->direct->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order('sort desc,id desc')->select();
                    foreach ($data as $k => &$v) {
                        $v['img2'] = explode(',', $v['img']);
                        foreach ($v['img2'] as $k2 => $v2) {
                            $v['img_var'][] = pic($v2);
                        }
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                    }
//                    dump($data);exit;
                    $this->assign('data', $data); //申报信息
                    $this->assign('pagination', $show);
                    //dump($data);exit;
                    $this->display('direct');
                    break;
                case 100: //项目直通车参与管理
                    $this->assign('title', '项目报名管理');
                    $count = $this->directapply->where($map)->count();
                    $Page = new Page($count, $r);
                    $show = $Page->show();
                    $data = $this->directapply->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->select();
                    foreach ($data as $k => &$v) {
                        $v['direct_id_var'] = $this->direct->where(array('id' => $v['direct_id']))->getField('title');
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['source_var'] = $this->source[$v['source']];
                        $v['party_var'] = $this->party[$v['party']];
                        $v['types_var'] = $this->company[$v['types']];
                    }
                    $this->assign('data', $data); //报名信息
                    $this->assign('pagination', $show);
                    $this->display('directapply');
                    break;
                case 101: //项目直通车评价
                    $pj = array(
                        1 => '非常满意',
                        2 => '满意',
                        3 => '一般',
                        4 => '不够满意',
                    );
                    $this->assign('title', '众筹评价管理');
                    $count = $this->directevaluate->where($map)->count();
                    $Page = new Page($count, $r);
                    $show = $Page->show();
                    $data = $this->directevaluate->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->select();
                    foreach ($data as $k => &$v) {
                        $v['direct_id_var'] = $this->direct->where(array('id' => $v['direct_id']))->field('title')->find();
                        $v['openid_var'] = $this->wxuser->where(array('openid' => $v['openid']))->field('nickname,headimgurl')->find();
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['pj_var'] = $pj[$v['pj']];
                    }
                    $this->assign('data', $data); //报名信息
                    $this->assign('pagination', $show);
                    $this->display('directevaluate');
                    break;
            }
        } //项目直通车
        elseif ($type == 96 || $type == 97 || $type == 98) {
            $builder = new AdminConfigBuilder();
            $this->assign('issue_id', $type);
            $map['status'] = array('egt', 1);
            $sta = array(
                100 => '全部',
                -1 => '审核未通过',
                0 => '待审核',
                1 => '审核通过',
                2 => '已认领',
                3 => '已完成',
                4 => '用户已取消',
            );
            $typ = array(
                1 => '金钱众筹',
                2 => '人员众筹',
            );
            if ($type == 96) {
                unset($sta[2]);
            }
            if ($type == 97) {
                unset($sta['2']);
                unset($sta['3']);

            }
            if ($type == 98) {
                unset($sta[2]);
                unset($sta[3]);
            }
            $stt = I('types');
            if ($stt != '' && $stt != 100) {
                $map['state'] = $stt;
            } else {
                $stt = 100;
            }

            $this->assign('sta', $sta); //状态显示
            $this->assign('stt', $stt);//当前状态


            switch ($type) {
                case 96://众筹服务申报管理
                    $this->assign('title', '众筹服务申报管理');
                    $count = $this->zhch->where($map)->count();
                    $Page = new Page($count, $r);
                    $show = $Page->show();
                    $data = $this->zhch->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order('sort desc,id desc')->select();
                    foreach ($data as $k => &$v) {
                        $v['img_var'] = pic($v['img']);
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['source_var'] = $this->source[$v['source']];
                        $v['party_var'] = $this->party[$v['party']];
                        $v['type_var'] = $typ[$v['classif']];
                    }
//                    dump($data);exit;
                    $this->assign('data', $data); //申报信息
                    $this->assign('pagination', $show);
                    $this->display('zhch');
                    break;
                case 97: //众筹服务参与管理
                    $this->assign('title', '众筹服务参与管理');
                    $count = $this->zhchapply->where($map)->count();
                    $Page = new Page($count, $r);
                    $show = $Page->show();
                    $data = $this->zhchapply->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->select();
                    foreach ($data as $k => &$v) {
                        $v['zhch_id_var'] = $this->zhch->where(array('id' => $v['zhch_id']))->getField('title');
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['source_var'] = $this->source[$v['source']];
                        $v['party_var'] = $this->party[$v['party']];
                        $v['types_var'] = $this->company[$v['types']];
                    }
                    $this->assign('data', $data); //报名信息
                    $this->assign('pagination', $show);
                    $this->display('zhchapply');
                    break;
                case 98: //众筹评价管理
                    $pj = array(
                        //1非常满意2满意3一般4不够满意
                        1 => '非常满意',
                        2 => '满意',
                        3 => '一般',
                        4 => '不够满意',
                    );
                    $this->assign('title', '众筹评价管理');
                    $count = $this->zhchevaluate->where($map)->count();
                    $Page = new Page($count, $r);
                    $show = $Page->show();
                    $data = $this->zhchevaluate->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->select();
                    foreach ($data as $k => &$v) {
                        $v['zhch_id_var'] = $this->zhch->where(array('id' => $v['zhch_id']))->field('title')->find();
                        $v['openid_var'] = $this->wxuser->where(array('openid' => $v['openid']))->field('nickname,headimgurl')->find();
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['pj_var'] = $pj[$v['pj']];
                    }
//                    dump($data);exit;
                    $this->assign('data', $data); //报名信息
                    $this->assign('pagination', $show);
                    $this->display('zhchevaluate');
                    break;
            }


        }  //众筹
        elseif ($type == 92 || $type == 93 || $type == 94 ) {
            $builder = new AdminConfigBuilder();
            $this->assign('issue_id', $type);
            $map['status'] = array('egt', 1);
            $sta = array(
                100 => '全部',
                -1 => '审核未通过',
                0 => '待审核',
                1 => '审核通过',
                2 => '已认领',
                3 => '已完成',
                4 => '用户已取消',
            );
            $obj = array(
                1 => '个人',
                2 => '团体',
                3 => '群众',
            );
            $form = array(
                1 => '物质',
                2 => '精神',
            );
            //按状态查询
            $stt = I('types');
            if ($stt != '' && $stt != 100) {
                $map['state'] = $stt;
            } else {
                $stt = 100;
            }
            if ($type == 93) {
                unset($sta[2]);
                unset($sta[3]);
            }
            if ($type == 94) {
                unset($sta[2]);
                unset($sta[3]);
                unset($sta[4]);
            }
            $this->assign('sta', $sta); //状态显示
            $this->assign('stt', $stt);//当前状态
            switch ($type) {
                case 92://微心愿申报管理
                    $this->state[2] = '已认领';
                    $this->assign('title', '微心愿申报管理');
                    $count = $this->wish->where($map)->count();
                    $Page = new Page($count, $r);
                    $show = $Page->show();
                    $data = $this->wish->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->select();
                    foreach ($data as $k => &$v) {
                        $v['img_var'] = pic($v['img']);
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $sta[$v['state']];
                        $v['source_var'] = $this->source[$v['source']];
                        $v['obj_var'] = $obj[$v['obj']];
                        $v['form_var'] = $form[$v['form']];
                        $v['party_var'] = $this->party[$v['party']];
                    }
                    $this->assign('data', $data); //报名信息
                    $this->assign('pagination', $show);
                    $this->display('wxylist');
                    break;
                case 93: //微心愿认领管理
                    $this->assign('title', '微心愿认领管理');
                    $count = $this->wishapply->where($map)->count();
                    $Page = new Page($count, $r);
                    $show = $Page->show();
                    $data = $this->wishapply->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->select();
                    foreach ($data as $k => &$v) {
                        $v['wish_id_var'] = $this->wish->where(array('id' => $v['wish_id']))->getField('title');
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['source_var'] = $this->source[$v['source']];
                        $v['party_var'] = $this->party[$v['party']];
                        $v['company_var'] = $this->company[$v['party']];
                        $v['types_var'] = $this->company[$v['types']];
                    }
                    $this->assign('data', $data); //报名信息
                    $this->assign('pagination', $show);
                    $this->display('wxyapply');
                    break;
                case 94: //微心愿评价管理
                    $pj = array(
                        //1非常满意2满意3一般4不够满意
                        1 => '非常满意',
                        2 => '满意',
                        3 => '一般',
                        4 => '不够满意',
                    );
                    $this->assign('title', '微心愿评价管理');
                    $count = $this->wishevaluate->where($map)->count();
                    $Page = new Page($count, $r);
                    $show = $Page->show();
                    $data = $this->wishevaluate->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->select();
                    foreach ($data as $k => &$v) {
                        $v['wish_id_var'] = $this->wish->where(array('id' => $v['wish_id']))->field('title')->find();
                        $v['openid_var'] = $this->wxuser->where(array('openid' => $v['openid']))->field('nickname,headimgurl')->find();
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['pj_var'] = $pj[$v['pj']];

                    }
//                    dump($data);exit;
                    $this->assign('data', $data); //报名信息
                    $this->assign('pagination', $show);
                    $this->display('wxyevaluate');
                    break;
            }
        } //微心愿
        elseif ($type == 104 || $type == 114 || $type == 116) {
            $builder = new AdminConfigBuilder();
            $this->assign('issue_id', $type);
            $map['status'] = array('egt', 1);
            $sta = array(
                100 => '全部',
                -1 => '审核未通过',
                0 => '待审核',
                1 => '审核通过',
                4 => '用户已取消',
            );

            $stt = I('types');
            if ($stt != '' && $stt != 100) {
                $map['state'] = $stt;
            } else {
                $stt = 100;
            }

            $this->assign('sta', $sta); //状态显示
            $this->assign('stt', $stt);//当前状态`
            switch ($type) {
                case 104://党员心声
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
                case 114://微心愿申报管理
                    $tab = M('sign_direct_demand');
                    $this->assign('title', '项目需求');
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
                    $this->display('demand');
                    break;
                case 116://党员心得
                    $tab = M('sign_gain');
                    $this->assign('title', '党员心得');
                    $count = $tab->where($map)->count();
                    $Page = new Page($count, $r);
                    $show = $Page->show();
                    $data = $tab->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->select();
                    foreach ($data as $k => &$v) {
                        $v['status_var'] = $this->status[$v['status']];
                        $v['state_var'] = $this->state[$v['state']];
                        $v['content'] = mb_substr($v['content'], 0, 20, 'utf-8');
                    }
                    $this->assign('data', $data); //报名信息
                    $this->assign('pagination', $show);
                    $this->display('gain');
                    break;
            }
        }  //党员心声
        elseif ($type == 107 || $type == 109 || $type == 110 || $type == 111 || $type == 113 || $type == 119 || $type == 121 || $type == 123 || $type == 124 || $type == 126 ) {
            //读取列表
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
            $map['status'] = 1;
            if ($type) {
                $map['issue_id'] = $type;
            }

            $model = M('Issue_content');
            $map['status'] = $title;
            $map['uid'] = $uid;
            $list = $model->where($map)->page($page, $r)->order("sort desc,id desc")->select();
            foreach ($list as $k => $v) {
                $issueInfo = $category->where('id = ' . $v['issue_id'])->find();
                $list[$k]['issue_name'] = $issueInfo['title'];
                $list[$k]['link'] = $v['title'];
            }
            unset($li);
            $totalCount = $model->where($map)->count();
            //显示页面
            $builder = new AdminListBuilder();
            $attr['class'] = 'btn ajax-post';
            $attr['target-form'] = 'ids';

            header("Content-type:text/html;charset=UTF-8");
            if ($title == 1) $titlename = '已发布列表';
            if ($title == 0) $titlename = '编辑中列表';
            if ($title == -1) $titlename = '已删除列表';

            $builder->title($titlename);
            $builder->setStatusUrl(U('setWildcardStatus'));
            if ($title != 1) {
                $builder->buttonEnable('', L('_AUDIT_SUCCESS_'));
            }

            if ($title == -1) {
                $builder->setStatusUrl(U('setEditStatus'));
                $builder->buttonRestore();
                $builder->setStatusUrl(U('setDeltteStatus'));
                $builder->buttonDelete();
            }

            if ($title != -1) {
                $builder->buttonDelete();
            }
            //        dump($list);exit;
            $builder->button('新增', array('class' => 'btn btn-success', 'href' => U('admin/Cx/addcontent?type=' . $type)));
            $builder->button('已发布列表', array('class' => 'btn btn-info', 'href' => U('admin/Cx/content?title=1&type=' . $type)))
                ->button('编辑中列表', array('class' => 'btn btn-warning', 'href' => U('admin/Cx/content?title=0&type=' . $type)))
                ->button('已删除列表', array('class' => 'btn btn-danger', 'href' => U('admin/Cx/content?title=-1&type=' . $type)));
            $builder->keyId()
                ->keyText('link', '标题')
                ->keyImage('cover_id', '封面', array('width' => '25px'))
                ->keyCreateTime('time', '显示时间')
                ->keyDoActionEdit("admin/Cx/addcontent/type/" . $type . '?id=###')
                ->data($list)
                ->pagination($totalCount, $r)
                ->display();
        }
        elseif($type == 108){
            //读取列表
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
            $map['status'] = 1;
            if ($type) {
                $map['issue_id'] = $type;
            }
            $model = M('Issue_content');
            $map['status'] = $title;
            $map['uid'] = $uid;
            $list = $model->where($map)->page($page, $r)->order("sort desc,id desc")->select();
            foreach ($list as $k => &$v) {
                $issueInfo = $category->where('id = ' . $v['issue_id'])->find();
                $list[$k]['issue_name'] = $issueInfo['title'];
                $list[$k]['link'] = $v['title'];
                $v['ewm'] = 'http://cxdj.cmlzjz.com/home/index/qrcode.html?url=http://cxdj.cmlzjz.com/index.php?s=/home/wxindex/public_det/id/'.$v['id'];
            }
            unset($li);
            $totalCount = $model->where($map)->count();
            //显示页面
            $builder = new AdminListBuilder();
            $attr['class'] = 'btn ajax-post';
            $attr['target-form'] = 'ids';

            header("Content-type:text/html;charset=UTF-8");
            if ($title == 1) $titlename = '已发布列表';
            if ($title == 0) $titlename = '编辑中列表';
            if ($title == -1) $titlename = '已删除列表';

            $builder->title($titlename);
            $builder->setStatusUrl(U('setWildcardStatus'));
            if ($title != 1) {
                $builder->buttonEnable('', L('_AUDIT_SUCCESS_'));
            }

            if ($title == -1) {
                $builder->setStatusUrl(U('setEditStatus'));
                $builder->buttonRestore();
                $builder->setStatusUrl(U('setDeltteStatus'));
                $builder->buttonDelete();
            }

            if ($title != -1) {
                $builder->buttonDelete();
            }
            //        dump($list);exit;
            $builder->button('新增', array('class' => 'btn btn-success', 'href' => U('admin/Cx/addcontent?type=' . $type)));
            $builder->button('已发布列表', array('class' => 'btn btn-info', 'href' => U('admin/Cx/content?title=1&type=' . $type)))
                ->button('编辑中列表', array('class' => 'btn btn-warning', 'href' => U('admin/Cx/content?title=0&type=' . $type)))
                ->button('已删除列表', array('class' => 'btn btn-danger', 'href' => U('admin/Cx/content?title=-1&type=' . $type)));
            $builder->keyId()
                ->keyText('link', '标题')
                ->keyImage('cover_id', '封面', array('width' => '25px'))
                ->keyImage('ewm', '二维码', array('width' => '25px'))
                ->keyCreateTime('time', '显示时间')
                ->keyDoActionEdit("admin/Cx/addcontent/type/" . $type . '?id=###')
                ->data($list)
                ->pagination($totalCount, $r)
                ->display();
        }
        elseif ($type == 122) {
            //读取列表
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
            $map['status'] = 1;
            if ($type) {
                $map['issue_id'] = $type;
            }

            $model = M('Issue_content');
            $map['status'] = $title;
            $map['uid'] = $uid;
            $list = $model->where($map)->page($page, $r)->order("sort desc,id desc")->select();
            foreach ($list as $k => $v) {
                $issueInfo = $category->where('id = ' . $v['issue_id'])->find();
                $list[$k]['issue_name'] = $issueInfo['title'];
                $list[$k]['link'] = $v['title'];
            }
            unset($li);
            $totalCount = $model->where($map)->count();
            //显示页面
            $builder = new AdminListBuilder();
            $attr['class'] = 'btn ajax-post';
            $attr['target-form'] = 'ids';

            header("Content-type:text/html;charset=UTF-8");
            if ($title == 1) $titlename = '已发布列表';
            if ($title == 0) $titlename = '编辑中列表';
            if ($title == -1) $titlename = '已删除列表';

            $builder->title($titlename);
            $builder->setStatusUrl(U('setWildcardStatus'));
            if ($title != 1) {
                $builder->buttonEnable('', L('_AUDIT_SUCCESS_'));
            }

            if ($title == -1) {
                $builder->setStatusUrl(U('setEditStatus'));
                $builder->buttonRestore();
                $builder->setStatusUrl(U('setDeltteStatus'));
                $builder->buttonDelete();
            }

            if ($title != -1) {
                $builder->buttonDelete();
            }
            //        dump($list);exit;
            $builder->button('新增', array('class' => 'btn btn-success', 'href' => U('admin/Cx/addcontent?type=' . $type)));
            $builder->button('已发布列表', array('class' => 'btn btn-info', 'href' => U('admin/Cx/content?title=1&type=' . $type)))
                ->button('编辑中列表', array('class' => 'btn btn-warning', 'href' => U('admin/Cx/content?title=0&type=' . $type)))
                ->button('已删除列表', array('class' => 'btn btn-danger', 'href' => U('admin/Cx/content?title=-1&type=' . $type)));
            $builder->keyId()
                ->keyText('link', '题目')
                ->keyText('content', '答案')
                ->keyDoActionEdit("admin/Cx/addcontent/type/" . $type . '?id=###')
                ->data($list)
                ->pagination($totalCount, $r)
                ->display();
        }
        elseif ($type == 118) {
            //读取列表
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
            $map['status'] = 1;
            if ($type) {
                $map['issue_id'] = $type;
            }

            $model = M('Issue_content');
            $map['status'] = $title;
            $map['uid'] = $uid;
            $list = $model->where($map)->page($page, $r)->order("sort desc,id desc")->select();
            foreach ($list as $k => $v) {
                $issueInfo = $category->where('id = ' . $v['issue_id'])->find();
                $list[$k]['issue_name'] = $issueInfo['title'];
                $list[$k]['link'] = $v['title'];
            }
            unset($li);
            $totalCount = $model->where($map)->count();
            //显示页面
            $builder = new AdminListBuilder();
            $attr['class'] = 'btn ajax-post';
            $attr['target-form'] = 'ids';

            header("Content-type:text/html;charset=UTF-8");
            if ($title == 1) $titlename = '已发布列表';
            if ($title == 0) $titlename = '编辑中列表';
            if ($title == -1) $titlename = '已删除列表';

            $builder->title($titlename);
            $builder->setStatusUrl(U('setWildcardStatus'));
            if ($title != 1) {
                $builder->buttonEnable('', L('_AUDIT_SUCCESS_'));
            }

            if ($title == -1) {
                $builder->setStatusUrl(U('setEditStatus'));
                $builder->buttonRestore();
                $builder->setStatusUrl(U('setDeltteStatus'));
                $builder->buttonDelete();
            }

            if ($title != -1) {
                $builder->buttonDelete();
            }
            //        dump($list);exit;
            $builder->button('新增', array('class' => 'btn btn-success', 'href' => U('admin/Cx/addcontent?type=' . $type)));
            $builder->button('已发布列表', array('class' => 'btn btn-info', 'href' => U('admin/Cx/content?title=1&type=' . $type)))
                ->button('编辑中列表', array('class' => 'btn btn-warning', 'href' => U('admin/Cx/content?title=0&type=' . $type)))
                ->button('已删除列表', array('class' => 'btn btn-danger', 'href' => U('admin/Cx/content?title=-1&type=' . $type)));
            $builder->keyId()
                ->keyText('link', '标题')
                ->keyText('addr', '地址')
                ->keyText('teacher', '讲师')
                ->keyImage('cover_id', '封页')
                ->keyTime('time', '上课时间')
                ->keyDoActionEdit("admin/Cx/addcontent/type/" . $type . '?id=###')
                ->data($list)
                ->pagination($totalCount, $r)
                ->display();

        }
        //统计页面
        elseif ($type == 83 || $type == 85 ||$type == 87 ||$type == 89 || $type == 95 ||$type == 85 ||$type == 190||$type == 191){
            $builder = new AdminConfigBuilder();
            $this->assign('issue_id', $type);
            switch ($type) {
                //党建活动预约统计分析
                case 83:
                    $this->assign('title','党建活动预约统计');
                    $map['issue_id'] = 82; //党建活动预约
                    $map['host'] = array('exp', 'IS NOT NULL'); //党建活动预约
                    $timehd = I('time-hd');
                    $timebm = I('time-bm');
                    if($timebm||$timehd){
                        if($timehd){
                            $this->assign('timehd',$timehd);
                            $timehd = explode('-',$timehd);
                            $timehd = $this->mFristAndLast($timehd[0],$timehd[1]);
                            $map['create_time'] = array(array('egt',$timehd['firstday']),array('elt',$timehd['lastday']));
                        }
                        if($timebm){
                            $this->assign('timebm',$timebm);
                            $timebm = explode('-',$timebm);
                            $timebm = $this->mFristAndLast($timebm[0],$timebm[1]);
                            $map2['cre_time'] = array(array('egt',$timebm['firstday']),array('elt',$timebm['lastday']));
                        }
                    }
                    $host = $this->issue_content->where($map)->field('status,id,host')->select();
                    $ho = array();
                    foreach ($host as $h => $t) {
                        if (!$ho[$t['host']]) {
                            $ho[$t['host']]['count'] = 1;
                            if ($t['status'] == 1) {
                                $ho[$t['host']]['start'] = 1;
                            } else {
                                $ho[$t['host']]['stop'] = 1;
                            }
                            //报名信息
                            $map2['content_id'] = $t['id'];
                            $map2['status'] = 1;
                            $besp = $this->bespoke->where($map2)->field('state,id')->select();
                            foreach ($besp as $b => $p) {
                                $ho[$t['host']]['sign_count']++;  //总报名
                                if ($p['state'] == 1) {
                                    $ho[$t['host']]['sign_state']++; //通过人数
                                } elseif ($p['state'] == 3) {
                                    $ho[$t['host']]['sign_cancel']++; //取消人数
                                } elseif ($p['state'] == 0) {
                                    $ho[$t['host']]['sign_wait']++; //待审批人数
                                }
                            }

                        }
                        else {
                            $ho[$t['host']]['count']++;
                            if ($t['status'] == 1) {
                                $ho[$t['host']]['start']++;
                            } else {
                                $ho[$t['host']]['stop']++;
                            }
                            //报名信息
                            $map2['content_id'] = $t['id'];
                            $map2['status'] = 1;
                            $besp = $this->bespoke->where($map2)->field('state,id')->select();
                            foreach ($besp as $b => $p) {
                                $ho[$t['host']]['sign_count']++;  //总报名
                                if ($p['state'] == 1) {
                                    $ho[$t['host']]['sign_state']++; //通过人数
                                } elseif ($p['state'] == 3) {
                                    $ho[$t['host']]['sign_cancel']++; //取消人数
                                } elseif ($p['state'] == 0) {
                                    $ho[$t['host']]['sign_wait']++; //待审批人数
                                }
                            }
                        }
                    }
                    $oh = 0;
                    foreach ($ho as $dii=>&$di){
                        $hoo[$oh]['count'] = (int)$di['count'];
                        $hoo[$oh]['start'] = (int)$di['start'];
                        $hoo[$oh]['stop'] = (int)$di['stop'];
                        $hoo[$oh]['sign_count'] = (int)$di['sign_count'];
                        $hoo[$oh]['sign_state'] = (int)$di['sign_state'];
                        $hoo[$oh]['sign_cancel'] = (int)$di['sign_cancel'];
                        $hoo[$oh]['sign_wait'] = (int)$di['sign_wait'];
                        $hoo[$oh]['host'] = $dii;
                        $oh++;
                    }
                    unset($oh);
                    $count = count($hoo);
                    $pager = new Page($count, $r);
                    $this->assign("pagination", $pager->show());
                    $i = ($page-1)*$r;
                    $p = $page*$r;
                    for ($i;$i<$p;$i++){
                        $data[] = $hoo[$i];
                    }
                    $data = array_filter($data);
                    $this->assign('data',$data);
                    $this->display('bespoke_statistics');
                    break;
                //志愿者活动报名统计
                case 85:
                    $this->assign('title','志愿者活动报名统计');
                    $map['issue_id'] = 84; //志愿者活动
                    $map['host'] = array('exp', 'IS NOT NULL'); //志愿者活动
                    $timehd = I('time-hd');
                    $timebm = I('time-bm');
                    if($timebm||$timehd){
                        if($timehd){
                            $this->assign('timehd',$timehd);
                            $timehd = explode('-',$timehd);
                            $timehd = $this->mFristAndLast($timehd[0],$timehd[1]);
                            $map['create_time'] = array(array('egt',$timehd['firstday']),array('elt',$timehd['lastday']));
                        }
                        if($timebm){
                            $this->assign('timebm',$timebm);
                            $timebm = explode('-',$timebm);
                            $timebm = $this->mFristAndLast($timebm[0],$timebm[1]);
                            $map2['cre_time'] = array(array('egt',$timebm['firstday']),array('elt',$timebm['lastday']));
                        }
                    }
                    $host = $this->issue_content->where($map)->field('status,id,host')->select();
                    $ho = array();
                    foreach ($host as $h => $t) {
                        if (!$ho[$t['host']]) {
                            $ho[$t['host']]['count'] = 1;
                            if ($t['status'] == 1) {
                                $ho[$t['host']]['start'] = 1;
                            } else {
                                $ho[$t['host']]['stop'] = 1;
                            }
                            //报名信息
                            $map2['content_id'] = $t['id'];
                            $map2['status'] = 1;
                            $besp = $this->volunteer->where($map2)->field('state,id')->select();
                            foreach ($besp as $b => $p) {
                                $ho[$t['host']]['sign_count']++;  //总报名
                                if ($p['state'] == 1) {
                                    $ho[$t['host']]['sign_state']++; //通过人数
                                } elseif ($p['state'] == 3) {
                                    $ho[$t['host']]['sign_cancel']++; //取消人数
                                } elseif ($p['state'] == 0) {
                                    $ho[$t['host']]['sign_wait']++; //待审批人数
                                }
                            }

                        }
                        else {
                            $ho[$t['host']]['count']++;
                            if ($t['status'] == 1) {
                                $ho[$t['host']]['start']++;
                            } else {
                                $ho[$t['host']]['stop']++;
                            }
                            //报名信息
                            $map2['content_id'] = $t['id'];
                            $map2['status'] = 1;
                            $besp = $this->volunteer->where($map2)->field('state,id')->select();
                            foreach ($besp as $b => $p) {
                                $ho[$t['host']]['sign_count']++;  //总报名
                                if ($p['state'] == 1) {
                                    $ho[$t['host']]['sign_state']++; //通过人数
                                } elseif ($p['state'] == 3) {
                                    $ho[$t['host']]['sign_cancel']++; //取消人数
                                } elseif ($p['state'] == 0) {
                                    $ho[$t['host']]['sign_wait']++; //待审批人数
                                }
                            }
                        }
                    }
                    $oh = 0;
                    foreach ($ho as $dii=>&$di){
                        $hoo[$oh]['count'] = (int)$di['count'];
                        $hoo[$oh]['start'] = (int)$di['start'];
                        $hoo[$oh]['stop'] = (int)$di['stop'];
                        $hoo[$oh]['sign_count'] = (int)$di['sign_count'];
                        $hoo[$oh]['sign_state'] = (int)$di['sign_state'];
                        $hoo[$oh]['sign_cancel'] = (int)$di['sign_cancel'];
                        $hoo[$oh]['sign_wait'] = (int)$di['sign_wait'];
                        $hoo[$oh]['host'] = $dii;
                        $oh++;
                    }
                    unset($oh);
                    $count = count($hoo);
                    $pager = new Page($count, $r);
                    $this->assign("pagination", $pager->show());
                    $i = ($page-1)*$r;
                    $p = $page*$r;
                    for ($i;$i<$p;$i++){
                        $data[] = $hoo[$i];
                    }
                    $data = array_filter($data);
                    $this->assign('data',$data);
                    $this->display('bespoke_statistics');
                    break;
                //场馆预约统计
                case 87:
                    $this->assign('title','场馆预约统计');
                    $map['issue_id'] = 86; //场馆预约
                    $map['host'] = array('exp', 'IS NOT NULL'); //场馆预约
                    $timehd = I('time-hd');
                    $timebm = I('time-bm');
                    if($timebm||$timehd){
                        if($timehd){
                            $this->assign('timehd',$timehd);
                            $timehd = explode('-',$timehd);
                            $timehd = $this->mFristAndLast($timehd[0],$timehd[1]);
                            $map['create_time'] = array(array('egt',$timehd['firstday']),array('elt',$timehd['lastday']));
                        }
                        if($timebm){
                            $this->assign('timebm',$timebm);
                            $timebm = explode('-',$timebm);
                            $timebm = $this->mFristAndLast($timebm[0],$timebm[1]);
                            $map2['cre_time'] = array(array('egt',$timebm['firstday']),array('elt',$timebm['lastday']));
                        }
                    }
                    $host = $this->issue_content->where($map)->field('status,id,host')->select();
                    $ho = array();
                    foreach ($host as $h => $t) {
                        if (!$ho[$t['host']]) {
                            $ho[$t['host']]['count'] = 1;
                            if ($t['status'] == 1) {
                                $ho[$t['host']]['start'] = 1;
                            } else {
                                $ho[$t['host']]['stop'] = 1;
                            }
                            //报名信息
                            $map2['content_id'] = $t['id'];
                            $map2['status'] = 1;
                            $besp = $this->field->where($map2)->field('state,id')->select();
                            foreach ($besp as $b => $p) {
                                $ho[$t['host']]['sign_count']++;  //总报名
                                if ($p['state'] == 1) {
                                    $ho[$t['host']]['sign_state']++; //通过人数
                                } elseif ($p['state'] == 3) {
                                    $ho[$t['host']]['sign_cancel']++; //取消人数
                                } elseif ($p['state'] == 0) {
                                    $ho[$t['host']]['sign_wait']++; //待审批人数
                                }
                            }

                        }
                        else {
                            $ho[$t['host']]['count']++;
                            if ($t['status'] == 1) {
                                $ho[$t['host']]['start']++;
                            } else {
                                $ho[$t['host']]['stop']++;
                            }
                            //报名信息
                            $map2['content_id'] = $t['id'];
                            $map2['status'] = 1;
                            $besp = $this->field->where($map2)->field('state,id')->select();
                            foreach ($besp as $b => $p) {
                                $ho[$t['host']]['sign_count']++;  //总报名
                                if ($p['state'] == 1) {
                                    $ho[$t['host']]['sign_state']++; //通过人数
                                } elseif ($p['state'] == 3) {
                                    $ho[$t['host']]['sign_cancel']++; //取消人数
                                } elseif ($p['state'] == 0) {
                                    $ho[$t['host']]['sign_wait']++; //待审批人数
                                }
                            }
                        }
                    }
                    $oh = 0;
                    foreach ($ho as $dii=>&$di){
                        $hoo[$oh]['count'] = (int)$di['count'];
                        $hoo[$oh]['start'] = (int)$di['start'];
                        $hoo[$oh]['stop'] = (int)$di['stop'];
                        $hoo[$oh]['sign_count'] = (int)$di['sign_count'];
                        $hoo[$oh]['sign_state'] = (int)$di['sign_state'];
                        $hoo[$oh]['sign_cancel'] = (int)$di['sign_cancel'];
                        $hoo[$oh]['sign_wait'] = (int)$di['sign_wait'];
                        $hoo[$oh]['host'] = $dii;
                        $oh++;
                    }
                    unset($oh);
                    $count = count($hoo);
                    $pager = new Page($count, $r);
                    $this->assign("pagination", $pager->show());
                    $i = ($page-1)*$r;
                    $p = $page*$r;
                    for ($i;$i<$p;$i++){
                        $data[] = $hoo[$i];
                    }
                    $data = array_filter($data);
                    $this->assign('data',$data);
                    $this->display('bespoke_statistics');
                    break;
                //党课预约统计分析
                case 89:
                    $this->assign('title','党课预约统计');
                    $map['issue_id'] = 88; //党课预约
                    $map['host'] = array('exp', 'IS NOT NULL'); //党课预约统计
                    $timehd = I('time-hd');
                    $timebm = I('time-bm');
                    if($timebm||$timehd){
                        if($timehd){
                            $this->assign('timehd',$timehd);
                            $timehd = explode('-',$timehd);
                            $timehd = $this->mFristAndLast($timehd[0],$timehd[1]);
                            $map['create_time'] = array(array('egt',$timehd['firstday']),array('elt',$timehd['lastday']));
                        }
                        if($timebm){
                            $this->assign('timebm',$timebm);
                            $timebm = explode('-',$timebm);
                            $timebm = $this->mFristAndLast($timebm[0],$timebm[1]);
                            $map2['cre_time'] = array(array('egt',$timebm['firstday']),array('elt',$timebm['lastday']));
                        }
                    }
                    $host = $this->issue_content->where($map)->field('status,id,host')->select();
                    $ho = array();
                    foreach ($host as $h => $t) {
                        if (!$ho[$t['host']]) {
                            $ho[$t['host']]['count'] = 1;
                            if ($t['status'] == 1) {
                                $ho[$t['host']]['start'] = 1;
                            } else {
                                $ho[$t['host']]['stop'] = 1;
                            }
                            //报名信息
                            $map2['content_id'] = $t['id'];
                            $map2['status'] = 1;
                            $besp = $this->lecture->where($map2)->field('state,id')->select();
                            foreach ($besp as $b => $p) {
                                $ho[$t['host']]['sign_count']++;  //总报名
                                if ($p['state'] == 1) {
                                    $ho[$t['host']]['sign_state']++; //通过人数
                                } elseif ($p['state'] == 3) {
                                    $ho[$t['host']]['sign_cancel']++; //取消人数
                                } elseif ($p['state'] == 0) {
                                    $ho[$t['host']]['sign_wait']++; //待审批人数
                                }
                            }

                        }
                        else {
                            $ho[$t['host']]['count']++;
                            if ($t['status'] == 1) {
                                $ho[$t['host']]['start']++;
                            } else {
                                $ho[$t['host']]['stop']++;
                            }
                            //报名信息
                            $map2['content_id'] = $t['id'];
                            $map2['status'] = 1;
                            $besp = $this->lecture->where($map2)->field('state,id')->select();
                            foreach ($besp as $b => $p) {
                                $ho[$t['host']]['sign_count']++;  //总报名
                                if ($p['state'] == 1) {
                                    $ho[$t['host']]['sign_state']++; //通过人数
                                } elseif ($p['state'] == 3) {
                                    $ho[$t['host']]['sign_cancel']++; //取消人数
                                } elseif ($p['state'] == 0) {
                                    $ho[$t['host']]['sign_wait']++; //待审批人数
                                }
                            }
                        }
                    }
                    $oh = 0;
                    foreach ($ho as $dii=>&$di){
                        $hoo[$oh]['count'] = (int)$di['count'];
                        $hoo[$oh]['start'] = (int)$di['start'];
                        $hoo[$oh]['stop'] = (int)$di['stop'];
                        $hoo[$oh]['sign_count'] = (int)$di['sign_count'];
                        $hoo[$oh]['sign_state'] = (int)$di['sign_state'];
                        $hoo[$oh]['sign_cancel'] = (int)$di['sign_cancel'];
                        $hoo[$oh]['sign_wait'] = (int)$di['sign_wait'];
                        $hoo[$oh]['host'] = $dii;
                        $oh++;
                    }
                    unset($oh);
                    $count = count($hoo);
                    $pager = new Page($count, $r);
                    $this->assign("pagination", $pager->show());
                    $i = ($page-1)*$r;
                    $p = $page*$r;
                    for ($i;$i<$p;$i++){
                        $data[] = $hoo[$i];
                    }
                    $data = array_filter($data);
                    $this->assign('data',$data);
                    $this->display('bespoke_statistics');
                    break;
                //微心愿数据统计
                case 95:
                    $this->assign('title','微心愿数据统计');
//                $map['issue_id'] = 92; //微心愿数据
                    $map['status'] = 1;

                    $map['organization'] = array('exp', 'IS NOT NULL'); //党课预约统计

                    //时间控制
                    $timehd = I('time-hd');//活动时间
                    $timebm = I('time-bm');//报名时间

                    if($timebm||$timehd){
                        if($timehd){
                            $this->assign('timehd',$timehd);
                            $timehd = explode('-',$timehd);
                            $timehd = $this->mFristAndLast($timehd[0],$timehd[1]);
                            $map['cre_time'] = array(array('egt',$timehd['firstday']),array('elt',$timehd['lastday']));
                        }
                        if($timebm){
                            $this->assign('timebm',$timebm);
                            $timebm = explode('-',$timebm);
                            $timebm = $this->mFristAndLast($timebm[0],$timebm[1]);
                            $map2['cre_time'] = array(array('egt',$timebm['firstday']),array('elt',$timebm['lastday']));
                        }
                    }
                    $host = $this->wish->where($map)->field('state,status,id,organization')->select();

//                dump($host);exit;
                    $ho = array();
                    foreach ($host as $h => $t) {
                        if (!$ho[$t['organization']]) {
                            $ho[$t['organization']]['count'] = 1;
                            if ($t['state'] == 1) {
                                $ho[$t['organization']]['adopt'] = 1;   //通过
                            }
                            else if( $t['state'] == 0 ) {
                                $ho[$t['organization']]['pending'] = 1;  //待审批
                            }
                            else if( $t['state'] == -1 ) {
                                $ho[$t['organization']]['fail'] = 1;  //审批失败
                            }
                            //已认领
                            else if( $t['state'] == 2 ) {
                                $ho[$t['organization']]['claim'] = 1;  //已认领
                            }
                            //已完成
                            else if( $t['state'] == 3 ) {
                                $ho[$t['organization']]['done'] = 1;  //已完成
                            }
                            //用户已取消
                            else if( $t['state'] == 4 ) {
                                $ho[$t['organization']]['cancel'] = 1;  //用户取消
                            }

                            //报名信息
//                        dump($t);
                            $map2['wish_id'] = $t['id'];
                            $map2['status'] = 1;
                            $besp = $this->wishapply->where($map2)->field('state,id,wish_id')->select();
//                        dump($besp);exit;
                            foreach ($besp as $b => $p) {
                                $ho[$t['organization']]['sign_count']++;  //微心愿总申请数
                                if ($p['state'] == 1) {
                                    $ho[$t['organization']]['sign_state']++; //通过人数
                                } elseif ($p['state'] == 3) {
                                    $ho[$t['organization']]['sign_cancel']++; //取消人数
                                } elseif ($p['state'] == 0) {
                                    $ho[$t['organization']]['sign_wait']++; //待审批人数
                                }
                            }

                        }
                        else {
                            $ho[$t['organization']]['count'] ++;
                            if ($t['state'] == 1) {
                                $ho[$t['organization']]['adopt'] ++;   //通过
                            }
                            else if( $t['state'] == 0 ) {
                                $ho[$t['organization']]['pending'] ++;  //待审批
                            }
                            else if( $t['state'] == -1 ) {
                                $ho[$t['organization']]['fail'] ++; //审批失败
                            }
                            //已认领
                            else if( $t['state'] == 2 ) {
                                $ho[$t['organization']]['claim'] ++;  //已认领
                            }
                            //已完成
                            else if( $t['state'] == 3 ) {
                                $ho[$t['organization']]['done'] ++;  //已完成
                            }
                            //用户已取消
                            else if( $t['state'] == 4 ) {
                                $ho[$t['organization']]['cancel'] ++;  //用户取消
                            }
                            //报名信息
                            $map2['wish_id'] = $t['id'];
                            $map2['status'] = 1;
                            $besp = $this->lecture->where($map2)->field('state,id')->select();
                            foreach ($besp as $b => $p) {
                                $ho[$t['organization']]['sign_count']++;  //总报名
                                if ($p['state'] == 1) {
                                    $ho[$t['organization']]['sign_state']++; //通过人数
                                } elseif ($p['state'] == 3) {
                                    $ho[$t['organization']]['sign_cancel']++; //取消人数
                                } elseif ($p['state'] == 0) {
                                    $ho[$t['organization']]['sign_wait']++; //待审批人数
                                }
                            }
                        }
                    }
                    $oh = 0;

                    foreach ($ho as $dii=>&$di){
                        $hoo[$oh]['count'] = (int)$di['count'];
                        $hoo[$oh]['adopt'] = (int)$di['adopt'];
                        $hoo[$oh]['pending'] = (int)$di['pending'];
                        $hoo[$oh]['fail'] = (int)$di['fail'];
                        $hoo[$oh]['claim'] = (int)$di['claim'];
                        $hoo[$oh]['done'] = (int)$di['done'];
                        $hoo[$oh]['cancel'] = (int)$di['cancel'];
                        $hoo[$oh]['sign_count'] = (int)$di['sign_count'];
                        $hoo[$oh]['sign_state'] = (int)$di['sign_state'];
                        $hoo[$oh]['sign_wait'] = (int)$di['sign_wait'];
                        $hoo[$oh]['sign_cancel'] = (int)$di['sign_cancel'];
                        $hoo[$oh]['host'] = $dii;
                        $oh++;
                    }
                    unset($oh);
                    $count = count($hoo);
                    $pager = new Page($count, $r);
                    $this->assign("pagination", $pager->show());
                    $i = ($page-1)*$r;
                    $p = $page*$r;
                    for ($i;$i<$p;$i++){
                        $data[] = $hoo[$i];
                    }
                    $data = array_filter($data);
                    $this->assign('data',$data);
                    $this->display('bespoke_statistics2');
                    break;
                //众筹服务统计
                case 190:
                    $this->assign('title','众筹服务统计');
//                $map['issue_id'] = 96; //众筹服务统计
                    $map['status'] = 1;

                    $map['title'] = array('exp', 'IS NOT NULL'); //党课预约统计

                    //时间控制
                    $timehd = I('time-hd');//活动时间
                    $timebm = I('time-bm');//报名时间

                    if($timebm||$timehd){
                        if($timehd){
                            $this->assign('timehd',$timehd);
                            $timehd = explode('-',$timehd);
                            $timehd = $this->mFristAndLast($timehd[0],$timehd[1]);
                            $map['cer_time'] = array(array('egt',$timehd['firstday']),array('elt',$timehd['lastday']));
                        }
                        if($timebm){
                            $this->assign('timebm',$timebm);
                            $timebm = explode('-',$timebm);
                            $timebm = $this->mFristAndLast($timebm[0],$timebm[1]);
                            $map2['cer_time'] = array(array('egt',$timebm['firstday']),array('elt',$timebm['lastday']));
                        }
                    }
                    $host = $this->zhch->where($map)->field('state,status,id,title')->select();

//                dump($host);exit;
                    $ho = array();
                    foreach ($host as $h => $t) {
                        if (!$ho[$t['title']]) {
                            $ho[$t['title']]['count'] = 1;
                            if ($t['state'] == 1) {
                                $ho[$t['title']]['adopt'] = 1;   //通过
                            }
                            else if( $t['state'] == 0 ) {
                                $ho[$t['title']]['pending'] = 1;  //待审批
                            }
                            else if( $t['state'] == -1 ) {
                                $ho[$t['title']]['fail'] = 1;  //审批失败
                            }
//                        //已认领
//                        else if( $t['state'] == 2 ) {
//                            $ho[$t['title']]['claim'] = 1;  //已认领
//                        }
                            //已完成
                            else if( $t['state'] == 2 ) {
                                $ho[$t['title']]['done'] = 1;  //已完成
                            }
                            //用户已取消
                            else if( $t['state'] == 3 ) {
                                $ho[$t['title']]['cancel'] = 1;  //用户取消
                            }

                            //报名信息
//                        dump($t);
                            $map2['zhch_id'] = $t['id'];
                            $map2['status'] = 1;
                            $besp = $this->zhchapply->where($map2)->field('state,id,zhch_id')->select();
//                        dump($besp);exit;
                            foreach ($besp as $b => $p) {
                                $ho[$t['title']]['sign_count']++;  //微心愿总申请数
                                if ($p['state'] == 1) {
                                    $ho[$t['title']]['sign_state']++; //通过人数
                                } elseif ($p['state'] == 4) {
                                    $ho[$t['title']]['sign_cancel']++; //取消人数
                                } elseif ($p['state'] == 0) {
                                    $ho[$t['title']]['sign_wait']++; //待审批人数
                                }elseif ($p['state'] == 3) {
                                    $ho[$t['title']]['sign_wancheng']++; //待审批人数
                                }
                            }

                        }
                        else {
                            $ho[$t['title']]['count'] ++;
                            if ($t['state'] == 1) {
                                $ho[$t['title']]['adopt'] ++;   //通过
                            }
                            else if( $t['state'] == 0 ) {
                                $ho[$t['title']]['pending'] ++;  //待审批
                            }
                            else if( $t['state'] == -1 ) {
                                $ho[$t['title']]['fail'] ++; //审批失败
                            }
//                        //已认领
//                        else if( $t['state'] == 2 ) {
//                            $ho[$t['title']]['claim'] ++;  //已认领
//                        }
                            //已完成
                            else if( $t['state'] == 2) {
                                $ho[$t['title']]['done'] ++;  //已完成
                            }
                            //用户已取消
                            else if( $t['state'] == 3 ) {
                                $ho[$t['title']]['cancel'] ++;  //用户取消
                            }
                            //报名信息
                            $map2['zhch_id'] = $t['id'];
                            $map2['status'] = 1;
                            $besp = $this->zhchapply->where($map2)->field('state,id')->select();
                            foreach ($besp as $b => $p) {
                                $ho[$t['title']]['sign_count']++;  //总报名
                                if ($p['state'] == 1) {
                                    $ho[$t['title']]['sign_state']++; //通过人数
                                } elseif ($p['state'] == 4) {
                                    $ho[$t['title']]['sign_cancel']++; //取消人数
                                } elseif ($p['state'] == 0) {
                                    $ho[$t['title']]['sign_wait']++; //待审批人数
                                }elseif ($p['state'] == 3) {
                                    $ho[$t['title']]['sign_wancheng']++; //待审批人数
                                }
                            }
                        }
                    }
                    $oh = 0;

                    foreach ($ho as $dii=>&$di){
                        $hoo[$oh]['count'] = (int)$di['count'];
                        $hoo[$oh]['adopt'] = (int)$di['adopt'];
                        $hoo[$oh]['pending'] = (int)$di['pending'];
                        $hoo[$oh]['fail'] = (int)$di['fail'];
                        $hoo[$oh]['claim'] = (int)$di['claim'];
                        $hoo[$oh]['done'] = (int)$di['done'];
                        $hoo[$oh]['cancel'] = (int)$di['cancel'];
                        $hoo[$oh]['sign_count'] = (int)$di['sign_count'];
                        $hoo[$oh]['sign_state'] = (int)$di['sign_state'];
                        $hoo[$oh]['sign_wait'] = (int)$di['sign_wait'];
                        $hoo[$oh]['sign_cancel'] = (int)$di['sign_cancel'];
                        $hoo[$oh]['sign_wancheng'] = (int)$di['sign_wancheng'];
                        $hoo[$oh]['host'] = $dii;
                        $oh++;
                    }
                    unset($oh);
                    $count = count($hoo);
                    $pager = new Page($count, $r);
                    $this->assign("pagination", $pager->show());
                    $i = ($page-1)*$r;
                    $p = $page*$r;
                    for ($i;$i<$p;$i++){
                        $data[] = $hoo[$i];
                    }
                    $data = array_filter($data);
                    $this->assign('data',$data);
                    $this->display('bespoke_statistics4');
                    break;
                //项目直通车
                case 191:
                    $this->assign('title','项目统计');
//                $map['issue_id'] = 96; //众筹服务统计
                    $map['status'] = 1;

                    $map['title'] = array('exp', 'IS NOT NULL'); //党课预约统计

                    //时间控制
                    $timehd = I('time-hd');//活动时间
                    $timebm = I('time-bm');//报名时间

                    if($timebm||$timehd){
                        if($timehd){
                            $this->assign('timehd',$timehd);
                            $timehd = explode('-',$timehd);
                            $timehd = $this->mFristAndLast($timehd[0],$timehd[1]);
                            $map['cer_time'] = array(array('egt',$timehd['firstday']),array('elt',$timehd['lastday']));
                        }
                        if($timebm){
                            $this->assign('timebm',$timebm);
                            $timebm = explode('-',$timebm);
                            $timebm = $this->mFristAndLast($timebm[0],$timebm[1]);
                            $map2['cer_time'] = array(array('egt',$timebm['firstday']),array('elt',$timebm['lastday']));
                        }
                    }
                    $host = $this->direct->where($map)->field('state,status,id,title')->select();

//                dump($host);exit;
                    $ho = array();
                    foreach ($host as $h => $t) {
                        if (!$ho[$t['title']]) {
                            $ho[$t['title']]['count'] = 1;
                            if ($t['state'] == 1) {
                                $ho[$t['title']]['adopt'] = 1;   //通过
                            }
                            else if( $t['state'] == 0 ) {
                                $ho[$t['title']]['pending'] = 1;  //待审批
                            }
                            else if( $t['state'] == -1 ) {
                                $ho[$t['title']]['fail'] = 1;  //审批失败
                            }


                            //报名信息
//                        dump($t);
                            $map2['direct_id'] = $t['id'];
                            $map2['status'] = 1;
                            $besp = $this->directapply->where($map2)->field('state,id,direct_id')->select();
                            foreach ($besp as $b => $p) {
                                $ho[$t['title']]['sign_count']++;  //微心愿总申请数
                                if ($p['state'] == 1) {
//                                                        dump($p);exit;

                                    $ho[$t['title']]['sign_state']++; //通过人数
                                }
                                elseif ($p['state'] == 4) {
                                    $ho[$t['title']]['sign_cancel']++; //取消人数
                                }
                                elseif ($p['state'] == 0) {
                                    $ho[$t['title']]['sign_wait']++; //待审批人数
                                }
                            }

                        }
                        else {
                            $ho[$t['title']]['count'] ++;
                            if ($t['state'] == 1) {
                                $ho[$t['title']]['adopt'] ++;   //通过
                            }
                            else if( $t['state'] == 0 ) {
                                $ho[$t['title']]['pending'] ++;  //待审批
                            }
                            else if( $t['state'] == -1 ) {
                                $ho[$t['title']]['fail'] ++; //审批失败
                            }
//                        //已认领
//                        else if( $t['state'] == 2 ) {
//                            $ho[$t['title']]['claim'] ++;  //已认领
//                        }
                            //已完成
//                        else if( $t['state'] == 2) {
//                            $ho[$t['title']]['done'] ++;  //已完成
//                        }
//                        //用户已取消
//                        else if( $t['state'] == 3 ) {
//                            $ho[$t['title']]['cancel'] ++;  //用户取消
//                        }
                            //报名信息
                            $map2['direct_id'] = $t['id'];
                            $map2['status'] = 1;
                            $besp = $this->directapply->where($map2)->field('state,id,direct_id')->select();
                            foreach ($besp as $b => $p) {
                                $ho[$t['title']]['sign_count']++;  //总报名
                                if ($p['state'] == 1) {
                                    $ho[$t['title']]['sign_state']++; //通过人数
                                } elseif ($p['state'] == 4) {
                                    $ho[$t['title']]['sign_cancel']++; //取消人数
                                } elseif ($p['state'] == 0) {
                                    $ho[$t['title']]['sign_wait']++; //待审批人数
                                }
                            }
                        }
                    }
                    $oh = 0;

                    foreach ($ho as $dii=>&$di){
                        $hoo[$oh]['count'] = (int)$di['count'];
                        $hoo[$oh]['adopt'] = (int)$di['adopt'];
                        $hoo[$oh]['pending'] = (int)$di['pending'];
                        $hoo[$oh]['fail'] = (int)$di['fail'];
                        $hoo[$oh]['claim'] = (int)$di['claim'];
                        $hoo[$oh]['done'] = (int)$di['done'];
                        $hoo[$oh]['cancel'] = (int)$di['cancel'];
                        $hoo[$oh]['sign_count'] = (int)$di['sign_count'];
                        $hoo[$oh]['sign_state'] = (int)$di['sign_state'];
                        $hoo[$oh]['sign_wait'] = (int)$di['sign_wait'];
                        $hoo[$oh]['sign_cancel'] = (int)$di['sign_cancel'];
                        $hoo[$oh]['host'] = $dii;
                        $oh++;
                    }
                    unset($oh);
                    $count = count($hoo);
                    $pager = new Page($count, $r);
                    $this->assign("pagination", $pager->show());
                    $i = ($page-1)*$r;
                    $p = $page*$r;
                    for ($i;$i<$p;$i++){
                        $data[] = $hoo[$i];
                    }
                    $data = array_filter($data);
                    $this->assign('data',$data);
                    $this->display('bespoke_statistics5');
                    break;


            }


        }
        else {
            //读取列表
            Cookie('__forward__', $_SERVER['REQUEST_URI']);
            $map['status'] = 1;
            if ($type) {
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
            );
            foreach ($list as $k => $v) {
                $issueInfo = $category->where('id = ' . $v['issue_id'])->find();
                $list[$k]['issue_name'] = $issueInfo['title'];
                $list[$k]['link'] = $v['title'];
                $list[$k]['mode'] = $appointment[$v['mode']];
            }
            unset($li);
            $totalCount = $model->where($map)->count();


            //显示页面
            $builder = new AdminListBuilder();
            $attr['class'] = 'btn ajax-post';
            $attr['target-form'] = 'ids';

            header("Content-type:text/html;charset=UTF-8");
            if ($title == 1) $titlename = '已发布列表';
            if ($title == 0) $titlename = '编辑中列表';
            if ($title == -1) $titlename = '已删除列表';

            $builder->title($titlename);
            $builder->setStatusUrl(U('setWildcardStatus'));
            if ($title != 1) {
                $builder->buttonEnable('', L('_AUDIT_SUCCESS_'));
            }

            if ($title == -1) {
                $builder->setStatusUrl(U('setEditStatus'));
                $builder->buttonRestore();
                $builder->setStatusUrl(U('setDeltteStatus'));
                $builder->buttonDelete();
            }

            if ($title != -1) {
                $builder->buttonDelete();
            }


            //        dump($list);exit;
            $builder->button('新增', array('class' => 'btn btn-success', 'href' => U('admin/Cx/addcontent?type=' . $type)));
            $builder->button('已发布列表', array('class' => 'btn btn-info', 'href' => U('admin/Cx/content?title=1&type=' . $type)))
                ->button('编辑中列表', array('class' => 'btn btn-warning', 'href' => U('admin/Cx/content?title=0&type=' . $type)))
                ->button('已删除列表', array('class' => 'btn btn-danger', 'href' => U('admin/Cx/content?title=-1&type=' . $type)));

            if ($type == 82) { //党建活动预约管理
                $builder->keyId()
                    ->keyText('link', '标题')
                    ->keyText('addr', '活动地址')
                    ->keyCreateTime('time', '活动开始时间')
                    ->keyText('num', '人数')
                    ->keyText('host', '组织单位')
                    ->keyDoActionEdit("admin/Cx/addcontent/type/" . $type . '?id=###')
                    ->keyDoActionEdit("admin/Cx/sign/type/" . $type . '?content_id=###', '查看报名人员')
                    ->data($list)
                    ->pagination($totalCount, $r)
                    ->display();
            } elseif ($type == 84) { //志愿者报名管理
                $builder->keyText('link', '标题')
                    ->keyText('addr', '活动地址')
                    ->keyText('gather', '活动集合地点')
                    ->keyText('service_object', '服务对象')
                    ->keyText('time', '活动开始时间')
                    ->keyText('duration', '活动持续时间')
                    ->keyCreateTime()
                    ->keyText('num', '计划招募人数')
                    ->keyText('host', '组织单位')
                    ->keyDoActionEdit("admin/Cx/addcontent/type/" . $type . '?id=###')
                    ->keyDoActionEdit("admin/Cx/sign/type/" . $type . '?content_id=###', '查看报名人员')
                    ->data($list)
                    ->pagination($totalCount, $r)
                    ->display();
            } elseif ($type == 86) { //场地预约管理
                $builder->keyText('link', '标题')
                    ->keyText('addr', '活动地址')
                    ->keyText('mode', '预约方式')
                    ->keyImage('cover_id', '封面图片', array('width' => 25))
                    ->keyCreateTime()
                    ->keyText('num', '最大容纳人数')
                    ->keyText('host', '组织单位')
                    ->keyText('telphone', '预约电话')
                    ->keyText('sort', '排序')
                    ->keyDoActionEdit("admin/Cx/addcontent/type/" . $type . '?id=###')
                    ->keyDoActionEdit("admin/Cx/sign/type/" . $type . '?content_id=###', '查看报名人员')
                    ->data($list)
                    ->pagination($totalCount, $r)
                    ->display();
            } elseif ($type == 88) { //党课预约管理
                $builder->keyText('link', '党课名称')
                    ->keyText('addr', '授课地址')
                    ->keyText('host', '举办单位')
                    ->keyCreateTime('time', '举办时间')
                    ->keyText('teacher', '授课老师')
                    ->keyText('user', '联系人')
                    ->keyText('telphone', '联系电话')
                    ->keyText('num', '教室最大人数')
                    ->keyText('sort', '排序')
                    ->keyDoActionEdit("admin/Cx/addcontent/type/" . $type . '?id=###')
                    ->keyDoActionEdit("admin/Cx/sign/type/" . $type . '?content_id=###', '查看报名人员')
                    ->data($list)
                    ->pagination($totalCount, $r)
                    ->display();
            } elseif ($type == 90) { //讲师预约管理
                $builder
                    ->keyImage('cover_id', '讲师头像', array('width' => 40))
                    ->keyText('link', '讲师姓名')
                    ->keyText('host', '所属组织')
                    ->keyText('content', '讲师简介')
                    ->keyText('sort', '排序')
                    ->keyDoActionEdit("admin/Cx/addcontent/type/" . $type . '?id=###')
//                    ->keyDoActionEdit("admin/Cx/sign/type/".$type.'?content_id=###','查看报名人员')
                    ->data($list)
                    ->pagination($totalCount, $r)
                    ->display();
            } elseif ($type == 105) { //讲师预约管理
                $builder
                    ->keyImage('cover_id', '党员头像', array('width' => 40))
                    ->keyText('link', '姓名')
                    ->keyText('host', '所属组织')
                    ->keyText('content', '讲师简介')
                    ->keyText('sort', '排序')
                    ->keyDoActionEdit("admin/Cx/addcontent/type/" . $type . '?id=###')
                    ->data($list)
                    ->pagination($totalCount, $r)
                    ->display();
            } elseif ($type == 170 || $type == 171 || $type == 174 || $type == 175 || $type == 176 || $type == 177 || $type == 173 || $type == 178 || $type == 179 || $type == 180 || $type == 181 || $type == 182 || $type == 199) {
                $builder->keyId()
                    ->keyText('link', '标题')
                    ->keyCreateTime()
                    ->keyDoActionEdit("admin/Cx/addcontent/type/" . $type . '?id=###')
                    ->data($list)
                    ->pagination($totalCount, $r)
                    ->display();
            } elseif ($type == 174 || $type == 182) {
                $builder->keyId()
                    ->keyText('name', '姓名')
                    ->keyText('content', '简介')
                    ->keyCreateTime()
                    ->keyDoActionEdit("admin/Cx/addcontent/type/" . $type . '?id=###')
                    ->data($list)
                    ->pagination($totalCount, $r)
                    ->display();
            }elseif ($type >=183 && $type <=189){
                $builder->keyId()
                    ->keyText('link', '标题')
                    ->keyCreateTime()
                    ->keyDoActionEdit("admin/Cx/addcontent/type/" . $type . '?id=###')
                    ->data($list)
                    ->pagination($totalCount, $r)
                    ->display();
            }
        } //预约服务
    }
    //统计分析
    /*
     * host  发布单位
     * classify 类型*/
    public function statistical_analysis($host = null,$classify = null,$types=null,$page=1,$r=10)
    {
        if(empty($host) || empty($classify) || empty($types)){
            exit;
        }else{
            $host = $this->unescape($host);
        }

        switch ($types){
            //活动统计
            case 83:
                $timehd = I('timebm');
                $timebm = I('timehd');
                if($timebm||$timehd){
                    if($timehd){
                        $this->assign('timehd',$timehd);
                        $timehd = explode('-',$timehd);
                        $timehd = $this->mFristAndLast($timehd[0],$timehd[1]);
                        $map['create_time'] = array(array('egt',$timehd['firstday']),array('elt',$timehd['lastday']));
                    }
                    if($timebm){
                        $this->assign('timebm',$timebm);
                        $timebm = explode('-',$timebm);
                        $timebm = $this->mFristAndLast($timebm[0],$timebm[1]);
                        $map2['cre_time'] = array(array('egt',$timebm['firstday']),array('elt',$timebm['lastday']));
                    }
                }
                switch ($classify){
                    case 'fbhdzsl':
                        $map['issue_id'] = 82;  //活动表单
                        $map['host'] = $host;
                        $count = M('issue_content')->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = M('issue_content')->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['state'] = 1;
                            $map2['content_id'] = $v['id'];
                            $v['bm_count'] = M('sign_bespoke')->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;

                        break;
                    case 'xsxm':
                        $map['status'] = 1;
                        $map['issue_id'] = 82;  //活动表单
                        $map['host'] = $host;
                        $count = M('issue_content')->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = M('issue_content')->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['state'] = 1;
                            $map2['content_id'] = $v['id'];
                            $v['bm_count'] = M('sign_bespoke')->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;

                        break;
                    case 'yjzxm':
                        $map['status'] = -1;
                        $map['issue_id'] = 82;  //活动表单
                        $map['host'] = $host;
                        $count = M('issue_content')->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = M('issue_content')->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['state'] = 1;
                            $map2['content_id'] = $v['id'];
                            $v['bm_count'] = M('sign_bespoke')->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);
                        $this->display('details');exit;

                        break;
                    case 'cyzrs':
//                        $map['status'] = -1;
                        $map['issue_id'] = 82;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $data2 = M('sign_bespoke')->where($map2)->page($page,$r)->select();
                        $count = M('sign_bespoke')->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);

                        $this->display('details2');
                        exit;

                        break;
                    case 'spcgrs':
//                        $map['status'] = -1;
                        $map['issue_id'] = 82;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['state'] = 1;
                        $map2['status'] = 1;
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $data2 = M('sign_bespoke')->where($map2)->page($page,$r)->select();
                        $count = M('sign_bespoke')->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);

                        $this->display('details2');
                        exit;

                        break;
                    case 'dsprs':
//                        $map['status'] = -1;
                        $map['issue_id'] = 82;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['state'] = 0;
                        $map2['status'] = 1;
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $data2 = M('sign_bespoke')->where($map2)->page($page,$r)->select();
                        $count = M('sign_bespoke')->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);

                        $this->display('details2');
                        exit;

                        break;
                    case 'yhqx':
//                        $map['status'] = -1;
                        $map['issue_id'] = 82;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['state'] = 3;
                        $map2['status'] = 1;
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $data2 = M('sign_bespoke')->where($map2)->page($page,$r)->select();
                        $count = M('sign_bespoke')->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);

                        $this->display('details2');
                        exit;

                        break;
                }
                break;
            //志愿者活动
            case 85:
                $timehd = I('timebm');
                $timebm = I('timehd');
                if($timebm||$timehd){
                    if($timehd){
                        $this->assign('timehd',$timehd);
                        $timehd = explode('-',$timehd);
                        $timehd = $this->mFristAndLast($timehd[0],$timehd[1]);
                        $map['create_time'] = array(array('egt',$timehd['firstday']),array('elt',$timehd['lastday']));
                    }
                    if($timebm){
                        $this->assign('timebm',$timebm);
                        $timebm = explode('-',$timebm);
                        $timebm = $this->mFristAndLast($timebm[0],$timebm[1]);
                        $map2['cre_time'] = array(array('egt',$timebm['firstday']),array('elt',$timebm['lastday']));
                    }
                }
                switch ($classify){
                    case 'fbhdzsl':
                        $map['issue_id'] = 84 ;  //志愿者活动表单
                        $map['host'] = $host;
                        $count = M('issue_content')->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = M('issue_content')->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['state'] = 1;
                            $map2['content_id'] = $v['id'];
                            $v['bm_count'] =$this->volunteer->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;

                        break;
                    case 'xsxm':
                        $map['status'] = 1;
                        $map['issue_id'] = 84;  //活动表单
                        $map['host'] = $host;
                        $count = M('issue_content')->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = M('issue_content')->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['state'] = 1;
                            $map2['content_id'] = $v['id'];
                            $v['bm_count'] = $this->volunteer->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;

                        break;
                    case 'yjzxm':
                        $map['status'] = -1;
                        $map['issue_id'] = 84;  //活动表单
                        $map['host'] = $host;
                        $count = M('issue_content')->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = M('issue_content')->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['state'] = 1;
                            $map2['content_id'] = $v['id'];
                            $v['bm_count'] = $this->volunteer->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);
                        $this->display('details');exit;

                        break;
                    case 'cyzrs':
//                        $map['status'] = -1;
                        $map['issue_id'] = 84;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $data2 = $this->volunteer->where($map2)->page($page,$r)->select();
                        $count = $this->volunteer->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details2');
                        exit;

                        break;
                    case 'spcgrs':
//                        $map['status'] = -1;
                        $map['issue_id'] = 84;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['state'] = 1;
                        $map2['status'] = 1;
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $data2 = $this->volunteer->where($map2)->page($page,$r)->select();
                        $count = $this->volunteer->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details2');
                        exit;

                        break;
                    case 'dsprs':
//                        $map['status'] = -1;
                        $map['issue_id'] = 84;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['state'] = 0;
                        $map2['status'] = 1;
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $data2 = $this->volunteer->where($map2)->page($page,$r)->select();
                        $count = $this->volunteer->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details2');
                        exit;

                        break;
                    case 'yhqx':
//                        $map['status'] = -1;
                        $map['issue_id'] = 84;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['state'] = 3;
                        $map2['status'] = 1;
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $data2 = $this->volunteer->where($map2)->page($page,$r)->select();
                        $count = $this->volunteer->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details2');
                        exit;

                        break;
                }
                break;
            //场地预约
            case 87:
                $timehd = I('timebm');
                $timebm = I('timehd');
                if($timebm||$timehd){
                    if($timehd){
                        $this->assign('timehd',$timehd);
                        $timehd = explode('-',$timehd);
                        $timehd = $this->mFristAndLast($timehd[0],$timehd[1]);
                        $map['create_time'] = array(array('egt',$timehd['firstday']),array('elt',$timehd['lastday']));
                    }
                    if($timebm){
                        $this->assign('timebm',$timebm);
                        $timebm = explode('-',$timebm);
                        $timebm = $this->mFristAndLast($timebm[0],$timebm[1]);
                        $map2['cre_time'] = array(array('egt',$timebm['firstday']),array('elt',$timebm['lastday']));
                    }
                }
                switch ($classify){
                    case 'fbhdzsl':
                        $map['issue_id'] = 86;  //志愿者活动表单
                        $map['host'] = $host;
                        $count = M('issue_content')->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = M('issue_content')->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['state'] = 1;
                            $map2['content_id'] = $v['id'];
                            $v['bm_count'] =$this->field->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;

                        break;
                    case 'xsxm':
                        $map['status'] = 1;
                        $map['issue_id'] = 86;  //活动表单
                        $map['host'] = $host;
                        $count = M('issue_content')->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = M('issue_content')->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['state'] = 1;
                            $map2['content_id'] = $v['id'];
                            $v['bm_count'] = $this->field->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;

                        break;
                    case 'yjzxm':
                        $map['status'] = -1;
                        $map['issue_id'] = 86;  //活动表单
                        $map['host'] = $host;
                        $count = M('issue_content')->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = M('issue_content')->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['state'] = 1;
                            $map2['content_id'] = $v['id'];
                            $v['bm_count'] = $this->field->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);
                        $this->display('details');exit;

                        break;
                    case 'cyzrs':
//                        $map['status'] = -1;
                        $map['issue_id'] = 86;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $data2 = $this->field->where($map2)->page($page,$r)->select();
                        $count = $this->field->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details2');
                        exit;

                        break;
                    case 'spcgrs':
//                        $map['status'] = -1;
                        $map['issue_id'] = 86;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['state'] = 1;
                        $map2['status'] = 1;
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $data2 = $this->field->where($map2)->page($page,$r)->select();
                        $count = $this->field->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details2');
                        exit;

                        break;
                    case 'dsprs':
//                        $map['status'] = -1;
                        $map['issue_id'] = 86;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['state'] = 0;
                        $map2['status'] = 1;
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $data2 = $this->field->where($map2)->page($page,$r)->select();
                        $count = $this->field->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details2');
                        exit;

                        break;
                    case 'yhqx':
//                        $map['status'] = -1;
                        $map['issue_id'] = 86;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['state'] = 3;
                        $map2['status'] = 1;
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $data2 = $this->field->where($map2)->page($page,$r)->select();
                        $count = $this->field->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details2');
                        exit;

                        break;
                }
                break;
            //党课预约统计分析
            case 89:
                $timehd = I('timebm');
                $timebm = I('timehd');
                if($timebm||$timehd){
                    if($timehd){
                        $this->assign('timehd',$timehd);
                        $timehd = explode('-',$timehd);
                        $timehd = $this->mFristAndLast($timehd[0],$timehd[1]);
                        $map['create_time'] = array(array('egt',$timehd['firstday']),array('elt',$timehd['lastday']));
                    }
                    if($timebm){
                        $this->assign('timebm',$timebm);
                        $timebm = explode('-',$timebm);
                        $timebm = $this->mFristAndLast($timebm[0],$timebm[1]);
                        $map2['cre_time'] = array(array('egt',$timebm['firstday']),array('elt',$timebm['lastday']));
                    }
                }
                switch ($classify){
                    case 'fbhdzsl':
                        $map['issue_id'] = 88;  //志愿者活动表单
                        $map['host'] = $host;
                        $count = M('issue_content')->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = M('issue_content')->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['state'] = 1;
                            $map2['content_id'] = $v['id'];
                            $v['bm_count'] =$this->lecture->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;

                        break;
                    case 'xsxm':
                        $map['status'] = 1;
                        $map['issue_id'] = 88;  //活动表单
                        $map['host'] = $host;
                        $count = M('issue_content')->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = M('issue_content')->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['state'] = 1;
                            $map2['content_id'] = $v['id'];
                            $v['bm_count'] = $this->lecture->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;

                        break;
                    case 'yjzxm':
                        $map['status'] = -1;
                        $map['issue_id'] = 88;  //活动表单
                        $map['host'] = $host;
                        $count = M('issue_content')->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = M('issue_content')->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['state'] = 1;
                            $map2['content_id'] = $v['id'];
                            $v['bm_count'] = $this->lecture->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);
                        $this->display('details');exit;

                        break;
                    case 'cyzrs':
//                        $map['status'] = -1;
                        $map['issue_id'] = 88;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $data2 = $this->lecture->where($map2)->page($page,$r)->select();
                        $count = $this->lecture->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details2');
                        exit;

                        break;
                    case 'spcgrs':
//                        $map['status'] = -1;
                        $map['issue_id'] = 88;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['state'] = 1;
                        $map2['status'] = 1;
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $data2 = $this->lecture->where($map2)->page($page,$r)->select();
                        $count = $this->lecture->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details2');
                        exit;

                        break;
                    case 'dsprs':
//                        $map['status'] = -1;
                        $map['issue_id'] = 88;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['state'] = 0;
                        $map2['status'] = 1;
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $data2 = $this->lecture->where($map2)->page($page,$r)->select();
                        $count = $this->lecture->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details2');
                        exit;

                        break;
                    case 'yhqx':
//                        $map['status'] = -1;
                        $map['issue_id'] = 88;  //活动表单
                        $map['host'] = $host;
                        $data = M('issue_content')->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['state'] = 3;
                        $map2['status'] = 1;
                        $map2['content_id']=array('in',implode(',',$hdid));
                        $data2 = $this->lecture->where($map2)->page($page,$r)->select();
                        $count = $this->lecture->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['content_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details2');
                        exit;

                        break;
                }
                break;
            //微心愿
            case 95:
                $map['status'] = 1;

                $timehd = I('timebm');
                $timebm = I('timehd');
                if($timebm||$timehd){
                    if($timehd){
                        $this->assign('timehd',$timehd);
                        $timehd = explode('-',$timehd);
                        $timehd = $this->mFristAndLast($timehd[0],$timehd[1]);
                        $map['cre_time'] = array(array('egt',$timehd['firstday']),array('elt',$timehd['lastday']));
                    }
                    if($timebm){
                        $this->assign('timebm',$timebm);
                        $timebm = explode('-',$timebm);
                        $timebm = $this->mFristAndLast($timebm[0],$timebm[1]);
                        $map2['cer_time'] = array(array('egt',$timebm['firstday']),array('elt',$timebm['lastday']));
                    }
                }
                switch ($classify){
                    case 'fbxyzsl':
                        $map['organization'] = $host;
                        $count = $this->wish->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = $this->wish->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['wish_id'] = $v['id'];
                            $v['bm_count'] =$this->wishapply->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;

                        break;
                    case 'drl':
                        $map['organization'] = $host;
                        $map['state'] = 1;
                        $count = $this->wish->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = $this->wish->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['wish_id'] = $v['id'];
                            $v['bm_count'] =$this->wishapply->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;

                        break;
                    case 'wsp':
                        $map['organization'] = $host;
                        $map['state'] = 0;
                        $count = $this->wish->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = $this->wish->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['wish_id'] = $v['id'];
                            $v['bm_count'] =$this->wishapply->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;

                        break;
                    case 'spsb':
                        $map['organization'] = $host;
                        $map['state'] = -1;
                        $count = $this->wish->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = $this->wish->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['wish_id'] = $v['id'];
                            $v['bm_count'] =$this->wishapply->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;
                        break;
                    case 'yrl':
                        $map['organization'] = $host;
                        $map['state'] = 2;
                        $count = $this->wish->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = $this->wish->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['wish_id'] = $v['id'];
                            $v['bm_count'] =$this->wishapply->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;
                        break;
                    case 'ywc':
                        $map['organization'] = $host;
                        $map['state'] = 3;
                        $count = $this->wish->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = $this->wish->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['wish_id'] = $v['id'];
                            $v['bm_count'] =$this->wishapply->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;
                        break;
                    case 'yhqx':
                        $map['organization'] = $host;
                        $map['state'] = 4;
                        $count = $this->wish->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = $this->wish->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['wish_id'] = $v['id'];
                            $v['bm_count'] =$this->wishapply->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;
                        break;
                    case 'cyzs':
                        $map['organization'] = $host;
                        $data = $this->wish->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['wish_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $data2 = $this->wishapply->where($map2)->page($page,$r)->select();
                        $count = $this->wishapply->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            4=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['wish_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details3');
                        exit;
                        break;
                    case 'cycg':
                        $map['organization'] = $host;
                        $data = $this->wish->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['wish_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $map2['state'] = 1;
                        $data2 = $this->wishapply->where($map2)->page($page,$r)->select();
                        $count = $this->wishapply->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            4=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['wish_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details3');
                        exit;
                        break;
                    case 'cywsp':
                        $map['organization'] = $host;
                        $data = $this->wish->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['wish_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $map2['state'] = 0;
                        $data2 = $this->wishapply->where($map2)->page($page,$r)->select();
                        $count = $this->wishapply->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            4=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['wish_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details3');
                        exit;
                        break;
                    case 'yhqxcy':
                        $map['organization'] = $host;
                        $data = $this->wish->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['wish_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $map2['state'] = 4;
                        $data2 = $this->wishapply->where($map2)->page($page,$r)->select();
                        $count = $this->wishapply->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            4=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['wish_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details3');
                        exit;
                        break;
                }
                break;
            //众筹
            case 190:
                $map['status'] = 1;

                $timehd = I('timebm');
                $timebm = I('timehd');
                if($timebm||$timehd){
                    if($timehd){
                        $this->assign('timehd',$timehd);
                        $timehd = explode('-',$timehd);
                        $timehd = $this->mFristAndLast($timehd[0],$timehd[1]);
                        $map['cre_time'] = array(array('egt',$timehd['firstday']),array('elt',$timehd['lastday']));
                    }
                    if($timebm){
                        $this->assign('timebm',$timebm);
                        $timebm = explode('-',$timebm);
                        $timebm = $this->mFristAndLast($timebm[0],$timebm[1]);
                        $map2['cer_time'] = array(array('egt',$timebm['firstday']),array('elt',$timebm['lastday']));
                    }
                }
                switch ($classify){
                    case 'fbxmzsl':
                        $map['title'] = $host;
                        $count = $this->zhch->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = $this->zhch->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['zhch_id'] = $v['id'];
                            $v['bm_count'] =$this->zhchapply->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;

                        break;
                    case 'zcz':
                        $map['title'] = $host;
                        $map['state'] = 1;
                        $count = $this->zhch->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = $this->zhch->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['zhch_id'] = $v['id'];
                            $v['bm_count'] =$this->zhchapply->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;

                        break;
                    case 'wsp':
                        $map['title'] = $host;
                        $map['state'] = 0;
                        $count = $this->zhch->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = $this->zhch->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['zhch_id'] = $v['id'];
                            $v['bm_count'] =$this->zhchapply->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;
                        break;
                    case 'spsb':
                        $map['title'] = $host;
                        $map['state'] = -1;
                        $count = $this->zhch->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = $this->zhch->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['zhch_id'] = $v['id'];
                            $v['bm_count'] =$this->zhchapply->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;
                        break;
                    case 'ywc':
                        $map['title'] = $host;
                        $map['state'] = 2;
                        $count = $this->zhch->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = $this->zhch->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['zhch_id'] = $v['id'];
                            $v['bm_count'] =$this->zhchapply->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;
                        break;
                    case 'cyzs':
                        $map['title'] = $host;
                        $data = $this->zhch->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['zhch_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $data2 = $this->zhchapply->where($map2)->page($page,$r)->select();
                        $count = $this->zhchapply->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'已完成',
                            4=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['zhch_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details4');
                        exit;
                        break;
                    case 'cycg':
                        $map['title'] = $host;
                        $data = $this->zhch->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['zhch_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $map2['state'] = 1;
                        $data2 = $this->zhchapply->where($map2)->page($page,$r)->select();
                        $count = $this->zhchapply->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'已完成',
                            4=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['zhch_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details4');
                        exit;
                        break;
                    case 'cywsp':
                        $map['title'] = $host;
                        $data = $this->zhch->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['zhch_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $map2['state'] = 0;
                        $data2 = $this->zhchapply->where($map2)->page($page,$r)->select();
                        $count = $this->zhchapply->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'已完成',
                            4=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['zhch_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details4');
                        exit;
                        break;
                    case 'wc':
                        $map['title'] = $host;
                        $data = $this->zhch->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['zhch_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $map2['state'] = 3;
                        $data2 = $this->zhchapply->where($map2)->page($page,$r)->select();
                        $count = $this->zhchapply->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'已完成',
                            4=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['zhch_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details4');
                        exit;
                        break;
                    case 'yhqxcy':
                        $map['title'] = $host;
                        $data = $this->zhch->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['zhch_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $map2['state'] = 4;
                        $data2 = $this->zhchapply->where($map2)->page($page,$r)->select();
                        $count = $this->zhchapply->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                            3=>'已完成',
                            4=>'用户取消',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['zhch_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details4');
                        exit;
                        break;
                }
                break;
                //项目直通车
            case 191:
                $map['status'] = 1;

                $timehd = I('timebm');
                $timebm = I('timehd');
                if($timebm||$timehd){
                    if($timehd){
                        $this->assign('timehd',$timehd);
                        $timehd = explode('-',$timehd);
                        $timehd = $this->mFristAndLast($timehd[0],$timehd[1]);
                        $map['cer_time'] = array(array('egt',$timehd['firstday']),array('elt',$timehd['lastday']));
                    }
                    if($timebm){
                        $this->assign('timebm',$timebm);
                        $timebm = explode('-',$timebm);
                        $timebm = $this->mFristAndLast($timebm[0],$timebm[1]);
                        $map2['cer_time'] = array(array('egt',$timebm['firstday']),array('elt',$timebm['lastday']));
                    }
                }
                switch ($classify){
                    case 'xmzsl':
                        $map['title'] = $host;
                        $count = $this->direct->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = $this->direct->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['direct_id'] = $v['id'];
                            $v['bm_count'] =$this->directapply->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;

                        break;
                    case 'zcz':
                        $map['title'] = $host;
                        $map['state'] = 1;
                        $count = $this->direct->where($map)->count();
                        $Page = new Page($count, $r);
                        $show = $Page->show();
                        $data = $this->direct->where($map)->page($page,$r)->select();
                        foreach ($data as $k=>&$v){
                            $map2['status'] = 1;
                            $map2['direct_id'] = $v['id'];
                            $v['bm_count'] =$this->directapply->where($map2)->count();
                        }
                        $this->assign('data',$data);
                        $this->assign('pagination', $show);

                        $this->display('details');exit;

                        break;
                    case 'cyzs':
                        $map['title'] = $host;
                        $data = $this->direct->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['direct_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $data2 = $this->directapply->where($map2)->page($page,$r)->select();
                        $count = $this->directapply->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['direct_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details4');
                        exit;
                        break;
                    case 'cycg':
                        $map['title'] = $host;
                        $data = $this->direct->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['direct_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $map2['state'] = 1;
                        $data2 = $this->directapply->where($map2)->page($page,$r)->select();
                        $count = $this->directapply->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();
                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['direct_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details4');
                        exit;
                        break;
                    case 'cywsp':
                        $map['title'] = $host;
                        $data = $this->direct->where($map)->field('id,title')->select();
                        $hd = array();
                        $hdid = '';
                        foreach ($data as $k=>&$v){
                            $hd[$v['id']] = $v['title'];
                            $hdid[] =$v['id'];

                        }
                        $map2['direct_id']=array('in',implode(',',$hdid));
                        $map2['status'] = 1;
                        $map2['state'] = 0;
                        $data2 = $this->directapply->where($map2)->page($page,$r)->select();
                        $count = $this->directapply->where($map2)->count();
                        $Page = new Page($count,$r);
                        $show = $Page->show();

                        $ste = array(
                            0=>'待审批',
                            1=>'审批通过',
                            -1=>'审批未通过',
                        );
                        foreach ($data2 as $kk=>&$vv){
                            $vv['state_var'] = $ste[$vv['state']];
                            $vv['hd_title'] = $hd[$vv['direct_id']];
                        }
                        $this->assign('data',$data2);
                        $this->assign('pagination', $show);
                        $this->display('details4');
                        exit;
                        break;
                }
                break;
        }


    }

    /*新增*/
    public function addcontent($type = 0)
    {

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

            if ($groupid == 10) {
                $data['project_category'] = $uid;
            }

            if (!$data['cover_id']) {
                $data['cover_id'] = 16;
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
                session('issue_id_temp', $data['issue_id']);
                if ($Model->add($data) !== false) {
                    $this->success("添加成功", Cookie('__forward__'));
                } else {
                    $this->error("添加失败");
                }
            }

        } else {
//            if($type){
//                $fatherId1 =  $category->where('id='.$type)->getField('pid');
//                $fatherId2 =  $category->where('id='.$fatherId1)->getField('pid');
//                $fatherId3 =  $category->where('id='.$fatherId2)->getField('pid');
//                $this->assign('type',$type);
//            }
//
//            $this->assign('actionname',ACTION_NAME);  //方法名
//            $this->assign('accontroller',CONTROLLER_NAME);  //控制器名
//
//            if(CONTROLLER_NAME =='Cx'){
//                $mapcontent['status']=1;
//                if($groupid == 3){
//                    $mapcontent['group_id'] = array('like','%3%');
//                }elseif ($groupid == 10){
//                    $mapcontent['group_id'] = array('like','%10%');
//                }
//                $mapcontent['lv']=0;
////            $mapcontent['project_category'] = 0;
//                $issuemenus = $category->where($mapcontent)->field(true)->order('sort asc')->select();
////            dump( $issuemenus);die;
//                foreach($issuemenus as $k=>$v){
//                    $mapcontent['lv']=1;
////                if($groupid == 11){
////                    $mapcontent['group_id']=11;
////                }elseif ($groupid == 10){
////                    $mapcontent['has_jw']=1;
////                }
//                    $mapcontent['pid']=$v['id'];
////                $mapcontent['project_category'] = 0;
//                    $issuemenus1 = $category->where($mapcontent)->field(true)->order('sort asc')->select();
//                    $issuemenus[$k]['issuemenus'] =$issuemenus1;
////                dump( $issuemenus[$k]['issuemenus']);
//                    $issuemenus[$k]['count'] =count($issuemenus1);
//                    if($v['id']==$fatherId1||$v['id']==$fatherId2||$v['id']==$fatherId3)$issuemenus[$k]['class'] =' open in ';
//                    if($v['id']==$type)$issuemenus[$k]['class'] ='active';
//
//                    foreach($issuemenus[$k]['issuemenus'] as $k1=>$v1){
//                        $mapcontent['lv']=2;
////                    if($groupid == 11){
////                        $mapcontent['group_id']=11;
////                    }elseif ($groupid == 10){
////                        $mapcontent['has_jw']=1;
////                    }
//                        $mapcontent['pid']=$v1['id'];
////                    $mapcontent['project_category'] = 0;
//                        $issuemenus2 = $category->where($mapcontent)->field(true)->order('sort asc')->select();
//                        $issuemenus[$k]['issuemenus'][$k1]['issuemenus'] =$issuemenus2;
////                    dump($issuemenus[$k]['issuemenus'][$k1]['issuemenus']);
//                        $issuemenus[$k]['issuemenus'][$k1]['count'] = count($issuemenus2);
//                        if($v1['id']==$fatherId1||$v1['id']==$fatherId2||$v1['id']==$fatherId3){
//                            $issuemenus[$k]['issuemenus'][$k1]['class'] =' open in ';
//                        }
//                        if($v1['id']==$type)$issuemenus[$k]['issuemenus'][$k1]['class'] ='active';
//                        foreach($issuemenus[$k]['issuemenus'][$k1]['issuemenus'] as $k2=>$v2){
//                            $mapcontent['lv']=3;
////                        if($groupid == 11){
////                            $mapcontent['group_id']=11;
////                        }elseif ($groupid == 10){
////                            $mapcontent['has_jw']=1;
////                        }
//                            $mapcontent['pid']=$v2['id'];
////                        $mapcontent['project_category'] = 0;
//                            $issuemenus3 = $category->where($mapcontent)->field(true)->order('sort asc')->select();
//                            $issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['issuemenus'] =$issuemenus3;
//                            $issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['count'] =count($issuemenus3);
//                            if($v1['id']==$fatherId1||$v1['id']==$fatherId2||$v1['id']==$fatherId3){
//                                $issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['class'] =' open in ';
//                            }
//                            if($v2['id']==$type)$issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['class'] ='active';
//                            foreach($issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['issuemenus'] as $k3=>$v3){
//                                if($v3['id']==$type)$issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['issuemenus'][$k3]['class'] ='active';
//                            }
//                        }
//                    }
//
//                }
//
////            dump($issuemenus);
////            die;
////            dump($issuemenus[0]['issuemenus'][1]);exit;
//                $this->assign('menutree',1);
//                $this->assign('issuemenus1', $issuemenus);
//            }
//
            $this->leftlist();
            $content = M('Issue_content');
            $data = $content->where(array("id" => $id))->find();
            $builder = new AdminConfigBuilder();
            if ($type) {
                $data['issue_id'] = $type;
            } elseif (session('issue_id_temp') && !$data['issue_id']) {
                $data['issue_id'] = session('issue_id_temp');
                session('issue_id_temp', '');

            }
            if ($groupid == 3) {
                $mapcontent2['group_id'] = array('like', '%3%');
            } elseif ($groupid == 10) {
                $mapcontent2['group_id'] = array('like', '%10%');
            }

            $mapcontent2['status'] = 1;
            $menus = $category->where($mapcontent2)->field(true)->order('sort asc')->select();
            foreach ($menus as $k => $v) {
                if ($v['lv'] == 0) {
                    $menus[$k]['title'] .= '===';
                }
                //└
                if ($v['lv'] == 1) {
                    $menus[$k]['title'] = '　└' . $menus[$k]['title'];
                    $menus[$k]['title'] .= '---';
                }
                if ($v['lv'] == 2) {
                    $menus[$k]['title'] = '　　└' . $menus[$k]['title'];
                }
                if ($v['lv'] == 3) {
                    $menus[$k]['title'] = '　　　　└' . $menus[$k]['title'];
                }
                if ($v['lv'] == 4) {
                    $menus[$k]['title'] = '　　　　　└' . $menus[$k]['title'];
                }
                if ($v['lv'] == 5) {
                    $menus[$k]['title'] = '　　　　　　└' . $menus[$k]['title'];
                }
                if ($v['lv'] == 6) {
                    $menus[$k]['title'] = '　　　　　　　└' . $menus[$k]['title'];
                }
            }
            $menus = D('Common/Tree')->toFormatTree($menus);
            $options = array_combine(array_column($menus, 'id'), array_column($menus, 'title'));
            if ($type == 82) {   //党建活动预约管理
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id', '分类编号')
                    ->keyText('title', '标题')
                    ->key('integral', '活动积分', '整数 默认1', 'number')
                    ->keyText('host', '组织单位')
//                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('sort', '排序', '数值越大越靠前')
                    ->keyBDMap('addr|lat|lng', '活动地址')
                    ->keyText('teacher', '授课老师')
                    ->keyTime('time', '活动开始时间')
                    ->keyText('duration', "活动持续时间")
                    ->keyText('num', '活动人数')
                    ->keyTextArea('content', "内容")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();
            } elseif ($type == 84) { //志愿者报名管理
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id', '分类编号')
                    ->keyText('title', '标题')
                    ->key('integral', '活动积分', '整数 默认1', 'number')
                    ->keyText('host', '实施队伍')
//                    ->keySelect("issue_id", "分类", "", $options)

                    ->keyText('sort', '排序', '数值越大越靠前')
                    ->keyText('gather', '活动集合地点')
                    ->keyBDMap('addr|lat|lng', '活动地址')
                    ->keyText('service_object', '服务对象')
                    ->keyTime('time', '活动开始时间')
                    ->keyText('duration', "活动持续时间")
                    ->keyText('num', '计划招募人数')
                    ->keyText('telphone', "联系电话")
                    ->keyTextArea('condition', "志愿者报名条件说明")
                    ->keyTextArea('content', "活动内容")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();
            } elseif ($type == 86) { //场地预约管理
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id', '分类编号')
                    ->keyText('title', '标题')
                    ->keyText('host', '组织单位')
//                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('sort', '排序', '数值越大越靠前')
                    ->keySelect("mode", "预约方式", "决定微信页面显示预约按钮还是预约电话【不选择默认网页】", array(1 => '网络预约', 2 => '电话预约'))
                    ->keySingleImage('cover_id', '封面图片')
                    ->keyBDMap('addr|lat|lng', '场地地址')
                    ->keyTime('time', '时间')
                    ->keyText('num', '最大容纳人数')
                    ->keyTextArea('content', "内容")
                    ->keyText('telphone', "预约电话")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();

            } elseif ($type == 88) { //党课预约管理
                $teach = M('issue_content')->where(array('status' => 1, 'state' => 1, 'issue_id' => 90))->field('title')->select();
                $li[] = '请选择授课讲师';
                foreach ($teach as $k2 => $v2) {
                    $li[$v2['title']] = $v2['title'];
                }

                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id', '分类编号')
                    ->keyText('title', '党课名称')
                    ->key('integral', '党课积分', '整数 默认1', 'number')
                    ->keyText('host', '举办单位')
//                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('sort', '排序', '数值越大越靠前')
                    ->keyTime('time', '举办时间')
                    ->keySelect('teacher', '授课老师', '', $li)
                    ->keyTextArea('teacher_brief', '授课老师简介')
                    ->keyBDMap('addr|lat|lng', '授课地址')
                    ->keyText('user', '联系人')
                    ->keyText('telphone', '联系电话')
                    ->keyText('num', '教师最大人数')
                    ->keyTextArea('content', "党课内容")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();
            } elseif ($type == 90) { //讲师预约管理
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id', '分类编号')
                    ->keyText('title', '讲师姓名')
                    ->keyText('host', '讲师所属组织')
//                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('sort', '排序', '数值越大越靠前')
                    ->keySingleImage('cover_id', '讲师头像')
                    ->keyTextArea('content', "讲师简介")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();
            } elseif ($type == 105) { //优秀党员
//                $dzz = $this->cxdzz();
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id', '分类编号')
                    ->keySingleImage('cover_id', '党员头像')
                    ->keyText('title', '姓名')
//                    ->keySelectdzz('host', "党组织",'',$dzz)
                    ->keyText('host', "党组织", '')
                    ->keyText('sort', '排序', '数值越大越靠前')
                    ->keyTextArea("content", "简介")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();
            } elseif ($type == 107 || $type == 109 || $type == 110 || $type == 111 || $type == 113 || $type == 119 || $type == 121 || $type == 123 || $type == 124 || $type == 126 || $type == 108) {
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id', '分类编号')
                    ->keyText('title', '标题')
                    ->keySingleImage('cover_id', '封面')
                    ->keyTime('time', '显示时间')
                    ->keyRichText('content', '内容', '')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();


            } elseif ($type == 122) {
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id', '分类编号')
                    ->keyText('title', '标题')
                    ->keyTextArea('content', '答案')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();
            } elseif ($type == 118) {
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id', '分类编号')
                    ->keyText('title', '标题')
                    ->keySingleImage('cover_id', '封页')
                    ->keyText('addr', '地址')
                    ->keyText('teacher', '讲师')
                    ->keyTime('time', '上课时间')
                    ->keySingleFile('video_id', '视频', '小于100M')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();

            } elseif ($type == 170 || $type == 171 || $type == 173 || $type == 174 || $type == 175 || $type == 176 || $type == 177 || $type == 178 || $type == 179 || $type == 180 || $type == 181 || $type == 182 || $type == 199) {
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id', '分类编号')
                    ->keyText('title', '标题')
                    ->keySingleImage('cover_id', '图片')
                    ->keyText('sort', '排序')
                    ->keyRichText('content', "内容")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();

            } elseif ($type == 174 || $type == 182) {
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id', '分类编号')
                    ->keyText('name', '姓名')
                    ->keySingleImage('cover_id', '头像')
                    ->keyText('sort', '排序')
                    ->keyTextArea('content', "内容")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();
            } elseif ($type >= 183 && $type <=188){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id', '分类编号')
                    ->keyText('title', '标题')
                    ->keyText('sort', '排序')
                    ->keyRichText('content', "内容")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();
            }elseif ($type == 189){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyId('issue_id', '分类编号')
                    ->keyText('title', '标题')
                    ->keySingleImage('cover_id', '图片')
                    ->keyText('sort', '排序')
                    ->keySingleFile('video_id', '视频')
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()
                    ->display();
            }else {
                echo '<script>';
                echo "location.href='/admin/cx/content/type/" . $type . "'";
                echo '</script>';
            }
        }
    }

    /*
     * 设置状态
     */

    public function setWildcardStatus()
    {
        $ids = I('ids');
        $status = I('get.status', 0, 'intval');
        $builder = new AdminListBuilder();
        $builder->doSetStatus('Issue_content', $ids, $status);
    }

    /**
     * 还原
     */
    public function setEditStatus()
    {
        $ids = I('ids');
        $status = 0;
        $builder = new AdminListBuilder();
        $builder->doSetStatus('Issue_content', $ids, $status);
    }

    /**
     * 删除
     */
    public function setDeltteStatus()
    {
        $ids = I('ids');
        $status = -2;
        $builder = new AdminListBuilder();
        $builder->doSetStatus('Issue_content', $ids, $status);
    }

    /*
     * 查看报名人员*/
    public function sign($page = 1, $r = 10, $type = 82, $title = 1, $content_id = null)
    {

        $this->leftlist($type);  //左侧菜单栏
        $builder = new AdminListBuilder();
        $map['status'] = array('egt', 1);
        $sta = array(
            100 => '全部',
            -1 => '审核未通过',
            0 => '待审核',
            1 => '审核通过',
            3 => '用户已取消',
        );
        //按状态查询
        $stt = I('types');
        if ($stt != '' && $stt != 100) {
            $map['state'] = $stt;
        } else {
            $stt = 100;
        }
        $this->assign('sta', $sta); //状态显示
        $this->assign('stt', $stt);//当前状态
        switch ($type) {
            case 82:
                $map['content_id'] = $content_id;
                $count = $this->bespoke->where($map)->count();
                $Page = new Page($count, $r);
                $show = $Page->show();
                $data = $this->bespoke->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order("id desc")->select();
                foreach ($data as $k => &$v) {
                    $v['status_var'] = $this->status[$v['status']];
                    $v['state_var'] = $this->state[$v['state']];
                    $v['source_var'] = $this->source[$v['source']];
                    $v['company_var'] = $this->company[$v['type']];
                }
                $tit = $this->issue_content->where(array('id' => $content_id))->find();
                $this->assign('tit', $tit);  //活动信息
                $this->assign('data', $data); //报名信息
                $this->assign('pagination', $show);
                $this->display('bespoke');
                break;
            case 84: //志愿者报名管理
                $map['content_id'] = $content_id;
                $count = $this->volunteer->where($map)->count();
                $Page = new Page($count, $r);
                $show = $Page->show();
                $data = $this->volunteer->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order("id desc")->select();
                foreach ($data as $k => &$v) {
                    $v['status_var'] = $this->status[$v['status']];
                    $v['state_var'] = $this->state[$v['state']];
                    $v['source_var'] = $this->source[$v['source']];
                    $v['company_var'] = $this->company[$v['type']];
                }
                $tit = $this->issue_content->where(array('id' => $content_id))->find();
//                $count = $this->volunteer->where(array('status'=>1,'state'=>1))->sum('bespoke_num');
                $this->assign('tit', $tit);  //活动信息
                $this->assign('data', $data); //报名信息
                $this->assign('pagination', $show); //分页
                $this->display('volunteer');

                break;
            case 86: //场地预约管理
                $map['content_id'] = $content_id;
                $count = $this->field->where($map)->count();
                $Page = new Page($count, $r);
                $show = $Page->show();
                $data = $this->field->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->order("id desc")->select();
                foreach ($data as $k => &$v) {
                    $v['status_var'] = $this->status[$v['status']];
                    $v['state_var'] = $this->state[$v['state']];
                    $v['source_var'] = $this->source[$v['source']];
                    $v['company_var'] = $this->company[$v['type']];
                }
                $tit = $this->issue_content->where(array('id' => $content_id))->find();
                $this->assign('tit', $tit);  //活动信息
                $this->assign('data', $data); //报名信息
                $this->assign('pagination', $show); //分页
                $this->display('field');
                break;
            case 88: //党课预约管理
                $map['content_id'] = $content_id;
                $count = $this->lecture->where($map)->count();
                $Page = new Page($count, $r);
                $show = $Page->show();
                $data = $this->lecture->where($map)->page($page, $r)->order("id desc")->select();
                foreach ($data as $k => &$v) {
                    $v['status_var'] = $this->status[$v['status']];
                    $v['state_var'] = $this->state[$v['state']];
                    $v['source_var'] = $this->source[$v['source']];
                    $v['company_var'] = $this->company[$v['type']];
                }
                $tit = $this->issue_content->where(array('id' => $content_id))->find();
                $this->assign('tit', $tit);  //活动信息
                $this->assign('data', $data); //报名信息
                $this->assign('pagination', $show); //分页
                $this->display('lecture');
                break;
            case 90:
                $map['content_id'] = $content_id;
                $count = $this->tutor->where($map)->count();
                $Page = new Page($count, $r);
                $show = $Page->show();
                $data = $this->tutor->where($map)->page($page, $r)->order("id desc")->select();
                foreach ($data as $k => &$v) {
                    $v['status_var'] = $this->status[$v['status']];
                    $v['state_var'] = $this->state[$v['state']];
                    $v['source_var'] = $this->source[$v['source']];
                    $v['company_var'] = $this->company[$v['type']];
                }
                $tit = $this->issue_content->where(array('id' => $content_id))->find();
                $this->assign('tit', $tit);  //活动信息
                $this->assign('data', $data); //报名信息
                $this->assign('pagination', $show); //分页
                $this->display('tutor');
                break;
        }
    }

    /*
     * 预约报名新添编辑*/
    public function signedit()
    {
        $id = I('id');
        $examine = I('examine');
        $content_id = I('content_id');
        $title = $id ? "编辑" : "新增";

        $issud_id = $this->issue_content->where(array('id' => $content_id))->getField('issue_id');
        $this->leftlist($issud_id);

        switch ($issud_id) {
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
            if ($data['state'] == '') {
                unset($data['state']);
            }
            switch ($issud_id) {
                case 82:
                    $te = $data['phone'];
                    $tex = '活动预约';


                    $hd = M('issue_content')->where(array('id' => $data['content_id'], 'state' => 1, 'statue' => 1))->find();

                    $co = M('sign_bespoke')->where(array('content_id' => $data['content_id'], 'state' => 1, 'status' => 1))->sum('bespoke_num');

                    if ($data['state'] == 1) {
                        $exa = '通过';
                        if (!$hd) {
                            $this->error("活动已失效");
                        }
                        if ($hd['time'] <= time()) {
                            $this->error("活动已开始或已结束");
                        }
                        if ($hd['num'] < ($co + $data['bespoke_num'])) {
                            $this->error("预约人数过多 ！已预约" . $co . "位！还可以预约" . ($hd['num'] - $co) . "位");
                        }

                    } elseif ($data['state'] == -1) {
                        $exa = '不通过';
                    }
                    break;
                case 84:
                    $te = $data['phone'];
                    $tex = '志愿者活动';
                    $hd = M('issue_content')->where(array('id' => $data['content_id'], 'state' => 1, 'statue' => 1))->find();

                    $co = M('sign_volunteer')->where(array('content_id' => $data['content_id'], 'state' => 1, 'status' => 1))->sum('bespoke_num');

                    if ($data['state'] == 1) {
                        $exa = '通过';
                        if (!$hd) {
                            $this->error("活动已失效");
                        }
                        if ($hd['time'] <= time()) {
                            $this->error("活动已开始或已结束");
                        }
                        if ($hd['num'] < ($co + $data['bespoke_num'])) {
                            $this->error("预约人数过多 ！已预约" . $co . "位！还可以预约" . ($hd['num'] - $co) . "位");
                        }
                    } elseif ($data['state'] == -1) {
                        $exa = '不通过';
                    }
                    break;
                case 86:
                    if ($data['start_time'] > $data['end_time']) {
                        $this->error("结束时间不能小于开始时间");
                    }

                    $te = $data['phone'];
                    $tex = '场馆预约';
                    $hd = M('issue_content')->where(array('id' => $data['content_id'], 'state' => 1, 'statue' => 1))->find();

                    $co = M('sign_field')->where(array('content_id' => $data['content_id'], 'state' => 1, 'status' => 1))->field('start_time,end_time')->select();


                    if ($data['state'] == 1) {
                        $exa = '通过';
                        if (!$hd) {
                            $this->error("场馆已失效");
                        }
                        foreach ($co as $k => &$v) {
                            $s = $this->isMixTime($data['start_time'], $data['end_time'], $v['start_time'], $v['end_time']);
                            if ($s) {
                                $t = true;
                            }
//                            dump($v['start_time']);
//                            dump($v['end_time']);
                        }
//                        dump($data['start_time']);
//                        dump($data['end_time']);
//                        dump($t);
//                            exit;
                        if ($t) {
                            $this->error("预约时间有冲突");
                        }
                    } elseif ($data['state'] == -1) {
                        $exa = '不通过';
                    }


                    break;
                case 88:
                    $te = $data['phone'];
                    $tex = '党课预约';
                    $hd = M('issue_content')->where(array('id' => $data['content_id'], 'state' => 1, 'statue' => 1))->find();

                    $co = M('sign_lecture')->where(array('content_id' => $data['content_id'], 'state' => 1, 'status' => 1))->sum('bespoke_num');

                    if ($data['state'] == 1) {
                        $exa = '通过';
                        if (!$hd) {
                            $this->error("党课已失效");
                        }
                        if ($hd['time'] <= time()) {
                            $this->error("党课已开始或已结束");
                        }
                        if ($hd['num'] < ($co + $data['bespoke_num'])) {
                            $this->error("预约人数过多 ！已预约" . $co . "位！还可以预约" . ($hd['num'] - $co) . "位");
                        }
                    } elseif ($data['state'] == -1) {
                        $exa = '不通过';
                    }
                    break;
                case 90:
                    if ($data['start_time'] > $data['end_time']) {
                        $this->error("结束时间不能小于开始时间");
                    }
                    break;
            }

//            dump($data);exit;
            if ($data["id"]) {
                if ($tab->where(array('id' => $data["id"]))->save($data) !== false) {
                    if ($te && $tex && $exa) {
                        $aa['telephone'] = $te;  //电话
                        $aa['classify'] = 2;
                        $aa['option'] = $tex;//提醒类型
                        $aa['examination'] = $exa;//审批结果
                        $s = _httpClient($aa, 'http://183.131.86.64:8620/home/wxapi/dxtd');
                    }
                    //积分
                    if ($data['state'] == 1) {
                        if ($issud_id == 82) {
                            $a = $tab->where(array('id' => $data["id"]))->find();
                            if ($a['openid']) {
                                $b = M('wxuser')->where(array('openid' => $a['openid']))->setInc('integral', $hd['integral']);
                                if ($b) {
                                    $integral['user_id'] = M('wxuser')->where(array('openid' => $a['openid']))->getField('id');
                                    $integral['openid'] = $a['openid'];
                                    $integral['integral'] = $hd['integral'];
                                    $integral['classif'] = 3;
                                    $integral['time'] = time();
                                    $integral['text'] = '参与活动：' . $hd['title'] . '。';
                                    M('wxuser_integral')->add($integral);
                                }
                            }
                        } elseif ($issud_id == 84) {
                            $a = $tab->where(array('id' => $data["id"]))->find();
                            if ($a['openid']) {
                                $b = M('wxuser')->where(array('openid' => $a['openid']))->setInc('integral', $hd['integral']);
                                if ($b) {
                                    $integral['user_id'] = M('wxuser')->where(array('openid' => $a['openid']))->getField('id');
                                    $integral['openid'] = $a['openid'];
                                    $integral['integral'] = $hd['integral'];
                                    $integral['classif'] = 3;
                                    $integral['time'] = time();
                                    $integral['text'] = '参与活动：' . $hd['title'] . '。';
                                    M('wxuser_integral')->add($integral);
                                }
                            }
                        } elseif ($issud_id == 88) {
                            $a = $tab->where(array('id' => $data["id"]))->find();
                            if ($a['openid']) {
                                $b = M('wxuser')->where(array('openid' => $a['openid']))->setInc('integral', $hd['integral']);
                                if ($b) {
                                    $integral['user_id'] = M('wxuser')->where(array('openid' => $a['openid']))->getField('id');
                                    $integral['openid'] = $a['openid'];
                                    $integral['integral'] = $hd['integral'];
                                    $integral['classif'] = 3;
                                    $integral['time'] = time();
                                    $integral['text'] = '参与党课：' . $hd['title'] . '。';
                                    M('wxuser_integral')->add($integral);
                                }
                            }
                        }
                    }

                    $this->success(L('_SUCCESS_UPDATE_'), '/admin/cx/sign/type/' . $issud_id . '/content_id/' . $content_id);
                } else {
                    $this->error(L('_FAIL_UPDATE_'));
                }
            } else {
                $data['source'] = 3;
                $data['cre_time'] = date('Y-m-d H:i:s', time());
                if ($tab->add($data) !== false) {
                    $this->success("添加成功", '/admin/cx/sign/type/' . $issud_id . '/content_id/' . $content_id);
                } else {
                    $this->error("添加失败");
                }
            }

        } else {
            $content = $this->issue_content->where(array('id' => $content_id))->find();
            if ($id) {
                $map['id'] = $id;
                $map['content_id'] = $content_id;
                $data = $tab->where($map)->find();
            }
            $data['content_id'] = $content_id;
//            dump($data);exit;
            $builder = new  AdminConfigBuilder();
            $builder->title($content['title'] . $title);

            $dzz = $this->cxdzz();
            $this->assign('dzz', $dzz);
            switch ($issud_id) {
                case 82: //党建活动预约管理
                    unset($this->state[0]);
                    $builder->keyId()
                        ->keyId('content_id', '活动编号')
                        ->keyText('name', "姓名")
                        ->keyText('phone', "电话")
                        ->keyText('identity', "身份证")
                        ->keySelectdzz('organization', "党组织", '', $dzz)
                        ->keyText('company', "所属单位")
                        ->keyText('bespoke_num', "预约人数")
                        ->keySelect('types', "预约方式", '', $this->company)
                        ->keyText('text', "备注")
                        ->data($data);
                    break;
                case 84: //志愿者报名管理
                    $builder->keyId()
                        ->keyId('content_id', '活动编号')
                        ->keyText('name', "姓名")
                        ->keyText('phone', "电话")
                        ->keyText('identity', "身份证")
                        ->keySelectdzz('organization', "党组织", '', $dzz)
                        ->keyText('company', "所属单位")
                        ->keyText('bespoke_num', "预约人数")
                        ->keySelect('types', "预约方式", '', $this->company)
                        ->keyText('text', "备注")
                        ->data($data);
                    break;
                case 86: //场地预约管理
                    $builder->keyId()
                        ->keyId('content_id', '场地编号')
                        ->keyText('name', "姓名")
                        ->keyText('phone', "电话")
                        ->keyTime('start_time', "开始时间")
                        ->keyTime('end_time', "结束时间")
                        ->keyText('identity', "身份证")
                        ->keySelectdzz('organization', "党组织", '', $dzz)
                        ->keyText('company', "所属单位")
                        ->keyText('bespoke_num', "预约人数")
                        ->keySelect('types', "预约方式", '', $this->company)
                        ->keyText('text', "备注")
                        ->data($data);
                    break;
                case 88: //党课预约管理
                    $builder->keyId()
                        ->keyId('content_id', '党课编号')
                        ->keyText('name', "姓名")
                        ->keyText('phone', "电话")
                        ->keyText('identity', "身份证")
                        ->keySelectdzz('organization', "党组织", '', $dzz)
                        ->keyText('company', "所属单位")
                        ->keyText('bespoke_num', "预约人数")
                        ->keySelect('types', "预约方式", '', $this->company)
                        ->keyText('text', "备注")
                        ->data($data);
                    break;
                case 90: //讲师预约管理
                    $builder->keyId()
                        ->keyId('content_id', '场地编号')
                        ->keyText('name', "姓名")
                        ->keyText('phone', "电话")
                        ->keyTime('start_time', "开始时间")
                        ->keyTime('end_time', "结束时间")
                        ->keyText('identity', "身份证")
                        ->keySelectdzz('organization', "党组织", '', $dzz)
                        ->keyText('company', "所属单位")
                        ->keyText('bespoke_num', "预约人数")
                        ->keySelect('types', "预约方式", '', $this->company)
                        ->keyText('text', "备注")
                        ->data($data);
                    break;
            }
            if ($examine == 1) {
                $builder->buttonSubmit()
                    ->buttonBack();
            } elseif ($examine == 2) {
                $builder->keySelect('state', "审批", '', $this->state)
                    ->buttonSubmit()
                    ->buttonBack();
            } else {
                $builder
                    ->buttonBack();
            }
            $builder->display();


//            $this->display();

        }


    }


    //检测2时间端是否有交际 ture  有交集    false 无交集

    private function isMixTime($begintime1, $endtime1, $begintime2, $endtime2)
    {
//        $begintime1 = 1111111;
//        $endtime1   = 22222222;
//        $begintime2 = 1111111;
//        $endtime2   = 2222222;

        $status = $begintime2 - $begintime1;
        if ($status > 0) {
            $status2 = $begintime2 - $endtime1;
            if ($status2 > 0) {
                return false;
            } else {
                return true;
            }
        } else {
            $status2 = $begintime1 - $endtime2;
            if ($status2 > 0) {
                return false;
            } else {
                return true;
            }
        }
    }


    /*
     * 预约报名删除报名*/
    public function signdel()
    {
        $type = $_REQUEST['type'];
        switch ($type) {
            case 82 : //党建活动预约管理
                $table = 'sign_bespoke';
                break;
            case 84: //志愿者报名管理
                $table = 'sign_volunteer';
                break;
            case 86 : //场地预约管理
                $table = 'sign_field';
                break;
            case 88 : //党课预约管理
                $table = 'sign_lecture';
                break;
            case 90 : //讲师预约管理
                $table = 'sign_bespoke';
                break;
        }
        $ids = I('id');

        $status = I('get.status', 0, 'intval');
        $builder = new AdminListBuilder();
        $builder->doSetStatus($table, $ids, $status);
    }


    /*
     * 微心愿管理新增
     * 众筹新增管理*/
    public function wxyedit()
    {
        $id = I('id');
        $examine = I('examine');
        $type = $_REQUEST['type'];

        $this->leftlist($type);

        $title = $id ? "编辑" : "新增";
        $sta = array(
//            100=>'全部',
            -1 => '审核未通过',
//            0 => '待审核',
            1 => '审核通过',
//            2 => '已认领',
            3 => '已完成',
//            4=> '用户已取消',
        );
        $obj = array(
            1 => '个人',
            2 => '团体',
            3 => '群众',
        );
        $form = array(
            1 => '物质',
            2 => '精神',
        );
        switch ($type) {
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
            case 114:
                $tab = $this->demand;
                break;
            case 116:
                $tab = $this->gain;
                break;
            case 104:
                $tab = M('sign_heart');;
                break;

        }
        $map['status'] = 1;
        if (IS_POST) {
            $data = $_POST;
            if ($data['state'] == '') {
                unset($data['state']);
            }
            switch ($type) {
                case 92:
                    $te = $data['telephone'];
                    $tex = '微心愿申请';

                    if ($data['state'] == 1) {
                        $exa = '通过';
                        $data['adopt_time'] == time();
                    } elseif ($data['state'] == 2) {
                        $exa = '已认领';
                        $data['claim_time'] == time();
                    } elseif ($data['state'] == 3) {
                        $exa = '已完成';
                        $data['complete_time'] == time();
                    } elseif ($data['state'] == -1) {
                        $exa = '不通过';
                    }
                    break;
                case 93:
                    $te = $data['telephone'];
                    $tex = '微心愿认领';
                    $w['status'] = 1;
                    $w['id'] = $data['wish_id'];
                    if ($data['state'] == 1) {
                        $w['state'] = 1;
                        $exa = '通过';
                    } else if ($data['state'] == -1) {
                        $exa = '不通过';
                    }
                    $wi = M('sign_wish')->where($w)->find();
                    if (!$wi) {
                        $this->error(L('微心愿已认领或已删除'));
                    }
                    break;
                case 96:
                    $te = $data['applytelephone'];
                    $tex = '众筹申报';
                    if ($data['state'] == 1) {
                        $exa = '通过';
                        if ($data['state_time'] >= $data['end_time']) {
                            $this->error(L('开始时间要小于结束时间'));
                        }
                        if ($data['classif'] == 1) {
                            if (empty($data['money'])) {
                                $this->error(L('金钱众筹金额不能小于1'));
                            }
                        } else if ($data['classif'] == 2) {
                            if (empty($data['count'])) {
                                $this->error(L('人员众筹人员不能小于1'));
                            }
                        }
                    } else if ($data['state'] == -1) {
                        $exa = '不通过';
                    }
                    break;
                case 97:
                    if ($data['zhch_id']) {
                        $ch['status'] = 1;
                        $ch['state'] = 1;
                        $ch['id'] = $data['zhch_id'];
                        $zh = M('sign_zhch')->where($ch)->find();
                    }
                    if (!$zh) {
                        $this->error(L('该众筹不存在'));
                    }
                    $comap['status'] = 1;
                    $comap['state'] = array('egt', 1);
                    $comap['zhch_id'] = $data['zhch_id'];
                    $co = M('sign_zhch_apply')->where($comap)->field('sum(count) as counts,sum(money) as moneys')->find();

                    $te = $data['telephone'];
                    $tex = '众筹服务参与';
                    if ($data['state'] == 1) {
                        if ($zh['end_time'] <= time()) {
                            $this->error(L('该众筹已结束'));
                        }
                        $exa = '通过';
                        //众筹金额 判定

                        if ($zh['classif'] == 1) {
                            if (empty($data['money'])) {
                                $this->error(L('金钱众筹金额不能小于1'));
                            }
                            if ($zh['money'] < ($co['moneys'] + $data['money'])) {
                                $this->error(L('认领金额超出众筹金额！' . '总需金额：' . $zh['money'] . '还需金额：' . ($zh['money'] - $co['moneys'])));
                            }
                        } //众筹人员判定
                        else if ($zh['classif'] == 2) {
                            if (empty($data['count'])) {
                                $this->error(L('人员众筹人员不能小于1'));
                            }
                            if ($zh['count'] < ($co['counts'] + $data['count'])) {
                                $this->error(L('参与人数超出众筹人数！' . '总需人数：' . $zh['count'] . '还需人数：' . ($zh['count'] - $co['counts'])));
                            }
                        }
                    } else if ($data['state'] == -1) {
                        $exa = '不通过';
                    }
                    break;
                case 100:
                    $te = $data['telephone'];
                    $tex = '项目直通车';
                    if ($data['state'] == 1) {
                        $exa = '通过';
                    }
                    break;
            }
//            dump($data);exit;
            if ($data["id"]) {
                if ($tab->where(array('id' => $data["id"]))->save($data) !== false) {
                    if ($te && $tex && $exa) {
                        $aa['telephone'] = $te;  //电话
                        $aa['classify'] = 2;
                        $aa['option'] = $tex;//提醒类型
                        $aa['examination'] = $exa;//审批结果
                        $s = _httpClient($aa, 'http://183.131.86.64:8620/home/wxapi/dxtd');
                    }
                    if ($type == 93 && $data['state'] == 1) {
                        M('sign_wish')->where($w)->save(array('state' => 2, 'claim_time' => time()));
                        $a = $tab->where(array('id' => $data["id"]))->find();
                        if ($a['openid']) {
                            $b = M('wxuser')->where(array('openid' => $a['openid']))->setInc('integral', $wi['integral']);
                            if ($b) {
                                $integral['user_id'] = M('wxuser')->where(array('openid' => $a['openid']))->getField('id');
                                $integral['openid'] = $a['openid'];
                                $integral['integral'] = $wi['integral'];
                                $integral['classif'] = 3;
                                $integral['time'] = time();
                                $integral['text'] = '认领微心愿：' . $wi['title'] . '。';
                                M('wxuser_integral')->add($integral);
                            }
                        }
                    }
                    if ($type == 97 && $data['state'] == 1) {
                        $a = $tab->where(array('id' => $data["id"]))->find();
                        if ($a['openid']) {
                            $b = M('wxuser')->where(array('openid' => $a['openid']))->setInc('integral', $zh['integral']);
                            if ($b) {
                                $integral['user_id'] = M('wxuser')->where(array('openid' => $a['openid']))->getField('id');
                                $integral['openid'] = $a['openid'];
                                $integral['integral'] = $zh['integral'];
                                $integral['classif'] = 3;
                                $integral['time'] = time();
                                $integral['text'] = '参与众筹：' . $zh['title'] . '。';
                                M('wxuser_integral')->add($integral);
                            }
                        }

                    }
                    $this->success(L('_SUCCESS_UPDATE_'), '/admin/cx/content/type/' . $type);
                } else {
                    $this->error(L('_FAIL_UPDATE_'));
                }
            } else {
                $data['source'] = 3;
                $data['cre_time'] = time();
                $data['cer_time'] = time();
                if ($tab->add($data) !== false) {
                    $this->success("添加成功", '/admin/cx/content/type/' . $type);
                } else {
                    $this->error("添加失败");
                }
            }

        } else {
            $map['id'] = $id;
            $data = $tab->where($map)->find();
            foreach ($sta as $k => $v) {
                if ($k <= $data['state']) {
                    unset($sta[$k]);
                }
            }
            if ($data['state'] == 0) {
                $sta[-1] = '审核未通过';
            }
            $data['issue_id'] = $type;
            $builder = new AdminConfigBuilder();
            $dzz = $this->cxdzz();
            $dzz = array_merge(array(0 => array('NAME' => '未选择')), $dzz);

            $this->assign('dzz', $dzz);
            switch ($type) {
                case 92: //微心愿申报管理

                    $builder->title('微心愿' . $title)
                        ->keyId()
//                        ->keyHidden('issue_id','')
                        ->keyText('title', '心愿')
                        ->keySingleImage('img', '图片')
                        ->keyText('username', "姓名")
                        ->keyText('telephone', "电话")
                        ->keyText('identity', "身份证")
                        ->keyText('address', "地址")
                        ->keySelectdzz('organization', "党组织", '', $dzz)
                        ->keyTime('start_time', "用户选择时间")
                        ->keySelect('obj', "心愿对象", '', $obj)
                        ->keySelect('form', "心愿形式", '', $form)
                        ->keySelect('party', "党员", '', $this->party)
                        ->keyTextArea('content', "小故事")
                        ->data($data);
                    break;
                case 93: //微心愿认领
                    unset($sta[3]);
                    unset($sta[4]);
                    $da = $this->wish->where(array('id' => $data['wish_id']))->find();
                    $wishs[$da['id']] = $da['title'];
                    $mapwish['status'] = 1;
                    $mapwish['state'] = 1;
                    $wish = $this->wish->where($mapwish)->select();  //当前可用微心愿
                    $wish = array_unique($wish);
                    foreach ($wish as $k => $v) {
                        $wishs[$v['id']] = $v['title'];
                    }
                    $builder->title('微心愿认领' . $title)
                        ->keyId()
//                        ->keyHidden('issue_id','')
                        ->keySelect('wish_id', '申请心愿', '', $wishs)
                        ->keyText('name', '姓名')
                        ->keyText('telephone', '电话')
                        ->keyText('company', "单位")
                        ->keySelect('types', "申请类型", '', $this->company)
                        ->keyText('identity', "身份证")
//                        ->keyText('address', "地址")
                        ->keySelect('party', "党员", '', $this->party)
                        ->keyTextArea('content', "备注")
                        ->data($data);
                    break;
                case 94: //微心愿评价
                    $pj = array(
                        1 => '非常满意',
                        2 => '满意',
                        3 => '一般',
                        4 => '不够满意',
                    );
                    $builder->title('微心愿评价' . $title)
                        ->keySelect('pj', "评价", '', $pj)
                        ->keyTextArea('content', "评价")
                        ->data($data);
                    break;
                case 96: //众筹服务申报管理
                    $typ = array(
                        1 => '金钱众筹',
                        2 => '人员众筹',
                    );
                    unset($sta[2]);
                    $builder->title('项目众筹' . $title)
                        ->keyId()
//                        ->keyHidden('issue_id','')
                        ->keyText('title', '标题')
                        ->keySelect('classif', '众筹类别', '', $typ)
                        ->keyText('money', '金额')
                        ->keyText('count', '人员')
                        ->keyMultiImage('img', '图片')
                        ->keyTime('state_time', '开始参与时间')
                        ->keyTime('end_time', '终止参与时间')
                        ->keyText('applyname', '申请人')
                        ->keyText('applytelephone', '电话')
                        ->keyText('identity', "身份证")
                        ->keyText('address', "地址")
                        ->keySelect('party', "党员", '', $this->party)
                        ->keyTextArea('content', "备注")
                        ->data($data);
                    break;
                case 97: //项目众筹参与
//                    $sta[2] = '已联系';
                    $da = $this->zhch->where(array('id' => $data['zhch_id']))->find();
                    $wishs[$da['id']] = $da['title'];
                    $mapwish['status'] = 1;
                    $mapwish['state'] = 1;
                    $wish = $this->zhch->where($mapwish)->select();  //当前可用微心愿
//                    $wish =array_unique($wish);
                    foreach ($wish as $k => $v) {
                        $wishs[$v['id']] = $v['title'];
                    }
                    $builder->title('项目众筹' . $title)
                        ->keyId()
//                        ->keyHidden('issue_id','')
                        ->keySelect('zhch_id', '申请心愿', '', $wishs)
                        ->keySelect('types', '参与方式', '', $this->company)
                        ->keyText('money', '意向金额')
                        ->keyText('count', '申请人数')
                        ->keyText('company', '单位')
                        ->keyText('name', '姓名')
                        ->keyText('telephone', '电话')
                        ->keyText('identity', "身份证")
                        ->keySelect('party', "党员", '', $this->party)
                        ->keyTextArea('content', "备注")
                        ->data($data);

                    break;
                case 98:
                    $pj = array(
                        1 => '非常满意',
                        2 => '满意',
                        3 => '一般',
                        4 => '不够满意',
                    );
                    $builder->keyId()
                        ->title('众筹评价' . $title)
                        ->keySelect('pj', "评价", '', $pj)
                        ->keyTextArea('content', "评价")
                        ->data($data);
                    break;
                case 99:
                    unset($sta[2]);
                    unset($sta[3]);
                    unset($sta[4]);
                    $builder->keyId()
                        ->title('项目直通车' . $title)
                        ->keyText('title', '标题')
                        ->keyTextArea('desc', '简介')
                        ->keyMultiImage('img', '图片')
                        ->data($data);
                    break;
                case 100:
                    unset($sta[2]);
                    unset($sta[3]);
                    $da = $this->direct->where(array('id' => $data['direct_id']))->find();
                    $wishs[$da['id']] = $da['title'];
                    $mapwish['status'] = 1;
                    $mapwish['state'] = 1;
                    $wish = $this->direct->where($mapwish)->select();

//                    $wish =array_unique($wish);
                    foreach ($wish as $k => $v) {
                        $wishs[$v['id']] = $v['title'];
                    }
//                    dump($wish);exit;
                    $builder->keyId()
                        ->title('项目直通车报名' . $title)
                        ->keySelect('direct_id', '申请心愿', '', $wishs)
                        ->keySelect('types', '参与方式', '', $this->company)
                        ->keyText('company', '单位')
                        ->keyText('name', '姓名')
                        ->keyText('telephone', '电话')
                        ->keyText('identity', "身份证")
                        ->keySelect('party', "党员", '', $this->party)
                        ->keyTextArea('content', "备注")
                        ->data($data);
                    break;
                case 101:
                    $pj = array(
                        1 => '非常满意',
                        2 => '满意',
                        3 => '一般',
                        4 => '不够满意',
                    );
                    $builder->keyId()
                        ->title('项目直通车评价' . $title)
                        ->keySelect('pj', "评价", '', $pj)
                        ->keyTextArea('content', "评价")
                        ->data($data);
                    break;

                case 114:
                    $builder->keyId()
                        ->title('项目直通车需求' . $title)
                        ->keyText('name', "姓名")
                        ->keyText('telephone', "电话")
                        ->keyTextArea('content', "需求")
                        ->data($data);
                    break;
                case 116:
                    $builder->keyId()
                        ->title('党员心得' . $title)
                        ->keyText('name', "姓名")
                        ->keyText('telephone', "电话")
                        ->keyText('identity', "身份证")
                        ->keyTime('time', "时间")
                        ->keyText('sort', "排序", '越大越靠前')
                        ->keyTextArea('content', "心得")
                        ->data($data);
                    break;
                case 104:
                    $builder->keyId()
                        ->title('党员心声' . $title)
                        ->keyText('name', "姓名")
                        ->keyText('telephone', "电话")
                        ->keyText('identity', "身份证")
                        ->keyTime('time', "时间")
                        ->keyText('sort', "排序", '越大越靠前')
                        ->keyTextArea('content', "心得")
                        ->data($data);
                    break;
            }
            if ($examine == 1) {
                $builder->buttonSubmit()
                    ->buttonBack();
            } elseif ($examine == 2) {
                if ($type == 92 || $type == 96 || $type == 99) {
                    $builder->key('integral', '活动积分', '整数 默认1', 'number');
                }
                if ($type == 92) {
                    if ($data['state'] == -1) {
                        unset($sta[-1]);
                        unset($sta[3]);
                    } elseif ($data['state'] == 1 || $data['state'] == 2) {
                        unset($sta[1]);
                        unset($sta[-1]);
                    }
                }
                $builder->keySelect('state', "审批", '', $sta)
                    ->buttonSubmit()
                    ->buttonBack();
            } else {
                $builder
                    ->buttonBack();
            }
            $builder->display();

        }


    }

    /*
     * 微心愿删除*/
    public function wxydel()
    {
        $type = $_REQUEST['type'];
        switch ($type) {
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
            case 114 : //场地预约管理
                $table = 'sign_direct_demand';
                break;
            case 116 : //场地预约管理
                $table = 'sign_gain';
                break;
        }
        $status = I('get.status', 0, 'intval');
        $state = I('get.state', 0, 'intval');
        $ids = I('id');
        $builder = new AdminListBuilder();
        if ($status) {
            $builder->doSetStatus($table, $ids, $status);
        } elseif ($state || $state == 0) {
            $builder->doSetState($table, $ids, $state);

        }


    }


    /*左侧菜单栏*/
    private function leftlist($type)
    {
        $category = M('issue');
        $groupid = session('GroupId');
        $user = session('user_auth');
        $uid = $user['uid'];


        if ($type) {
            $fatherId1 = $category->where('id=' . $type)->getField('pid');

            $fatherId2 = $category->where('id=' . $fatherId1)->getField('pid');

            $fatherId3 = $category->where('id=' . $fatherId2)->getField('pid');

            $this->assign('type', $type);
        }

        $this->assign('actionname', 'content');
        $this->assign('accontroller', CONTROLLER_NAME);


        if (CONTROLLER_NAME == 'Cx') {
            $mapcontent['status'] = 1;
            if ($groupid == 3) {
                $mapcontent['group_id'] = array('like', '%3%');
            } elseif ($groupid == 10) {
                $mapcontent['group_id'] = array('like', '%10%');
            }
            $mapcontent['lv'] = 0;
//            $mapcontent['project_category'] = 0;
            $issuemenus = $category->where($mapcontent)->field(true)->order('sort asc')->select();
//            $aa = M()->getLastsql();
//            dump($aa);die;
//            dump( $issuemenus);die;
            foreach ($issuemenus as $k => $v) {
                $mapcontent['lv'] = 1;
//                if($groupid == 11){
//                    $mapcontent['group_id']=11;
//                }elseif ($groupid == 10){
//                    $mapcontent['has_jw']=1;
//                }
                $mapcontent['pid'] = $v['id'];
//                $mapcontent['project_category'] = 0;
                $issuemenus1 = $category->where($mapcontent)->field(true)->order('sort asc')->select();
                $issuemenus[$k]['issuemenus'] = $issuemenus1;
//                dump( $issuemenus[$k]['issuemenus']);
                $issuemenus[$k]['count'] = count($issuemenus1);
                if ($v['id'] == $fatherId1 || $v['id'] == $fatherId2 || $v['id'] == $fatherId3) $issuemenus[$k]['class'] = ' open in ';
                if ($v['id'] == $type) $issuemenus[$k]['class'] = 'active';

                foreach ($issuemenus[$k]['issuemenus'] as $k1 => $v1) {
                    $mapcontent['lv'] = 2;
//                    if($groupid == 11){
//                        $mapcontent['group_id']=11;
//                    }elseif ($groupid == 10){
//                        $mapcontent['has_jw']=1;
//                    }
                    $mapcontent['pid'] = $v1['id'];
//                    $mapcontent['project_category'] = 0;
                    $issuemenus2 = $category->where($mapcontent)->field(true)->order('sort asc')->select();
                    $issuemenus[$k]['issuemenus'][$k1]['issuemenus'] = $issuemenus2;
//                    dump($issuemenus[$k]['issuemenus'][$k1]['issuemenus']);
                    $issuemenus[$k]['issuemenus'][$k1]['count'] = count($issuemenus2);
                    if ($v1['id'] == $fatherId1 || $v1['id'] == $fatherId2 || $v1['id'] == $fatherId3) {
                        $issuemenus[$k]['issuemenus'][$k1]['class'] = ' open in ';
                    }

                    $issuemenus[$k]['issuemenus'][$k1]['url'] = '/admin/cx/content/type/' . $type;


                    if ($v1['id'] == $type) $issuemenus[$k]['issuemenus'][$k1]['class'] = 'active';
                    foreach ($issuemenus[$k]['issuemenus'][$k1]['issuemenus'] as $k2 => $v2) {
                        $mapcontent['lv'] = 3;
//                        if($groupid == 11){
//                            $mapcontent['group_id']=11;
//                        }elseif ($groupid == 10){
//                            $mapcontent['has_jw']=1;
//                        }
                        $mapcontent['pid'] = $v2['id'];
//                        $mapcontent['project_category'] = 0;
                        $issuemenus3 = $category->where($mapcontent)->field(true)->order('sort asc')->select();
                        $issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['issuemenus'] = $issuemenus3;
                        $issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['count'] = count($issuemenus3);
                        if ($v1['id'] == $fatherId1 || $v1['id'] == $fatherId2 || $v1['id'] == $fatherId3) {
                            $issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['class'] = ' open in ';
                        }
                        if ($v2['id'] == $type) $issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['class'] = 'active';
                        foreach ($issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['issuemenus'] as $k3 => $v3) {
                            if ($v3['id'] == $type) $issuemenus[$k]['issuemenus'][$k1]['issuemenus'][$k2]['issuemenus'][$k3]['class'] = 'active';
                        }
                    }
                }

            }

//            dump($issuemenus);
//            die;
//            dump($issuemenus[0]['issuemenus'][1]);exit;
            $this->assign('menutree', 1);
            $this->assign('issuemenus1', $issuemenus);
//            dump($issuemenus[0]);exit;
        }
    }

    /*
     * 党组织树形数组
     * */
    private function cxdzz()
    {
        if ($_SESSION['cxdzzJSON']) {
            return $_SESSION['cxdzzJSON'];
        }
        $data = M('ajax_dzz')->field('BRANCH_ID,NAME,PARENT_ID')->order('PARENT_ID asc')->select();
        $datas = $this->make_tree1($data, 'BRANCH_ID', 'PARENT_ID');
        $list = $datas[0]['_child'][0]['_child'][0]['_child'][0]['_child'];
        $_SESSION['cxdzzJSON'] = $list;
//        $list = json_encode($datas[0]['_child'][0]['_child'][1]['_child'][0]);
//        echo json_encode($list);
//       dump($list);exit;
        return $list;
    }

    /*
     * 无限极分类*/
    private function make_tree1($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
    {
        $tree = array();
        foreach ($list as $key => $val) {
            if ($val[$pid] == $root) {
                //获取当前$pid所有子类
                unset($list[$key]);
                if (!empty($list)) {
                    $child = $this->make_tree1($list, $pk, $pid, $child, $val[$pk]);
                    if (!empty($child)) {
                        $val['_child'] = $child;
                    }
                }
                $tree[] = $val;
            }
        }
        return $tree;
    }

    /*
     * 党组织接口*/
    public function branchList()
    {
        $url = 'http://www.dysfz.gov.cn/apiXC/branchList.do';//党组织
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 1500;
        for ($i = 1; $i < 10; $i++) {
            $da['START'] = $i;
            $das = json_encode($da);
            $list = httpjson($url, $das);
            foreach ($list['data'] as $k => &$v) {
                $dzz = M('ajax_dzz')->where(array('BRANCH_ID' => $v['BRANCH_ID']))->find();
                if ($v['PICTURE']) {
                    $v['PICTURE'] = 'http://www.dysfz.gov.cn/' . $v['PICTURE'];
                }
                if ($dzz['id']) {
                    M('ajax_dzz')->where(array('BRANCH_ID' => $v['BRANCH_ID']))->save($v);
                } else {
                    M('ajax_dzz')->add($v);
                }
            }
            echo $i . '+++++';
        }

    }

    /*
     * 党员接口更新*/
    public function partyList()
    {
        exit;
        set_time_limit(0);
        $url = 'http://www.dysfz.gov.cn/apiXC/partyList.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 1000;
//        dump(1);exit;
        for ($i = 1; $i < 38; $i++) {
            $da['START'] = $i;
            $das = json_encode($da);
            $list = httpjson($url, $das);
            if (empty($list['data'])) {
                dump($i);
                exit;
            }
            echo $i;
//            dump($list);exit;
            foreach ($list['data'] as $k => &$v) {
                $user = M('ajax_user')->where(array('PARTY_ID' => $v['PARTY_ID']))->find();
                if ($v['PHOTO']) {
                    $v['PHOTO'] = 'http://www.dysfz.gov.cn/' . $v['PHOTO'];
                }
                if ($user['id']) {
                    M('ajax_user')->where(array('PARTY_ID' => $v['PARTY_ID']))->save($v);
                } else {
                    M('ajax_user')->add($v);
                }
            }
        }
    }


    public function partyList2()
    {
        $url = 'http://www.dysfz.gov.cn/apiXC/getReportInfo'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 10;
        $da['BRANCH_ID'] = 92;
        for ($i = 1; $i < 147; $i++) {
            $da['START'] = $i;
            $das = json_encode($da);
            $list = httpjson($url, $das);
            foreach ($list['data'] as $k => $v) {
                $id = M('dr_dzz_dhl')->where(array('BRANCH_ID' => $v['BRANCH_ID']))->find();
                if ($id) {
                    M('dr_dzz_dhl')->where(array('BRANCH_ID' => $v['BRANCH_ID']))->save($v);
                } else {
                    M('dr_dzz_dhl')->add($v);
                }
            }
        }
    }


    public function organization()
    {
        $url = 'http://www.dysfz.gov.cn/apiXC/getHeldZTDRlistPage.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 100;
        $da['START'] = 2;
        $das = json_encode($da);
        $list1[] = httpjson($url, $das);
        dump($list1);die;
        $count = $list1['0']['totalCount'];
        $pagenum = ceil($count/$da['COUNT']);
        for ($i = 1; $i <= $pagenum; $i++) {
            $da['START'] = $i;
            $das = json_encode($da);
            $list[] = httpjson($url, $das);
            foreach ($list[$i-1]['data'] as $k=>$v){
                $data[] = $v;

            }
        }

        dump($data);die;

    }

    public function activity()
    {
        $url = 'http://www.dysfz.gov.cn/apiXC/getHeldZtdrlistDetaiPage.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['BRANCH_ID'] = 96;
        $da['TYPE'] = 2;
        $da['START'] = 3;
        $da['COUNT'] = 100;
        $das = json_encode($da);
        $list1[] = httpjson($url, $das);
        dump($list1);die;
        $count = $list1['0']['totalCount'];
    }

    public function activitydetail()
    {
        $url = 'http://www.dysfz.gov.cn/apiXC/ztdrActivityDetai.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['ACTIVITY_ID'] = 36058;
        $das = json_encode($da);
        $list1 = httpjson($url, $das);
        dump($list1);die;
        $count = $list1['0']['totalCount'];
    }



    public function volunteer()
    {
        $url = 'http://www.dysfz.gov.cn/apiXC/volunteerList.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 300;
        $da['START'] = 1;
        $das = json_encode($da);
        $list = httpjson($url, $das);
        dump($list);die;
        for ($i = 1; $i < 147; $i++) {
            $da['START'] = $i;
            $das = json_encode($da);
            $list = httpjson($url, $das);
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
        $url = 'http://www.dysfz.gov.cn/apiXC/volunteerList.do'; //党员党建
        $da['DYSFZ_TOKEN'] = '7a0f6dc987354a563836f14b33f977ee';
        $da['COUNT'] = 100;
//        $da['START'] = 1;
//        $das = json_encode($da);
//        $list = httpjson($url, $das);
//        dump($list['data']);die;
        for ($i = 1; $i < 147; $i++) {
            $da['START'] = $i;
            $das = json_encode($da);
            $list = httpjson($url, $das);
            foreach ($list['data'] as $k => $v) {
                if($v['TYPE'] == 3){
                    foreach ($v['DetailsRecordList'] as $k=>$v){
                        $items[] = $v;
                    }
                }
            }

            foreach ($items as $k=>$v){
                $id = M('wxy_party')->where(array('RECORDV_ID' => $v['RECORDV_ID']))->find();
                if ($id) {
                    M('wxy_party')->where(array('RECORDV_ID' => $v['RECORDV_ID']))->save($v);
                } else {
                    $datas['PARTY_NAME'] = $v['PARTY_NAME'];
                    $datas['VOLUNTEER_ID'] = $v['VOLUNTEER_ID'];
                    $datas['RECORDV_ID'] = $v['RECORDV_ID'];
                    $datas['CREATE_TIME'] = $v['CREATE_TIME'];
                    M('wxy_party')->add($datas);
                }
            }

        }

    }

    function unescape($str){
        $ret = '';
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++){
            if ($str[$i] == '%' && $str[$i+1] == 'u'){
                $val = hexdec(substr($str, $i+2, 4));
                if ($val < 0x7f) $ret .= chr($val);
                else if($val < 0x800) $ret .= chr(0xc0|($val>>6)).chr(0x80|($val&0x3f));
                else $ret .= chr(0xe0|($val>>12)).chr(0x80|(($val>>6)&0x3f)).chr(0x80|($val&0x3f));
                $i += 5;
            }elseif ($str[$i] == '%'){
                $ret .= urldecode(substr($str, $i, 3));
                $i += 2;
            }else{
                $ret .= $str[$i];
            }
        }
        return $ret;
    }
    //按年月计算时间戳 起始和结束
    function mFristAndLast($y = "", $m = ""){
        if ($y == "") $y = date("Y");
        if ($m == "") $m = date("m");
        $m = sprintf("%02d", intval($m));
        $y = str_pad(intval($y), 4, "0", STR_PAD_RIGHT);

        $m>12 || $m<1 ? $m=1 : $m=$m;
        $firstday = strtotime($y . $m . "01000000");
        $firstdaystr = date("Y-m-01", $firstday);
        $lastday = strtotime(date('Y-m-d 23:59:59', strtotime("$firstdaystr +1 month -1 day")));

        return array(
            "firstday" => $firstday,
            "lastday" => $lastday
        );
    }

}