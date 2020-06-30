function getClientInfo() {
    var e = navigator.userAgent;
    var t = {},
        n = {
            Trident: e.indexOf("Trident") > -1 || e.indexOf("NET CLR") > -1,
            Presto: e.indexOf("Presto") > -1,
            WebKit: e.indexOf("AppleWebKit") > -1,
            Gecko: e.indexOf("Gecko/") > -1,
            Safari: e.indexOf("Safari") > -1,
            Chrome: e.indexOf("Chrome") > -1 || e.indexOf("CriOS") > -1,
            IE: e.indexOf("MSIE") > -1 || e.indexOf("Trident") > -1,
            Edge: e.indexOf("Edge") > -1,
            Firefox: e.indexOf("Firefox") > -1 || e.indexOf("FxiOS") > -1,
            "Firefox Focus": e.indexOf("Focus") > -1,
            Chromium: e.indexOf("Chromium") > -1,
            Opera: e.indexOf("Opera") > -1 || e.indexOf("OPR") > -1,
            Vivaldi: e.indexOf("Vivaldi") > -1,
            Yandex: e.indexOf("YaBrowser") > -1,
            Kindle: e.indexOf("Kindle") > -1 || e.indexOf("Silk/") > -1,
            360 : e.indexOf("360EE") > -1 || e.indexOf("360SE") > -1,
            UC: e.indexOf("UC") > -1 || e.indexOf(" UBrowser") > -1,
            QQBrowser: e.indexOf("QQBrowser") > -1,
            QQ: e.indexOf("QQ/") > -1,
            Baidu: e.indexOf("Baidu") > -1 || e.indexOf("BIDUBrowser") > -1,
            Maxthon: e.indexOf("Maxthon") > -1,
            Sogou: e.indexOf("MetaSr") > -1 || e.indexOf("Sogou") > -1,
            LBBROWSER: e.indexOf("LBBROWSER") > -1,
            "2345Explorer": e.indexOf("2345Explorer") > -1,
            TheWorld: e.indexOf("TheWorld") > -1,
            XiaoMi: e.indexOf("MiuiBrowser") > -1,
            Quark: e.indexOf("Quark") > -1,
            Qiyu: e.indexOf("Qiyu") > -1,
            Wechat: e.indexOf("MicroMessenger") > -1,
            Taobao: e.indexOf("AliApp(TB") > -1,
            Alipay: e.indexOf("AliApp(AP") > -1,
            Weibo: e.indexOf("Weibo") > -1,
            Douban: e.indexOf("com.douban.frodo") > -1,
            Suning: e.indexOf("SNEBUY-APP") > -1,
            iQiYi: e.indexOf("IqiyiApp") > -1,
            Windows: e.indexOf("Windows") > -1,
            Linux: e.indexOf("Linux") > -1 || e.indexOf("X11") > -1,
            "Mac OS": e.indexOf("Macintosh") > -1,
            Android: e.indexOf("Android") > -1 || e.indexOf("Adr") > -1,
            Ubuntu: e.indexOf("Ubuntu") > -1,
            FreeBSD: e.indexOf("FreeBSD") > -1,
            Debian: e.indexOf("Debian") > -1,
            "Windows Phone": e.indexOf("IEMobile") > -1 || e.indexOf("Windows Phone") > -1,
            BlackBerry: e.indexOf("BlackBerry") > -1 || e.indexOf("RIM") > -1,
            MeeGo: e.indexOf("MeeGo") > -1,
            Symbian: e.indexOf("Symbian") > -1,
            iOS: e.indexOf("like Mac OS X") > -1,
            "Chrome OS": e.indexOf("CrOS") > -1,
            WebOS: e.indexOf("hpwOS") > -1,
            Mobile: e.indexOf("Mobi") > -1 || e.indexOf("iPh") > -1 || e.indexOf("480") > -1,
            Tablet: e.indexOf("Tablet") > -1 || e.indexOf("Pad") > -1 || e.indexOf("Nexus 7") > -1
        };
    n.Mobile && (n.Mobile = !(e.indexOf("iPad") > -1));
    var r = {
        engine: ["WebKit", "Trident", "Gecko", "Presto"],
        browser: ["Safari", "Chrome", "Edge", "IE", "Firefox", "Firefox Focus", "Chromium", "Opera", "Vivaldi", "Yandex", "Kindle", "360", "UC", "QQBrowser", "QQ", "Baidu", "Maxthon", "Sogou", "LBBROWSER", "2345Explorer", "TheWorld", "XiaoMi", "Quark", "Qiyu", "Wechat", "Taobao", "Alipay", "Weibo", "Douban", "Suning", "iQiYi"],
        os: ["Windows", "Linux", "Mac OS", "Android", "Ubuntu", "FreeBSD", "Debian", "iOS", "Windows Phone", "BlackBerry", "MeeGo", "Symbian", "Chrome OS", "WebOS"],
        device: ["Mobile", "Tablet"]
    };
    t.device = "PC";
    for (var i in r) for (var o = 0; o < r[i].length; o++) {
        var a = r[i][o];
        n[a] && (t[i] = a)
    }
    var s = {
        Windows: function() {
            var t = e.replace(/^.*Windows NT ([\d.]+);.*$/, "$1");
            return {
                6.4 : "10",
                6.3 : "8.1",
                6.2 : "8",
                6.1 : "7",
                "6.0": "Vista",
                5.2 : "XP",
                5.1 : "XP",
                "5.0": "2000"
            } [t] || t
        },
        Android: function() {
            return e.replace(/^.*Android ([\d.]+);.*$/, "$1")
        },
        iOS: function() {
            return e.replace(/^.*OS ([\d_]+) like.*$/, "$1").replace(/_/g, ".")
        },
        Debian: function() {
            return e.replace(/^.*Debian\/([\d.]+).*$/, "$1")
        },
        "Windows Phone": function() {
            return e.replace(/^.*Windows Phone( OS)? ([\d.]+);.*$/, "$2")
        },
        /**
         * @return {string}
         */
        "Mac OS": function() {
            return e.replace(/^.*Mac OS X ([\d_]+).*$/, "$1").replace(/_/g, ".")
        },
        WebOS: function() {
            return e.replace(/^.*hpwOS\/([\d.]+);.*$/, "$1")
        }
    };
    t.osVersion = "",
    s[t.os] && (t.osVersion = s[t.os](), t.osVersion === e && (t.osVersion = ""));
    var l = {
        Safari: function() {
            return e.replace(/^.*Version\/([\d.]+).*$/, "$1")
        },
        /**
         * @return {string}
         */
        Chrome: function() {
            return e.replace(/^.*Chrome\/([\d.]+).*$/, "$1").replace(/^.*CriOS\/([\d.]+).*$/, "$1")
        },
        /**
         * @return {string}
         */
        IE: function() {
            return e.replace(/^.*MSIE ([\d.]+).*$/, "$1").replace(/^.*rv:([\d.]+).*$/, "$1")
        },
        Edge: function() {
            return e.replace(/^.*Edge\/([\d.]+).*$/, "$1")
        },
        /**
         * @return {string}
         */
        Firefox: function() {
            return e.replace(/^.*Firefox\/([\d.]+).*$/, "$1").replace(/^.*FxiOS\/([\d.]+).*$/, "$1")
        },
        "Firefox Focus": function() {
            return e.replace(/^.*Focus\/([\d.]+).*$/, "$1")
        },
        Chromium: function() {
            return e.replace(/^.*Chromium\/([\d.]+).*$/, "$1")
        },
        /**
         * @return {string}
         */
        Opera: function() {
            return e.replace(/^.*Opera\/([\d.]+).*$/, "$1").replace(/^.*OPR\/([\d.]+).*$/, "$1")
        },
        Vivaldi: function() {
            return e.replace(/^.*Vivaldi\/([\d.]+).*$/, "$1")
        },
        Yandex: function() {
            return e.replace(/^.*YaBrowser\/([\d.]+).*$/, "$1")
        },
        Kindle: function() {
            return e.replace(/^.*Version\/([\d.]+).*$/, "$1")
        },
        Maxthon: function() {
            return e.replace(/^.*Maxthon\/([\d.]+).*$/, "$1")
        },
        QQBrowser: function() {
            return e.replace(/^.*QQBrowser\/([\d.]+).*$/, "$1")
        },
        QQ: function() {
            return e.replace(/^.*QQ\/([\d.]+).*$/, "$1")
        },
        Baidu: function() {
            return e.replace(/^.*BIDUBrowser[\s\/]([\d.]+).*$/, "$1")
        },
        UC: function() {
            return e.replace(/^.*UC?Browser\/([\d.]+).*$/, "$1")
        },
        /**
         * @return {string}
         */
        Sogou: function() {
            return e.replace(/^.*SE ([\d.X]+).*$/, "$1").replace(/^.*SogouMobileBrowser\/([\d.]+).*$/, "$1")
        },
        "2345Explorer": function() {
            return e.replace(/^.*2345Explorer\/([\d.]+).*$/, "$1")
        },
        TheWorld: function() {
            return e.replace(/^.*TheWorld ([\d.]+).*$/, "$1")
        },
        XiaoMi: function() {
            return e.replace(/^.*MiuiBrowser\/([\d.]+).*$/, "$1")
        },
        Quark: function() {
            return e.replace(/^.*Quark\/([\d.]+).*$/, "$1")
        },
        Qiyu: function() {
            return e.replace(/^.*Qiyu\/([\d.]+).*$/, "$1")
        },
        Wechat: function() {
            return e.replace(/^.*MicroMessenger\/([\d.]+).*$/, "$1")
        },
        Taobao: function() {
            return e.replace(/^.*AliApp\(TB\/([\d.]+).*$/, "$1")
        },
        Alipay: function() {
            return e.replace(/^.*AliApp\(AP\/([\d.]+).*$/, "$1")
        },
        Weibo: function() {
            return e.replace(/^.*weibo__([\d.]+).*$/, "$1")
        },
        Douban: function() {
            return e.replace(/^.*com.douban.frodo\/([\d.]+).*$/, "$1")
        },
        Suning: function() {
            return e.replace(/^.*SNEBUY-APP([\d.]+).*$/, "$1")
        },
        iQiYi: function() {
            return e.replace(/^.*IqiyiVersion\/([\d.]+).*$/, "$1")
        }
    };
    t.version = "",
    l[t.browser] && (t.version = l[t.browser](), t.version === e && (t.version = "")),
        "Edge" === t.browser ? t.engine = "EdgeHTML": "Chrome" === t.browser && parseInt(t.version) > 27 ? t.engine = "Blink": "Opera" == t.browser && parseInt(t.version) > 12 ? t.engine = "Blink": "Yandex" == t.browser ? t.engine = "Blink": void 0 == t.browser && (t.browser = "Unknow App")
    return t;
}
function sendComment(data) {
     $.ajax({
         url:commentUrl,
         async:false,
         data:data,
         dataType:'json',
         type: 'post',
         success:function (res) {
             if(!res.state){
                 layer.alert(res.msg, {icon: 5});
             } else {
                 layer.msg('回复成功！');
                 getComment(gid);
             }
         }
     })
}
$('.vat').click(function () {
       var cid=$(this).attr('data-cid');
       var name=$(this).attr('data-name');
       $('input[name=cid]').attr('value',cid);
       $('#comment_cid').html('Reply '+name+' ');
       $('#comment_cancle').html('/ Cancel Reply');
       $('#comment_reply').css('display','block');
       window.location.href='#comments';
});
$("#comment_cancle").click(function () {
    $('input[name=cid]').attr('value','0');
    $('#comment_cid').html('');
    $('#comment_cid2').html('');
    $('#comment_cancle').html('');
    $('#comment_reply').css('display','none');
});
$("#submit").click(function () {
        var data= $('#comment').serializeArray();
        var cilent= getClientInfo();
        data.push({name:'browser',value: cilent['browser']+' '+cilent['version']});
        data.push({name:'os',value: cilent['os']+' '+cilent['osVersion']});
        sendComment(data);
});


$("input[name=qq]").blur(function(){
    var qq_num= $(this).val();
    $("input[name=mail]").val(qq_num+'@qq.com');
    $("input[name=url]").val('https://user.qzone.qq.com/'+qq_num);
    $.ajax({
        url:qq,
        async:false,
        data:{qq:qq_num},
        dataType:'json',
        type: 'post',
        success:function (res) {
            $("input[name=name]").val(res.name);
            $('#comment_cid2').html(' Visiter - '+res.name+' ');
            $('#comment_reply').css('display','block');
        }
    })
});
