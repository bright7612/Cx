<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;

class DywxqController extends AdminController
{
    public function contents($page = 1, $r = 10)
    {
        //读取列表
        header("Content-type:text/html;charset=UTF-8");
        $map = array('status' => 1);
        $model = M('wxuser');

        $map['wall'] = array('eq','1'); //公益需求
        $list = $model->where($map)->page($page, $r)->order('first_time desc')->select();
        foreach ($list as $k=>&$v){
            if($v['sex'] == 1){
                $v['sex'] = '男';
            }elseif ($v['sex'] == 2){
                $v['sex'] = '女';
            }elseif ($v['sex'] == 0){
                $v['sex'] = '';
            }
            $v['first_time'] = date('Y-m-d h:i',$v['first_time']);
        }
        unset($li);
        $totalCount = $model->where($map)->count();

        $builder = new AdminListBuilder();

        $attr['class'] = 'btn ajax-post';
        $attr['target-form'] = 'ids';
        $list = $this->issue_title($list);

        $builder->title(L('_CONTENT_MANAGE_'))
            ->setStatusUrl(U('setWildcardStatus'))
//            ->buttonDisable('', L('_AUDIT_UNSUCCESS_'))
//            ->buttonDelete()
            ->keyId()
            ->keyText("nickname", "昵称")
            ->keyText("sex", "性别")
            ->keyImage("headimgurl", "头像",array('width'=>50,'height'=>50))
            ->keyText("province", "省")
            ->keyText("city", "城市")
            ->keyText("first_time", "参加活动时间")
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();
    }

    /**
     * 获取分类名称
     * @param $list
     * @return mixed
     */
    public function issue_title($list){
        $issue = M('issue');
        foreach ($list as $k=>$v){
            $list[$k]['category'] = $issue->where(array('id'=>$v['issue_id']))->getField('title');
            $list[$k]['uid'] = "admin";
        }
        return $list;
    }

}