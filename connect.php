<?php
/*Connexion à la bdd*/
// $db = new PDO('mysql:host=localhost;dbname=exo1_userslist;charset=utf8', 'root', 'Ereul9Aeng');
//
//
$user = 'root';
$pass = 'Ereul9Aeng';

try {
   $db = new PDO('mysql:host=localhost;dbname=exo1_userslist', $user, $pass);

   $db = null;
} catch (PDOException $e) {
   print "Erreur !: " . $e->getMessage() . "<br/>";
   die();
}
