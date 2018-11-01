$(function () {
    //obj 物质0 精神1
    //type  0 单位 1 个人
    //id 传递的参数
    //name  联系人姓名
    //card   身份证信息
    //phone  联系方式
    //party   党员信息
    //addr 地址
    //remark  备注
    var party;
    var obj = 0;
    var type = 0;
    getUrl('dataId');
    var form_submit = {
        id: id,//传参
        type: '1',//
        remark: '',//备注
        party: '',//所属党组织
        obj: '0',//心愿类型
        name: '',//姓名
        card: '',//身份证信息
        addr: ''//地址
    };
    //获取传递过来的dataId,即id
    function getUrl(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        id = r[2];
        if (r != null) return decodeURI(r[2]);
        return null;
    }

    form_submit.id = id;
    //身份证信息输入
    $('#card').on('blur', function () {
        var cardNum = $(this).val();
        var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
        if (reg.test(cardNum)) {
            getCard(cardNum);
        }
    });


    //点击单位个人时候显示隐藏
    $('#type').on('change', function () {
        type = $(this).find("option:selected").attr('value');
        form_submit.type = type;
    });
    $('#obj').on('change', function () {
        obj = $(this).find("option:selected").attr('value');
        form_submit.obj = obj;
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
        var text = textTest($('#name').val(), '姓名', 'name');
        if (text) {
            popShow(text);
            return false;
        }
        text = cardTest($('#card').val(), '身份证信息', 'card');
        if (text) {
            popShow(text);
            return false;
        }
        text = phoneTest($('#phone').val(), '联系方式', 'phone');
        if (text) {
            popShow(text);
            return false;
        }
        text= textTest($('#addr').val(),'联系地址','addr');
        if(text){
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

    function getCard(text) {
        $.ajax({
            type: 'GET',
            url: 'http://cxdj.cmlzjz.com/home/wxapi/party_identity',
            data: {identity: text},
            async: true,
            dataType: 'JSON',
            success: function (e) {
                //330522199406010237 党员
                //410927199302144022 非党员
                var code = e.code;
                var msg = e.msg;
                if (code == 202) {//非党员
                    $('#party').val(msg);
                    party = msg;
                } else if (code == 200) {
                    party = e.data.NAME;
                    $('#party').val(party);
                }
                form_submit.party = party;
            },
            error: function () {
                popShow('获取党员信息失败，请检查网络');
            }
        })
    }

    //点击提交按钮
    //点击提交按钮获取数据
    $('#submit').on('click', function () {
        if (testAll()) {
            form_submit.remark = $('#remark').val();
            console.log(form_submit);
            // popShow('信息提交成功');
            formSubmit();
            $('#popUp').on('click', '.sure', function () {
                // window.location.reload();
            })
        }
    });
    function formSubmit() {
        $.ajax({
            url: 'http://36.26.83.105:8620/cx/cx/receive_apply',
            type: 'GET',
            data: form_submit,
            async: true,
            dataType: 'JSON',
            success: function (e) {
                console.log("成功");
                popShow('信息提交成功');
            },
            error: function () {
                console.log("出错");
            }
        })
    }
});