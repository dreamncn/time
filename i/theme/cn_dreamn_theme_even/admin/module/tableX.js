/**
 * 表格扩展模块
 * date:2020-02-29   License By http://easyweb.vip
 */
layui.define(["layer", "table", "laytpl", "contextMenu"], function(f) {
    var g = layui.jquery;
    var i = layui.layer;
    var q = layui.table;
    var n = layui.laytpl;
    var e = layui.contextMenu;
    var c = layui.device();
    var h = "tb-search";
    var p = "tb-refresh";
    var b = "tb-export";
    var m = "txField_";
    var a = {};
    a.merges = function(w, y, s, v) {
        if (typeof s === "boolean") {
            v = s;
            s = undefined
        }
        var r = g('[lay-filter="' + w + '"]+.layui-table-view>.layui-table-box>.layui-table-body>table');
        var t = r.find(">tbody>tr");
        for (var u = 0; u < y.length; u++) {
            if (s) {
                x(w, y[u], s[u])
            } else {
                x(w, y[u])
            }
        }
        t.find('[del="true"]').remove();
        if (v === undefined || v) {
            q.on("sort(" + w + ")", function() {
                a.merges(w, y, s, false)
            })
        }

        function x(z, F, G) {
            var D = q.cache[z];
            if (D.length > 0) {
                var H, A = 1;
                if (G) {
                    H = D[0][G]
                } else {
                    H = t.eq(0).find("td").eq(F).find(".layui-table-cell").html()
                }
                for (var E = 1; E < D.length; E++) {
                    var I;
                    if (G) {
                        I = D[E][G]
                    } else {
                        I = t.eq(E).find("td").eq(F).find(".layui-table-cell").html()
                    } if (I === H) {
                        A++;
                        if (E === D.length - 1) {
                            t.eq(E - A + 1).find("td").eq(F).attr("rowspan", A);
                            for (var C = 1; C < A; C++) {
                                t.eq(E - C + 1).find("td").eq(F).attr("del", "true")
                            }
                        }
                    } else {
                        t.eq(E - A).find("td").eq(F).attr("rowspan", A);
                        for (var B = 1; B < A; B++) {
                            t.eq(E - B).find("td").eq(F).attr("del", "true")
                        }
                        A = 1;
                        H = I
                    }
                }
            }
        }
    };
    a.bindCtxMenu = function(t, r) {
        var u = q.cache[t];
        var s = "#" + t + "+.layui-table-view .layui-table-body tr";
        g(s).bind("contextmenu", function(v) {
            var w = g(this);
            g(s).removeClass("layui-table-click");
            w.addClass("layui-table-click");
            if (typeof r === "function") {
                r = r(u[w.data("index")], v.currentTarget)
            }

            function x(A) {
                if (!A) {
                    return
                }
                var y = [];
                for (var z = 0; z < A.length; z++) {
                    y.push({
                        icon: A[z].icon,
                        name: A[z].name,
                        _click: A[z].click,
                        click: function(D, C) {
                            var B = g(C.currentTarget);
                            this._click && this._click(u[B.data("index")], C.currentTarget);
                            B.removeClass("layui-table-click")
                        },
                        subs: x(A[z].subs)
                    })
                }
                return y
            }
            e.show(x(r), v.clientX, v.clientY, v);
            return false
        })
    };
    a.exportData = function(v) {
        var w = v.cols;
        var J = v.data;
        var H = v.fileName;
        var r = v.expType;
        var y = v.option;
        y || (y = {});
        if (c.ie) {
            return i.msg("不支持ie导出")
        }
        if (typeof J === "string") {
            var G = i.load(2);
            y.url = J;
            a.loadUrl(y, function(L) {
                i.close(G);
                v.data = L;
                a.exportData(v)
            });
            return
        }
        for (var F = 0; F < w.length; F++) {
            for (var E = 0; E < w[F].length; E++) {
                if (w[F][E].type === undefined) {
                    w[F][E].type = "normal"
                }
                if (w[F][E].hide === undefined) {
                    w[F][E].hide = false
                }
            }
        }
        var K = [],
            A = [],
            z = [];
        q.eachCols(undefined, function(L, M) {
            if (M.type === "normal" && !M.hide) {
                K.push(M.title || "");
                A.push(M.field || (m + L))
            }
        }, w);
        var I = a.parseTbData(w, a.deepClone(J), true);
        for (var B = 0; B < I.length; B++) {
            var u = [];
            for (var C = 0; C < A.length; C++) {
                var x = I[B][A[C]];
                x && (x = x.toString().replace(/,/g, "，"));
                u.push(x)
            }
            z.push(u.join(","))
        }
        var t = document.createElement("a");
        var s = ({
            csv: "text/csv",
            xls: "application/vnd.ms-excel"
        })[r || "xls"];
        var D = encodeURIComponent(K.join(",") + "\r\n" + z.join("\r\n"));
        t.href = "data:" + s + ";charset=utf-8,﻿" + D;
        t.download = (H || "table") + "." + (r || "xls");
        document.body.appendChild(t);
        t.click();
        document.body.removeChild(t)
    };
    a.exportDataX = function(r) {
        layui.use("excel", function() {
            var A = layui.excel;
            var D = r.cols;
            var v = r.data;
            var t = r.fileName;
            var E = r.expType;
            var x = r.option;
            x || (x = {});
            E || (E = "xlsx");
            if (v && typeof v === "string") {
                var u = i.load(2);
                x.url = v;
                a.loadUrl(x, function(F) {
                    i.close(u);
                    r.data = F;
                    a.exportDataX(r)
                });
                return
            }
            for (var w = 0; w < D.length; w++) {
                for (var s = 0; s < D[w].length; s++) {
                    if (D[w][s].type === undefined) {
                        D[w][s].type = "normal"
                    }
                    if (D[w][s].hide === undefined) {
                        D[w][s].hide = false
                    }
                }
            }
            var z = {},
                y = [];
            q.eachCols(undefined, function(F, G) {
                if (G.type === "normal" && !G.hide) {
                    var H = G.field || (m + F);
                    y.push(H);
                    z[H] = G.title || ""
                }
            }, D);
            var B = a.parseTbData(D, a.deepClone(v), true);
            var C = A.filterExportData(B, y);
            C.unshift(z);
            A.exportExcel({
                sheet1: C
            }, (t || "table") + "." + E, E)
        })
    };
    a.exportDataBack = function(t, x, r) {
        x || (x = {});
        if (!r || r.toString().toLowerCase() === "get") {
            var v = "";
            for (var z in x) {
                if (!v) {
                    v = ("?" + z + "=" + x[z])
                } else {
                    v += ("&" + z + "=" + x[z])
                }
            }
            window.open(t + v)
        } else {
            var s = '<html><body><form id="eFrom" action="' + t + '" method="' + r + '">';
            for (var w in x) {
                s += ('<textarea name="' + w + '">' + x[w] + "</textarea>")
            }
            s += "</form></body></html>";
            g("#exportFrame").remove();
            g("body").append('<iframe id="exportFrame" style="display: none;"></iframe>');
            var A = document.getElementById("exportFrame");
            var y = A.contentWindow;
            var u = y.document;
            y.focus();
            u.open();
            u.write(s);
            u.close();
            u.getElementById("eFrom").submit()
        }
    };
    a.render = function(t) {
        var r = g(t.elem).attr("lay-filter");
        t.autoSort = false;
        var s = q.render(t);
        q.on("sort(" + r + ")", function(w) {
            var u = w.field,
                x = w.type;
            var v = g.extend(t.where, {
                sort: u,
                order: x
            });
            s.reload({
                where: v,
                page: {
                    curr: 1
                }
            })
        });
        return s
    };
    a.renderFront = function(w) {
        var v, t = g(w.elem).attr("lay-filter");
        w.autoSort = false;
        for (var s = 0; s < w.cols.length; s++) {
            for (var r = 0; r < w.cols[s].length; r++) {
                if (w.cols[s][r].templet && !w.cols[s][r].field) {
                    w.cols[s][r].field = m + s + "_" + r
                }
            }
        }
        if (w.url) {
            var u = a.deepClone(w);
            u.data = [];
            u.url = undefined;
            v = q.render(u);
            v.reloadUrl = function(y) {
                var x = a.deepClone(w);
                y && (x = g.extend(x, y));
                g(w.elem + "+.layui-table-view>.layui-table-box").append('<div class="layui-table-init"><i class="layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop"></i></div>');
                a.loadUrl(x, function(z) {
                    v.reload({
                        url: "",
                        data: z,
                        page: {
                            curr: 1
                        }
                    });
                    a.putTbData(t, a.parseTbData(x.cols, z));
                    g("input[" + h + '="' + t + '"]').val("");
                    window.tbX.cacheSearch[t] = undefined
                })
            };
            v.reloadUrl()
        } else {
            v = q.render(w);
            v.reloadData = function(x) {
                v.reload(x);
                a.parseTbData(w.cols, x.data);
                a.putTbData(t, x.data);
                g("input[" + h + '="' + t + '"]').val("");
                window.tbX.cacheSearch[t] = undefined
            };
            a.putTbData(t, a.parseTbData(w.cols, w.data))
        }
        a.renderAllTool(v);
        return v
    };
    a.loadUrl = function(t, u) {
        t.response = g.extend({
            statusName: "code",
            statusCode: 0,
            msgName: "msg",
            dataName: "data",
            countName: "count"
        }, t.response);
        var r = t.response;
        var s = t.where;
        if (t.contentType && t.contentType.indexOf("application/json") === 0) {
            s = JSON.stringify(s)
        }
        g.ajax({
            type: t.method || "get",
            url: t.url,
            contentType: t.contentType,
            data: s,
            dataType: "json",
            headers: t.headers || {},
            success: function(v) {
                if (typeof t.parseData === "function") {
                    v = t.parseData(v) || v
                }
                if (v[r.statusName] != r.statusCode) {
                    var w = v[r.msgName] || ("返回的数据不符合规范，正确的成功状态码 (" + r.statusName + ") 应为：" + r.statusCode);
                    i.msg(w, {
                        icon: 2
                    })
                } else {
                    u(v[r.dataName])
                }
            },
            error: function(w, v) {
                i.msg("数据接口请求异常：" + v, {
                    icon: 2
                })
            }
        })
    };
    a.parseTbData = function(x, u, s) {
        var w = [];
        q.eachCols(undefined, function(A, B) {
            if (B.templet) {
                var z = {
                    field: ((B.field && (s || B.field.indexOf(m) === 0)) ? B.field : ("txField_" + A))
                };
                if (typeof B.templet === "string") {
                    z.templet = function(D) {
                        var C = undefined;
                        n(g(B.templet).html()).render(D, function(E) {
                            C = E
                        });
                        return C
                    }
                } else {
                    z.templet = B.templet
                }
                w.push(z)
            }
        }, x);
        for (var t = 0; t < u.length; t++) {
            var v = u[t];
            for (var r = 0; r < w.length; r++) {
                var y = "<div>" + w[r].templet(v) + "</div>";
                v[w[r].field] = g(y).not(".export-hide").text().replace(/(^\s*)|(\s*$)/g, "")
            }
        }
        return u
    };
    a.putTbData = function(s, r) {
        window.tbX.cache[s] = r
    };
    a.getTbData = function(s) {
        var r = window.tbX.cache[s];
        return a.deepClone(r || q.cache[s])
    };
    a.filterData = function(z, s, r) {
        var t = [],
            x;
        for (var w = 0; w < z.length; w++) {
            var v = z[w];
            if (!x) {
                if (!s) {
                    x = [];
                    for (var y in v) {
                        if (!v.hasOwnProperty(y)) {
                            continue
                        }
                        x.push(y)
                    }
                } else {
                    x = s.split(",")
                }
            }
            for (var u = 0; u < x.length; u++) {
                if (a.isContains(v[x[u]], r)) {
                    t.push(v);
                    break
                }
            }
        }
        return t
    };
    a.isContains = function(s, r) {
        s || (s = "");
        r || (r = "");
        s = s.toString().toLowerCase();
        r = r.toString().toLowerCase();
        if (s === r || s.indexOf(r) >= 0) {
            return true
        }
        return false
    };
    a.renderAllTool = function(r) {
        o(r);
        l(r);
        j(r);
        k(r)
    };
    a.deepClone = function(u) {
        var r;
        var s = a.isClass(u);
        if (s === "Object") {
            r = {}
        } else {
            if (s === "Array") {
                r = []
            } else {
                return u
            }
        }
        for (var t in u) {
            if (!u.hasOwnProperty(t)) {
                continue
            }
            var v = u[t];
            if (a.isClass(v) === "Object") {
                r[t] = arguments.callee(v)
            } else {
                if (a.isClass(v) === "Array") {
                    r[t] = arguments.callee(v)
                } else {
                    r[t] = u[t]
                }
            }
        }
        return r
    };
    a.isClass = function(r) {
        if (r === null) {
            return "Null"
        }
        if (r === undefined) {
            return "Undefined"
        }
        return Object.prototype.toString.call(r).slice(8, -1)
    };
    window.tbX || (window.tbX = {});
    window.tbX.cache || (window.tbX.cache = {});
    window.tbX.cacheSearch || (window.tbX.cacheSearch = {});
    var j = function(s) {
        var r = s.config.id,
            t = g("input[" + h + '="' + r + '"]');
        if (!(t && t.length > 0)) {
            return
        }
        if (!t.attr("placeholder")) {
            t.attr("placeholder", "输入关键字按回车键搜索")
        }
        t.off("keydown").on("keydown", function(x) {
            if (x.keyCode !== 13) {
                return
            }
            var u = t.attr("name");
            var y = t.val().replace(/(^\s*)|(\s*$)/g, "");
            var v = i.msg("搜索中..", {
                icon: 16,
                shade: 0.01,
                time: 0
            });
            var w = a.getTbData(r);
            var z = a.filterData(w, u, y);
            window.tbX.cacheSearch[r] = z;
            s.reload({
                url: "",
                data: z,
                page: {
                    curr: 1
                }
            });
            i.close(v)
        })
    };
    var l = function(s) {
        var r = s.config.id;
        q.on("sort(" + r + ")", function(w) {
            var t = w.field,
                x = w.type;
            var u = i.msg("加载中..", {
                icon: 16,
                shade: 0.01,
                time: 0
            });
            var v = window.tbX.cacheSearch[r];
            v || (v = a.getTbData(r));
            if (x) {
                v = v.sort(function(B, z) {
                    var A = B[t],
                        y = z[t];
                    if (x === "asc") {
                        return (A === y) ? 0 : ((A < y) ? -1 : 1)
                    } else {
                        return (A === y) ? 0 : ((A < y) ? 1 : -1)
                    }
                })
            }
            s.reload({
                initSort: w,
                url: "",
                data: v,
                page: {
                    curr: 1
                }
            });
            i.close(u)
        })
    };
    var o = function(r) {
        g("[" + p + '="' + r.config.id + '"]').off("click").on("click", function() {
            if (r.reloadUrl) {
                r.reloadUrl()
            } else {
                r.reload({
                    page: {
                        curr: 1
                    }
                })
            }
        })
    };
    var k = function(s) {
        var r = s.config.id;
        g("[" + b + '="' + r + '"]').off("click").on("click", function(t) {
            if (g(this).find(".tbx-dropdown-menu").length > 0) {
                return
            }
            if (t !== void 0) {
                t.preventDefault();
                t.stopPropagation()
            }
            var u = '<div class="tbx-dropdown-menu">';
            u += '      <div class="tbx-dropdown-menu-item" data-type="check">导出选中数据</div>';
            u += '      <div class="tbx-dropdown-menu-item" data-type="current">导出当前页数据</div>';
            u += '      <div class="tbx-dropdown-menu-item" data-type="all">导出全部数据</div>';
            u += "   </div>";
            g(this).append(u);
            g(this).addClass("tbx-dropdown-btn");
            g(this).parent().css("position", "relative");
            g(this).parent().css("z-index", "9998");
            g(".tbx-dropdown-menu").off("click").on("click", ".tbx-dropdown-menu-item", function(x) {
                var w = g(this).data("type");
                if (w === "check") {
                    var v = q.checkStatus(r);
                    if (v.data.length === 0) {
                        i.msg("请选择要导出的数据", {
                            icon: 2
                        })
                    } else {
                        g(".tbx-dropdown-menu").remove();
                        a.exportData({
                            fileName: s.config.title,
                            cols: s.config.cols,
                            data: v.data
                        })
                    }
                } else {
                    if (w === "current") {
                        a.exportData({
                            fileName: s.config.title,
                            cols: s.config.cols,
                            data: q.cache[r]
                        })
                    } else {
                        if (w === "all") {
                            a.exportData({
                                fileName: s.config.title,
                                cols: s.config.cols,
                                data: a.getTbData(r)
                            })
                        }
                    }
                } if (x !== void 0) {
                    x.preventDefault();
                    x.stopPropagation()
                }
            })
        });
        g(document).off("click.tbxDropHide").on("click.tbxDropHide", function() {
            g(".tbx-dropdown-menu").remove()
        })
    };
    var d = function() {
        var r = ".tbx-dropdown-btn {";
        r += "        position: relative;";
        r += "   }";
        r += "   .tbx-dropdown-btn:hover {";
        r += "        opacity: 1";
        r += "   }";
        r += "   .tbx-dropdown-menu {";
        r += "        position: absolute;";
        r += "        top: 100%;";
        r += "        right: 0;";
        r += "        padding: 5px 0;";
        r += "        margin: 5px 0 0 0;";
        r += "        overflow: visible;";
        r += "        min-width: 110px;";
        r += "        background: #fff;";
        r += "        border-radius: 2px;";
        r += "        box-shadow: 0 2px 4px rgba(0, 0, 0, .12);";
        r += "        border: 1px solid #d2d2d2;";
        r += "        z-index: 9998;";
        r += "        cursor: default;";
        r += "   }";
        r += "   .tbx-dropdown-menu .tbx-dropdown-menu-item {";
        r += "        display: block;";
        r += "        color: #555;";
        r += "        font-size: 14px;";
        r += "        padding: 10px 15px;";
        r += "        text-decoration: none;";
        r += "        white-space: nowrap;";
        r += "        cursor: pointer;";
        r += "        user-select: none;";
        r += "        line-height: normal;";
        r += "   }";
        r += "   .tbx-dropdown-menu .tbx-dropdown-menu-item:hover {";
        r += "        background-color: #eeeeee;";
        r += "   }";
        r += "   .export-show {";
        r += "        display: none;";
        r += "   }";
        return r
    };
    g("head").append('<style id="ew-css-tbx" type="text/css">' + d() + "</style>");
    f("tableX", a)
});