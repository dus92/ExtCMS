$(document).ready(function (){
    var allow_scroll = 1;    
    
    function clear_onload(){ //обнуляем чекбоксы, выставляем значение селектора по умолчанию 0("все")
        form_to.sel_to.selectedIndex = 0;
        //form = document.forms['form_ch'];
        for(var i=0;i<form_ch.elements.length;i++)
            form_ch.elements[i].checked = false;
        for(var i=0;i<form_gids.elements.length;i++)
            form_gids.elements[i].checked = false;       
        
        if (form_ch.elements.length == 0){
            $("#sel_to option[id=1]").attr('disabled','disabled');
            $("#sel_to option[id=2]").attr('disabled','disabled');
                        
            $("#imail_text_input").attr('disabled','disabled');
        }        
        if ($("#sel_groups").children().length == 0){
            $("#sel_watch option[id=2]").attr('disabled','disabled');
            $("#sel_groups").remove();
        }
    }    
    
    function showhideBlocks(val,name){
        if (name=='sel_to'){
            if (val == 0){                
                $('#div_users').hide();
                $('#div_groups').hide(); 
            }
            else if (val == 1){ 
                $('#i'+val).show();
                $('#div_users').show(); 
                $('#div_groups').hide();  
            }
            else if (val == 2){
                $('#i'+val).show();                
                $('#div_groups').show();
                $('#div_users').hide();
            }
            else{            
                $('#div_users').hide(); 
                $('#div_groups').hide();
            }    
        }
        else if (name=='sel_watch'){
            var sel_index = form_watch.sel_watch.selectedIndex;
            if (sel_index==2){
                //$("#sel_watch").css('width','50%');
                $("#div_sel_watch").css('width','50%');                
                
                $("#div_sel_groups").css('width','49%');
                $("#sel_groups").css('width','100%');
                
                $('#sel_groups').show();
                form_watch.sel_groups?form_watch.sel_groups.selectedIndex = 0:false;
            }
            else{                
                $("#div_sel_watch").css('width','100%');                                
                $('#sel_groups').hide();
            }            
        }
    }
    clear_onload();
    
    $("#sel_to").change(function(){
        showhideBlocks($(this).attr('value'), $(this).attr('name'));
    });
    $("#sel_watch").change(function(){
        showhideBlocks($(this).attr('value'), $(this).attr('name'));
    });    	

    // делаем фокус на поле ввода при загрузке страницы
	if ($("#imail_text_input").size()>0)	
		$("#imail_text_input").focus();
	
    //alert($("#imail_text_field").html());
    //console.log($("#imail_text_field").children());
    if (!$("#imail_text_field").children().length){
        //$("#imail_text_field").html('<b id="no_messages">Нет сообщений в чате!</b>');
        if (form_ch.elements.length == 0)
           $("#no_messages").append('<br>Нет созданных пользователей!'); 
    }
	////////////////////////////////////////////////
	my_url = 'ajax.php?module=imail';     // TODO : move code to admin/ajax/imail.php
    ////////////////////////////////////////////////
	
	function get_imail_messages () 
	{	
		if ($('#block').val() == 'no')	// если не выставлена блокировка повторного выполнения данной функции, продолжаем
		{            
            //	$('#block').val('yes');		// ставим блокировку
			var last_act = $('#last_act').val();
			$.ajax(
			{
				url: my_url,
				type: 'POST',
				data: 
				{
					'action': 'get_imail_message',
					'last_act': last_act
				},
				dataType: 'json',
				success: function (result) 
				{			
				if(result != null)
				{		
					$("#no_messages").remove();
                    $('#imail_text_field').append(result.message_code); 	// добавляем в текстовое поле новые сообщения
					$('#last_act').val(result.last_act);				// обновляем значение последнего сообщения
					}	
					//$('#block').val('yes');
					// автопрокрутка текстового поля вниз
				//	$('#imail_text_field').scrollTop($('#imail_text_field').scrollTop()+100*$('.imail_post_my, .imail_post_other').size());	
					
				//	$('#block').val('no');// убираем блокировку
					
				} // конец success
			}); // конец ajax	  
		}
		
	}
    //очищаем чекбоксы
   	function clearCheckBoxes() 
    {
	  var idd;
      var form=document.forms['form_ch'];
	  for(var i=0;i<form.elements.length;i++) 
      {
	      if(form.elements[i].checked) 
          {
              form.elements[i].checked=false;
	      }    
      }
	}
	
	// отправка сообщений при нажатии клавиши "Enter"
	$('#imail_text_input').keyup(function(event)
    {
        if (event.which == 13 && event.ctrlKey)
        {
      		var message_text = $('#imail_text_input').val();
            var form=document.forms['form_ch'];
            var ch1 = new Array();
            for(var i=0;i<form.elements.length;i++) 
            {
                if(form.elements[i].checked) 
                {
                    ch1[i] = form.elements[i].value; //чекбоксы пользователей
                }    
            }
            var form1 = document.forms['form_gids'];
            var ch_groups = new Array();
            var uids_from_groups = new Array();
            for(var i=0;i<form1.elements.length;i++) 
            {
                if(form1.elements[i].checked) 
                {
                    ch_groups[i] = form1.elements[i].value; //чекбоксы групп
                    uids_from_groups[i] = form1.elements[i].name; //id users from checked groups
	           }   
            }
            //alert(ch_groups);
            var sel_index = form_to.sel_to.selectedIndex; //выбранный пункт списка
        
            if (sel_index==2 && ch_groups=='') //если не выбраны группы
            {
                //alert('Choose groups!');
                alert('Не выбрана группа!');
                return;
            }
        
            if (sel_index==1 && ch1=='') //если не выбраны пользователи
            {
                //alert('Choose users!');
                alert('Не выбраны пользователи!');
                return;
            }

            if (message_text!=""){
			     $.ajax({
				    url: my_url,
				    type: 'POST',
				    data: {
					   'action': 'add_message',
                        'ch1': ch1.toString(),
                        'message_text': message_text,
                        'sel_index' : sel_index,
                        'ch_groups' : ch_groups.toString(),
                        'uids_from_groups' : uids_from_groups.toString()
				    },
				    dataType: 'json',
				    success: function (result) {					
   					    $('#imail_text_input').val(''); 	// очищаем поле ввода
                        get_imail_messages(); 			// сразу же подгружаем отправленное сообщение в чат	
                        setTimeout(function(){
                            $('#imail_text_field').scrollTop($('#imail_text_field').scrollTop()+100*$('.imail_post_my, .imail_post_other').size());
                        },200);				
				    }, // конец success
                    error: function(){
                        alert("error sending!!!");
                    }
                
			     }); // конец ajax
            }
    	}
    });
	
	// отправка сообщений при нажатии кнопки "Ответить"
	$('#imail_button').click(function() {
		var message_text = $('#imail_text_input').val();
        var form=document.forms['form_ch'];
        var ch1 = new Array();
        for(var i=0;i<form.elements.length;i++) {
            if(form.elements[i].checked) {
                ch1[i] = form.elements[i].value; //чекбоксы пользователей
                //form.elements[i].checked=false;
	        }    
        }
        var form1 = document.forms['form_gids'];
        var ch_groups = new Array();
        var uids_from_groups = new Array();
        for(var i=0;i<form1.elements.length;i++) {
            if(form1.elements[i].checked) {
                ch_groups[i] = form1.elements[i].value; //чекбоксы групп
                uids_from_groups[i] = form1.elements[i].name; //id users from checked groups
	        }   
        }
        //alert(ch_groups);
        var sel_index = form_to.sel_to.selectedIndex; //выбранный пункт списка
        
        if (sel_index==2 && ch_groups=='') //если не выбраны группы
        {
            //alert('Choose groups!');
            alert('Не выбрана группа!');
            return;
        }
        
         if (sel_index==1 && ch1=='') //если не выбраны пользователи
         {
            //alert('Choose users!');
            alert('Не выбраны пользователи!');
            return;
         }
            
        
        //var d = '111';
        //alert(sel_index);
        //alert(ch1);
        if (message_text!="")
        {
    
			$.ajax(
			{
				url: my_url,
				type: 'POST',
				data: 
				{
					'action': 'add_message',
                    'ch1': ch1.toString(),
                    'message_text': message_text,
                    'sel_index' : sel_index,
                    'ch_groups' : ch_groups.toString(),
                    'uids_from_groups' : uids_from_groups.toString()
				},
				dataType: 'json',
				success: function (result) 
				{					
   					$('#imail_text_input').val(''); 	// очищаем поле ввода
                    get_imail_messages(); 			// сразу же подгружаем отправленное сообщение в чат		
                    setTimeout(function(){
                        $('#imail_text_field').scrollTop($('#imail_text_field').scrollTop()+100*$('.imail_post_my, .imail_post_other').size());
                    },200);                    			
				}, // конец success
                error: function()
                {
                    alert("error sending!!!");
                }
                
			}); // конец ajax
         }
        
        //send_message();
        //clearCheckBoxes();
	});
    
    //Отображение сообшений по выбору элемента из списка
    
    
   	//Действие для кнопки "Очистить"
	$('#clear').click(function() 
	{	
        $('#imail_text_input').val('');
	});
	
	// Действие для кнопки "Выход"
	$('#logout_button').click(function() 
	{
		window.location.href = 'imail/index.php?logout';
	});
	
	// проверяем наличие новых сообщений каждые 2 секунды
	setInterval(function() 
	{
		get_imail_messages();
	}, 2000);
	
	// прокрутка текстового поля до последнего сообщения вниз
	$('#imail_text_field').scrollTop($('#imail_text_field').scrollTop()+100*$('.imail_post_my, .imail_post_other').size());
	
	//////////////загрузка имеющихся сообщений//////////////////////
      
    $('#sel_watch').change(function() 
    {       
        $('#imail_text_field').empty();
        var sel_index = form_watch.sel_watch.selectedIndex;
        //var sel_index_gr = form_watch.sel_groups.selectedIndex;
        
       // var sel_index_gr = form_watch.sel_groups.selectedIndex;
        //alert(sel_index_gr);
        $.ajax(
	    {
	       url: my_url,
	       type: 'POST', 
	       data: 
	       {
	           'action' : 'show_messages',
               'sel_index': sel_index
	       },
	       dataType: 'json',
           success: function (result) 
	       {					
               // if (sel_index == 2)
//                {
//                 //   $('#imail_text_field').empty();
//                   // sel_index_gr = 0;
//                    $('#imail_text_field').append("Choose group!");
//                }
                $('#imail_text_field').append(result.message_code);
                //$('#imail_text_field').append(result.str); //-----------------
                // автопрокрутка текстового поля вниз
				$('#imail_text_field').scrollTop($('#imail_text_field').scrollTop()+1000*$('.imail_post_my, .imail_post_other').size());
                //alert(result.min_gid);
                if (sel_index == 2) 
                {
                    //sel_index_gr = 0;
                    if (result.message_code == null)
                    {
                        //alert('No messages addressed to this group!');
                        //alert('Нет сообщений, адресованных указанной группе (группам)!');                        
                        //$('#imail_text_field').append('No messages addressed to this group!');
                        //$('#imail_text_field').append('Нет сообщений, адресованных указанной группе (группам)!');
                        //$('#imail_text_field').append(result.str); //-----------------
                    }
                }
                else{
                    if (result.message_code == null){                     
                        //$('#imail_text_field').append('<span style="margin-left:30px;">Нет сообщений!</span>');
                        //$('#imail_text_field').append(result.str); //-----------------
                    }
                }
                    
	       }, // конец success
           error: function()
           {
                alert('Ошибка при загрузке сообщений!');
           }
                
	   }); // конец ajax
     });
///////////////////загрузка сообщений определенных групп пользователей/////////////////////////
      $('#sel_groups').change(function() 
    {
       // alert('gdgfdfg');
        $('#imail_text_field').empty();
        
        //var sel_index_watch = form_watch.sel_watch.selectedIndex;
        var sel_index_gr = form_watch.sel_groups?form_watch.sel_groups.selectedIndex:false;
        var sel_value = form_watch.sel_groups.value;
        
        $.ajax(
	    {
	       url: my_url,
	       type: 'POST',
	       data: 
	       {
	           'action' : 'show_messages',
               'sel_index_gr': sel_index_gr,
               'sel_value' : sel_value
	       },
	       dataType: 'json',
           success: function (result) 
	       {					
                if (result.message != null)
                {
                    $('#imail_text_field').append(result.message);
                    //$('#imail_text_field').append(result.str); //-----------------
                    // автопрокрутка текстового поля вниз
				    $('#imail_text_field').scrollTop($('#imail_text_field').scrollTop()+1000*$('.imail_post_my, .imail_post_other').size());
                    //alert(result.message); 
                }
                else
                {
                    //alert('No messages addressed to this group!');
                    //$('#imail_text_field').append('No messages addressed to this group!');
                    //$('#imail_text_field').append('Нет сообщений, адресованных указанной группе (группам)!');
                    //$('#imail_text_field').append(result.str); //-----------------
                }                                    
	       }, // конец success
           error: function()
           {
                //alert("error loading messages!!!");
                alert('Ошибка при загрузке сообщений!');
           }                
	   }); // конец ajax
     });          	        
}); // конец ready

    