<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 *
 * (c) Alexander Schilling
 * http://alexanderschilling.net
 *
 */

function dignity_blogs_autoload()
{
	mso_hook_add('admin_init', 'dignity_blogs_admin_init');
	mso_hook_add('custom_page_404', 'dignity_blogs_custom_page_404');
	
	// подключаем плагин jquery
	mso_hook_add('head','blogs_char_count_js_head');
	
	// регестируем виджет
	mso_register_widget('dignity_blogs_category_widget', t('Категории блогов', __FILE__));
	mso_register_widget('dignity_blogs_new_widget', t('Новы записи в блогах', __FILE__));
}

function dignity_blogs_activate($args = array())
{	
	mso_create_allow('dignity_blogs_edit', t('Админ-доступ к', 'plugins') . ' ' . t('«Блоги»', __FILE__));
	
	// доступ к CI
        $CI = & get_instance();	

	// создаём табилицу для записей
	if ( !$CI->db->table_exists('dignity_blogs'))
	{
		$charset = $CI->db->char_set ? $CI->db->char_set : 'utf8';
		$collate = $CI->db->dbcollat ? $CI->db->dbcollat : 'utf8_general_ci';
		$charset_collate = ' DEFAULT CHARACTER SET ' . $charset . ' COLLATE ' . $collate;
		
		$sql = "
		CREATE TABLE " . $CI->db->dbprefix . "dignity_blogs (
		dignity_blogs_id bigint(20) NOT NULL auto_increment,
		dignity_blogs_title varchar(100) NOT NULL default '',
		dignity_blogs_keywords longtext NOT NULL default '',
		dignity_blogs_description longtext NOT NULL default '',
		dignity_blogs_cuttext longtext NOT NULL default '',
		dignity_blogs_text longtext NOT NULL default '',
		dignity_blogs_datecreate datetime NOT NULL default '0000-00-00 00:00:00',
		dignity_blogs_dateupdate datetime NOT NULL default '0000-00-00 00:00:00',
		dignity_blogs_approved varchar(1) NOT NULL default '',
		dignity_blogs_comments varchar(1) NOT NULL default '',
		dignity_blogs_rss varchar(1) NOT NULL default '',
		dignity_blogs_ontop varchar(1) NOT NULL default '',
		dignity_blogs_views bigint(20) NOT NULL default '0',
		dignity_blogs_comuser_id bigint(20) NOT NULL default '0',
		dignity_blogs_category bigint(20) NOT NULL default '0',
		PRIMARY KEY (dignity_blogs_id)
		)" . $charset_collate;
		
		$CI->db->query($sql);
	}
	
	// создаём табилицу для комментарий
	if ( !$CI->db->table_exists('dignity_blogs_comments'))
	{
		$charset = $CI->db->char_set ? $CI->db->char_set : 'utf8';
		$collate = $CI->db->dbcollat ? $CI->db->dbcollat : 'utf8_general_ci';
		$charset_collate = ' DEFAULT CHARACTER SET ' . $charset . ' COLLATE ' . $collate;
		
		$sql = "
		CREATE TABLE " . $CI->db->dbprefix . "dignity_blogs_comments (
		dignity_blogs_comments_id bigint(20) NOT NULL auto_increment,
		dignity_blogs_comments_text longtext NOT NULL default '',
		dignity_blogs_comments_thema_id bigint(20) NOT NULL default '0',
		dignity_blogs_comments_datecreate datetime NOT NULL default '0000-00-00 00:00:00',
		dignity_blogs_comments_dateupdate datetime NOT NULL default '0000-00-00 00:00:00',
		dignity_blogs_comments_approved varchar(1) NOT NULL default '',
		dignity_blogs_comments_comuser_id bigint(20) NOT NULL default '0',
		PRIMARY KEY (dignity_blogs_comments_id)
		)" . $charset_collate;
		
		$CI->db->query($sql);
	}
	
	// создаём табилицу категорий
	if ( !$CI->db->table_exists('dignity_blogs_category'))
	{
		$charset = $CI->db->char_set ? $CI->db->char_set : 'utf8';
		$collate = $CI->db->dbcollat ? $CI->db->dbcollat : 'utf8_general_ci';
		$charset_collate = ' DEFAULT CHARACTER SET ' . $charset . ' COLLATE ' . $collate;
		
		$sql = "
		CREATE TABLE " . $CI->db->dbprefix . "dignity_blogs_category (
		dignity_blogs_category_id bigint(20) NOT NULL auto_increment,
		dignity_blogs_category_name longtext NOT NULL default '',
		dignity_blogs_category_description longtext NOT NULL default '',
		dignity_blogs_category_position bigint(20) NOT NULL default '0',
		PRIMARY KEY (dignity_blogs_category_id)
		)" . $charset_collate;
		
		$CI->db->query($sql);
	}
	
	// создаём табилицу для тэгов
	if ( !$CI->db->table_exists('dignity_blogs_tags_entrys'))
	{
		$charset = $CI->db->char_set ? $CI->db->char_set : 'utf8';
		$collate = $CI->db->dbcollat ? $CI->db->dbcollat : 'utf8_general_ci';
		$charset_collate = ' DEFAULT CHARACTER SET ' . $charset . ' COLLATE ' . $collate;
		
		$sql = "
		CREATE TABLE " . $CI->db->dbprefix . "dignity_blogs_tags_entrys (
		dignity_blogs_tags_entrys_id bigint(20) NOT NULL auto_increment,
		dignity_blogs_tags_entrys_entryid bigint(20) NOT NULL default '0',
		dignity_blogs_tags_entrys_tagid bigint(20) NOT NULL default '0',
		PRIMARY KEY (dignity_blogs_tags_entrys_id)
		)" . $charset_collate;
		
		$CI->db->query($sql);
	}
	
	// создаём табилицу для тэгов
	if ( !$CI->db->table_exists('dignity_blogs_tags'))
	{
		$charset = $CI->db->char_set ? $CI->db->char_set : 'utf8';
		$collate = $CI->db->dbcollat ? $CI->db->dbcollat : 'utf8_general_ci';
		$charset_collate = ' DEFAULT CHARACTER SET ' . $charset . ' COLLATE ' . $collate;
		
		$sql = "
		CREATE TABLE " . $CI->db->dbprefix . "dignity_blogs_tags (
		dignity_blogs_tags_id bigint(20) NOT NULL auto_increment,
		dignity_blogs_tags_tag varchar(200) NOT NULL default '',
		PRIMARY KEY (dignity_blogs_tags_id)
		)" . $charset_collate;
		
		$CI->db->query($sql);
	}

	return $args;
}

function dignity_blogs_uninstall($args = array())
{	
	mso_delete_option('plugin_dignity_blogs', 'plugins');
	mso_remove_allow('dignity_blogs_edit');
	
	// получааем доступ к CI
	$CI = &get_instance();
	
	$CI->load->dbforge();
	
	// удаляем таблицы
	$CI->dbforge->drop_table('dignity_blogs');
	$CI->dbforge->drop_table('dignity_blogs_comments');
	$CI->dbforge->drop_table('dignity_blogs_category');
	
	// удаляем настройки виджета
	mso_delete_option_mask('dignity_blogs_category_widget_', 'plugins');
	mso_delete_option_mask('dignity_blogs_new_widget_', 'plugins');

	return $args;
}

# функция, которая берет настройки из опций виджетов
function dignity_blogs_category_widget($num = 1) 
{
	$widget = 'dignity_blogs_category_widget_' . $num; // имя для опций = виджет + номер
	$options = mso_get_option($widget, 'plugins', array() ); // получаем опции
	
	return dignity_blogs_category_widget_custom($options, $num);
}

# функции плагина
function dignity_blogs_category_widget_custom($options = array(), $num = 1)
{
	// получаем доступ к CI
	$CI = & get_instance();
	
	// обьявляем переменую
	$out = '';
	
	// загружаем опции
	$options = mso_get_option('plugin_blogs_plugins', 'plugins', array());
	if ( !isset($options['slug']) ) $options['slug'] = 'blogs';
	
	// добавляем заголовок «Категории»
	$out .= mso_get_val('widget_header_start', '<h2 class="box"><span>') . t('Категории', __FILE__) . mso_get_val('widget_header_end', '</span></h2>');
	
	// берём данные из базы
	$CI->db->from('dignity_blogs_category');
	$CI->db->order_by('dignity_blogs_category_position', 'asc');
	$query = $CI->db->get();
	
	// если есть что выводить
	if ($query->num_rows() > 0)	
	{	
		$entrys = $query->result_array();
		
		// обьявлем переменую
                $catout = '';
		
		// цикл
		foreach ($entrys as $entry) 
		{
			// узнаем количество записей в категории
			$CI->db->where('dignity_blogs_approved', true);
			$CI->db->where('dignity_blogs_category', $entry['dignity_blogs_category_id']);
			$CI->db->from('dignity_blogs');
			$entry_in_cat = $CI->db->count_all_results();
			
			if ($entry_in_cat > 0)
			{
			
				// выводим названия категории и количество записей в ней
				$catout .= '<li><a href="' . getinfo('siteurl') . $options['slug'] . '/category/'
				    . $entry['dignity_blogs_category_id'] . '">' . $entry['dignity_blogs_category_name'] . '</a>' . ' (' . $entry_in_cat . ') ' . '</li>';
			}
		}
		
		// начиаем новый список
		$out .= '<ul>';
		
		// выводим назавания категорий и количетсов записей
		$out .= $catout;
		
		// количетсов записей всего
		$CI->db->where('dignity_blogs_approved', true);
		$CI->db->from('dignity_blogs');
		$all_entry_in_cat = $CI->db->count_all_results();
		
		// добавляем ссылку «все записи»
		$out .= '<li><a href="' . getinfo('site_url') . $options['slug'] . '/' . '">' . t('Все записи', __FILE__) . '</a>' . ' (' . $all_entry_in_cat . ') ' . '</li>';
		
		// заканчиваем список
		$out .= '</ul>';
	}
	
	return $out;	
}

# функция, которая берет настройки из опций виджетов
function dignity_blogs_new_widget($num = 1) 
{
	$widget = 'dignity_blogs_new_widget_' . $num; // имя для опций = виджет + номер
	$options = mso_get_option($widget, 'plugins', array() ); // получаем опции
	
	return dignity_blogs_new_widget_custom($options, $num);
}

# функции плагина
function dignity_blogs_new_widget_custom($options = array(), $num = 1)
{
	$out = '';
	
	// загружаем опции
	$options = mso_get_option('plugin_blog_plugins', 'plugins', array());
	if ( !isset($options['slug']) ) $options['slug'] = 'blogs';
	
	// добавляем заголовок «Категории»
	$out .= mso_get_val('widget_header_start', '<h2 class="box"><span>') . t('Новые записи в блогах', __FILE__) . mso_get_val('widget_header_end', '</span></h2>');
        
	// получаем доступ к CI
	$CI = & get_instance();
	
	// берём данные из базы
	$CI->db->from('dignity_blogs');
	$CI->db->where('dignity_blogs_approved', true);
	$CI->db->limit(5);
	$CI->db->order_by('dignity_blogs_datecreate', 'desc');
	$query = $CI->db->get();
	
	// если есть что выводить
	if ($query->num_rows() > 0)	
	{	
		$entrys = $query->result_array();
		
		// обьявлем переменую
		$catout = '';
		
		foreach ($entrys as $entry) 
		{
			// выводим названия категории и количество записей в ней
			$catout .= '<li><a href="' . getinfo('siteurl') . $options['slug'] . '/view/'
                                . $entry['dignity_blogs_id'] . '">' . $entry['dignity_blogs_title'] . '</a>' . '</li>';
		}
		
		// начиаем новый список
		$out .= '<ul>';
		
		// выводим назавания категорий и количетсов записей
		$out .= $catout;
	
		// заканчиваем список
		$out .= '</ul>';
		
		$out .= '<a href="' . getinfo('siteurl') . $options['slug'] . '">' . t('Все записи»', __FILE__) . '</a>';
	}
	else
	{
		$out .= t('Новых записей нет.', __FILE__);
	}
	
	return $out;	
}

function dignity_blogs_admin_init($args = array()) 
{
	if ( !mso_check_allow('dignity_blogs_edit') ) 
	{
		return $args;
	}
	
	$this_plugin_url = 'dignity_blogs';
	
	mso_admin_menu_add('plugins', $this_plugin_url, t('Блоги', __FILE__));
	mso_admin_url_hook ($this_plugin_url, 'dignity_blogs_admin_page');
	
	return $args;
}

function dignity_blogs_admin_page($args = array()) 
{
	if ( !mso_check_allow('dignity_blogs_edit') ) 
	{
		echo t('Доступ запрещен', 'plugins');
		return $args;
	}
	
	mso_hook_add_dinamic( 'mso_admin_header', ' return $args . "' . t('Блоги', __FILE__) . '"; ' );
	mso_hook_add_dinamic( 'admin_title', ' return "' . t('Блоги', __FILE__) . ' - " . $args; ' );

	if ( mso_segment(3) == 'edit') require(getinfo('plugins_dir') . 'dignity_blogs/edit.php');
	elseif ( mso_segment(3) == 'editone') require(getinfo('plugins_dir') . 'dignity_blogs/editone.php');
	
	else require(getinfo('plugins_dir') . 'dignity_blogs/admin.php');
}

function dignity_blogs_custom_page_404($args = false)
{
	$options = mso_get_option('plugin_dignity_blogs', 'plugins', array());
	if ( !isset($options['slug']) ) $options['slug'] = 'blogs';
   
	if ( mso_segment(1)==$options['slug'] )
	{
		if(mso_segment(2) == 'add')
		{
			// открываем add
			require( getinfo('plugins_dir') . 'dignity_blogs/blog_add.php' );
		}
		elseif(mso_segment(2) == 'edit')
		{
			// открываем edit
			require( getinfo('plugins_dir') . 'dignity_blogs/blog_edit.php' );
		}
		elseif(mso_segment(2) == 'blog')
		{
			// открываем view
			require( getinfo('plugins_dir') . 'dignity_blogs/blog.php' );
		}
		elseif(mso_segment(2) == 'view')
		{
			// открываем view
			require( getinfo('plugins_dir') . 'dignity_blogs/view.php' );
		}
		elseif(mso_segment(2) == 'all')
		{
			// открываем view
			require( getinfo('plugins_dir') . 'dignity_blogs/all.php' );
		}
		elseif(mso_segment(2) == 'my')
		{
			// открываем view
			require( getinfo('plugins_dir') . 'dignity_blogs/my.php' );
		}
		elseif(mso_segment(2) == 'category')
		{
			// открываем view
			require( getinfo('plugins_dir') . 'dignity_blogs/category.php' );
		}
		elseif(mso_segment(2) == 'rss')
		{
			// открываем view
			require( getinfo('plugins_dir') . 'dignity_blogs/rss.php' );
		}
		elseif(mso_segment(2) == 'comments')
		{
			// открываем view
			require( getinfo('plugins_dir') . 'dignity_blogs/comments.php' );
		}
		elseif(mso_segment(2) == 'new')
		{
			// открываем view
			require( getinfo('plugins_dir') . 'dignity_blogs/new.php' );
		}
		else
		{
			// открываем
			require( getinfo('plugins_dir') . 'dignity_blogs/blogs.php' ) ;
		}
		
		return true;
	}

   return $args;
}

// подсчёт количества символов
function blogs_char_count_js_head()
{
	if (is_login_comuser())
	{
		echo '<script type="text/javascript" src="' . getinfo('plugins_url') . 'dignity_blogs/js/charCount.js' . '"></script>';	
	}
}

function blogs_yandex_share($out='')
{
	$out .= '<script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script>
<div class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="icon" data-yashareQuickServices="vkontakte,facebook,twitter,gplus"></div>';
	
	return $out;
}

// парсер bb-code -> html
function blogs_cleantext(&$content)
{
        // защита от xss
        mso_xss_clean($content);
    
        // массив
        $preg = array(
                    
                // жирный
                '~\[b\](.*?)\[\/b\]~si' => '<strong>$1</strong>',
                    
                // курсив
                '~\[i\](.*?)\[\/i\]~si' => '<i>$1</i>',
                    
                // подчёркнутый
                '~\[u\](.*?)\[\/u\]~si' => '<u>$1</u>',
                
                // зачёркнутый
                '~\[s\](.*?)\[\/s\]~si' => '<s>$1</s>',
                
                // заголовок h1
                '~\[h1\](.*?)\[\/h1\]~si' => '<h1>$1</h1>',
                
                // заголовок h2
                '~\[h2\](.*?)\[\/h2\]~si' => '<h2>$1</h2>',
                
                // заголовок h3
                '~\[h3\](.*?)\[\/h3\]~si' => '<h3>$1</h3>',
                
                // цвет
                '~\[color=(.*?)\](.*?)\[\/color\]~si' => '<span style="color:$1">$2</span>',
                
                // p-абзац
                '~\[p\](.*?)\[\/p\]~si'	=> '<p>$1</p>',
                '~\[p=(.*?)\](.*?)\[\/p\]~si' => '<p style="$1">$2</p>',
                
                // пренудительный перенос
                '~\[br\]~si' => '<br>',
                
                // pre
                '~\[pre\](.*?)\[\/pre\]~si' => '<pre>$1</pre>',
                
                // цитата
                '~\[quote\](.*?)\[\/quote\]~si' => '<blockquote>$1</blockquote>',
                
                // код
                '~\[code\](.*?)\[\/code\]~si' => '<code>$1</code>',
                
                // изображение
                '~\[img\](.*?)\[\/img\]~si' => '<img src="$1" title="" alt="">',
                
                // ссылка
                '~\[url\](.*?)\[\/url\]~si' => '<a href="$1" rel="nofollow">$1</a>',
                '~\[url=(.[^ ]*?)\](.*?)\[\/url\]~si' => '<a href="$1" rel="nofollow">$2</a>',
                
                // youtube
                '~\[youtube\](.*?)\[\/youtube\]~si' => '<iframe width="640" height="360" src="http://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>',
                
		// вконтакте
                '~\[vk\](.*?)\[\/vk\]~si' => '<iframe src="$1" width="640" height="360" frameborder="0"></iframe>',
				
		// vimeo
		'~\[vimeo\](.*?)\[\/vimeo\]~si' => '<iframe src="http://player.vimeo.com/video/$1" width="640" height="360" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>',

		// yandex-video
		'~\[yavideo\](.*?)\[\/yavideo\]~si' => '<iframe width="640" height="360" frameborder="0" src="$1"></iframe>',
				
                // горизонтальная линия
                '~\[hr\]~si' => '<hr>',
                
                // переносы                                        
                '~\n~' => '<br/>',
		
		# div
		'~\[left\](.*?)\[\/left\]~si' => '<div style="text-align: left;">$1</div>',

		'~\[right\](.*?)\[\/right\]~si' => '<div style="text-align: right;">$1</div>',

		'~\[center\](.*?)\[\/center\]~si' => '<div style="text-align: center;">$1</div>',

		'~\[justify\](.*?)\[\/justify\]~si' => '<div style="text-align: justify;">$1</div>',
		
		'~\[size=(.*?)\](.*?)\[\/size\]~si'		=> '<span style="font-size:$1">$2</span>',
                
            );
            
            $content = preg_replace(array_keys($preg), array_values($preg), $content);
            
    return $content;

}

// подключаем редактор markitup и задаём настройки
function dignity_blogs_editor()
{
 
	// подключаем js от редактора markitup
	echo '<script src="'. getinfo('plugins_url') . 'editor_markitup/jquery.markitup.js"></script>';

	// подключаем стили
	echo '<link rel="stylesheet" href="'. getinfo('plugins_url') . 'dignity_blogs/style.css">';
 
	echo "<script type=\"text/javascript\" >
		var dignity_plugins_editor_settings = {
		
		nameSpace:'bbcode',
		
		markupSet:[
			{name:'Полужирный', openWith:'[b]', closeWith:'[/b]', className:'bold', key:'B'},
			{name:'Курсив', openWith:'[i]', closeWith:'[/i]', className:'italic', key:'I'},
			{name:'Подчеркнутый', openWith:'[u]', closeWith:'[/u]', className:'underline', key:'U'},
			{name:'Зачеркнутый', openWith:'[s]', closeWith:'[/s]', className:'stroke', key:'S'},
			{name:'Заголовок1', openWith:'[h1]', closeWith:'[/h1]', className:'h1'},
			{name:'Заголовок2', openWith:'[h2]', closeWith:'[/h2]', className:'h2'},
			{name:'Заголовок3', openWith:'[h3]', closeWith:'[/h3]', className:'h3'},
			{name:'По левому краю', openWith:'[left]', closeWith:'[/left]', className:'left'},
			{name:'По центру', openWith:'[center]', closeWith:'[/center]', className:'center'},
			{name:'По правому краю', openWith:'[right]', closeWith:'[/right]', className:'right'},
			{name:'По ширине', openWith:'[justify]', closeWith:'[/justify]', className:'justify'},
			{name:'Размер текста', openWith:'[size=]', closeWith:'[/size]', className:'text_smallcaps'},
			{name:'Цвет', openWith:'[color=]', closeWith:'[/color]', className:'colors'},
			{name:'Принудительный перенос', replaceWith:'[br]', className:'br'},
			{name:'Преформатированный текст', openWith:'[pre]', closeWith:'[/pre]', className:'pre'},
			{name:'Цитата', openWith:'[quote]', closeWith:'[/quote]', className:'quote'},
			{name:'Код', openBlockWith:'[code]', closeBlockWith:'[/code]', className:'code'}, 
			{name:'Изображение', openWith:'[img]', closeWith:'[/img]', className:'picture'},
			{name:'Ссылка', openBlockWith:'[url]', closeBlockWith:'[/url]', className:'link'},
			{name:'Youtube-Видео', openBlockWith:'[youtube]', closeBlockWith:'[/youtube]', className:'youtube'},
			{name:'Видко-Вконтакте', openBlockWith:'[vk]', closeBlockWith:'[/vk]', className:'vk'},
			{name:'Vimeo-Видео', openBlockWith:'[vimeo]', closeBlockWith:'[/vimeo]', className:'vimeo'},
			{name:'Яндекс-Видео', openBlockWith:'[yavideo]', closeBlockWith:'[/yavideo]', className:'ya_video'},
		],
		
		}
	</script>";
 
	echo '<script type="text/javascript" >
			$(document).ready(function() {
			$(".markItUp").markItUp(dignity_plugins_editor_settings);
			});
	</script>';
	
}

// меню
function blogs_menu()
{
        
        // загружаем опции
        $options = mso_get_option('plugin_dignity_blogs', 'plugins', array());
        if ( !isset($options['slug']) ) $options['slug'] = 'blogs';
        
        echo "<style>
        
        .tabs{
            border-bottom:solid 1px #dddddd;
            padding-bottom:1px;
            width: 100%;
        }
        
        ul.tabs-nav {
            margin: 0;
            padding: 0;
            height: 30px;
            width: 100%;
            list-style: none;
        }
        
        ul.tabs-nav li.elem {
            float: left;
            display: inline;
            position: relative;
            line-height: 30px;
            height: 30px;
            margin: 0 2px 0 0;
            padding: 0 5px;
            cursor: pointer;
            font-size: .9em;
            background: #fff;
            color: #888;
            -webkit-border-radius: 5px 5px 0 0;
            -moz-border-radius: 5px 5px 0 0;
            border-radius: 5px 5px 0 0;
            border:solid 1px #dddddd;
        }
        
        ul.tabs-nav li.elem:hover,
        ul.tabs-nav li.tabs-current {
            background: #DDD;
            color: black;
        }
        
        </style>";
        
        echo '<div class="tabs"><ul class="tabs-nav">';
        
        if (mso_segment(2))
        {
            echo '<li class="elem"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/fav.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '">' . t('Избранные', __FILE__) . '</a></span></li>';
        }
        else
        {
            echo '<li class="elem tabs-current"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/fav.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '">' . t('Избранные', __FILE__) . '</a></span></li>';
        }
        
        if (mso_segment(2) == 'all')
        {
            echo '<li class="elem tabs-current"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/all.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/all/' . '">' . t('Блоги', __FILE__) . '</a></span></li>';
        }
        else
        {
            echo '<li class="elem"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/all.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/all/' . '">' . t('Блоги', __FILE__) . '</a></span></li>';
        }
        
        if (is_login_comuser())
	{
            if (mso_segment(2) == 'my')
            {
                echo '<li class="elem tabs-current"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/my.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/my/' . getinfo('comusers_id') . '">' . t('Мои записи', __FILE__) . '</a></span></li>';
            }
            else
            {
                 echo '<li class="elem"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/my.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/my/' . getinfo('comusers_id') . '">' . t('Мои записи', __FILE__) . '</a></span></li>';
            }
        }
        
 if (mso_segment(2) == 'add')
            {
                echo '<li class="elem tabs-current"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/edit.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/add/' . '">' . t('Новая запись', __FILE__) . '</a></span></li>';
            }
            else
            {
                 echo '<li class="elem"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/edit.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/add/' . '">' . t('Новая запись', __FILE__) . '</a></span></li>';
            }
        
	if (mso_segment(2) == 'new')
        {
            echo '<li class="elem tabs-current"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/new.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/new/' . '">' . t('Новые', __FILE__) . '</a></span></li>';
        }
        else
        {
            echo '<li class="elem"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/new.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/new/' . '">' . t('Новые', __FILE__) . '</a></span></li>';
        }
	
	if (mso_segment(2) == 'comments')
        {
            echo '<li class="elem tabs-current"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/comments.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/comments/' . '">' . t('Комментарии', __FILE__) . '</a></span></li>';
        }
        else
        {
            echo '<li class="elem"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/comments.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/comments/' . '">' . t('Комментарии', __FILE__) . '</a></span></li>';
        }
	
	echo '<li class="elem"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/rss.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/rss/' . '">' . t('RSS', __FILE__) . '</a></span></li>';
	
        echo '</ul></div><br>';
}

#end of file
