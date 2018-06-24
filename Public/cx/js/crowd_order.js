$(function () {
    //id 传递的参数
    //name  联系人姓名
    //card   身份证信息
    //phone  联系方式
    //party   党员信息
    //type  0 单位 1 个人
    //order_num  预约人数
    //company 单位企业
    //remark  备注
    console.log(111);
    var party;
    getUrl('dataId');
    var form_submit = {
        id: id,//传参
        title:'',//项目标题
        phone:'',//联系电话
        crowd_num:'',//期望人数
        card:'',//身份证号码
        content: '',//备注
        name:'',//联系人姓名
    };
    var value = '0';
    //获取传递过来的dataId,即id
    function getUrl(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        id = r[2];
        if (r != null) return decodeURI(r[2]);
        return null;
    }

    form_submit.id = id;


    //点击单位个人时候显示隐藏
    $('#type').on('change', function () {
        $('div.danwei').show();
        value = $(this).find("option:selected").attr('value');
        if (value == '1') {
            $('div.danwei').hide();
        }
        form_submit.type = value;
    });

    function textTest(text, tip, form_key) {
        if (text.length > 0) {
            form_submit[form_key] = text;
            return false;
        } else {
            return tip + "不能为空";
        }
    }

    function phoneTest(text, tip, form_key) {
        var reg = /^(((13|14|15|18|17)\d{9}))$/;
        if (reg.test(text)) {
            form_submit[form_key] = text;
            return false;
        } else {
            return tip + "输入不合法，请重新输入";
        }
    }

    function cardTest(text, tip, form_key) {
        var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
        if (reg.test(text)) {
            form_submit[form_key] = text;
            return false;
        } else {
            return tip + "输入不合法，请重新输入";
        }
    }

    function numTest(text, tip, form_key) {
        var reg = /^\d*$/;
        if (text.length) {
            if (reg.test(text)) {
                form_submit[form_key] = text;
                return false;
            } else {
                return tip + "输入不合法，请重新输入";
            }
        } else {
            return tip + "输入不合法，请重新输入";
        }

    }

    function testAll() {
        var text = textTest($('#title').val(), '项目标题', 'title');
        if (text) {
            popShow(text);
            return false;
        }
        text = numTest($('#crowd_number').val(), '众筹人数', 'crowd_number');
        if (text) {
            popShow(text);
            return false;
        }
        text = textTest($('#time').val(), '期望时间', 'time');
        if (text) {
            popShow(text);
            return false;
        }
        text = textTest($('#name').val(), '联系人姓名', 'name');
        if (text) {
            popShow(text);
            return false;
        }
        text = phoneTest($('#phone').val(), '联系方式', 'phone');
        if (text) {
            popShow(text);
            return false;
        }
        text = cardTest($('#card').val(), '身份证信息', 'card');
        if (text) {
            popShow(text);
            return false;
        }
        return true;
    }

    function popShow(text) {
        $('#popUp .pop_content').html(text);
        $('#popUp').show();
        $('#popUp .sure').on('click', function () {
            $('#popUp').hide();
        })
    }

    //laydate的使用
    laydate.render({
        elem: '#time'
        ,type: 'date'
    });

    //点击提交按钮
    //点击提交按钮获取数据
    $('#submit').on('click', function () {
        testAll();
        if (testAll()) {
            form_submit.remark = $('#remark').val();
            if (value == '1') {
                form_submit.company = '';
            }
            console.log(form_submit);
            popShow('信息提交成功');
            $('#popUp').on('click', '.sure', function () {
                window.location.reload();
            })
        }
    });

});