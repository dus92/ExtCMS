<?
    $iblock = new iBlockItem();
    $iblock->BeginiBlockItemsListRead('', '*', '', 'idate', 'DESC');
    $res = '';
    while($row = $iblock->Read()){
        if($row['contid'] == 'news'){
            $res.='
            <div class="new_cont">
                <div class="new_date">'.date("d.m.Y", strtotime($row['idate'])).'</div>
                <div class="new_main">
                    <div class="new_title">
                        <a href="news_'.$row['id'].'-'.$row['title'].'.html">'.$row['title'].'</a>
                    </div>
                    <div class="new_descr">
                        '.$row['description'].'
                    </div>
                </div>
            </div>
            <div style="clear: both"></div>';
        }
    }
    unset($iblock);


?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>                                                             
    <title><?rcms_show_element('title')?></title>
    <LINK REL="shortcut icon" HREF="/favicon.ico" TYPE="image/x-icon">
    <?rcms_show_element('meta')?>
    <link rel="stylesheet" href="<?=CUR_SKIN_PATH?>css/style.css" type="text/css">
    <link rel="stylesheet" type="text/css" href="<?=CUR_SKIN_PATH?>/highslide/highslide.css" />
    <?rcms_show_element('head_js')?>
    <script type="text/javascript" src="<?=CUR_SKIN_PATH?>js/general.js"></script>
    <script type="text/javascript" src="<?=CUR_SKIN_PATH?>highslide/highslide-with-gallery.js"></script>
    <script src="http://api-maps.yandex.ru/2.0/?load=package.standard,package.traffic&lang=ru-RU" type="text/javascript"></script>
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="<?=CUR_SKIN_PATH?>js/jquery.waterwheelCarousel.js"></script>
    
    <!--[if gte IE 9]>
  <style type="text/css">
    #footer{
       filter: none;
    }
  </style>
<![endif]-->    
</head>
<body>	
    <?rcms_show_element('header')?>    
    <div style="min-height: 100%;height: auto !important;height: 100%;margin: 0 0 -80px 0; padding: 0;">
    <div id="div_main">
        <div id="top">
            <a href="./"><div id="logo"></div></a>
            <div id="slogan">
                <img src="<?=CUR_SKIN_PATH?>img/slogan.jpg" border="0" alt="Сохраняя традиции качества" />
            </div>
            <div id="cow">
                <img src="<?=CUR_SKIN_PATH?>img/cow.jpg" border="0" />
            </div>
        </div>  
        <div style="clear: both;"></div>    
    </div>
    <div id="top_line"></div>
    <div id="twenty_years">
        <img src="<?=CUR_SKIN_PATH?>img/twenty_years.jpg" border="0" alt="20 лет работаем для Вас" />
    </div>
    <div id="top_menu">
        <div class="topmenu_item"><a href="./">О КОМПАНИИ</a></div>
        <div class="topmenu_item"><a href="static_1-production.html">ПРОИЗВОДСТВО</a></div>
        <div class="topmenu_item"><a href="gallery_22-gallery-photo.html">ГАЛЕРЕЯ</a></div>
        <div class="topmenu_item"><a href="partners">ПАРТНЕРАМ</a></div>
        <div class="topmenu_item"><a href="consumers">ПОТРЕБИТЕЛЯМ</a></div>
    </div>
    <div id="cont_blue">
        <div id="left_menu">
            <div id="leftmenu_about" class="leftmenu_subitems">
                <div class="leftmenu_item"><a href="about_2-mission.html">Миссия</a></div>
                <div class="leftmenu_item"><a href="about_3-history.html">История</a></div>
                <div class="leftmenu_item"><a href="about_4-achievements.html">Достижения</a></div>
                <div class="leftmenu_item"><a href="about_5-contacts.html">Контакты</a></div>
                <div class="leftmenu_item"><a href="about_41-vacancies.html">Вакансии</a></div>
            </div>
            <div id="leftmenu_production" class="leftmenu_subitems">
                <div class="leftmenu_item"><a href="production_17-products.html">Продукция</a></div>
                <div class="leftmenu_item"><a href="production_6-quality.html">Контроль качества</a></div>
                <!--<div class="leftmenu_item"><a href="production_8-technology.html">Технологии</a></div>-->
                <div class="leftmenu_item"><a href="production_9-logistics.html">Логистика</a></div>
            </div>
            <div id="leftmenu_gallery" class="leftmenu_subitems">                
                <div class="leftmenu_item"><a href="gallery_22-gallery-photo.html">Фото</a></div>
                <div class="leftmenu_item"><a href="gallery-video">Видео</a></div>
            </div>
            <div id="leftmenu_partners" class="leftmenu_subitems">
                <div class="leftmenu_item"><a href="partners_10-cooperation.html">Сотрудничество</a></div>
                <div class="leftmenu_item"><a href="partners_11-documents.html">Документы</a></div>
            </div>
            <div id="leftmenu_consumers" class="leftmenu_subitems">
                <div class="leftmenu_item"><a href="consumers_12-cooperation.html">Места продаж</a></div>
                <div class="leftmenu_item"><a href="consumers_13-about-butter.html">О сливочном масле</a></div>
                <div class="leftmenu_item"><a href="consumers_14-about-cheese.html">О сыре</a></div>
                <div class="leftmenu_item"><a href="consumers_15-right-choise.html">Как правильно выбрать</a></div>
                <!--<div class="leftmenu_item"><a href="consumers_16-its-interesting.html">Это интересно</a></div>-->
            </div>
        </div>
        <div id="cars"></div>
        <div id="gallery">
            <div class="gallery_item">
                <img src="<?=CUR_SKIN_PATH?>img/gallery_item1.png" border="0" height="320" />    
            </div>
        </div>
    </div>
    <div id="main_cont">
        <div id="news">
            <div id="news_top_title"><a href="news">НОВОСТИ</a></div>
            <!--<div id="news_top_title" style="margin: 0px 0px 0px 40px; float: right;"><a href="news">СМИ О РЕП</a></div>-->
            <div style="clear: both;"></div>            
            <?=$res?>
            <!--
            <div class="new_cont">
                <div class="new_date">18.10.13</div>
                <div class="new_main">
                    <div class="new_title"><a href="">С Юбилеем!</a></div>
                    <div class="new_descr">
                        Известный петербургский изготовитель сливочного масла ЗАО "РосЭкспоПром" 
                        18 октября отметил свой юбилей.
                    </div>
                </div>
            </div>
            -->
        </div>
        <div id="main_module">            
            <div id="main_content">
                <?rcms_show_element('main_point', $module . '@window.center')?>
                <!--<div id="loader"><img width="50" src="<?=CUR_SKIN_PATH?>img/loader.gif" /></div>-->
            </div>    
        </div>
    </div>
    </div>
    <div id="footer">
        <div id="footer_inner">
            <div class="footer_elem"><a href="production_17-products.html"><img src="<?=CUR_SKIN_PATH?>img/footer/1.jpg" border="0" height="40" /></a></div>
            <div class="footer_elem"><a href="production_17-products.html"><img src="<?=CUR_SKIN_PATH?>img/footer/2.jpg" border="0" height="40" /></a></div>
            <div class="footer_elem"><a href="production_17-products.html"><img src="<?=CUR_SKIN_PATH?>img/footer/3.jpg" border="0" height="40" /></a></div>
            <div class="footer_elem"><a href="production_17-products.html"><img src="<?=CUR_SKIN_PATH?>img/footer/4.jpg" border="0" height="40" /></a></div>
            <div class="footer_elem"><a href="production_17-products.html"><img src="<?=CUR_SKIN_PATH?>img/footer/5.jpg" border="0" height="40" /></a></div>
            <div class="footer_elem"><a href="production_17-products.html"><img src="<?=CUR_SKIN_PATH?>img/footer/6.jpg" border="0" height="40" /></a></div>
            <div class="footer_elem"><a href="production_17-products.html"><img src="<?=CUR_SKIN_PATH?>img/footer/7.jpg" border="0" height="40" /></a></div>
        </div>
    </div>

</body>
</html>