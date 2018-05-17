/**
 * Created by GM on 2017/3/22.
 */
$(function () {
    //请求后台数据
    // $.get('/Public/ljz/json/people.json', function (data) {
    //     var peopleName = template('peopleName', data);
    //     $('#people-content').html(peopleName);
    // });



    $('#bg').addClass('hide');
    // $('#select-content').css({'display': 'none'});
    var flag;   //定义头部点击标记
    var cookie;   //定义cookie

    /*
     点击头部选择栏
     */
    $('#header-select').on('click', function () {
        //判断之前是否已打开
        if (flag) {
            //若已打开则清除下拉样式
            $('#select-content').css({'display': 'none'});   //数据列表清空
            $(this).find('.icon-xia').css({'transform': 'rotate(' + 0 + 'deg)'});   //图标还原
            $('#bg').addClass('hide').removeClass('show');   //遮罩关闭
            flag = false;   //标记为关闭
        }else{
            //否则生成下拉样式
            $(this).find('.icon-xia').css({'transform': 'rotate(' + 180 + 'deg)'});   //图标翻转
            flag = true;   //标记为打开
            $that = $(this);

            $('#select-content').css({'display': 'block'});   //生成数据列表
            $('#'+ cookie +'').children('span:last-child').show();   //显示“勾”
            $('#bg').addClass('show').removeClass('hide');  //遮罩打开
            //请求后台数据
            // $.get('./json/catagory.json', function (data) {
            //     var dzbName = template('dzbName', data);
            //
            // });
        }
    });

    /*
     点击党支部每一列
     */
    $('#select-content li').on('click', function () {
        resetDropload($(this).data("type"));
        //更改头部文本
        var text = $(this).children('span:first-child').text();
        text = text.substr(0, 10);
        $that.children('span:first-child').text(text);
        //显示“勾”
        $(this).siblings().children('span:last-child').hide().end().end().children('span:last-child').show();
        //记录cookie值
        cookie = $(this).attr('id');

        //ajax

        setTimeout(function () {
            $('#select-content').css({'display': 'none'});
            $('#header-select').find('.icon-xia').css({'transform': 'rotate(' + 0 + 'deg)'});
            $('#bg').addClass('hide').removeClass('show');
            flag = false;   //标记为关闭
        }, 500);
    });

	$('#select-content').on('touchmove', function (event) {
        event.preventDefault();
    });
	
    $('#bg').on('touchmove', function (event) {
        event.preventDefault();
    });

});