<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * (c) Alexander Schilling
 * http://alexanderschilling.net
 */

// начало шаблона
require(getinfo('template_dir') . 'main-start.php');

// загружаем опции
$options = mso_get_option('plugin_dignity_blogs', 'plugins', array());
if (!isset($options['noapproved']))  $options['noapproved'] = true;
if (!isset($options['slug']))  $options['slug'] = 'blogs';
if ( !isset($options['cackle_code']) ) $options['cackle_code'] = '';

// получаем доступ к CI
$CI = & get_instance();

// выводим меню
blogs_menu();

// проверка сегмента
$id = mso_segment(3);
if (!is_numeric($id)) $id = false;
else $id = (int) $id;

// если число
if ($id)
{
	
	$CI->db->from('dignity_blogs');
	$CI->db->where('dignity_blogs_id', $id);
	$CI->db->where('dignity_blogs_approved', true);
	$CI->db->join('dignity_blogs_category', 'dignity_blogs_category.dignity_blogs_category_id = dignity_blogs.dignity_blogs_category', 'left');
	$CI->db->join('comusers', 'comusers.comusers_id = dignity_blogs.dignity_blogs_comuser_id', 'left');
	$query = $CI->db->get();

	// если есть что выводить
	if ($query->num_rows() > 0)	
	{	
		$allpages = $query->result_array();
		
		$out = '';
		
		foreach ($allpages as $onepage) 
		{
			
			//<- подсчёт количества просмотров через cookie
			
			global $_COOKIE;
			$name_cookies = 'dignity-blogs';
			$expire = 2592000;
			$slug = getinfo('siteurl') . $options['slug'] . '/' . mso_segment(2) . '/' . mso_segment(3);
			$all_slug = array();
			
			if (isset($_COOKIE[$name_cookies]))
			{
				$all_slug = explode('|', $_COOKIE[$name_cookies]);
			}
			
			if (in_array($slug, $all_slug))
			{
				false;
			}
			else
			{
				$all_slug[] = $slug;
				$all_slug = array_unique($all_slug);
				$all_slug = implode('|', $all_slug);
				$expire = time() + $expire;
				
				@setcookie($name_cookies, $all_slug, $expire);
				$page_view_count = $onepage['dignity_blogs_views'] + 1;
				
				$CI->db->where('dignity_blogs_id', $id);
				$CI->db->update('dignity_blogs', array('dignity_blogs_views'=>$page_view_count));
			}
			
			//-> конец подсчёта количества просмотров через cookie
			
			//<- выводим запись
			
			$out .= '<div class="page_only">';
		
				$out .= '<div class="info info-top">';
					$out .= '<h1><a href="' . getinfo('site_url') . $options['slug'] . '">' . $onepage['dignity_blogs_title'] . '</a></h1>';
				$out .= '</div>';
				
			// если вошел автор
			if ($onepage['dignity_blogs_comuser_id'] == getinfo('comusers_id'))
			{
				// выводим ссылку «редактировать»
				$out .= '<p><span style="padding-right:10px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/edit.png' . '"></span><a href="' . getinfo('site_url') . $options['slug'] . '/edit/' . $onepage['dignity_blogs_id'] . '">' . t('Редактировать', __FILE__) . '</a></p>';
			}
		
			$out .= '<span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/user.png' . '"></span> <a href="' . getinfo('site_url') . $options['slug'] . '/blog/' . $onepage['dignity_blogs_comuser_id'] . '">' . t('Блог им. ', __FILE__) . $onepage['comusers_nik'] . '</a>';
			
			$out .= '<p>' . blogs_cleantext($onepage['dignity_blogs_cuttext']) . '</p>';
			$out .= '<p>' . blogs_cleantext($onepage['dignity_blogs_text']) . '</p>';
		
			$out .= '<div class="info info-bottom">'
				. '<span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/views.png' . '" title="' . t('Просмотров', __FILE__) . '"></span>' . $onepage['dignity_blogs_views'] . ' | '
				. '<span style="padding-right:5px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/public.png' . '" title="' . t('Дата публикации', __FILE__) . '"></span>' . mso_date_convert($format = 'd.m.Y → H:i', $onepage['dignity_blogs_datecreate']);
				if ($onepage['dignity_blogs_category_id'])
				{
					$out .= ' | ' . '<span style="padding-right:0px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/ordner.png' . '" title="' . t('Категория', __FILE__) . '"></span>' . ' <a href="' . getinfo('site_url') . $options['slug'] . '/category/' . $onepage['dignity_blogs_category_id'] . '">' . $onepage['dignity_blogs_category_name'] . '</a>';
				}
				else
				{
					$out .= ' | ' . '<span style="padding-right:0px;"><img src="' . getinfo('plugins_url') . 'dignity_blogs/img/ordner.png' . '" title="' . t('Категория', __FILE__) . '"></span>' . ' <a href="' . getinfo('site_url') . $options['slug'] . '">' . t('Все записи', __FILE__) . '</a>';
				}
				
				$out .= blogs_yandex_share();
				
			$out .= '</div>';
			
			$out .= '<div class="break"></div>';
			
			$out .= '</div>';
			
			//-> конец вывода записи
		}
		
		// выводим запись
		echo $out;
		
		// meta-тэги
		mso_head_meta('title', $onepage['dignity_blogs_title']);
		mso_head_meta('description', $onepage['dignity_blogs_description']);
		mso_head_meta('keywords', $onepage['dignity_blogs_keywords']);

		//<- выводим комментарии
		
		$pag = array();
		$pag['limit'] = 10;
		$CI->db->select('dignity_blogs_comments_id');
		$CI->db->from('dignity_blogs_comments');
		$CI->db->where('dignity_blogs_comments_approved', true);
		$CI->db->where('dignity_blogs_comments_thema_id', $id);
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

		$CI->db->from('dignity_blogs_comments');
		$CI->db->where('dignity_blogs_comments_approved', true);
		$CI->db->where('dignity_blogs_comments_thema_id', $id);
		$CI->db->order_by('dignity_blogs_comments_datecreate', 'asc');
		$CI->db->join('comusers', 'comusers.comusers_id = dignity_blogs_comments.dignity_blogs_comments_comuser_id', 'left');
		if ($pag and $offset) $CI->db->limit($pag['limit'], $offset);
		else $CI->db->limit($pag['limit']);
		$query = $CI->db->get();

		// если есть что выводить...
		if ($query->num_rows() > 0)	
		{
			$allcomments = $query->result_array();
	
			$comments_out = '';
			
			$comments_out .= '<div class="leave_a_comment">Комментарии через наш сайт:</div>';
			
			$comments_out .= '<ol>';
	
			foreach ($allcomments as $onecomment) 
			{
				$avatar = '';
				if ($onecomment['comusers_avatar_url'])
				{
					$avatar = $onecomment['comusers_avatar_url'];
				}
				else
				{
					$avatar = getinfo('plugins_url') . 'dignity_blogs/img/noavatar.jpg';
				}
				
				$comments_out .= '<div class="type type_page_comments">
				<div class="comments">
				<li style="clear: both" class="users">
					<div class="comment-info">
					<span class="date"><img src="' . $avatar . '" height="40px" width="40px" style="padding:3px 15px 3px 0px;">' .
					t('Комментарий от ', __FILE__) . '<a href="' . getinfo('site_url') . 'users/' . $onecomment['comusers_id'] . '">' . $onecomment['comusers_nik'] . '</a>' . ' в ' . mso_date_convert($format = 'H:i → d.m.Y', $onecomment['dignity_blogs_comments_datecreate']) . '</span></div>
					<div class="comments_content"><p>' . blogs_cleantext($onecomment['dignity_blogs_comments_text']) . '</p></div>
				</li></div>
				<div class="break"></div>
				</div>';
			}
			
			$comments_out .= '</ol>';
			
			// выводим комментарии
			echo $comments_out;
	
			// добавляем пагинацию
			mso_hook('pagination', $pag);
		}
		else
		{
			echo '<div class="leave_a_comment">Комментарии через наш сайт:</div>';
			echo '<p>' . t('Нет комментариев. Ваш будет первым!', __FILE__) . '</p>';
		}
		
		 // если комюзер
		if (is_login_comuser() && $onepage['dignity_blogs_comments'])
		 {
            
			// если пост
			if ( $post = mso_check_post(array('f_session_id', 'f_submit_dignity_blogs_comments_add')) )
			{
				// id == 3 сегмент
				$id = mso_segment(3);
                        
				// проверяем реферала
				mso_checkreferer();
				
				// смотрим опции - вкл или откл проверка комментарий
				$no_approved = '';
				if($options['noapproved'])
				{
					$no_approved = 1;
				}
				else
				{
					$no_approved = 0;
				}

				// массивы для добавления в базу данных
				$ins_data = array (
				        'dignity_blogs_comments_text' => htmlspecialchars($post['f_dignity_blogs_comments_text']),
				        'dignity_blogs_comments_datecreate' => date('Y-m-d H:i:s'),
				        'dignity_blogs_comments_dateupdate' => date('Y-m-d H:i:s'),
				        'dignity_blogs_comments_thema_id' => $id,
				        'dignity_blogs_comments_approved' => $no_approved,
				        'dignity_blogs_comments_comuser_id' => getinfo('comusers_id'),
				);
				
				$res = ($CI->db->insert('dignity_blogs_comments', $ins_data)) ? '1' : '0';
                        
				if ($res)
				{
					echo '<div class="update">';
					echo t('Комментарий добавлен!', __FILE__);
					
					if (!$options['noapproved'])
					{
						echo '<br>' . t('После проверки он будет опубликован.', __FILE__);
					}
					echo '</div>';
					
					if ($options['noapproved'])
					{
						echo '<script>location.replace(window.location); </script>';
					}
				}
				else echo '<div class="error">' . t('Ошибка добавления в базу данных...', __FILE__) . '</div>';
		
				 // сбрасываем кеш
				mso_flush_cache();
				
			 }
			else
			{
			        $form = '';     
				$form .= '<h2>' . t('Оставьте комментарий!', __FILE__) . '</h2>';     
				$form .= '<form action="" method="post">' . mso_form_session('f_session_id');
				$form .= '<p><strong>' . t('Текст (можно использовать bb-code):', __FILE__) . '<span style="color:red;">*</span></strong><br><textarea name="f_dignity_blogs_comments_text" class="markItUp"
					cols="80" rows="10" value="" required="required" style="margin-top: 2px; margin-bottom: 2px; "></textarea>';
				$form .= '<p><input type="submit" class="submit" name="f_submit_dignity_blogs_comments_add" value="' . t('Отправить', __FILE__) . '"></p>';
				$form .= '</form>';
                        
				// выводим форму
				echo $form;
			}

			if ($options['cackle_code'])
			{
				echo '<div class="leave_a_comment">Комментарии через социальные сети:</div>';
				echo $options['cackle_code'];
			}
		}
		else
		{
			// если не комюзер
			if (!is_login_comuser())
			{
			     echo '<p style="border:solid 1px #DBE0E4; padding:10px; background:#FFFFE1;">' . t('Чтобы оставить свой комментарий, вам нужно', __FILE__) . ' <a href="' . getinfo('siteurl') . 'registration">' . t('зарегистироваться', __FILE__) . '</a> ' . t('или',__FILE__) . ' <a href="' . getinfo('siteurl') . 'login">' . t('войти на сайт', __FILE__) . '.</a></p>';	

				if ($options['cackle_code'])
				{
					echo '<div class="leave_a_comment">Комментарии через социальные сети:</div>';
					echo $options['cackle_code'];
				}

			}
			else
			{
				echo '<p>' . t('Эту запись нельзя комментировать.', __FILE__) . '</p>';
			}
		}
		
	}
	else
	{
		echo '<h1>' . tf('404. Ничего не найдено...') . '</h1>';
		echo '<p>' . tf('Извините, ничего не найдено') . '</p>';
		echo mso_hook('page_404');
		
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
