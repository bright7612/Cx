<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/16
 * Time: 15:42
 */
namespace Time\Controller;
use Think\Controller;
class WgController extends Controller
{
    public function event()
    {
        $Model = M('ZZB_WG','CIGPROXY','DB_M');
        $data = $Model->select();
        dump($data);
    }
}