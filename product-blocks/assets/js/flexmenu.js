!function(e){"function"==typeof define&&define.amd?define(["jquery"],e):e(jQuery)}((function(e){var i,n=e(window).width(),l=e(window).height(),o=[];e(window).resize((function(){clearTimeout(i),i=setTimeout((function(){e(window).width()===n&&e(window).height()===l||(e(o).each((function(){e(this).flexMenu({undo:!0}).flexMenu(this.options)})),n=e(window).width(),l=e(window).height())}),200)})),e.fn.flexMenu=function(i){var n,l=e.extend({threshold:2,cutoff:2,linkText:"More",linkTitle:"View More",linkTextAll:"Menu",linkTitleAll:"Open/Close Menu",shouldApply:function(){return!0},showOnHover:!0,popupAbsolute:!0,popupClass:"",undo:!1},i);return this.options=l,(n=e.inArray(this,o))>=0?o.splice(n,1):o.push(this),this.each((function(){var i,n,o,t,f,u,s=e(this),d=s.find("> li"),r=d.first(),p=d.last(),a=d.length,h=Math.floor(r.offset().top),c=Math.floor(r.outerHeight(!0)),M=!1;function w(e){return Math.ceil(e.offset().top)>=h+c}if(w(p)&&a>l.threshold&&!l.undo&&s.is(":visible")&&l.shouldApply()){var v=e('<ul class="flexMenu-popup" style="display:none;'+(l.popupAbsolute?" position: absolute;":"")+'"></ul>');for(v.addClass(l.popupClass),u=a;u>1;u--){if(n=w(i=s.find("> li:last-child")),u-1<=l.cutoff){e(s.children().get().reverse()).appendTo(v),M=!0;break}if(!n)break;i.appendTo(v)}M?s.append('<li class="flexMenu-viewMore flexMenu-allInPopup"><a href="#" title="'+l.linkTitleAll+'">'+l.linkTextAll+"</a></li>"):s.append('<li class="filter-item flexMenu-viewMore"><a href="#" title="'+l.linkTitle+'">'+l.linkText+"</a></li>"),w(o=s.find("> li.flexMenu-viewMore"))&&s.find("> li:nth-last-child(2)").appendTo(v),v.children().each((function(e,i){v.prepend(i)})),o.append(v),s.find("> li.flexMenu-viewMore > a").click((function(i){var n;n=o,e("li.flexMenu-viewMore.active").not(n).removeClass("active").find("> ul").hide(),v.toggle(),o.toggleClass("active"),i.preventDefault()})),l.showOnHover&&"undefined"!=typeof Modernizr&&!Modernizr.touch&&o.hover((function(){v.show(),e(this).addClass("active")}),(function(){v.hide(),e(this).removeClass("active")}))}else if(l.undo&&s.find("ul.flexMenu-popup")){for(t=(f=s.find("ul.flexMenu-popup")).find("li").length,u=1;u<=t;u++)f.find("> li:first-child").appendTo(s);f.remove(),s.find("> li.flexMenu-viewMore").remove()}}))}}));