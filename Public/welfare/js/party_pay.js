$(function () {
    var partyType = "1";
    var monthly_pay;
    var payCost;

    //选中触发事件
    $('select').on('change', function () {
        partyType = $('option:selected').val();
        $('.input_box>div').remove('.new,.long,.text');
        switch (partyType) {
            case "1": {
                $('.input_box').append(
                    "<div class='new'>" +
                    "<span>月税后收入</span>" +
                    "<input type='text' placeholder='  元'id='input1'>"
                    + "</div>");
                break;
            }
            case "2": {
                $('.input_box').append(
                    "<div class='new'>" +
                    "<span>月实际收入</span>" +
                    "<input type='text' placeholder='  元'id='input1'>"
                    + "</div>");
                break;
            }
            case "3": {
                $('.input_box').append(
                    "<div class='new'>" +
                    "<span>上季度月均收入</span>" +
                    "<input type='text' placeholder='  元'id='input1'>"
                    + "</div>");
                break;
            }
            case "4": {
                $('.input_box').append(
                    "<div class='long'>" +
                    "<span>月开退休费或养老金总额</span>" +
                    "<input type='text' placeholder='  元'id='input1'>"
                    + "</div>");
                break;
            }
            case "5": {
                $('.input_box').append(
                    "<div class='new'>" +
                    "<span>月返聘收入</span>" +
                    "<input type='text' placeholder='  元'id='input1'>"
                    + "</div>" +
                    "<div class='long'>" +
                    "<span>月开退休费或养老金总额</span>" +
                    "<input type='text' placeholder='  元'id='input2'>"
                    + "</div>");
                break;
            }
            case "6":
            case"7":
            case"8":
            case"9": {
                $('.input_box').append(
                    "<div class='text'>" +
                    "<span>您的身份享受党费特殊照顾政策</span>"
                    + "</div>");
                break;
            }
        }
    });
    //点击按钮
    $('.btn_box').on('click', '.btn', function () {
        getDate();
    });
    //获取参数
    function getDate() {
        //输入合法判断
        var reg = /^[0-9]*$/;
        if (partyType == "1" || partyType == "2" || partyType == "3" || partyType == "4") {
            if ((reg.test($('#input1').val())) && ($('#input1').val().length)) {
                var monthSalary = $('#input1').val();
                payCost = ((monthSalary - 0) / 100 * filterReward(monthSalary)).toFixed(2);
                popShow('您每月应缴纳的党费为'+payCost+'元');
            } else {
                popShow('请输入正确的收入信息');
            }
        } else if (partyType == "5") {
            if ((reg.test($('#input1').val())) && ($('#input1').val().length)) {
                if ((reg.test($('#input2').val())) && ($('#input2').val().length)) {
                    var rework1 = $('#input2').val();
                    var rework2 = $('#input1').val();
                    rework1 = (rework1 - 0) / 100 * filterPension(rework1);
                    rework2 = (rework2 - 0) / 100 * filterReward(rework2);
                    payCost = (rework1 + rework2).toFixed(2);
                    popShow('您每月应缴纳的党费为'+payCost+'元');
                    return;
                } else {
                    popShow('请输入正确的月离退休费或养老金总额');
                    return;
                }
            } else {
                popShow('请输入正确的月返聘收入信息');
                return;
            }
        } else if (partyType == "6") {
            popShow('您每月应缴纳的党费为1元');
        } else if (partyType == "7" || partyType == "8" || partyType == "9") {
            popShow('您每月应缴纳的党费为0.2元');
        }
    }

    function popShow(text) {
        $('.show_content').html(text);
        $('.party_show').show().on('click', '.btn', function () {
            $('.party_show').hide();
        })
    }

    /*
     计算收入报酬的转换费率
     */
    function filterReward(salary) {
        var salary = salary - 0;
        if (salary <= 3000) return 0.5;
        if (salary > 3000 && salary <= 5000) return 1;
        if (salary > 5000 && salary <= 10000) return 1.5;
        if (salary > 10000) return 2;
    }

    /*
     计算退休金或养老金的转换费率
     */
    function filterPension(salary) {
        var salary = salary - 0;
        if (salary <= 5000) return 0.5;
        if (salary > 5000) return 1;
    }
});