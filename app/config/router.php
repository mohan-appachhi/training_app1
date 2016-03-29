<?php
use Phalcon\Mvc\Router;

$router = new Phalcon\Mvc\Router();
$router->add("/([0-9]+)",
    array(
        "controller" => "Index",
        "action"     => "index",
        "page"       =>  1
    )
);
$router
->add("/photo/{name_id}",
	[
		"controller" => "Photo",
		"action"     => "photo",
		"name_id"    => 1
	]
);

$router->handle();

return $router;