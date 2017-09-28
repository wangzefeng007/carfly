function gobj28(type) {

    $.ajax({
        type: "post",	//提交类型
        dataType: "json",	//提交数据类型
        url: '/Home/ajax/index',  //提交地址
        data:{
            "type":type
        },
        success: function (data) {
            if (data.ResultCode == 200) {
                    window.location.href = '/Home/Dan?T='+type;
            } else {
                alert(data.Message);
            }

        },

    });

}
function goJnd28(type) {
    $.ajax({
        type: "post",	//提交类型
        dataType: "json",	//提交数据类型
        url: '/Home/ajax/index',  //提交地址
        data:{
            "type":type
        },
        success: function (data) {
            if (data.ResultCode == 200) {
                    window.location.href = '/Home/Jnd?T='+type;
            } else {
                alert(data.Message);
            }

        },

    });

}