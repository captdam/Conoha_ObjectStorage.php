<?php
	//Ini
	$os = new Conoha();
	
	//Setup the class, two ways, choose one:
	//Basic way, apply a new token every time
	//This way is simple, but not good for performance
	$iniStatus = $this->objectStorage->ini('',0);
	if ($iniStatus == false) { #Ini fail
		header('HTTP/1.1 500 Internal Server Error');
		echo 'Object storage server error.';
		exit;
	}
	//If you use database to manage token, use this method:
	//Assume you have a table with tow colums (key and value), each row save one variable
	$iniStatus = $this->objectStorage->ini('Token',$this->site['ConohaExpire']);
	if ($iniStatus == false) { #Ini fail
		header('HTTP/1.1 500 Internal Server Error');
		echo 'Object storage server error.';
		exit;
	}
	else if ($iniStatus !== true) { #Token renewed
		$SQL = $this->database->prepare('UPDATE SiteVariables SET `Value` = ? WHERE `Key` = ? LIMIT 1');
		$SQL->execute(array($iniStatus,'ConohaToken'));
		$SQL->execute(array(time()+72000,'ConohaExpire')); #Conoha says token vaild for 24 hrs, here we use 20 hrs
	}
	
	//Now, you are ready to use this class, the following shows how to use this class:
	//To see the output of each method, using var_dump($x);
	//Then, your will get an array with 2 keys, where:
	//$x[0] is the return HTTP code, eg: 200, 404, 409
	//$x[1] is the return body
	//Notice that, some method may not return a body. For example, if you using DELETE method, only HTTP satus code 409 will be return.
	
	//GET
	
	//GET container list
	$x = $os->get('');
	//GET file (resource) list in a container
	$x = $os->get('YOUR_CONTAINER_NAME/');
	//GET a file
	$x = $os->get('YOUR_CONTAINER_NAME/THE_RESOURCE NAME');
	
	//PUT
	
	//Create a container
	$x = $os->put('NEW_CONTAINER_NAME/');
	//Create a resource (no content)
	$x = $os->put('CONTAINER_NAME/RESOURCE_NAME');
	//Create a resource, copy from a file
	$x = $os->put('CONTAINER_NAME/RESOURCE_NAME','LOCAL_FILE_NAME');
	//Create a resource, the content is part of the parameter of this method
	$x = $os->put('CONTAINER_NAME/RESOURCE_NAME','CONTENT_YOU_WISH_TO_WRITE',true);
	
	//DELETE
	
	//Delete a resource
	$x = $os->delete('CONTAINER_NAME/RESOURCE_NAME');
	//Delete a container, notice that you need to delete every thing in the container first
	$x = $os->delete('CONTAINER_NAME/');
?>
