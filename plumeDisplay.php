<html>
	<head>
		<title>PHP Display Tests</title>
	</head>
	<body>
		<p>
			<form method="post" action="#" name="PlumeSelectForm">
				<?php
					// Get available plume output graphics directories by date
					$dateDirs = glob('../plumes/*', GLOB_ONLYDIR);
					// Empty dates array to populate with options for the drop-down menu.
					$dates = array();
					// Create datetime objects from directory names and create options for a drop-down menu.
					foreach ($dateDirs as $dir) {
						//echo substr($dir,count($dir)-9,8);
						// The datetime object.
						$date = DateTime::createFromFormat('!Ymd',substr($dir,count($dir)-9,8));
						// TODO: 2 lines of debug code to be removed later
						echo '<p>' . $dir . '</p>';
						echo '<p>' . $date->format('Y-m-d') . '</p>';
						// Add the option to the array.
						array_push($dates,$date->format('Y-m-d'));
					}
					// Sort dates by most recent first.
					rsort($dates);
				?>
				<select name="dd1" id="dd1" onchange="PlumeSelectForm.submit();">
					<!--<option selected="selected">Choose</option>-->
					<?php
						foreach($dates as $date) {?>
							<?php
								if(array_search($date,$dates) === 0){?>
									<option selected="<?php echo $date ?>"><?php echo $date ?></option>
							<?php
								}
								else {?>
									<option value="<?php echo $date ?>"><?php echo $date ?></option>
							<?php
								}
							?>
					<?php
						} 
					?>
				</select>
				<?php
					$hourDirs = glob('../plumes/' . substr($_POST['dd1'],0,4) . substr($_POST['dd1'],5,2) . substr($_POST['dd1'],8,2) . '/*', GLOB_ONLYDIR);
					$hours = array();
					foreach ($hourDirs as $dir) {
						echo '<p> ' . $dir . '</p>';
						echo '@@@' . substr($dir,count($dir)-4,3);
						
					}
				?>
				<!--
				<select name="dd2" id="dd2">
					?php 
						foreach($times as $time) {?>
							<option value="?php echo $time ?>">?php echo $time ?></option>
					?php
						}
					?>
				-->
		</p>
	</body>
</html>