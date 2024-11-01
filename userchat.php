<?php
/**
 * Plugin Name: UserChat
 * Author: UserChat
 * Author URI: www.userchat.online
 * Plugin URI: https://userchat.online/
 * Description: UserChat - Chat via messengers
 * Version: 1.1.0
 * Text Domain: userchat
 * Domain Path: /lang
 */
 
register_activation_hook(__FILE__, 'userchat_install'); //hook при активации плагина

add_action( 'plugins_loaded', function(){
	load_plugin_textdomain( 'userchat', false, dirname( plugin_basename(__FILE__) ) . '/lang' );
} );

add_action('admin_menu', 'userchat_plugin_page'); //Создание страницы настроек
 
add_action('admin_menu', 'userchat_admin_script'); //Подключение script в админку

add_action('admin_menu', 'userchat_admin_style'); //Подключение style в админку
 
add_action( 'wp_footer', 'userchat_script_footer' ); //Добавляем скрипт в footer
 
register_uninstall_hook(__FILE__, 'userchat_uninstall'); //hook при удалении плагина
 
function userchat_install(){
	add_option('userchat_cf7_tel', 'your-tel');
}
 
function userchat_admin_style() {
	wp_register_style('userchat_admin_style', plugins_url('css/admin-page.css', __FILE__));
	wp_enqueue_style('userchat_admin_style');
}

function userchat_admin_script() {
	wp_register_script('userchat_admin_script', plugins_url('js/admin-page.js', __FILE__));
	wp_enqueue_script('userchat_admin_script');
}

function userchat_script_footer(){ 
	 $user_id = get_option("userchat_user_id");
	 $enable_chat = get_option("userchat_enable_chat");
	 if ($enable_chat == 1){
	 ?>
		<script type='text/javascript'>
		var userId = '<?php echo $user_id; ?>';
		(function(){
			var wg = document.createElement('script');
			wg.type ='text/javascript';
			wg.charset = 'utf-8';
			wg.async = true;
			wg.src = 'https://userchat.online/widget/script.js?userId=' + userId;
			var pl = document.getElementsByTagName('script')[0];
			if (pl) pl.parentNode.insertBefore(wg, pl);
			else document.documentElement.firstChild.appendChild(wg);
	})();
	</script>
	<script type='text/javascript' src='https://userchat.online/widget/chat.js'></script>
	<div id='wiget-output'></div>

<?php }
}

function userchat_uninstall(){
	delete_option('userchat_user_id');
	delete_option('userchat_cf7_tel');
	delete_option('userchat_cf7_title');
	delete_option('userchat_cf7_id');
 }
 
function userchat_plugin_page(){
	add_options_page('UserChat', 'UserChat', 8, 'userchat', 'userchat_options_page');
}

function userchat_options_page() {
	userchat_save_main_settings(); 
	userchat_save_cf7_settings();?>
	<h2><?php _e("Настройки плагина", "userchat"); ?></h2>
	<ul class="tabs">
            <li class="active"><?php _e("Общие настройки", "userchat"); ?></li>
            <li><?php _e("Обратный звонок через формы Contact Form7", "userchat"); ?></li>
        </ul>
        <div class="tabs_divs">
            <div class="active">
			<!-- Form 1 основные настройки -->
			<?php userchat_main_form(); ?>
            </div>
			<!-- Form 2 cf7-->
            <div>
              <?php userchat_cf7_form(); ?>
            </div>
        </div>
	
<?php }

function userchat_main_form(){ ?>

	<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>?page=userchat&amp;updated=true'>
		<?php
		if (function_exists ('wp_nonce_field') ){
			wp_nonce_field('userchat_main_form'); 
		}
		?>
		<table>
			<tr>
				<td><?php _e("Включить", "userchat"); ?></td>
				<td><input type='checkbox' name='userchat_enable_chat' value='1' <?php checked( 1, get_option("userchat_enable_chat") ) ?>/></td>
			</tr>
			<tr>
				<td><?php _e("ID чата", "userchat"); ?></td>
				<td><input type='text' name='userchat_user_id' id='userchat_user_id' required size="1" value="<?php echo get_option('userchat_user_id'); ?>"></td>
			</tr>
		</table>
		<input type='submit' name='userchat_save_btn' class="button-primary" value='<?php _e("Сохранить изменения", "userchat"); ?>' style="cursor: pointer;"/>
	</form>
	<p><?php _e('Введите здесь идентификатор пользователя, полученный <a href="https://userchat.online/account/chat/" target="_blank">в настройках чата</a>&nbsp;сервиса <a href="https://userchat.online/" target="_blank">UserChat.online</a>. Если у Вас еще нет учетной записи, выберите и активируйте подходящий <a href="https://userchat.online/shop/" target="_blank">тариф</a>.', 'userchat'); ?></p>
<?php }

function userchat_cf7_form(){ ?>
<?php 
		if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) { ?>
		
	<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>?page=userchat&amp;updated=true'>
		<?php
		if (function_exists ('wp_nonce_field') ){
			wp_nonce_field('userchat_cf7_form'); 
		}
		
		$title = get_option('userchat_cf7_title');
		$cf7_id = get_option('userchat_cf7_id');
		$cf7_tel = get_option('userchat_cf7_tel');
		$cf7_id_value = implode(",", $cf7_id);
		?>
		<table class="cf7_info">
			<tr>
				<td><?php _e("Включить", "userchat"); ?></td>
				<td><input type='checkbox' name='userchat_enable_cf7' value='1' <?php checked( 1, get_option("userchat_enable_cf7") ) ?>/></td>
			</tr>
			<tr>
				<td><?php _e("Форма", "userchat"); ?></td>
				<td>
					<select multiple= "multiple" name="userchat_cf7_title" id="userchat_cf7_title">
						<?php
						$wpb_all_query = new WP_Query(array('post_status'=>'publish', 'post_type'=>'wpcf7_contact_form')); 
						if ( $wpb_all_query->have_posts() ) :
							while ( $wpb_all_query->have_posts() ) : $wpb_all_query->the_post();
								$title = get_the_title();
								$post_id = get_the_ID();
								if (in_array($post_id, $cf7_id))
									echo "<option value='{$post_id}' selected>{$title}</option>";
								else echo "<option value='{$post_id}'>{$title}</option>";
							 endwhile;
						endif;
						wp_reset_postdata();
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td><?php _e("Идентификатор поля \"Номер телефона\"", "userchat"); ?></td>
				<td><input type="text" name="userchat_cf7_tel" value = "<?php echo $cf7_tel; ?>" required></td>
			</tr>
			<tr>
				<td><input type="hidden" name="userchat_cf7_id" id = "userchat_cf7_id"  value = "<?php echo $cf7_id_value; ?>"></td>
			</tr>
		</table>
		<input type='submit' name='userchat_save_cf7_btn' class="button-primary" value='<?php _e("Сохранить изменения", "userchat"); ?>' style="cursor: pointer;"/>
	</form>
	<p><?php _e('Для осуществления обратного звонка у вас долна быть активна подписка <a href="https://userchat.online/product-category/callback/" target="_blank">Обратный звонок</a>.', 'userchat'); ?></p>
		<?php } else echo "Плагин Contact Form 7 не активирован. <a href='/wp-admin/plugins.php' target='_blank'>Активировать</a>"?>
<?php }

function userchat_save_main_settings(){
		if (isset($_POST['userchat_save_btn'])) 

	{   

	   if ( function_exists('current_user_can') && 

			!current_user_can('manage_options') )

				die ( _e('Hacker?', 'userchat') );

		if (function_exists ('check_admin_referer') )
		{

			check_admin_referer('userchat_main_form');

		}
		
		//Обновление данных
		$enable_chat = $_POST['userchat_enable_chat'];
		$user_id = $_POST['userchat_user_id'];
		if (is_numeric($user_id) && $user_id > 0){
			$user_id = (int)$user_id;
			update_option('userchat_user_id', $user_id);
		} else if ($user_id == ""){
			update_option('userchat_user_id', $user_id);
		}
		
		if (($enable == 1) || ($enable == ""))
			update_option('userchat_enable_chat', $enable_chat);
	}
}

function userchat_save_cf7_settings(){
		if (isset($_POST['userchat_save_cf7_btn'])) 

	{   

	   if ( function_exists('current_user_can') && 

			!current_user_can('manage_options') )

				die ( _e('Hacker?', 'userchat') );

		if (function_exists ('check_admin_referer') )
		{

			check_admin_referer('userchat_cf7_form');

		}
		
		//Обновление данных
		$title = $_POST['userchat_cf7_title'];
		$cf7_id = $_POST['userchat_cf7_id'];
		$cf7_tel = $_POST['userchat_cf7_tel'];
		$enable_cf7 = $_POST['userchat_enable_cf7'];
		
		if (($enable == 1) || ($enable == ""))
			update_option('userchat_enable_cf7', $enable_cf7);
		
		 
		$cf7_id = explode(",", sanitize_text_field($cf7_id));
		update_option('userchat_cf7_id', $cf7_id);
	
		update_option('userchat_cf7_title', sanitize_text_field( $title ));
		
		update_option('userchat_cf7_tel', sanitize_text_field( $cf7_tel ));
		
	}
}

add_action( 'wpcf7_mail_sent', 'userchat_cf7_mail_sent' );

function userchat_cf7_mail_sent( $contact_form ) {
	
	$id = $contact_form->id;
	$posted_data = $contact_form->posted_data;
	
	$cf7_id = get_option('userchat_cf7_id');
	$cf7_tel = get_option('userchat_cf7_tel');
	$user_id = get_option('userchat_user_id');
	$enable_cf7 = get_option('userchat_enable_cf7');
	
	
	if ((in_array($id,$cf7_id)) && (!empty($cf7_id)) && (!empty($cf7_tel)) && (!empty($user_id)) && ($enable_cf7 == 1) ) { 
		
		$submission = WPCF7_Submission::get_instance();
		$posted_data = $submission->get_posted_data();
		
		$phone = $posted_data["{$cf7_tel}"]; 
		
		$url = 'https://userchat.online/callback/callback.php';
		$params = array(
			'phone' => $phone, 
			'id' => $user_id, 
		);
		$result = file_get_contents($url, false, stream_context_create(array(
			'http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query($params)
			)
		)));  

		return $result;
	}
}

add_filter( 'wpcf7_before_send_mail', 'userchat_before_cf7_mail' );

function userchat_before_cf7_mail($cf7){
	$id = $cf7->id;
	$cf7_id = get_option('userchat_cf7_id');
	$cf7_tel = get_option('userchat_cf7_tel');
	$user_id = get_option('userchat_user_id');
	$enable_cf7 = get_option('userchat_enable_cf7');
	
	if ((in_array($id,$cf7_id)) && (!empty($cf7_id)) && (!empty($cf7_tel)) && (!empty($user_id)) && ($enable_cf7 == 1) ) { 
		$mail=$cf7->prop('mail');
	
		$text = __( 'По данному номеру телефона уже был произведен обратный звонок. Если вы пропустили его, то перезвоните.', 'userchat');
	
		$mail['body'] .= "<p>{$text}</p>";
	
		$cf7->set_properties(array('mail'=>$mail));
	}
}