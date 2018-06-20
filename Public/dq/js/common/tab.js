define(function (require) {

  var $ = require('jquery');

  function tabNav(el) {
    var Dom = {
      tabNav: $(el + ' .tab-nav .item'),
      box: $(el + ' .tab-content .item'),
      link: $(el + ' .tab-link .move')
    };

    for (var i = 0; i < Dom.tabNav.length; i++) {

      Dom.tabNav.eq(i).attr("data-id",i);

    }

    Dom.tabNav.on("click",function () {

      var index = Number($(this).attr('data-id'));

      Dom.tabNav.removeClass('item-active');$(this).addClass('item-active');

      Dom.box.removeClass('item-show');Dom.box.eq(index).addClass('item-show');

      Dom.link.removeClass('move-active');Dom.link.eq(index).addClass('move-active');

      scroll

    })
  }

  return tabNav

});
