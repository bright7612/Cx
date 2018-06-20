<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 9:13
 */

namespace  Cx\Controller;
use Think\Controller;
class EducationController extends Controller
{
    public function index()
    {

        $this->display('index');
    }


    public function videoList()
    {

        $this->display('video_list');
    }

    public function study_list()
    {

        $this->display('study_list');
    }

    public function detail()
    {

        $this->display('detail');
    }

    public function teacherList()
    {

        $this->display('teacher_list');
    }

    public function teacherDetail()
    {

        $this->display('teacher_detail');
    }

    public function yugao()
    {
        $this->display('class_foreshow');
    }

    public function huigu()
    {
        $this->display('class_history');
    }

    public function book()
    {
        $this->display('book_list');
    }

    public function study_form()
    {
        $this->display('study_form');
    }

}