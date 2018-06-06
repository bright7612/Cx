$(function () {
    var markshow = false;
    var dataId = $('#tabBox>.tabs:first-child').attr('data-id');
    var show=false;//控制右上角列表显示隐藏

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
        var value = $(this).attr('value');
        //需要地图撒点的value值都设置成了1;
        if (value == 1) {
            dataId = $(this).attr('data-id');
            Maplist(dataId);
        }
        //隐藏右上角列表
        $('#mapMark').hide();
        //改变右边list的列表头 #r1list>.title1
        console.log($(this).children('.tab_text').html());
        $('#r1List>.title1').html($(this).children('.tab_text').html());
    });


    //切换列表和地图页面的显示
    $('#r1Map').on('click','.mChange',function(){
        $(this).parent().hide().prev().hide();
        $('#r1List').show();
    });
    $('#r1List').on('click','.mChange',function(){
        $(this).parent().hide();
        if(show){
            $('#mapMark').show();
        }
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
            url: "http://cxdj.cmlzjz.com/cx/cx/mapData",
            data: {category_id: a},
            dataType: "json",
            success: function (e) {
                data = e.data;
                $('#r1ul').empty();
                if (a == 82) {
                    $('#r1ul').html(template('rlist1', data));
                }
                if (a == 88) {
                    $('#r1ul').html(template('rlist2', data));
                }
                for (var i = 0; i < data.length; i++) {
                    var point = new BMap.Point(data[i].lat, data[i].lng);
                    addMarker(point, data[i]);
                }
            },
            error: function () {
                console.log("获取数据错误");
            }
        });
        //更改列表内的内容
    }

    //地图上添加点的标识
    function addMarker(point, data) {
        var myIcon = new BMap.Icon("/Public/cx/imgs/marker.png", new BMap.Size(54, 61));
        var marker = new BMap.Marker(point, {icon: myIcon});
        // var marker = new BMap.Marker(point);
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
        var dates={
            list:listData
        }
        var position = {
            '82': 'text1',
            '88': 'text2'
        };
        $('#mapMark').html(template(position[dataId], dates));
        $('#mapMark').show();
    }

    $('.right .mapMark').on('click', '.close', function () {
        show=false;
        $(this).parent().hide();
    })
});