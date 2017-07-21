<?php
if (!defined('ABSPATH'))
	die("NO SCRIPT KIDDOS");

?>
<h2>WPuppy Settings</h2>
<div class='wrapper'>
	<?php
		function WPuppy_generateRandomString($length = 10) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			return $randomString;
		}
		if (!$key = get_option('wpuppy_key')) {
			$success = false;
			if (isset($_REQUEST['generate']) && check_admin_referer("wpuppy_setup") && current_user_can("manage_options")) {
				$key = WPuppy_generateRandomString();
				update_option('wpuppy_key', $key);
				update_option('wpuppy_setup', "false");

				$site_url = str_replace("http://", "", str_replace("https://", "", site_url()));
				if (substr($site_url, -1) === "/")
					$site_url = substr($site_url, 0, -1);

				$secure_connection = false;

				if(isset($_SERVER['HTTPS'])) {
					if ($_SERVER['HTTPS'] == "on") {
						$secure_connection = true;
					}
				}

				echo "A WPuppy Key has been generated! Copy the information below into WPuppy to get started!<br />
				<strong>Website name (example):</strong> " . get_bloginfo('name') . "<br />
				<strong>Domain:</strong> {$site_url}<br />
				<strong>WPuppy Key:</strong> {$key}
				<strong>This website uses SSL:</strong> " . (($secure_connection) ? "checked on" : "checked off");
				$success = true;
			}
			if (!$success):
				?>
				<form method='post' action='<?php echo $_SERVER['REQUEST_URI']; ?>'>
					<?= wp_nonce_field("wpuppy_setup"); ?>
					<input type='submit' name='generate' value='Click to generate a WPuppy Key' />
				</form>
				<?php
			endif;
		} else {
			if (get_option('wpuppy_setup') === "false") {
				$site_url = str_replace("http://", "", str_replace("https://", "", site_url()));
				if (substr($site_url, -1) === "/")
					$site_url = substr($site_url, 0, -1);

				$secure_connection = false;

				if(isset($_SERVER['HTTPS'])) {
					if ($_SERVER['HTTPS'] == "on") {
						$secure_connection = true;
					}
				}
				echo "You have not yet setup WPuppy. Copy the information below into WPuppy to get started!<br />
				<strong>Website name (example):</strong> " . get_bloginfo('name') . "<br />
				<strong>Domain:</strong> {$site_url}<br />
				<strong>WPuppy Key:</strong> {$key}
				<strong>This website uses SSL:</strong> " . (($secure_connection) ? "checked on" : "checked off");
			} else {
				$success = false;
				if (isset($_REQUEST['remove']) && check_admin_referer("wpuppy_remove") && current_user_can("manage_options")) {
					if (delete_option('wpuppy_key') !== true || update_option("wpuppy_setup", "false") !== true) {
						echo "An error occurred.";
					} else {
						echo "Your linked WPuppy Account has been unlinked! You can add this site to a new account by generating a WPuppy key.";
						$success = true;
					}
				}
				if (!$success):
					?>
					Click the button below to remove this website from the linked WPuppy account.<br />
					<form method='post' action='<?php echo $_SERVER['REQUEST_URI']; ?>'>
						<?= wp_nonce_field("wpuppy_remove"); ?>
						<input type='submit' name='remove' value='Remove Link' />
					</form>
					<?php
				endif;
			}
		}
	?>
</div>