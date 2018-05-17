/**
 * Created by Jeremy on 2017/3/13.
 */

$(function () {
    $.getJSON('/home/ljz/listProject/', function (data) {
        var contentlist = template('contentList', data);
        $('#content').html(contentlist);
    });
    $('#bg').addClass('hide');
    var flag;   //定义标记
    var jwhCookie, proCookie;   //定义点击区域cookie值

    //点击头部tab栏
    $('.tabs-header li').on('click', function () {
        $.ajaxSetup({
            async:false
        });

        //判断之前是否点击过
        if ($(this).attr('id') == flag) {

            //若已点击则清楚下拉样式
            $('.tabs-list').html('').css({'display': 'none'})
            $('#bg').addClass('hide').removeClass('show');
            $('.layout').css({'overflow': 'visible'});
            $(this).find('.icon-xia').css({'transform': 'rotate(' + 0 + 'deg)'});
            flag = null;
            return;
        } else {
            //若没有点击过则出现数据列表
            var jwhid = 0, proid = 0;  //定义id值
            flag = $(this).attr('id');
            $(this).parent().next().find('ul:eq(1)').html('');
            $('.tabs-header li').find('span:eq(1)').css({'transform': 'rotate(' + 0 + 'deg)'});
            $('.tabs-list').html('');
            $('#bg').addClass('show').removeClass('hide');
            $('.layout').css({'overflow': 'hidden'});
            //若点击居委会选择
            if ($(this).attr('id') == 'header0') {
                $(this).find('span:eq(1)').css({'transform': 'rotate(' + 180 + 'deg)'});
                $that = $(this);

                var jwhStorage = localStorage.getItem('jwhStorage');
                //判断缓存中是否有值
                if (jwhStorage === null) {
                    //通过ajax获取数据
                    $.getJSON('/home/ljz/listJwh', function (data) {
                        var data = JSON.stringify(data);
                        localStorage.setItem('jwhStorage', data);
                    });
                }
                jwhStorage = JSON.parse(localStorage.getItem('jwhStorage'));
                var tag = template('jwhList', jwhStorage);
                $('.tabs-list').html(tag).css({'display': 'block'});
                // console.log('该时刻的jwhid---------' + jwhCookie);
                $('#' + jwhCookie + '').children('span:last-child').show();

                //点击每一个下拉的选项时出现的效果
                $('.tabs-list li').on('click', function () {
                    var text = $(this).children('span:first-child').text();
                    $that.children('span:first-child').text(text);
                    $('#pro').text('选择项目');
                    //点击时可以出现本选项的选中效果
                    $(this).siblings().children('span:last-child').hide();
                    $(this).children('span:last-child').show();
                    jwhid = $(this).attr('id');
                    jwhCookie = jwhid;

                    //动态更新内容数据
                    $.getJSON('/home/ljz/listProject/', {
                        jwhName: text,
                    }, function (data) {
                        var contentlist = template('contentList', data);
                        $('#content').html(contentlist);
                    });

                    delayTime();
                })
                //若点击项目选择
            } else if ($(this).attr('id') == 'header1') {
                $('#bg').addClass('show').removeClass('hide');
                $(this).find('span:eq(1)').css({'transform': 'rotate(' + 180 + 'deg)'});
                $that = $(this);

                // 先判断是否已选择了居委会;
                if ($('#jwh').text() === '选择居委会') {
                    $('#alert').removeClass('hide').addClass('show');
                    setTimeout(function () {
                        $('#bg').addClass('hide').removeClass('show')
                            .find('#alert').addClass('hide').removeClass('show');
                        $('.tabs-header li').find('span:eq(1)').css({'transform': 'rotate(' + 0 + 'deg)'});
                        flag = null;
                    }, 1300);
                    return;
                } else {
                    var jwhName = $('#jwh').text();

                    //通过ajax获取数据
                    $.getJSON('/home/ljz/listProject/', {jwhName: jwhName}, function (data) {
                        var prolist = template('proList', data);
                        $('.tabs-list').html(prolist).css({'display': 'block'});
                        // console.log('该时刻的jwhid---------' + proCookie);
                        $('#' + proCookie + '').children('span:last-child').show();

                        $('.tabs-list li').on('click', function () {
                            var text = $(this).children('span:first-child').text();
                            //截取文本前6个字
                            text = text.substr(0, 6);
                            $that.children('span:first-child').text(text);
                            //点击时可以出现本选项的选中效果
                            $(this).siblings().children('span:last-child').hide();
                            $(this).children('span:last-child').show();
                            proid = $(this).attr('id');
                            proCookie = proid;

                            var proId = $(this).attr('id');
                            $.getJSON('/home/ljz/listProject/', {
                                projectId: proId
                            }, function (data) {
                                var contentlist = template('contentList', data);
                                $('#content').html(contentlist);
                            });
                            delayTime();
                        })
                    });
                }
            }
        }
    });

    $('.tabs-list').on('touchmove', function (event) {
        event.stopPropagation();
    });

    $('#bg').on('touchmove', function (event) {
        event.preventDefault();
    });

    function delayTime() {
        setTimeout(function () {
            $('.tabs-list').html('');
            $('.tabs-header li').find('span:eq(1)').css({'transform': 'rotate(' + 0 + 'deg)'});
            flag = null;
            $('#bg').addClass('hide').removeClass('show');
            $('.layout').css({'overflow': 'visible'});
        }, 500);
    }
});