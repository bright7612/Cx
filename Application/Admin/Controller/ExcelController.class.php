<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/8
 * Time: 9:53
 */
namespace Admin\Controller;
use Think\Controller;

class ExcelController extends Controller
{
    //导入模板下载
    public function excel($name){
        $filename = $name;
        $filepath = "/Public/Excel/".$filename.'.xls';
        header("Location: $filepath");
    }

    //党员-已展开主题党日---党员-报道登记2018报道
    public function import_dy_ztdr(){
        $file = $_FILES['file']['tmp_name'];
        $dataa = import_excel_m($file);
        $Model = M('dr_dy_ztdr');
        foreach ($dataa as $key=>$value){
            $data['name'] = $value['0'];
            $data['organization'] = $value['1'];
            $data['title'] = $value['2'];

            if(is_float($value['3'])){
                $data['time'] = date('Y/m/d',\PHPExcel_Shared_Date::ExcelToPHP($value['3']));
            }else{
                $data['time'] = $value['3'];
            }
            $data['status'] = 1;
            $data['cre_time'] = time();
            $record = $Model->add($data);
        }
        if($record){
            echo json_encode(array('status'=>1,'msg'=>'成功'));
        } else {
            echo json_encode(array('status'=>-1,'msg'=>'失败'));
        }

    }

    //党员---已展开主题党日导出
    public function excel_out_ztdr()
    {
        //导出对应标题
        $title = array('序号','姓名','所属党组织','主题','时间');
        $data = M("dr_dy_ztdr")->where('status = 1')->field('id,name,organization,title,time')->select();
        array_unshift($data,$title);
        create_xls($data,'已展开主题党日');
    }


     //党员---未展开主题党日导入
    public function import_dy_unztdr()
    {
        vendor("PHPExcel.PHPExcel");
        $file = $_FILES['file']['tmp_name'];
        $dataa = import_excel_m($file);
        $Model = M('dr_dy_unztdr');
        foreach ($dataa as $key => $value) {
            $data['name'] = $value['0'];
            $data['organization'] = $value['1'];
            if(is_float($value['2'])){
                $data['time'] = date('Y/m/d',\PHPExcel_Shared_Date::ExcelToPHP($value['2']));
            }else{
                $data['time'] = $value['2'];
            }

            $data['status'] = 1;
            $data['cre_time'] = time();
            $record = $Model->add($data);
        }
        if ($record) {
            echo json_encode(array('status' => 1, 'msg' => '成功'));
        } else {
            echo json_encode(array('status' => -1, 'msg' => '失败'));
        }
    }

    //党员---已展开主题党日导出
    public function excel_out_unztdr()
    {
        //导出标题
        $title = array('序号','姓名','所属党组织','时间');
        $data = M("dr_dy_unztdr")->where('status = 1')->field('id,name,organization,time')->select();
        array_unshift($data,$title);
        create_xls($data,'未展开主题党日.xls');

    }

    //报道登记-不计入到会党员
    public function excel_import_late()
    {
        vendor("PHPExcel.PHPExcel");
        $file = $_FILES['file']['tmp_name'];
        $dataa = import_excel_m($file);
        $Model = M('dr_dy_late');
        foreach ($dataa as $key => $value) {
            $data['name'] = $value['0'];
            $data['organization'] = $value['1'];
            $data['ID_CARD'] = $value['2'];
            $data['status'] = 1;
            $data['cre_time'] = time();
            $record = $Model->add($data);
        }
        if ($record) {
            echo json_encode(array('status' => 1, 'msg' => '成功'));
        } else {
            echo json_encode(array('status' => -1, 'msg' => '失败'));
        }
    }

    //党员——不计入到会党员导出
    public function excel_out_late()
    {
        //导出标题
        $title = array('序号','姓名','所属党组织','身份证');
        $data = M('dr_dy_late')->where('status = 1')->field('id,name,organization,ID_CARD')->select();
        array_unshift($data,$title);
        create_xls($data,'不计入到会党员');
    }

    //党员--党性体检-健康
    public function import_excel_health()
    {
        vendor("PHPExcel.PHPExcel");
        $file = $_FILES['file']['tmp_name'];
        $dataa = import_excel_m($file);
        $Model = M('dr_dy_dxtj');
        foreach ($dataa as $key => $value) {
            $data['name'] = $value['0'];
            $data['organization'] = $value['1'];
            $data['result1'] = $value['2'];
            $data['result2'] = $value['3'];
            $data['status'] = 1;
            $data['cre_time'] = time();
            $record = $Model->add($data);
        }
        if ($record) {
            echo json_encode(array('status' => 1, 'msg' => '成功'));
        } else {
            echo json_encode(array('status' => -1, 'msg' => '失败'));
        }
    }

    //党员--党性体检-体检健康
    public function excel_out_health($type)
    {
          if($type == 22){
              $where['result1'] = '健康';
              $stitle = '党性体检-健康';
          }elseif ($type == 23){
              $where['result1'] = '不健康';
              $stitle = '党性体检-不健康';
          }elseif ($type == 24){
              $where['result1'] = '亚健康';
              $stitle = '党性体检-亚健康';
          }

        $where['status'] = 1;
        $title = array('序号','姓名','所属党组织','健康状态','评价');
        $data = M('dr_dy_dxtj')->where($where)->field('id,name,organization,result1,result2')->select();
        array_unshift($data,$title);
        create_xls($data,$stitle);
    }

    //党员--党员考评-闪光言行
    public function import_excel_sgyx()
    {
        vendor("PHPExcel.PHPExcel");
        $file = $_FILES['file']['tmp_name'];
        $dataa = import_excel_m($file);
        $Model = M('dr_dy_sgyx');
        foreach ($dataa as $key => $value) {
            $data['name'] = $value['0'];
            $data['speech'] = $value['1'];
            $data['dy_lv'] = $value['2'];
            if(is_float($value['3'])){
                $data['time'] = date('Y/m/d',\PHPExcel_Shared_Date::ExcelToPHP($value['3']));
            }else{
                $data['time'] = $value['3'];
            }
            $data['status'] = 1;
            $data['cre_time'] = time();
            $record = $Model->add($data);
        }
        if ($record) {
            echo json_encode(array('status' => 1, 'msg' => '成功'));
        } else {
            echo json_encode(array('status' => -1, 'msg' => '失败'));
        }
    }


    //党员--党员考评-闪光言行
    public function excel_out_sgyx()
    {
        //导出标题
        $title = array('序号','姓名','演讲内容','等级','时间');
        $data = M('dr_dy_sgyx')->where('status = 1')->field('id,name,speech,dy_lv,time')->select();
        array_unshift($data,$title);
        create_xls($data,'闪光言行');
    }

    //党员--党员考评-评优评先
    public function import_excel_honor()
    {
        vendor("PHPExcel.PHPExcel");
        $file = $_FILES['file']['tmp_name'];
        $dataa = import_excel_m($file);
        $Model = M('dr_dzz_honor');
        foreach ($dataa as $key => $value) {
            $data['name'] = $value['0'];
            $data['organization'] = $value['1'];
            $data['honor'] = $value['2'];
            $data['status'] = 1;
            $data['cre_time'] = time();
            $record = $Model->add($data);
        }
        if ($record) {
            echo json_encode(array('status' => 1, 'msg' => '成功'));
        } else {
            echo json_encode(array('status' => -1, 'msg' => '失败'));
        }
    }

    //党员--党员考评-评优评先
    public function excel_out_honor()
    {
        //导出标题
        $title = array('序号','姓名','所属党组织','优秀称号');
        $data = M('dr_dzz_honor')->where('status = 1')->field('id,name,organization,honor')->select();
        array_unshift($data,$title);
        create_xls($data,'评优评先');
    }

    public function import_excel_dzz_ztdr()
    {
        vendor("PHPExcel.PHPExcel");
        $file = $_FILES['file']['tmp_name'];
        $dataa = import_excel_m($file);
        $Model = M('dr_dzz_ztdr');
        foreach ($dataa as $key => $value) {
            $data['organization'] = $value['0'];
            $data['title'] = $value['1'];
            if(is_float($value['2'])){
                $data['time'] = date('Y/m/d',\PHPExcel_Shared_Date::ExcelToPHP($value['2']));
            }else{
                $data['time'] = $value['2'];
            }
            $data['status'] = 1;
            $data['cre_time'] = time();
            $record = $Model->add($data);
        }
        if ($record) {
            echo json_encode(array('status' => 1, 'msg' => '成功'));
        } else {
            echo json_encode(array('status' => -1, 'msg' => '失败'));
        }
    }
    //党组织--主题党日已展开
    public function excel_out_dzz_ztdr()
    {
        //导出标题
        $title = array('序号','所属党组织','主题','时间');
        $data = M('dr_dzz_ztdr')->where('status = 1')->field('id,organization,title,time')->select();
        array_unshift($data,$title);
        create_xls($data,'党组织-已展开主题党日');
    }

    //党组织--主题党日未展开
    public function import_excel_dzz_unztdr()
    {
        vendor("PHPExcel.PHPExcel");
        $file = $_FILES['file']['tmp_name'];
        $dataa = import_excel_m($file);
        $Model = M('dr_dzz_no_ztdr');
        foreach ($dataa as $key => $value) {
            $data['organization'] = $value['0'];
            $data['secretary'] = $value['1'];
            $data['status'] = 1;
            $data['cre_time'] = time();
            $record = $Model->add($data);
        }
        if ($record) {
            echo json_encode(array('status' => 1, 'msg' => '成功'));
        } else {
            echo json_encode(array('status' => -1, 'msg' => '失败'));
        }
    }
    //党组织--主题党日未展开
    public function excel_out_dzz_unztdr()
    {
        //导出标题
        $title = array('序号','所属党组织','书记');
        $data = M('dr_dzz_no_ztdr')->where('status = 1')->field('id,organization,secretary')->select();
        array_unshift($data,$title);
        create_xls($data,'党组织-未展开主题党日');
    }

    //党组织--发展党员-入党积申请人
    public function excel_out_apply()
    {
        //导出标题
        $title = array('序号','姓名','性别','出生日期','文化程度','所属党组织');
        $data = M('dr_dzz_application_party')->where('status = 1')->field('id,name,sex,birthday,education,organization')->select();
        array_unshift($data,$title);
        create_xls($data,'入党申请人');
    }

    //入党申请人
    public function import_dzz_apply(){
        $file = $_FILES['file']['tmp_name'];
        $dataa = import_excel_m($file);
        $Model = M('dr_dzz_application_party');
        foreach ($dataa as $key=>$value){
            $data['name'] = $value['0'];
            $data['sex'] = $value['1'];
            $data['birthday'] = $value['2'];
            $data['education'] = $value['3'];
            $data['organization'] = $value['4'];
            $data['status'] = 1;
            $data['cre_time'] = time();
            $record = $Model->add($data);
        }
        if($record){
            echo json_encode(array('status'=>1,'msg'=>'成功'));
        } else {
            echo json_encode(array('status'=>-1,'msg'=>'失败'));
        }

    }

    //党组织--发展党员-入党积极分子
    public function excel_out_activity()
    {
        //导出标题
        $title = array('序号','姓名','性别','出生日期','文化程度','所属党组织','电话','职称');
        $data = M('dr_dzz_activity_dy')->where('status = 1')->field('id,name,sex,birthday,education,organization,phone,technical_title')->select();
        array_unshift($data,$title);
        create_xls($data,'入党积极分子');
    }

    //党组织-入党积极分子
    public function import_dzz_activity(){
        $file = $_FILES['file']['tmp_name'];
        $dataa = import_excel_m($file);
        $Model = M('dr_dzz_activity_dy');
        foreach ($dataa as $key=>$value){
            $data['name'] = $value['0'];
            $data['sex'] = $value['1'];
            $data['birthday'] = $value['2'];
            $data['education'] = $value['3'];
            $data['organization'] = $value['4'];
            $data['phone'] = $value['5'];
            $data['technical_title'] = $value['6'];
            $data['status'] = 1;
            $data['cre_time'] = time();
            $record = $Model->add($data);
        }
        if($record){
            echo json_encode(array('status'=>1,'msg'=>'成功'));
        } else {
            echo json_encode(array('status'=>-1,'msg'=>'失败'));
        }

    }

    //党组织--已展开党性体检
    public function import_dzz_dxtj(){
        $file = $_FILES['file']['tmp_name'];
        $dataa = import_excel_m($file);
        $Model = M('dr_dzz_dxtj');
        foreach ($dataa as $key=>$value){
            $data['organization'] = $value['0'];
            $data['secretary'] = $value['1'];
            if(is_float($value['2'])){
                $data['time'] = date('Y/m/d',\PHPExcel_Shared_Date::ExcelToPHP($value['2']));
            }else{
                $data['time'] = $value['2'];
            }
            $data['status'] = 1;
            $data['cre_time'] = time();
            $record = $Model->add($data);
        }
        if($record){
            echo json_encode(array('status'=>1,'msg'=>'成功'));
        } else {
            echo json_encode(array('status'=>-1,'msg'=>'失败'));
        }

    }
    //党组织--已展开党性体检
    public function excel_out_dxtj()
    {
        //导出标题
        $title = array('序号','所属党组织','书记','时间');
        $data = M('dr_dzz_dxtj')->where('status = 1')->field('id,organization,secretary,time')->select();
        array_unshift($data,$title);
        create_xls($data,'已展开党性体检');
    }

    //党组织--未展开党性体检
    public function import_dzz_undxtj(){
        $file = $_FILES['file']['tmp_name'];
        $dataa = import_excel_m($file);
        $Model = M('dr_dzz_undxtj');
        foreach ($dataa as $key=>$value){
            $data['organization'] = $value['0'];
            $data['secretary'] = $value['1'];
            $data['status'] = 1;
            $data['cre_time'] = time();
            $record = $Model->add($data);
        }
        if($record){
            echo json_encode(array('status'=>1,'msg'=>'成功'));
        } else {
            echo json_encode(array('status'=>-1,'msg'=>'失败'));
        }

    }

    //党组织--未展开党性体检
    public function excel_out_undxtj()
    {
        //导出标题
        $title = array('序号','所属党组织','书记');
        $data = M('dr_dzz_undxtj')->where('status = 1')->field('id,organization,secretary')->select();
        array_unshift($data,$title);
        create_xls($data,'未展开党性体检');
    }

    //党组织--已经缴纳党费
    public function import_dzz_pay(){
        $file = $_FILES['file']['tmp_name'];
        $dataa = import_excel_m($file);
        $Model = M('dr_dzz_money');
        foreach ($dataa as $key=>$value){
            $data['organization'] = $value['0'];
            $data['money'] = $value['1'];
            if(is_float($value['2'])){
                $data['time'] = date('Y/m/d',\PHPExcel_Shared_Date::ExcelToPHP($value['2']));
            }else{
                $data['time'] = $value['2'];
            }
            $data['status'] = 1;
            $data['cre_time'] = time();
            $record = $Model->add($data);
        }
        if($record){
            echo json_encode(array('status'=>1,'msg'=>'成功'));
        } else {
            echo json_encode(array('status'=>-1,'msg'=>'失败'));
        }

    }

    //党组织-已缴纳党费
    public function excel_out_pay($type)
    {
        $where['status'] = 1;
        if($type == 12){
            $where['money'] = array('neq',0);
            $stitle = '已缴纳党费';
        }elseif ($type == 13)
        {
            $where['money'] = array('eq',0);
            $stitle = '未缴纳党费';
        }
        //导出标题
        $title = array('序号','所属党组织','金额','时间');
        $data = M('dr_dzz_money')->where($where)->field('id,organization,money,time')->select();
        array_unshift($data,$title);
        create_xls($data,$stitle);
    }

}