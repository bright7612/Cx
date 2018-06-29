/**
 * Created by GM on 2018/4/6.
 */
var apiUrl = {
    base: 'http://wei.wiseljz.com/home/apibranch/information', // 基础信息
    baseList: 'http://wei.wiseljz.com/home/apibranch/details', // 基础信息列表

    base2: 'http://cxdj.cmlzjz.com/home/wxapi/information',
    baseList2: 'http://cxdj.cmlzjz.com/home/wxapi/details',
    baseMember: 'http://cxdj.cmlzjz.com/home/wxapi/partyMember',

    partier: 'http://cxdj.cmlzjz.com/cx/data/member', // 党员数据

    warn: 'http://cxdj.cmlzjz.com/home/wxapi/warning', // 预警提醒
    warnlist: 'http://cxdj.cmlzjz.com/home/wxapi/warningList', // 预警提醒列表

    rank: 'http://wei.wiseljz.com/home/apibranch/integral', // 积分排名

    home: 'http://a.wiseljz.com/api/local/getdatacount.html', // 家门口
    homeList: 'http://a.wiseljz.com/api/local/getdatalist', // 家门口列表

    volunteer: 'http://cxdj.cmlzjz.com/cx/data/volunteer', // 志愿服务列表

    wise: 'http://cxdj.cmlzjz.com/cx/data/wxy', // 微心愿数
    requireList: 'http://cxdj.cmlzjz.com/cx/data/wxyRecord', // 需求资源列表
    // http://wei.wiseljz.com/home/api/zyqdlist
    wiseDetail: 'http://192.168.1.254/cx/data/wxy', // 微心愿详情

    project: 'http://cxdj.cmlzjz.com/cx/data/loveList', // 爱心众筹
    help: 'http://cxdj.cmlzjz.com/cx/data/zc_help', // 党员创业互助

    // http://wei.wiseljz.com/home/apibranch/funding
    map: 'http://cxdj.cmlzjz.com/home/wxapi/geoMap',

    monitor: 'http://cxdj.cmlzjz.com/home/wxapi/videos_dp', // 红色资源
    redResource: 'http://cxdj.cmlzjz.com/home/wxapi/redResource', // 红色资源地图

    grid: 'http://cxdj.cmlzjz.com/cx/data/WG', // 网格数据
    gridList: 'http://cxdj.cmlzjz.com/cx/data/wgRecord' // 网格员总数

    // 四个平台 http://183.131.86.64:8620/cx/data/platform
}

var $j = jQuery.noConflict();

var app = new Vue({
    el: '#app',
    data: {
        allScreen: false,
        bMap: '',
        map: {
            myIcon: null,
            curMarker: null,
            mkUrl1: './imgs/marker/marker1.png',
            mkUrl2: './imgs/marker/marker2.png',
            mkUrl3: './imgs/marker/marker3.png',
            mkUrl4: './imgs/marker/marker4.png',
            title: ''
        },
        tabStatus: false,
        time: '',
        day: '',
        date: '',
        tabIndex: 1,
        // mapShow: true,
        platformType: 1,
        showType: 1,
        partyType: 1,
        rightType2: 1,
        rightType3: 2,
        // proProgress1: 10,
        // proProgress2: 55,
        listData:{},
        listDataSecond: {},
        // 基础信息数据
        base: {
            organization: {
                general: {},
                branch: {},
                committee: {},
                union: {},
                network: {}
            },
            type: {
                office: {},
                enterprise: {},
                undertaking: {},
                community: {},
                new: {}
            }
        },
        // 党员数据
        partier: {
            degree: {
                num: [0, 0, 0, 0]
            },
            age: {
                num: [0, 0, 0, 0, 0]
            }
        },
        degreeShow: false,
        ageShow: false,
        // 预警提醒数据
        warn: {
            show: false,
            basedata: {
                organizational_life: {
                    party: {
                        count: 0
                    },
                    personnel: {
                        count: 11940
                    }
                },
                report: {
                    sign: {
                        count: 0
                    },
                    nosign: {
                        count: 0
                    }
                },
                money: {
                    nomoney: {
                        count: 0
                    }
                },
            },
            listData: {},
            type: 1,
            time: 1
        },
        // 积分排名数据
        rank: {
            show: false,
            title: '',
            org: [],
            people: []
        },
        // 网格数据
        home: {
            years: [],
            monthes: [],
            all: 0,
            done: 0,
            ordinary: 0
        },
        // 志愿服务数据
        volunteer: {
            sum: 0,
            list: []
        },
        modelShow: false,
        modelSecondShow: false,
        // 微心愿数据
        wise: {
            claimed: 0,
            claimNot: 0,
            doing: 0,
            complete: 0,
            uncomplete: 0,
            all: 0,
            show: false,
            detail: {}
        },
        // 项目众筹数据
        project: {
            complete: 0,
            implement: 0,
            list: [],
            show: false
        },
        // 党员创业互助
        help: {
            complete: 0,
            implement: 0,
            list: [],
            show: false
        },
        projectAndHelp: {
            list: [],
            show: false
        },
        // 红色资源数据
        resource: {
            data: {
                head: [],
                list: [],
                title: ''
            },
            show: false
        }
    },
    computed: {
        // 子网页链接
        iframeUrl: function () {
            var url = ''
            if (this.tabIndex === 1) {
                url = 'http://www.dysfz.gov.cn/f-index/indexList?printMode=displayScreen';
            }
            if (this.tabIndex === 2) {
                url = 'http://dysfz.gov.cn/f-earea/toMap?printMode=displayScreen';
            }
            return url;
        },
        // 积分类别
        rankType: function () {
            return this.rank.title === '党组织积分' ? '党组织名称' : '党员姓名';
        },
        // 全岗通完成率
        homeRate: function () {
            return (this.home.done / this.home.all * 100).toFixed(2);
        },
        // 微心愿形式
        wiseType: function () {
            return this.wise.detail.NEEDTYPE === '1' ? '精神' : '物质';
        },
        // 微心愿对象
        wiseFunc:  function () {
            return this.wise.detail.REQUIRE_TYPE === '1' ? '个人' : '团体';
        },
        // 党员学历总数
        degreeTotal: function () {
            var total = 0;
            this.partier.degree.num.forEach(function (value) {
                total += value;
            });
            return total
        },
        // 党员年龄总数
        ageTotal: function () {
            var total = 0;
            this.partier.age.num.forEach(function (value) {
                total += value;
            });
            return total
        }
    },
    methods: {
        changeMapSize: function () {
            this.allScreen = !this.allScreen;
        },
        changeTabStatus: function () {
            this.tabStatus = !this.tabStatus
        },
        computePercent: function (current, total) {
            return Math.round(current/total*100)
        },
        // 环状进度
        circlePercent: function (radius, percent) {
            return Math.PI * 2 * radius * percent / 100 + ' ' + Math.PI * 2 * radius * (1 - percent / 100);
        },
        // 时间展示
        renderTime: function () {
            var date = new Date().getTime();
            this.time = this._dateFormat(date, 'hh : mm : ss');
        },
        // 日期展示
        renderDate: function () {
            var date = new Date();
            this.day = this._getWeek(date.getDay());
            this.date = this._dateFormat(date.getTime(), 'yyyy年MM月dd日');
        },
        // 切换基础信息
        changeParty: function (type) {
            this.partyType = type;
            this.hideDistribute();
        },
        // 切换四个平台
        changePlatform: function (type) {
            this.platformType = type;
        },
        // 显示党员分布
        showDistribute: function (type) {
            this.hideDistribute();
            if (type === 1) {
                this.degreeShow = true;
            } else {
                this.ageShow = true;
            }
        },
        // 隐藏党员分布
        hideDistribute: function () {
            this.degreeShow = false;
            this.ageShow = false;
        },
        // 切换资源需求
        changeRequire: function (type) {
            this.requireType = type;
        },
        // 切换蛇
        changePublic: function (type) {
            this.publicType = type;
        },
        // 改变地图展示
        changeMap: function (type, index) {
            var _this = this;
            this.tabIndex = index;
            // this.mapShow = true;
            this.showType = 1;
        },
        // 监控地图初始化
        initMap: function () {
            this.bMap = new BMap.Map("bmap");
            var point = new BMap.Point(119.703592, 31.167098);
            this.bMap.centerAndZoom(point, 12);
            this.bMap.enableScrollWheelZoom(true);
        },
        // 切换爱心众筹/志愿服务
        changeRightType2: function (type) {
            this.rightType2 = type
        },
        // 切换微心愿/党员众筹
        changeRightType3: function (type) {
            this.rightType3 = type
        },
        // 显示热力图
        showHot: function () {
          this.showType = 2;
        },
        // 打开大视频
        openVideo: function () {
            this.tabIndex = 0;
            // this.mapShow = false;
            this.showType = 3;
        },
        // 打开公用模态框
        openModel: function (module, type, select, classify) {
            if (module === 'base') {
                this._getBaseData(type, select, classify);
            }
            if (module === 'require') {
                this._getRequireData(type);
            }
            if (module === 'grid') {
                this._getGridList(type);
            }
        },
        // 打开公用模态框详情
        openModelDetail: function (id, type) {
            if (id && type === 'partyMember') {
                this._getBaseMember(id);
                return false;
            }
            if (id) {
                this._getWiseDetail(id);
            }
        },
        // 关闭二级公用模态框
        closeSecondModel: function () {
            this.modelSecondShow = false;
        },
        // 关闭微心愿详情
        closeWiseDetail: function () {
            this.wise.show = false;
            this.wise.detail = {};
        },
        // 打开项目众筹列表
        openProjectList: function (type) {
            if (type === 'project') {
                this.projectAndHelp.list = this.project.list;
            }
            if (type === 'help') {
                this.projectAndHelp.list = this.help.list;
            }
            this.projectAndHelp.show = true;
        },
        // 关闭项目众筹列表
        closeProject: function () {
            this.projectAndHelp.show = false;
        },
        // 关闭公用模态框
        closeModel: function () {
            this.modelShow = false;
        },
        // 打开预警提醒模态框
        openWarn: function (type) {
            this._getWarnList(type);
        },
        // 关闭预警提醒
        closeWarn: function () {
            this.warn.title = '';
            this.warn.show = false;
        },
        // 打开积分排名
        openRank: function (item) {
            var scoreTitle = ['党组织积分', '党员积分'];
            this.rank.title = scoreTitle[item.type-1];
            this.rank.name = item.name;
            this.rank.origin = item.origin;
            this.rank.show = true;
        },
        // 关闭积分排名
        closeRank: function () {
            this.rank.show = false;
        },
        // 打开红色资源
        openResource: function (type) {
            // this._getMonitorData();
            // this.resource.show = true;
            var _this = this;

            var mapTitle = {
                party: '区域党群服务中心数量',
                school: '红领学院数量',
                belt: '示范带数量',
                base: '教学基地数量'
            }
            this.map.title = mapTitle[type];
            this.tabIndex = 7;
            this.bMap.clearOverlays();

            this._getRedResource(type).then(function (data) {
                if (data.length > 0) {
                    if (type === 'belt') {
                        var colors = ['red', 'orange', '#a39b14', 'green', 'blue', 'purple', 'gray', '#ffced0', '#73ffff', '#8bff5e']
                        data.forEach(function (value, index) {
                            var arrPoint = [];
                            for (var i = 0; i < value.length; i++) {
                                var point = new BMap.Point(value[i].lng, value[i].lat);
                                arrPoint.push(point);
                                _this._addMarker(point, value[i], true);
                            }
                            // _this._addLine(arrPoint, colors[index]);
                        })
                        return false;
                    }

                    for (var i = 0; i < data.length; i++) {
                        var point = new BMap.Point(data[i].lng, data[i].lat);
                        _this._addMarker(point, data[i]);
                    }
                }
            })
        },
        // 关闭红色资源
        closeResource: function () {
            ButtonStopRealplayByWndNo_onclick();
            var video = document.getElementById('monitor');
            video.style.width = 0 + 'px';
            video.style.height = 0 + 'px';
        },
        // 红色资源监控播放
        monitorPlay: function (tdid, title) {
            document.getElementById('monTitle').innerText = title;
            var video = document.getElementById('monitor');
            video.style.width = 740 + 'px';
            video.style.height = 560 + 'px';
            ButtonStartRealplayByWndNo_onclick(tdid);
        },
        // 党员男女比例饼图
        genderRender: function () {
            var myChart = echarts.init(document.getElementById('gender'));
            var i = 0;
            var color = ['#ff4459', '#ffd441'];
            option = {
                animation: false,
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: ['男','女']
                },
                series : [
                    {
                        name: '男女比例',
                        type: 'pie',
                        radius : '100%',
                        center: ['50%', '50%'],
                        labelLine: {
                            normal: {
                                show: false
                            }
                        },
                        data:[
                            {value: this.partier.man, name:'男'},
                            {value: this.partier.woman, name:'女'},
                        ],
                        itemStyle : {
                            normal : {
                                color: function (){
                                    return color[i++];
                                }
                            }
                        }
                    }
                ]
            };
            myChart.setOption(option);
        },
        // 党员学历分布柱状图
        educationRender: function () {
            var _this = this;
            var myChartRate = echarts.init(document.getElementById('education'));
            option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                grid: {
                    left: '2%',
                    right: '3%',
                    bottom: '0%',
                    top: '10%',
                    containLabel: true,
                    borderColor: '#fff',
                    borderWidth: 1
                },
                xAxis: {
                    type: 'category',
                    data: ['高中', '大专', '本科', '研究生'],
                    axisLabel: {
                        show: true,
                        textStyle: {
                            color: '#FFF',
                            fontSize: 12
                        }
                    },
                    axisLine:{
                        lineStyle: {
                            color: '#48b8f0',
                            width: 2
                        }
                    },
                    axisTick: {
                        show: false
                    },
                },
                yAxis: {
                    type: 'value',
                    boundaryGap: [0, 0.01],
                    axisLabel: {
                        formatter: '{value}',
                        textStyle: {
                            color: '#FFF',
                            fontSize: 12
                        }
                    },
                    axisLine:{
                        lineStyle: {
                            color: '#48b8f0',
                            width: 2
                        }
                    },
                    axisTick: {
                        show: false
                    },
                    splitNumber: 2,
                    splitLine:{
                        show:false
                    }
                },
                label: {
                    show: true,
                    position: 'top',
                    textStyle: {
                        color: 'white'
                    }
                },
                series: [
                    {
                        name: '当年',
                        type: 'bar',
                        barWidth: 10,
                        data: _this.partier.degree.num,
                        label: {
                            color: '#fff'
                        },
                        itemStyle:{
                            normal:{
                                color:'#f67c46',
                            }
                        }
                    }
                ]
            };
            myChartRate.setOption(option);
        },
        // 党员年龄比例柱状图
        ageRender: function () {
            var _this = this;
            var myChartRate = echarts.init(document.getElementById('age'));
            option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                grid: {
                    left: '2%',
                    right: '3%',
                    bottom: '0%',
                    top: '8%',
                    containLabel: true,
                    borderColor: '#fff',
                    borderWidth: 1
                },
                xAxis: {
                    type: 'category',
                    data: ['30岁以下', '30-40', '40-50', '50-60', '60岁以上'],
                    axisLabel: {
                        show: true,
                        textStyle: {
                            color: '#FFF',
                            fontSize: 12
                        }
                    },
                    axisLine:{
                        lineStyle: {
                            color: '#48b8f0',
                            width: 2
                        }
                    },
                    axisTick: {
                        show: false
                    },
                },
                yAxis: {
                    type: 'value',
                    boundaryGap: [0, 0.01],
                    axisLabel: {
                        formatter: '{value}',
                        textStyle: {
                            color: '#FFF',
                            fontSize: 12
                        }
                    },
                    axisLine:{
                        lineStyle: {
                            color: '#48b8f0',
                            width: 2
                        }
                    },
                    axisTick: {
                        show: false
                    },
                    splitNumber: 2,
                    splitLine:{
                        show:false
                    }
                },
                label: {
                    show: true,
                    position: 'top',
                    textStyle: {
                        color: 'white'
                    }
                },
                series: [
                    {
                        name: '当年',
                        type: 'bar',
                        barWidth: 12,
                        data: _this.partier.age.num,
                        label: {
                            color: '#fff'
                        },
                        itemStyle:{
                            normal:{
                                color:'#50e1df',
                            }
                        }
                    }
                ]
            };
            myChartRate.setOption(option);
        },
        // 党员学历分布饼图
        distributeEduRender: function () {
            var myChart = echarts.init(document.getElementById('distributeEdu'));
            var i = 0;
            var color = ['#ffa683', '#52d1ff', '#30a8ff', '#ffe76c'];
            option = {
                animation: false,
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: ['高中','大专', '本科', '研究生']
                },
                series : [
                    {
                        name: '学历分布',
                        type: 'pie',
                        radius : '95%',
                        center: ['50%', '50%'],
                        labelLine: {
                            normal: {
                                show: false
                            }
                        },
                        data:[
                            {value: this.partier.degree.num[0], name:'高中'},
                            {value: this.partier.degree.num[1], name:'大专'},
                            {value: this.partier.degree.num[2], name:'本科'},
                            {value: this.partier.degree.num[3], name:'研究生'},
                        ],
                        itemStyle : {
                            normal : {
                                color: function (){
                                    return color[i++];
                                }
                            }
                        }
                    }
                ]
            };
            myChart.setOption(option);
        },
        // 党员年龄分布饼图
        distributeAgeRender: function () {
            var myChart = echarts.init(document.getElementById('distributeAge'));
            var i = 0;
            var color = ['#ffa683', '#52d1ff', '#30a8ff', '#ffe76c', '#8bff5e'];
            option = {
                animation: false,
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: ['30岁以下','30-40岁', '40-50岁', '50-60岁', '60岁以上']
                },
                series : [
                    {
                        name: '年龄分布',
                        type: 'pie',
                        radius : '95%',
                        center: ['50%', '50%'],
                        labelLine: {
                            normal: {
                                show: false
                            }
                        },
                        data:[
                            {value: this.partier.age.num[0], name:'30岁以下'},
                            {value: this.partier.age.num[1], name:'30-40岁'},
                            {value: this.partier.age.num[2], name:'40-50岁'},
                            {value: this.partier.age.num[3], name:'50-60岁'},
                            {value: this.partier.age.num[4], name:'60岁以上'},
                        ],
                        itemStyle : {
                            normal : {
                                color: function (){
                                    return color[i++];
                                }
                            }
                        }
                    }
                ]
            };
            myChart.setOption(option);
        },
        // 微心愿已认领饼图
        claimRender: function () {
            var myChart = echarts.init(document.getElementById('claim'));
            var i = 0;
            var color = ['#ff4459', '#ffd441'];
            option = {
                animation: false,
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: ['已达标','进行中']
                },
                series : [
                    {
                        name: '访问来源',
                        type: 'pie',
                        radius : '100%',
                        center: ['50%', '50%'],
                        labelLine: {
                            normal: {
                                show: false
                            }
                        },
                        data:[
                            {value: this.wise.complete, name:'已达标'},
                            {value: this.wise.doing, name:'进行中'},
                        ],
                        itemStyle : {
                            normal : {
                                color: function (){
                                    return color[i++];
                                }
                            }
                        }
                    }
                ]
            };
            myChart.setOption(option);
        },
        // 微心愿已实现饼图
        realizeRender: function () {
            var myChart = echarts.init(document.getElementById('realize'));
            var i = 0;
            var color = ['#00b6ff', '#a4ffff'];
            option = {
                animation: false,
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: ['已实现','未实现']
                },
                series : [
                    {
                        name: '访问来源',
                        type: 'pie',
                        radius : '100%',
                        center: ['50%', '50%'],
                        labelLine: {
                            normal: {
                                show: false
                            }
                        },
                        data:[
                            {value: this.wise.realized, name:'已实现'},
                            {value: this.wise.realizeNot, name:'未实现'},
                        ],
                        itemStyle : {
                            normal : {
                                color: function (){
                                    return color[i++];
                                }
                            }
                        }
                    }
                ],
                label: {
                    labelLine: {
                        normal: {
                            show: false
                        }
                    }
                }
            };
            myChart.setOption(option);
        },
        // 网格柱状图
        lineRender: function () {
            var _this = this;
            var myChartRate = echarts.init(document.getElementById('server'));
            option = {
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                legend: {
                    data: ['当年', '当月']
                },
                grid: {
                    left: '2%',
                    right: '3%',
                    bottom: '0%',
                    top: '5%',
                    containLabel: true,
                    borderColor: '#fff',
                    borderWidth: 1
                },
                xAxis: {
                    type: 'category',
                    data: ['交通', '公共', '公安', '消防', '卫计', '国土', '城建'],
                    axisLabel: {
                        show: true,
                        textStyle: {
                            color: '#FFF',
                            fontSize: 14
                        }
                    },
                    axisLine:{
                        lineStyle: {
                            color: '#48b8f0',
                            width: 2
                        }
                    },
                    axisTick: {
                        show: false
                    },
                },
                yAxis: {
                    type: 'value',
                    boundaryGap: [0, 0.01],
                    axisLabel: {
                        formatter: '{value}',
                        textStyle: {
                            color: '#FFF',
                            fontSize: 14
                        }
                    },
                    axisLine:{
                        lineStyle: {
                            color: '#48b8f0',
                            width: 2
                        }
                    },
                    axisTick: {
                        show: false
                    },
                    splitNumber: 2,
                    splitLine:{
                        show:false
                    }
                },
                label: {
                    show: true,
                    position: 'top',
                    textStyle: {
                        color: 'white'
                    }
                },
                series: [
                    {
                        name: '当年',
                        type: 'bar',
                        barWidth: 11,
                        data: this.home.years,
                        label: {
                            color: '#fff'
                        },
                        itemStyle:{
                            normal:{
                                color:'#f67c46',
                            }
                        }
                    },
                    {
                        name: '当月',
                        type: 'bar',
                        barWidth: 11,
                        data: this.home.monthes,
                        label: {
                            color: '#fff'
                        },
                        itemStyle:{
                            normal:{
                                color:'#50e1df',
                            }
                        }
                    }
                ]
            };
            myChartRate.setOption(option);
            myChartRate.on("click", function(param) {
                if (typeof param.seriesIndex == 'undefined') {
                    return;
                }
                if (param.type == 'click') {
                    var types = {
                        '党群': 1,
                        '政务': 2,
                        '生活': 3,
                        '法律': 4,
                        '健康': 5,
                        '文化': 6,
                        '社区': 7,
                        '自治': 8
                    }
                    var type =  types[param.name];
                    var select = param.seriesIndex + 1;
                    _this.openModel('home', type, select);
                }
            });
        },
        // 网格饼图
        dealRender: function () {
            var myChart = echarts.init(document.getElementById('deal'));
            var i = 0;
            var color = ['#1de0ee', '#ff4459'];
            option = {
                animation: false,
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: ['普通网格员','红色网格员']
                },
                series : [
                    {
                        name: '访问来源',
                        type: 'pie',
                        radius : '100%',
                        center: ['50%', '50%'],
                        labelLine: {
                            normal: {
                                show: false
                            }
                        },
                        data:[
                            {value: this.home.done, name:'红色网格员'},
                            {value: this.home.ordinary, name:'普通网格员'}
                        ],
                        itemStyle : {
                            normal : {
                                color: function (){
                                    return color[i++];
                                }
                            }
                        }
                    }
                ],
                label: {
                    labelLine: {
                        normal: {
                            show: false
                        }
                    }
                }
            };
            myChart.setOption(option);
        },
        // 综合治理环图
        governRender: function () {
            var myChart = echarts.init(document.getElementById('govern'));
            var i = 0;
            // var color = ['#ec5593', '#ffb779', '#ffff52'];
            var color = ['#ec5593', '#ffb779'];
            option = {
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b}: {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    x: 'left',
                    // data:['户籍人员','流动人员','境外人员']
                    data:['户籍人员','流动人员']
                },
                series: [
                    {
                        name:'访问来源',
                        type:'pie',
                        radius: ['50%', '100%'],
                        avoidLabelOverlap: false,
                        hoverAnimation: false,
                        labelLine: {
                            normal: {
                                show: false
                            }
                        },
                        data:[
                            {value:606805, name:'户籍人员'},
                            {value:58126, name:'流动人员'},
                            // {value:200, name:'境外人员'}
                        ],
                        itemStyle : {
                            normal : {
                                color: function (){
                                    return color[i++];
                                }
                            }
                        }
                    }
                ]
            };
            myChart.setOption(option);
        },
        // 综合执法环图
        lawRender: function () {
            var myChart = echarts.init(document.getElementById('law'));
            var i = 0;
            var color = ['#CD3333', '#74ffff'];
            option = {
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b}: {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    x: 'left',
                    data:['办结事件','在办事件']
                },
                series: [
                    {
                        name:'访问来源',
                        type:'pie',
                        radius: ['50%', '100%'],
                        avoidLabelOverlap: false,
                        hoverAnimation: false,
                        labelLine: {
                            normal: {
                                show: false
                            }
                        },
                        data:[
                            {value:18120, name:'党员办结数'},
                            {value:42539, name:'非党员办结数'}
                        ],
                        itemStyle : {
                            normal : {
                                color: function (){
                                    return color[i++];
                                }
                            }
                        }
                    }
                ]
            };
            myChart.setOption(option);
        },
        // 趋势统计折线图
        tendency: function () {
            var myChart = echarts.init(document.getElementById('tendency'));
            option = {
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data:['邮件营销','联盟广告']
                },
                grid: {
                    left: '5%',
                    right: '8%',
                    top: '5%',
                    bottom: '5%',
                    containLabel: true,
                    borderColor: '#fff',
                    borderWidth: 1
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: ['18-01','18-02','18-03','18-04','18-05','18-06'],
                    axisLabel: {
                        show: true,
                        textStyle: {
                            color: '#FFF',
                            fontSize: 12
                        }
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#fff'
                        }
                    }
                },
                yAxis: {
                    type: 'value',
                    axisLabel: {
                        formatter: '{value}',
                        textStyle: {
                            color: '#FFF',
                            fontSize: 12
                        }
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#fff'
                        }
                    },
                    splitNumber: 3,
                },
                series: [
                    {
                        name:'邮件营销',
                        type:'line',
                        color: '#38bdff',
                        stack: '总量',
                        data:[250, 100, 90, 40, 350, 170]
                    },
                    {
                        name:'联盟广告',
                        type:'line',
                        color: '#ffff19',
                        stack: '总量',
                        data:[200, 60, 110, 30, 380, 220]
                    }
                ]
            };
            myChart.setOption(option);
        },
        // 项目众筹饼图
        projectRender: function () {
            var myChart = echarts.init(document.getElementById('project'));
            var i = 0;
            var color = ['#F67C46', '#ffd441'];
            option = {
                animation: false,
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: ['已完成','未完成']
                },
                series : [
                    {
                        name: '访问来源',
                        type: 'pie',
                        radius : '100%',
                        center: ['50%', '50%'],
                        labelLine: {
                            normal: {
                                show: false
                            }
                        },
                        data:[
                            {value: this.project.complete, name:'已完成'},
                            {value: this.project.implement, name:'未完成'},
                        ],
                        itemStyle : {
                            normal : {
                                color: function (){
                                    return color[i++];
                                }
                            }
                        }
                    }
                ]
            };
            myChart.setOption(option);
        },
        // 党员众创互助
        helpRender: function () {
            var myChart2 = echarts.init(document.getElementById('project2'));
            var i = 0;
            var color = ['#F67C46', '#ffd441'];
            option = {
                animation: false,
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: ['已完成','未完成']
                },
                series : [
                    {
                        name: '访问来源',
                        type: 'pie',
                        radius : '100%',
                        center: ['50%', '50%'],
                        labelLine: {
                            normal: {
                                show: false
                            }
                        },
                        data:[
                            {value: this.project.complete, name:'已完成'},
                            {value: this.project.implement, name:'未完成'},
                        ],
                        itemStyle : {
                            normal : {
                                color: function (){
                                    return color[i++];
                                }
                            }
                        }
                    }
                ]
            };
            myChart2.setOption(option);
        },
        // 创建覆盖物
        _addMarker: function (point, data) {
            var _this = this;
            var url = '';
            if (data.tdid) {
                url = this.map.mkUrl1;
                if (data.status == '100') {
                    url = this.map.mkUrl3;
                }
            } else {
                url = this.map.mkUrl2;
                if (data.status == '100') {
                    url = this.map.mkUrl4;
                }
            }

            if (data.status == '100') {
                this.map.myIcon = new BMap.Icon(url, new BMap.Size(50, 50));
            } else {
                this.map.myIcon = new BMap.Icon(url, new BMap.Size(26, 26));
            }
            // 创建标注对象并添加到地图
            var marker = new BMap.Marker(point, {icon: this.map.myIcon});
            marker.data = data;
            this.bMap.addOverlay(marker);

            // 监听覆盖物点击
            marker.addEventListener("click", function () {
                _this._attribute(marker);
            });

            // var label = new BMap.Label(data.status,{offset:new BMap.Size(20,-10)});
            // marker.setLabel(label);
        },
        // 创建连线
        _addLine: function (arrPoint, color) {
            var polyline = new BMap.Polyline(arrPoint, {
                strokeColor: color,
                strokeWeight: 6,
                strokeOpacity: 1
            });   //创建折线
            this.bMap.addOverlay(polyline);
        },
        // 覆盖物点击回调
        _attribute: function (marker) {
            var p = marker.getPosition();  //获取marker的位置
            this.bMap.panTo(p);

            // if (this.map.curMarker) {
            //     this.map.curMarker.setIcon(new BMap.Icon(this.map.mkUrl1, new BMap.Size(32, 32)));
            // }
            // this.map.curMarker = marker;
            // marker.setIcon(new BMap.Icon(this.map.mkUrl1, new BMap.Size(50, 50)));
            // console.log(marker.data);
            if (!marker.data.tdid) {
                return false;
            }
            this.monitorPlay(marker.data.tdid, marker.data.title);
        },
        // 获取基础数据
        _getBase: function () {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.base2,
                dataType: 'json',
                cache: false,
                success: function (res) {
                    var data = res;
                    if (data.code === 200) {
                        _this.base.organization = data.data.dzz.organization
                        _this.base.type = data.data.dzz.type
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取基础信息数据
        _getBaseData: function (type, select, classify) {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.baseList2,
                dataType: 'json',
                data: {
                    type: type,
                    subtype: select,
                    classify: classify
                },
                cache: false,
                success: function (res) {
                    var data = res;
                    if (data.code === 200) {
                        _this.listData = data.data;
                        _this.modelShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取微心愿详情数据
        _getBaseMember: function (id) {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.baseMember,
                dataType: 'json',
                data: {
                    id: id
                },
                cache: false,
                success: function (res) {
                    var data = res;
                    _this.listDataSecond = data.data;
                    _this.modelSecondShow = true;
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取党员信息数据
        _getPartier: function () {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.partier,
                dataType: 'json',
                cache: false,
                success: function (res) {
                    var data = res;
                    if (data.status === 1) {
                        _this.partier = data.data
                    }
                    _this.genderRender();
                    _this.educationRender();
                    _this.ageRender();
                    _this.distributeEduRender();
                    _this.distributeAgeRender();
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取预警提醒数据
        _getWarn: function () {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.warn,
                dataType: 'json',
                cache: false,
                success: function (res) {
                    var data = res;
                    if (data.code === 200) {
                        _this.warn.basedata = data.data;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取预警提醒列表数据
        _getWarnList: function (type, time) {
            var _this = this;
            if (!type) {type = this.warn.type}
            if (!time) {time = 1}
            this.warn.type = type;
            this.warn.time = time;

            $j.ajax({
                type: 'GET',
                url: apiUrl.warnlist,
                data: {
                    types: type,
                    classify: time
                },
                dataType: 'json',
                cache: false,
                success: function (res) {
                    var data = res;
                    if (data.code === 200) {
                        if (type === 1 || type === 2) {
                            _this.warn.listData = data.data;
                            _this.warn.show = true;
                        } else {
                            _this.listData = data.data;
                            _this.modelShow = true;
                        }
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取积分排名数据
        _getRankData: function (type) {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.rank,
                dataType: 'json',
                cache: false,
                success: function (res) {
                    var data = res;
                    if (data.code === 200) {
                        _this.rank.org =  data.data.org;
                        _this.rank.people = data.data.people;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        // 获取网格数据
        _getGrid: function() {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.grid,
                dataType: 'json',
                cache: false,
                success: function (res) {
                    var data = res;
                    _this.home.years = data.year
                    _this.home.monthes = data.month
                    _this.home.all = data.wg;
                    _this.home.done = data.dy;
                    _this.home.ordinary = data.people;
                    _this.lineRender();
                    _this.dealRender();
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取网格员数据
        _getGridList: function (type) {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.gridList,
                data: {
                  classif: type
                },
                dataType: 'json',
                cache: false,
                success: function (res) {
                    var data = res;
                    if (data.code === 200) {
                        _this.listData = data.data;
                        _this.modelShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        // 获取家门口服务数据
        _getHomeData: function (type, select) {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.homeList,
                dataType: 'json',
                data: {
                    type: type,
                    select: select
                },
                cache: false,
                success: function (res) {
                    var data = res;
                    if (data.code === 200) {
                        _this.warn.data = data.data;
                        _this.warn.show = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        // 获取公益城数据
        _getVolunteerData: function () {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.volunteer,
                dataType: 'json',
                cache: false,
                success: function (res) {
                    var data = res;
                    if (data.status === 1) {
                        _this.volunteer = data.data;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        // 获取微心愿数据
        _getWiseData: function () {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.wise,
                dataType: 'json',
                cache: false,
                success: function (res) {
                    var data = res.data;
                    _this.wise.doing = data.wxy_ing;
                    _this.wise.complete = data.wxy_db;
                    _this.wise.uncomplete = data.wxy_ed;
                    _this.wise.realized = data.already;
                    _this.wise.realizeNot = data.wei;
                    _this.wise.all = data.count;
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        // 获取微心愿列表数据
        _getRequireData: function (type) {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.requireList,
                dataType: 'json',
                data: {
                    type: type
                },
                cache: false,
                success: function (res) {
                    var data = res;
                    if (data.status === 200) {
                        _this.listData = data.data;
                        _this.modelShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取微心愿详情数据
        _getWiseDetail: function (id) {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.wiseDetail,
                dataType: 'json',
                data: {
                    wxyid: id
                },
                cache: false,
                success: function (res) {
                    var data = res;
                    _this.wise.detail = data.data;
                    _this.wise.show = true;
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取爱心众筹数据
        _getProjectData: function () {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.project,
                dataType: 'json',
                cache: false,
                success: function (res) {
                    var data = res;
                    // if (data.code === 200) {
                    //     _this.project = data.data;
                    // }
                    _this.project = data.data;
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        // 获取党员创业互助数据
        _getHelpData: function () {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.help,
                dataType: 'json',
                cache: false,
                success: function (res) {
                    var data = res;
                    // if (data.code === 200) {
                    //     _this.help = data.data;
                    // }
                    _this.help = data.data;
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        // 获取地图geoJson
        _getMapJson: function () {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.map,
                dataType: 'json',
                cache: false,
                success: function (res) {
                    if (res.code === 200) {
                        addGeoJsonLayer(res.data);
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        // 获取监控数据
        _getMonitorData: function () {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.monitor,
                dataType: 'json',
                cache: false,
                success: function (res) {
                    if (res.code === 200) {
                        _this.resource.data = res.data;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        // 获取红色资源地图数据
        _getRedResource: function (type) {
            var def = $j.Deferred();
            $j.ajax({
                type: 'GET',
                url: apiUrl.redResource,
                data: {
                    classif: type
                },
                dataType: 'json',
                cache: false,
                success: function (data) {
                    if (data.code === 200) {
                        def.resolve(data.data);
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
            return def;
        },
        // 获取星期
        _getWeek: function (day) {
            day = String(day);
            var week = {
                '0': '日',
                '1': '一',
                '2': '二',
                '3': '三',
                '4': '四',
                '5': '五',
                '6': '六',
            }
            return week[day];
        },
        // 时间格式化
        _dateFormat: function (date, fmt) {
            var date = new Date(date);

            if (/(y+)/.test(fmt)) {
                fmt = fmt.replace(RegExp.$1, (date.getFullYear() + '').substr(4 - RegExp.$1.length));
            }
            var o = {
                'M+': date.getMonth() + 1,
                'd+': date.getDate(),
                'h+': date.getHours(),
                'm+': date.getMinutes(),
                's+': date.getSeconds()
            }
            for (var k in o) {
                if (new RegExp("(" + k + ")").test(fmt)) {
                    var str = o[k] + '';
                    fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? str : ('00' + str).substr(str.length))
                }
            }
            return fmt
        }
    },
    created: function () {
        var _this = this;

        // this._getMapJson();

        this.renderTime();
        this.renderDate();
        setInterval(function () {
            _this.renderTime();
        }, 1000);

        this._getBase();
        this._getWarn();
        this._getPartier();
        this._getGrid();
        this._getVolunteerData();
        this._getWiseData();
        this._getRankData();
        this._getProjectData();
        this._getHelpData();
    },
    mounted: function () {
        var _this = this;
        _this.initMap();

        setTimeout(function () {
            _this.claimRender();
            _this.realizeRender();
            // _this.lineRender();
            // _this.dealRender();
            _this.projectRender();
            _this.helpRender();
            _this.governRender();
            _this.lawRender();
            _this.tendency();
        }, 1500);

    },
});