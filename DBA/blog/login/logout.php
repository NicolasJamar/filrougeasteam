<?php
// Initialise la session
session_start();
 
// Annuler toutes les variables de session
$_SESSION = array();
 
// Détruire la session.
session_destroy();
 
// Rediriger vers la page de connexion
header("location: login.php");
exit;
?>