$(document).ready(function(){
   //gallery
   var play = 1;
   var id = '';   
   
   ///////////////
   //carouselInit();
        
//    setInterval(function(){
//            if (play && $("#gallery").is(':visible')){
//                var last = 0;
//                $(".gallery_item").each(function(){
//                    if ($(this).hasClass('cur_slide')){                        
//                        id = $(this).attr('id');
//                        $(this).fadeOut('normal');
//                        $(this).removeClass('cur_slide');
//                        if(!$(this).hasClass('last_item'))
//                            $(this).prev().addClass('cur_slide');
//                            else{                                
//                                //$(".gallery_item").removeClass('cur_slide');                                
//                                last = 1;
//                            }
//                    } 
//                });
//                if(last){
//                    $("#slide1").addClass('cur_slide');
//                    $("#slide1").fadeIn('normal');
//                }           
//                if ($("#"+id).hasClass('last_item')){                    
//                    $(".gallery_item:last").fadeIn('normal');
//                    slide_num = 1;
//                }
//                else{
//                    $("#"+id).prev().fadeIn('normal');
//                }
//            }
//
//    }, 5000);
//    
//    $("#gallery_left").click(function(){
//        $(".gallery_item").hide();
//        $(".gallery_item").each(function(){
//           if($(this).hasClass('cur_slide')){
//                $(this).removeClass('cur_slide');
//                if(!$(this).hasClass('first_item')){
//                    $(this).next().addClass('cur_slide');
//                    $(this).next().fadeIn('normal');
//                    slide_num = parseInt($(this).next().attr('id').substr(5));
//                }
//                else{
//                    $(".gallery_item:first").addClass('cur_slide');
//                    $(".gallery_item:first").fadeIn('normal');
//                    slide_num = 5;
//                }
//                
//                return false;                                
//           } 
//        });
//    });
//    
//    $("#gallery_right").click(function(){
//        $(".gallery_item").hide();
//        $(".gallery_item").each(function(){
//           if($(this).hasClass('cur_slide')){
//                $(this).removeClass('cur_slide');
//                if(!$(this).hasClass('last_item')){
//                    $(this).prev().addClass('cur_slide');
//                    $(this).prev().fadeIn('normal');
//                    slide_num = parseInt($(this).prev().attr('id').substr(5));
//                }
//                else{
//                    $(".gallery_item.first_item").addClass('cur_slide');
//                    $(".gallery_item.first_item").fadeIn('normal');
//                    slide_num = 1;
//                }
//                
//                return false;                                
//           } 
//        });
//    });
    
    //menu
    $(".has_sub a").click(function(){
        $(this).parent().next().slideToggle('normal');    
    });
    
    changeBrandImg();
    changeImgSize();
    
    //////////////highslide gallery////////////////////////////
    
    hs.graphicsDir = './skins/default/highslide/graphics/';
    hs.align = 'center';
    hs.transitions = ['expand', 'crossfade'];
    hs.outlineType = 'rounded-white';
    hs.fadeInOut = true;
    //hs.dimmingOpacity = 0.75;

    // Add the controlbar
    hs.addSlideshow({
	   //slideshowGroup: 'group1',
	   interval: 5000,
	   repeat: false,
	   useControls: true,
	   fixedControls: 'fit',
	   overlayOptions: {
          opacity: 0.75,
		  position: 'bottom center',
		  hideOnMouseOut: true
	   }
    });
    
    //yandex map
    //ymaps.ready(init);
     
});

function changeBrandImg(){
    $("#main_content").find("#production_brands img").each(function(){
       var src = $(this).attr('src');
       $(this).hover(function(){
          $(this).attr('src', $(this).attr('src').replace("_op", ""));   
       },function(){
          $(this).attr('src', src);  
       });
    });
}

function changeImgSize(){
    $("#main_content").find(".product_photo").each(function(){
        var w = $(this).find('img').width();
        var h = $(this).find('img').height();
        
        if(w > h){
            $(this).find('img').width("200px");
        }
        else{
            $(this).find('img').width("150px");
        }
    });
}

function init () {
    var myMap = new ymaps.Map('map', {
        center: [59.7440,30.4888],//[59.9192,30.3102],
        zoom: 9
    });
    
    var points = [
        [59.9192,30.3102],
        [59.8290,30.3462],
        [59.5708,30.0548]
    ];
    
    var hint = [
        ['Офис'],
        ['Производство'],
        ['Производство']
    ];
    
    var address = [
        ['Офис, г. Санкт-Петербург, наб. реки Фонтанки, д. 118'],
        ['Производство, г. Санкт-Петербург, Московское шоссе, д. 13 '],
        ['Производство, г. Гатчина, ул. 120-й Гатчинской дивизии, д.1.']
    ];
    
    for(var i=0; i<points.length; i++){
        myPlacemark = new ymaps.Placemark(points[i], {
            hintContent: hint[i]+', ЗАО Росэкспопром',
            balloonContent: address[i],
            maxHeight: 100
        }, {
            // Опции.
            // Необходимо указать данный тип макета.
            iconLayout: 'default#image',
            // Своё изображение иконки метки.
            iconImageHref: 'skins/default/img/logo.png',
            // Размеры метки.
            iconImageSize: [62, 35],
            // Смещение левого верхнего угла иконки относительно
            // её "ножки" (точки привязки).
            iconImageOffset: [-3, -42]
        });
        //myPlacemark.maxHeight = 100;
        myMap.geoObjects.add(myPlacemark);
    }
    
    myMap.controls
        // Кнопка изменения масштаба.
        .add('zoomControl', { left: 5, top: 5 })
        // Список типов карты
        .add('typeSelector')
        // Стандартный набор кнопок
        .add('mapTools', { left: 35, top: 5 });

    // Также в метод add можно передать экземпляр класса элемента управления.
    // Например, панель управления пробками.
    var trafficControl = new ymaps.control.TrafficControl();
    myMap.controls
        .add(trafficControl)
        // В конструкторе элемента управления можно задавать расширенные
        // параметры, например, тип карты в обзорной карте.
        .add(new ymaps.control.MiniMap({
            type: 'yandex#publicMap'
        }));
}

///carousel////
function carouselInit(gallery){   
   var urls = new Array('http://pyaterochka.ru/', 'http://perekrestok.ru', 'http://karusel.ru/', 'http://www.lenta.com/', 'http://www.okmarket.ru/', 'http://dixy.ru/', 'http://magnit-info.ru/', 'http://www.7-ya.ru/', 'http://www.polushka.info/', '', 'http://www.tdreal.spb.ru/', 'http://www.verno-info.ru/');
   
   var i = 0;
   $("#carousel a").each(function(){
        if(!i){
            if(gallery){
                $(this).attr('href', $(this).find('img').attr('src'));
                $(this).attr('onclick', 'return hs.expand(this)');    
            }
            else{
                $(this).attr('href', urls[i]);
                $(this).attr('target', '_blank');   
            }
        }
        i++;
   });
    
   var carousel = $("#carousel").waterwheelCarousel({
    flankingItems: 3,    
    movedToCenter: function ($item) {        
        if(gallery){
            $item.parent('a').attr('href', $item.attr('src'));
            $item.parent('a').attr('onclick', 'return hs.expand(this)');    
        }
        else{
            var i = 0;
            $("#sale_places img").each(function(){
               if($(this).attr('src') === $item.attr('src')){
                    $item.parent('a').attr('href', urls[i]);
                    $item.parent('a').attr('target', '_blank');
               }
               i++;
            });
        }
        
    },    
    movedFromCenter: function ($item) {
        $item.parent('a').attr('href', '#');
        $item.parent('a').removeAttr('onclick');
        $item.parent('a').removeAttr('target');
    }    
   });

    $('#prev').bind('click', function () {
          carousel.prev();
          return false
    });

    $('#next').bind('click', function () {
        carousel.next();
        return false;
    });

    $('#reload').bind('click', function () {
        newOptions = eval("(" + $('#newoptions').val() + ")");
        carousel.reload(newOptions);
        return false;
    });
}