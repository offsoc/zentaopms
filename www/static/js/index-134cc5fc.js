import{ah as t,a1 as o,q as B,ai as v,aj as U,ak as F,p as V,d as N,x as G,h as j,z as k,e as u,w as y,f as a,F as z,r as E,o as K,al as d}from"./index.js";import{u as I,a as $,b as W,l as _}from"./useSync.hook-05b49b53.js";import{_ as Y}from"./index-895034b2.js";import{l as q}from"./plugin-b76d8513.js";import{i as b}from"./icon-0cceaeda.js";const{CopyIcon:X,CutIcon:J,ClipboardOutlineIcon:Q,TrashIcon:Z,ChevronDownIcon:ee,ChevronUpIcon:te,LockOpenOutlineIcon:ne,LockClosedOutlineIcon:oe,EyeOutlineIcon:ae,EyeOffOutlineIcon:le}=b.ionicons5,{UpToTopIcon:ie,DownToBottomIcon:se,PaintBrushIcon:re,Carbon3DSoftwareIcon:ue,Carbon3DCursorIcon:ce}=b.carbon,e=I(),O=(n=3)=>({type:"divider",key:`d${n}`}),f=[{label:"\u9501\u5B9A",key:t.LOCK,icon:o(oe),fnHandle:e.setLock},{label:"\u89E3\u9501",key:t.UNLOCK,icon:o(ne),fnHandle:e.setUnLock},{label:"\u9690\u85CF",key:t.HIDE,icon:o(le),fnHandle:e.setHide},{label:"\u663E\u793A",key:t.SHOW,icon:o(ae),fnHandle:e.setShow},{type:"divider",key:"d0"},{label:"\u590D\u5236",key:t.COPY,icon:o(X),fnHandle:e.setCopy},{label:"\u526A\u5207",key:t.CUT,icon:o(J),fnHandle:e.setCut},{label:"\u7C98\u8D34",key:t.PARSE,icon:o(Q),fnHandle:e.setParse},{type:"divider",key:"d1"},{label:"\u7F6E\u9876",key:t.TOP,icon:o(ie),fnHandle:e.setTop},{label:"\u7F6E\u5E95",key:t.BOTTOM,icon:o(se),fnHandle:e.setBottom},{label:"\u4E0A\u79FB",key:t.UP,icon:o(te),fnHandle:e.setUp},{label:"\u4E0B\u79FB",key:t.DOWN,icon:o(ee),fnHandle:e.setDown},{type:"divider",key:"d2"},{label:"\u6E05\u7A7A\u526A\u8D34\u677F",key:t.CLEAR,icon:o(re),fnHandle:e.setRecordChart},{label:"\u5220\u9664",key:t.DELETE,icon:o(Z),fnHandle:e.removeComponentList}],h=[{label:"\u521B\u5EFA\u5206\u7EC4",key:t.GROUP,icon:o(ue),fnHandle:e.setGroup},{label:"\u89E3\u9664\u5206\u7EC4",key:t.UN_GROUP,icon:o(ce),fnHandle:e.setUnGroup}],de=[t.PARSE,t.CLEAR],S=(n,l)=>{if(!l)return n;const i=[];return l.forEach(c=>{i.push(...n.filter(s=>s.key===c))}),i},_e=(n,l)=>l?n.filter(i=>l.findIndex(c=>c!==i.key)!==-1):n,r=B([]),fe=(n,l,i,c,s)=>{n.stopPropagation(),n.preventDefault();let p=n.target;for(;p instanceof SVGElement;)p=p.parentNode;e.setTargetSelectChart(l&&l.id),e.setRightMenuShow(!1),e.getTargetChart.selectId.length>1?r.value=h:r.value=f,l||(r.value=S(v(r.value),de)),c&&(r.value=_e([...h,O(),...f],c)),s&&(r.value=S([...h,O(),...f],s)),i&&(r.value=i(U(v(r.value)),[...h,...f],l)),F().then(()=>{e.setMousePosition(n.clientX,n.clientY),e.setRightMenuShow(!0)})},pe=()=>(r.value=f,{menuOptions:r,defaultOptions:f,defaultMultiSelectOptions:h,handleContextMenu:fe,onClickOutSide:()=>{e.setRightMenuShow(!1)},handleMenuSelect:i=>{e.setRightMenuShow(!1);const c=r.value.filter(s=>s.key===i);r.value.forEach(s=>{if(s.key===i){if(s.fnHandle){s.fnHandle();return}c||q()}})},mousePosition:e.getMousePosition});const ye={class:"go-chart"},he={style:{overflow:"hidden",display:"flex"}},Ee=N({__name:"index",setup(n){const l=$(),i=I(),{dataSyncFetch:c}=W();l.canvasInit(i.getEditCanvas);const s=_(()=>d(()=>import("./index-9b8e0686.js"),["static/js/index-9b8e0686.js","static/js/index.js","static/css/index-d2bdc124.css"])),p=_(()=>d(()=>import("./index-94769740.js"),["static/js/index-94769740.js","static/css/index-8a1b0194.css","static/js/index.js","static/css/index-d2bdc124.css","static/js/useSync.hook-05b49b53.js","static/css/useSync.hook-e841a6d4.css","static/js/plugin-b76d8513.js","static/js/icon-0cceaeda.js","static/js/tables_list-f613fa36.js"])),H=_(()=>d(()=>import("./index-7874674a.js"),["static/js/index-7874674a.js","static/css/index-7b4df191.css","static/js/index.js","static/css/index-d2bdc124.css","static/js/icon-0cceaeda.js","static/js/useSync.hook-05b49b53.js","static/css/useSync.hook-e841a6d4.css","static/js/plugin-b76d8513.js","static/js/tables_list-f613fa36.js"])),g=_(()=>d(()=>import("./index-dc5a95cb.js"),["static/js/index-dc5a95cb.js","static/css/index-67cd4ee4.css","static/js/index.js","static/css/index-d2bdc124.css","static/js/index-05f8eb92.js","static/css/index-2c7157c7.css","static/js/useSync.hook-05b49b53.js","static/css/useSync.hook-e841a6d4.css","static/js/plugin-b76d8513.js","static/js/icon-0cceaeda.js","static/js/tables_list-f613fa36.js","static/js/index-895034b2.js","static/js/index-b06cdd26.js","static/css/index-d857b8d6.css"])),P=_(()=>d(()=>import("./index-cc36070e.js").then(function(C){return C.i}),["static/js/index-cc36070e.js","static/css/index-43b0e65d.css","static/js/index.js","static/css/index-d2bdc124.css","static/js/index-05f8eb92.js","static/css/index-2c7157c7.css","static/js/useSync.hook-05b49b53.js","static/css/useSync.hook-e841a6d4.css","static/js/plugin-b76d8513.js","static/js/icon-0cceaeda.js","static/js/tables_list-f613fa36.js"])),T=_(()=>d(()=>import("./index-1398b087.js"),["static/js/index-1398b087.js","static/css/index-ab0c5adf.css","static/js/index.js","static/css/index-d2bdc124.css","static/js/useSync.hook-05b49b53.js","static/css/useSync.hook-e841a6d4.css","static/js/plugin-b76d8513.js","static/js/icon-0cceaeda.js","static/js/tables_list-f613fa36.js","static/js/index-05f8eb92.js","static/css/index-2c7157c7.css"])),R=_(()=>d(()=>import("./index-5bc32e57.js"),["static/js/index-5bc32e57.js","static/js/useSync.hook-05b49b53.js","static/css/useSync.hook-e841a6d4.css","static/js/index.js","static/css/index-d2bdc124.css","static/js/plugin-b76d8513.js","static/js/icon-0cceaeda.js","static/js/tables_list-f613fa36.js"])),{menuOptions:w,onClickOutSide:A,mousePosition:m,handleMenuSelect:D}=pe();return G(()=>{c()}),(C,Ce)=>{const x=E("n-layout-content"),M=E("n-layout"),L=E("n-dropdown");return K(),j(z,null,[k("div",ye,[u(M,null,{default:y(()=>[u(a(Y),null,{left:y(()=>[u(a(s))]),center:y(()=>[u(a(H))]),"ri-left":y(()=>[u(a(p))]),_:1}),u(x,{"content-style":"overflow:hidden; display: flex"},{default:y(()=>[k("div",he,[u(a(P)),u(a(g))]),u(a(T))]),_:1})]),_:1})]),u(L,{placement:"bottom-start",trigger:"manual",size:"small",x:a(m).x,y:a(m).y,options:a(w),show:a(i).getRightMenuShow,"on-clickoutside":a(A),onSelect:a(D)},null,8,["x","y","options","show","on-clickoutside","onSelect"]),u(a(R))],64)}}});var me=V(Ee,[["__scopeId","data-v-f3644264"]]),be=Object.freeze(Object.defineProperty({__proto__:null,default:me},Symbol.toStringTag,{value:"Module"}));export{O as d,be as i,pe as u};