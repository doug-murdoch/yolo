<?php
// Code to randomly generate conjoint profiles to send to a Qualtrics instance

// Terminology clarification: 
// Task = Set of choices presented to respondent in a single screen (i.e. pair of candidates)
// Profile = Single list of attributes in a given task (i.e. candidate)
// Attribute = Category characterized by a set of levels (i.e. education level)
// Level = Value that an attribute can take in a particular choice task (i.e. "no formal education")

// Attributes and Levels stored in a 2-dimensional Array 

// Function to generate weighted random numbers
function weighted_randomize($prob_array, $at_key)
{
	$prob_list = $prob_array[$at_key];
	
	// Create an array containing cutpoints for randomization
	$cumul_prob = array();
	$cumulative = 0.0;
	for ($i=0; $i<count($prob_list); $i++){
		$cumul_prob[$i] = $cumulative;
		$cumulative = $cumulative + floatval($prob_list[$i]);
	}

	// Generate a uniform random floating point value between 0.0 and 1.0
	$unif_rand = mt_rand() / mt_getrandmax();

	// Figure out which integer should be returned
	$outInt = 0;
	for ($k = 0; $k < count($cumul_prob); $k++){
		if ($cumul_prob[$k] <= $unif_rand){
			$outInt = $k + 1;
		}
	}

	return($outInt);

}
                    

$featurearray = array("townhall" => array("0","5","15","45"),"accomplishment" => array("Secured $50,000 for a local University","Secured $1,000,000 for a local University","Secured $20,000,000 to repair local roads","Secured $20,000,000 for a local University","Secured $50,000 to repair local roads","Secured $1,000,000 to repair local roads"),"ideology" => array("0","10","20","30","40","50","60","70","80","90","100"),"photo" => array("https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_29rryixpeCTdayV","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_2toz6CHdpDtxufz","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_2tu08CqxcxD1E69","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_3IxEgZ0hrhvYJ8h","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_3CxN5mdI58iuddr","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_bpec0n35Uz7NH0N","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_dbrNmLLKZRjWrqJ","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_cAPleVPuc4pcEU5","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_2sm3viyduhHVQ9v","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_0MnmcaYdrkMHpxr","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_89fWlThewASGFQp","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_aXgW5uwn4ZUJoPP","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_389Mq2z0wW77yPb","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_2bO0R6U4Cg2CEAt","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_81Z27U70RyZ2Su9","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_4TSgUEp5hsdqJsF","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_9QTIGsI0to1S7pr","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_3IQLI1Ls25QKAjb","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_3h4eA3o1rgvy8HX","https://sewg.az1.qualtrics.com/ControlPanel/Graphic.php?IM=IM_7WLgPqJta5xO8kd"));

$restrictionarray = array();

// Indicator for whether weighted randomization should be enabled or not
$weighted = 0;

// K = Number of tasks displayed to the respondent
$K = 5;

// N = Number of profiles displayed in each task
$N = 2;

// num_attributes = Number of Attributes in the Array
$num_attributes = count($featurearray);


$attrconstraintarray = array();


// Re-randomize the $featurearray

// Place the $featurearray keys into a new array
$featureArrayKeys = array();
$incr = 0;

foreach($featurearray as $attribute => $levels){	
	$featureArrayKeys[$incr] = $attribute;
	$incr = $incr + 1;
}

// Backup $featureArrayKeys
$featureArrayKeysBackup = $featureArrayKeys;

// If order randomization constraints exist, drop all of the non-free attributes
if (count($attrconstraintarray) != 0){
	foreach ($attrconstraintarray as $constraints){
		if (count($constraints) > 1){
			for ($p = 1; $p < count($constraints); $p++){
				if (in_array($constraints[$p], $featureArrayKeys)){
					$remkey = array_search($constraints[$p],$featureArrayKeys);
					unset($featureArrayKeys[$remkey]);
				}
			}
		}
	}
} 
// Re-set the array key indices
$featureArrayKeys = array_values($featureArrayKeys);
// Re-randomize the $featurearray keys
shuffle($featureArrayKeys);

// Re-insert the non-free attributes constrained by $attrconstraintarray
if (count($attrconstraintarray) != 0){
	foreach ($attrconstraintarray as $constraints){
		if (count($constraints) > 1){
			$insertloc = $constraints[0];
			if (in_array($insertloc, $featureArrayKeys)){
				$insert_block = array($insertloc);
				for ($p = 1; $p < count($constraints); $p++){
					if (in_array($constraints[$p], $featureArrayKeysBackup)){
						array_push($insert_block, $constraints[$p]);
					}
				}
				
				$begin_index = array_search($insertloc, $featureArrayKeys);
				array_splice($featureArrayKeys, $begin_index, 1, $insert_block);
			}
		}
	}
}


// Re-generate the new $featurearray - label it $featureArrayNew

$featureArrayNew = array();
foreach($featureArrayKeys as $key){
	$featureArrayNew[$key] = $featurearray[$key];
}
// Initialize the array returned to the user
// Naming Convention
// Level Name: F-[task number]-[profile number]-[attribute number]
// Attribute Name: F-[task number]-[attribute number]
// Example: F-1-3-2, Returns the level corresponding to Task 1, Profile 3, Attribute 2 
// F-3-3, Returns the attribute name corresponding to Task 3, Attribute 3

$returnarray = array();

// For each task $p
for($p = 1; $p <= $K; $p++){

	// For each profile $i
	for($i = 1; $i <= $N; $i++){

		// Repeat until non-restricted profile generated
		$complete = False;

		while ($complete == False){

			// Create a count for $attributes to be incremented in the next loop
			$attr = 0;
			
			// Create a dictionary to hold profile's attributes
			$profile_dict = array();

			// For each attribute $attribute and level array $levels in task $p
			foreach($featureArrayNew as $attribute => $levels){	
				
				// Increment attribute count
				$attr = $attr + 1;

				// Create key for attribute name
				$attr_key = "F-" . (string)$p . "-" . (string)$attr;

				// Store attribute name in $returnarray
				$returnarray[$attr_key] = $attribute;

				// Get length of $levels array
				$num_levels = count($levels);

				// Randomly select one of the level indices
				if ($weighted == 1){
					$level_index = weighted_randomize($probabilityarray, $attribute) - 1;

				}else{
					$level_index = mt_rand(1,$num_levels) - 1;	
				}	

				// Pull out the selected level
				$chosen_level = $levels[$level_index];
			
				// Store selected level in $profileDict
				$profile_dict[$attribute] = $chosen_level;

				// Create key for level in $returnarray
				$level_key = "F-" . (string)$p . "-" . (string)$i . "-" . (string)$attr;

				// Store selected level in $returnarray
				$returnarray[$level_key] = $chosen_level;

			}

			$clear = True;
			// Cycle through restrictions to confirm/reject profile
			if(count($restrictionarray) != 0){

				foreach($restrictionarray as $restriction){
					$false = 1;
					foreach($restriction as $pair){
						if ($profile_dict[$pair[0]] == $pair[1]){
							$false = $false*1;
						}else{
							$false = $false*0;
						}
						
					}
					if ($false == 1){
						$clear = False;
					}
				}
			}
			$complete = $clear;
		}
	}


}

// Return the array back to Qualtrics
print  json_encode($returnarray);
?>
