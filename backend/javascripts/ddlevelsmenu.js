var ddlevelsmenu={enableshim:true,downarrowpointer:["images/menu/arrow-down.gif",11,7],rightarrowpointer:["images/menu/arrow-right.gif",12,12],hideinterval:200,revealanimate:[true,10],httpsiframesrc:"blank.htm",topmenuids:[],topitems:{},subuls:{},lastactivesubul:{},topitemsindex:-1,ulindex:-1,hidetimers:{},shimadded:false,nonFF:!/Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent),getoffset:function(what,offsettype){return what.offsetParent?what[offsettype]+this.getoffset(what.offsetParent,offsettype):what[offsettype];},getoffsetof:function(el){el._offsets={left:this.getoffset(el,"offsetLeft"),top:this.getoffset(el,"offsetTop")};},getwindowsize:function(){this.docwidth=window.innerWidth?window.innerWidth-10:this.standardbody.clientWidth-10;this.docheight=window.innerHeight?window.innerHeight-15:this.standardbody.clientHeight-18;},gettopitemsdimensions:function(){for(var m=0;m<this.topmenuids.length;m++){var topmenuid=this.topmenuids[m];for(var i=0;i<this.topitems[topmenuid].length;i++){var header=this.topitems[topmenuid][i];var submenu=document.getElementById(header.getAttribute("rel"));try{header._dimensions={w:header.offsetWidth,h:header.offsetHeight,submenuw:submenu.offsetWidth,submenuh:submenu.offsetHeight};}catch(e){}}}},isContained:function(m,e){var e=window.event||e;var c=e.relatedTarget||(e.type=="mouseover"?e.fromElement:e.toElement);while(c&&c!=m){try{c=c.parentNode;}catch(e){c=m;}}if(c==m){return true;}else{return false;}},addpointer:function(target,imgclass,imginfo,BeforeorAfter){var pointer=document.createElement("img");pointer.src=imginfo[0];pointer.style.width=imginfo[1]+"px";pointer.style.height=imginfo[2]+"px";pointer.style.left=target.offsetWidth-imginfo[2]-2+"px";pointer.className=imgclass;if(BeforeorAfter=="before"){target.insertBefore(pointer,target.firstChild);}else{target.appendChild(pointer);}},css:function(el,targetclass,action){var needle=new RegExp("(^|\\s+)"+targetclass+"($|\\s+)","ig");if(action=="check"){return needle.test(el.className);}else if(action=="remove"){el.className=el.className.replace(needle,"");}else if(action=="add"&&!needle.test(el.className)){el.className+=" "+targetclass;}},addshimmy:function(target){var shim=!window.opera?document.createElement("iframe"):document.createElement("div");shim.className="ddiframeshim";shim.setAttribute("src",location.protocol=="https:"?this.httpsiframesrc:"about:blank");shim.setAttribute("frameborder","0");target.appendChild(shim);try{shim.style.filter="progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0)";}catch(e){}return shim;},positionshim:function(header,submenu,dir,scrollX,scrollY){if(header._istoplevel){var scrollY=window.pageYOffset?window.pageYOffset:this.standardbody.scrollTop;var topgap=header._offsets.top-scrollY;var bottomgap=scrollY+this.docheight-header._offsets.top-header._dimensions.h;if(topgap>0){this.shimmy.topshim.style.left=scrollX+"px";this.shimmy.topshim.style.top=scrollY+"px";this.shimmy.topshim.style.width="99%";this.shimmy.topshim.style.height=topgap+"px";}if(bottomgap>0){this.shimmy.bottomshim.style.left=scrollX+"px";this.shimmy.bottomshim.style.top=header._offsets.top+header._dimensions.h+"px";this.shimmy.bottomshim.style.width="99%";this.shimmy.bottomshim.style.height=bottomgap+"px";}}},hideshim:function(){this.shimmy.topshim.style.width=this.shimmy.bottomshim.style.width=0;this.shimmy.topshim.style.height=this.shimmy.bottomshim.style.height=0;},buildmenu:function(mainmenuid,header,submenu,submenupos,istoplevel,dir){header._master=mainmenuid;header._pos=submenupos;header._istoplevel=istoplevel;if(istoplevel){this.addEvent(header,function(e){ddlevelsmenu.hidemenu(ddlevelsmenu.subuls[this._master][parseInt(this._pos)]);},"click");}this.subuls[mainmenuid][submenupos]=submenu;header._dimensions={w:header.offsetWidth,h:header.offsetHeight,submenuw:submenu.offsetWidth,submenuh:submenu.offsetHeight};this.getoffsetof(header);submenu.style.left=0;submenu.style.top=0;submenu.style.visibility="hidden";this.addEvent(header,function(e){if(!ddlevelsmenu.isContained(this,e)){var submenu=ddlevelsmenu.subuls[this._master][parseInt(this._pos)];if(this._istoplevel){ddlevelsmenu.css(this,"selected","add");clearTimeout(ddlevelsmenu.hidetimers[this._master][this._pos]);}ddlevelsmenu.getoffsetof(header);var scrollX=window.pageXOffset?window.pageXOffset:ddlevelsmenu.standardbody.scrollLeft;var scrollY=window.pageYOffset?window.pageYOffset:ddlevelsmenu.standardbody.scrollTop;var submenurightedge=this._offsets.left+this._dimensions.submenuw+(this._istoplevel&&dir=="topbar"?0:this._dimensions.w);var submenubottomedge=this._offsets.top+this._dimensions.submenuh;var menuleft=this._istoplevel?this._offsets.left+(dir=="sidebar"?this._dimensions.w:0):this._dimensions.w;if(submenurightedge-scrollX>ddlevelsmenu.docwidth){menuleft+=-this._dimensions.submenuw+(this._istoplevel&&dir=="topbar"?this._dimensions.w:-this._dimensions.w);}submenu.style.left=menuleft+"px";var menutop=this._istoplevel?this._offsets.top+(dir=="sidebar"?0:this._dimensions.h):this.offsetTop;if(submenubottomedge-scrollY>ddlevelsmenu.docheight){if(this._dimensions.submenuh<this._offsets.top+(dir=="sidebar"?this._dimensions.h:0)-scrollY){menutop+=-this._dimensions.submenuh+(this._istoplevel&&dir=="topbar"?-this._dimensions.h:this._dimensions.h);}else{menutop+=-(this._offsets.top-scrollY)+(this._istoplevel&&dir=="topbar"?-this._dimensions.h:0);}}submenu.style.top=menutop+"px";if(ddlevelsmenu.enableshim&&(ddlevelsmenu.revealanimate[0]==false||ddlevelsmenu.nonFF)){ddlevelsmenu.positionshim(header,submenu,dir,scrollX,scrollY);}else{submenu.FFscrollInfo={x:scrollX,y:scrollY};}ddlevelsmenu.showmenu(header,submenu,dir);}},"mouseover");this.addEvent(header,function(e){var submenu=ddlevelsmenu.subuls[this._master][parseInt(this._pos)];if(this._istoplevel){if(!ddlevelsmenu.isContained(this,e)&&!ddlevelsmenu.isContained(submenu,e)){ddlevelsmenu.hidemenu(submenu);}}else if(!this._istoplevel&&!ddlevelsmenu.isContained(this,e)){ddlevelsmenu.hidemenu(submenu);}},"mouseout");},showmenu:function(header,submenu,dir){if(this.revealanimate[0]){submenu._curanimatepoint=0;var endpoint=header._istoplevel&&dir=="topbar"?header._dimensions.submenuh:header._dimensions.submenuw;submenu.style.width=submenu.style.height=0;submenu.style.overflow="hidden";submenu.style.visibility="visible";clearTimeout(submenu._animatetimer);submenu._animatetimer=setInterval(function(){ddlevelsmenu.revealmenu(header,submenu,endpoint,dir);},10);}else{submenu.style.visibility="visible";}},revealmenu:function(header,submenu,endpoint,dir){if(submenu._curanimatepoint<endpoint){if(submenu._curanimatepoint==0){submenu.style[header._istoplevel&&dir=="topbar"?"width":"height"]="auto";}submenu.style[header._istoplevel&&dir=="topbar"?"height":"width"]=submenu._curanimatepoint+"px";}else{submenu.style[header._istoplevel&&dir=="topbar"?"height":"width"]=endpoint+"px";if(this.enableshim&&submenu.FFscrollInfo){this.positionshim(header,submenu,dir,submenu.FFscrollInfo.x,submenu.FFscrollInfo.y);}submenu.style[header._istoplevel&&dir=="topbar"?"height":"width"]="auto";submenu.style.overflow="visible";clearInterval(submenu._animatetimer);}submenu._curanimatepoint=submenu._curanimatepoint+Math.round((endpoint-submenu._curanimatepoint)/this.revealanimate[1])+1;},hidemenu:function(submenu){if(typeof submenu._pos!="undefined"){this.css(this.topitems[submenu._master][parseInt(submenu._pos)],"selected","remove");if(this.enableshim){this.hideshim();}}clearTimeout(submenu._animatetimer);submenu.style.left=0;submenu.style.top="-1000px";submenu.style.visibility="hidden";},addEvent:function(target,functionref,tasktype){if(target.addEventListener){target.addEventListener(tasktype,functionref,false);}else if(target.attachEvent){target.attachEvent("on"+tasktype,function(){return functionref.call(target,window.event);});}},init:function(mainmenuid,dir){this.standardbody=document.compatMode=="CSS1Compat"?document.documentElement:document.body;this.topitemsindex=-1;this.ulindex=-1;this.topmenuids.push(mainmenuid);this.topitems[mainmenuid]=[];this.subuls[mainmenuid]=[];this.hidetimers[mainmenuid]=[];if(this.enableshim&&!this.shimadded){this.shimmy={};this.shimmy.topshim=this.addshimmy(document.body);this.shimmy.bottomshim=this.addshimmy(document.body);this.shimadded=true;}var menubar=document.getElementById(mainmenuid);var alllinks=menubar.getElementsByTagName("a");this.getwindowsize();for(var i=0;i<alllinks.length;i++){try{if(alllinks[i].getAttribute("rel")){this.topitemsindex++;this.ulindex++;var menuitem=alllinks[i];this.topitems[mainmenuid][this.topitemsindex]=menuitem;var dropul=document.getElementById(menuitem.getAttribute("rel"));dropul.style.zIndex=2000;dropul._master=mainmenuid;dropul._pos=this.topitemsindex;this.addEvent(dropul,function(){ddlevelsmenu.hidemenu(this);},"click");var arrowpointer=dir=="sidebar"?"rightarrowpointer":"downarrowpointer";this.addpointer(menuitem,arrowpointer,this[arrowpointer],dir=="sidebar"?"before":"after");this.buildmenu(mainmenuid,menuitem,dropul,this.ulindex,true,dir);dropul.onmouseover=function(){clearTimeout(ddlevelsmenu.hidetimers[this._master][this._pos]);};this.addEvent(dropul,function(e){if(!ddlevelsmenu.isContained(this,e)&&!ddlevelsmenu.isContained(ddlevelsmenu.topitems[this._master][parseInt(this._pos)],e)){var dropul=this;if(ddlevelsmenu.enableshim){ddlevelsmenu.hideshim();}ddlevelsmenu.hidetimers[this._master][this._pos]=setTimeout(function(){ddlevelsmenu.hidemenu(dropul);},ddlevelsmenu.hideinterval);}},"mouseout");var subuls=dropul.getElementsByTagName("ul");for(var c=0;c<subuls.length;c++){this.ulindex++;var parentli=subuls[c].parentNode;this.addpointer(parentli.getElementsByTagName("a")[0],"rightarrowpointer",this.rightarrowpointer,"before");this.buildmenu(mainmenuid,parentli,subuls[c],this.ulindex,false,dir);}}}catch(e){}}this.addEvent(window,function(){ddlevelsmenu.getwindowsize();ddlevelsmenu.gettopitemsdimensions();},"resize");},setup:function(mainmenuid,dir){this.addEvent(window,function(){ddlevelsmenu.init(mainmenuid,dir);},"load");}};