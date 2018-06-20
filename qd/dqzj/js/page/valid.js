var Error = null;
define(function (require) {
  var fields = {
    apply: {
      title: {
        required: "请输入项目标题",
        failHtml: false,
        rule: 'required|disblur',
        fail: function(messages) {
          Error = messages
        },
        failStyle: false,
        pass: function() {
          Error = null
        }
      },
      num: {
        required: "请输入众筹人数",
        failHtml: false,
        rule: 'required|disblur|number',
        fail: function(messages) {
          Error = messages;
        },
        failStyle: false,
        pass: function() {
          Error = null
        }
      },
      date: {
        required: "请选择日期",
        failHtml: false,
        rule: 'required|disblur',
        fail: function(messages) {
          Error = messages;
        },
        failStyle: false,
        pass: function() {
          Error = null
        }
      },
      name: {
        required: "请输入您的姓名",
        failHtml: false,
        rule: 'required|disblur|onlyName(2,6)',
        fail: function(messages) {
          Error = messages;
        },
        failStyle: false,
        pass: function() {
          Error = null
        }
      },
      phone: {
        required: "请输入手机号码",
        failHtml: false,
        rule: 'required|disblur|onlyPhone',
        fail: function(messages) {
          Error = messages;
        },
        failStyle: false,
        pass: function() {
          Error = null
        }
      },
      content: {
        required: "请填写您的众筹简介",
        failHtml: false,
        rule: 'required|disblur|onlyLength(1,300)',
        fail: function(messages) {
          Error = messages;
        },
        failStyle: false,
        pass: function() {
          Error = null
        }
      }
    },
    message: {
      message: {
        required: "请输入您想说的话",
        failHtml: false,
        rule: 'required|disblur|onlyLength(1,500)',
        fail: function(messages) {
          Error = messages
        },
        failStyle: false,
        pass: function() {
          Error = null
        }
      },
      name: {
        required: "请输入您的姓名",
        failHtml: false,
        rule: 'required|disblur|onlyName(2,6)',
        fail: function(messages) {
          Error = messages;
        },
        failStyle: false,
        pass: function() {
          Error = null
        }
      }
    },
  };
  var rules = {
    apply: {
      onlyLength: function (val,num,num1) {

        if (val.length >= num && val.length <= num1) {
          return true
        } else {
          return "众筹简介字数控制在"+ num1 +"字以内！"
        }

      },
      onlyName: function (val,num,num1) {
        if (val.length >= num && val.length <= num1) {
          return true
        } else {
          return "请输入真实姓名！"
        }
      },
      onlyPhone:[/^1[34578]\d{9}$/,"手机号码有误"]
    },
    message: {
      onlyLength: function (val,num,num1) {

        if (val.length >= num && val.length <= num1) {
          return true
        } else {
          return "字数控制在"+ num1 +"以内！"
        }

      },
      onlyName: function (val,num,num1) {
        if (val.length >= num && val.length <= num1) {
          return true
        } else {
          return "请输入真实姓名！"
        }
      }
    }
  };
  return {
    apply: fields.apply,
    a_rules: rules.apply,
    message: fields.message,
    m_rules: rules.message
  }

});