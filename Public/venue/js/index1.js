$(function () {

    $("#thismonth").click(function () {
        $('#yearnum').css("display", "none");
        $('#monthnum').css("display", "block");
    });
    $("#thisyear").click(function () {
        $('#yearnum').css("display", "block");
        $('#monthnum').css("display", "none");
    });


    fillTime();
    fillDate();
    fillWeather();
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
        if (html == '6:00:00') {
            fillWeather();
        }
        if (html == '12:00:00') {
            fillWeather();
        }
        if (html == '18:00:00') {
            fillWeather();
        }
        if (html == '23:59:59') {
            fillWeather();
        }
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
        $('#dateInput').val(year + '-' + month + '-' + date);
    }

    //填充天气
    function fillWeather() {
        $.ajax({
            type:'GET',
            async:true,
            url: "http://api.asilu.com/weather/",
            data:{
                "city":'长兴县'
            },
            dataType:'jsonp',
            success: function (e) {
                var data=e.weather[0];
                var temp=data.temp;//温度
                var weather=data.weather;//天气
                $('.weather_box1').find('.temperature').html(temp);
                $('.weather_box2').find('.weather').html(weather);
            }
        })
    }

    //默认选中今天
    function fillToday() {

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
        elem: '#dateInput'
        , done: function (value, date, endDate) {
            // alert(value);
            $("#scrollMsg").load("http://36.26.83.105:8620/cx/venue/lists.html" + "?date=" + value);
        }
    });
    $("#dateInput").change(function () {
        alert($("#dateInput").val());
    });
});