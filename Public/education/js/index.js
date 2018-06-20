$(function () {
    //swiper部分
    var swiper = new Swiper('.swiper-container', {
        effect: 'coverflow',
        grabCursor: true,
        centeredSlides: true,
        slidesPerView: 'auto',
        loop: true,
        autoplay: true
    });
    //第四部分的tab栏转换(党课预告、党课回顾)
    $('#partyClassBox').on('click','.tabs',function(){
        $('#partyClassBox').children('.tabs').css('z-index','1');
        $(this).css('z-index','2');
        var party_class=$(this).children('.text1').html();
        $('#party1').hide();
        $('#party2').hide();
        if(party_class=='党课预告'){
            $('#party1').show();
            $(this).parent().children('a').attr('href','Education/yugao');
        }
        if(party_class=='党课回顾'){
            $('#party2').show();
            $(this).parent().children('a').attr('href','Education/huigu');
        }
    })
});