<?php
	//API account info
	$token = 'Token issued last time (optional) You can just leave it blank';
	define('OS_USERNAME',   'gncu000000');
	define('OS_PASSWORD',   'passwordNeverBroken');
	define('OS_TENANT',     '0101010101000cacbhadkjsfbjqn0302w9f89');
	
	//Create a instance
	include 'conoha.class.php';
	$os = new Conoha(OS_TENANT,$token);
	
	//If you provide a valid token, this should return HTTP 200
	echo $os->get('')[0];
	//otherwise, auth
	$token = $os->register(array('username'=>OS_USERNAME,'password'=>OS_PASSWORD));
	save_my_token($token,time());
	/* The token is valid for 24 hrs, you may want to save it so you don't need to auth again next time */

	//Use the following procedures to PUT/GET/DELETE objects.
	//All procedures returns an array with 2 elements.
	//$return[0] <-- HTTP code of the response
	//$return[1] <-- Response body
	//Notice that, some methods may not return a body. For example, if you using DELETE method, only HTTP status code 409 will be return.
	
	/************************** GET **************************/
	
	//GET container list
	$x = $conoha->get('');
	//GET file (object) list in a container
	$x = $conoha->get('CONTAINER_NAME');
	//GET a file
	$x = $conoha->get('CONTAINER_NAME/OBJECT_NAME');
	
	/************************** PUT **************************/
	
	//Create a container
	$x = $conoha->put('NEW_CONTAINER_NAME');
	//Create an object (no content)
	$x = $conoha->put('CONTAINER_NAME/NEW_OBJECTE_NAME');
	//Upload a file from local server to the object storage server
	$x = $conoha->put('CONTAINER_NAME/NEW_OBJECT_NAME','PATH_TO_LOCAL_FILE_NAME');
	//Create an object, the content is saved in a variable
	$x = $os->put('CONTAINER_NAME/NEW_OBJECT_NAME','CONTENT_YOU_WISH_TO_WRITE',false);

	/*
	Name conflict:
	If you create a container that is already exist, 202 will be returned with no change on the storage server.
	If you upload an object that is already exist, 201 will be returned and the old object will be overwriten (no matter content is provided)
	Hint:
	It is better to include file name extension in the object name (e.g. $os->put('container1/myImage.jpeg',...))
	When using web publishing (All objects in a container is avaliable for GET without token),
	Conoha uses the file name extension to determine MIME in the HTTP response header.
	*/
	
	/************************* DELETE ************************/
	
	//Delete an object
	$x = $conoha->delete('CONTAINER_NAME/OBJECT_NAME');
	//Delete a container, notice that you need to delete every thing in the container first, otherwise 409 will be returned
	$x = $conoha->delete('CONTAINER_NAME');
?>
