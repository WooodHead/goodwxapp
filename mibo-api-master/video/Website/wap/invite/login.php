<?php 
    include('../../init.php');
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
    if($user_id) {
        $user_info_sql = 'select user.nick_name from mb_user as user where id = ' . $user_id;
        $user_info = $pdo->query($user_info_sql)->row_array();
        if(!empty($user_info)) {
            $nick_name = $user_info['nick_name'];
        } else {
            $nick_name = '你的朋友';
        }
    }
 ?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta name="format-detection" content="telephone=no">
    <meta name="viewport"
          content="width=device-width,target-densitydpi=medium-dpi,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" type="text/css" href="./src/land.css">
    <link rel="stylesheet" type="text/css" href="./src/slick.css">
    <link rel="stylesheet" type="text/css" href="./src/md_pop.css">
    <script type="text/javascript" src="./src/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="./src/slick.min.js"></script>
    <script type="text/javascript" src="./src/MdPop.js"></script>
    <script type="text/javascript" src="./src/jquery-ui.min.js"></script>
    <script type="text/javascript" src="./src/jquery.floatingmessage.js"></script>
    <script type="text/javascript" src="./src/hyip.js"></script>
    <script src="./src/jquery.form.js" type="text/javascript"></script>

    <!-- 微信朋友圈、微博、发送好友 S -->
    <script type="text/javascript">
        var presenterCode = '34929198';
        var sign = '32d413e5ee16e78e6f9ebc31b9a61193';
    </script>
    <script type="text/javascript" src="./src/weixin.js"></script>
    <!-- 微信朋友圈、微博、发送好友 E -->


    <script type="text/javascript">
        var presenterCode = '34929198';
        var sign = '32d413e5ee16e78e6f9ebc31b9a61193';
        var noRefresh = false;
        var isPicCode = 2;

        $(window).load(function () {
            $(":text").val("");
        });

        // JavaScript Document
        String.prototype.trim = function () {
            return this.replace(/(^\s*)|(\s*$)/g, "");
        }

        var mb_user = {};

        mb_user.isEmpty = function (msg) {
            return !/.+/.test(msg);
        }
        //弹出dialog
        mb_user.dialog = function (option) {
            var defaults = {
                popBtn: "",
                shadow: 1,
                popBox: "",
                closeTag: $(".jsClose2"),
                eventType: 'click'
            };
            $.extend(defaults, option);

            function addShadow() {
                var shadow = '<div id="user_shadow"></div>';
                if ($("#user_shadow")[0]) {
                    $("#user_shadow").show();
                } else {
                    $(defaults.popBox).before(shadow);
                }
            };

            function popWin() {
                var obj = defaults.popBox,
                        h = obj.outerHeight(),
                        w = obj.outerWidth();
                obj.css({
                    "position": "fixed",
                    "left": "50%",
                    "top": "50%",
                    "margin-left": -w / 2,
                    "margin-top": -h / 2,
                    "z-index": "100000"
                });
                obj.show();
            };

            function closeWin() {
                defaults.popBox.hide();
                $("#user_shadow").hide();
                if (defaults.closeCallback) {
                    defaults.closeCallback();
                }
            };

            function init() {
                if (defaults.popBtn == "") return false;
                defaults.popBtn.live(defaults.eventType, function (e) {
                    if (defaults.login == "" || defaults.login == 'anonymousUser') {
                        if (defaults.doThing) {
                            defaults.doThing();
                        }
                    } else {
                        var $this = $(this);
                        if (defaults.popBox != "") {
                            popWin();
                            if (defaults.shadow) {
                                addShadow();
                            }
                        }
                        if (defaults.callback) {
                            defaults.callback($this);
                        }
                    }
                    e.preventDefault();
                });
                $(defaults.closeTag, defaults.popBox).bind('click', function () {
                    closeWin();
                });
            }

            init();
        };

        mb_user.dialogOption = {
            addShadow: function (obj) {
                var shadow = '<div id="user_shadow"></div>';
                if ($("#user_shadow")[0]) {
                    $("#user_shadow").show();
                } else {
                    obj.after(shadow);
                }
            },

            popWin: function (obj) {
                var h = obj.outerHeight(),
                        w = obj.outerWidth();
                obj.css({
                    "position": "fixed",
                    "left": "50%",
                    "top": "50%",
                    "margin-left": -w / 2,
                    "margin-top": -h / 2,
                    "z-index": "100000"
                });
                obj.show();
            },

            closeWin: function (obj) {
                obj.hide();
                $("#user_shadow").hide();
            }
        }
    </script>


    <script type="text/javascript">
        $(function () {
            $(":password.prohibited").bind("copy cut paste", function (e) {
                e.preventDefault();
                return false;
            });
            //获取request的值
            $("#shareCode").val($.request.queryString["shareCode"]);
            var coin = $.request.queryString["coin"];
            var userName = $.request.queryString["userName"];
            if (coin && userName) {
                $("#regMiStr").html("注册送" + coin + "米播," + userName + "推荐你注册米播");
            }
            $("#doRegist_1").bind('click', function () {
                var item = this;
                $(item).attr("disabled", true);
                $(item).addClass("invalidate");
                $(item).val("正在注册，请耐心等待");
                $('#regist_form').ajaxSubmit({
                    beforeSubmit: function () {
                        noRefresh = true;
                        if (!checkAllParams()) {
                            $(item).val("同意协议并注册");
                            $(item).removeClass("invalidate");
                            $(item).removeAttr("disabled");
                            noRefresh = false;
                            return false;
                        }
                        return true;
                    },
                    type: "post",
                    async: false,
                    dataType: "jsonp",
                    jsonp: "callbackparam",//传递给请求处理程序或页面的，用以获得jsonp回调函数名的参数名(默认为:callback)
                    jsonpCallback: "success_jsonpCallback",//自定义的jsonp回调函数名称，默认为jQuery自动生成的随机函数名
                    success: function (data, status) {
                        if (data.status == true || data.status == "true") {
                            if (data.detail.downloadUrl != '') {
                                window.location.href = 'http://bingfenggu.cn/';
                            } else {
                                window.location.href = 'http://www.baidu.com';
                            }
                        } else {
                            noRefresh = false;
                            HYIP.showDynamicErrorMsg4Wap(data.errorMessage);
                            //if(data.success == false || data.success == "false"){
                            $(item).val("同意协议并注册");
                            $(item).removeClass("invalidate");
                            $(item).removeAttr("disabled");
                            //}
                        }
                    },
                    error: function (e) {
                        $(item).val("同意协议并注册");
                        $(item).removeClass("invalidate");
                        $(item).removeAttr("disabled");
                        HYIP.showDynamicErrorMsg4Wap("服务器内部异常，请联系管理员");
                    },
                    dataType: 'jsonp'
                });
            });
        });

        function getVerifyCode() {
        }


        function isMobile(mobile) {
            //手机号码
            var uTel = /^((13[0-9])|145|147|17[0678]|(15[^4\D])|(18[0-9]))\d{8}$/;
            if (uTel.test(mobile)) {
                return true;
            }
            return false;
        }

        function isPwd(pwd) {
            var uPw = /^[\x00-\xff]{6,14}$/;  //密码
            if (uPw.test(pwd) && pwd.indexOf(' ') == -1) {
                return true;
            }
            return false;
        }
        var usernameType;
        function checkMobile(message) {
            usernameType = 'mobile';
            str = $("#username").val();
            var uMail = /^([a-zA-Z0-9]+[_|\-|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\-|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;   //邮箱规则
            var uNum = /^\d+$/;   //纯数字
            var uTel = /^((13[0-9])|145|147|17[0678]|(15[^4\D])|(18[0-9]))\d{8}$/    //手机号码
            var uName = /^([a-zA-Z0-9_]){6,20}$/  //用户名
            if (str.length == 11) {
                //是否电话号码
                if (!uTel.test(str)) {
                    $("#usernameErr").html("<img class=\"imgVC\" src=\"./src/wrong.png\" />您输入的手机号有误");
                    return false;
                } else {
                    isPicCode = 1;
                    $.ajax({
                        type: "get",
                        async: false,
                        url: "http://114.215.148.88:8080/mibo/proxy.jsp?url=http://localhost:8080/mibo/m/user/checkMobile&mobile=" + str,
                        dataType: "jsonp",
                        jsonp: "callbackparam",//传递给请求处理程序或页面的，用以获得jsonp回调函数名的参数名(默认为:callback)
                        jsonpCallback: "success_jsonpCallback",//自定义的jsonp回调函数名称，默认为jQuery自动生成的随机函数名
                        success: function (json) {
                            if (json && false == json.status) {
                                $("#usernameErr").html("<img class=\"imgVC\" src=\"./src/wrong.png\" />手机号已经注册");
                                return false;
                            }
                        },
                        error: function () {
                            return false;
                        }
                    });
                    $("#usernameErr").html("");
                    return true;
                }
            }
            else {
                $("#usernameErr").html("<img class=\"imgVC\" src=\"./src/wrong.png\" />请输入手机号码");
                return false;
            }

        }

        function checkPwd(message) {
            if (message) {
                $("#passwordErr").html("<img class=\"imgVC\" src=\"./src/wrong.png\" />" + message);
                return;
            }

            if (isPwd($("#password").val())) {
                $("#passwordErr").html("");
                return true;
            } else {
                $("#passwordErr").html("<img class=\"imgVC\" src=\"./src/wrong.png\" />密码为6-14位字符，支持数字，字母和标点");
                return false;
            }
        }

        function checkRePwd(message) {
            if (message) {
                $("#rePasswordErr").html("<img class=\"imgVC\" src=\"./src/wrong.png\" />" + message);
                return;
            }

            if ($("#rePassword").val() == "") {
                $("#rePasswordErr").html("<img class=\"imgVC\" src=\"./src/wrong.png\" />再输入一次密码");
                return true;
            } else if ($("#password").val() == $("#rePassword").val()) {
                $("#rePasswordErr").html("");
                return true;
            } else {
                $("#rePasswordErr").html("<img class=\"imgVC\" src=\"./src/wrong.png\" />两次输入的密码不一致");
                return false;
            }
        }

        function checkVCode() {
            var code = $("#securityCode").val();
            if (code.length != 4) {
                $("#vCodeErr").html("<img class=\"imgVC\" src=\"./src/wrong.png\" />请正确输入验证码");
                return false;
            } else {
                $("#vCodeErr").html("");
                return true;
            }
        }

        function checkAllParams() {
            var flag = true;
            if (!checkMobile()) {
                flag = false;
            }
            if (!checkPwd()) {
                flag = false;
            }
            if (!checkRePwd()) {
                flag = false;
            }
            if (!checkVCode()) {
                flag = false;
            }
            return flag;
        }

    </script>

</head>
<body>
<div class="main">
    <!--06-09改 增加 S-->
    <div id="weixinDown_div" class="weixinDown pct100" style="display: none">
    </div>
    <!--06-09改 E-->
    <div class="tc hd">
        <img class="logo" src="./src/welcome.png">
    </div>
    <div class="bd rel">
        <div class="land-info">
            <p id="MTconsult_" class="tl hl">
                <span id="regMiStr">注册赠送300米钻,<?php echo $nick_name ?>邀请您注册[米播]</span>
            </p>
        </div>

        <div class="formR">
            <!--06-09改 图片调换，字增加S-->
            <div class="title"><span class="bOrg"></span><span class="cOrg">会员注册</span><span
                    class="cSU f20">SIGN UP</span></div>
            <!--06-09改 E-->
            <form id="regist_form" method="post"
                  action="http://114.215.148.88:8080/mibo/proxy.jsp?url=http://localhost:8080/mibo/m/user/register">
                <ul class="formList">
                    <li class="list_item clearfix rel">
                        <label>会员账号</label>
                        <input style=" z-index:10; position:relative;" type="text" id="username" name="loginName"
                               class="prohibited" onblur="checkMobile();" tabindex="-1" placeholder="输入手机号">
                    </li>

                    <li id="usernameErr" class="wrongTips"></li>
                    <!--<li class="list_item clearfix rel">-->
                    <!--<label>昵&nbsp;&nbsp;&nbsp;称</label>-->
                    <!--<input style=" z-index:10; position:relative;" type="text" id="nicename" name="nicename" class="prohibited" onblur="checkMobile();" tabindex="-1" placeholder="输入昵称">-->
                    <!--</li>-->

                    <li id="nicenameErr" class="wrongTips"></li>
                    <li class="list_item clearfix rel">
                        <label>密&nbsp;&nbsp;&nbsp;码</label>
                        <input type="password" id="password" name="password" class="prohibited" onblur="checkPwd();"
                               tabindex="-1" placeholder="为6-14位字符，支持数字、字母和标点">
                    </li>
                    <li id="passwordErr" class="wrongTips"></li>
                    <li class="list_item clearfix rel last">
                        <label>确&nbsp;&nbsp;&nbsp;认</label>
                        <input type="password" id="rePassword" name="rePassword" class="prohibited"
                               onblur="checkRePwd();" tabindex="-1" placeholder="再输入一次密码">
                    </li>
                    <li id="rePasswordErr" class="wrongTips"></li>
                </ul>

                <!-- 验证码 S -->
                <div id="vcode_div" class="formList mt15 getVCode clearfix">
                    <div id="code_input_div" class="specialInput">
                        <input type="text" id="securityCode" name="validCode" tabindex="-1">
                    </div>
                    <div id="button_div">
                        <input type="button" id="btn" class="btnVCode hand invalidate" value="获取验证码" tabindex="-1" >
                    </div>
                </div>
                <!-- 验证码 E -->

                <div id="vCodeErr" class="wrongTips"></div>
                <input id="doRegist_1" type="button" class="bgBtnR pct100 mt15 hand" value="同意协议并注册" tabindex="4">
                <!--06-09改 字增加S-->
                <div class="mt25 tc"><a class="cBlue" id="jsOpenPrivacy"
                                        href="http://user.qbao.com/usercenter/privacy.html">《米播注册协议》</a></div>
                <!--06-09改 E-->
                <input type="hidden" name="otherType" value="5"/>
                <input type="hidden" name="shareCode" value=""/>
            </form>
        </div>
    </div>
    <!--06-09改 增加 S-->
    <div class="signFoot f20">
        <p>Copyright © 2014 粤ICP备14027703号</p>
    </div>
</div>
<div class="popWin rel" id="jsPopWin" style="display: none;">
</div>
<div class="popWin rel" id="jsPrivacy" style="display: none;">
    <img class="jsClose2 abs" src="./src/close2.png">
    <div style="height:100%; overflow:auto">
        <div class="tc hd">
            <img class="logo" src="./src/logo.png">
        </div>
        <div class="bd">
            <div class="tc title">米播注册协议</div>
            <p>米播在此特别提醒用户认真阅读、充分理解本《服务协议》（下称《协议》）---用户应认真阅读、充分理解本《协议》中各条款，包括免除或者限制米播责任的免责条款及对用户的权利限制条款。请您审慎阅读并选择接受或不接受本《协议》（未成年人应在法定监护人陪同下阅读）。除非您接受本《协议》所有条款，否则您无权注册、登录或使用本协议所涉相关服务。您的注册、登录、使用等行为将视为对本《协议》的接受，并同意接受本《协议》各项条款的约束。</p>
            <p>本《协议》是您（下称“用户”）与米播公司（下称“米播”）之间关于用户注册、登录、使用“米播”服务所订立的协议。本《协议》描述米播与用户之间关于“米播”服务相关方面的权利义务。“用户”是指注册、登录、使用、浏览本服务的个人或组织。</p>
            <p>本《协议》可由米播随时更新，更新后的协议条款一旦公布即代替原来的协议条款，恕不再另行通知，用户可在本网站查阅最新版协议条款。在米播修改《协议》条款后，如果用户不接受修改后的条款，请立即停止使用米播提供的服务，用户继续使用米播提供的服务将被视为已接受了修改后的协议。</p>
            <p>一、使用规则</p>
            <div>
                <p>1、用户充分了解并同意，米播仅为用户提供信息分享、传送及获取的平台，用户必须为自己注册帐号下的一切行为负责，包括您所传送的任何内容以及由此产生的任何结果。用户应对米播中的内容自行加以判断，并承担因使用内容而引起的所有风险，包括因对内容的正确性、完整性或实用性的依赖而产生的风险。米播公司无法且不会对因用户行为而导致的任何损失或损害承担责任。</p>
                <p>2、用户在米播服务中或通过米播服务所传送的任何内容并不反映米播公司的观点或政策，米播公司对此不承担任何责任。</p>
                <p>3、用户充分了解并同意，米播是一个基于用户关系网的点对点即时通讯产品，用户须对在米播上的注册信息的真实性、合法性、有效性承担全部责任，用户不得冒充他人；不得利用他人的名义传播任何信息；不得恶意使用注册帐号导致其他用户误认；否则米播公司有权立即停止提供服务，收米播帐号并由用户独自承担由此而产生的一切法律责任。</p>
                <p>4、用户须对在米播上所传送信息的真实性、合法性、无害性、有效性等全权负责，与用户所传播的信息相关的任何法律责任由用户自行承担，与米播无关。</p>
                <p>5、米播保留因业务发展需要，单方面对本服务的全部或部分服务内容在任何时候不经任何通知的情况下变更、暂停、限制、终止或撤销米播服务的权利，用户需承担此风险。</p>
                <p>6、米播提供的服务中可能包括广告，用户同意在使用过程中显示米播和第三方供应商、合作伙伴提供的广告。</p>
                <p>7、用户不得利用米播或米播服务制作、上载、复制、发送如下内容：</p>
                <p>(1)反对宪法所确定的基本原则的；</p>
                <p>(2)危害国家安全，泄露国家秘密，颠覆国家政权，破坏国家统一的；</p>
                <p>(3)损害国家荣誉和利益的；</p>
                <p>(4)煽动民族仇恨、民族歧视，破坏民族团结的；</p>
                <p>(5)破坏国家宗教政策，宣扬邪教和封建迷信的；</p>
                <p>(6)散布谣言，扰乱社会秩序，破坏社会稳定的；</p>
                <p>(7)散布淫秽、色情、赌博、暴力、凶杀、恐怖或者教唆犯罪的；</p>
                <p>(8)侮辱或者诽谤他人，侵害他人合法权益的；</p>
                <p>(9)含有法律、行政法规禁止的其他内容的信息。</p>
                <p>8、米播公司可依其合理判断，对违反有关法律法规或本协议约定；或侵犯、妨害、威胁任何人权利或安全的内容，或者假冒他人的行为，米播公司有权依法停止传输任何前述内容，并有权依其自行判断对违反本条款的任何人士采取适当的法律行动，包括但不限于，从米播服务中删除具有违法性、侵权性、不当性等内容，终止违反者的成员资格，阻止其使用米播全部或部分服务，并且依据法律法规保存有关信息并向有关部门报告等。</p>
                <p>9、用户权利及义务：</p>
                <p>(1)米播帐号的所有权归米播公司所有，用户完成申请注册手续后，获得米播帐号的使用权，该使用权仅属于初始申请注册人，禁止赠与、借用、租用、转让或售卖。米播公司因经营需要，有权回收用户的米播帐号。</p>
                <p>(2)用户有权更改、删除在米播上的个人资料、注册信息及传送内容等，但需注意，删除有关信息的同时也会删除任何您储存在系统中的文字和图片。用户需承担该风险。</p>
                <p>(3)用户有责任妥善保管注册帐号信息及帐号密码的安全，用户需要对注册帐号以及密码下的行为承担法律责任。用户同意在任何情况下不使用其他成员的帐号或密码。在您怀疑他人在使用您的帐号或密码时，您同意立即通知米播公司。</p>
                <p>(4)用户应遵守本协议的各项条款，正确、适当地使用本服务，如因用户违反本协议中的任何条款，米播公司有权依据协议终止对违约用户米播帐号提供服务。同时，米播保留在任何时候收回米播帐号、用户名的权利。</p>
                <p>(5)如果用户有自己的常用英文帐号，那么用户有优先将该英文帐号注册为帐号的权利，但是如果用户在米播服务上线后一段时间内没有注册帐号，或者用户虽然注册了帐号，但是并不是使用该英文帐号作为帐号的，视为用户放弃了将该英文帐号注册为帐号的权利，米播公司有权将该英文帐号回收并分配给其他用户使用，以免造成资源浪费，由此带来问题均由用户自行承担。</p>
                <p>(6)用户注册米播帐号后如果长期不登录该帐号，米播有权回收该帐号，以免造成资源浪费，由此带来问题均由用户自行承担。</p>

                <p>二、隐私保护</p>

                <p>用户同意个人隐私信息是指那些能够对用户进行个人辨识或涉及个人通信的信息，包括下列信息：用户真实姓名，身份证号，手机号码，IP地址。而非个人隐私信息是指用户对本服务的操作状态以及使用习惯等一些明确且客观反映在米播服务器端的基本记录信息和其他一切个人隐私信息范围外的普通信息；以及用户同意公开的上述隐私信息；</p>
                <p>尊重用户个人隐私信息的私有性是米播的一贯制度，米播将会采取合理的措施保护用户的个人隐私信息，除法律或有法律赋予权限的政府部门要求或用户同意等原因外，米播未经用户同意不向除合作单位以外的第三方公开、透露用户个人隐私信息。但是，用户在注册时选择同意，或用户与米播及合作单位之间就用户个人隐私信息公开或使用另有约定的除外，同时用户应自行承担因此可能产生的任何风险，米播对此不予负责。同时，为了运营和改善米播的技术和服务，米播将可能会自行收集使用或向第三方提供用户的非个人隐私信息，这将有助于米播向用户提供更好的用户体验和提高米播的服务质量。详情请参见《米播用户隐私政策》。</p>

                <p>三、米播商标信息</p>

                <p>米播服务中所涉及的米播图形、文字或其组成，以及其他米播标志及产品、服务名称，均为米播公司之商标（以下简称“米播标识”）。未经米播事先书面同意，用户不得将米播标识以任何方式展示或使用或作其他处理，也不得向他人表明您有权展示、使用、或其他有权处理米播标识的行为。</p>
                <p>四、法律责任及免责</p>

                <p>1、用户违反本《协议》或相关的服务条款的规定，导致或产生的任何第三方主张的任何索赔、要求或损失，包括合理的律师费，用户同意赔偿米播与合作公司、关联公司，并使之免受损害。</p>
                <p>2、用户因第三方如电信部门的通讯线路故障、技术问题、网络、电脑故障、系统不稳定性及其他各种不可抗力原因而遭受的一切损失，米播及合作单位不承担责任。</p>
                <p>3、因技术故障等不可抗事件影响到服务的正常运行的，米播及合作单位承诺在第一时间内与相关单位配合，及时处理进行修复，但用户因此而遭受的一切损失，米播及合作单位不承担责任。</p>
                <p>4、本服务同大多数互联网服务一样，受包括但不限于用户原因、网络服务质量、社会环境等因素的差异影响，可能受到各种安全问题的侵扰，如他人利用用户的资料，造成现实生活中的骚扰；用户下载安装的其它软件或访问的其他网站中含有“特洛伊木马”等病毒，威胁到用户的计算机信息和数据的安全，继而影响本服务的正常使用等等。用户应加强信息安全及使用者资料的保护意识，要注意加强密码保护，以免遭致损失和骚扰。</p>
                <p>5、用户须明白，使用本服务因涉及Internet服务，可能会受到各个环节不稳定因素的影响。因此，本服务存在因不可抗力、计算机病毒或黑客攻击、系统不稳定、用户所在位置、用户关机以及其他任何技术、互联网络、通信线路原因等造成的服务中断或不能满足用户要求的风险。用户须承担以上风险，米播公司不作担保。对因此导致用户不能发送和接受阅读信息、或接发错信息，米播公司不承担任何责任。</p>
                <p>6、用户须明白，在使用本服务过程中存在有来自任何他人的包括威胁性的、诽谤性的、令人反感的或非法的内容或行为或对他人权利的侵犯（包括知识产权）的匿名或冒名的信息的风险，用户须承担以上风险，米播公司和合作公司对本服务不作任何类型的担保，不论是明确的或隐含的，包括所有有关信息真实性、适商性、适于某一特定用途、所有权和非侵权性的默示担保和条件，对因此导致任何因用户不正当或非法使用服务产生的直接、间接、偶然、特殊及后续的损害，不承担任何责任。</p>
                <p>7、米播公司定义的信息内容包括：文字、软件、声音、相片、录像、图表；在广告中全部内容；米播公司为用户提供的商业信息，所有这些内容受版权、商标权、和其它知识产权和所有权法律的保护。所以，用户只能在米播公司和广告商授权下才能使用这些内容，而不能擅自复制、修改、编纂这些内容、或创造与内容有关的衍生产品。</p>
                <p>8、在任何情况下，米播公司均不对任何间接性、后果性、惩罚性、偶然性、特殊性或刑罚性的损害，包括因用户使用米播服务而遭受的利润损失，承担责任（即使米播已被告知该等损失的可能性亦然）。尽管本协议中可能含有相悖的规定，米播公司对您承担的全部责任，无论因何原因或何种行为方式，始终不超过您在成员期内因使用米播服务而支付给米播公司的费用(如有)。</p>
                <p>五、其他条款</p>

                <p>1、米播公司郑重提醒用户注意本《协议》中免除米播公司责任和加重用户义务的条款，请用户仔细阅读，自主考虑风险。未成年人应在法定监护人的陪同下阅读本《协议》。以上各项条款内容的最终解释权及修改权归米播公司所有。</p>
                <p>2、本《协议》所定的任何条款的部分或全部无效者，不影响其它条款的效力。</p>
                <p>3、本《协议》签订地为深圳。本《协议》的解释、效力及纠纷的解决，适用于中华人民共和国法律。若用户和米播之间发生任何纠纷或争议，首先应友好协商解决，协商不成的，用户在此完全同意将纠纷或争议提交米播住所地地即深圳市有管辖权的人民法院管辖。</p>
                <p>4、本《协议》的版权米播所有，米播保留一切解释和修改权利。</p>
            </div>
        </div>
    </div>
</div>
<!--错误提示信息-->
<!--     <div class="popTips" >该手机号码格式不,该手机号码格式不正确正确机号码</div> -->
<div id="user_shadow"></div>

<script type="text/javascript">
    var wait = 60;
    function time(o) {
        if (wait == 0) {
            o.removeAttribute("disabled");
            o.value = "获取验证码";
            wait = 60;
        } else {
            o.setAttribute("disabled", true);
            o.value = "重新发送(" + wait + ")";
            wait--;
            setTimeout(function () {
                        time(o)
                    },
                    1000)
        }
    }
    document.getElementById("btn").onclick = function () {
        var mobile = $("#username").val();
        if (mobile == "") {
            $("#usernameErr").html("<img class=\"imgVC\" src=\"./src/wrong.png\" />请输入手机号码");
            return false;
        }
        if (!checkMobile(mobile)) {
            return false;
        }
        time(this);
       
        // $.ajax({
        //     type: "get",
        //     async: false,
        //     url: "http://mibo.yahalei.com/mibo/index.php?service=App.getVerifycode&channel=bt&code_type=1&phone=" + mobile,
        //     dataType: "jsonp",
        //     jsonp: "callbackparam",//传递给请求处理程序或页面的，用以获得jsonp回调函数名的参数名(默认为:callback)
        //     jsonpCallback: "success_jsonpCallback",//自定义的jsonp回调函数名称，默认为jQuery自动生成的随机函数名
        //     success: function (json) {
        //         if (json && false == json.status) {
        //             $("#usernameErr").html("<img class=\"imgVC\" src=\"./src/wrong.png\" />" + json.errorMessage);
        //             return false;
        //         }
        //     },
        //     error: function () {
        //         alert('请求异常');
        //         return false;
        //     }
        // });
        $.ajax(  
            {  
                type:'get',  
                url : "http://mibo.yahalei.com/mibo/index.php?service=App.getVerifycode&channel=bt&phone=18316227457&code_type=1&mibo",  
                dataType : 'jsonp',  
                jsonp:"jsoncallback",  
                success  : function(data) {  
                    alert("用户名："+ data.user +" 密码："+ data.pass);  
                },  
                error : function() {  
                    alert('fail');  
                }  
            }  
        );  
    }
</script>
<script>
    (function ($) {
        $(".jsClose").click(function () {
            $(".join").hide();
            return false;
        })

        $(".closeWeixinDown").click(function () {
            //$(".weixinShd").hide();
            //$(".weixinDown").hide();
            mb_user.dialogOption.closeWin($(".weixinDown"));
            return false;
        })

        $(".tips").click(function () {
            $(this).hide();
            $(this).siblings("input").focus();
        })
        $("input").click(function () {
            $(this).focus();
            $(this).siblings(".tips").hide();
        })
        $(".popWin").hide();
        /* mb_user.dialog({
         popBtn:$("#jsOpenPop"),
         popBox:$("#jsPopWin")
         });

         $("#jsOpenPop").bind('click',function(){
         $(".join").hide();
         $("#user_shadow").height( $("body,html").height() );
         mb_user.dialogOption.addShadow($("body"));
         mb_user.dialogOption.popWin($("#jsPopWin"));

         });
         */
        $("#jsPopWin .jsClose2").bind('click', function () {
            $(".join").show();
            mb_user.dialogOption.closeWin($("#jsPopWin"));
        });

        mb_user.dialog({
            popBtn: $("#jsOpenPrivacy"),
            popBox: $("#jsPrivacy")
        });
    })(jQuery);

    //默认显示注册介绍
    //if(true){
    //$("#jsOpenPop").click();
    //}

    //if(false){

    //hideFc();
    //}

    /*  转屏 */
    var evt = "onorientationchange" in window ? "orientationchange" : "resize";

    window.addEventListener(evt, function () {
        objW = document.getElementById("jsPopWin");
        objP = document.getElementById("jsPrivacy");
        var hW = objW.offsetHeight;
        wW = objW.offsetWidth;
        hP = objP.offsetHeight;
        wP = objP.offsetWidth;
        objW.style.marginLeft = -wW / 2 + "px";
        objW.style.marginTop = -hW / 2 + "px";
        objP.style.marginLeft = -wP / 2 + "px";
        objP.style.marginTop = -hP / 2 + "px";
    }, false);

    $.request = (function () {
        var apiMap = {};

        function request(queryStr) {
            var api = {};
            if (apiMap[queryStr]) {
                return apiMap[queryStr];
            }
            api.queryString = (function () {
                var urlParams = {};
                var e,
                        d = function (s) {
                            return decodeURIComponent(s.replace(/\+/g, " "));
                        },
                        q = queryStr.substring(queryStr.indexOf('?') + 1),
                        r = /([^&=]+)=?([^&]*)/g;
                while (e = r.exec(q))    urlParams[d(e[1])] = d(e[2]);
                return urlParams;
            })();
            api.getUrl = function () {
                var url = queryStr.substring(0, queryStr.indexOf('?') + 1);
                for (var p in api.queryString) {
                    url += p + '=' + api.queryString[p] + "&";
                }
                if (url.lastIndexOf('&') == url.length - 1) {
                    return url.substring(0, url.lastIndexOf('&'));
                }
                return url;
            }
            apiMap[queryStr] = api;
            return api;
        }

        $.extend(request, request(window.location.href));
        return request;
    })();
</script>

</body>
</html>