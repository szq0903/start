(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-user-userInfo-myCard"],{"0829":function(t,a,i){"use strict";i.r(a);var n=i("0ac0"),e=i("4555");for(var s in e)"default"!==s&&function(t){i.d(a,t,function(){return e[t]})}(s);i("91a8");var o=i("2877"),d=Object(o["a"])(e["default"],n["a"],n["b"],!1,null,"759e5926",null);a["default"]=d.exports},"0ac0":function(t,a,i){"use strict";var n=function(){var t=this,a=t.$createElement,i=t._self._c||a;return i("div",{staticClass:"bg"},[i("div",{staticClass:"card"},[i("v-uni-image",{attrs:{src:t.myInfo.imgurl}}),i("div",{staticClass:"card-right"},[i("div",{staticClass:"name"},[i("span",[t._v("姓名:")]),i("span",[t._v(t._s(t.myInfo.name))])]),i("div",{staticClass:"phone"},[i("span",[t._v("电话:")]),i("span",[t._v(t._s(t.myInfo.phone))])]),i("div",{staticClass:"job"},[i("span",[t._v("职务:")]),i("span",[t._v(t._s(t.myInfo.job))])]),i("div",{staticClass:"add"},[i("span",[t._v("地址:")]),i("span",[t._v(t._s(t.myInfo.address))])])])],1),i("div",{staticClass:"card-btn"},[i("div",{on:{click:function(a){a=t.$handleEvent(a),t.phone(t.myInfo.phone)}}},[t._v(t._s(t.myInfo.phone)),i("v-uni-image",{attrs:{src:"../../../static/image/login/phone.png"}})],1),i("div",{on:{click:function(a){a=t.$handleEvent(a),t.share(a)}}},[t._v("分享名片"),i("v-uni-image",{attrs:{src:"../../../static/image/login/share.png"}})],1)]),i("div",{staticClass:"modification",on:{click:function(a){a=t.$handleEvent(a),t.modification(a)}}},[i("v-uni-image",{attrs:{src:"../../../static/image/login/modification.png"}})],1)])},e=[];i.d(a,"a",function(){return n}),i.d(a,"b",function(){return e})},"3f0c":function(t,a,i){a=t.exports=i("2350")(),a.push([t.i,".bg[data-v-759e5926]{position:fixed;top:0;left:0;width:100%;height:100%;padding-top:%?88?%;background:#f2f2f2}.card[data-v-759e5926]{display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-justify-content:space-between;-ms-flex-pack:justify;justify-content:space-between;background:#fff;margin:%?40?%;padding:%?40?%;padding:%?30?% %?30?% %?40?%;font-size:%?28?%;border-radius:%?20?%}.card uni-image[data-v-759e5926]{width:%?240?%;height:%?260?%;margin-top:%?20?%}.card-right[data-v-759e5926]{width:58%}.card-right span[data-v-759e5926]{margin-right:%?20?%}.job[data-v-759e5926],.name[data-v-759e5926],.phone[data-v-759e5926]{line-height:%?80?%}.add[data-v-759e5926]{line-height:%?40?%}.add span[data-v-759e5926]:first-child{width:%?64?%;height:%?40?%}.add span[data-v-759e5926]:nth-child(2){line-height:%?40?%}.card-btn[data-v-759e5926]{display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-justify-content:space-between;-ms-flex-pack:justify;justify-content:space-between;padding:0 %?40?%}.card-btn div[data-v-759e5926]{width:40%;line-height:%?70?%;background:#00b9a1;border-radius:%?30?%;padding-left:%?60?%;font-size:%?24?%;color:#fff;text-align:center;position:relative}.card-btn div uni-image[data-v-759e5926]{position:absolute;left:%?24?%;top:%?15?%;width:%?40?%;height:%?40?%}.modification[data-v-759e5926]{width:%?80?%;height:%?80?%;background:#fff;border-radius:50%;float:right;text-align:center;margin:%?120?% %?40?% 0 0}.modification uni-image[data-v-759e5926]{width:%?40?%;height:%?40?%;margin-top:%?20?%}",""])},4555:function(t,a,i){"use strict";i.r(a);var n=i("751e"),e=i.n(n);for(var s in n)"default"!==s&&function(t){i.d(a,t,function(){return n[t]})}(s);a["default"]=e.a},"751e":function(t,a,i){"use strict";Object.defineProperty(a,"__esModule",{value:!0}),a.default=void 0;var n={data:function(){return{myInfo:{imgurl:"../../../static/image/login/tou.png"}}},created:function(){this.getmyInfo()},methods:{getmyInfo:function(){var t=this,a=this.getStr("uid"),i=this.getStr("verif");if(a&&i){var n="/index/Index/card/uid/"+a+"/verif/"+i;this.http(n).then(function(a){1==a.status&&(t.myInfo=a.data)})}},modification:function(){uni.reLaunch({url:"./modification"})},share:function(){uni.reLaunch({url:"./shareCard"})},phone:function(t){console.log(t),uni.makePhoneCall({phoneNumber:t})}},onNavigationBarButtonTap:function(){uni.switchTab({url:"../user"})}};a.default=n},7578:function(t,a,i){var n=i("3f0c");"string"===typeof n&&(n=[[t.i,n,""]]),n.locals&&(t.exports=n.locals);var e=i("4f06").default;e("62a05b92",n,!0,{sourceMap:!1,shadowMode:!1})},"91a8":function(t,a,i){"use strict";var n=i("7578"),e=i.n(n);e.a}}]);