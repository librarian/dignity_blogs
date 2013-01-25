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
	mso_hook_add('users_add_out', 'dignity_blogs_users_add_out', '100');
	
	// регестируем виджеты
	mso_register_widget('dignity_blogs_category_widget', t('Категории блогов', __FILE__));
	mso_register_widget('dignity_blogs_new_widget', t('Новые записи в блогах', __FILE__));
}

function dignity_blogs_activate($args = array())
{	
	require_once(getinfo('plugins_dir') . 'dignity_blogs/core/activate.php');

	return $args;
}

function dignity_blogs_uninstall($args = array())
{	
	
	require_once(getinfo('plugins_dir') . 'dignity_blogs/core/uninstall.php');

	return $args;
}

# подключаем функции виджетов из файлов
require_once(getinfo('plugins_dir') . 'dignity_blogs/widgets/category_widget.php');
require_once(getinfo('plugins_dir') . 'dignity_blogs/widgets/new_pages.php');

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

	else require(getinfo('plugins_dir') . 'dignity_blogs/admin/admin.php');
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
			require( getinfo('plugins_dir') . 'dignity_blogs/user/add.php' );
		}
		elseif(mso_segment(2) == 'edit')
		{
			// открываем edit
			require( getinfo('plugins_dir') . 'dignity_blogs/user/edit.php' );
		}
		elseif(mso_segment(2) == 'blog')
		{
			// открываем blog - показываем все записи одного пользователя
			require( getinfo('plugins_dir') . 'dignity_blogs/views/blog.php' );
		}
		elseif(mso_segment(2) == 'view')
		{
			// открываем view - показываем всю запись
			require( getinfo('plugins_dir') . 'dignity_blogs/views/view.php' );
		}
		elseif(mso_segment(2) == 'all')
		{
			// открываем all - показываем все блоги
			require( getinfo('plugins_dir') . 'dignity_blogs/views/all.php' );
		}
		elseif(mso_segment(2) == 'my')
		{
			// открываем my
			require( getinfo('plugins_dir') . 'dignity_blogs/user/my.php' );
		}
		elseif(mso_segment(2) == 'category')
		{
			// открываем category
			require( getinfo('plugins_dir') . 'dignity_blogs/views/category.php' );
		}
		elseif(mso_segment(2) == 'rss')
		{
			// открываем rss - rss лента всех записей
			require( getinfo('plugins_dir') . 'dignity_blogs/views/rss.php' );
		}
		elseif(mso_segment(2) == 'comments')
		{
			// открываем comments - новые комментарии
			require( getinfo('plugins_dir') . 'dignity_blogs/views/comments.php' );
		}
		elseif(mso_segment(2) == 'new')
		{
			// открываем new - новые записи
			require( getinfo('plugins_dir') . 'dignity_blogs/views/new.php' );
		}
		elseif(mso_segment(2) == 'feed')
		{
			// открываем feed - rss лента блога
			require( getinfo('plugins_dir') . 'dignity_blogs/views/feed.php' );
		}
		else
		{
			// открываем избранные записи
			require( getinfo('plugins_dir') . 'dignity_blogs/views/blogs.php' ) ;
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
	require_once(getinfo('plugins_dir') . 'dignity_blogs/user/users_add_out.php');
	
	return $comuser;
}

#end of file
