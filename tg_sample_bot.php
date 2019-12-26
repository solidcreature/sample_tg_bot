<?php
/*
Plugin Name: Telegram Sample Bot
Plugin URI: 
Description: 
Version: 
Author: Nikolay Mironov
Author URI: http://wpfolio.ru
License: 
License URI: 
*/

/////
//Подгружаем страницы настроек плагина и дополнительные типы записей
define( 'PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_URL', plugin_dir_url( __FILE__ ) );

include PLUGIN_DIR . '/inc/plugin-options.php';
include PLUGIN_DIR . '/inc/post-types.php';

include PLUGIN_DIR . '/inc/acf-groups.php';


/////
//Получаем токен чат-бота из настроек плагина 
$options = get_option('tg_bot_options');
$tg_bot_token = $options['tg_bot_token'];
define( 'BOT_TOKEN', $tg_bot_token );


/////
//Задаем end-поинт для общения между сайтом и чат-ботом
add_action( 'rest_api_init', function(){

	register_rest_route( 'myplugin/v1', '/tg_sample_bot', [
		'methods'  => 'post',
		'callback' => 'my_awesome_func',
	] );

} );

/////
//Ключевая функция, которая обрабатывает сообщения из ТГ
function my_awesome_func() {
	
	$body = file_get_contents('php://input'); 
	$arr = json_decode($body, true); 
	
	//$data = print_r($arr, true);
	//update_field('sample_data', $data, 210);

	$message = $arr['message']['text']; 
	$peer_id = $arr['message']['chat']['id'];
	
	//Идентифицируем пользователя и его статус
	$person_id = get_person_id($peer_id, $arr);
	$status = get_field('person_status',$person_id);
	
	$not_command = true;
	
	//Выполняем комманды
	if ($message == '/start') {
		send_greetings($person_id, $peer_id);
		$not_command = false;
	}
	
	elseif ($message == 'Зарегистрироваться') {
		person_registration($person_id, $peer_id, $message);
		$not_command = false;
	}
	
	elseif ($message == 'Показать информацию') {
		show_info($person_id, $peer_id);
		$not_command = false;
	}
	
	elseif ($message == 'Начать сначала') {
		person_restart($person_id, $peer_id);
		$not_command = false;
	}
	
	
	//Выполняем все остальное
	if ($not_command) {
		
		if ($status == 'registration') {
			person_registration($person_id, $peer_id, $message);
		}
		
		else {
			$text = 'К сожалению мы не поняли команду';
			
			$row1 = array('Начать сначала');
			$row2 = array('Показать информацию');
			$rows = array($row1, $row2);
			
			$keyboard = array(
				'one_time_keyboard' => false,
				'resize_keyboard' => true,
		    	'keyboard' => $rows
			);
			
			tg_send_w_keyboard($peer_id, $text, $keyboard);	
		}
	}	
	
	//Обязательно возвращаем "ok", чтобы телеграмм не подумал, что запрос не дошёл
	exit('ok'); 
}




//Отправить приветствие и вывести базовые команды
function send_greetings($person_id, $peer_id) {
	
	$text = 'Вас приветствует TG Sample Bot!';


	$row1 = array('Зарегистрироваться');
	$rows = array($row1);
			
	$keyboard = array(
		'one_time_keyboard' => true,
		'resize_keyboard' => true,
	  	'keyboard' => $rows
	);
	
	tg_send_w_keyboard($peer_id, $text, $keyboard);	
	
}

//Пошаговая регистрация
function person_registration($person_id, $peer_id, $message) {
		update_field('person_status','registration', $person_id);

		$reg_step = get_field('reg_step',$person_id);
		
		if ($reg_step == 0) {
			$welcome = 'Начнем регистрацию!';
			tg_send($peer_id, $welcome);
			
			$question = 'Ваше имя?';
			tg_send($peer_id, $question);
			update_field('reg_step',1,$person_id);
			return;
		} 
		
		elseif ($reg_step == 1) {
			update_field('person_name', $message, $person_id);			
			
			$question = 'Ваша Фамилия?';
			tg_send($peer_id, $question);
			update_field('reg_step',2,$person_id);
			return;
		}
		
		elseif ($reg_step == 2) {
			update_field('person_surname', $message, $person_id);			
			
			$question =  'Ваш город?';
			tg_send($peer_id, $question);
			update_field('reg_step',3,$person_id);			
			return;
		}		
		
		elseif ($reg_step == 3) {
			update_field('person_city', $message, $person_id);			
			update_field('reg_step',4,$person_id);
			
			$text = 'Ваш часовой пояс?';

			$row1 = array('UTC+1, Калининград'); 
			$row2 = array('UTC+2, Санкт-Петербург'); 
			$row3 = array('UTC+3, Москва'); 
			$row4 = array('UTC+4, Екатеринбург'); 
			$row5 = array('UTC+5, Омск'); 
			$row6 = array('UTC+6, Красноярск'); 
			$row7 = array('UTC+7, Иркутск'); 
			$row8 = array('UTC+8, Чита'); 
			$row9 = array('UTC+9, Хабаровск'); 
			$row10 = array('UTC+10, Магадан'); 
			$row11 = array('UTC+11'); 
			$row12 = array('UTC+12'); 
			
			
			$rows = array($row1, $row2, $row3, $row4, $row5, $row6, $row7, $row8, $row9, $row10, $row11, $row12);
			
			$keyboard = array(
				'one_time_keyboard' => true,
				'resize_keyboard' => true,
		    	'keyboard' => $rows
			);
			
			tg_send_w_keyboard($peer_id, $text, $keyboard);	
			return;
		} 
		
		elseif ($reg_step == 4) {
			$updated = false;
			if (($message == 'UTC+1, Калининград') or ($message == '+1') or ($message == '1')) {
				update_field('person_timezone', 1, $person_id);	
				$updated = true;
			} elseif (($message == 'UTC+2, Санкт-Петербург') or ($message == '+2') or ($message == '2')) {
				update_field('person_timezone', 2, $person_id);	
				$updated = true;
			} elseif (($message == 'UTC+3, Москва') or ($message == '+3') or ($message == '3')) {
				update_field('person_timezone', 3, $person_id);	
				$updated = true;
			} elseif (($message == 'UTC+4, Екатеринбург') or ($message == '+4') or ($message == '4')) {
				update_field('person_timezone', 4, $person_id);	
				$updated = true;
			} elseif (($message == 'UTC+5, Омск') or ($message == '+5') or ($message == '5')) {
				update_field('person_timezone', 5, $person_id);	
				$updated = true;
			} elseif (($message == 'UTC+6, Красноярск') or ($message == '+6') or ($message == '6')) {
				update_field('person_timezone', 6, $person_id);	
				$updated = true;
			} elseif (($message == 'UTC+7, Иркутск') or ($message == '+7') or ($message == '7')) {
				update_field('person_timezone', 7, $person_id);	
				$updated = true;
			} elseif (($message == 'UTC+8, Чита') or ($message == '+8') or ($message == '8')) {
				update_field('person_timezone', 8, $person_id);	
				$updated = true;
			} elseif (($message == 'UTC+9, Хабаровск') or ($message == '+9') or ($message == '9')) {
				update_field('person_timezone', 9, $person_id);
				$updated = true;
			} elseif (($message == 'UTC+10, Магадан') or ($message == '+10') or ($message == '10')) {
				update_field('person_timezone', 10, $person_id);
				$updated = true;
			} elseif (($message == 'UTC+11') or ($message == '+11') or ($message == '11')) {
				update_field('person_timezone', 11, $person_id);
				$updated = true;
			} elseif (($message == 'UTC+12') or ($message == '+12') or ($message == '12')) {
				update_field('person_timezone', 12, $person_id);
				$updated = true;
			} 
			
			if (!$updated) { update_field('person_timezone', 3, $person_id); }
			
			update_field('reg_step',5,$person_id);
			update_field('person_registered',true,$person_id);
			update_field('person_status','select',$person_id);
			
			$thankyou = 'Спасибо за регистрацию!';
			
			$row1 = array('Начать сначала');
			$row2 = array('Показать информацию');
			$rows = array($row1, $row2);
			
			$keyboard = array(
				'one_time_keyboard' => false,
				'resize_keyboard' => true,
		    	'keyboard' => $rows
			);
			
			tg_send_w_keyboard($peer_id, $thankyou, $keyboard);				
		
		
		} else {
			show_info($person_id, $peer_id);
		}

}

//Показать информацию о добавленном участнике
function show_info($person_id, $peer_id) {
	$text = 'Вы уже зарегистрированы:' . PHP_EOL;
	$text .= get_field('person_name',$person_id) . ' ' . get_field('person_surname',$person_id) . ', ' . get_field('person_city',$person_id) . ' UTC+' . get_field('person_timezone',$person_id);
	update_field('person_status','', $person_id);
			
	tg_send($peer_id, $text);
	
}


//Сбросить регистрацию пользователя
function person_restart($person_id, $peer_id) {
	update_field('person_status','', $person_id);
	update_field('reg_step',0, $person_id);
	send_greetings($person_id, $peer_id);
}


//Получить ID участника по идентификатору тг-чата, если нет, создать нового
function get_person_id($peer_id, $arr) {
	$args = array(
		'post_type' => 'tg_person',
		'meta_key' => 'person_tg',
		'meta_value' => $peer_id
	);
			
	$query = new WP_Query($args);
	
	if ( $query->have_posts() )	{
		while ( $query->have_posts() ) : $query->the_post();
			$person_id = get_the_ID();
		endwhile; 
		
		return $person_id;
		
	} else {
		
	
		$f_name = $arr['message']['from']['first_name'];
		$l_name = $arr['message']['from']['last_name'];
		
		$post_data = array(
		'post_title'    => $f_name . ' ' . $l_name,
		'post_content' => '',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type' => 'tg_person'
		);

		// Вставляем запись в базу данных
		$person_id = wp_insert_post( $post_data );
		
		update_field('person_tg', $peer_id, $person_id);
		update_field('reg_step', 0, $person_id);
		
		return $person_id;
	}
	
	wp_reset_postdata();
}







function tg_request($method, $data = array()) {
        $curl = curl_init(); 
          
        curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot' . BOT_TOKEN .  '/' . $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST'); 
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); 
          
        $out = json_decode(curl_exec($curl), true); 
          
        curl_close($curl); 
          
        return $out; 
}
   
    
    
function tg_send_w_keyboard($peer_id, $message, $keyboard) {   
    	$message = clear_tags($message); 
    	$replyMarkup = json_encode($keyboard);
    	
        $data = array(
            'chat_id'      => $peer_id,
            'text'     => $message,
            'parse_mode' => 'HTML',
            'reply_markup' => $replyMarkup,
        );

        $out = tg_request('sendMessage', $data);
        return $out;
    }     
    
    
function tg_send($peer_id, $message) { 
		$message = clear_tags($message); 
        $data = array(
            'chat_id'      => $peer_id,
            'text'     => $message,
            'parse_mode' => 'HTML',
            
        );
        $out = tg_request('sendMessage', $data);
        return $out;
    }
   
function tg_send_photo($peer_id, $photo) { 
        $data = array(
            'chat_id'      => $peer_id,
            'photo'     => $photo,
            'caption' => ''
        );
        $out = tg_request('sendPhoto', $data);
        return $out;	
}	
 


//Преобразует дату в формате ‘Y-m-d H:i:s’ в юникс-время с поправкой на часовой поя   
function person_time($time, $person_id) {
	$hour = 3600;
	$shift = 3;
	
	if (get_field('person_timezone', $person_id)) $shift = get_field('person_timezone', $person_id);
	$person_shift = $hour * $shift; 

	return $time - $person_shift; 
}  

 
    
function clear_tags($text) {
	$clear = str_replace("<p>", "", $text);
	$clear = str_replace("</p>", " 
	", $clear);
	$clear = str_replace("<br/>", " 
	", $clear);
	$clear = str_replace("<br />", " 
	", $clear);
	$clear = str_replace("<br>", " 
	", $clear);
	$clear = str_replace("<ol>", " 
	", $clear);
	$clear = str_replace("</ol>", " 
	", $clear);
	$clear = str_replace("<ul>", " ", $clear);
	$clear = str_replace("</ul>", " ", $clear);
	$clear = str_replace("<li>", "— ", $clear);
	$clear = str_replace("</li>", " ", $clear);
	$clear = str_replace("<h1>", " ", $clear);
	$clear = str_replace("</h1>", " ", $clear);
	$clear = str_replace("<h2>", " ", $clear);
	$clear = str_replace("</h2>", " ", $clear);
	$clear = str_replace("<h3>", " ", $clear);
	$clear = str_replace("</h3>", " ", $clear);
	$clear = str_replace("<h4>", " ", $clear);
	$clear = str_replace("</h4>", " ", $clear);
	$clear = str_replace("<h5>", " ", $clear);
	$clear = str_replace("</h5>", " ", $clear);	
	$clear = str_replace("<h6>", " ", $clear);
	$clear = str_replace("</h6>", " ", $clear);	
	$clear = str_replace("&nbsp;", "", $clear);		

	return $clear;
}    
   