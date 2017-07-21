<?php
	if (!defined('ABSPATH'))
		die("NO SCRIPT KIDDO'S");

	if ($_POST['wpuppy-action'] === 'reset-htaccess')
		file_put_contents(dirname(dirname(dirname(dirname(__FILE__)))) . "/.htaccess", "
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>

# END WordPress"
		);

	global $wpuppy;
	$requirements = $wpuppy->get_requirements();

	$accessible = $requirements['accessible'];
	$phpversion = $requirements['phpversion'];
	$php = $requirements['php'];
	$safemode = $requirements['safemode'];
	$htaccess = $requirements['htaccess'];
?>
<style>

	ul li .fa {
		font-size: 1.5em;
	}

	.fa-check {
		color: green;
	}

	.fa-warning {
		color: orange;
	}

	.fa-times {
		color: red;
	}

	ul ul {
		padding-left: 50px;
	}
</style>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<h2>WPuppy</h2>
<div class='wrapper'>
	Welcome to WPuppy! This is the website Dashboard!
</div>
<div id="requirements">
	<h3>Requirements checker</h3>
	<ul>
		<li>
			<?php echo "<i class='fa fa-" . ($accessible ? "check" : "times") . "'></i>";
			?> /wpuppy/api/ must be accessible
		</li>
		<?php
			if (!$accessible)
			{
				?>
				<ul>
					<li>
						<?php
							if (!$htaccess)
								echo "<div>It looks like you don't have an .htaccess file. You need one of those for WordPress to function correctly. Shall we generate a new one for you?</div>";
							else
								echo "<div>This is likely caused by a problem in the .htaccess file. We can reset that for you. If that doesn't help, make sure the WPuppy plugin is installed correctly.</div>";
						
						?>
						<form method="post">
							<input type="hidden" name="wpuppy-action" value="reset-htaccess">
							<input type="submit" value="Click here to reset the .htaccess file to the WordPress default">
						</form>
					</li>
				</ul>
				<?php
			}

		?>
		<li>
			<i class='fa fa-<?= ($php ? "check" : "warning"); ?>'></i>
			PHP version should be at least 5.4
		</li>
		<?php if (!$php): ?>
		<ul>
			<li>
				Current version is <?= $phpversion; ?>. Contact your hosting provider to upgrade your PHP version.
			</li>
			<li>
				<i class='fa fa-<?= ($safemode ? "times" : "check"); ?>'></i>
				PHP Safe mode must be turned off
			</li>
			<?php
				if ($safemode)
				{
					?>
					<ul>
						<li>
							You'll likely need to contact your hosting provider to turn this off.
						</li>
					</ul>
					<?php
				}

			?>
			<?php endif; ?>
		</ul>
	</ul>
</div>