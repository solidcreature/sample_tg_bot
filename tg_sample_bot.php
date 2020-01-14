<?php
/*
Plugin Name: Telegram Sample Bot
Plugin URI: https://github.com/solidcreature/sample_tg_bot/
Description: Минималистичный телеграм-бот. Основа для создания новых ботов
Version: 0.2
Author: Nikolay Mironov
Author URI: http://wpfolio.ru
*/

/////
//Подгружаем страницы настроек плагина и дополнительные типы записей
define( 'TG_SAMPLE_DIR', plugin_dir_path( __FILE__ ) );
define( 'TG_SAMPLE_URL', plugin_dir_url( __FILE__ ) );

include TG_SAMPLE_DIR . '/inc/plugin-options.php';
include TG_SAMPLE_DIR . '/inc/post-types.php';

include TG_SAMPLE_DIR . '/inc/acf-groups.php';


/////
//Получаем токен чат-бота из настроек плагина 
$options = get_option('tg_bot_options');
$tg_bot_token = $options['tg_bot_token'];
define( 'TG_SAMPLE_TOKEN', $tg_bot_token );


/////
//Задаем end-поинт для общения между сайтом и чат-ботом
add_action( 'rest_api_init', function(){

	register_rest_route( 'myplugin/v1', '/tg_sample_bot', [
		'methods'  => 'post',
		'callback' => 'tg_main_function',
	] );

} );

/////
//Ключевая функция, которая обрабатывает сообщения из ТГ
function tg_main_function() {
	
	$body = file_get_contents('php://input'); 
	$arr = json_decode($body, true); 
	
	$message = $arr['message']['text']; 
	$peer_id = $arr['message']['chat']['id'];
	
	//Идентифицируем пользователя и его статус
	//Если нет участника с указанным peer_id, то будет добавлен новый
	$person_id = tg_get_person_id($peer_id, $arr);
	$status = get_field('person_status',$person_id);
	
	//Проверяем является ли отправленное пользователем сообщение командой или нет
	$not_command = true;
	
	//Выполняем комманды
	if ($message == '/start') {
		tg_send_greetings($person_id, $peer_id);
		$not_command = false;
	}
	
	elseif ($message == 'Зарегистрироваться') {
		tg_person_registration($person_id, $peer_id, $message);
		$not_command = false;
	}
	
	elseif ($message == 'Показать информацию') {
		tg_show_info($person_id, $peer_id);
		$not_command = false;
	}
	
	elseif ($message == 'Начать сначала') {
		tg_person_restart($person_id, $peer_id);
		$not_command = false;
	}
	
	
	//Выполняем все остальное
	if ($not_command) {
		
		if ($status == 'registration') {
			tg_person_registration($person_id, $peer_id, $message);
		}
		
		elseif ($status == 'registered') {
			$text = 'Спасибо, что попробовали TG Sample Bot!';
			
			$rows = array();
			$rows[] = array('Начать сначала');
			$rows[] = array('Показать информацию');

			
			$keyboard = array(
				'one_time_keyboard' => false,
				'resize_keyboard' => true,
		    	'keyboard' => $rows
			);
			
			tg_send_w_keyboard($peer_id, $text, $keyboard);	
		}
		
		else {
			$text = 'К сожалению мы не поняли команду';
			
			$rows = array();
			$rows[] = array('Начать сначала');
			$rows[] = array('Показать информацию');

			
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
function tg_send_greetings($person_id, $peer_id) {
	
	$text = 'Вас приветствует TG Sample Bot!';

	$rows = array();
	$rows[] = array('Зарегистрироваться');
			
	$keyboard = array(
		'one_time_keyboard' => true,
		'resize_keyboard' => true,
	  	'keyboard' => $rows
	);
	
	tg_send_w_keyboard($peer_id, $text, $keyboard);	
	
}

//Пошаговая регистрация
function tg_person_registration($person_id, $peer_id, $message) {
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
			update_field('person_status','registered', $person_id);
			
			$thankyou = 'Спасибо за регистрацию!';
			
			$rows = array();
			$rows[] = array('Начать сначала');
			$rows[] = array('Показать информацию');
			
			$keyboard = array(
				'one_time_keyboard' => false,
				'resize_keyboard' => true,
		    	'keyboard' => $rows
			);
			
			tg_send_w_keyboard($peer_id, $thankyou, $keyboard);		

		} else {
			tg_show_info($person_id, $peer_id);
		}

}

//Показать информацию о добавленном участнике
function tg_show_info($person_id, $peer_id) {
	$text = 'Вы уже зарегистрированы:' . PHP_EOL;
	$text .= get_field('person_name',$person_id) . ' ' . get_field('person_surname',$person_id) . ', ' . get_field('person_city',$person_id);
			
	tg_send($peer_id, $text);	
}


//Сбросить регистрацию пользователя
function tg_person_restart($person_id, $peer_id) {
	update_field('person_status','', $person_id);
	update_field('reg_step',0, $person_id);
	tg_send_greetings($person_id, $peer_id);
}


//Получить ID участника по идентификатору тг-чата, если нет, создать нового
function tg_get_person_id($peer_id, $arr) {
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
          
        curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot' . TG_SAMPLE_TOKEN .  '/' . $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST'); 
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); 
          
        $out = json_decode(curl_exec($curl), true); 
          
        curl_close($curl); 
          
        return $out; 
}
   
    
    
function tg_send_w_keyboard($peer_id, $message, $keyboard) {   
    	$message = tg_clear_tags($message); 
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
		$message = tg_clear_tags($message); 
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
 
 
function tg_clear_tags($text) {
	$clear = strip_tags($text, '<b><strong><i><em><u><ins><s><strike><del><a><code><pre>');
	return $clear;
}    
   
