<?php

try {
  $dbh = new PDO('mysql:host=db;dbname=db_panneau_solaire' , $user, $pass);  
} catch (PDOException $e) {
    return 'catch';
}
$sql = $dbh->query('SELECT * FROM mesure ORDER BY ASC');
$sql = $dbh->query('LIMIT 10');

