$(function () {
    var detailId;
    var pop = 0;
    var displayNum = $('#displayInput').attr('value');
    var text='';
    $('#bespoke_num').attr('placeholder', '剩余可预约人数最多为' + displayNum + '人');
    function getUrl(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        detailId = r[2];
        if (r != null) return decodeURI(r[2]);
        return null;
    }

    getUrl('dataId');
    var form_up = {};
    form_up.id = detailId;


    $('#submit').on('click', function () {
        formSubmit();
    });

    //验证表单的完整和正则
    function test() {
        var type = $('#type>option:selected').val();
        //1. 验证姓名
        var ret;
        var name = $('#name').val();
        if (name.length >= 2) {
            form_up.name = name;
        } else {
            return "姓名不能为空";
        }
        //验证身份证号码
        ret = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
        var card=$('#card').val();
        if(ret.test(card)){
            form_up.ID_card=card;
        }else{
            return "身份证号码输入不合法";
        }
        //验证电话
        ret = /^(((13|14|15|18|17)\d{9}))$/;
        var phone = $('#phone').val();
        if (ret.test(phone)) {
            form_up.phone = phone;
        } else {
            return "联系方式输入格式不正确";
        }
        //验证党组织
        var organization = $('#organization>option:selected').val();
        if (organization == 0) {
            return "请选择所属党组织";
        } else {
            form_up.organization = organization;
        }
        //验证单位企业的名称
        var companty = $('#company').val();
        if (((type == 0) && (companty.length)) || (type == 1)) {
            form_up.company = companty;
        } else {
            return '请输入单位企业名称';
        }
        //验证预约人数人数的合法性
        var bespoke_num = $('#bespoke_num').val();
        ret = /^\d*$/;
        if ((bespoke_num.length > 0) && ret.test(bespoke_num) && (parseInt(bespoke_num) <= parseInt(displayNum)) && (parseInt(bespoke_num) > 0)) {
            form_up.bespoke_num = bespoke_num;
        } else {
            return '预约人数不能为空且必须小于' + displayNum;
        }
        var type = $('#type>option:selected').val();
        //单位0 个人1
        form_up.type = type;
        form_up.text=$('#text').val();
        pop=1;
        return false;
    }

    function formSubmit() {
        if (!test()) {
            console.log(form_up);
            $.ajax({
                type: 'GET',
                url: 'http://cxdj.cmlzjz.com/cx/cx/bespoke',
                data: form_up,
                dataType: 'JSON',
                success: function (e) {
                    popShow(e.msg, 1);
                },
                error: function () {
                    console.log("预约失败")
                }
            })
        } else {
            popShow(test(), 0);
        }
    }

    //弹窗显示
    function popShow(e, pop) {
        $('#popUp .pop_content').html(e);
        if (pop == 0) {//不上传数据只弹窗口
            $('#popUp').show().on('click', '.sure', function () {
                $(this).parent().hide();
            });
        }
        if (pop == 1) {//上传数据并刷新页面
            $('#popUp').show().on('click', '.sure', function () {
                $(this).parent().hide();
                window.location.reload();
            });
        }
    }

    popShow(1);
});