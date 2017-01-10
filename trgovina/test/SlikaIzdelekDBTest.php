<?php
require 'model/Slika_B.php';

class SlikaIzdelekDBTest
{

    public static function index()
    {

        echo Slika_B::insert(
            [
                "izdelek_id" => "1", "slika" => "blazina.jpg"
            ]
        );


    }

}



//SlikaIzdelkaDB::update(
//    [
//        "idSlikaIzdelka" => "1", "idIzdelek" => "1", "slika" => "pot do slike2"
//    ]
//);

//SlikaIzdelkaDB::delete(
//    [
//        "idSlikaIzdelka" => "1"
//    ]
//);

//SlikaIzdelkaDB::get(
//    [
//        "idSlikaIzdelka" => "2"
//    ]
//);

//SlikaIzdelkaDB::getAll();
