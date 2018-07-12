$(function () {
    //点击更换类别
    $('.tabs_box').on('click','.tab',function(){
        var value=$(this).attr('value');
        //横线的显示
        $(this).css('border-bottom','2px solid #f08418').siblings().css('border-bottom','2px solid transparent');
        //背景颜色的显示
        $('.tab_little').css({
            'color':'#e27000',
            'background':'transparent'
        });
        $(this).children('.tab_little').css({
            'color':'#ffffff',
            'background':'#f49436'
        });
        $('.box1').hide().eq(value).show();
    });
    //点击更换二维码显示
    $('.exchange_box').on('click','.exchange',function(){
        $(this).next().show();
        $(this).next().on('click',function(){
            $(this).hide();
        })
    })
});