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
		$paths = glob('./plumes/*/*/*/*');
		// 0: ., 1: plumes, 2: Date, 3: Hour, 4: Type, 5: File
		$plumeDates = array();
		$plumeHours = array();
		$plumeTypes = array();
		$plumeStations = array();
		foreach($paths as $path){
			$parts = explode('/', $path);
			$date = $parts[2];
			$hour = $parts[3];
			$type = $parts[4];
			$file = $parts[5];
			$stationName = substr($file, 0, count($file) - 5);
			if(DEBUG){
				echo '<p>' . $date . ' ' . $hour . ' ' . $type . ' ' . $stationName . '</p>';
			}
			if(!in_array($date,$plumeDates)){
				if(DEBUG){
					echo '<p> Adding Date: ' . $date . '</p>';
				}
				array_push($plumeDates, $date);
				$plumeHours[$date] = array();
				$plumeTypes[$date] = array();
				$plumeStations[$date] = array();
			}
			if(!in_array($hour,$plumeHours[$date])){
				if(DEBUG){
					echo '<p> Adding Hour: ' . $hour . '</p>';
				}
				array_push($plumeHours[$date], $hour);
				$plumeTypes[$date][$hour] = array();
				$plumeStations[$date][$hour] = array();
			}
			if(!in_array($type,$plumeTypes[$date][$hour])){
				if(DEBUG){
					echo '<p> Adding Type: ' . $type . '</p>';
				}
				array_push($plumeTypes[$date][$hour], $type);
				$plumeStations[$date][$hour][$type] = array();
			}
			if(!in_array($stationName,$plumeStations[$date][$hour][$type])){
				if(DEBUG){
					echo '<p> Adding Station: ' . $stationName . '</p>';
				}
				array_push($plumeStations[$date][$hour][$type], $stationName);
			}
		}
		// Sort top level arrays by latest first.
		rsort($plumeDates);
		rsort($plumeHours[$plumeDates[0]]);
		sort($plumeTypes[$plumeDates[0]][$plumeHours[$plumeDates[0]][0]]);
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
				<select name="PlumeSelectType" onchange="PlumeSelectForm.submit();">
					<?php
						/*
						 * Define a dropdown menu for available graphics based
						 *  on the initial or selected date and time.
						 * function defineTypes() eliminates duplicate code.
						 */
						function defineTypes($date_,$hour_,$plumeTypes_){
							foreach($plumeTypes_[$date_][$hour_] as $type){
								if(isset($_POST['PlumeSelectType']) && $_POST['PlumeSelectType'] === $type){?>
									<option value="<?php echo $type ?>" selected="selected"><?php echo $type ?></option>
								<?php
								}
								else{?>
									<option value="<?php echo $type ?>"><?php echo $type ?></option>
								<?php
								}
							}
						}
						// Check if post data is available, use defaults if not.
						if(isset($_POST['PlumeSelectHour']) and isset($_POST['PlumeSelectHour'])){
							// Check if selected hour is available.
							if(in_array($_POST['PlumeSelectHour'],$plumeHours[$_POST['PlumeSelectDate']])){
								defineTypes($_POST['PlumeSelectDate'],$_POST['PlumeSelectHour'],$plumeTypes);
							}
							else{
								defineTypes($_POST['PlumeSelectDate'],$plumeHours[$_POST['PlumeSelectDate']][0],$plumeTypes);
							}
						}
						else{
							defineTypes($plumeDates[0],$plumeHours[$plumeDates[0]][0],$plumeTypes);
						}
					?>
				</select>
				<select name="PlumeSelectStation" onchange="PlumeSelectForm.submit();">
					<?php
						/*
						 * Define a dropdown menu for available graphics based
						 *   on the initial or selected date, time and type.
						 * function defineGraphics() eliminates duplicate code.
						 */
						function defineGraphics($date_,$hour_,$type_,$plumeStations_){
							foreach($plumeStations_[$date_][$hour_][$type_] as $station){
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
						if(isset($_POST['PlumeSelectDate']) and isset($_POST['PlumeSelectHour']) and isset($_POST['PlumeSelectType'])){
							$dateToUse = $_POST['PlumeSelectDate'];
							// Check if selected hour is valid. If not, use
							//   latest available hour.
							if(in_array($_POST['PlumeSelectHour'],$plumeHours[$dateToUse])){
								$hourToUse = $_POST['PlumeSelectHour'];
							}
							else{
								$hourToUse = $plumeHours[$dateToUse][0];
							}
							// Check if selected type is valid. If not, use
							//   latest available hour.
							if(in_array($_POST['PlumeSelectType'],$plumeTypes[$dateToUse][$hourToUse])){
								$typeToUse = $_POST['PlumeSelectType'];
							}
							else{
								$typeToUse = $plumeTypes[$dateToUse][$hourToUse][0];
							}
						}
						// Case 2:
						// Use all defaults.
						else{
							$dateToUse = $plumeDates[0];
							$hourToUse = $plumeHours[$dateToUse][0];
							$typeToUse = $plumeTypes[$dateToUse][$hourToUse][0];
						}
						defineGraphics($dateToUse,$hourToUse,$typeToUse,$plumeStations);
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
					// Check if type is valid.
					if(in_array($_POST['PlumeSelectType'],$plumeTypes[$imageDate][$imageHour])){
						$imageType = $_POST['PlumeSelectType'];
					}
					else{
						$imageType = $plumeTypes[$imageDate][$imageHour][0];
					}
					// Check if station is valid.
					if(in_array($_POST['PlumeSelectStation'],$plumeStations[$imageDate][$imageHour][$imageType])){
						$imageStation = $_POST['PlumeSelectStation'];
					}
					else{
						$imageStation = $plumeStations[$imageDate][$imageHour][$imageType][0];
					}
				}
				// Case 2:
				// Use all defaults.
				else{
					$imageDate = $plumeDates[0];
					$imageHour = $plumeHours[$imageDate][0];
					$imageType = $plumeTypes[$imageDate][$imageHour][0];
					$imageStation = $plumeStations[$imageDate][$imageHour][$imageType][0];
				}
				$imagePath = '../plumes/' . $imageDate . '/' . $imageHour . '/' . $imageType . '/' .  $imageStation . '.png';
				?>
				<img src="<?php echo $imagePath?>" alt="Plume Image">
		</p>
	</body>
</html>