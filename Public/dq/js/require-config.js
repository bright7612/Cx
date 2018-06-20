/**
 * Created by qiangxl on 2018/5/17.
 */
var amdDefine = define;
requirejs.config({
  paths: {
    // 核心工具库
    jquery: '/Public/dq/js/lib/jquery.min',
    lodash: '/Public/dq/js/lib/lodash.min',

    // require插件
    css: "/Public/dq/js/lib/require-css",

    // 表单验证
    SMV: '/Public/dq/js/lib/SMValidator.min',

    // 流加载
    flow: '/Public/dq/js/lib/flow/flow',

    // 弹层
    layer: '/Public/dq/js/lib/layer/layer.min',

    // 图片懒加载
    lazyload: "/Public/dq/js/lib/lazyload/lazyload.min",

    // 图片上传
    imgUploading: "/Public/dq/js/lib/tinyImgUpload-master/js/tinyImgUpload",

    // 时间、日期控件
    ICalendar: "/Public/dq/js/lib/ICalendar/js/lCalendar",

    swiper: "/Public/dq/js/lib/swiper/swiper-4.2.6.min",

    BScroll: "https://unpkg.com/better-scroll/dist/bscroll.min",
    IScroll: "https://cdn.bootcss.com/iScroll/5.2.0/iscroll.min",

    smartphoto: "/Public/dq/js/lib/smartphoto/js/smartphoto.min",

    // page
    valid: '/Public/dq/js/page/valid',
    index: '/Public/dq/js/page/index',
    scroll: '/Public/dq/js/common/index',
    tab: '/Public/dq/js/common/tab'
  },
  shim: {
    flow: ['jquery','css!/Public/dq/js/lib/flow/flow.css'],
    layer: ['jquery','css!/Public/dq/js/lib/layer/layer.css'],
    imgUploading: ["css!/Public/dq/js/lib/tinyImgUpload-master/css/tinyImgUpload.css"],
    ICalendar: ['jquery','css!/Public/dq/js/lib/ICalendar/css/lCalendar.css'],
    swiper: ['css!/Public/dq/js/lib/swiper/swiper-4.2.6.min.css'],
    scroll: ['IScroll'],
    smartphoto: ['css!/Public/dq/js/lib/smartPhoto/css/smartphoto.min.css']
  }
});