function getArticle(page) {
    $.ajax(
        {
            url: getArticleUrl,
            data: {page: page},
            contentType: 'application/x-www-form-urlencoded',
            type: "POST",
            dataType: "JSON",
            success:
                function (result) {

                    if (result.state) {//获取成功
                       if (result.page !== null) page_set(result.page, 'getArticle');
                        AppendTO(result.data);

                    } else {
                        $("#articleList").html("<p style='text-align: center;\n" +
                            "margin-top: 40px;\n" +
                            "font-size: 40px;'>暂无文章</p>");
                    }
                }
        });
}

function AppendTO(result) {

    const m = '<article class="post">\n' +
        '        <header class="post-header">\n' +
        '            <h1 class="post-title">\n' +
        '                <a class="post-link" href="/posts/{{alians}}/">{{title}}</a>\n' +
        '            </h1>\n' +
        '            <time class="post-time">\n' +
        '                {{date}}\n' +
        '            </time>\n' +
        '        </header>\n' +
        '        <div class="post-excerpt post-content">\n' +
        '            {{info}}\n' +
        '        </div>\n' +
        '    </article>';
    var v = "";
    $("#articleList").empty();
    for (let i = 0; i < result.length; i++) {
        v = m;
        /*v = v.replace(/{{views}}/g, result[i].views, v);
        v = v.replace(/{{comments}}/g, result[i].comments, v);*/
        v = v.replace(/{{alians}}/g, result[i].alians, v);
        result[i].title='['+result[i].sname+'] '+result[i].title;
        if(parseInt(result[i].top)===1)result[i].title='[置顶] '+result[i].title;
        v = v.replace(/{{title}}/g, result[i].title, v);
        v = v.replace(/{{info}}/g, result[i].info, v);
        v = v.replace(/{{date}}/g, result[i].date, v);

        $("#articleList").append(v);
    }
}

getArticle(1);