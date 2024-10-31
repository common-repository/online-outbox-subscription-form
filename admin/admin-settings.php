<?php

global $oosf_options;

if ($_POST['action'] && $_POST['action'] == 'oosf_update' && $_POST['Submit']!='') {

	$fields_string_array = array(
		'oosf_username' => $_POST['oosf_username'],
		'oosf_password' => $_POST['oosf_password'],
	);

	foreach($fields_string_array as $key => $val) { $fields_string .= $key . '=' . $val . '&'; }
	rtrim($fields_string);

	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, 'http://www.onlineoutbox.com/scripts/returntoken.php');
	curl_setopt($ch,CURLOPT_POST,count($fields_string_array));
	curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);
	
	$response = explode(';', $output);

	if($response[0] == 'SUCCESS') {
		
		define(API_TOKEN_SUCCESS, true);
		$oosf_api_token = $response[1];

		$oosf_update_array = array(
				'oosf_username' => $_POST['oosf_username'],
				'oosf_password' => $_POST['oosf_password'],
				'oosf_api_token' => $oosf_api_token,
				'oosf_api_key_valid' => 1,
			);

		update_option('oosf_options', $oosf_update_array);
				
		if (function_exists('wp_cache_flush')) {
			wp_cache_flush();
		}

		$oosf_options = get_option('oosf_options');

	} else {
		
		define(API_TOKEN_SUCCESS, false);
		$form_error[] = $response[1];

	}
	
}


?> 

<div class="wrap">

	<h2>Online Outbox Settings</h2>

	<h3>Login Info</h3>

	<div style="width:500px">

		<p><strong>Api Token:</strong> <?= $oosf_options['oosf_api_token'] ? "<span style='color:green'>" . $oosf_options['oosf_api_token'] . '</span>' : "<span style='color:red'>Not Set</span>"; ?></p>

		<p>To start using the Online Outbox plugin, we first need to login and get your API Key. Please enter your Online Outbox username and password below.<br />
		  <br />
	    Don't have a Online Outbox account? <a href="http://www.onlineoutbox.com/register/" target="_blank">Register here</a> for a 30-day free trial.*</p>
        

		<? if(isset($form_error)): ?>

			<p class="error" style="color:red"><br />There was an error retrieving your API Token:<br /><br />&bull; <?= ucwords($form_error[0]) ?><br /><br /></p>

		<? elseif($_POST['action'] && $_POST['action'] == 'oosf_update' && $_POST['Submit']!='' && !isset($form_error)): ?>
			
			<p class="success" style="color:green">Your API Token was successfully retrieved and set.</p>

		<? endif; ?>

		<form action="admin.php?page=oosf-settings" method="post">

			<table class="form-table">
			<tr>
				<td>Username:</td>
				<td><input type="text" name="oosf_username" value="<?php echo stripcslashes($oosf_options['oosf_username']); ?>" /></td>
			</tr>
			<tr>
				<td>Password:</td>
				<td><input type="password" name="oosf_password" value="<?php echo stripcslashes($oosf_options['oosf_password']); ?>" /></td>
			</tr>
			</table>

			<input type="hidden" value="oosf_update" name="action">
			
			<p><input type="submit" class="button" value="Save &amp; Check" name="Submit"></p>

		</form>

        <br /><hr />
        <table><tr><td valign="top"><img src="http://www.onlineoutbox.com/images/lgo_OnlineOutbox.png" /></td><td><img src="http://yourdesignonline.com/images/_spacer.gif" width="10" /></td><td>
        <p>Use of this plugin requires a membership at Online Outbox to function. Accounts sending fewer than 1,000 emails per month are free.
        </p>
        <p>
            <strong>Online Outbox</strong> is an email marketing platform that enables you to stay in the forefront of the minds of your customers and potential customers. Online Outbox allows you to:<br />
                <blockquote>
                1. Personalize your customer relations. <br />
                2. Send any type of email communication. <br />
                3. Improve sales and increase revenue. <br />
                4. Build trust with your subscribers. <br />
                6. Create an additional revenue stream. <br />
                7. Comply with email laws. <br /><br />
                </blockquote>
            
            <strong>Get more info on how Online Outbox can work for you!</strong><br />
                <blockquote>
                <a href="http://www.onlineoutbox.com/features/" target="_blank">Features</a><br />
                <a href="http://www.onlineoutbox.com/about/pricing/" target="_blank">Pricing</a><br />
                <a href="http://www.onlineoutbox.com/contact/" target="_blank">Contact</a><br />
                <a href="http://www.onlineoutbox.com/register/" target="_blank">Register for a 30 day free trial</a>*<br />
                </blockquote>
            
            <em>*Accounts sending fewer than 1,000 emails per month are free.</em>
        </p>
        
        <p>
        <strong>Have an issue with Online Outbox or the plugin?</strong> We're here to help!<br />
        Please either<br />
        1) Visit our Support Forum <a href="http://forum.onlineoutbox.com/" target="_blank">here</a>, or
        2) Submit a Support Ticket <a href="http://support.onlineoutbox.com/" target="_blank">here</a><br />
        </p>
        
        <p>Much thanks to <a href="http://wordpress.org/extend/plugins/profile/takayukister" target="_blank">Takayuki Miyoshi</a>, developer of <a href="http://wordpress.org/extend/plugins/contact-form-7/" target="_blank">Contact Form 7</a> plugin, which served as a foundation for futhered development. 
        </p>
		</td></tr></table>

	</div>

</div>