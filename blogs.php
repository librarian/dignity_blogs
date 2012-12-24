<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * (c) Alexander Schilling
 * http://alexanderschilling.net
 */

// начало шаблона
if ($fn = mso_find_ts_file('main/main-start.php')) require($fn);

// доступ к CI
$CI = & get_instance();

// загружаем опции
$options = mso_get_option('plugin_dignity_blogs', 'plugins', array());
if ( !isset($options['limit']) ) $options['limit'] = 10;
if ( !isset($options['slug']) ) $options['slug'] = 'blogs';
if ( !isset($options['title']) ) $options['title'] = t('Блоги', __FILE__);
if ( !isset($options['description']) ) $options['description'] = '';
if ( !isset($options['keywords']) ) $options['keywords'] = '';
if ( !isset($options['textdo']) ) $options['textdo'] = '';

mso_head_meta('title', $options['title']);
mso_head_meta('description', $options['description']);
mso_head_meta('keywords', $options['keywords']);

// выводим меню
blogs_menu();

echo '<h1>' . $options['title'] . '</h1>';

if ($options['textdo'])
{
	echo '<p>' . $options['textdo'] . '</p>';
}

// готовим пагинацию для статей
$pag = array();
$pag['limit'] = $options['limit'];
$CI->db->select('dignity_blogs_id');
$CI->db->from('dignity_blogs');
$CI->db->where('dignity_blogs_approved', true);
$CI->db->where('dignity_blogs_ontop', true);
$query = $CI->db->get();
$pag_row = $query->num_rows();

if ($pag_row > 0)
{
	$pag['maxcount'] = ceil($pag_row / $pag['limit']);

	$current_paged = mso_current_paged();
	if ($current_paged > $pag['maxcount']) $current_paged = $pag['maxcount'];

	$offset = $current_paged * $pag['limit'] - $pag['limit'];
}
else
{
	$pag = false;
}

// загружаем статьи из базы
$CI->db->from('dignity_blogs');
$CI->db->where('dignity_blogs_approved', true);
$CI->db->where('dignity_blogs_ontop', true);
$CI->db->join('dignity_blogs_category', 'dignity_blogs_category.dignity_blogs_category_id = dignity_blogs.dignity_blogs_category', 'left');
$CI->db->join('comusers', 'comusers.comusers_id = dignity_blogs.dignity_blogs_comuser_id', 'left');
$CI->db->order_by('dignity_blogs_datecreate', 'desc');
if ($pag and $offset) $CI->db->limit($pag['limit'], $offset);
else $CI->db->limit($pag['limit']);
$query = $CI->db->get();

// если есть что выводить...
if ($query->num_rows() > 0)	
{
	$allpages = $query->result_array();
	
	$out = '';
	
	foreach ($allpages as $onepage) 
	{
		
		$out .= '<div class="page_only">';
		
		$out .= '<div class="info info-top">';
		$out .= '<h1>';
		$out .= '<a href="' . getinfo('site_url') . $options['slug'] . '/view/' . $onepage['dignity_blogs_id'] . '">' . $onepage['dignity_blogs_title'] . '</a>';
		$out .= '</h1>';
		$out .= '</div>';
		
		// если вошел автор записи
       if ($onepage['dignity_blogs_comuser_id'] == getinfo('comusers_id'))
       {
            // выводим ссылку «редактировать»
            $out .= '<p><span style="padding-right:10px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/edit.png' . '"></span><a href="' . getinfo('site_url') . $options['slug'] . '/edit/' . $onepage['dignity_blogs_id'] . '">' . t('Редактировать', __FILE__) . '</a></p>';
       }
		
		$out .= '<p><span style="padding-right:10px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/user.png' . '"></span><a href="' . getinfo('site_url') . $options['slug'] . '/blog/' . $onepage['dignity_blogs_comuser_id'] . '">' . t('Блог им. ', __FILE__) . $onepage['comusers_nik'] . '</a></p>';
		
		$out .= '<p>' . blogs_cleantext($onepage['dignity_blogs_cuttext']) . '</p>';
		
		// если нет текста, скрываем ссылку «подробнее»
		if (!$onepage['dignity_blogs_text'])
		{
			// ничего не показываем...
			$out .= '';
		}
		else
		{
			// показываем ссылку «подробнее»
			$out .= '<p style="padding-bottom:10px;">';
			$out .= '<a href="' . getinfo('site_url') . $options['slug'] . '/view/' . $onepage['dignity_blogs_id'] . '">' .
				t('Подробнее →', __FILE__) . '</a>';
			$out .= '</p>';
		}
		
		$out .= '<div class="info info-bottom">'
			. '<span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/public.png' . '"></span>';

		$out .= mso_date_convert($format = 'd.m.Y → H:i', $onepage['dignity_blogs_datecreate']);

		if ($onepage['dignity_blogs_category_id'])
		{
			$out .= ' | ' . '<span style="padding-right:0px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/ordner.png' . '"></span>' . ' <a href="' . getinfo('site_url') . $options['slug'] . '/category/' . $onepage['dignity_blogs_category_id'] . '">' . $onepage['dignity_blogs_category_name'] . '</a>';
		}
		else
		{
			$out .= ' | ' . '<span style="padding-right:0px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/ordner.png' . '"></span>' . ' <a href="' . getinfo('site_url') . $options['slug'] . '">' . t('Все записи', __FILE__) . '</a>';
		}
		
		$CI->db->from('dignity_blogs_comments');
		$CI->db->where('dignity_blogs_comments_approved', true);
		$CI->db->where('dignity_blogs_comments_thema_id', $onepage['dignity_blogs_id']);
		$out .= ' | ' . '<span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/comments.png' . '"></span>' . $CI->db->count_all_results();
		
		$out .= '</div>';
		
		$out .= '<div class="break"></div>';
		$out .= '</div><!--div class="page_only"-->';
		
	}
	
	echo $out;
	
	mso_hook('pagination', $pag);
}
else
{
	echo t('Нет записей. Ваша будет первой!', __FILE__);
}

// просьба не удалять эту строчку!
echo '<p style="font-size:10px; color:#555555; text-align:right;">Dignity Blogs by <a href="http://alexanderschilling.net">Alexander Schilling</a> | Source on <a href="https://github.com/dignityinside/dignity-blogs">github</a></p>';

// конец шаблона
if ($fn = mso_find_ts_file('main/main-end.php')) require($fn);

#end of file
