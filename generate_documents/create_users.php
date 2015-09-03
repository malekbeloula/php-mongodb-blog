<?php

require('../classes/dbconnection.php');
$mongo = DBConnection::singleton();
$collection = $mongo->getCollection('users');

$users = array(
    array(
        'name' => 'Luke Skywalker',
        'username' => 'jedimaster23',
        'password' => md5('usetheforce'),
        'birthday' => new MongoDate(
                strtotime('1971-09-29 00:00:00')),
        'address' => array(
            'town' => 'Mos Eisley',
            'planet' => 'Tatooine'
        )
    ),
    array(
        'name' => 'Leia Organa',
        'username' => 'princessleia',
        'password' => md5('eviltween'),
        'birthday' => new MongoDate
                (strtotime('1976-10-21 00:00:00')),
        'address' => array(
            'town' => 'Aldera',
            'planet' => 'Alderaan'
        )
    ),
    array(
        'name' => 'Chewbacca',
        'username' => 'chewiethegreat',
        'password' => md5('loudgrowl'),
        'birthday' => new MongoDate
                (strtotime('1974-05-19 00:00:00')),
        'address' => array(
            'town' => 'Kachiro',
            'planet' => 'Kashyyk'
        )
    )
);
foreach ($users as $user) {
    try {
        $mongo->create($collection, $user);
    } catch (MongoCursorException $e) {
        die($e->getMessage());
    }
}
echo 'Users created successfully';
