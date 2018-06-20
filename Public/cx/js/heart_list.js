$(function () {
    //更换select事件
    var object = '';
    var form = '';
    var area = '';
    var status = '';
    changePage();
    $('#object').on('change', function () {
        object = $('option:selected', this).val();
        changePage();
    });
    $('#form').on('change', function () {
        form = $('option:selected', this).val();
        changePage()
    });
    $('#area').on('change', function () {
        area = $('option:selected', this).val();
        changePage()
    });
    $('#status').on('change', function () {
        status = $('option:selected', this).val();
        changePage();
    });
    //触发页面渲染
    function changePage() {
        var listSubmit = {
            object: object,
            form: form,
            area: area,
            status: status
        };
        $.ajax({
            url: 'http://192.168.1.254/wx/wx/wxylist',
            data: listSubmit,
            dataType: 'JSON',
            success: function (e) {
                var status = e.status;
                if (status == 1) {
                    var list = e.data.list;
                    console.log(list);
                    console.log("渲染数据");
                    $('#listBox').empty();
                    $('#listBox').html(template('list', list));
                    return;
                }
                if (status == -1) {
                    $('#listBox').empty();
                }
            },
            error: function (e) {
                console.log("失败");
            }
        })
    }
});