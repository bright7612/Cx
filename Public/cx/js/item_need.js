/**
 * Created by Administrator on 2018/8/15.
 */
$(function () {
    //id 传递的参数
    //name  联系人姓名
    //phone  联系方式
    //remark  备注
    var party;
    getUrl('dataId');
    var form_submit = {
        id: id,//传参
        remark: ''//备注
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
    function testAll() {
        var text = textTest($('#name').val(), '姓名', 'name');
        if (text) {
            popShow(text);
            return false;
        }
        text = phoneTest($('#phone').val(), '联系方式', 'phone');
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
            formSubmit();
            $('#popUp').on('click', '.sure', function () {
                // window.location.reload();
                window.location.href="http://36.26.83.105:8620/cx/cx";
            })
        }
    });
    function formSubmit() {
        $.ajax({
            url: 'http://36.26.83.105:8620/cx/cx/ztc_demand',
            type: 'GET',
            data: form_submit,
            async: true,
            dataType: 'JSON',
            success: function (e) {
                console.log("成功");
                popShow('提交成功');
            },
            error: function () {
                console.log("出错");
            }
        })
    }
});