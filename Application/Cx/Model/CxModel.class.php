<?php
namespace Cx\Model;
use Think\Model;
class CxModel extends Model
{
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
                                    ljz_issue_content AS content
                                JOIN ljz_bespoke AS bespoke ON bespoke.content_id = content.id
                                WHERE
                                    `status` = 1
                                AND
                                     issue_id = $category_id");
       return $res;
    }
}