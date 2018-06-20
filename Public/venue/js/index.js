$(function () {
    fillTime();
    fillDate();
    // fillWeather();
    fillToday();
    scrollMsg();

    //每秒变一次数据
    setInterval(function () {
        fillTime();
    }, 1000);

    //填充时间
    function fillTime() {
        var myDate = new Date();
        var h = myDate.getHours();
        var m = myDate.getMinutes();
        var s = myDate.getSeconds();
        if (s < 10) {
            s = '0' + s;
        }
        var html = h + ':' + m + ':' + s;
        $('#time').html(html);
    }

    //填充日期和星期

    function fillDate() {
        var myDate = new Date();
        var year = myDate.getFullYear();
        var month = myDate.getMonth() + 1;
        var date = myDate.getDate();
        var day = myDate.getDay();
        var week;
        switch (day) {
            case 1 : {
                week = '星期一';
                break;
            }
            case 2 : {
                week = '星期二';
                break;
            }
            case 3 : {
                week = '星期三';
                break;
            }
            case 4 : {
                week = '星期四';
                break;
            }
            case 5 : {
                week = '星期五';
                break;
            }
            case 6 : {
                week = '星期六';
                break;
            }
            case 7 : {
                week = '星期日';
                break;
            }
        }
        $('#date').html(year + '/' + month + '/' + date);
        $('#day').html(week);
        $('#dateInput').val(year+'-'+month+'-'+date);
    }

    //填充天气
    function fillWeather() {
        $.ajax({
            url: "http://www.weather.com.cn/data/sk/101190408.html",
            success: function (e) {
                console.log(e);
            }
        })
    }

    //默认选中今天
    function fillToday(){

    }

    //点击"每年""每月"按钮事件

    $('#numBox').on('click', '.tabs1', function () {
        $(this).css('borderColor', '#f6cc3c').siblings().css('borderColor', '#ffffff');
    });

    //滚动信息屏幕

    function scrollMsg() {
        console.log(111);
        var $scrollMsg = $('#scrollMsg');
        var length = $('#scrollMsg li').length;
        if (length > 4) {
            $scrollMsg.css({
                webkitAnimation: 'swiper ' + length * 2 + 's infinite linear',
                animation: 'swiper ' + length * 2 + 's infinite linear'
            });
        }
        $scrollMsg.on('mouseover', function () {
            $scrollMsg.css({
                animationPlayState: 'paused',
                webkitAnimationPlayState: 'paused'
            })
        }).on('mouseout', function () {
            $scrollMsg.css({
                animationPlayState: 'running',
                webkitAnimationPlayState: 'running'
            })
        });
    }

    //layDate的使用(日期输入控件)
    laydate.render({
        elem: '#dateInput' //指定元素
    });
});