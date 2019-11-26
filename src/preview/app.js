!function() {
  "use strict";
  var t = "undefined" == typeof global ? self : global;
  if ("function" != typeof t.require) {
    var e = {}
      , r = {}
      , n = {}
      , i = {}.hasOwnProperty
      , a = /^\.\.?(\/|$)/
      , o = function(t, e) {
      for (var r, n = [], i = (a.test(e) ? t + "/" + e : e).split("/"), o = 0, s = i.length; o < s; o++)
        r = i[o],
          ".." === r ? n.pop() : "." !== r && "" !== r && n.push(r);
      return n.join("/")
    }
      , s = function(t) {
      return t.split("/").slice(0, -1).join("/")
    }
      , l = function(e) {
      return function(r) {
        var n = o(s(e), r);
        return t.require(n, e)
      }
    }
      , d = function(t, e) {
      var n = m && m.createHot(t)
        , i = {
        id: t,
        exports: {},
        hot: n
      };
      return r[t] = i,
        e(i.exports, l(t), i),
        i.exports
    }
      , u = function(t) {
      return n[t] ? u(n[t]) : t
    }
      , c = function(t, e) {
      return u(o(s(t), e))
    }
      , f = function(t, n) {
      null == n && (n = "/");
      var a = u(t);
      if (i.call(r, a))
        return r[a].exports;
      if (i.call(e, a))
        return d(a, e[a]);
      throw new Error("Cannot find module '" + t + "' from '" + n + "'")
    };
    f.alias = function(t, e) {
      n[e] = t
    }
    ;
    var h = /\.[^.\/]+$/
      , v = /\/index(\.[^\/]+)?$/
      , y = function(t) {
      if (h.test(t)) {
        var e = t.replace(h, "");
        i.call(n, e) && n[e].replace(h, "") !== e + "/index" || (n[e] = t)
      }
      if (v.test(t)) {
        var r = t.replace(v, "");
        i.call(n, r) || (n[r] = t)
      }
    };
    f.register = f.define = function(t, n) {
      if (t && "object" == typeof t)
        for (var a in t)
          i.call(t, a) && f.register(a, t[a]);
      else
        e[t] = n,
          delete r[t],
          y(t)
    }
      ,
      f.list = function() {
        var t = [];
        for (var r in e)
          i.call(e, r) && t.push(r);
        return t
      }
    ;
    var m = t._hmr && new t._hmr(c,f,e,r);
    f._cache = r,
      f.hmr = m && m.wrap,
      f.brunch = !0,
      t.require = f
  }
}(),
  function() {
    var t;
    "undefined" == typeof window ? this : window;
    require.register("decks.js", function(t, e, r) {
      "use strict";
      function n(t) {
        return t && t.__esModule ? t : {
          "default": t
        }
      }
      Object.defineProperty(t, "__esModule", {
        value: !0
      });
      var i = e("jquery")
        , a = n(i)
        , o = {
        init: function() {
          this.updateTimezone()
        },
        actOn: function(t) {
          var e = (0,
            a["default"])("#did" + t);
          e.append('<img id=act src="/static/activity.gif">')
        },
        actOff: function() {
          (0,
            a["default"])("#act").remove()
        },
        select: function(t) {
          var e = this;
          this.actOn(t);
          var r = "select/" + t;
          a["default"].post(r, {}, function(t) {
            e.actOff(),
              window.location = _host + "/study/"
          })
        },
        deckok: function(t) {
          return !/::$|^::|::::/.test(t)
        },
        rem: function(t) {
          var e = this;
          return "1" === t ? void alert("The default deck can not be removed at the moment. You can delete cards that are inside it with the computer version.") : void (confirm("Delete all cards in deck? This can not be undone.") && (this.actOn(t),
            a["default"].getJSON("delete", {
              id: t
            }, function(t) {
              e.actOff(),
                window.location.reload()
            })))
        },
        share: function(t) {
          if ("1" === t) {
            var e = "Unfortunately the default deck can't be shared at the moment. Please move your cards into a new deck using the computer version, and then try again.";
            return void alert(e)
          }
          window.location = "/decks/share/" + t
        },
        rename: function(t) {
          var e = this
            , r = (0,
            a["default"])("#did" + t)
            , n = r.data("full")
            , i = prompt("Enter new name", n);
          if (i && n !== i) {
            if (!this.deckok(i))
              return void alert("Invalid name");
            this.actOn(t),
              a["default"].getJSON("rename", {
                id: t,
                "new": i
              }, function(t) {
                e.actOff(),
                  "ok" === t.status ? window.location.reload() : "exists" === t.status && alert("The provided deck already exists.")
              })
          }
        },
        updateTimezone: function() {
          var t = (new Date).getTimezoneOffset();
          a["default"].post("/decks/updateTimezone", {
            offset: t
          }, function(t) {
            t.needRefresh && location.reload(!0)
          }).fail(function() {
            return window.alert("Error communicating with server.")
          })
        }
      };
      (0,
        a["default"])(function() {
        return o.init()
      }),
        t["default"] = o
    }),
      require.register("editor.js", function(t, e, r) {
        "use strict";
        function n(t) {
          return t && t.__esModule ? t : {
            "default": t
          }
        }
        Object.defineProperty(t, "__esModule", {
          value: !0
        });
        var i = function() {
          function t(t, e) {
            var r = []
              , n = !0
              , i = !1
              , a = void 0;
            try {
              for (var o, s = t[Symbol.iterator](); !(n = (o = s.next()).done) && (r.push(o.value),
              !e || r.length !== e); n = !0)
                ;
            } catch (l) {
              i = !0,
                a = l
            } finally {
              try {
                !n && s["return"] && s["return"]()
              } finally {
                if (i)
                  throw a
              }
            }
            return r
          }
          return function(e, r) {
            if (Array.isArray(e))
              return e;
            if (Symbol.iterator in Object(e))
              return t(e, r);
            throw new TypeError("Invalid attempt to destructure non-iterable instance")
          }
        }()
          , a = e("jquery")
          , o = n(a)
          , s = {
          version: "20170111",
          data: [],
          init: function() {
            "add" === this.mode ? (this.setupModels(),
              this.setupDecks()) : ((0,
              o["default"])("#modelarea").hide(),
              (0,
                o["default"])("#save2").show(),
              this.drawFields(),
              this.fillFields())
          },
          setupModels: function() {
            var t = this
              , e = ""
              , r = 0;
            this.models.sort(function(t, e) {
              return t.name.localeCompare(e.name)
            });
            var n = !0
              , i = !1
              , a = void 0;
            try {
              for (var s, l = Array.from(this.models)[Symbol.iterator](); !(n = (s = l.next()).done); n = !0) {
                var d, u = s.value;
                u.id.toString() === this.curModelID.toString() ? (d = "selected",
                  this.curModel = u) : d = "",
                  e += "<option " + d + " value=" + r + ">" + u.name + "</option>",
                  r += 1
              }
            } catch (c) {
              i = !0,
                a = c
            } finally {
              try {
                !n && l["return"] && l["return"]()
              } finally {
                if (i)
                  throw a
              }
            }
            (0,
              o["default"])("#models").html(e),
              (0,
                o["default"])("#models").change(function() {
                return t.onModelChange()
              }),
              this.onModelChange()
          },
          setupDecks: function() {
            var t = [];
            for (var e in this.decks) {
              var r = this.decks[e];
              t.push(r.name)
            }
            t.sort(),
              (0,
                o["default"])("#deck").completer({
                source: t,
                suggest: !0
              })
          },
          onModelChange: function() {
            var t = (0,
              o["default"])("#models").val();
            this.curModel = this.models[t];
            var e = this.decks[this.curModel.did];
            e ? (0,
              o["default"])("#deck").val(e.name) : (0,
              o["default"])("#deck").val("Default"),
              this.drawFields()
          },
          drawFields: function() {
            var t = ""
              , e = 0
              , r = []
              , n = !0
              , a = !1
              , s = void 0;
            try {
              for (var l, d = Array.from(this.curModel.flds)[Symbol.iterator](); !(n = (l = d.next()).done); n = !0) {
                var u = l.value;
                r.push([u.name, e]),
                  e++
              }
            } catch (c) {
              a = !0,
                s = c
            } finally {
              try {
                !n && d["return"] && d["return"]()
              } finally {
                if (a)
                  throw s
              }
            }
            r.push(["Tags", -1]);
            var f = !0
              , h = !1
              , v = void 0;
            try {
              for (var y, m = Array.from(r)[Symbol.iterator](); !(f = (y = m.next()).done); f = !0) {
                var w = i(y.value, 2)
                  , p = w[0]
                  , g = w[1];
                t += '\n<div class="form-group row">\n    <label for="f' + g + '" class="col-2 col-form-label">' + p + '</label>\n    <div class="col-10"><div class="form-control field" id="f' + g + '" contentEditable></div></div>\n</div>\n'
              }
            } catch (c) {
              h = !0,
                v = c
            } finally {
              try {
                !f && m["return"] && m["return"]()
              } finally {
                if (h)
                  throw v
              }
            }
            (0,
              o["default"])("#fields").html(t),
              this.setFonts()
          },
          setFonts: function() {
            var t = 0;
            Array.from(this.curModel.flds).map(function(e) {
              return (0,
                o["default"])("#f" + t).css("font-family", e.font).css("font-size", e.size),
                t++
            })
          },
          fillFields: function() {
            for (var t = 0; t < this.curModel.flds.length; t++)
              (0,
                o["default"])("#f" + t).html(this.note[0][t]);
            (0,
              o["default"])("#f-1").html(this.note[1].join(" "))
          },
          getFields: function() {
            for (var t = [], e = 0; e < this.curModel.flds.length; e++)
              t.push((0,
                o["default"])("#f" + e).html());
            return [t, (0,
              o["default"])("#f-1").text()]
          },
          save: function() {
            var t = this
              , e = this.getFields()
              , r = !0
              , n = !1
              , i = void 0;
            try {
              for (var a, s = Array.from(e)[Symbol.iterator](); !(r = (a = s.next()).done); r = !0) {
                var l = a.value;
                if (/src="data:/.test(l) || /webkit-fake-url/.test(l))
                  return void alert("Sorry, adding embedded images via AnkiWeb is not currently supported. Please use the computer version or a mobile client.")
              }
            } catch (d) {
              n = !0,
                i = d
            } finally {
              try {
                !r && s["return"] && s["return"]()
              } finally {
                if (n)
                  throw i
              }
            }
            var u = {
              nid: this.nid,
              data: JSON.stringify(e),
              csrf_token: this.csrf_token2
            };
            this.nid || (u.mid = this.curModel.id,
              u.deck = (0,
                o["default"])("#deck").val()),
              o["default"].post("/edit/save", u, function(e) {
                if (t.nid)
                  return void (window.location = "/study/");
                if (0 === e)
                  return void alert("No cards could be created with the provided fields.");
                t.showMsg("Added.");
                var r = 0;
                return function() {
                  var e = []
                    , n = !0
                    , i = !1
                    , a = void 0;
                  try {
                    for (var s, d = Array.from(t.curModel.flds)[Symbol.iterator](); !(n = (s = d.next()).done); n = !0)
                      l = s.value,
                      0 === r && (0,
                        o["default"])("#f" + r).focus(),
                      l.sticky || (0,
                        o["default"])("#f" + r).html(""),
                        e.push(r++)
                  } catch (u) {
                    i = !0,
                      a = u
                  } finally {
                    try {
                      !n && d["return"] && d["return"]()
                    } finally {
                      if (i)
                        throw a
                    }
                  }
                  return e
                }()
              }).fail(function() {
                return window.alert("Error saving.")
              })
          },
          showMsg: function(t) {
            window.scrollTo(0, 1),
              (0,
                o["default"])("#msg").show().text(t).fadeOut(3e3)
          }
        };
        t["default"] = s
      }),
      require.register("initialize.js", function(t, e, r) {
        "use strict";
        e("babel-polyfill"),
          document.addEventListener("DOMContentLoaded", function() {})
      }),
      require.register("options.js", function(t, e, r) {
        "use strict";
        function n(t) {
          return t && t.__esModule ? t : {
            "default": t
          }
        }
        Object.defineProperty(t, "__esModule", {
          value: !0
        });
        var i = e("jquery")
          , a = n(i)
          , o = {
          confs: [],
          deck: null,
          idx: null,
          init: function() {
            var t = this;
            this.drawConfs(),
              this.readOpts(),
              (0,
                a["default"])("#confs").change(function() {
                return t.onChange()
              })
          },
          drawConfs: function() {
            var t = (0,
              a["default"])("#confs");
            t.empty();
            var e = 0
              , r = !0
              , n = !1
              , i = void 0;
            try {
              for (var o, s = Array.from(this.confs)[Symbol.iterator](); !(r = (o = s.next()).done); r = !0) {
                var l = o.value;
                (0,
                  a["default"])("<option value=" + e + ">" + l.name + "</option>").appendTo(t),
                l.id.toString() === this.deck.conf.toString() && (this.idx = e),
                  e++
              }
            } catch (d) {
              n = !0,
                i = d
            } finally {
              try {
                !r && s["return"] && s["return"]()
              } finally {
                if (n)
                  throw i
              }
            }
            t.val(this.idx)
          },
          readOpts: function() {
            var t = this.confs[this.idx];
            (0,
              a["default"])("#newDay").val(t["new"].perDay),
              (0,
                a["default"])("#revDay").val(t.rev.perDay)
          },
          writeOpts: function(t) {
            var e = {
              revDay: (0,
                a["default"])("#revDay").val(),
              newDay: (0,
                a["default"])("#newDay").val()
            };
            a["default"].post("saveOpts", {
              data: JSON.stringify(e)
            }, function(e) {
              t && t()
            })
          },
          save: function() {
            return this.writeOpts(function() {
              window.location = "/study/"
            }),
              !1
          }
        };
        (0,
          a["default"])(function() {
          return o.init()
        }),
          t["default"] = o
      }),
      require.register("sharedlist.js", function(t, e, r) {
        "use strict";
        function n(t) {
          return t && t.__esModule ? t : {
            "default": t
          }
        }
        Object.defineProperty(t, "__esModule", {
          value: !0
        });
        var i = e("jquery")
          , a = n(i)
          , o = {
          init: function() {
            this.sortStars()
          },
          cmp: function(t, e) {
            return t > e ? 1 : t < e ? -1 : 0
          },
          sortStars: function() {
            var t = this
              , e = function(t, e) {
              if (0 === e)
                return 0;
              var r = 1.96
                , n = 1 * t / e;
              return (n + r * r / (2 * e) - r * Math.sqrt((n * (1 - n) + r * r / (4 * e)) / e)) / (1 + r * r / e)
            };
            this.files.sort(function(r, n) {
              var i = r[2]
                , a = i + r[3]
                , o = n[2]
                , s = o + n[3]
                , l = e(i, a)
                , d = e(o, s);
              return l || d ? t.cmp(d, l) : t.cmp(r[3], n[3])
            }),
              this.render()
          },
          sortMod: function() {
            var t = this;
            this.files.sort(function(e, r) {
              return t.cmp(r[4], e[4])
            }),
              this.render()
          },
          sortTitle: function() {
            var t = this;
            this.files.sort(function(e, r) {
              return t.cmp(e[1].toLowerCase(), r[1].toLowerCase())
            }),
              this.render()
          },
          render: function() {
            if (this.files.length) {
              var t = ""
                , e = !0
                , r = !1
                , n = void 0;
              try {
                for (var i, o = Array.from(this.files)[Symbol.iterator](); !(e = (i = o.next()).done); e = !0) {
                  var s, l, d, u = i.value;
                  this.decks && (d = u[5],
                    s = u[6] ? u[6] : "<font color='#eee'>0</span>",
                    l = u[7] ? u[7] : "<font color='#eee'>0</span>"),
                    t += '<tr><td></td><td class=decktitle><a href="/shared/info/' + u[0] + '">' + u[1] + "</a></td>\n<td align=left>" + this.stars(u) + '</td>\n<td class="hidden-sm-down" align=left>' + this.mod(u[4]) + "</td>",
                  this.decks && (t += "<td align=right>" + d + '</td>\n<td align=right class="hidden-sm-down">' + s + '</td>\n<td align=right class="hidden-sm-down">' + l + "</td>")
                }
              } catch (c) {
                r = !0,
                  n = c
              } finally {
                try {
                  !e && o["return"] && o["return"]()
                } finally {
                  if (r)
                    throw n
                }
              }
              t += "</tr>";
              var f = '<table width=100%\ncellspacing=4><tr><th></th>\n<th align=left><a href="#" onclick="shared.sortTitle();return false;">Title</a></th>\n<th align=left><a href="#" onclick="shared.sortStars();return false;">Ratings</a></th>\n<th class="hidden-sm-down modcol"><a href="#" onclick="shared.sortMod();return false;">Modified</a></th>';
              this.decks && (f += '<th align=right>Notes</th><th align=right class="hidden-sm-down">Audio</th>\n<th align=right class="hidden-sm-down">Images</th>'),
                f += "</tr>",
                t = f + t + "</table>",
              1e3 === this.files.length && (t += "<p>More that 1000 matches found; please narrow your search."),
                (0,
                  a["default"])("#list").html(t)
            }
          },
          stars: function(t) {
            var e = t[2] + t[3]
              , r = Math.round(t[2] / e * 25);
            return '<table><tr><td><div class="likebar dislike"><div class="likebar like" style="width: ' + r + 'px;"></div></div></td><td>' + e + "</td></tr></table>"
          },
          mod: function(t) {
            var e = new Date(t *= 1e3)
              , r = e.getMonth() + 1;
            r < 10 && (r = "0" + r);
            var n = e.getDate();
            return n < 10 && (n = "0" + n),
            e.getFullYear() + "-" + r + "-" + n
          },
          search: function() {
            window.location = "/shared/decks/" + (0,
              a["default"])("#q").val()
          }
        };
        (0,
          a["default"])(function() {
          return o.init()
        }),
          t["default"] = o
      }),
      require.register("study.js", function(t, e, r) {
        "use strict";
        function n(t) {
          return t && t.__esModule ? t : {
            "default": t
          }
        }
        Object.defineProperty(t, "__esModule", {
          value: !0
        });
        var i = e("jquery")
          , a = n(i);
        String.prototype.format = function() {
          var t = /\{\d+\}/g
            , e = arguments;
          return this.replace(t, function(t) {
            return e[t.match(/\d+/)]
          })
        }
        ;
        var o = {
          state: "initial",
          lastCardShown: 0,
          currentCard: null,
          activityCount: 0,
          CID: 0,
          CQUESTION: 1,
          CANSWER: 2,
          CQUEUE: 3,
          CNID: 4,
          CINTS: 5,
          CORD: 6,
          deck: {
            stats: [0, 0, 0],
            cards: [],
            answers: []
          },
          targetURL: null,
          zoom: 1,
          cacheBust: 1,
          initStudy: function() {
            var t = this;
            (0,
              a["default"])("#overview").hide(),
              (0,
                a["default"])("#quiz").show(),
              window.onbeforeunload = function() {
                return t.deck.answers.length ? "Please save first." : null
              }
              ,
              (0,
                a["default"])(document).keyup(function(e) {
                e.which >= 49 && e.which <= 52 && t.answerCard(e.which - 48)
              }),
              (0,
                a["default"])("content").addClass("contentAjax"),
              this.checkForNextCard()
          },
          getNextCard: function() {
            this.currentCard = this.deck.cards.shift()
          },
          getCards: function(t) {
            var e = this;
            if (null == t && (t = !1),
            0 !== this.deck.cards.length && !t)
              return this.drawQuestion();
            0 === this.deck.cards.length && this.showWaiting();
            var r = this.deck.answers;
            this.deck.answers = [];
            var n = {
              answers: r
            };
            t && (n.force = !0),
              this.getJSON(_ihost + "/study/getCards", n, function(r) {
                if (e.targetURL)
                  return void (window.parent.location = e.targetURL);
                if (e.hideWaiting(),
                  t)
                  return void e.updateStatus();
                r.error && alert("Your collection is in an inconsistent state. Please use Tools>Check Database on the computer version."),
                  e.deck.stats = r.counts;
                var n = !0
                  , i = !1
                  , a = void 0;
                try {
                  for (var o, s = Array.from(r.cards)[Symbol.iterator](); !(n = (o = s.next()).done); n = !0) {
                    var l = o.value;
                    e.deck.cards.push(l)
                  }
                } catch (d) {
                  i = !0,
                    a = d
                } finally {
                  try {
                    !n && s["return"] && s["return"]()
                  } finally {
                    if (i)
                      throw a
                  }
                }
                e.deck.cards.length ? e.drawQuestion() : window.location.reload()
              })
          },
          saveThenGoto: function(t) {
            this.targetURL = _host + t,
              this.getCards(!0)
          },
          checkForNextCard: function() {
            this.state = "initial",
              (0,
                a["default"])("#qa").html(""),
              this.vhide("#easebuts"),
              this.vshow("#quiz"),
              this.getCards()
          },
          save: function() {
            this.deck.answers.length && this.getCards(!0)
          },
          drawQuestion: function() {
            if ("initial" === this.state) {
              this.getNextCard(),
                this.state = "questionShown",
                this.lastCardShown = (new Date).getTime(),
                this.updateStatus();
              var t = (0,
                a["default"])("#qa_box");
              t[0].className = "card card" + (this.currentCard[this.CORD] + 1),
                (0,
                  a["default"])("#qa").html(this.wrappedQA("q")),
                this._resizeFonts(),
                this.vshow("#ansbut"),
                (0,
                  a["default"])("#ansbuta").focus(),
                document.getElementById("quiz").scrollIntoView()
            }
          },
          drawAnswer: function() {
            var t = void 0;
            if ("questionShown" !== this.state)
              return !1;
            var e = this._getButtons()
              , r = "<center><table><tr>"
              , n = !0
              , i = !1
              , o = void 0;
            try {
              for (var s, l = Array.from(e)[Symbol.iterator](); !(n = (s = l.next()).done); n = !0) {
                var d = s.value
                  , u = void 0;
                "Good" === d[1] ? (u = "btn-primary",
                  t = d[0]) : u = "btn-secondary",
                  r += "<td valign=top align=center>\n" + d[2] + "<br><button id=ease" + d[0] + ' class="btn ' + u + ' btn-lg"\nonclick="study.answerCard(' + d[0] + ');">' + d[1] + "</button></td>"
              }
            } catch (c) {
              i = !0,
                o = c
            } finally {
              try {
                !n && l["return"] && l["return"]()
              } finally {
                if (i)
                  throw o
              }
            }
            r += "</tr></table></center>",
              this.vhide("#ansbut"),
              (0,
                a["default"])("#easebuts").html(r),
              this.state = "answerShown",
              this.vshow("#easebuts"),
              (0,
                a["default"])("#qa").html(this.wrappedQA("a")),
              this._resizeFonts(),
              (0,
                a["default"])("#ease" + t).focus();
            var f = document.getElementById("answer");
            return f && setTimeout(function() {
              f.scrollIntoView()
            }, 10),
              !1
          },
          _getButtons: function() {
            var t = this.currentCard[this.CINTS];
            return 4 === t.length ? [[1, "Again", t[0]], [2, "Hard", t[1]], [3, "Good", t[2]], [4, "Easy", t[3]]] : 3 === t.length ? [[1, "Again", t[0]], [2, "Good", t[1]], [3, "Easy", t[2]]] : [[1, "Again", t[0]], [2, "Good", t[1]]]
          },
          answerCard: function(t) {
            "answerShown" === this.state && (t > this.currentCard[this.CINTS].length || (this.state = "initial",
              this.deck.answers.push([this.currentCard[this.CID], t, (new Date).getTime() - this.lastCardShown]),
              this.currentCard = null,
              this.checkForNextCard()))
          },
          updateStatus: function() {
            var t = void 0
              , e = void 0
              , r = void 0
              , n = this.deck.stats;
            if (this.currentCard) {
              var i = this.currentCard[this.CQUEUE];
              e = "<font color=#990000>{0}</font>".format(n[1]),
                r = "<font color=#009900>{0}</font>".format(n[2]),
                t = "<font color=#0000ff>{0}</font>".format(n[0]),
                0 === i ? t = "<u>{0}</u>".format(t) : 1 === i ? e = "<u>{0}</u>".format(e) : r = "<u>{0}</u>".format(r),
                (0,
                  a["default"])("#rightStudyMenu").html("{0} + {1} + {2}".format(t, e, r))
            } else
              e = 0,
                r = 0,
                t = 0,
                (0,
                  a["default"])("#rightStudyMenu").html("");
            var o = "";
            if (this.currentCard) {
              var s = void 0;
              o = "<a class='btn btn-secondary' onclick='study.saveThenGoto(\"/edit/{0}\")'>Edit</a> ".format(this.currentCard[this.CNID]) + o,
                s = this.deck.answers.length ? "btn btn-secondary" : "btn btn-secondary disabled",
                o += " <a class='" + s + "' title=\"Save recent answers\" onclick='return study.save();'>Save</a>",
                o += '\n  <button class="btn btn-secondary" type="button" title="Bigger text" onclick="study.bigger();">+</button>\n  <button class="btn btn-secondary" type="button" title="Smaller text" onclick="study.smaller();">-</button>\n'
            }
            (0,
              a["default"])("#leftStudyMenu").html(o)
          },
          randomUniform: function(t, e) {
            return Math.random() * (e - t) + t
          },
          vshow: function(t) {
            return (0,
              a["default"])(t).removeClass("invisible")
          },
          vhide: function(t) {
            return (0,
              a["default"])(t).addClass("invisible")
          },
          showWaiting: function() {
            return this.vshow("#activity")
          },
          hideWaiting: function() {
            return this.vhide("#activity")
          },
          showQuiz: function() {
            return this.vshow("#quiz")
          },
          getJSON: function(t, e, r, n) {
            var i = this;
            null == n && (n = !1);
            var o = !0
              , s = !1
              , l = void 0;
            try {
              for (var d, u = Object.keys(e || {})[Symbol.iterator](); !(o = (d = u.next()).done); o = !0) {
                var c = d.value;
                e[c] = JSON.stringify(e[c])
              }
            } catch (f) {
              s = !0,
                l = f
            } finally {
              try {
                !o && u["return"] && u["return"]()
              } finally {
                if (s)
                  throw l
              }
            }
            e.ts = (new Date).getTime(),
            n || this.showWaiting();
            var h = a["default"].post(t, e, function(t) {
              try {
                r(t)
              } catch (e) {
                console.warn(e);
                try {
                  console.warn(e.stack)
                } catch (e) {}
              }
            }, "json");
            h.fail(function(t, e, r) {
              i.hideWaiting(),
                alert("Error while saving latest answers. Reloading..."),
                window.location.reload()
            })
          },
          wrappedQA: function(t) {
            var e = "q" === t ? this.CQUESTION : this.CANSWER
              , r = this.currentCard[e]
              , n = ""
              , i = 1
              , a = function(t, e) {
              if (/.mp3/i.test(e)) {
                var r = '<div style="display: inline-block;" id="_player_' + i + '"></div>\n<div style="display: inline-block; font-size: 20px !important;" id="jp_container_' + i + '">\n <a href="#" class="jp-play">Play</a>\n <a href="#" class="jp-pause">Pause</a>\n</div>\n<script type="text/javascript">\n  $("#_player_' + i + '").jPlayer({\n   ready: function () {\n    $(this).jPlayer("setMedia", {\n     mp3: "' + e + '"\n    });\n   },\n   ended: function () {\n    $(this).jPlayer("setMedia", {\n     mp3: "' + e + '"\n    });\n    \n   },\n   error: function (event) {\n    if (event.jPlayer.error.type == $.jPlayer.error.URL) {\n        $("#jp_container_' + i + ' .jp-play").text("(missing audio)");\n    } else {\n        console.warn("Error playing file: "+event.jPlayer.error.type);\n    }\n   },\n   cssSelectorAncestor: "#jp_container_' + i + '",\n   swfPath: "/static/",\n   supplied: "mp3",\n   errorAlerts: true,\n   preload:"none"\n  });\n</script>';
                return i += 1,
                  r
              }
              return ""
            };
            return r = r.replace(/\[sound:(.+?)\]/g, a),
              r = r.replace(/\[\[type:.+?\]\]/g, ""),
              r += n
          },
          showDesc: function() {
            return (0,
              a["default"])("#shortdesc").hide(),
              (0,
                a["default"])("#fulldesc").show(),
              !1
          },
          bigger: function() {
            this._adjSize(.1)
          },
          smaller: function() {
            this._adjSize(-.1)
          },
          _adjSize: function(t) {
            this.zoom += t,
              this._resizeFonts()
          },
          _resizeFonts: function() {
            (0,
              a["default"])("#qa, #qa *").css("zoom", this.zoom)
          }
        };
        (0,
          a["default"])(function() {
          return (0,
            a["default"])("#studynow").focus()
        }),
          t["default"] = o
      }),
      require.register("tools.js", function(t, e, r) {
        "use strict";
        function n(t) {
          return t && t.__esModule ? t : {
            "default": t
          }
        }
        Object.defineProperty(t, "__esModule", {
          value: !0
        });
        var i = e("jquery")
          , a = n(i)
          , o = {
          renderTimestamps: function() {
            (0,
              a["default"])(".timestamp").each(function(t, e) {
              (0,
                a["default"])(this).text(new Date(1e3 * (0,
                a["default"])(this).text()).toLocaleDateString())
            })
          }
        };
        t["default"] = o
      }),
      require.alias("process/browser.js", "process"),
      t = require("process"),
      require.register("___globals___", function(t, e, r) {
        window.$ = e("jquery"),
          window.jQuery = e("jquery")
      })
  }(),
  require("___globals___");
