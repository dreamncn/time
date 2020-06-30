function shareToTsina() {
    var n = screen, t = document, i = encodeURIComponent, r = "http://v.t.sina.com.cn/share/share.php?",
        u = t.location.href, f = ["url=", i(u), "&title=", i(t.title)].join("");
    window.open([r, f].join(""), "mb", ["toolbar=0,status=0,resizable=1,width=620,height=450,left=", (n.width - 620) / 2, ",top=", (n.height - 450) / 2].join("")) || (u.href = [r, f].join(""))
}
function shareTOQQ() {
    var n = screen, t = document, i = encodeURIComponent,
        r = "http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?",
        u = t.location.href, f = ["url=", i(u), "&title=", i(t.title), "&desc=", '这篇文章不错,分享一下~~'].join("");
    window.open([r, f].join(""), "mb", ["toolbar=0,status=0,resizable=1,width=620,height=450,left=", (n.width - 620) / 2, ",top=", (n.height - 450) / 2].join("")) || (u.href = [r, f].join(""))
}
function shareTODB() {
    var n = screen, t = document, i = encodeURIComponent, r = " http://www.douban.com/recommend/?",
        u = t.location.href, f = ["url=", i(u), "&title=", i(t.title)].join("");
    window.open([r, f].join(""), "mb", ["toolbar=0,status=0,resizable=1,width=620,height=450,left=", (n.width - 620) / 2, ",top=", (n.height - 450) / 2].join("")) || (u.href = [r, f].join(""))
}
function shareQR() {
    var n = screen, t = document, i = encodeURIComponent,
        r = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=%E5%86%85%E5%AE%B9",
        u = t.location.href, f = ["size=", '250x250', "&data=", i(u)].join("");
    window.open([r, f].join(""), "mb", ["toolbar=0,status=0,resizable=1,width=620,height=450,left=", (n.width - 620) / 2, ",top=", (n.height - 450) / 2].join("")) || (u.href = [r, f].join(""))
}

var getPic=function (dom) {
    var cpb = $(dom)
        , imgList = $(dom+' img');

    if (cpb.length > 0 && imgList.length > 0) {
        $.each(imgList, function (i) {
            var tem = $(imgList[i]);
            var flg = tem.attr('id');
            //console.log(tem,tem.outerWidth());
            if (typeof flg == 'undefined') {
                tem.wrap("<a class='lightbox' href='" + tem.attr('src') + "'></a>");
            }
        });
        baguetteBox.run('.lightbox');
    }
};

/**
 * 将所有外链替换成本地跳转
 * @param dom
 */
var jumpUrl=function(dom){
    var cpb = $(dom)
        , aList = $(dom+' a');
    if (cpb.length > 0 && aList.length > 0) {
        $.each(aList, function (i) {
            var tem = $(aList[i]);

            var flg = tem.attr('href');
            if(flg===undefined)return;
            var flgclass = tem.attr('class');
            if(flgclass!==undefined)return;
            index=flg.indexOf(window.location.host);

            if (index === -1) {
                var href=window.location.protocol+"//"+window.location.host+'/jump/'+window.btoa(flg);
                console.log(window.location.protocol+"//"+window.location.host+'/jump/'+window.btoa(flg));
                tem.attr('href',href);
                tem.attr("target",'_blank');
            }
        });
    }
};

/**
 * 加载评论
 * @param id
 */
function getComment(id) {
    $.ajax(
        {
            url: comment,
            data: "id=" + id,
            type: "POST",
            contentType: 'application/x-www-form-urlencoded',
            dataType: "html",
            success:
                function (result) {
                    $("#comments").html(result);
                },
            error:
                function (xhr, status, error) {
                    console.log(xhr, status, error);
                }
        });
}
var postDirectoryBuild = function(dom) {
    var postChildren = function children(childNodes, reg) {
            var result = [],
                isReg = typeof reg === 'object',
                isStr = typeof reg === 'string',
                node, i, len;
            for (i = 0, len = childNodes.length; i < len; i++) {
                node = childNodes[i];
                if ((node.nodeType === 1 || node.nodeType === 9) &&
                    (!reg ||
                        isReg && reg.test(node.tagName.toLowerCase()) ||
                        isStr && node.tagName.toLowerCase() === reg)) {
                    result.push(node);
                }
            }
            return result;
        },
        createPostDirectory = function(article, directory, isDirNum) {
            var contentArr = [],
                titleId = [],
                levelArr, root, level,
                currentList, list, li, link, i, len;
            console.log(article.childNodes);
            levelArr = (function(article, contentArr, titleId) {
                var titleElem = postChildren(article.childNodes, /^h\d$/),
                    levelArr = [],
                    lastNum = 1,
                    lastRevNum = 1,
                    count = 0,
                    guid = 1,
                    id = 'directory' + (Math.random() + '').replace(/\D/, ''),
                    num, elem;

                while (titleElem.length) {
                    elem = titleElem.shift();
                    contentArr.push(elem.innerHTML);
                    num = +elem.tagName.match(/\d/)[0];
                    if (num > lastNum) {
                        levelArr.push(1);
                        lastRevNum += 1;
                    } else if (num === lastRevNum ||
                        num > lastRevNum && num <= lastNum) {
                        levelArr.push(0);
                        //lastRevNum = lastRevNum;
                    } else if (num < lastRevNum) {
                        levelArr.push(num - lastRevNum);
                        lastRevNum = num;
                    }
                    count += levelArr[levelArr.length - 1];
                    lastNum = num;
                    elem.id = elem.id || (id + guid++);
                    titleId.push(elem.id);
                }
                if (count !== 0 && levelArr[0] === 1) levelArr[0] = 0;

                return levelArr;
            })(article, contentArr, titleId);
            currentList = root = document.createElement('ul');
            dirNum = [0];

            for (i = 0, len = levelArr.length; i < len; i++) {
                level = levelArr[i];
                if (level === 1) {
                    list = document.createElement('ul');
                    if (!currentList.lastElementChild) {
                        currentList.appendChild(document.createElement('li'));
                    }
                    currentList.lastElementChild.appendChild(list);
                    currentList = list;
                    dirNum.push(0);
                } else if (level < 0) {
                    level *= 2;
                    while (level++) {
                        if (level % 2) dirNum.pop();
                        currentList = currentList.parentNode;
                    }
                }
                dirNum[dirNum.length - 1]++;
                li = document.createElement('li');
                link = document.createElement('a');
                link.href = '#' + titleId[i];
                link.innerHTML = !isDirNum ? contentArr[i] :
                    contentArr[i] ;
                li.appendChild(link);
                currentList.appendChild(li);
            }
            directory.appendChild(root);
        };
    createPostDirectory(document.getElementById(dom),document.getElementById('directory'), true);
};

var postDirectory = new Headroom(document.getElementById("directory-content"), {
    tolerance: 0,
    offset : 90,    classes: {
        initial: "initial",
        pinned: "pinned",
        unpinned: "unpinned"
    }
});
postDirectory.init();
var postSharer = new Headroom(document.getElementById("post-bottom-bar"), {
    tolerance: 0,
    offset : 70,
    classes: {
        initial: "animated",
        pinned: "pinned",
        unpinned: "unpinned"
    }
});

postSharer.init();
var header = new Headroom(document.getElementById("header"), {
    tolerance: 0,
    offset : 70,
    classes: {
        initial: "animated",
        pinned: "slideDown",
        unpinned: "slideUp"
    }
});
header.init();
