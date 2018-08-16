<?php

function Delimiter($options)
{
	$delimiter = ",";
	if (isset($options['d'])) {
		$delimiter = $options['d'];
		return $delimiter;
	}
	if (isset($options['delimiter'])) {
		$delimiter = $options['delimiter'];
		return $delimiter;
	}
	return $delimiter;
}



