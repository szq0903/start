(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-home-homeTem-news"],{"4a71":function(t,e,i){"use strict";i.r(e);var s=i("73d2"),n=i("4e8b");for(var a in n)"default"!==a&&function(t){i.d(e,t,function(){return n[t]})}(a);i("f145");var l=i("2877"),o=Object(l["a"])(n["default"],s["a"],s["b"],!1,null,"243fb226",null);e["default"]=o.exports},"4e8b":function(t,e,i){"use strict";i.r(e);var s=i("88d4"),n=i.n(s);for(var a in s)"default"!==a&&function(t){i.d(e,t,function(){return s[t]})}(a);e["default"]=n.a},6545:function(t,e,i){e=t.exports=i("2350")(),e.push([t.i,".news[data-v-243fb226]{margin:%?20?% 0;background:#fff;padding:%?20?% %?20?% 0}.news-content[data-v-243fb226]{color:#a9a9a9}.news-title[data-v-243fb226]{color:#000;display:inline-block;width:100%;font-size:%?32?%;font-weight:700;line-height:%?40?%;padding-bottom:%?20?%;text-align:center}.news-list[data-v-243fb226],.news-title[data-v-243fb226]{border-bottom:solid #ebebeb}.news-list[data-v-243fb226]{display:-webkit-flex;display:-ms-flexbox;display:flex;padding:%?20?% 0}.news-photo[data-v-243fb226]{width:%?200?%;height:%?200?%;display:block;margin-right:%?20?%}.news-list-right[data-v-243fb226]{width:70%;height:%?200?%}.news-list-top[data-v-243fb226]{display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-justify-content:space-between;-ms-flex-pack:justify;justify-content:space-between}.news-list-title[data-v-243fb226]{width:50%;font-size:%?28?%;line-height:%?60?%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.news-list-time[data-v-243fb226]{display:inline-block;line-height:%?60?%;font-size:%?32?%}.news-list-content[data-v-243fb226]{text-indent:2em;font-size:%?24?%;line-height:%?40?%;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:3}.news-bot[data-v-243fb226]{margin:0 %?20?%;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-justify-content:space-between;-ms-flex-pack:justify;justify-content:space-between}.news-more[data-v-243fb226]{color:#6c81a6;font-size:%?28?%;line-height:%?80?%}.news-img[data-v-243fb226]{width:%?40?%;height:%?40?%;margin:%?20?% 0}",""])},"73d2":function(t,e,i){"use strict";var s=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",{staticClass:"news"},[i("div",{staticClass:"news-content"},[i("span",{staticClass:"news-title"},[t._v("华油新闻")]),t._l(t.newsList,function(e){return i("div",{staticClass:"news-list",on:{click:function(i){i=t.$handleEvent(i),t.toNewsDetail(e)}}},[i("v-uni-image",{staticClass:"news-photo",attrs:{src:e.thumb}}),i("div",{staticClass:"news-list-right"},[i("div",{staticClass:"news-list-top"},[i("span",{staticClass:"news-list-title"},[t._v(t._s(e.title))]),i("span",{staticClass:"news-list-time"},[t._v(t._s(e.update.split(" ")[0]))])]),i("div",{staticClass:"news-list-content"},[t._v(t._s(e.info.substring(1,50)))])])],1)}),i("div",{staticClass:"news-bot"},[i("span",{staticClass:"news-more",on:{click:function(e){e=t.$handleEvent(e),t.newsMore(e)}}},[t._v("查看更多")]),i("v-uni-image",{staticClass:"news-img",attrs:{src:"/static/image/news/more.png"},on:{click:function(e){e=t.$handleEvent(e),t.newsMore(e)}}})],1)],2)])},n=[];i.d(e,"a",function(){return s}),i.d(e,"b",function(){return n})},"88d4":function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var s={data:function(){return{newsList:[]}},methods:{toNewsDetail:function(t){uni.navigateTo({url:"../../../../news/newsTem/newsDetail?id="+t.id})},newsMore:function(){uni.switchTab({url:"../../../../news/news"})},getNewsList:function(){var t=this,e=2,i="/index/Index/indexArtList/type/1/cid/4/limit/0,"+e+"/order";this.http(i).then(function(e){1==e.status?t.newsList=e.data.list:t.showToast("网络请求失败")})}},created:function(){this.getNewsList()}};e.default=s},ddf9:function(t,e,i){var s=i("6545");"string"===typeof s&&(s=[[t.i,s,""]]),s.locals&&(t.exports=s.locals);var n=i("4f06").default;n("91060e08",s,!0,{sourceMap:!1,shadowMode:!1})},f145:function(t,e,i){"use strict";var s=i("ddf9"),n=i.n(s);n.a}}]);