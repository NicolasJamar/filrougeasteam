<?php
@include '../connection_db.php';

// Initialise la session
session_start();
 
// Si la variable de session n'est pas définie, elle redirigera vers la page de connexion
if(!isset($_SESSION['username']) || empty($_SESSION['username'])){
  header("location: login.php");
  exit;
}

// On convertit le GET en_integer
$_GET['article'] = (int) $_GET['article'];

if(isset($_POST['titre']) AND isset($_POST['contenu']) AND isset($_POST['categories']) AND isset($_POST['auteur']) AND isset($_GET['article'])){

	$nvtitre = htmlspecialchars($_POST['titre']);
	$nvcontenu = htmlspecialchars($_POST['contenu']);
	$nvauteurs = htmlspecialchars($_POST['auteur']);

	//Requête pour modifier les champs dans 'articles' En fonction de l'Id
	$update_table_articles = $pdo->prepare("UPDATE articles SET 
		titre = :nvtitre, 
		contenu = :nvcontenu, 
		auteurs = :nvauteurs 
		WHERE Id = :Id");

	$update_table_articles->execute(array(
	    'nvtitre' => $nvtitre,
	    'nvcontenu' => $nvcontenu,
	    'nvauteurs' => $nvauteurs,
	    'Id' => $_GET['article']
	    ));
	

	//Delete table articles_has_categories En fonction de l'id_articles
	$delete_id_articles = $pdo->prepare("DELETE FROM articles_has_categories WHERE id_articles = ?");
	$delete_id_articles->execute(array($_GET['article']));

	//On insert les catégories sélectionnées dans 'articles_has_categories' en fonction de l'id_articles
	$nvcategories = $_POST['categories'];

	foreach ($nvcategories as $key => $value) {

		$insert_checkbox_categories = $pdo->prepare("INSERT INTO articles_has_categories(id_articles, id_categories) VALUES (? , ? )");
		$insert_checkbox_categories->execute(array( $_GET['article'], $nvcategories[$key] ));
		
	}
}

//On appelle le tableau categories_list pour les diposer avec des checkbox
$categories_liste = $pdo->query("SELECT * FROM categories_liste");
$categories_liste = $categories_liste->fetchAll();

//Je mets ce code après pour que les modifs s'affichent quand on édite
//On va chercher la bdd en fonction de l'id
$reponse_article = $pdo->prepare("SELECT Id, titre, contenu, auteurs, DATE_FORMAT(date_ajout, '%d/%m/%Y à %Hh%i') AS date_ajout_fr FROM articles WHERE Id = ?");

//l'id se trouve dans le GET
$reponse_article->execute(array($_GET['article']));

?>

<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>Éditer article</title>
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
		<meta content="" name="keywords">
		<meta content="" name="description">

		<!-- Favicons -->
		<link href="../../img/favicon.png" rel="icon">
		<link href="../../img/apple-touch-icon.png" rel="apple-touch-icon">

		<!-- Google Fonts -->
		<link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,700|Open+Sans:300,300i,400,400i,700,700i" rel="stylesheet">

		<!-- Bootstrap CSS File -->
		<link href="../../lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

		<!-- Libraries CSS Files -->
		<link href="../../lib/animate/animate.min.css" rel="stylesheet">
		<link href="../../lib/font-awesome/css/font-awesome.min.css" rel="stylesheet">
		<link href="../../lib/ionicons/css/ionicons.min.css" rel="stylesheet">
		<link href="../../lib/magnific-popup/magnific-popup.css" rel="stylesheet">

		<link rel="stylesheet" href="style.css">
</head>
	<body>
<?php 
		include('admin_blog_header.php');
?>
		<div class="container marg-top">
			<div class="row">
		
<?php
while($donnees_article = $reponse_article->fetch()){
?>
			        <div class="col-lg-12">
			            <div class="box wow fadeInLeft">
			            	<form action="" method="POST" class="create_post">
						       <h2>Éditer un article</h2>
						      
							      <label for="titre">Titre</label>
					              <input type="text" name="titre" value="<?php echo $donnees_article['titre'] ?>">

					              <label for="contenu">Contenu</label>
					              <textarea name="contenu" rows="10" cols="100"><?php echo $donnees_article['contenu']?></textarea>

					              <label for="auteur">Auteur</label>
					              <input type="text" name="auteur" value="<?php echo $donnees_article['auteurs']?>"> le <?php echo $donnees_article['date_ajout_fr']?>
									<br><br>
				              	<p>Catégorie(s) : </p>

				              	
				              	<p><i>
				              		
								<?php 
          						$categories_selection = $pdo->prepare("
          							SELECT categories_liste.categorie_nom 
          							from categories_liste 
          							INNER JOIN articles_has_categories 
          							ON articles_has_categories.id_categories = categories_liste.categorie_id 
          							AND articles_has_categories.id_articles= ? ");

						          $categories_selection->execute(array($_GET['article']));
						          $categories_selection = $categories_selection->fetchAll();
						            foreach ($categories_selection as $key => $value) {
						              echo $categories_selection[$key]['categorie_nom'].' ';
						            };
						          ?>  

				              	</i></p>
								
								<label for="categories">Changer de catégorie(s)</label>
					        	<ul id="categories_liste_wrapper">
					        		<?php foreach ($categories_liste as $i => $value) {
					        		?>

					        		<li><input type="checkbox" name="categories[]" value="<?php echo $categories_liste[$i]['categorie_id'] ?>"><?php echo $categories_liste[$i]['categorie_nom'] ?></li>

					        		<?php } ?>
						        </ul>

				              <div class="icon-blog"><i class="ion-edit"><input type="submit" value="Éditer" name="submit" class="button"></i></div>
			              	</form>
			            </div>
			        </div>

<?php
}
?>
			</div>
  		</div>

<?php include('admin_blog_footer.php') ?>

</body>
</html>