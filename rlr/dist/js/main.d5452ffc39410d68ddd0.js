!function(e){function t(t){for(var n,i,c=t[0],s=t[1],l=t[2],f=0,p=[];f<c.length;f++)i=c[f],Object.prototype.hasOwnProperty.call(a,i)&&a[i]&&p.push(a[i][0]),a[i]=0;for(n in s)Object.prototype.hasOwnProperty.call(s,n)&&(e[n]=s[n]);for(u&&u(t);p.length;)p.shift()();return o.push.apply(o,l||[]),r()}function r(){for(var e,t=0;t<o.length;t++){for(var r=o[t],n=!0,c=1;c<r.length;c++){var s=r[c];0!==a[s]&&(n=!1)}n&&(o.splice(t--,1),e=i(i.s=r[0]))}return e}var n={},a={0:0},o=[];function i(t){if(n[t])return n[t].exports;var r=n[t]={i:t,l:!1,exports:{}};return e[t].call(r.exports,r,r.exports,i),r.l=!0,r.exports}i.m=e,i.c=n,i.d=function(e,t,r){i.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},i.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},i.t=function(e,t){if(1&t&&(e=i(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(i.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)i.d(r,n,function(t){return e[t]}.bind(null,n));return r},i.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return i.d(t,"a",t),t},i.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},i.p="";var c=window.webpackJsonp=window.webpackJsonp||[],s=c.push.bind(c);c.push=t,c=c.slice();for(var l=0;l<c.length;l++)t(c[l]);var u=s;o.push([44,1]),r()}({41:function(e,t,r){},42:function(e,t,r){e.exports={footer:"_3VgU3tP7z49cwgMoqOCbUB"}},44:function(e,t,r){"use strict";r.r(t);var n=r(0),a=r.n(n),o=r(7),i=r.n(o),c=r(23),s=(r(33),r(9)),l=r(22),u=r.n(l),f=r(12),p=r.n(f),d=r(13),h=r.n(d),g=r(8),m=r.n(g),v=r(14),y=r.n(v),b=r(10),O=r.n(b),w=r(15),k=r.n(w),j=r(6),E=r.n(j),S=r(17),P=r.n(S),D=r(3);var x=function(e,t,r){var n=null,a=!1;try{n=JSON.parse(t),a=!0}catch(e){n=t}this.response=n,this.message=e,this.status=r,D.b.error("Serveris atgrieza kļūdu: ".concat(this.toString()),{autoClose:1e4}),this.toString=function(){return"".concat(this.message,"\nResponse:\n").concat(a?JSON.stringify(this.response,null,2):this.response)}},C="//edgars.toolforge.org/rlr/api.php";var N=function(e,t){var r="";"http"==e.substr(0,4)?r=e:C!==e.substr(0,C.length)&&(r=C+e);var n,a=void 0===t||0===Object.keys(t).length?"":(n=t,"?"+Object.keys(n).reduce((function(e,t){return e.push(t+"="+encodeURIComponent(n[t])),e}),[]).join("&"));return"".concat(r).concat(a)};function _(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function R(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?_(Object(r),!0).forEach((function(t){E()(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):_(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}var A=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},r={},n={},a=["post","put","patch"],o=R({},r,{},t,{headers:R({},n,{},t.headers)});o.method=o.method.toLowerCase();var i=o.body instanceof File;o.body&&"object"===P()(o.body)&&!i&&a.indexOf(o.method)>-1&&(o.body=JSON.stringify(o.body));var c=N(e,o.params),s=null;return fetch(c,o).then((function(e){return(s=e).status<200||s.status>=300?s.text():s.json()})).then((function(e){if(s.status<200||s.status>=300)throw e;return e})).catch((function(e){throw s?new x("Request failed with status ".concat(s.status,"."),e,s.status):new x(e.toString(),null,"REQUEST_FAILED")}))},L=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};return A(e,{params:t,method:"get"})},I=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};return A(e,{body:t,method:"post"})},H={mediawiki:{linksHere:function(e){return L("https://lv.wikipedia.org/w/api.php",{action:"query",origin:"*",format:"json",prop:"linkshere",titles:e,lhprop:"title",lhnamespace:"0",lhlimit:"max",formatversion:"2"})},openSearch:function(e,t){return L("https://".concat(e,".wikipedia.org/w/api.php"),{action:"opensearch",origin:"*",format:"json",formatversion:2,search:t,namespace:0,limit:10,suggest:!0})}},tool:{nextArticle:function(){return L("",{action:"next_suggestion"})},saveArticle:function(e,t){return I("",{action:"save_action",title:e,message:t})},redirect:function(e,t){return I("",{action:"redirect",from:e,to:t})}}};function F(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(e){return!1}}var M=r(40);r(41);var T=function(e){return e.replace(/ /g,"_")},z=function(e){return e.replace(/_/g," ")},U=function(e,t){var r=new M({timeout:1}),n=r.main(e,t);return r.prettyHtml(n)},J=function(e){return a.a.createElement("a",{href:"https://lv.wikipedia.org/wiki/".concat(T(e)),target:"_blank",rel:"noopener noreferrer"},e)},W=function(e){k()(n,e);var t,r=(t=n,function(){var e,r=O()(t);if(F()){var n=O()(this).constructor;e=Reflect.construct(r,arguments,n)}else e=r.apply(this,arguments);return y()(this,e)});function n(e){var t;return p()(this,n),t=r.call(this,e),E()(m()(t),"init",(function(){t.setState({loading:!0}),H.tool.nextArticle().then((function(e){var r=e.suggestion,n=e.targets;r=z(r),n=n.map((function(e){return z(e)})),t.setState({suggestion:r,targets:n}),t.setLinks(r),t.setSearch(r)})).catch((function(e){t.setState({error:!0})})).finally((function(){t.setState({loading:!1})}))})),E()(m()(t),"setLinks",(function(e){H.mediawiki.linksHere(e).then((function(e){var r=e.query.pages[0];if("missing"in r)if("linkshere"in r){var n=r.linkshere.map((function(e){return e.title}));t.setState({whatLinksHere:n})}else t.saveData("no links",!1);else t.saveData("not redlink",!1)}))})),E()(m()(t),"setSearch",(function(e){H.mediawiki.openSearch("lv",e).then((function(e){var r=u()(e,4),n=(r[0],r[1]);r[2],r[3];t.setState({search:n})}))})),E()(m()(t),"saveData",(function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",r=!(arguments.length>1&&void 0!==arguments[1])||arguments[1];t.setState({saving:!0}),H.tool.saveArticle(t.state.suggestion,e).then((function(e){"ok"===e.status&&r&&r.success('Darbība rakstam "'.concat(t.state.suggestion,'" saglabāta'),{autoClose:3e3}),"ok"!==e.status&&(console.log(e),r.warn("Neveiksmīga saglabāšana: ".concat(e.message),{autoClose:7500}))})).catch((function(e){})).finally((function(){t.setState({saving:!1},(function(){t.init()}))}))})),E()(m()(t),"createRedirect",(function(e){console.log(e),t.setState({saving:!0}),H.tool.redirect(t.state.suggestion,e).then((function(e){"ok"===e.status&&D.b&&D.b.success('Darbība rakstam "'.concat(t.state.suggestion,'" saglabāta'),{autoClose:3e3}),"ok"!==e.status&&D.b.warn("Neveiksmīga saglabāšana: ".concat(e.message),{autoClose:7500})})).catch((function(e){})).finally((function(){t.setState({saving:!1},(function(){t.init()}))}))})),t.state={suggestion:null,targets:[],search:[],loading:!1,error:!1,saving:!1,previews:{},whatLinksHere:[],saves:0},t}return h()(n,[{key:"componentDidMount",value:function(){this.init()}},{key:"render",value:function(){var e=this,t=this.state,r=t.suggestion,n=t.targets,o=t.loading,i=(t.error,t.saving,t.whatLinksHere),c=t.search;return null===r||o?"":a.a.createElement("div",{style:{display:"flex",flexDirection:"row"}},a.a.createElement("div",{style:{display:"flex",flexDirection:"column",minWidth:"250px"}},a.a.createElement("button",{type:"button",className:"btn btn-outline-primary",onClick:this.init},"Cits kandidāts"),a.a.createElement("button",{type:"button",className:"btn btn-outline-success",onClick:function(){return e.saveData("done")}},"Salabots"),a.a.createElement("button",{type:"button",className:"btn btn-outline-danger",onClick:function(){return e.saveData("delete")}},"Nav jālabo")),a.a.createElement("div",{style:{marginLeft:"3vw"}},a.a.createElement("h3",null,'"',a.a.createElement("a",{href:"https://lv.wikipedia.org/wiki/Special:WhatLinksHere/".concat(T(r)),target:"_blank",rel:"noopener noreferrer"},z(r)),'"'," ",a.a.createElement("small",null,"(",a.a.createElement("a",{href:"https://lv.wikipedia.org/w/index.php?title=".concat(T(r),"&action=edit"),target:"_blank",rel:"noopener noreferrer"},"labot"),")")),a.a.createElement("h4",null,"Kandidātlapas"),a.a.createElement("div",{style:{display:"flex",flexDirection:"row",flexWrap:"wrap"}},n.map((function(t){return a.a.createElement("div",{className:"card",key:t},a.a.createElement("div",{className:"card-body"},a.a.createElement("h5",{className:"card-title"},J(t)),a.a.createElement("div",{dangerouslySetInnerHTML:{__html:U(t,r)}}),a.a.createElement("a",{href:"#",className:"card-link",onClick:function(){return e.createRedirect(t)}},"Izveidot pāradresāciju")))}))),c.length>0&&a.a.createElement(a.a.Fragment,null,a.a.createElement("h4",null,"Meklēšanas rezultāti"),a.a.createElement("div",{style:{display:"flex",flexDirection:"row",flexWrap:"wrap"}},c.map((function(t){return a.a.createElement("div",{className:"card",key:t},a.a.createElement("div",{className:"card-body"},a.a.createElement("h5",{className:"card-title"},J(t)),a.a.createElement("div",{dangerouslySetInnerHTML:{__html:U(t,r)}}),a.a.createElement("a",{href:"#",className:"card-link",onClick:function(){return e.createRedirect(t)}},"Izveidot pāradresāciju")))})))),i.length>0&&a.a.createElement(a.a.Fragment,null,a.a.createElement("h4",null,"Saites uz šo lapu"),a.a.createElement("ul",null,i.map((function(e){return a.a.createElement("li",{key:e},a.a.createElement("a",{href:"https://lv.wikipedia.org/wiki/".concat(T(e)),target:"_blank",rel:"noopener noreferrer"},e)," ","(",a.a.createElement("a",{href:"https://lv.wikipedia.org/w/index.php?title=".concat(T(e),"&action=edit"),target:"_blank",rel:"noopener noreferrer"},"labot"),")")}))))))}}]),n}(n.Component),q=function(){return a.a.createElement("div",{style:{padding:"2vh"}},a.a.createElement(W,null))},B=r(1),V=r.n(B);function K(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(e){return!1}}var Q=function(e){k()(n,e);var t,r=(t=n,function(){var e,r=O()(t);if(K()){var n=O()(this).constructor;e=Reflect.construct(r,arguments,n)}else e=r.apply(this,arguments);return y()(this,e)});function n(e){var t;return p()(this,n),(t=r.call(this,e)).state={error:null,errorInfo:null},t}return h()(n,[{key:"componentDidCatch",value:function(e,t){this.setState({error:e,errorInfo:t})}},{key:"render",value:function(){return this.state.errorInfo?a.a.createElement("div",{className:"bodyWrapper"},a.a.createElement("h2",null,"Notika kļūda, ielādējot lapu!"),a.a.createElement("details",{style:{whiteSpace:"pre-wrap"}},this.state.error&&this.state.error.toString(),a.a.createElement("br",null),this.state.errorInfo.componentStack)):this.props.children}}]),n}(a.a.Component);function G(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function X(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?G(Object(r),!0).forEach((function(t){E()(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):G(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}Q.propTypes={children:V.a.node};var Y=Object(s.b)({name:"app",initialState:{isAuth:!1,user:null,articles:null,lastArticleDate:null},reducers:{setOverview:{reducer:function(e,t){var r=t.payload;return X({},e,{articles:r.count,lastArticleDate:r.date})},prepare:function(e,t){return{payload:{count:e,date:t}}}},setAuthUser:{reducer:function(e,t){return X({},e,{user:t.payload.user,isAuth:!0})},prepare:function(e){return{payload:{user:e}}}},logout:{reducer:function(e){return X({},e,{user:null,isAuth:!1})}}}}),Z=Y.actions,$=(Z.setOverview,Z.setAuthUser,Z.logout,Y.reducer),ee=(r(42),r(43),function(){return a.a.createElement(a.a.Fragment,null,a.a.createElement(Q,null,a.a.createElement(q,null)),a.a.createElement(D.a,{position:"bottom-right",autoClose:2500,hideProgressBar:!1,newestOnTop:!1,closeOnClick:!0,rtl:!1,pauseOnVisibilityChange:!0,draggable:!1,pauseOnHover:!0}))}),te=r(5);function re(e,t){var r=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r.push.apply(r,n)}return r}function ne(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?re(Object(r),!0).forEach((function(t){E()(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):re(Object(r)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}var ae=Object(s.b)({name:"article",initialState:{id:0,title:null,fetching:!1,saving:!1,fromList:!1},reducers:{getNextArticle:{reducer:function(e,t){var r=t.payload;return ne({},e,{id:r.id,title:r.title,fetching:!1})},prepare:function(e,t){return{payload:{id:e,title:t}}}},setFetchStart:{reducer:function(e){return ne({},e,{fetching:!0})}},setComingFromArticleList:{reducer:function(e,t){return ne({},e,{fromList:t.payload.newState})},prepare:function(e){return{payload:{newState:e}}}},setSaveProcess:{reducer:function(e){return ne({},e,{saving:!e.saving})}},resetCounter:{reducer:function(e){return{id:0,title:null,fetching:!1,saving:!1,fromList:!1}}},setTitle:{reducer:function(e,t){return ne({},e,{title:t.payload.title})},prepare:function(e){return{payload:{title:e}}}}}}),oe=ae.actions,ie=(oe.getNextArticle,oe.setFetchStart,oe.setSaveProcess,oe.resetCounter,oe.setTitle,oe.setComingFromArticleList,ae.reducer),ce=Object(te.c)({article:ie,app:$}),se=Object(s.a)({reducer:ce});i.a.render(a.a.createElement(c.a,{store:se},a.a.createElement(ee,null)),document.getElementById("app"))}});
//# sourceMappingURL=main.d5452ffc39410d68ddd0.js.map