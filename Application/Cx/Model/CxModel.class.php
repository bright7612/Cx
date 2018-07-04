<?php
namespace Cx\Model;
use Think\Model;
class CxModel extends Model
{
    //党建活动预约
    public function mapCommon($category_id = 1)
    {
        $Model = M('issue_content');
        $res = $Model->query("SELECT
                                    content.id,
                                    content.title,
                                    content.content,
                                    content.time,
                                    content.addr,
                                    content.`host`,
                                    content.teacher,
                                    content.num,
                                    sum(bespoke.bespoke_num) AS y_num,
                                    (content.num-sum(bespoke.bespoke_num)) AS k_num
                                
                                FROM
                                    cxdj_issue_content AS content
                                JOIN cxdj_sign_bespoke AS bespoke ON bespoke.content_id = content.id
                                WHERE
                                    content.`status` = 1
                                AND
                                     content.id = $category_id");
       return $res;

    }
    //主题党课预约
    public function classCommon($category_id = 1)
    {
        $Model = M('issue_content');
        $res = $Model->query("SELECT
                                    content.id,
                                    content.title,
                                    content.content,
                                    content.time,
                                    content.addr,
                                    content.`host`,
                                    content.teacher,
                                    content.num,
                                    sum(lecture.bespoke_num) AS y_num,
                                    (content.num-sum(lecture.bespoke_num)) AS k_num
                                
                                FROM
                                    cxdj_issue_content AS content
                                JOIN cxdj_sign_lecture AS lecture ON lecture.content_id = content.id
                                WHERE
                                    content.`status` = 1
                                AND
                                     content.id = $category_id");
        return $res;

    }

}