$(function () {
    var dataId = $('#tabBox>.tabs:first-child').attr('data-id');
    var show = false;//控制小列表显示隐藏
    var mapShow = true;
    var value = '1';
    var obj1 = {};
    var data1;
     var key_show={};

    //地图初始化代码

    var map = new BMap.Map("mMap");
    var point = new BMap.Point(119.917873, 31.031990);
    map.centerAndZoom(point, 15);
    map.enableScrollWheelZoom(true);
    map.setCurrentCity("长兴");
    ajaxObj();
    function ajaxObj(data1, obj1) {
        $.ajax({
            type: "GET",
            // url: "http://192.168.1.254/cx/cx/applylist",
            url: "http://36.26.83.105:8620/cx/cx/applylist",
            // url: "1.json",
            // data: {category_id: a},
            dataType: "json",
            async: true,
            success: function (e) {
                obj1 = e.data;
                data1 = obj1.activity;
                console.log(obj1, data1);
                Maplist(data1, obj1);
            },
            error: function (e) {
                console.log("进入了error");
            },
            complete: function () {
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
                    Maplist(data1, obj1);//调用函数进行撒点
                    //隐藏右上角列表
                    $('#mapMark').hide();
                    //改变右边list的列表头
                    $('#r1List>.title1').html($(this).children('.tab_text').html());
                });

                //切换列表和地图页面的显示
                //地图→→→→→→列表页
                $('#r1Map').on('click', '.mChange', function () {
                    $(this).parent().hide().prev().hide();
                    mapShow = false;
                    $('#r1List').show();
                });
                //列表页→→→→→→地图页
                $('#r1List').on('click', '.mChange', function () {
                    $(this).parent().hide();
                    if (show) {
                        //tab未动的情况下切换原来现实有小列表则显示
                        $('#mapMark').show();
                    }
                    mapShow = true;
                    $('#r1Map').show();
                });
            }
        });
    }

    //获取数据并且撒点切换列表；
    function Maplist(data1, obj1) {
        //清空地图
        map.clearOverlays();
        //获取撒点数据
        $('#searchBox2 input').val() == '';
        if (value == "1" || value == "2" || value == "3" || value == "5") {
            //需要地图渲染的tab
            if (value == "1") {
                $('#searchBox2 input').val('');
                data1 = obj1.activity;
                $('#searchBox2').show();
                searchEvent(value);
            }
            if (value == "2") {
                data1 = obj1.lecture;
                $('#searchBox2').hide();
            }
            if (value == "3") {
                $('#searchBox2 input').val('');
                data1 = obj1.volunteers;
                $('#searchBox2').hide();
            }
            if (value == "5") {
                data1 = obj1.direct;
                $('#searchBox2').hide();
                $('#searchBox2').show();
                searchEvent(value);
            }
            $('#r1ul').empty();
            $('#r1ul').html(template('rlist' + value, data1));
            for (var i = 0; i < data1.length; i++) {
                var point = new BMap.Point(data1[i].lat, data1[i].lng);
                addMarker(point, data1[i]);
            }
            $('#r1List .mChange').show();
            $('#r1Map').hide();
            $('#r1List').hide();
            if (mapShow) {
                $('#r1Map').show();
            } else {
                $('#r1List').show();
            }
        } else {
            if (value == "4") {
                data1 = obj1.wish;
                $('#searchBox2').hide();
            }
            if (value == "6") {
                data1 = obj1.raise;
                $('#searchBox2').hide();
            }
            if (value == "7") {
                $('#searchBox2 input').val('');
                data1 = obj1.ztc;
                $('#searchBox2').show();
                searchEvent(value);
            }
            $('#r1ul').empty();
            $('#r1ul').html(template('rlist' + value, data1));
            //不需要渲染地图
            $('#r1Map').hide();
            $('#r1List .mChange').hide();
            $('#r1List').show();
        }
        //更改列表内的内容
    }

    //地图上添加点的标识
    function addMarker(point, data) {
        var myIcon = new BMap.Icon("/Public/cx/imgs/marker.png", new BMap.Size(54, 61));
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
    });
    function searchEvent(value) {
        var v = value;
        var urlList = {
            'url1': 'http://36.26.83.105:8620/cx/cx/activity_search',
            'url2': 'http://36.26.83.105:8620/cx/cx/direct_search',
            'url3': 'http://36.26.83.105:8620/cx/cx/ztc_search'
        };
        if (v == "1") {
            $('#search').off('click').on('click', function () {
                var search_key = $('#searchBox2 input').val();
               key_show=getSearchDate(urlList.url1, search_key);
                $('#r1ul').empty();
                $('#r1ul').html(template('rlist1', key_show));
            })
        }
        if (v == "5") {
            $('#search').off('click').on('click', function () {
                var search_key = $('#searchBox2 input').val();
                key_show=getSearchDate(urlList.url2, search_key);
                $('#r1ul').empty();
                $('#r1ul').html(template('rlist5', key_show));
            })
        }
        if (v == "7") {
            $('#search').off('click').on('click', function () {
                var search_key = $('#searchBox2 input').val();
                key_show=getSearchDate(urlList.url3, search_key);
                $('#r1ul').empty();
                $('#r1ul').html(template('rlist7', key_show));
            })
        }
    }

    function getSearchDate(url, sub_key) {
        var key_list;
        $.ajax({
            type: "GET",
            url: url,
            data: {
                title: sub_key
            },
            dataType: "json",
            async: false,
            success: function (e) {
                 key_list=e.data;
            },
            error: function (e) {
                console.log("访问错误");
                 key_list={}
            },
            complete: function () {
            }
        });
        return key_list;
    }
});