// Instantiate an empty object.
var Instagram = {};

// Small object for holding important configuration data.
Instagram.Config = {
  //clientID: 'a307c0d0dada4b77b974766d71b72e0e',
  clientID: 'b3d184209c7b43d1be39c19dc000541b',
  apiHost: 'https://api.instagram.com'
};


// ************************
// ** Main Application Code
// ************************
(function(){
  var photoTemplate, resource;
  var photo_id_arr = new Array();
  var contest_active = 0; //флаг активности конкурса

  function init(){
    bindEventHandlers();
    photoTemplate = _.template($('#photo-template').html());
  }

  function toTemplate(photo){
    photo = {
      count: photo.likes.count,
      avatar: photo.user.profile_picture,
      photo: photo.images.low_resolution.url,
      url: photo.link,
      id: photo.id
    };

    return photoTemplate(photo);
  }

    function getPhotos(photos){
        var photos_html = '';
        var photos_arr = new Array();
    
        $.each(photos, function(index, photo){
            photos_html += toTemplate(photo);      
            photo_id_arr.push(photo.id);          
        });                             
        $('div#photos-wrap').html(photos_html);                                         
    }
  
  function loadPhotos(photos){
    var photos_html = '';
    var photos_arr = new Array();

    //$('.paginate a').attr('data-max-tag-id', photos.pagination.next_max_id).fadeIn();

    $.each(photos.data, function(index, photo){
      photos_html += toTemplate(photo);      
      photo_id_arr.push(photo.id);          
    });
               
    //$('div#photos-hide').html(photos_html);
    $('div#div-hide').html("");
    $('div#div-hide').prepend(photos_html);
    $('div#photos-hide').html(photos_html);  
    
    var i=0;
    var listitems = $('div#photos-hide').children().get();
    listitems.sort(function(a, b) {
        var compA = parseInt($(a).find('strong').attr('id').substr(6));
        var compB = parseInt($(b).find('strong').attr('id').substr(6));;
        return (compA > compB) ? -1 : (compA < compB) ? 1 : 0;
    });
    $('div#photos-wrap').html("");
    $.each(listitems, function(idx, itm) { $('div#photos-wrap').append(itm); });          
    //$('div#photos-wrap').append(photos_html);                                         
  }
  
  function updatePhotos(photos){
    var photos_html = '';
    var count = 0;
   // $('.paginate a').attr('data-max-tag-id', photos.pagination.next_max_id).fadeIn();
        
    $.each(photos.data, function(index, photo){                  
      count = 0;
      var k=0;           
      $("div#div-hide").find(".photo").each(function(){                  
        if ($(this).attr("id").substr(6) != photo.id){
            count++;
        }         
      });            
      if (count == $('div#div-hide').children().length){
         photos_html += toTemplate(photo);
         photo_id_arr.push(photo.id);
      }      
    });
    $('div#div-hide').prepend(photos_html);                
    $('div#photos-hide').html($('div#div-hide').html());            
    
    var i=0;
    var listitems = $('div#photos-hide').children().get();
    
    listitems.sort(function(a, b) {
        var compA = parseInt($(a).find('strong').attr('id').substr(6));
        var compB = parseInt($(b).find('strong').attr('id').substr(6));;
        return (compA > compB) ? -1 : (compA < compB) ? 1 : 0;
    });    
    $('div#photos-wrap').html("");
    //$('div#photos-wrap').prepend(photos_html);    
    $.each(listitems, function(idx, itm) { $('div#photos-wrap').append(itm); });                         
    
    //if($(".photo").length > 20){
//        var k=0;
//        for (var i=20;i<$(".photo").length;i++){
//        }     
//    }    
  }
  
//  function updateLikes(l, media_id){
//    //console.log(likes.data.length);
//    var num_likes = parseInt($("#heart_"+media_id+" strong").html());
//    var new_like_id;
//    if (l.data.likes.count > num_likes){
////        $("#heart_"+media_id).fadeOut('normal',function(){
////            $(this).css('background-image', 'url(images/fav_active.png)');
////        }).fadeIn('normal', function(){
////            var th = $(this);
////            setTimeout(function(){
////                th.css('background-image', 'url(images/fav.png)');
////            },500);            
////        });   
//        $("#heart_"+media_id).css('background-image', 'url(skins/default/images/fav_active.png)');
//        new_like_id = media_id;                 
//    }        
//    $("#heart_"+media_id+" strong").html(l.data.likes.count);
//    //////////////////////////////////////////////////////////
//    //$('div#div-hide').prepend(photos_html);                
//    $('div#photos-hide').html($('div#div-hide').html());            
//    
//    var i=0;
//    var listitems = $('div#photos-hide').children().get();
//    
//    listitems.sort(function(a, b) {
//        var compA = parseInt($(a).find('strong').attr('id').substr(6));
//        var compB = parseInt($(b).find('strong').attr('id').substr(6));;
//        return (compA > compB) ? -1 : (compA < compB) ? 1 : 0;
//    });    
//    $('div#photos-wrap').html("");
//    //$('div#photos-wrap').prepend(photos_html);    
//    $.each(listitems, function(idx, itm) { $('div#photos-wrap').append(itm); });
//    $("#heart_"+new_like_id).css('background-image', 'url(skins/default/images/fav_active.png)');
//  }

  function updateLikes(likes, media_id){
      $("#heart_"+media_id+" strong").html(likes);       
  }  
  
  function getLikes(media_id){
    var config = Instagram.Config, url;
    url = config.apiHost + "/v1/media/" + media_id + "?callback=?&access_token=1190957630.b3d1842.79aced89d6e6467cb2465a5f149a13cb";
    return url;  
  }

  function generateResource(tag){
    var config = Instagram.Config, url;

    if(typeof tag === 'undefined'){
      throw new Error("Resource requires a tag.");
    } else {
      // Make sure tag is a string, trim any trailing/leading whitespace and take only the first 
      // word, if there are multiple.
      tag = String(tag).trim().split(" ")[0];
    }

    url = config.apiHost + "/v1/tags/" + tag + "/media/recent?callback=?&client_id=" + config.clientID;
    return url;

//    return function(max_id){
//      var next_page;
//      if(typeof max_id === 'string' && max_id.trim() !== '') {
//        next_page = url + "&max_id=" + max_id;
//      }
//      return next_page || url;
//    };
  }

  function paginate(max_id){    
    $.getJSON(generateUrl(tag), toScreen);
  }    

  function search(tag){
    resource = generateResource(tag);
    $('.paginate a').hide();
//    $('#photos-wrap *').remove();
    //$('#photos-wrap').html("");
    fetchPhotos();
  }

  function fetchPhotos(){    
    tag = $.cookie('tag');
    $.getJSON(generateResource(tag), function(data){        
        $.ajax({
            type: 'POST',
            url: 'ajax.php?module=instagram',
            dataType: 'json',
            data: {
                tag: tag,
                getImg: 'getImg',
                img_arr: data          
            },
            success: function(res){
                getPhotos(res.data);            
            },
            error: function(data){
                console.log(data);
            } 
        });       
    });           
  }
  
  function fetchUpdatePhotos(ContestID, tag){
    //tag = 'zenitpiter';
    $.getJSON(generateResource(tag), function(data){
        //updatePhotos(data);                    
        $.ajax({
            type: 'POST',
            url: 'ajax.php?module=instagram',
            dataType: 'json',
            data: {
                tag: tag,
                getImg: 'getImg',
                img_arr: data,
                ContestID: ContestID
            },
            success: function(res){                
                getPhotos(res.data);                            
            },
            error: function(data){
                console.log(data);
            } 
        });               
    });    
  }
  
  function fetchLikes(media_id){
    $.getJSON(getLikes(media_id), function(data){
       // updateLikes(data, media_id);   
    });
  }

  function bindEventHandlers(){
//    $('body').on('click', '.paginate a.btn', function(){
//      //var tagID = $(this).attr('data-max-tag-id');
//     // fetchPhotos(tagID);
//      return false;
//    });
        
    //setTimeout(function(){                  
        $.ajax({
            type: 'POST',
            url: 'ajax.php?module=instagram',
            dataType: 'json',
            data: {
               isActiveContest: 'isActiveContest'  
            },            
            success: function(res){                
                //console.log(res);
                if (res.success){
                    Release(res.ContestID, res.tag, 5000, 10000);
                    //$("#btn_update").show();
                }                    
                else{
                    $("div#photos-wrap").html('<h4>'+res.msg+'</h4>');
                    $("#btn_update").hide();                    
                    //$(".container").html('<h4>'+res.msg+'</h4>');   
                }                         
            },
            error: function(data){
                alert("error");
            }     
        });
                              
    //},5000);
        
    onFormSubmit();
    
    $("#btn_update").click(function(){
        
    });

  }
  
  function onFormSubmit(){
    // Bind an event handler to the `submit` event on the form
    $('form').submit(function(e){
        e.preventDefault();
        var tag = $('input.search-tag').val().trim();
        $.cookie('tag', tag);
        $.cookie('tag', tag);
        if(tag) 
            search(tag);
    });  
  }
  
  function Release(ContestID, tag, intervalPhotos, intervalLikes){
    var id = '', href = '';
    setInterval(function(){
        fetchUpdatePhotos(ContestID, tag);              
    },intervalPhotos);
    
    var i = 0, cycle_kol = 0, length = 0;
    var succes_req = 1;
    setInterval(function(){                        
        if (i == length){
            success_req = 1;
            i=0;
        }
        else
            success_req = 0;
        //alert($('div#div-hide').children().length);
        //$('div#div-hide').find('.photo').each(function(){
        var t = 500;    
        $('div#photos-wrap').find('.photo').each(function(){
            var th = $(this);
            //length = $('div#div-hide').children().length;            
            href = $(this).find('a').attr('href');
        //    if (success_req || cycle_kol == 0){
            setTimeout(function(){
            $.ajax({
                type: "POST",
                url: 'ajax.php?module=instagram',
                async: true,
                data: {
                    'href': href   
                },
               // timeout: 10000000000000,
                dataType: "json",
                success: function(data){            
                 //   updateLikes(data.likes, data.img_id);
                },
                error: function(data){
                    console.log(data);
                }                
            });
            },t);
            t += 350;            
    //   }                  
        });
        cycle_kol++;
        
    },intervalLikes);
  }

  function showPhoto(p){
    //$(p).fadeIn();
    $(p).show();
  }

  // Public API
  Instagram.App = {
    search: search,
    showPhoto: showPhoto,
    init: init
  };
}());

$(function(){
  Instagram.App.init();
  //var date = new Date();
  //alert(Math.round( date.getTime()/1000));
  // Start with a search on cats; we all love cats.    
  $.cookie('tag', 'zenitpiter');
  //$(".search-tag").val($.cookie('tag'));
  //Instagram.App.search($.cookie('tag'));
});


$(document).ready(function(){      
    //$(".search-tag").val($.cookie('tag'));        
});

