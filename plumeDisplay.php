<html>
	<head>
		<title>Ensemble Plume Display on Purgatorio</title>
	</head>
	<body>
		<?php
			set_error_handler('custom_error_handler');
			//
			function custom_error_handler($severity, $message, $filename, $lineno){
				if (error_reporting() == 0) {
					return;
				}
				if (error_reporting() & $severity) {
					throw new ErrorException($message, 0, $severity, $filename, $lineno);
				}
			}
			// Create default selection based on latest available graphics.
			// Populate form and summon latest graphic.
			// First, find the latest date by rsort of a list of date directories.
			// Next, find the latest time by rsort of a list of hour directories.
			// Finally, select the first alphabetical station graphic by sort of a list of graphics.
			//
			// TODO: Distinguish between dates for GEFS and SREF systems.
			//
			// plumeDates is a single-level array with available dates
			// plumeHours is a 2-level array with available times accessible by
			//             date in the form YYYYMMDD.
			// plumePaths is a 3-level array with available graphics accessible
			//             by date YYYYMMDD and hour HHZ where Z is the Z
			//             character.
			$plumeDates = array();
			$plumeHours = array();
			$plumeStations = array();
			foreach(glob('./plumes/*',GLOB_ONLYDIR) as $dateDir){
				//echo '<p> DT' . $dateDir . '</p>';
				$dateString = substr($dateDir, count($dateDir) - 9, 8);
				//echo '<p> DTS' . $dateString . '</p>';
				array_push($plumeDates,$dateString);
				$plumeHours[$dateString] = array();
				$plumeStations[$dateString] = array();
				foreach(glob($dateDir . '/*',GLOB_ONLYDIR) as $hourDir){
					//echo '<p> HR' . $hourDir . '</p>';
					$hourString = substr($hourDir, count($hourDir) - 4, 3);
					array_push($plumeHours[$dateString],$hourString);
					$plumeStations[$dateString][$hourString] = array();
					foreach(glob($hourDir . '/*') as $plumeImagePath){
						//echo '<p> IMG' . $plumeImagePath . '</p>';
						$stationName = substr(substr($plumeImagePath,22),0,count(substr($plumeImagePath,22)) - 5);
						array_push($plumeStations[$dateString][$hourString],$stationName);
					}
				}
			}
			// Sort dates by latest first. Latest may be accessed by $plumeDates[0]
			rsort($plumeDates);
			//echo '<p> Latest Date: ' . $plumeDates[0] . '</p>';
			// Sort hours by latest first. Latest may be accessed by $plumeHours[<date>][0]
			//   where <date> is a string date from $plumeDates form: YYYYMMDD.
			rsort($plumeHours[$plumeDates[0]]);
			//echo '<p> Latest Hour: ' . $plumeHours[$plumeDates[0]][0] . '</p>';
			/*
			 * Explanation of form creation. . .
			 * When the page is first loaded, the dropdown menus should be
			 *   initialized with the latest date and time selected, along with
			 *   the first alphabetical station in the station list for that
			 *   date and time.
			 *   The latest date may be accessed using $plumeDates[0]
			 *   The latest hour may be accessed using 
			 *     $plumeHours[<date>][0]
			 *   The first alphabetical station may be accessed using
			 *     $plumeStations[<date>][<hour>][0]
			 *   The following substring may be used to get the station name
			 *     from a path to a plume image file.
			 *   substr(substr($plumeImagePath,22),0,count(substr($plumeImagePath,22)) - 5)
			 *   
			 * When the form is submitted, the page is reloaded with POST data.
			 *   This POST data may be parsed to determine selected options
			 *   as the dropdown menus are being created.
			 */
			// Begin form definition.
		?>
		<p>
			<form method="post" action="#" name="PlumeSelectForm">
				<select name="PlumeSelectDate" onchange="PlumeSelectForm.submit();">
					<?php
						/*
						 * Define a dropdown menu from available dates.
						 * Proc the selected tag if a selection for this menu
						 *   is in the POST data.
						 * isset is checked first on the POST data because on
						 *   the initial load of the page POST is not defined,
						 *   and the attempt to access it results in an error.
						 */
						foreach($plumeDates as $date) {
							if(isset($_POST['PlumeSelectDate']) && $_POST['PlumeSelectDate'] === $date){?>
								<option value="<?php echo $date ?>" selected="selected"><?php echo $date ?></option>
							<?php
							}
							else{?>
								<option value="<?php echo $date ?>"><?php echo $date ?></option>
							<?php
							}
						}
					?>
				</select>
				<select name="PlumeSelectHour" onchange="PlumeSelectForm.submit();">
					<?php
						/*
						 * Define a dropdown menu for available hours based on
						 *   the initial or selected date.
						 */
						function defineHours($date_,$plumeHours_){
							foreach($plumeHours_[$date_] as $hour){
								if(isset($_POST['PlumeSelectHour']) && $_POST['PlumeSelectHour'] === $hour){?>
									<option value="<?php echo $hour ?>" selected="selected"><?php echo $hour ?></option>
								<?php 
								}
								else{?>
									<option value="<?php echo $hour ?>"><?php echo $hour ?></option>
								<?php
								}
							}
						}
						// Define a dropdown menu from available times based on date selected.
						// If no date selected, use most recent date.
						if(isset($_POST['PlumeSelectDate'])){
							defineHours($_POST['PlumeSelectDate'],$plumeHours);
						}
						else{
							defineHours($plumeDates[0],$plumeHours);
						}
					?>
				</select>
				<select name="PlumeSelectStation" onchange="PlumeSelectForm.submit();">
					<?php
						function defineGraphics($date_,$hour_,$plumeStations_){
							foreach($plumeStations_[$date_][$hour_] as $station){
								if(isset($_POST['PlumeSelectStation']) && $_POST['PlumeSelectStation'] === $station){?>
									<option value="<?php echo $station ?>" selected="selected"><?php echo $station ?></option>
								<?php
								}
								else{?>
									<option value="<?php echo $station ?>"><?php echo $station ?></option>
								<?php
								}
							}
						}
						/* Define a dropdown menu from available graphics based
						 *   on date and time selected.
						 * 4 Cases: X - Selected, O - Not Selected
						 *     Date  Hour  Number
						 *   -   X     X     1
						 *   -   X     O     2
						 *   -   O     X     3
						 *   -   O     O     4
						 * Case 1: Use selected date and time.
						 * Case 2: Use selected date, latest time.
						 * Case 3: Use latest date, selected time.
						 * Case 4: Use latest date, latest time.
						 */
						// Case 1:
						//   This errors when the date changes and an invalid hour is selected.
						//   Custom error handler defined at the top of this file to take care of that.
						if(isset($_POST['PlumeSelectDate']) and isset($_POST['PlumeSelectHour'])){
							try{
								defineGraphics($_POST['PlumeSelectDate'],$_POST['PlumeSelectHour'],$plumeStations);
							} catch (ErrorException $e){
								defineGraphics($_POST['PlumeSelectDate'],$plumeHours[$_POST['PlumeSelectDate']][0],$plumeStations);
							}
						}
						// Case 2:
						elseif(isset($_POST['PlumeSelectDate']) and !isset($_POST['PlumeSelectHour'])){
							defineGraphics($_POST['PlumeSelectDate'],$plumeHours[$_POST['PlumeSelectDate']][0],$plumeStations);
						}
						// Case 3:
						elseif(!isset($_POST['PlumeSelectDate']) and isset($_POST['PlumeSelectHour'])){
							defineGraphics($plumeDates[0],$_POST['PlumeSelectHour'],$plumeStations);
						}
						// Case 4:
						else{
							defineGraphics($plumeDates[0],$plumeHours[$plumeDates[0]][0],$plumeStations);
						}
					?>
				</select>
			</form>
		</p>
		<p>
			<?php 
				/* 8 Cases: X - Selected, O - Not Selected
				 *     Date  Hour  Graphic  Number
				 *   -   X     X      X       1
				 *   -   X     X      O       2
				 *   -   X     O      X       3
				 *   -   X     O      O       4
				 *   -   O     X      X       5
				 *   -   O     X      O       6
				 *   -   O     O      X       7
				 *   -   O     O      O       8
				 * Case 1: Summon Selection
				 * Case 2: Summon selected date/hour, top alphabetical
				 * Case 3: Summon selected date/graphic, latest hour
				 *         Must check if graphic is valid.
				 * Case 4: Summon selected date, latest hour, top alphabetical
				 * Case 5: Summon latest date, selected hour/graphic
				 *         This should not happen because hour is defined based on date.
				 * Case 6: Summon latest date, selected hour, top alphabetical
				 * Case 7: Summon latest date/hour, selected graphic
				 *         This shouldn't happen because graphic is defined based on date and hour.
				 * Case 8: Summon latest date/hour, top alphabetical
				 */
				if(isset($_POST['PlumeSelectDate']) && isset($_POST['PlumeSelectHour']) && isset($_POST['PlumeSelectStation'])){
					$imagePath = '../plumes/' . $_POST['PlumeSelectDate'] . '/' . $_POST['PlumeSelectHour'] . '/' .  $_POST['PlumeSelectStation'] . '.png';
				}
				else{
					$imageDate = $plumeDates[0];
					$imageHour = $plumeHours[$imageDate][0];
					$imageStation = $plumeStations[$imageDate][$imageHour][0];
					$imagePath = '../plumes/' . $imageDate . '/' . $imageHour . '/' .  $imageStation . '.png';
				}?>
				<img src="<?php echo $imagePath?>" alt="Plume Image">
		</p>
	</body>
</html>