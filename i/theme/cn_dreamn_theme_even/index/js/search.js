function getSearch(page) {
    $.ajax(
        {
            url: getSearchUrl,
            data: {page: page,s:searchName},
            contentType: 'application/x-www-form-urlencoded',
            type: "POST",
            dataType: "JSON",
            success:
                function (result) {

                    if (result.state) {//获取成功
                       if (result.page !== null) page_set(result.page, 'getSearch');
                        AppendTO(result.data);

                    } else {
                        $("#articleList").html("<p  class=\"post\" style='text-align: center;\n" +
                            "font-size: 18px;'>很抱歉，没有找到任何内容。</p>");
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
        v = v.replace(/{{alians}}/g, result[i].alians, v);
        v = v.replace(/{{title}}/g, result[i].title, v);
        v = v.replace(/{{info}}/g, result[i].info, v);
        v = v.replace(/{{date}}/g, result[i].date, v);

        $("#articleList").append(v);
    }
}

getSearch(1);