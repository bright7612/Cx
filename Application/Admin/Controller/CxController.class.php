<?php
namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
class CxController extends AdminController{
    public function content($page = 1, $r = 10,$type='1',$title=1)
    {
        $category = M('issue');
        $groupid = session('GroupId');
        $user = session('user_auth');
        $uid = $user['uid'];


        echo $groupid . '<br/>';
        echo  $uid;

        if($type){
            $fatherId1 =  $category->where('id='.$type)->getField('pid');

            $fatherId2 =  $category->where('id='.$fatherId1)->getField('pid');

            $fatherId3 =  $category->where('id='.$fatherId2)->getField('pid');

            $this->assign('type',$type);
        }

        $this->assign('actionname',ACTION_NAME);



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
//        var_dump($issuemenus[0]['issuemenus'][0]);exit;
        //读取列表
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $map['status'] = 1;
        if($type){
            $map['issue_id'] = $type;
        }

        $model = M('Issue_content');
        $map['status'] = $title;
        $map['uid'] = $uid;
        $list = $model->where($map)->page($page, $r)->order("sort asc,id desc")->select();

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


        $builder->button('新增', array('class' => 'btn btn-success','href' => U('admin/Cx/addcontent?type='.$type)));
        $builder->button('已发布列表', array('class' => 'btn btn-info','href' => U('admin/Cx/content?title=1&type='.$type)))
            ->button('编辑中列表', array('class' => 'btn btn-warning','href' => U('admin/Cx/content?title=0&type='.$type)))
            ->button('已删除列表', array('class' => 'btn btn-danger','href' => U('admin/Cx/content?title=-1&type='.$type)))

            ->keyId()
            ->keyText('link','标题')
//            ->keyStatus()->keyText('sort','排序')
            ->keyText('issue_name','分类')->keyCreateTime()
            ->keyDoActionEdit("admin/Cx/addcontent/type/0/type/".$type.'?id=###')
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

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

            $data['edit_time'] = time();
            $Model = M('Issue_content');

//            if($groupid == 10){
//                $data['project_category'] = $uid;
//            }


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

            $this->assign('actionname',ACTION_NAME);

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
//            $select = array('0'=>'请选择','1'=>'宣传片','2'=>'合庆专题之路','3'=>'合庆镇环境综合整治治理行动纪实');

            if($type >= 28 && $type <=37) {
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('title', '标题')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('sort','排序')
                    ->keyMultiImage('cover_id', "图片")
                    ->keyRichText('content', "内容")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 6 ) {
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('title', '标题')
//                ->keySelect("issue_id", "分类", "", $opt)
                    ->keySelect("issue_id", "分类", "", $options)
//                    ->keyText('date', '日期', '周一至周日')
//                    ->keyText('time', '时间')
//                    ->keyText('address', '活动地址')
                    ->keyText('sort', '排序')
//                    ->keyMultiImage('cover_id', "图片")
//                    ->keyText('link', '弹出链接')
                    ->keySingleFile('video_id', '视频')
                    ->keyBool('video_status', "是否设置为首页播放")
                    ->keySelect('video_type', '选择视频类别', '', $select)
                    ->keyRichText('content', "内容")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 45 || $type == 60 || $type == 61 || $type == 71 || $type == 72) {
//                dump($_SESSION);exit;
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('title', '标题')
                    ->keyText('time', '案例时间')
                    ->keyText('source', '来源')
//                    ->keySelect("system", "选择制度分类", "", $select1)
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('sort', '排序')
//                    ->keyMultiImage('cover_id', "图片")
                    ->keyRichText('content', "内容")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 120){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('title', '标题')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('sort', '排序')
//                   ->keyBool('hotmessage_status','是否关闭热点信息')
//                    ->keySingleImage('cover_id', "图片")
                    ->keyRichText('content', "内容")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }elseif($type == 124){
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('title', '标题')
                    ->keySelect("issue_id", "分类", "", $options)
                    ->keyText('sort', '排序')
                    ->keyMultiImage('cover_id', "图片")
//                    ->keyBool('video_status', "是否设置为首页播放")
//                    ->keyRichText('content', "内容")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();

            }else{
                $builder->title($act . "内容")
                    ->keyId()
                    ->keyText('title', '标题')
                    ->keySelect("issue_id", "分类", "", $options)
//                    ->keyText('date', '日期', '周一至周日')
//                    ->keyText('address', '活动地址')
                    ->keyText('sort', '排序')
                    ->keyText('time', '时间')
                    ->keyText('host','组织单位')
                    ->keyText('teacher','授课老师')
                    ->keyText('num','活动人数')
//                    ->keySingleImage('cover_id', "图片")
//                    ->keyText('link', '弹出链接')
//                    ->keySingleFile('video_id', '视频')
//                    ->keyBool('video_status', "是否设置为首页播放")
//                    ->keySelect('video_type', '选择视频类别', '', $select)
                    ->keyBDMap('addr|lat|lng','地址')
                    ->keyRichText('content', "内容")
                    ->data($data)
                    ->buttonSubmit()
                    ->buttonBack()->display();
            }
        }
    }

    /*
     * 设置状态
     *
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



}