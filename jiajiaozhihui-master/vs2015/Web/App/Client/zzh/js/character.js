var url=window.location.href;
$.getJSON('http://161s5g6007.51mypc.cn/app/tool/WxJsCconfig.ashx',{url:url},function(res){
    console.log(res);
    wx.config({
        debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: res.appId, // 必填，公众号的唯一标识
        timestamp:res.timestamp, // 必填，生成签名的时间戳
        nonceStr:res.nonceStr, // 必填，生成签名的随机串
        signature: res.signature,// 必填，签名，见附录1
        jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','onMenuShareQZone'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
    });
})
wx.ready(function(){
    var data={
        title:'父母性格测试',
        type:'music',
        dataUrl:'http://161s5g6007.51mypc.cn/app/client/character/imgs/taiji.mp3',
        imgUrl:'http://161s5g6007.51mypc.cn/app/client/character/imgs/logo.png',
    };
    wx.onMenuShareTimeline(data);
    wx.onMenuShareAppMessage(data);
    wx.onMenuShareQQ(data);
    wx.onMenuShareWeibo(data);
})

$("#share").click(function(){
    $(".cover").show();
    setTimeout(function(){
        $(".cover").hide();
    },1500)
})

function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
};