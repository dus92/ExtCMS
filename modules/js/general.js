function onGetPageContentSuccess(data){      
    //$("#window_center").html(data);
    var href = window.location.pathname.substr(window.location.pathname.lastIndexOf('/')+1);
    $("#main_content").css('text-align', 'justify');
    if(href === 'gallery-video'){
        $("#main_content").html('<div id="main_title">Видео</div><br />');
        $("#main_content").append('<div class="video"><object width="500" height="375"><param name="movie" value="//www.youtube.com/v/5FqltmyXLsM?version=3&amp;hl=ru_RU"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="//www.youtube.com/v/5FqltmyXLsM?version=3&amp;hl=ru_RU" type="application/x-shockwave-flash" width="500" height="375" allowscriptaccess="always" allowfullscreen="true"></embed></object></div>');
        $("#main_content").append('<div class="video" style="margin: 30px auto;"><object width="500" height="375"><param name="movie" value="//www.youtube.com/v/FFtMwbKrdOI?version=3&amp;hl=ru_RU"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="//www.youtube.com/v/FFtMwbKrdOI?version=3&amp;hl=ru_RU" type="application/x-shockwave-flash" width="500" height="375" allowscriptaccess="always" allowfullscreen="true"></embed></object></div>');             
    }
    else
        $("#main_content").html(data).hide().fadeIn('normal', function(){
            if(href === 'gallery_22-gallery-photo.html'){
                carouselInit(1);    
            }                
            else if(href === 'consumers_12-cooperation.html'){
                carouselInit(0);    
            }
        });
    
    
    //production brands
    setTimeout(function(){    
        changeBrandImg();        
    },500);
    
    changeImgSize();    
}

function nav_click(href){
    $("#window_center").ajaxError(
        function(e, xhr, settings, exept){
            //alert("При выполнении ajax-запроса страницы " + settings.url + " произошла ошибка.");
            //if (!$.browser.msie)
              //  window.location.reload();
        }
    );
    //$.get(href + "&n_ajax",onGetPageContentSuccess);    
    if(href !== 'index.php?module=iblocks&contid=gallery-video'){
        $("#main_content").css('text-align', 'center');
        $("#main_content").html('<img src="skins/default/img/loader.gif" width="70" />');
    }
    $.ajax({
       url: href + "&n_ajax",
       dataType: 'html',
       type: 'GET',
       success: function(data){
            onGetPageContentSuccess(data);
       },
       error: function(data){
           console.log(data);  
       }
    });
    return false;
}

function changeUrl(url){
    var res_url;
    var myRe_cont = /\?module=iblocks&contid=(.*)/ig;
    var myRe_item = /\?module=iblocks&action=show&item=(.*)/ig;
    var myRe_iblock = /\?module=iblocks&ibid=(.*)/ig;
    var myRe_module = /\?module=(.*)/ig;
    var myRe_users = /\?module=user.list/ig;
    var myRe_index = /\?module=index/ig;
    var myRe_register = /\?module=user.profile&act=register/ig;                  
             	 
    var myArray = new Object();         
    myArray.cont = myRe_cont.exec(url);
    myArray.item = myRe_item.exec(url);
    myArray.iblock = myRe_iblock.exec(url);
    myArray.module = myRe_module.exec(url);
    myArray.users = myRe_users.exec(url);
    myArray.index = myRe_index.exec(url);
    myArray.register = myRe_register.exec(url);
                         
    if (myArray.cont != null)
        res_url = myArray.cont[1];
        else if (myArray.item != null)
            res_url = 'static_'+myArray.item[1]+'-page.html';
            else if (myArray.iblock != null)
                res_url = 'info_'+myArray.iblock[1];
                else if (myArray.users != null)
                    res_url = 'users';
                    else if (myArray.index != null)
                        res_url = './';
                        else if (myArray.register != null)
                            res_url = 'register';
                            else if (myArray.module != null)
                                res_url = myArray.module[1];
                                else
                                    res_url = url;                            
    return res_url;   
}

// функция для ссылок обрабатывается при клике на ссылку
var state = new Object();
function handlerAnchors(title, href) {
    // заполним хранилище
    state = {
        title: title,
        url: href            
    }
    // заносим ссылку в историю        
    var item = '';
    //alert(state.url);
    if(/user\.profile|user\.panel|filesdb|feedback|cabinet|shop\.basket|instagram/.test(state.url)){
        item = state.url; 
        state.url = 'index.php?module=' + state.url;        
    }
    else if (/\.html/.test(state.url)){
        item = state.url; 
        state.url = 'index.php?module=iblocks&action=show&item='+state.url.substring(state.url.indexOf('_')+1, state.url.indexOf('-'));            
    }
    else if(state.url == './'){
        item = state.url;
        //state.url = 'index.php?module=index';
        state.url = 'index.php?module=index';
    }
    else if(/.+[^\.]/.test(state.url)){
        item = state.url; 
        state.url = 'index.php?module=iblocks&contid='+state.url;        
    }
    nav_click(state.url);                              
    state.url = item=='' ? changeUrl(state.url) : item;
    change_content(state.url);                   
    history.pushState( state, state.title, state.url );                 
    // не даем выполнить действие по умолчанию
    return false;
}

//window.onload = function() {              
//    // функция для ссылок обрабатывается при клике на ссылку
//    function handlerAnchors() {
//        // заполним хранилище чем нибудь
//        var state = {
//            title: this.getAttribute( "title" ),
//            url: this.getAttribute( "href", 2 ) // двоечка нужна для ИЕ6-7
//        }
//        // заносим ссылку в историю
//        history.pushState( state, state.title, state.url );
//        nav_click(state.url);                                        
//        // не даем выполнить действие по умолчанию
//        return false;
//    }
//    // ищем все ссылки
//    var anchors = document.getElementsByTagName( 'a' );
//    // вешаем события на все ссылки в нашем документе
//    for( var i = 0; i < anchors.length; i++ ) {
//        anchors[ i ].onclick = handlerAnchors;
//    }
//    // вешаем событие на popstate которое срабатывает
//    // при нажатии back/forward в браузере
//    window.onpopstate = function( e ) {
//       nav_click(window.location.href); 
//    }
//}

jQuery(document).ready(function() {

    // ищем все ссылки
    // вешаем события на все ссылки в нашем документе
    var pathname = window.location.pathname.substr(window.location.pathname.lastIndexOf('/')+1);
    change_content(pathname);
    
    $("a").click(function(){        
        if (!$(this).attr('onclick') && $(this).attr('href')!='./admin.php' && $(this).attr('target')!='_blank' && $(this).attr('href').substr(0, 8) !== 'download' && $(this).attr('href') !== '#'){            
            //alert($(this).attr('href').substr($(this).attr('href').indexOf('?')));
            handlerAnchors($(this).attr('title'), $(this).attr('href'));
            return false;
        }                  
    });
    
    $(".art_tlink").click(function(){
     //  alert('dsfsdf');
       return false; 
    });
    // вешаем событие на popstate которое срабатывает
    // при нажатии back/forward в браузере
    window.onpopstate = function( e ) {
       var myRe_cont = /.*/gi;
       var myRe_iblock = /info_.*/gi;
       var myRe_register = /register/gi;
       var myRe_item = /[^\._]+_([^\._]+)-[^\._]+\.html/gi;
       var modules = new Array('/', '/users', '/user.profile', '/filesdb', '/feedback', '/cabinet', '/instagram');
       var url ='';       
       var path = window.location.pathname.substr(window.location.pathname.lastIndexOf('/'));
       var pathname = window.location.pathname.substr(window.location.pathname.lastIndexOf('/')+1);
       change_content(pathname);
       
       for (var i=0; i<modules.length; i++){
         switch (path){
            case modules[0]:
                url = 'index.php?module=index';
                break;
            case modules[1]:
                url = 'index.php?module=user.list';
                break;
            case modules[i]:
                url = 'index.php?module=' + path.substr(1);
                break;
         }         
       }                
       if (url == ''){
            if (myRe_iblock.test(path))                                     
                url = 'index.php?module=iblocks&ibid=' + path.substr(1);
                else if(myRe_register.test(path))
                    url = 'index.php?module=user.profile&act=register';
                    else if (myRe_item.test(path))
                        url = 'index.php?module=iblocks&action=show&item='+path.substring(path.indexOf('_')+1, path.indexOf('-'));
                        else
                            url = 'index.php?module=iblocks&contid=' + path.substr(1);
       }                                            
       nav_click(url);
    }        
});

function change_content(href){
    $(".leftmenu_subitems").hide();
    $("#news").show();
    $("#main_module").width('705px');    
    
    switch(href){
        case 'index.php?module=index':
        case '/index.php':
        case './':
        case '':
        case 'about_2-mission.html':
        case 'about_3-history.html':
        case 'about_4-achievements.html':
        case 'about_5-contacts.html':
        case 'about_41-vacancies.html':
            //$("#home").find('.topmenu_item').addClass('active');
            $("#leftmenu_about").show();
            break;
        case 'gallery':
        case 'smi':
        case 'gallery-photo':
        case 'gallery_100-video.html':
        case 'gallery_22-gallery-photo.html':
        case 'gallery-video':
            $("#leftmenu_gallery").show();
            break;
            
        case 'static_1-production.html':
        case 'rep':
            $("#leftmenu_production").show();
            break;
        case 'partners':
        case 'partners_10-cooperation.html':
        case 'partners_11-documents.html':
            $("#leftmenu_partners").show();
            break;
        case 'consumers':
        case 'consumers_12-cooperation.html':
        case 'consumers_13-about-butter.html':
        case 'consumers_14-about-cheese.html':
        case 'consumers_15-right-choise.html':
        case 'consumers_16-its-interesting.html':
            $("#leftmenu_consumers").show();
            break;
        case 'news':
            $("#news").hide();
            $("#main_module").width('970px');
            $("#leftmenu_about").show();
            break;
    }
    
    if(href.substr(0,4) === 'news'){
        $("#leftmenu_about").show();
    }
    
    if(href == 'about_5-contacts.html'){
      setTimeout(function(){
        //init();
        ymaps.ready(init);
      }, 500);
    }
    
    if(href.substr(0, 10) == 'production'){
        $("#leftmenu_production").show();
    }
    
    if(href.substr(0, 9) == 'consumers'){
        $("#leftmenu_consumers").show();
    }
    
    if(href === 'gallery-video'){
        $("#main_content").html('<div id="main_title">Видео</div><br />');
        $("#main_content").append('<div class="video"><object width="500" height="375"><param name="movie" value="//www.youtube.com/v/5FqltmyXLsM?version=3&amp;hl=ru_RU"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="//www.youtube.com/v/5FqltmyXLsM?version=3&amp;hl=ru_RU" type="application/x-shockwave-flash" width="500" height="375" allowscriptaccess="always" allowfullscreen="true"></embed></object></div>');
        $("#main_content").append('<div class="video" style="margin: 30px auto;"><object width="500" height="375"><param name="movie" value="//www.youtube.com/v/FFtMwbKrdOI?version=3&amp;hl=ru_RU"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="//www.youtube.com/v/FFtMwbKrdOI?version=3&amp;hl=ru_RU" type="application/x-shockwave-flash" width="500" height="375" allowscriptaccess="always" allowfullscreen="true"></embed></object></div>');     
    }
    
    if(href === 'gallery_22-gallery-photo.html'){
        carouselInit(1);    
    }                
    else if(href === 'consumers_12-cooperation.html'){
        carouselInit(0);    
    }
 
    //else if(href == 'index.php?module=index' || href == '/index.php' || href == '/' ){

}