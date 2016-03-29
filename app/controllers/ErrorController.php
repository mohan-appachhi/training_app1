<?php
use \Phalcon\Config\Adapter\Ini as ConfigIni;
use \Phalcon\Mvc\Controller;
class ErrorController extends Controller
{
    public function indexAction()
    {
        $this->tag->setTitle('Oops!');
        $this->logger->error("Error");
    }

    public function show404Action()
    {
        $this->tag->setTitle('Oops!');
        $this->logger->error("Error_show404");
        //$config = new ConfigIni("../app/config/config.ini");
        //$msg = "There is an issue flickr controller showing page not found";
        //mail($config->variables->DEV_EMAIL, "Issue", $msg);
    }

    public function show105Action()
    {
        $this->tag->setTitle('Oops!');
        $this->logger->error("Error_show105");
    }

    public function show500Action()
    {
         $this->tag->setTitle('Oops!');
         $this->logger->error("Error_show500");
    }

}
