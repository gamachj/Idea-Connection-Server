<?php

/*
	Function to return the result in a Json format from ResultObject
*/
function getJsonFromResultObject($result){
	$ideas = array();
	if($result->num_rows > 0){
		while($row = $result->fetch_array(MYSQLI_ASSOC)){
			array_push($ideas, $row);
		}
	}

	return json_encode($ideas);
};
?>