<?php

require_once "./main.php";

class controller {
    const MAIN = 1;
    
    protected function getWindow()
    {
        return self::MAIN;
    }
    
    public function processRequest()
    {
        $action = $this->getWindow();
        $main = new main();

    }
}