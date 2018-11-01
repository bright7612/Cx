/**
 * Created by GM on 2018/4/6.
 */
var DOMAIN_CXDJ = 'http://36.26.83.105:8620'
var DOMAIN_183 = 'http://183.131.86.64:8620'

var apiUrl = {
    base: DOMAIN_CXDJ + '/home/wxapi/information', // 基础信息
    baseList: DOMAIN_CXDJ + '/home/wxapi/details2', // 基础信息列表
    baseMember: DOMAIN_CXDJ + '/home/wxapi/partyMember',

    partier: DOMAIN_CXDJ + '/cx/data/member', // 党员数据
    partierList: DOMAIN_CXDJ + '/cx/data/memberRecord', // 党员列表数据
    partierActivity: DOMAIN_CXDJ + '/cx/data/memberRecord2', // 党员列表数据

    warn: DOMAIN_CXDJ + '/home/wxapi/warning2', // 预警提醒
    warnlist: DOMAIN_CXDJ + '/home/wxapi/warningList2', // 预警提醒列表
    // http://cxdj.cmlzjz.com/home/wxapi/warning
    rank: 'http://wei.wiseljz.com/home/apibranch/integral', // 积分排名
    theme: DOMAIN_CXDJ + '/Cx/data/dzz_ztdr', // 主题党日一级数据
    theme2: DOMAIN_CXDJ + '/Cx/data/ztdr_activity', // 主题党日二级数据
    theme3: DOMAIN_CXDJ + '/Cx/data/dzz_detail', // 主题党日三级数据
    apply2: DOMAIN_CXDJ + '/cx/data/applyList', // 入党申请人二级数据
    apply3: './data/develop3.json', // 入党申请人三级数据
    activity2: DOMAIN_CXDJ + '/cx/data/apply_activity', // 入党积极分子二级数据
    devObj2: DOMAIN_CXDJ + '/Cx/data/developList', // 发展党员二级数据
    dxtj2: DOMAIN_CXDJ + '/Cx/data/dxtjList', // 党性体检二级数据
    pay2: DOMAIN_CXDJ + '/cx/data/moneyList', // 党费缴纳二级数据

    dyTheme2: DOMAIN_CXDJ + '/cx/data/ztdrList', // 党员模块主题党日二级数据
    dyTheme3: DOMAIN_CXDJ + '/cx/data/dt_ztdr_detail', // 党员模块主题党日三级数据
    dykp2: DOMAIN_CXDJ + '/cx/data/honorList', // 党员模块党员考评二级数据
    dykp3: DOMAIN_CXDJ + '/cx/data/honorDetail', // 党员模块党员考评三级数据
    report: DOMAIN_CXDJ + '/cx/data/report_registration', // 党员模块报到登记二三级数据

    home: 'http://a.wiseljz.com/api/local/getdatacount.html', // 家门口
    homeList: 'http://a.wiseljz.com/api/local/getdatalist', // 家门口列表

    volunteer: DOMAIN_CXDJ + '/cx/data/volunteer', // 志愿服务列表
    volunteerRecord: DOMAIN_CXDJ + '/cx/data/volunteerRecord', // 志愿服务表格

    wise: DOMAIN_CXDJ + '/cx/data/wxy', // 微心愿数
    requireList: DOMAIN_CXDJ + '/cx/data/wxyRecord', // 微心愿列表
    requireList2: DOMAIN_CXDJ + '/Cx/data/wxy_party', // 微心愿二级列表
    wiseDetail: 'http://192.168.1.254/cx/data/wxy', // 微心愿详情

    project: DOMAIN_CXDJ + '/cx/data/loveList', // 爱心众筹
    help: DOMAIN_CXDJ + '/cx/data/zc_help', // 党员创业互助

    map: DOMAIN_CXDJ + '/home/wxapi/geoMap',

    monitor: DOMAIN_CXDJ + '/home/wxapi/videos_dp', // 红色资源
    redResource: DOMAIN_CXDJ + '/home/wxapi/redResource', // 红色资源地图

    platform: DOMAIN_CXDJ + '/cx/data/platform', // 四个平台
    grid: DOMAIN_CXDJ + '/cx/data/WG', // 网格数据
    gridList: DOMAIN_CXDJ + '/cx/data/wgRecord', // 网格员总数

    shzzMap: DOMAIN_CXDJ + '/home/SocialApi/map', // 社会组织地图

    shzzBaseData: DOMAIN_CXDJ + '/home/SocialApi/SocialOrganization', // 社会组织基础数据
    shzzBaseList: DOMAIN_CXDJ + '/home/SocialApi/socialList', // 社会组织基础列表数据

    shzzActivityData: DOMAIN_CXDJ + '/home/SocialApi/social_activity', // 社会组织优秀活动展示列表

    shzzRank: DOMAIN_CXDJ + '/home/SocialApi/activity_ranking', // 社会组织活动数排名
    shzzRankList: DOMAIN_CXDJ + '/home/SocialApi/social_activity_list', // 社会组织活动数排名列表

    shzzActive: DOMAIN_CXDJ + '/home/SocialApi/social_active', // 社会组织社工活跃指数排名
    shzzActiveList: DOMAIN_CXDJ + '/home/SocialApi/social_active_list', // 社会组织社工活跃指数排名列表

    shzzServer: DOMAIN_CXDJ + '/home/SocialApi/social_service', // 社会组织服务信息接口
    shzzServerList: DOMAIN_CXDJ + '/home/SocialApi/social_service_list' // 社会组织服务信息列表接口
}

var $j = jQuery.noConflict();

var app = new Vue({
    el: '#app',
    data: {
        allScreen: false,
        bMap: '',
        bMap2: '',
        map: {
            myIcon: null,
            curMarker: null,
            mkUrl1: './imgs/marker/monitor1.png',
            mkUrl2: './imgs/marker/monitor2.png',
            mkUrl3: './imgs/marker/monitor3.png',
            title: '',

        },
        map2: {
            show: false,
            data: {
                address: '',
                list: [
                    {
                        organization: '',
                        member: '',
                        personnel: '',
                        classify: ''
                    }
                ]
            }
        },
        tabStatus: false,
        time: '',
        day: '',
        date: '',
        tabIndex: 1,
        showType: 1,
        partyType: 3,
        rightType2: 1,
        rightType3: 2,
        // proProgress1: 10,
        // proProgress2: 55,
        listData:{},
        listDataSecond: {},
        listDataThird: {},
        modelShow: false,
        modelSecondShow: false,
        modelThirdShow: false,
        flagThird: 0,
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
            },
            bigtype: 0,
            select: 0,
            classify: 0,
            order: 0,
            orderShow: false
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
                organize: {
                    theme: {
                        start: 0,
                        unStart: 0
                    },
                    partier: {
                        apply: 0,
                        active: 0,
                        develop_dy: 0
                    },
                    experience: {
                        num: 0,
                        unNum: 0
                    },
                    pay: {
                        payed: 0,
                        unPay: 0
                    }
                },
                partier: {
                    theme: {
                        join: 0,
                        unJoin: 0
                    },
                    experience: {
                        health: 0,
                        yaHealth: 0,
                        unHealth: 0
                    },
                    register: {
                        num: 0,
                        unNum: 0
                    },
                    appraise: {
                        sgyx: 0,
                        pypx: 0
                    }
                }
            },
            listData: {},
            type: 1,
            time: 1,
            origin: '',
        },
        // 积分排名数据
        rank: {
            show: false,
            title: '',
            org: [],
            people: []
        },
        // 四个平台
        platform: {
            type: 1,
            towns: [
                '开发区(太湖街道)',
                '雉城街道',
                '和平镇',
                '虹星桥镇',
                '洪桥镇',
                '画溪街道',
                '小浦镇',
                '夹浦镇',
                '李家巷镇',
                '林城镇',
                '图影管委会',
                '龙山街道',
                '吕山乡',
                '煤山镇',
                '南太湖管委会',
                '水口乡',
                '泗安镇'
            ],
            select: 1,
            selectShow: false,
            data: {
                bmfw: 0,
                zhzf: {
                    event_dy: 0,
                    event_fdy: 0
                },
                zhzl: {
                    hj: 0,
                    ld: 0
                }
            }
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
            show: false,
            tdid: '',
            startTime: '',
            endTime: ''
        },
        // 主题党日数据
        theme: {
            show: false,
            data: {}
        },

        // 人才模块数据
        rcAll: {
            talent: {
                total: 0,
                male: 0,
                female: 0,
                edu: [],
                jobTitle: [],
                qianren: {
                    country: 0,
                    province: 0
                }
            },
            audit: {
                running: 0,
                finished: 0
            },
            thisYear: 0,
            nearlyThreeYears: 0,
            notice: [],
            pm: [],
        },
        rcNotice: {
            show: false,
            data: {}
        },

        // 社会组织基础数据
        shzzBase: {
            social_organization: {
                social: 0,
                party: 0,
                employment: 0,
                party_member: 0
            },
            social_worker: {
                assistant_worker: 0,
                social_worker: 0,
                senior_worker: 0
            },
            social: [],
            education: []
        },
        // 社会组织优秀活动展示数据
        shzzList: {
            display: [],
            displayModel: false,
            displayModelShow: false,
            displayModelTitle: '',
            displayModelText: '',
            rank: [],
            rankModel: false,
            rankOrg: '',
            active: [],
            poor: {
                list: [],
                date: []
            },
            old: {
                list: [],
                date: []
            }
        }
    },
    computed: {
        bigTitle: function () {
            return this.partyType === 4 ? '长兴县社会组织党建数据平台' : '长兴县智慧党建大数据平台'
        },
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
        // 众筹数据/党群创业互助
        phTitle: function () {
            return this.rightType2 === 1 ? '项目众筹' : '党群创业互助';
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
        },
        // 排序文本
        orderText: function () {
            var index = this.base.order;
            return index === 0 ? '未排序' : index === 1 ? '倒序' : '正序'
        },
        // 警告弹框tab栏文本
        warnTabText: function () {
            return this.warn.type === 2 ? '开展主题党日' : '缴纳党费'
        },
        // 四个平台街镇名
        townName: function () {
            var name = this.platform.select
            return name === 1 ? '街镇（园区）' : name
        },
        // 千人计划
        thousandPlan: function () {
            var num = 0;
            this.rcAll.pm.forEach(function (value) {
                if (value.name === '千人计划') {
                    num = value.total
                }
            });
            return num
        }
    },
    watch: {
        'platform.select': function () {
            this._getGrid(this.platform.select);
            this._getPlatform(this.platform.select);
        }
    },
    methods: {
        // 图片地址拼接
        imgCDN: function (url) {
            return 'http://www.dysfz.gov.cn/' + url
        },
        // 改变地图大小
        changeMapSize: function () {
            this.allScreen = !this.allScreen;
        },
        // 改变地图选项伸缩
        changeTabStatus: function () {
            this.tabStatus = !this.tabStatus
        },
        changeOrder: function () {
            if (this.base.order === 1) {
                this.base.order = -2;
            }
            this.base.order++;
            this._getBaseData(0, 0, 0, this.base.order);
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
        // 社会组织地图初始化
        initMap2: function () {
            this.bMap2 = new BMap.Map("bmapShzz");
            var point = new BMap.Point(119.885592, 31.036098);
            this.bMap2.centerAndZoom(point, 14);
            this.bMap2.enableScrollWheelZoom(true);
        },
        // 切换四个平台
        changePlatform: function (type) {
            var oldType = this.platform.type;

            if (type === 1) {
                this.platform.select = 1;
                this.platform.selectShow = false;
            }
            if (oldType === 1 && type === 2) {
                this.platform.select = this.platform.towns[0];
            }
            if (oldType === 2 && type === 2) {
                this.platform.selectShow = true;
            }
            this.platform.type = type;
        },
        // 四个平台选择街镇
        selectTown: function (town) {
            this.platform.select = town;
            this.platform.selectShow = false;
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
                this.base.orderShow = true;
                if (type === 1 && select === 1 && (classify === 1 || classify === 2)) {
                    this.base.orderShow = false;
                }
                this._getBaseData(type, select, classify);
            }
            if (module === 'require') {
                this._getRequireData(type);
            }
            if (module === 'grid') {
                this._getGridList(type);
            }
            if (module === 'partier') {
                this._getPartierList();
            }
            if (module === 'volunteer') {
                this._getVolunteerRecord();
            }
            if (module === 'theme') {
                this._getThemeData();
            }
        },
        // 打开公用模态框详情
        openModelDetail: function (id, type, title, content, org, name, ztdrId, ztdrType, ztdr2Id, applyId, idCard, activityId, wxyId, devId, townId, payId, dyztdrId, dyztdr2Id, sgId, honorId, lateId, late2Id) {
            if (id && type === 'partyMember') {
                this._getBaseMember(id);
                return false;
            }
            if (id && type === 'activity') {
                this._getPartierActivity(id);
                return false;
            }
            if (id) {
                this._getWiseDetail(id);
            }
            if (this.shzzList.displayModel) {
                this.shzzList.displayModelTitle = title;
                this.shzzList.displayModelText = content;
                this.shzzList.displayModelShow = true;
            }
            if (org) {
                this._getShzzRankList(org);
            }
            if (name) {
                this._getShzzActiveList(name);
            }
            if (ztdrId && ztdrType) {
                this._getThemeData2(ztdrId, ztdrType);
            }
            if (ztdr2Id) {
                this._getThemeData3(ztdr2Id);
            }
            if (idCard) {
                this._getApplyData3(idCard);
            }
            if (wxyId) {
                this._getRequireData2(wxyId);
            }
            if (devId) {
                this.flagThird = 1;
                this._getDevObjData2(devId);
            }
            if (applyId) {
                this.flagThird = 2;
                this._getApplyData2(applyId);
            }
            if (activityId) {
                this.flagThird = 3;
                this._getActivityData2(activityId);
            }
            if (townId) {
                this._getDxtjData2(townId, type);
            }
            if (payId) {
                this._getPayData2(payId, type);
            }

            if (dyztdrId) {
                this._getDyThemeData2(dyztdrId, type);
            }
            if (dyztdr2Id) {
                this._getDyThemeData3(dyztdr2Id, type);
            }
            if (sgId) {
                this._getDykpData2(sgId, type);
            }
            if (honorId) {
                this._getDykpData3(honorId, type);
            }
            if (lateId) {
                this._getReportData2(lateId, type);
            }
            if (late2Id) {
                this._getReportData3(late2Id, type);
            }
        },
        // 关闭二级公用模态框
        closeSecondModel: function () {
            this.modelSecondShow = false;
        },
        // 关闭三级公用模态框
        closeThirdModel: function () {
            this.modelThirdShow = false;
        },
        // 关闭主题党日详情页
        closeThemeDetail: function () {
            this.theme.show = false;
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
            this.base.orderShow = false;
            this.base.order = 0;
            this.shzzList.displayModel = false;
            this.modelShow = false;
            this.developFlag = false;
        },
        // 打开预警提醒模态框
        openWarn: function (origin, type) {
            this._getWarnList(origin, type);
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
                belt: '示范点数量',
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
                                _this._addMarker(point, value[i], type);
                            }
                            // _this._addLine(arrPoint, colors[index]);
                        })
                        return false;
                    }

                    for (var i = 0; i < data.length; i++) {
                        var point = new BMap.Point(data[i].lng, data[i].lat);
                        _this._addMarker(point, data[i], type);
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

            this.monitorTimeInit(tdid);
            ButtonStartRealplayByWndNo_onclick(tdid);
        },
        // 红色资源监控时间选择初始化
        monitorTimeInit: function (tdid) {
            var _this = this;
            _this.resource.tdid = tdid;

            _this.laydate =  laydate.render({
                elem: '#test1',
                type: 'datetime',
                min: -7,
                max: 0,
                done: function (value, date){
                    if (value) {
                        _this.resource.startTime = (new Date(Date.parse(value.replace(/-/g,"/"))).getTime() / 1000).toString();

                        var month = date.month.length === 1 ? '0' + date.month : date.month;
                        var day = date.date.length === 1 ? '0' + date.date : date.date;
                        var endTimeStr = date.year + '-' + month + '-' + day + ' 23:59:50';
                        _this.resource.endTime = (new Date(Date.parse(endTimeStr.replace(/-/g,"/"))).getTime() / 1000).toString();
                    } else {
                        _this.resource.startTime = '';
                        _this.resource.endTime = '';
                    }
                }
            });

        },

        openRcNotice: function (item) {
            this.rcNotice.data = item;
            this.rcNotice.show = true;
        },
        closeRcNotice: function (item) {
            this.rcNotice.data = {};
            this.rcNotice.show = false;
        },

        // 社会组织打开公用模态框
        openShzzModel: function (social) {
            this._getShzzBaseList(social);
        },
        // 打开优秀活动展示框
        openDisplayDetail: function (title, content) {
            this.shzzList.displayModelTitle = title;
            this.shzzList.displayModelText = content;
            this.shzzList.displayModelShow = true;
        },
        // 关闭优秀活动展示框
        closeDisplayDetail: function () {
            this.shzzList.displayModelShow = false;
        },

        // 党员男女比例饼图
        genderRender: function () {
            var myChart = echarts.init(document.getElementById('gender'));
            var i = 0;
            var color = ['#ff4459', '#ffd441'];
            var option = {
                animation: false,
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
            var option = {
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
            var option = {
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
                    data: ['35岁以下', '35-50', '50-60', '60岁以上'],
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
            var option = {
                animation: false,
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
            var option = {
                animation: false,
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
        // 人才男女比例饼图
        rcGenderRender: function () {
            var myChart = echarts.init(document.getElementById('rcGender'));
            var i = 0;
            var color = ['#ff4459', '#ffd441'];
            var option = {
                animation: false,
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
                            {value: this.rcAll.talent.male, name:'男'},
                            {value: this.rcAll.talent.female, name:'女'},
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
        // 人才职称分布饼图
        rcJobRender: function () {
            var myChart = echarts.init(document.getElementById('rcJob'));
            var i = 0;
            var color = ['#00b8ff', '#ff9f52', '#a8ffff'];

            var list = [];
            this.rcAll.talent.jobTitle.forEach(function (value) {
                list.push({
                    value: value.total,
                    name: value.name
                });
            });

            var option = {
                animation: false,
                series : [
                    {
                        name: '职称分布',
                        type: 'pie',
                        radius : '100%',
                        center: ['50%', '50%'],
                        labelLine: {
                            normal: {
                                show: false
                            }
                        },
                        data: list,
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
        // 人才学历分布柱状图
        rcEduRender: function () {
            var _this = this;
            var myChartRate = echarts.init(document.getElementById('rcEdu'));

            var nameList = [];
            var totalList = [];
            this.rcAll.talent.edu.forEach(function (value) {
                nameList.push(value.name);
                totalList.push(value.total);
            });

            var option = {
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
                    data: nameList,
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
                        color: 'white',
                        fontSize: 14
                    }
                },
                series: [
                    {
                        name: '当年',
                        type: 'bar',
                        barWidth: 17,
                        data: totalList,
                        label: {
                            color: '#fff',
                            fontSize: 14
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

        // 微心愿已认领饼图
        claimRender: function () {
            var myChart = echarts.init(document.getElementById('claim'));
            var i = 0;
            var color = ['#ff4459', '#ffd441'];
            var option = {
                animation: false,
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
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
            var color = ['#00b6ff', '#ff4459', '#a4ffff'];
            var option = {
                animation: false,
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
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
                            {value: this.wise.doing, name:'进行中'},
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
            var option = {
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
                    data: ['交通', '公共', '公安', '消防', '卫计', '国土', '其他'],
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
                        barWidth: 20,
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
                        barWidth: 20,
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
        },
        // 网格饼图
        dealRender: function () {
            var myChart = echarts.init(document.getElementById('deal'));
            var i = 0;
            var color = ['#1de0ee', '#ff4459'];
            var option = {
                animation: false,
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
                            {value: this.platform.data.zhzl.hj, name:'户籍人员'},
                            {value: this.platform.data.zhzl.ld, name:'流动人员'},
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
            var option = {
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
                            {value: this.platform.data.zhzf.event_dy, name:'党员办结数'},
                            {value: this.platform.data.zhzf.event_fdy, name:'非党员办结数'}
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
            var option = {
                tooltip: {
                    trigger: 'axis'
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
                    data: ['01','02','03','04','05','06','07','08','09','10','11','12'],
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
                        name:'新增',
                        type:'line',
                        color: '#38bdff',
                        stack: '总量',
                        data: this.platform.data.event_current
                    },
                    {
                        name:'历史同期',
                        type:'line',
                        color: '#ffff19',
                        stack: '总量',
                        data: this.platform.data.event_lastyear
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
            var option = {
                animation: false,
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
            var option = {
                animation: false,
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
                            {value: this.help.complete, name:'已完成'},
                            {value: this.help.implement, name:'未完成'},
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
        // 社会组织类别柱状图
        shzzOrgRender: function () {
            var _this = this;
            var i = 0;
            var myChartRate = echarts.init(document.getElementById('shzzOrg'));
            var color = ['#F67C46', '#ffd441', '#CD3333', '#fff'];
            var option = {
                animation: false,
                series : [
                    {
                        name: '访问来源',
                        type: 'pie',
                        radius : '70%',
                        center: ['50%', '60%'],
                        labelLine: {
                            normal: {
                                show: false
                            }
                        },
                        data:[
                            {value: _this.shzzBase.social[0], name:'社团'},
                            {value: _this.shzzBase.social[1], name:'基金会'},
                            {value: _this.shzzBase.social[2], name:'民非'},
                            {value: _this.shzzBase.social[3], name:'其他'},
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
            // var option = {
            //     tooltip: {
            //         trigger: 'axis',
            //         axisPointer: {
            //             type: 'shadow'
            //         }
            //     },
            //     grid: {
            //         left: '0%',
            //         right: '0%',
            //         bottom: '0%',
            //         top: '8%',
            //         containLabel: true,
            //         borderColor: '#fff',
            //         borderWidth: 1
            //     },
            //     xAxis: {
            //         type: 'category',
            //         data: ['社团', '基金会', '民非', '其他'],
            //         axisLabel: {
            //             show: true,
            //             textStyle: {
            //                 color: '#FFF',
            //                 fontSize: 12
            //             }
            //         },
            //         axisLine:{
            //             lineStyle: {
            //                 color: '#48b8f0',
            //                 width: 2
            //             }
            //         },
            //         axisTick: {
            //             show: false
            //         },
            //     },
            //     yAxis: {
            //         type: 'value',
            //         boundaryGap: [0, 0.01],
            //         axisLabel: {
            //             formatter: '{value}',
            //             textStyle: {
            //                 color: '#FFF',
            //                 fontSize: 12
            //             }
            //         },
            //         axisLine:{
            //             lineStyle: {
            //                 color: '#48b8f0',
            //                 width: 2
            //             }
            //         },
            //         axisTick: {
            //             show: false
            //         },
            //         splitNumber: 2,
            //         splitLine:{
            //             show:false
            //         }
            //     },
            //     label: {
            //         show: true,
            //         position: 'top',
            //         textStyle: {
            //             color: 'white'
            //         }
            //     },
            //     series: [
            //         {
            //             name: '类别',
            //             type: 'bar',
            //             barWidth: 12,
            //             data: _this.shzzBase.social,
            //             label: {
            //                 color: '#fff'
            //             },
            //             itemStyle:{
            //                 normal:{
            //                     color:'#f6db46',
            //                 }
            //             }
            //         }
            //     ]
            // };
            myChartRate.setOption(option);
            myChartRate.on("click", function(param) {
                if (typeof param.seriesIndex == 'undefined') {
                    return;
                }
                if (param.type == 'click') {
                    var types = ['shetuan', 'jijinhui', 'minfei', 'qita'];
                    _this.openShzzModel(types[param.dataIndex])
                }
            });
        },
        // 社工学历分布柱状图
        shzzEduRender: function () {
            var _this = this;
            var myChartRate = echarts.init(document.getElementById('shzzEdu'));
            var option = {
                grid: {
                    left: '0%',
                    right: '0%',
                    bottom: '0%',
                    top: '8%',
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
                        name: '学历',
                        type: 'bar',
                        barWidth: 12,
                        data: _this.shzzBase.education,
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
            myChartRate.on("click", function(param) {
                if (typeof param.seriesIndex == 'undefined') {
                    return;
                }
                if (param.type == 'click') {
                    var types = ['gaozhong', 'dazhuan', 'benke', 'yanjiusheng'];
                    _this.openShzzModel(types[param.dataIndex])
                }
            });
        },
        // 扶贫济弱折线图
        inforPoorRender: function () {
            var myChart = echarts.init(document.getElementById('inforPoor'));
            var option = {
                tooltip: {
                    trigger: 'axis'
                },
                grid: {
                    left: '5%',
                    right: '5%',
                    top: '5%',
                    bottom: '5%',
                    containLabel: true,
                    borderColor: '#fff',
                    borderWidth: 1
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: this.shzzList.poor.date,
                    axisLabel: {
                        show: true,
                        textStyle: {
                            color: '#FFF',
                            fontSize: 14
                        }
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#48b8f0',
                            width: 2
                        }
                    }
                },
                yAxis: {
                    type: 'value',
                    axisLabel: {
                        formatter: '{value}',
                        textStyle: {
                            color: '#FFF',
                            fontSize: 14
                        }
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#48b8f0',
                            width: 2
                        }
                    },
                    splitNumber: 3,
                },
                series: [
                    {
                        name:'新增',
                        type:'line',
                        color: '#fcfd1a',
                        stack: '总量',
                        data: this.shzzList.poor.list
                    }
                ]
            };
            myChart.setOption(option);
        },
        // 关爱老人折线图
        inforOldRender: function () {
            var myChart = echarts.init(document.getElementById('inforOld'));
            var option = {
                tooltip: {
                    trigger: 'axis'
                },
                grid: {
                    left: '5%',
                    right: '5%',
                    top: '5%',
                    bottom: '5%',
                    containLabel: true,
                    borderColor: '#fff',
                    borderWidth: 1
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: this.shzzList.old.date,
                    axisLabel: {
                        show: true,
                        textStyle: {
                            color: '#FFF',
                            fontSize: 14
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
                            fontSize: 14
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
                        name:'新增',
                        type:'line',
                        color: '#86e319',
                        stack: '总量',
                        data: this.shzzList.old.list
                    }
                ]
            };
            myChart.setOption(option);
        },

        closeMap2: function () {
            this.map2.show = false;
        },
        // 创建覆盖物
        _addMarker: function (point, data, types) {
            var _this = this;
            var url = '';
            // if (data.tdid) {
            //     url = this.map.mkUrl2;
            //     if (data.status == '100') {
            //         url = this.map.mkUrl1;
            //     }
            // } else {
            //     url = this.map.mkUrl3;
            // }
            if (data.tdid) {
                url = './imgs/marker/' + types + '1.png';
            } else {
                url = './imgs/marker/' + types + '2.png';
            }
            this.map.myIcon = new BMap.Icon(url, new BMap.Size(32, 32));
            // 创建标注对象并添加到地图
            var marker = new BMap.Marker(point, {icon: this.map.myIcon});
            marker.data = data;

            this.bMap.addOverlay(marker);
            if (data.tdid && data.status == 100) {
                marker.setAnimation(BMAP_ANIMATION_BOUNCE);
            }

            var label = new BMap.Label(marker.data.address, {
                offset: new BMap.Size(20,-10)
            });
            label.setStyle({
                borderColor: '#FFF'
            });
            marker.setLabel(label);

            // 监听覆盖物点击
            marker.addEventListener("click", function () {
                _this._attribute(marker);
            });

            // var label = new BMap.Label(data.status,{offset:new BMap.Size(20,-10)});
            // marker.setLabel(label);
        },
        _addMarker2: function (point, data) {

            var _this = this;
            var iconFlag = false;
            data.content.forEach(function (val) {
                if (val.party === '是') {
                    iconFlag = true
                }
            })
            if (iconFlag) {
                var icon = new BMap.Icon('./imgs/marker/lg.png', new BMap.Size(27, 23));
                var marker = new BMap.Marker(point, {icon: icon});  // 创建标注
            } else {
                var marker = new BMap.Marker(point);
            }

            marker.data = data;
            this.bMap2.addOverlay(marker);

            var label = new BMap.Label(marker.data.address, {
                offset: new BMap.Size(20,-10)
            });
            label.setStyle({
                borderColor: '#FFF'
            });
            marker.setLabel(label);

            marker.addEventListener("click", function () {
                var p = marker.getPosition();
                _this.bMap2.panTo(p);
                _this.map2.show = true;
                _this.map2.data.address = marker.data.address;
                _this.map2.data.list = marker.data.content;
            });

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
                url: apiUrl.base,
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
        _getBaseData: function (type, select, classify, order) {
            var _this = this;
            if (!type) {type = this.base.bigtype}
            if (!select) {select = this.base.select}
            if (!classify) {classify = this.base.classify}
            this.base.bigtype = type;
            this.base.select = select;
            this.base.classify = classify;

            $j.ajax({
                type: 'GET',
                url: apiUrl.baseList,
                dataType: 'json',
                data: {
                    type: type,
                    subtype: select,
                    classify: classify,
                    order: order
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
        // 获取基础信息党员数据
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
        // 获取党员列表数据
        _getPartierList: function () {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.partierList,
                dataType: 'json',
                cache: false,
                success: function (res) {
                    if (res.status === 200) {
                        _this.listData = res.data;
                        _this.modelShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        // 获取党员列表活动数据
        _getPartierActivity: function (id) {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.partierActivity,
                data: {
                    id: id
                },
                dataType: 'json',
                cache: false,
                success: function (res) {
                    if (res.status === 200) {
                        _this.listDataSecond = res.data;
                        _this.modelSecondShow = true;
                    }
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
        _getWarnList: function (origin, type, time) {
            var _this = this;
            this.warn.origin = origin;
            if (!type) {type = this.warn.type}
            if (!time) {time = 1}
            this.warn.type = type;
            this.warn.time = time;

            $j.ajax({
                type: 'GET',
                url: apiUrl.warnlist,
                data: {
                    origin: origin,
                    types: type,
                    classify: time
                },
                dataType: 'json',
                cache: false,
                success: function (res) {
                    var data = res;
                    if (data.code === 200) {
                        // if (type === 2 || (origin === 'organize' && type === 8)) {
                        //     _this.warn.listData = data.data;
                        //     _this.warn.show = true;
                        // } else {
                        //     _this.listData = data.data;
                        //     _this.modelShow = true;
                        // }
                        _this.listData = data.data;
                        _this.modelShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取主题党日列表数据
        _getThemeData: function ()  {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.theme,
                dataType: 'json',
                cache: false,
                success: function (res) {
                    var data = res;
                    if (data.status === 200) {
                        _this.listData = res.data;
                        _this.modelShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取主题党日二级列表数据
        _getThemeData2: function (ztdrId, ztdrType)  {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.theme2,
                dataType: 'json',
                data: {
                    BRANCH_ID: ztdrId,
                    TYPE: ztdrType
                },
                cache: false,
                success: function (res) {
                    if (res.status === 200) {
                        _this.listDataSecond = res.data;
                        _this.modelSecondShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取主题党日三级详情数据
        _getThemeData3: function (ztdr2Id)  {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.theme3,
                dataType: 'json',
                data: {
                    ACTIVITY_ID: ztdr2Id
                },
                cache: false,
                success: function (res) {
                    if (res.status === 200) {
                        _this.theme.data = res.data;
                        _this.theme.show = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取发展党员入党申请人二级列表数据
        _getApplyData2: function (applyId) {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.apply2,
                dataType: 'json',
                data: {
                    town_id: applyId
                },
                cache: false,
                success: function (res) {
                    if (res.status === 200) {
                        _this.listDataSecond = res.data;
                        _this.modelSecondShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取发展党员入党申请人三级列表数据
        _getApplyData3: function (idCard) {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: './data/develop' + this.flagThird + '.json',
                dataType: 'json',
                // data: {
                //     town_id: applyId
                // },
                cache: false,
                success: function (res) {
                    if (res.status === 200) {
                        _this.listDataThird = res.data;
                        _this.modelThirdShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取发展党员入党积极分子二级列表数据
        _getActivityData2: function (activityId) {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.activity2,
                dataType: 'json',
                data: {
                    town_id: activityId
                },
                cache: false,
                success: function (res) {
                    if (res.status === 200) {
                        _this.listDataSecond = res.data;
                        _this.modelSecondShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取发展党员发展对象二级列表数据
        _getDevObjData2: function (devId) {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.devObj2,
                dataType: 'json',
                data: {
                    branch_id: devId
                },
                cache: false,
                success: function (res) {
                    if (res.status === 200) {
                        _this.listDataSecond = res.data;
                        _this.modelSecondShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取党性体检二级列表数据
        _getDxtjData2: function (townId, townType) {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.dxtj2,
                dataType: 'json',
                data: {
                    town_id: townId,
                    type: townType
                },
                cache: false,
                success: function (res) {
                    if (res.status === 200) {
                        _this.listDataSecond = res.data;
                        _this.modelSecondShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取党费缴纳二级列表数据
        _getPayData2: function (payId, payType) {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.pay2,
                dataType: 'json',
                data: {
                    BRANCH_ID: payId,
                    type: payType
                },
                cache: false,
                success: function (res) {
                    if (res.status === 200) {
                        _this.listDataSecond = res.data;
                        _this.modelSecondShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },

        // 获取主题党日二级列表数据
        _getDyThemeData2: function (dyztdrId, type)  {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.dyTheme2,
                dataType: 'json',
                data: {
                    branch_id: dyztdrId,
                    type: type
                },
                cache: false,
                success: function (res) {
                    if (res.status === 200) {
                        _this.listDataSecond = res.data;
                        _this.modelSecondShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取主题党日三级详情数据
        _getDyThemeData3: function (dyztdr2Id, type)  {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.dyTheme3,
                dataType: 'json',
                data: {
                    branch_id: dyztdr2Id,
                    type: type
                },
                cache: false,
                success: function (res) {
                    if (res.status === 200) {
                        _this.listDataThird = res.data;
                        _this.modelThirdShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取党员考评二级列表数据
        _getDykpData2: function (sgId, type)  {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.dykp2,
                dataType: 'json',
                data: {
                    BRANCH_ID: sgId,
                    type: type
                },
                cache: false,
                success: function (res) {
                    if (res.status === 200) {
                        _this.listDataSecond = res.data;
                        _this.modelSecondShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取党员考评三级详情数据
        _getDykpData3: function (honorId, type)  {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.dykp3,
                dataType: 'json',
                data: {
                    BRANCH_ID: honorId,
                    type: type
                },
                cache: false,
                success: function (res) {
                    if (res.status === 200) {
                        _this.listDataThird = res.data;
                        _this.modelThirdShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取报到登记二级列表数据
        _getReportData2: function (lateId, type) {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.report,
                dataType: 'json',
                data: {
                    lateId: lateId,
                    type: type
                },
                cache: false,
                success: function (res) {
                    if (res.code === 200) {
                        _this.listDataSecond = res.data;
                        _this.modelSecondShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取报到登记三级列表数据
        _getReportData3: function (late2Id, type) {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.report,
                dataType: 'json',
                data: {
                    late2Id: late2Id,
                    type: type
                },
                cache: false,
                success: function (res) {
                    if (res.code === 200) {
                        _this.listDataThird = res.data;
                        _this.modelThirdShow = true;
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
        _getGrid: function(name) {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.grid,
                data: {
                  name: name
                },
                dataType: 'json',
                cache: false,
                success: function (res) {
                    if (res.code === 200) {
                        _this.home.years = res.data.event_year
                        _this.home.monthes = res.data.event_month
                        _this.home.all = res.data.total;
                        _this.home.done = res.data.fdy;
                        _this.home.ordinary = res.data.dy;
                        _this.lineRender();
                        _this.dealRender();
                    }

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
                  name: _this.platform.select,
                  classif: type
                },
                dataType: 'json',
                cache: false,
                success: function (res) {
                    console.log(res);
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
        // 获取四个平台数据
        _getPlatform: function(name) {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.platform,
                data: {
                  name: name
                },
                dataType: 'json',
                cache: false,
                success: function (res) {
                    if (res.code === 200) {
                        _this.platform.data = res.data;
                        _this.governRender();
                        _this.lawRender();
                        _this.tendency();
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
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
        // 获取志愿服务数据
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
        // 获取志愿服务表格数据
        _getVolunteerRecord: function () {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.volunteerRecord,
                dataType: 'json',
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
                    // _this.wise.complete = data.wxy_db;
                    // _this.wise.uncomplete = data.wxy_ed;
                    _this.wise.realized = data.already;
                    _this.wise.realizeNot = data.wei;
                    _this.wise.all = data.count;
                    // _this.claimRender();
                    _this.realizeRender();
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
        // 获取微心愿二级列表数据
        _getRequireData2: function (id, zcType) {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.requireList2,
                dataType: 'json',
                data: {
                    VOLUNTEER_ID: id,
                    type: zcType
                },
                cache: false,
                success: function (res) {
                    var data = res;
                    if (data.status === 200) {
                        _this.listDataSecond = data.data;
                        _this.modelSecondShow = true;
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
                    _this.projectRender();
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
                    _this.helpRender();
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

        // 获取人才全部数据
        _getRcAllData () {
            var _this = this;

            $j.ajax({
                type: 'POST',
                url: 'http://39.104.133.117:8080/cxrc/weixin/summary/info',
                dataType: 'json',
                cache: false,
                success: function (res) {
                    if (res.status === true) {
                        _this.rcAll = res.data;
                        _this.rcGenderRender();
                        _this.rcJobRender();
                        _this.rcEduRender();
                    }
                    console.log(res);
                },
                error: function (err) {
                    console.log(err);
                }
            })
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
        // 获取社会组织基本数据
        _getShzzBaseData: function () {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.shzzBaseData,
                dataType: 'json',
                cache: false,
                success: function (data) {
                    if (data.code === 200) {
                        _this.shzzBase = data.data;

                        _this.shzzOrgRender();
                        _this.shzzEduRender();
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        // 获取社会组织基础列表数据
        _getShzzBaseList: function (classify) {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.shzzBaseList,
                data: {
                    classify: classify
                },
                dataType: 'json',
                cache: false,
                success: function (data) {
                    if (data.code === 200) {
                        console.log(data);
                        _this.listData = data.data;
                        _this.modelShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        // 获取社会组织优秀活动展示数据
        _getShzzActivity: function (more) {
            var _this = this;
            var data = {};
            if (more) {
                data = {
                    classify: 'list'
                }
            }
            $j.ajax({
                type: 'GET',
                url: apiUrl.shzzActivityData,
                data: data,
                dataType: 'json',
                cache: false,
                success: function (data) {
                    if (data.code === 200) {
                        if (more) {
                            _this.listData = data.data;
                            _this.modelShow = true;
                            _this.shzzList.displayModel = true;
                            return false;
                        }
                        _this.shzzList.display = data.data
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        // 获取社会组织活动数排名数据
        _getShzzRank: function (more) {
            var _this = this;
            var data = {};
            if (more) {
                data = {
                    classify: 'list'
                }
            }
            $j.ajax({
                type: 'GET',
                url: apiUrl.shzzRank,
                data: data,
                dataType: 'json',
                cache: false,
                success: function (data) {
                    if (data.code === 200) {
                        if (more) {
                            _this.listData = data.data;
                            _this.modelShow = true;
                            return false;
                        }
                        _this.shzzList.rank = data.data
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        // 获取社会组织活动数排名详情数据
        _getShzzRankList: function (organization) {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.shzzRankList,
                dataType: 'json',
                data: {
                    organization: organization
                },
                cache: false,
                success: function (res) {
                    if (res.code === 200) {
                        _this.listDataSecond = res.data;
                        _this.modelSecondShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取社会组织活跃指数数据
        _getShzzActive: function (more) {
            var _this = this;
            var data = {};
            if (more) {
                data = {
                    classify: 'list'
                }
            }
            $j.ajax({
                type: 'GET',
                url: apiUrl.shzzActive,
                data: data,
                dataType: 'json',
                cache: false,
                success: function (data) {
                    if (data.code === 200) {
                        if (more) {
                            _this.listData = data.data;
                            _this.modelShow = true;
                            return false;
                        }
                        _this.shzzList.active = data.data
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        // 获取社会组织活跃指数排名数据
        _getShzzActiveList: function (name) {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.shzzActiveList,
                dataType: 'json',
                data: {
                    name: name
                },
                cache: false,
                success: function (res) {
                    if (res.code === 200) {
                        _this.listDataSecond = res.data;
                        _this.modelSecondShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取社会组织服务信息数据
        _getShzzServerData: function () {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.shzzServer,
                dataType: 'json',
                cache: false,
                success: function (res) {
                    if (res.code === 200) {
                        var poverty = res.data.poverty;
                        if (poverty.length) {
                            poverty.forEach(function (val, index) {
                                _this.shzzList.poor.list.push(parseInt(val.count))
                                _this.shzzList.poor.date.push(index + 1)
                            })
                        }
                        var care = res.data.care;
                        if (care.length) {
                            care.forEach(function (val, index) {
                                _this.shzzList.old.list.push(parseInt(val.count))
                                _this.shzzList.old.date.push(index + 1)
                            })
                        }

                        _this.inforPoorRender();
                        _this.inforOldRender();
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取社会组织服务信息列表数据
        _getShzzServerList: function (classify) {
            var _this = this;
            $j.ajax({
                type: 'GET',
                url: apiUrl.shzzServerList,
                dataType: 'json',
                data: {
                    classify: classify
                },
                cache: false,
                success: function (res) {
                    if (res.code === 200) {
                        _this.listData = res.data;
                        _this.modelShow = true;
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            })
        },
        // 获取社会组织地图信息数据
        _getShzzMapData: function () {
            var _this = this;

            $j.ajax({
                type: 'GET',
                url: apiUrl.shzzMap,
                dataType: 'json',
                cache: false,
                success: function (data) {
                    if (data.code === 200 && data.data.length > 0) {
                        var data = data.data;

                        for (var i = 0; i < data.length; i++) {
                            var point = new BMap.Point(data[i].lng, data[i].lat);
                            _this._addMarker2(point, data[i]);
                        }

                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });

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

        this._getMapJson();

        this.renderTime();
        this.renderDate();
        setInterval(function () {
            _this.renderTime();
        }, 1000);

        this._getBase();
        this._getWarn();
        this._getPartier();
        this._getPlatform(this.platform.select);
        this._getGrid(this.platform.select);
        this._getVolunteerData();
        this._getWiseData();
        this._getRankData();
        this._getProjectData();
        this._getHelpData();
    },
    mounted: function () {
        var _this = this;
        _this.initMap();
        _this.initMap2();

        this._getRcAllData ();

        this._getShzzBaseData();
        this._getShzzActivity();
        this._getShzzRank();
        this._getShzzActive();
        this._getShzzServerData();
        this._getShzzMapData();
    },
});
