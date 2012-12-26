<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/*
 * (c) Alexander Schilling
 * http://alexanderschilling.net
 * https://github.com/dignityinside/dignity_blogs (github)
 * License GNU GPL 2+
 */

// начало шаблона
if ($fn = mso_find_ts_file('main/main-start.php')) require($fn);

// доступ к CI
$CI = & get_instance();

// выводим меню
blogs_menu();

// загружаем опции
$options = mso_get_option('plugin_dignity_blogs', 'plugins', array());
if ( !isset($options['limit']) ) $options['limit'] = 10;
if ( !isset($options['slug']) ) $options['slug'] = 'blogs';

// проверка сегмента
$id = mso_segment(3);
if (!is_numeric($id)) $id = false;
else $id = (int) $id;

if ($id)
{
	// готовим пагинацию статей
	$pag = array();
	$pag['limit'] = $options['limit'];
	$CI->db->from('dignity_blogs');
	$CI->db->select('dignity_blogs_id');
	$CI->db->where('dignity_blogs_comuser_id', $id);
	$CI->db->where('dignity_blogs_approved', true);
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

	// берем статьи из базы
	$CI->db->from('dignity_blogs');
	$CI->db->where('dignity_blogs_comuser_id', $id);
	$CI->db->order_by('dignity_blogs_datecreate', 'desc');
	if ($pag and $offset) $CI->db->limit($pag['limit'], $offset);
	else $CI->db->limit($pag['limit']);
	$query = $CI->db->get();

	// если есть что выводить
	if ($query->num_rows() > 0)	
	{	
		$allpages = $query->result_array();
		
		$out = '';
		
        foreach ($allpages as $onepage) 
        {
            $out .= '<div class="blogs_page_only">';
			
				$no_approved = '';
				if ($onepage['dignity_blogs_comuser_id'] == getinfo('comusers_id'))
				{
					if (!$onepage['dignity_blogs_approved'])
					{
						$no_approved .= '<span style="color:red;">?</span>';
					}
				}
			
	            $out .= '<div class="blogs_info">';
		            $out .= '<h1>' . $no_approved;
					
					if($onepage['dignity_blogs_approved'])
					{
						$out .= '<a href="' . getinfo('site_url') . $options['slug'] . '/view/' . $onepage['dignity_blogs_id'] . '">';
					}
					else
					{
						$out .= '<a href="' . getinfo('site_url') . $options['slug'] . '/edit/' . $onepage['dignity_blogs_id'] . '">';
					}
					
					$out .= $onepage['dignity_blogs_title'] . '</a> ';
		                        
		            $out .= '</h1>';
	            $out .= '</div>';
	                        		
	            // если вошел автор записи
		       	if ($onepage['dignity_blogs_comuser_id'] == getinfo('comusers_id'))
		       	{
		            // выводим ссылку «редактировать»
		            $out .= '<div class="blogs_info_edit">';
						$out .= '<p>';
						$out .= '<span>';
						$out .= '<img src="' . getinfo('plugins_url') . 'dignity_blogs/img/edit.png' . '" alt="">';
						$out .= '</span>';
						$out .= '<a href="' . getinfo('site_url') . $options['slug'] . '/edit/' . $onepage['dignity_blogs_id'] . '" title="' . t('Редактировать статью', __FILE__) . '">' . t('Редактировать', __FILE__) . '</a>';
						$out .= '</p>';
					$out .= '</div>';
				}
			
	            // выводим анонс статьи
				$out .= '<div class="blogs_info_cuttext">';
					$out .= '<p>' . blogs_cleantext($onepage['dignity_blogs_cuttext']) . '</p>';
				$out .= '</div>';
			
	            // если нет текста, скрываем ссылку «подробнее»
				if ($onepage['dignity_blogs_text'])
				{
					// показываем ссылку «подробнее»
					$out .= '<div class="blogs_info_text">';
						$out .= '<p>';
						$out .= '<a href="' . getinfo('site_url') . $options['slug'] . '/view/' . $onepage['dignity_blogs_id'] . '" title="' . t('Показать всю статью', __FILE__) . '">' .
							t('Подробнее →', __FILE__) . '</a>';
						$out .= '</p>';
					$out .= '</div>';
				}
				
				$out .= '<div class="blogs_info"></div>';

				$out .= '<div class="break"></div>';

			$out .= '</div><!--div class="blog_page_only"-->';
        }
		
		echo $out;

		mso_hook('pagination', $pag);

	}
	else
	{
		echo '<p>' . t('Нет записей. Создайте вашу первую запись!', __FILE__) . '</p>';	
	}
}
else
{
	echo '<h1>' . tf('404. Ничего не найдено...') . '</h1>';
	echo '<p>' . tf('Извините, ничего не найдено') . '</p>';
	echo mso_hook('page_404');
}

// конец шаблона
if ($fn = mso_find_ts_file('main/main-end.php')) require($fn);

#end of file
