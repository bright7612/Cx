/**
 * Created by Administrator on 2017/3/24.
 */
$(function () {
    //请求后台数据
    var winH = $("#containerList").height();
    $("#containerList").scroll(function () {
        var pageH = $("#scrollcontainer").height();
        var scrollT = $("#containerList").scrollTop();
        var aa = (pageH - winH - scrollT) / winH;
        if (aa < 0.05) {
            getData();
        }
    });

    $('#bg').addClass('hide');
    $('#select-content').html('').css({'display': 'none'});
    var flag;   //定义头部点击标记
    var cookie;   //定义cookie

    /*
     点击头部选择栏
     */
    $('#header-select').on('click', function () {
        //判断之前是否已打开
        if (flag) {
            //若已打开则清除下拉样式
            $('#select-content').html('').css({'display': 'none'});   //数据列表清空
            $(this).find('.icon-xia').css({'transform': 'rotate(' + 0 + 'deg)'});   //图标还原
            $('#bg').addClass('hide').removeClass('show');   //遮罩关闭
            flag = false;   //标记为关闭
        }else{
            //否则生成下拉样式
            $(this).find('.icon-xia').css({'transform': 'rotate(' + 180 + 'deg)'});   //图标翻转
            flag = true;   //标记为打开
            $that = $(this);
            //请求后台数据
            $.get('/home/ljz/listdzz2', function (data) {
                var orgName = template('orgName', $.parseJSON(data));
                $('#select-content').html(orgName).css({'display': 'block'});   //生成数据列表
                $('#'+ cookie +'').children('span:last-child').show();   //显示“勾”
                $('#bg').addClass('show').removeClass('hide');  //遮罩打开
                /*
                 点击党支部每一列
                 */
                $('#select-content li').on('click', function () {
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
                        $('#select-content').html('').css({'display': 'none'});
                        $('#header-select').find('.icon-xia').css({'transform': 'rotate(' + 0 + 'deg)'});
                        $('#bg').addClass('hide').removeClass('show');
                        flag = false;   //标记为关闭
                    }, 500);
                })
            });
        }
    });

    $('#bg').on('touchmove', function (event) {
        event.preventDefault();
    });
});

var rows = 10;
var page = 1;
var nomore = false;
var isloading = false;
function getData(dzzid) {
	if(dzzid<=0){
		dzzid=0;
	}
    if (nomore || isloading) {
        return;
    }
    isloading = true;
    $("#loadingdiv").show();
    $.get('/home/ljz/listzzshh', {
        page: page,
        dzzid:dzzid,
        rows: rows
    }, function (data) {
        isloading = false;
        var d = $.parseJSON(data);
        if (d.status == 1) {
            if (d.result &&d.result.length == rows) {
                page++;
            } else {
                $('#nomorediv').show();
                nomore = true;
                $("#loadingdiv").hide();
            }
            var lis = template('lifeInfo', d);
            $('#orgLife-content').append(lis);
        }
    });
}
getData($("#dzzid").val());

function todetail(id) {
    window.location.href='/home/ljz/orgLife/id/'+id;
}

function tolist(id) {
    getData(id);
//  alert("dzzid："+id);
}