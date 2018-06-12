<?php
namespace Admin\Controller;
use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;

class VenueController extends AdminController
{
    public function contents($page = 1, $r = 10)
    {
        //读取列表
        header("Content-type:text/html;charset=UTF-8");
        $map = array('status' => 1);
        $model = M('dp_team');

        $map['status'] = array('eq','1'); //公益需求
        $list = $model->where($map)->page($page, $r)->order('cre_time desc')->select();
        foreach ($list as $k=>&$v){
            $v['date'] = date('Y-m-d H:i');
        }

        unset($li);
        $totalCount = $model->where($map)->count();

        $builder = new AdminListBuilder();

        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';

        $builder->title(L('_CONTENT_MANAGE_'))
            ->buttonNew(U('editContents'))
            ->setStatusUrl(U('setWildcardStatus'))
//            ->buttonDisable('', L('_AUDIT_UNSUCCESS_'))
            ->buttonDelete()
            ->keyId()
            ->keyText("name", "来访单位")
            ->keyText("num", "来访人数")
            ->keyText("date", "时间")
            ->keyDoActionEdit("editContents?id=###")
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    public function editContents()
    {
        $id = I('id');
        $title = $id ? "编辑" : "新增";
        if (IS_POST) {
            $data = $_POST;
            $data['status'] = 1;

//            dump($data);die;
            $admindzz = session("dzzid");
            $data['year'] = date('Y',$data['date']);
            $data['month'] = date('m',$data['date']);
            $data['day'] = date('d',$data['date']);
            $user = session('user_auth');
            if($admindzz) {
                $data["dzz"] = $admindzz;
            }

            $m = M('dp_team');
            if ($data["id"]) {

                $data['up_time'] = date('Y-m-d H:i:s',time());
                if ($m->save($data) !== false) {
                    $this->success(L('_SUCCESS_UPDATE_'), U('Venue/contents'));
                } else {
                    $this->error(L('_FAIL_UPDATE_'));
                }
            } else {
                $data['cre_time'] = date('Y-m-d H:i:s',time());
                if ($m->add($data) !== false) {
                    $this->success("添加成功", U('Venue/contents'));
                } else {
                    $this->error("添加失败");
                }
            }

        } else {

            if ($id) {
                $map['id'] = $id;
                $data = M('dp_team')->where($map)->find();
            }
            $data['type'] = 1;
            $builder = new AdminConfigBuilder();//http://hqdj.cmlzjz.com/home/ljz/hdlist
            $builder->title($title . "来访单位")
                ->keyId()
                ->keyText('name', "来访单位")
                ->keyText('num', "来访人数")
                ->keyTime('date', "来访日期")
                ->data($data)
                ->buttonSubmit()
                ->buttonBack()
                ->display();
        }
    }


    public function contentTrash($page = 1, $r = 10, $model = '')
    {

        //读取微博列表
        $builder = new AdminListBuilder();
        $builder->clearTrash($model);
        $map = array('status' => -1);
        $model = D('dp_team');
        $list = $model->where($map)->page($page, $r)->select();
        $totalCount = $model->where($map)->count();

        //显示页面
        $builder->title(L('_CONTENT_TRASH_'))
            ->setStatusUrl(U('setWildcardStatus'))
            ->buttonRestore()
            ->buttonClear('dp_team')
            ->keyId()
            ->keyText('name','来访单位')
            ->keyText('date', "时间")
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    //来访人员统计分析
    public function echart()
    {
        $Model = M('dp_team');
        $res = $Model-> field('num')->group('name')->select();

        echo json_encode(array('status'=>1,'msg'=>'请求成功','data'=>$res));
//        $this->display('chart');
    }


    /*通用方法*/
    public function setWildcardStatus(){
        $ids = I('ids');
        $status = I('get.status', 0, 'intval');
        $builder = new AdminListBuilder();
        $builder->doSetStatus('dp_team', $ids, $status);
    }


}