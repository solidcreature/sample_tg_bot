<?php
//Регистрируем страницу настроек плагина в админ-панели
function tg_bot_register_options_page() {
  add_options_page('Настройки Телеграм-бота', 'Telegram Bot', 'manage_options', 'tg_bot', 'tg_bot_options_page');
}
add_action('admin_menu', 'tg_bot_register_options_page');


//Добавляем форму на страницу настроек плагина
function tg_bot_options_page()
{
	?>
	
	<div>
	<h2>Настройки Телеграм-бота</h2>
	
	<form action="options.php" method="post">
	<?php settings_fields('tg_bot_options'); ?>
	<?php do_settings_sections('plugin'); ?>
	 
	<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
	</form>
	
	
	<div>
		
		<?php 
			$site = site_url(); 
			$options = get_option('tg_bot_options');
			$tg_bot_token = $options['tg_bot_token'];
			
			$link = "https://api.telegram.org/bot$tg_bot_token/setWebhook?url=$site//wp-json/myplugin/v1/tg_sample_bot";
			
			if ($tg_bot_token):
				echo "<h3>Для работы бота необходимо подключить веб-хук, пройдя по этой ссылке:</h3>
				<p><a href='$link' target='_blank'>$link</a></p>
				<p>На сайте должен быть активный SSL-сертификат</p>
				";	
			endif;	
		?>	
	</div>
	
	
	</div>
	 
	<?php

} 


//Здесь начинаем добавлять настройки
function vk_options_fields(){
register_setting( 'tg_bot_options', 'tg_bot_options', 'plugin_options_validate' );
add_settings_section('plugin_main', 'Основные настройки', 'plugin_section_text', 'plugin');
add_settings_field('tg_bot_token', 'Токен бота', 'tg_token_func', 'plugin', 'plugin_main');
}

add_action('admin_init', 'vk_options_fields');


function tg_token_func() {
$options = get_option('tg_bot_options');
echo "<input id='tg_bot_token' name='tg_bot_options[tg_bot_token]' size='110' type='text' value='{$options['tg_bot_token']}' />";
} 
