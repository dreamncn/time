/**
 *
 * 文章自动生成目录
 * **/
$(document).ready(function () {
    var body = $('body'),
        parent = 'post-content',
        h1, h2;
    s = $('#' + parent);

    if (s.length === 0) {return}
    h = s.find(':header');
    if (h.length > 0) {
        body.append('<div id="sideToolbar" style="position: fixed;top: 4em;">\<div id="sideCatalog-catalog">\<ul class="nav"style="width:230px;zoom:1">\</ul>\</div>\</div>\<a href="javascript:void(0);"id="sideCatalogBtn"class="sideCatalogBtnDisable"></a>\</div>');
        var html='';
        for (var i = 0; i < h.length; i++) {
            var th = $(h[i]);

            var th_name =   th[0].tagName.toLowerCase();
            if ($.inArray(th_name, ["h1", "h2"]) === -1) continue;

            var thText = th.text();
            th.text('');
            var id=  th_name+'-'+i.toString();
            th.append('<span class="dev_title" id="' + id + '">' + thText + '</span>');

            if (th_name === 'h1') {
                if (thText.length > 26) thText = thText.substr(0, 26) + "...";

                html += '<li><a href="#' +id + '">' +  '&nbsp;&nbsp;' + thText + '</a><span class="sideCatalog-dot"></span></li>';
            } else {
                if (thText.length > 30) thText = thText.substr(0, 30) + "...";

                html += '<li style="text-indent: 1em;"><a href="#' + id + '">'  + '&nbsp;&nbsp;' + thText + '</a></li>';
            }
        }
        $('#sideCatalog-catalog>ul').html(html);
    }
});