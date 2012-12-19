<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * (c) Alexander Schilling
 * http://alexanderschilling.net
 */

// начало шаблона
require(getinfo('template_dir') . 'main-start.php');

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
	// загружаем данные из базы
	$CI->db->from('dignity_blogs');
	$CI->db->where('dignity_blogs_comuser_id', $id);
	$CI->db->order_by('dignity_blogs_datecreate', 'desc');
	$query = $CI->db->get();

	// если есть что выводить
	if ($query->num_rows() > 0)	
	{	
		$allpages = $query->result_array();
		
		$out = '';
		
                foreach ($allpages as $onepage) 
                {
                        $out .= '<div class="page_only">';
			
			$no_approved = '';
			if ($onepage['dignity_blogs_comuser_id'] == getinfo('comusers_id'))
			{
				if (!$onepage['dignity_blogs_approved'])
				{
					$no_approved .= '<span style="color:red;">?</span>';
				}
			}
		
                        $out .= '<div class="info info-top"><h1>' . $no_approved;
			
			if($onepage['dignity_blogs_approved'])
			{
				$out .= '<a href="' . getinfo('site_url') . $options['slug'] . '/view/' . $onepage['dignity_blogs_id'] . '">';
			}
			else
			{
				$out .= '<a href="' . getinfo('site_url') . $options['slug'] . '/edit/' . $onepage['dignity_blogs_id'] . '">';
			}
			
			$out .= $onepage['dignity_blogs_title'] . '</a> ';
                        
                        $out .= '</h1></div>';
                        		
                        // если вошел автор
                        if ($onepage['dignity_blogs_comuser_id'] == getinfo('comusers_id'))
                        {
                                // выводим ссылку «редактировать»
                                $out .= '<p><span style="padding-right:10px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/edit.png' . '"></span><a href="' . getinfo('site_url') . $options['slug'] . '/edit/' . $onepage['dignity_blogs_id'] . '">' . t('Редактировать', __FILE__) . '</a></p>';
                        }
		
                        $out .= '<p>' . blogs_cleantext($onepage['dignity_blogs_cuttext']) . '</p>';
		
                        // если нет текста, скрываем ссылку «подробнее»
                        if (!$onepage['dignity_blogs_text'])
                        {
                                $out .= '';
                        }
                        else
                        {
                                $out .= '<p style="padding-bottom:10px;"><a href="' . getinfo('site_url') . $options['slug'] . '/view/' . $onepage['dignity_blogs_id'] . '">' .
                                	t('Подробнее»', __FILE__) . '</a></p>';
                        }
			
			$out .= '<div class="info info-bottom"></div>';
			$out .= '<div class="break"></div>';
			$out .= '</div><!--div class="page_only"-->';
                }
		
		echo $out;
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
require(getinfo('template_dir') . 'main-end.php');

#end of file