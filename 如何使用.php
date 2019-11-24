<?php
	//初始化，两种方法任选其一
	//1 - 基础方法，每一次都向服务器请求token。简单，但是效率低，因为向外部服务器请求token会消耗时间
	$iniStatus = $this->objectStorage->ini('username','password','tenantid','',0);
	//2 - 使用数据库来管理token
	$token = $DB->getToken();
	$expireTime = $DB->getexpireTime();
	$oldToken = $token;
	$conoha = new Conoha('username','password','tenantid',$token,$expireTime);
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
	$x = $conoha->get('');
	//获得一个container里面的资源的列表
	$x = $conoha->get('YOUR_CONTAINER_NAME/');
	//获取一个资源的内容
	$x = $conoha->get('YOUR_CONTAINER_NAME/THE_RESOURCE NAME');
	
	//PUT
	//创建一个container
	$x = $conoha->put('NEW_CONTAINER_NAME/');
	//创建一个无内容的资源
	$x = $conoha->put('CONTAINER_NAME/RESOURCE_NAME');
	//创建一个资源，其内容来源于一个本地文件
	$x = $conoha->put('CONTAINER_NAME/RESOURCE_NAME','LOCAL_FILE_NAME');
	//创建一个资源，其内容将被作为参数输入这个函数
	$x = $os->put('CONTAINER_NAME/RESOURCE_NAME','CONTENT_YOU_WISH_TO_WRITE',true);
	
	//DELETE
	//删除一个资源
	$x = $conoha->delete('CONTAINER_NAME/RESOURCE_NAME');
	//删除一个container，注意，你需要先删除container内部所有资源才能成功删除这个container
	$x = $conoha->delete('CONTAINER_NAME/');
?>

<?php
	//API账户信息
	$token = '上次签发的Token（可选项）可以留空';
	define('OS_USERNAME',   'gncu000000');
	define('OS_PASSWORD',   'passwordNeverBroken');
	define('OS_TENANT',     '0101010101000cacbhadkjsfbjqn0302w9f89');
	
	//建立一个对象储存服务器操作实例
	include 'conoha.class.php';
	$os = new Conoha(OS_TENANT,$token);
	
	//如果Token是有效的，这时应该返回HTTP 200
	echo $os->get('')[0];
	//否则，请求重新签发Token
	$token = $os->register(array('username'=>OS_USERNAME,'password'=>OS_PASSWORD));
	save_my_token($token,time());
	/* Token的有效期为24小时。可以把Token保存起来以供下次使用，这样就免去了每次使用都重新签发的额外工作 */

	//使用下面的方法PUT/GET/DELETE对象
	//所有方法都会返回一个包含两个元素的数组
	//$return[0] <-- HTTP状态码
	//$return[1] <-- 响应本体
	//有的方法不会返回响应本体，例如DELTE只会返回409状态码
	
	/************************** GET **************************/
	
	//获得Container列表
	$x = $conoha->get('');
	//获得某个Container内对象列表
	$x = $conoha->get('CONTAINER_NAME');
	//获得一个对象
	$x = $conoha->get('CONTAINER_NAME/OBJECT_NAME');
	
	/************************** PUT **************************/
	
	//创建Container
	$x = $conoha->put('NEW_CONTAINER_NAME');
	//创建对象（无内容）
	$x = $conoha->put('CONTAINER_NAME/NEW_OBJECTE_NAME');
	//将一个本地文件上传
	$x = $conoha->put('CONTAINER_NAME/NEW_OBJECT_NAME','PATH_TO_LOCAL_FILE_NAME');
	//使用变量的值作为内容创建一个对象
	$x = $os->put('CONTAINER_NAME/NEW_OBJECT_NAME','CONTENT_YOU_WISH_TO_WRITE',false);

	/*
	对象名冲突：
	创建一个名字已存在的Container，返回202，服务器上不会有任何改变发生。
	创建一个名字已存在的对象，返回201，旧的对象将会被覆盖（无论是否提供了内容）。
	提示：
	建议在创建对象时在对象名里面包含文件扩展名（例如：$os->put('container1/myImage.jpeg',...)）
	当使用Web publishing时（Container内所有对象都可以被外部无Token读取）
	Conoha将会通过文件扩展名决定HTTP头里的MIME
	
	/************************* DELETE ************************/
	
	//删除一个对象
	$x = $conoha->delete('CONTAINER_NAME/OBJECT_NAME');
	//删除一个Container （前提是Container里面没有对象，否则将会返回409）
	$x = $conoha->delete('CONTAINER_NAME');
?>
