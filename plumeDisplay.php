<html>
	<head>
		<title>PHP Display Tests</title>
	</head>
	<body>
		<?php
			// Create default selection based on latest available graphics.
			// Populate form and summon latest graphic.
			// First, find the latest date by rsort of a list of date directories.
			// Next, find the latest time by rsort of a list of hour directories.
			// Finally, select the first alphabetical station graphic by sort of a list of graphics.
			$plumeDates = array();
			$plumeHours = array();
			$plumePaths = array();
			foreach(glob('../plumes/*',GLOB_ONLYDIR) as $dateDir){
				//echo '<p>' . $dateDir . '</p>';
				$dateString = substr($dateDir, count($dateDir) - 9, 8);
				array_push($plumeDates,$dateString);
				$plumePaths[$dateString] = array();
				foreach(glob($dateDir . '/*',GLOB_ONLYDIR) as $hourDir){
					//echo '<p>' . $hourDir . '</p>';
					$hourString = substr($hourDir, count($hourDir) - 4, 3);
					array_push($plumeHours,$hourString);
					$plumePaths[$dateString][$hourString] = array();
					foreach(glob($hourDir . '/*') as $plumeImagePath){
						//echo '<p>' . $plumeImagePath . '</p>';
						array_push($plumePaths[$dateString][$hourString],$plumeImagePath);
					}
				}
			}
			rsort($plumeDates);
			rsort($plumeHours);
			echo '<p> Latest Date: ' . $plumeDates[0] . '</p>';
			echo '<p> Latest Hour: ' . $plumeHours[0] . '</p>';
			//foreach($plumeImagePaths as $pip){
			//	echo '<p>' . $pip . '</p>';
			//}
		?>
	</body>
</html>