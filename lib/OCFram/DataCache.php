<?php


namespace OCFram;


class DataCache
{
    public function test($listeNews){



        $test = serialize($listeNews);
        echo $test;
        echo "<br>";
        echo "<br>";
        $test2 = unserialize($test);
        var_dump($test2);
        die();
    }
}