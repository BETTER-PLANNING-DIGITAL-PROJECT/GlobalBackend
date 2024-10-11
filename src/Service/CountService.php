<?php
namespace App\Service;

class CountService
{
    public function random($arg): string
    {
        $string = "";
        $chain = "123456789";
        srand((double)microtime()*100000);
        for($i=0; $i<$arg; $i++) {
            $string .= $chain[rand()%strlen($chain)];
        }
        return $string;
    }
}

