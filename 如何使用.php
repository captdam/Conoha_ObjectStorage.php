<?php
	//初始化，两种方法任选其一
	//1 - 基础方法，每一次都向服务器请求token。简单，但是效率低，因为向外部服务器请求token会消耗时间
	$iniStatus = $this->objectStorage->ini('username','password','tenantid','',0);
	//2 - 使用数据库来管理token，使用这种方法。假设你的数据库有两列（分别是数据名、数据），这样你每一行储存了一个数据
	$token = $DB->getToken();
	$expireTime = $DB->getexpireTime();
	$oldToken = $token;
	$iniStatus = $this->objectStorage->ini('username','password','tenantid',$token,$expireTime);
	if ($token != $oldToken) { #Token是通过Reference传入的，如果token过期会重置
		$DB->setToken($token);
		$DB->setExpireTime($time()+72000); #Conoha表示token有效时间是24小时，为了保险，我们使用20小时
	}
	
	//现在，我们已经准备好使用这个class了，下面的函数详细介绍了如何使用
	//要查看函数输出，使用var_dump($x);
	//接下来你会得到一个长度为2的一元数组
	//$x[0]是返回的HTTP状态码，类似这样：200, 404, 409
	//$x[1]是返回的HTTP的body
	//注意，有的函数只会返回状态码。比如说，DELETE指令将只返回409表示成功
	
	//GET
	//获取Container列表
	$x = $os->get('');
	//获得一个container里面的资源的列表
	$x = $os->get('YOUR_CONTAINER_NAME/');
	//获取一个资源的内容
	$x = $os->get('YOUR_CONTAINER_NAME/THE_RESOURCE NAME');
	
	//PUT
	//创建一个container
	$x = $os->put('NEW_CONTAINER_NAME/');
	//创建一个无内容的资源
	$x = $os->put('CONTAINER_NAME/RESOURCE_NAME');
	//创建一个资源，其内容来源于一个本地文件
	$x = $os->put('CONTAINER_NAME/RESOURCE_NAME','LOCAL_FILE_NAME');
	//创建一个资源，其内容将被作为参数输入这个函数
	$x = $os->put('CONTAINER_NAME/RESOURCE_NAME','CONTENT_YOU_WISH_TO_WRITE',true);
	
	//DELETE
	//删除一个资源
	$x = $os->delete('CONTAINER_NAME/RESOURCE_NAME');
	//删除一个container，注意，你需要先删除container内部所有资源才能成功删除这个container
	$x = $os->delete('CONTAINER_NAME/');
?>
