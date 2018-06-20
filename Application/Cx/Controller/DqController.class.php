<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/18
 * Time: 19:55
 */

namespace Cx\Controller;
use Think\Controller;
class DqController extends Controller
{
    public function index()
    {

        $this->display('index');
    }

    public function articleList($type)
    {
        if($type == 1){
            $this->display('union_news');
        }

        if($type == 2){
            $this->display('union_news1');
        }

        if($type == 3){
            $this->display('union_news3');
        }

        if($type == 4){
            $this->display('union_news3');
        }

    }

    public function detail()
    {
            $this->display('detail');
    }

    public function category()
    {

        $this->display('menu');
    }

    public function resource()
    {

        $this->display('resource');
    }

    public function san_class()
    {

        $this->display('class');
    }

    public function dangjian()
    {
        $this->display('dangjian');
    }

    public function honor()
    {
        $this->display('honor');
    }

    public function card()
    {
        $this->display('card');
    }

    public function union()
    {

        $this->display('union');
    }

    public function register()
    {

        $this->display('register');
    }


    public function fw()
    {

        $this->display('fw');
    }

}