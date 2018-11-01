<?php
/*
    young 375858706@qq.com
    2017年2月24日
*/
   $fp = @fopen("C:/xampp/www/test.txt", "a+");
    fwrite($fp, date("Y-m-d H:i:s") . " 成功成功了！\n");
    fclose($fp);

