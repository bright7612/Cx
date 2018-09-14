/**
 * Created by qiangxl on 2018/5/17.
 */
var amdDefine = define;
requirejs.config({
  paths: {
    // 核心工具库
    jquery: 'lib/jquery.min',
    lodash: 'lib/lodash.min',

    // require插件
    css: "lib/require-css",

    // 表单验证
    SMV: 'lib/SMValidator.min',

    // 流加载
    flow: 'lib/flow/flow',

    // 弹层
    layer: 'lib/layer/layer.min',

    // 图片懒加载
    lazyload: "lib/lazyload/lazyload.min",

    // 图片上传
    imgUploading: "lib/tinyImgUpload-master/js/tinyImgUpload",

    // 时间、日期控件
    ICalendar: "lib/ICalendar/js/lCalendar",

    swiper: "lib/swiper/swiper-4.2.6.min",

    BScroll: "https://unpkg.com/better-scroll/dist/bscroll.min",
    IScroll: "https://cdn.bootcss.com/iScroll/5.2.0/iscroll.min",

    smartphoto: "lib/smartphoto/js/smartphoto.min",

    // page
    valid: 'page/valid',
    index: 'page/index',
    scroll: 'common/index',
    tab: 'common/tab'
  },
  shim: {
    flow: ['jquery','css!lib/flow/flow.css'],
    layer: ['jquery','css!lib/layer/layer.css'],
    imgUploading: ["css!lib/tinyImgUpload-master/css/tinyImgUpload.css"],
    ICalendar: ['jquery','css!lib/ICalendar/css/lCalendar.css'],
    swiper: ['css!lib/swiper/swiper-4.2.6.min.css'],
    scroll: ['IScroll'],
    smartphoto: ['css!lib/smartPhoto/css/smartphoto.min.css']
  }
});