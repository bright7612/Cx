$(function () {
    var dataId = $('#tabBox>.tabs:first-child').attr('data-id');
    var show = false;//控制小列表显示隐藏
    var mapShow=true;
    var value = '1';

    //地图初始化代码

    var map = new BMap.Map("mMap");
    var point = new BMap.Point(119.917873, 31.031990);
    map.centerAndZoom(point, 15);
    map.enableScrollWheelZoom(true);
    map.setCurrentCity("长兴");
    Maplist(dataId);

    //左边栏的点击事件
    $('#tabBox').on('click', '.tabs,.tabs1', function () {
        //点击改变左边tab的背景色和图标颜色
        $('#tabBox>span').css({
            'background': 'transparent',
            'color': '#f5a425'
        });
        $(this).css({
            'background': '#f58c25',
            'color': '#fff'
        });
        value = $(this).attr('value');//按键对应的value的值
        dataId = $(this).attr('data-id');//按键对应的传参值（获取后台数据、撒点、列表）
        Maplist(dataId);//调用函数进行撒点
        //隐藏右上角列表
        $('#mapMark').hide();
        //改变右边list的列表头
        $('#r1List>.title1').html($(this).children('.tab_text').html());
    });

    //切换列表和地图页面的显示
    //地图→→→→→→列表页
    $('#r1Map').on('click', '.mChange', function () {
        $(this).parent().hide().prev().hide();
        mapShow=false;
        $('#r1List').show();
    });
    //列表页→→→→→→地图页
    $('#r1List').on('click', '.mChange', function () {
        $(this).parent().hide();
        if (show) {
            //tab未动的情况下切换原来现实有小列表则显示
            $('#mapMark').show();
        }
        mapShow=true;
        $('#r1Map').show();
    });


    //获取数据并且撒点切换列表；
    function Maplist(a) {
        //清空地图
        map.clearOverlays();
        //获取撒点数据
        var data;
        $.ajax({
            type: "GET",
            // url: "http://192.168.1.254/cx/cx/mapData",
            url: "1.json",
            data: {category_id: a},
            dataType: "json",
            success: function (e) {
                data = e.data;
                $('#r1ul').empty();
                $('#r1ul').html(template('rlist' + value, data));
                if(value=="1"||value=="2"||value=="3"||value=="5"){
                    //需要地图渲染的tab
                    for (var i = 0; i < data.length; i++) {
                        var point = new BMap.Point(data[i].lng, data[i].lat);
                        addMarker(point, data[i]);
                    }
                    $('#r1List .mChange').show();
                    $('#r1Map').hide();
                    $('#r1List').hide();
                    if(mapShow){
                        $('#r1Map').show();
                    }else{
                        $('#r1List').show();
                    }
                }else{
                    //不需要渲染地图
                    $('#r1Map').hide();
                    $('#r1List .mChange').hide();
                    $('#r1List').show();
                }
            },
            error: function (e) {
                console.log("进入了error");
            }
        });
        //更改列表内的内容
    }

    //地图上添加点的标识
    function addMarker(point, data) {
        var myIcon = new BMap.Icon("imgs/marker.png", new BMap.Size(54, 61));
        var marker = new BMap.Marker(point, {icon: myIcon});
        marker.data = data;
        map.addOverlay(marker);
        // 监听覆盖物点击
        marker.addEventListener("click", function () {
            attribute(marker);
        });
    }


    //点击地图上的点触发的右上角的浮动列表

    function attribute(marker) {
        var p = marker.getPosition();  //获取marker的位置
        map.panTo(p);
        var listData = marker.data;
        //渲染数据
        show = true;
        var dates = {
            list: listData
        };
        $('#mapMark').html(template('text' + value, dates)).show();
    }

    //点击小列表的关闭按钮则不显示，直到再次点击地图上的撒点才会弹出地图上的小列表
    $('.right .mapMark').on('click', '.close', function () {
        show = false;
        $(this).parent().hide();
    })
});