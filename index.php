<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * (c) Alexander Schilling
 * http://alexanderschilling.net
 * https://github.com/dignityinside/dignity_blogs (github)
 * License GNU GPL 2+
 */

function dignity_blogs_autoload()
{
	mso_hook_add('admin_init', 'dignity_blogs_admin_init');
	mso_hook_add('custom_page_404', 'dignity_blogs_custom_page_404');

	// для вывода количества статей и комментарий
	mso_hook_add('users_add_out', 'dignity_blogs_users_add_out');
	
	// подключаем плагин jquery для подсчёта количества введеных символов
	mso_hook_add('head','blogs_char_count_js_head');
	
	// регестируем виджеты
	mso_register_widget('dignity_blogs_category_widget', t('Категории блогов', __FILE__));
	mso_register_widget('dignity_blogs_new_widget', t('Новые записи в блогах', __FILE__));
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
		dignity_blogs_category_parent_id bigint(20) NOT NULL default '0',
		PRIMARY KEY (dignity_blogs_category_id)
		)" . $charset_collate;
		
		$CI->db->query($sql);
	}
	
	// создаём табилицу для тэгов (на будущее)
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
	
	// создаём табилицу для тэгов (на будущее)
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
	$CI->dbforge->drop_table('dignity_blogs_tags_entrys');
	$CI->dbforge->drop_table('dignity_blogs_tags');
	
	// удаляем настройки виджета
	mso_delete_option_mask('dignity_blogs_category_widget_', 'plugins');
	mso_delete_option_mask('dignity_blogs_new_widget_', 'plugins');

	// сбрасываем кеш
	mso_flush_cache();

	return $args;
}

//<- начало первого виджета

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
	
	$out = '';
	
	// загружаем опции
	$options = mso_get_option('plugin_blogs_plugins', 'plugins', array());
	if ( !isset($options['slug']) ) $options['slug'] = 'blogs';
	
	// добавляем заголовок «категории»
	$out .= mso_get_val('widget_header_start', '<h2 class="box"><span>') . t('Категории', __FILE__) . mso_get_val('widget_header_end', '</span></h2>');
	
	// берём категори из базы
	$CI->db->from('dignity_blogs_category');
	$CI->db->order_by('dignity_blogs_category_position', 'asc');
	$query = $CI->db->get();
	
	// если есть что выводить
	if ($query->num_rows() > 0)	
	{	
		$entrys = $query->result_array();
		
        $catout = '';
		
		foreach ($entrys as $entry) 
		{
			// узнаем количество записей в категории
			$CI->db->where('dignity_blogs_approved', true);
			$CI->db->where('dignity_blogs_category', $entry['dignity_blogs_category_id']);
			$CI->db->from('dignity_blogs');
			$entry_in_cat = $CI->db->count_all_results();
			
			// если есть записи в категории
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

//-> конец первого виджета

// <- начало второго виджета

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

// -> конец второго виджета

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

	// редактировать комментарии (админ)
	if ( mso_segment(3) == 'edit_comments') require(getinfo('plugins_dir') . 'dignity_blogs/admin/edit_comments.php');
	elseif ( mso_segment(3) == 'editone_comment') require(getinfo('plugins_dir') . 'dignity_blogs/admin/editone_comment.php');
	elseif ( mso_segment(3) == 'edit_article') require(getinfo('plugins_dir') . 'dignity_blogs/admin/edit_article.php');
	elseif ( mso_segment(3) == 'editone_article') require(getinfo('plugins_dir') . 'dignity_blogs/admin/editone_article.php');

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
			// открываем blog - показываем все записи одного пользователя
			require( getinfo('plugins_dir') . 'dignity_blogs/blog.php' );
		}
		elseif(mso_segment(2) == 'view')
		{
			// открываем view - показываем всю запись
			require( getinfo('plugins_dir') . 'dignity_blogs/view.php' );
		}
		elseif(mso_segment(2) == 'all')
		{
			// открываем all - показываем все блоги
			require( getinfo('plugins_dir') . 'dignity_blogs/all.php' );
		}
		elseif(mso_segment(2) == 'my')
		{
			// открываем my
			require( getinfo('plugins_dir') . 'dignity_blogs/my.php' );
		}
		elseif(mso_segment(2) == 'category')
		{
			// открываем category
			require( getinfo('plugins_dir') . 'dignity_blogs/category.php' );
		}
		elseif(mso_segment(2) == 'rss')
		{
			// открываем rss - rss лента всех записей
			require( getinfo('plugins_dir') . 'dignity_blogs/rss.php' );
		}
		elseif(mso_segment(2) == 'comments')
		{
			// открываем comments - новые комментарии
			require( getinfo('plugins_dir') . 'dignity_blogs/comments.php' );
		}
		elseif(mso_segment(2) == 'new')
		{
			// открываем new - новые записи
			require( getinfo('plugins_dir') . 'dignity_blogs/new.php' );
		}
		elseif(mso_segment(2) == 'feed')
		{
			// открываем feed - rss лента блога
			require( getinfo('plugins_dir') . 'dignity_blogs/feed.php' );
		}
		else
		{
			// открываем избранные записи
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

	$out .= '<div class="blogs_social">' . t('Понравилась статья? Расскажи о ней друзьям в социальных сетях, им тоже должно понравиться!', __FILE__) . '</div>';

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

				// новый тэг для видео (вконтакте, яндекс-видео, vimeo и других)
				'~\[video\](.*?)\[\/video\]~si' => '<iframe width="640" height="360" frameborder="0" src="$1"></iframe>',
				
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
	echo '<script src="'. getinfo('plugins_url') . 'dignity_blogs/js/jquery.markitup.js"></script>';

	// подключаем стили редактора
	echo '<link rel="stylesheet" href="'. getinfo('plugins_url') . 'dignity_blogs/css/editor.css">';
 
	echo "<script type=\"text/javascript\" >
		var dignity_blogs_editor_settings = {
		
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
			{name:'Видео', openBlockWith:'[video]', closeBlockWith:'[/video]', className:'video'},
		],
		
		}
	</script>";
 
	echo '<script type="text/javascript" >
			$(document).ready(function() {
			$(".markItUp").markItUp(dignity_blogs_editor_settings);
			});
	</script>';
	
}

// подключаем редактор markitup и задаём настройки
function dignity_blogs_comments_editor()
{
 
	// подключаем js от редактора markitup
	echo '<script src="'. getinfo('plugins_url') . 'dignity_blogs/js/jquery.markitup.js"></script>';

	// подключаем стили редактора
	echo '<link rel="stylesheet" href="'. getinfo('plugins_url') . 'dignity_blogs/css/editor.css">';
 
	echo "<script type=\"text/javascript\" >
		var dignity_blogs_comments_editor_settings = {
		
		nameSpace:'bbcode',
		
		markupSet:[
			{name:'Полужирный', openWith:'[b]', closeWith:'[/b]', className:'bold', key:'B'},
			{name:'Курсив', openWith:'[i]', closeWith:'[/i]', className:'italic', key:'I'},
			{name:'Подчеркнутый', openWith:'[u]', closeWith:'[/u]', className:'underline', key:'U'},
			{name:'Зачеркнутый', openWith:'[s]', closeWith:'[/s]', className:'stroke', key:'S'},
			{name:'Цитата', openWith:'[quote]', closeWith:'[/quote]', className:'quote'},
			{name:'Код', openBlockWith:'[code]', closeBlockWith:'[/code]', className:'code'},
		],
		
		}
	</script>";
 
	echo '<script type="text/javascript" >
			$(document).ready(function() {
			$(".markItUp").markItUp(dignity_blogs_comments_editor_settings);
			});
	</script>';
	
}

// меню
function blogs_menu()
{
        
        // загружаем опции
        $options = mso_get_option('plugin_dignity_blogs', 'plugins', array());
        if ( !isset($options['slug']) ) $options['slug'] = 'blogs';
        
        echo '<div class="blogs_tabs">';
	        echo '<ul class="blogs_tabs-nav">';
	        
	        if (mso_segment(2))
	        {
	            echo '<li class="elem"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/fav.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '">' . t('Избранные', __FILE__) . '</a></span></li>';
	        }
	        else
	        {
	            echo '<li class="elem blogs_tabs-current"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/fav.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '">' . t('Избранные', __FILE__) . '</a></span></li>';
	        }
	        
	        if (mso_segment(2) == 'all')
	        {
	            echo '<li class="elem blogs_tabs-current"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/all.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/all/' . '">' . t('Блоги', __FILE__) . '</a></span></li>';
	        }
	        else
	        {
	            echo '<li class="elem"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/all.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/all/' . '">' . t('Блоги', __FILE__) . '</a></span></li>';
	        }
	        
	        if (is_login_comuser())
			{
	           	if (mso_segment(2) == 'my')
	            {
	                echo '<li class="elem blogs_tabs-current"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/my.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/my/' . getinfo('comusers_id') . '">' . t('Мои записи', __FILE__) . '</a></span></li>';
	            }
	            else
	            {
	                 echo '<li class="elem"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/my.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/my/' . getinfo('comusers_id') . '">' . t('Мои записи', __FILE__) . '</a></span></li>';
	            }
	        }
	        
			if (mso_segment(2) == 'add')
	        {
	            echo '<li class="elem blogs_tabs-current"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/edit.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/add/' . '">' . t('Написать в блог!', __FILE__) . '</a></span></li>';
	        }
	        else
	        {
	            echo '<li class="elem"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/edit.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/add/' . '">' . t('Написать в блог!', __FILE__) . '</a></span></li>';
	        }
	        
			if (mso_segment(2) == 'new')
		    {
		        echo '<li class="elem blogs_tabs-current"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/new.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/new/' . '">' . t('Новые', __FILE__) . '</a></span></li>';
		    }
		    else
		    {
		        echo '<li class="elem"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/new.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/new/' . '">' . t('Новые', __FILE__) . '</a></span></li>';
		    }
			
			if (mso_segment(2) == 'comments')
		    {
		        echo '<li class="elem blogs_tabs-current"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/comments.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/comments/' . '">' . t('Комментарии', __FILE__) . '</a></span></li>';
		    }
		    else
		    {
		        echo '<li class="elem"><span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/comments.png' . '"></span><span><a href="' . getinfo('site_url') . $options['slug'] . '/comments/' . '">' . t('Комментарии', __FILE__) . '</a></span></li>';
		    }
		
			echo '<li class="elem"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/rss.png' . '" title="' . t('RSS лента', __FILE__) . '"><span><a href="' . getinfo('site_url') . $options['slug'] . '/rss/' . '"></a></span></li>';
		
	        echo '</ul>';
        echo '</div>';
        echo '<br />';
}

// подключаем css стили
mso_hook_add('head', 'blogs_style_css');

function blogs_style_css($a = array())
{
	if (file_exists(getinfo('plugins_url') . 'dignity_blogs/css/custom.css'))
	{
		$css = getinfo('plugins_url') . 'dignity_blogs/css/custom.css';
	} 
	else $css = getinfo('plugins_url') . 'dignity_blogs/css/style.css';
		
	echo '<link rel="stylesheet" href="' . $css . '">' . NR;
	
	return $a;
}

// функция хука users_add_out
// выводит количество публикаций и комментарий на странице комюзера
function dignity_blogs_users_add_out($comuser = array())
{
	// доступ к CodeIgniter
	$CI = & get_instance();

	// загружаем опции
	$options = mso_get_option('plugin_dignity_blogs', 'plugins', array());
	if ( !isset($options['slug']) ) $options['slug'] = 'blogs';

	// выводим заголовок
	echo '<h2 style="padding: 3px; border-bottom: 1px solid #DDD;">' . t('Активность в блогах', __FILE__) . '</h2>';

	// подсчитываем количество статей комюзера
    $CI->db->from('dignity_blogs');
    $CI->db->where('dignity_blogs_approved', '1');
    $CI->db->where('dignity_blogs_comuser_id', mso_segment(2));
    $blogs_entry = $CI->db->count_all_results();

    // если больше одной, то выводим ссылку на блог
    if ($blogs_entry >= 1)
    {
    	$entry_url = '<a href="' . getinfo('site_url') . $options['slug'] . '/blog/' . mso_segment(2) . '">' . $blogs_entry . '</a>';
    }
    else
    {
    	$entry_url = $blogs_entry;
    }

    // выводим заголовок
    echo '<p style="padding-left:20px;">' . '<strong>' . t('Публикаций:', __FILE__) . '</strong> ' . $entry_url . '</p>';
      
    // подсчитываем количество комментарий комюзера
    $CI->db->from('dignity_blogs_comments');
	$CI->db->where('dignity_blogs_comments_approved', '1');
	$CI->db->where('dignity_blogs_comments_comuser_id', mso_segment(2));
	$blogs_comments = $CI->db->count_all_results();
    echo '<p style="padding-left:20px;">' . '<strong>' . t('Комментарий:', __FILE__) . '</strong> ' . $blogs_comments . '</p>';
	
	return $comuser;
}

#end of file
