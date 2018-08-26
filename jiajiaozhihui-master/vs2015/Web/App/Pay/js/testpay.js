﻿$(function () {

    $("#add").click(function () {
        addPayNumber();
    })
    $("#subtraction").click(function () {
        subtractionPayNumber();
    })
    account();
    $("#cover").hide();
})
wx.ready(function () {
    $(document).on("click", "#btnPay", function (event) {
        onlinePay.addOrder();
    })
    onlinePay.getLatestAddress();
    toast.hide();
    $(document).on("click", "#btnEditAddress", function (event) {
        toast.show();
        onlinePay.editAddress();
        toast.hide();
    })
})
var onlinePay = {
    getLatestAddress: function () {
        var adr = this.userAddress;
        WeixinJSBridge.invoke("getLatestAddress", this.address(),
             function (res) {
                 alert(JSON.stringify(res));
                 if (res.err_msg == "get_recently_used_address:ok") {
                     adr(res);
                 }
             }
         );
    },
    editAddress: function () {
        var adr = this.userAddress;
        WeixinJSBridge.invoke("editAddress11", this.address(),
                    function (res) {
                        if (res.err_msg == "edit_address:ok") {
                            adr(res);
                        }
                    }
                );
    },
    address: function () {
        var d = eval("(" + $("#hfAddressParameters").val() + ")");
        alert($("#hfAddressParameters").val());
        return {
            appId: d.appId,
            scope: d.scope,
            signType: d.signType,
            addrSign: d.addrSign,
            timeStamp: d.timeStamp,
            nonceStr: d.nonceStr
        }
    },
    userAddress: function (res) {
        $("#hfProvince").val(res.proviceFirstStageName);
        $("#hfCity").val(res.addressCitySecondStageName);
        $("#hfDistrict").val(res.addressCountiesThirdStageName);
        $("#receivename").text(res.userName);
        $("#telephone").text(res.telNumber);
        $("#receiveaddress").text(res.proviceFirstStageName + " " + res.addressCitySecondStageName + " " + res.addressCountiesThirdStageName + " " + res.addressDetailInfo);
        $("#hfAddress").val(res.addressDetailInfo);
        $("#hfPostCode").val(res.addressPostalCode);
    },
    payData: function () {
        return {
            timestamp: wxJsPayApiParam.timeStamp, // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
            nonceStr: wxJsPayApiParam.nonceStr, // 支付签名随机串，不长于 32 位
            package: wxJsPayApiParam.package, // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
            signType: wxJsPayApiParam.signType, // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
            paySign: wxJsPayApiParam.paySign // 支付签名
        };
    },
    pay: function () {
        wx.chooseWXPay(this.payData(),
            function (res) {
                // 支付成功后的回调函数
            }
        )
    },
    checkUndefined: function (value) {
        if (value == undefined || value == '') {
            return true;
        }
        return false;
    },
    addOrder: function () {
        var openid = $("#hfOpenId").val();
        var price = $("#price").text();
        var number = $("#payNumber").text();
        var province = $("#hfProvince").val();
        var city = $("#hfCity").val();
        var telephone = $("#telephone").text();
        var tradeno = $("#hfTradeno").val();
        var address = $("#hfAddress").val();
        var name = $("#receivename").text();
        var postcode = $("#hfPostCode").val();
        var district = $("#hfDistrict").val();
        if (this.checkUndefined(openid) || this.checkUndefined(price) || this.checkUndefined(number) || this.checkUndefined(province) || this.checkUndefined(city) || this.checkUndefined(telephone) || this.checkUndefined(tradeno) || this.checkUndefined(address) || this.checkUndefined(name)) {
            alert("请选择与编辑你的收货地址");
            return false;
        }
        toast.hide();
        $this = this;
        $.ajax({
            url: "../server/qzsf.ashx?method=add",
            type: "POST",
            dataType: "JSON",
            data: { 'openid': '' + openid + '', 'goodsid': '76', 'price': '' + price + '', 'number': '' + number + '', 'province': '' + province + '', 'city': '' + city + '', 'telephone': '' + telephone + '', 'tradeno': '' + tradeno + '', 'address': '' + address + '', 'name': '' + name + '', 'postcode': '' + postcode + '', 'district': '' + district + '' },
            beforeSend: function () {
                toast.show("正在提交订单中");
            },
            complete: function () {
                toast.hide();
            },
            success: function (msg) {
                if (msg.code == 1) {
                    $this.pay();
                }
            }
        });
    }
};
function subtractionPayNumber() {
    var value = $("#payNumber").text();
    if (value > 1) {
        value -= 1;
        $("#payNumber").text(value);
        load();
        account();
    }
}
function addPayNumber() {
    var value = parseInt($("#payNumber").text());
    value += 1;
    $("#payNumber").text(value);
    load();
    account();
}
function account() {
    var pice = $("#price").text();
    var payNumber = $("#payNumber").text();
    $("#totalprice").text((pice * payNumber).toFixed(2));
}

function load() {
    var number = $("#payNumber").text();
    var price = $("#price").text();
    var openid = $("#hfOpenId").val();
    $.ajax({
        url: "../server/qzsf.ashx?method=pay",
        type: "POST",
        dataType: "JSON",
        data: { 'number': '' + number + '', 'openid': '' + openid + '', 'price': '' + price + '' },
        beforeSend: function () {
            toast.show("正在处理中");
        },
        complete: function () {
            toast.hide();
        },
        success: function (msg) {
            $("#hfTradeno").val(msg.tradeno);
            wxJsPayApiParam = msg;
        }
    })
};
/*数据加载*/
var toast = {
    that: $('#loadingToast'),
    defaultContent: "数据加载中",
    hide: function () {
        this.that.hide();
    },
    show: function (msg) {
        if (msg == '' || msg == undefined) {
            msg = this.defaultContent;
        }
        var $content = this.that.find('.weui_toast_content');
        $content.html(msg);
        this.that.show();
    }
};