$(function () {

    var type = {
        //月薪制党员类型
        monthSalaryType : '<span>月税后收入</span>' +
        '<input id="monthSalary" type="text" placeholder="元">',
        //年薪制党员类型
        yearSalaryType : '<span>月实际收入</span>' +
        '<input id="yearSalary" type="text" placeholder="元">',
        //个体经营党员类型
        merchantType : '<span>上季度月平均收入</span>' +
        '<input id="merchant" type="text" placeholder="元">',
        //退休党员类型
        retireType : '<span>月离退休费或养老金总额</span>' +
        '<input id="retire" type="text" placeholder="元">',
        //退休返聘党员类型
        reworkType : '<div class="div-box">' +
        '<span>月离退休费或养老金总额</span>' +
        '<input id="rework1" type = "text" placeholder = "元" >' +
        '</div>' +
        '<div class="div-box">' +
        '<span>月返聘所得收入</span>' +
        '<input id="rework2" type="text" placeholder="元">' +
        '</div>',
        //特殊照顾党员类型
        specialType : '<span>您的身份享受党费特殊照顾政策</span>'
    }

    var partierType = $('#partierType').val();  //获取党员类型
    var template = null;

    typeChange();

    $('#partierType').on('change', function () {
        partierType = $(this).val();
        typeChange();
    })

    var payCost = null;
    //按钮事件监听
    $('#btn').on('click', function () {
        if ( partierType === '1' ) {
            var monthSalary = $('#monthSalary').val();
            if (!isSalaryLegal(monthSalary)) return;
            payCost = ((monthSalary - 0) / 100 * filterReward(monthSalary)).toFixed(2);
        };
        if ( partierType === '2' ) {
            var yearSalary = $('#yearSalary').val();
            if (!isSalaryLegal(yearSalary)) return;
            payCost = ((yearSalary - 0) / 100 * filterReward(yearSalary)).toFixed(2);
        };
        if ( partierType === '3' ) {
            var merchant = $('#merchant').val();
            if (!isSalaryLegal(merchant)) return;
            payCost = ((merchant - 0) / 100 * filterReward(merchant)).toFixed(2);
        };
        if ( partierType === '4' ) {
            var retire = $('#retire').val();
            if (!isSalaryLegal(retire)) return;
            payCost = ((retire - 0) / 100 * filterPension(retire)).toFixed(2);
        };
        if ( partierType === '5' ) {
            var rework1 = $('#rework1').val();
            var rework2 = $('#rework2').val();
            if ((!isSalaryLegal(rework1)) || (!isSalaryLegal(rework2))) return;
            rework1 = (rework1 - 0) / 100 * filterPension(rework1);
            rework2 = (rework2 - 0) / 100 * filterReward(rework2);
            payCost = (rework1 + rework2).toFixed(2);
        };
        if ( partierType === '6' ) payCost = 1;
        if ( partierType === '7' || partierType === '8' || partierType === '9' ) payCost = 0.2;


        // 修改意见说不要日期选择，只需计算一个月的党费，我先注释，以防万一。
        // var startDate = $('#demo1').val();
        // var endDate = $('#demo2').val();
        // console.log('startTime' + startTime);
        // console.log('endtime' + endTime);

        // var count = isDateLegal(startDate, endDate);

        // if (!count) {
        //     return;
        // } else {
        //     payCost = (payCost * count).toFixed(2);
        // }

        // $('#bg').find('#month').text(count).end()
        //     .find('#pay').text(payCost).end()
        //     .show();

        $('#bg').find('#pay').text(payCost)
            .end().show();
    });

    $('#close').on('click', function () {
        $('#bg').hide();
    });

    /*
     改变收入框中的显示内容
     */
    function typeChange() {
        if (partierType) {
            if ( partierType === '1' ) template = type.monthSalaryType;
            if ( partierType === '2' ) template = type.yearSalaryType;
            if ( partierType === '3' ) template = type.merchantType;
            if ( partierType === '4' ) template = type.retireType;
            if ( partierType === '5' ) template = type.reworkType;
            if ( partierType === '6' || partierType === '7' || partierType === '8' || partierType === '9') template = type.specialType;
        }
        $('#salary').html(template);
    }

    /*
     判断输入的收入是否合法
     */
    function isSalaryLegal(salary) {
        var isLegal = true;
        if (!salary) {
            alert('请输入您的收入');
            isLegal = false;
        } else if (isNaN(salary - 0)) {
            alert('请输入正确的数字格式');
            isLegal = false;
        }
        return isLegal;
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

    /*
     判断党费缴纳日期是否合法
     */
    function isDateLegal(dateStart, dateEnd) {
        if (dateStart === '' || dateEnd === '') {
            alert('请输入完整的起始日期');
            return false;
        }

        var regExpYear = /\d{4}/;
        var yearStart = regExpYear.exec(dateStart)[0];
        var yearEnd = regExpYear.exec(dateEnd)[0];
        var yearDiff = yearEnd - yearStart;

        var regExpMonth = /-(\d{2})/;
        var monthStart = regExpMonth.exec(dateStart)[1];
        var monthEnd = regExpMonth.exec(dateEnd)[1];
        var monthDiff = monthEnd - monthStart;

        if (yearDiff < 0 || (yearDiff == 0 && monthDiff < 0)) {
            alert('结束日期不能小于开始日期');
            return false;
        }

        var count = null;
        if (yearDiff == 0) {
            count = monthEnd - monthStart + 1;
        } else {
            count = (yearDiff - 1) * 12 + (13 - monthStart) + (monthEnd - 0);
        }
        return count;
    }
});
