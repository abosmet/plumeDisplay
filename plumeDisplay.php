<html>
	<head>
		<title>Ensemble Plume Display on Purgatorio</title>
	</head>
	<body>
		<noscript>WARNING: This page requires javascript to function.</noscript>
		<?php
			/*
			 * This script generates menus for and displays plume diagrams
			 *   stored according to the following:
			 *   ./plumes/<YYYYMMDD>/<HH>Z/<place name>,<state>.png
			 */
			// DEBUG is used for printing information helpful for debugging.
			const DEBUG = false;
			/*
			 * Create default selection based on latest available graphics.
			 * Populate the form and summon the latest graphic.
			 * 
			 * First, create and fill the following arrays:
			 * plumeDates is a single-level array with available dates
			 * plumeHours is a 2-level array with available times accessible by
			 *             date in the form YYYYMMDD. Times are stored in the
			 *             form HHZ, where Z is the Z character.
			 * plumeStations is a 3-level array with available station names
			 *             accessible by date YYYYMMDD and hour HHZ where Z is
			 *             the Z character. Station names are stored in the
			 *             form <place name>,<state> e.g. Scranton,PA
			 *             
			 * Using rsort() on the list of date directory names puts the
			 *   latest date at index 0.
			 * Using rsort() on the list of hour directories puts the latest
			 *   hour at index 0.
			 * Using sort() on the list of station names puts the first
			 *   alphabetical station at index 0.
			 * By using sorts in this way, the order that the OS returns the
			 *   directories using glob won't matter.
			 *   
			 * TODO: Distinguish between dates for GEFS and SREF systems.
			 *         May change directory structure to accomodate this.
			 *
			 */
			$plumeDates = array();
			$plumeHours = array();
			$plumeStations = array();
			// 3-level foreach loop defining all of these arrays.
			foreach(glob('./plumes/*',GLOB_ONLYDIR) as $dateDir){
				if(DEBUG){
					echo '<p> DT' . $dateDir . '</p>';
				}
				// Date will be stored as its directory name without path.
				$dateString = substr($dateDir, count($dateDir) - 9, 8);
				if(DEBUG){
					echo '<p> DTS' . $dateString . '</p>';
				}
				// Add the date to the list of dates.
				array_push($plumeDates,$dateString);
				// Create second level arrays for hours and stations with the
				//   current date as an index.
				$plumeHours[$dateString] = array();
				$plumeStations[$dateString] = array();
				foreach(glob($dateDir . '/*',GLOB_ONLYDIR) as $hourDir){
					if(DEBUG){
						echo '<p> HR' . $hourDir . '</p>';
					}
					// Hour will be stored as its directory name without path.
					$hourString = substr($hourDir, count($hourDir) - 4, 3);
					// Add the hour to the list of hours for this date.
					array_push($plumeHours[$dateString],$hourString);
					// Create third level array for stations with the current
					//   date and hour as indexes.
					$plumeStations[$dateString][$hourString] = array();
					foreach(glob($hourDir . '/*') as $plumeImagePath){
						if(DEBUG){
							echo '<p> IMG' . $plumeImagePath . '</p>';
						}
						// The station name is stored as its place name and
						//   state without path or file extension.
						$stationName = substr(substr($plumeImagePath,22),0,count(substr($plumeImagePath,22)) - 5);
						// Add the station name to the list of station names for
						//   this date and hour.
						array_push($plumeStations[$dateString][$hourString],$stationName);
					}
				}
			}
			// Sort dates by latest first.
			//   Latest date may be accessed by $plumeDates[0]
			rsort($plumeDates);
			if(DEBUG){
				echo '<p> Latest Date: ' . $plumeDates[0] . '</p>';
			}
			// Sort hours by latest first. 
			//   Latest may be accessed by $plumeHours[<date>][0]
			//   where <date> is a string date from $plumeDates form: YYYYMMDD.
			rsort($plumeHours[$plumeDates[0]]);
			if(DEBUG){
				echo '<p> Latest Hour: ' . $plumeHours[$plumeDates[0]][0] . '</p>';
			}
			/*
			 * Explanation of form creation. . .
			 * When the page is first loaded, the dropdown menus should be
			 *   initialized with the latest date and time selected, along with
			 *   the first alphabetical station in the station list for that
			 *   date and time.
			 *   The latest date may be accessed using $plumeDates[0]
			 *   The latest hour may be accessed using $plumeHours[<date>][0]
			 *   The first alphabetical station may be accessed using
			 *     $plumeStations[<date>][<hour>][0]
			 *   The following substring may be used to get the station name
			 *     from a path to a plume image file.
			 *   substr(substr($plumeImagePath,22),0,count(substr($plumeImagePath,22)) - 5)
			 *   
			 * When the form is submitted, the page is reloaded with POST data.
			 *   This POST data may be parsed to determine selected options
			 *   as the dropdown menus are being created.
			 * 
			 * Note that the form elements use javascript to submit the form
			 *   every time an element is changed.
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
						 * function defineHours() eliminates duplicate code.
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
						/*
						 * Define a dropdown menu for available graphics based
						 *   on the initial or selected date and time.
						 * function defineGraphics() eliminates duplicate code.
						 */
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
						/* 
						 * Page was loaded for one of two reasons:
						 * 1: The page was loaded because the form was
						 *   submitted. Inputs must be checked for validity.
						 * 2: The page was loaded for the first time. All
						 *   aspects should be loaded as default.
						 */
						// Case 1:
						// Check if POST data is available, use defaults if not.
						if(isset($_POST['PlumeSelectDate']) and isset($_POST['PlumeSelectHour'])){
							// Check if selected hour is valid. If not, use
							//   latest available hour.
							if(in_array($_POST['PlumeSelectHour'],$plumeHours[$_POST['PlumeSelectDate']])){
								defineGraphics($_POST['PlumeSelectDate'],$_POST['PlumeSelectHour'],$plumeStations);
							}
							else{
								defineGraphics($_POST['PlumeSelectDate'],$plumeHours[$_POST['PlumeSelectDate']][0],$plumeStations);
							}
						}
						// Case 2:
						// Use all defaults.
						else{
							defineGraphics($plumeDates[0],$plumeHours[$plumeDates[0]][0],$plumeStations);
						}
					?>
				</select>
			</form>
		</p>
		<p>
			<?php 
				/* 
				 * Page was loaded for one of two reasons:
				 * 1: The page was loaded because the form was submitted.
				 *   Inputs must be checked for validity.
				 * 2: The page was loaded for the first time. All aspects
				 *   should be loaded as default.
				 * 
				 * Case 1:
				 * First, hour must be in the date's hours array.
				 * Next, station must be in the date and hour's stations array.
				 * This assumes all dates are valid.
				 */
				// Case 1:
				if(isset($_POST['PlumeSelectDate']) && isset($_POST['PlumeSelectHour']) && isset($_POST['PlumeSelectStation'])){
					$imageDate = $_POST['PlumeSelectDate'];
					// Check if hour is valid.
					if(in_array($_POST['PlumeSelectHour'],$plumeHours[$imageDate])){
						$imageHour = $_POST['PlumeSelectHour'];
					}
					else{
						$imageHour = $plumeHours[$imageDate][0];
					}
					// Check if station is valid.
					if(in_array($_POST['PlumeSelectStation'],$plumeStations[$imageDate][$imageHour])){
						$imageStation = $_POST['PlumeSelectStation'];
					}
					else{
						$imageStation = $plumeStations[$imageDate][$imageHour][0];
					}
				}
				// Case 2:
				// Use all defaults.
				else{
					$imageDate = $plumeDates[0];
					$imageHour = $plumeHours[$imageDate][0];
					$imageStation = $plumeStations[$imageDate][$imageHour][0];
				}
				$imagePath = '../plumes/' . $imageDate . '/' . $imageHour . '/' .  $imageStation . '.png';
				?>
				<img src="<?php echo $imagePath?>" alt="Plume Image">
		</p>
	</body>
</html>