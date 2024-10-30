<script type="text/javascript">
function change_style() {
	var style = document.getElementById("style");
	var value = style.value;
	var mod_style = document.getElementById("mod_style");
	mod_style.value = value;
}
</script>
<?php

if ( isset($_POST['updateOptions']) ) {
	
	if ( $_POST['user'] == '') {
		$warning = '1';
		?>
		<div class="error"><p><strong><?php echo "Please enter username.\n";?></strong></p></div>
		<?php
	}
	if ( is_numeric(trim($_POST['size'])) === false ) {
		$warning = '1';
		?>
		<div class="error"><p><strong><?php echo "Please enter a number for the size.\n";?></strong></p></div>
		<?php
	}
	if ( is_numeric(trim($_POST['number'])) === false ) {
		$warning = '1';
		?>
		<div class="error"><p><strong><?php echo "Please enter a number for the number of artists shown.\n";?></strong></p></div>
		<?php
	}
	if ( trim($_POST['number']) > 20 ) {
		$warning = '1';
		?>
		<div class="error"><p><strong><?php echo "Please enter a resonable number for the number of artists shown.\n";?></strong></p></div>
		<?php
	}
	
	if ( $warning != '1' ) {
		
		$size = trim(round(abs($_POST['size'])));
		$number = trim(round(abs($_POST['number'])));
		$title = strip_tags(stripslashes($_POST['title']));
		$mod_style = strip_tags(stripslashes($_POST['mod_style']));
		$user = trim(strip_tags($_POST['user']));
		
		if ( str_replace(' ', '', $mod_style) ==  str_replace(' ', '', $_POST['style']) ) {
			$style = $_POST['style'];
		}else{
			$style = $mod_style;
		}
		
		$data = array('user' => $user,
					  'size' => $size,
					  'number' => $number,
					  'title' => $title,
					  'style' => $style,
					  'display' => $_POST['display'],
					  'type' => $_POST['type'],
					  'time' => $_POST['time']);		
		
		if ( update_option(OPTIONS_NAME, $data) ) {
			?>
			<div class="updated"><p><strong><?php echo "Settings Updated.\n";?></strong></p></div>
			<?php
		}else{
			?>
			<div class="updated"><p><strong><?php echo "No Settings Were Changed.\n";?></strong></p></div>
			<?php
		}
	}
} 
if ( isset($_POST['clearCache']) ) {
	foreach ( glob(ROOT_DIRECTORY.'*') as $filename ) {
		unlink($filename);
	}
	?>
		<div class="updated"><p><strong><?php echo "All cached files deleted.\n"; ?></strong></p></div>
	<?php
}
$info = get_option(OPTIONS_NAME);
?>
<div class="wrap">
	<h2>General Options</h2>Have Questions: <a href="plugins.php?page=lastfm-artists/help.php">Support</a> | <a href="http://finalstar.net/lastfm/instructions">Instructions</a>
	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">Title of Widget:</th> 
					<td><input type="text" name="title" value="<?php echo $info['title']; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Last.fm Username:</th> 
					<td>
						<input type="text" name="user" value="<?php echo $info['user']; ?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Image Type:</th> 
					<td>
						<select name="type">
							<?php
								$choices = array('square', 'wide');
								foreach ( $choices as $choice ) {
									if ( $choice == $info['type'] ) {
										?><option value="<?php echo $choice; ?>" selected="selected"><?php echo ucfirst($choice) ?></option><?php
									} else {
										?><option value="<?php echo $choice; ?>"><?php echo ucfirst($choice); ?></option><?php
									}
								}
							?>
						</select>
						<br />
						Images can be either square or wide.
						<br />
						Wide images will be 30px wider than the height set below.
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Image Size:</th> 
					<td>
						<input type="text" name="size" value="<?php echo $info['size']; ?>" />px
						<br />
						This sets the height of the image.
						<br />
						Only one value is needed.  Wide images will be 30px wider than the height.
						<br />
						Square images will, of course, have the same width as height.
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Number of Images Shown:</th>
					<td>
						<input type="text" name="number" value="<?php echo $info['number']; ?>" />
						<br />
						Values 1 - 20 are accepted.
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Select your time period</th>
					<td>
						<select name="time">
						<?php
							$times = array('Overall', 'Weekly', '3 Month', '6 Month', '12 Month', 'Recent', 'Loved');
							foreach ( $times as $time ) {
								$tmptime = preg_replace('/\s+/', '', strtolower($time));
								if ( $tmptime == $info['time'] ) {
									?><option value="<?php echo $tmptime; ?>" selected="selected"><?php echo $time; ?></option><?php
								} else {
									?><option value="<?php echo $tmptime; ?>"><?php echo $time; ?></option><?php
								}
							}
						?>
						</select>
						<br />
						<strong>Overall</strong> displays your top artists since you've been registered.
						<br />
						<strong>Weekly</strong> displays your top artists over the past 7 days.
						<br />
						<strong>3 Month</strong>, <strong>6 Month</strong>, <strong>12 Month</strong> displays your top artists over the given timeframe.
						<br />
						<strong>Recent</strong> displays the artists of your recently listened to tracks.
						<br />
						<strong>Loved</strong> displays the artists of your recently loved tracks.
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Display Information:</th> 
					<td>
						<select name="display">
							<?php
								$choices = array('yes', 'no');
								foreach ( $choices as $choice ) {
									if ( $choice == $info['display'] ) {
										?><option value="<?php echo $choice; ?>" selected="selected"><?php echo ucfirst($choice) ?></option><?php
									} else {
										?><option value="<?php echo $choice; ?>"><?php echo ucfirst($choice); ?></option><?php
									}
								}
							?>
						</select>
						<br />
						You can choose to display the artist's name and number of plays, time last played for recent artists, or time you loved a track for loved artists.
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Select Style:</th> 
					<td>
						<select id="style" name="style" onchange="change_style(this)">
							<?php
								foreach ( glob(PLUGIN_DIRECTORY.'/*.css') as $style ) {
									$name = end(explode(PLUGIN_DIRECTORY.'/', $style));
									$contents = file_get_contents($style);
									if (  preg_replace('/\s+/', '', strtolower($contents)) ==  preg_replace('/\s+/', '', strtolower($info['style'])) ) {
										?><option value="<?php echo $contents; ?>" selected="selected"><?php echo $name ?></option><?php
										$set = 1;
									} else {
										?><option value="<?php echo $contents; ?>"><?php echo $name; ?></option><?php
									}
								}
								if ( $set != 1 ) {
									?><option value="<?php echo $info['style']; ?>" selected="selected">Custom</option><?php
								}
							?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Modify Style:</th> 
					<td>
						<textarea rows=10 cols=50 name="mod_style" id="mod_style"><?php
							echo $info['style'];								
						?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="action" value="update" />
		<p class="submit">
			<input type="submit" name="updateOptions" value="<?php echo ("Update Options") ?>" />
			<input type="submit" name="clearCache" value="<?php echo ("Clear Cache") ?>" />
		</p>
	</form>
</div>
