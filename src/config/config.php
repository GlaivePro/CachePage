<?php

return [

	//Time to store cached pages if not specified.
	'time' => 5,
	
	//Allow skipping cache using parameters in HTTP request.
	'allowSkipping' => true,
	
	//Allow clearing the cached response using parameters in HTTP request.
	'allowClearing' => true,
	
	//Allow flushing all cached pages using parameters in HTTP request.
	'allowFlushing' => false,
];
