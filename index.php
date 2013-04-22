<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * (c) Alexander Schilling
 * http://alexanderschilling.net
 * https://github.com/dignityinside/dignity_blogs (github)
 * License GNU GPL 2+
 */

// функция автоподключения плагина
function dignity_blogs_autoload()
{
	// хук на админку
	mso_hook_add('admin_init', 'dignity_blogs_admin_init');

	// хук на пользовательский вывод
	mso_hook_add('custom_page_404', 'dignity_blogs_custom_page_404');

	// для вывода количества статей и комментарий
	mso_hook_add('users_add_out', 'dignity_blogs_users_add_out', '100');
	
	// регестируем виджеты
	mso_register_widget('dignity_blogs_category_widget', t('Категории блогов', __FILE__));
	mso_register_widget('dignity_blogs_new_widget', t('Новые записи в блогах', __FILE__));
}

// функция выполняется при активации (вкл) плагина
function dignity_blogs_activate($args = array())
{	
	// подключаем файл
	require_once(getinfo('plugins_dir') . 'dignity_blogs/core/activate.php');

	return $args;
}

// функция выполняется при деинсталяции плагина
function dignity_blogs_uninstall($args = array())
{	
	// подключаем файл
	require_once(getinfo('plugins_dir') . 'dignity_blogs/core/uninstall.php');

	return $args;
}

// подключаем функции виджетов из файлов
require_once(getinfo('plugins_dir') . 'dignity_blogs/widgets/category_widget.php');
require_once(getinfo('plugins_dir') . 'dignity_blogs/widgets/new_pages.php');

// функция выполняется при указаном хуке admin_init
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

// функция вызываемая при хуке, указанном в mso_admin_url_hook
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

	else require(getinfo('plugins_dir') . 'dignity_blogs/admin/admin.php');
}

function dignity_blogs_custom_page_404($args = false)
{
	$options = mso_get_option('plugin_dignity_blogs', 'plugins', array());
	if ( !isset($options['slug']) ) $options['slug'] = 'blogs';
   
   // если первый сегмент равен slug (blogs)
	if ( mso_segment(1) == $options['slug'] )
	{
		switch (mso_segment(2)) {
                case 'add':
			        require( getinfo('plugins_dir') . 'dignity_blogs/user/add.php' );
                    break;
                case 'edit':
			        require( getinfo('plugins_dir') . 'dignity_blogs/user/edit.php' );
                    break;
                case 'my':
			        require( getinfo('plugins_dir') . 'dignity_blogs/user/my.php' );
                    break;
                case 'blog':
			        require( getinfo('plugins_dir') . 'dignity_blogs/views/blog.php' );
                    break;
                case 'view':
			        require( getinfo('plugins_dir') . 'dignity_blogs/views/view.php' );
                    break;
                case 'all':
			        require( getinfo('plugins_dir') . 'dignity_blogs/views/all.php' );
                    break;
                case 'category':
			        require( getinfo('plugins_dir') . 'dignity_blogs/views/category.php' );
                    break;
                case 'new':
			        require( getinfo('plugins_dir') . 'dignity_blogs/views/new.php' );
                    break;
                case 'rss':
			        require( getinfo('plugins_dir') . 'dignity_blogs/views/rss.php' );
                    break;
                case 'comments':
			        require( getinfo('plugins_dir') . 'dignity_blogs/views/comments.php' );
                    break;
                case 'feed':
			        require( getinfo('plugins_dir') . 'dignity_blogs/views/feed.php' );
                    break;
                default:
			        require( getinfo('plugins_dir') . 'dignity_blogs/views/blogs.php' );
                    break;

		}

		
		return true;
	}

   return $args;
}

// подключаем css стили
mso_hook_add('head', 'blogs_style_css');

function blogs_style_css($a = array())
{
	if (file_exists(getinfo('plugins_dir') . 'dignity_blogs/css/custom.css'))
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
	// загружаем файл
	require_once(getinfo('plugins_dir') . 'dignity_blogs/user/users_add_out.php');
	
	return $comuser;
}

#end of file
