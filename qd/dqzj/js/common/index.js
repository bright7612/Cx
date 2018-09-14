define(function (require) {

  var IScroll = require('IScroll');

  var initScroll = new IScroll('#wrapper',{

    scrollbars: 'custom', // 自定义滚动条

    interactiveScrollbars: true, // 滚动条拖动

    mouseWheel: true, // 鼠标滑轮

    /*fadeScrollbars: true,*/

    shrinkScrollbars: 'scale'

  });

  return initScroll

});