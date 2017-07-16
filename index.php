<?php

/*Appel à la connexion bdd*/
require_once 'connect.php';

/* Déclarations des variables*/
$order = '';
$post = [];
$errors = [];

/*On détermine si il y a un ordre de tri selon le paramètre passé dans l'url*/
if(isset($_GET['order']) && isset($_GET['column'])) {

	if($_GET['column'] == 'lastname'){
		$order = ' ORDER BY lastname';
	}
	elseif($_GET['column'] == 'firstname'){
		$order = ' ORDER BY firstname';
	}
	elseif($_GET['column'] == 'birthdate'){
		$order = ' ORDER BY birthdate';
	}
	if($_GET['order'] == 'asc'){
		$order.= ' ASC';
	}
	elseif($_GET['order'] == 'desc'){
		$order.= ' DESC';
	}
}

/*Si il y a eu un envoi de données depuis le formulaire*/
if(!empty($_POST)) {

	/* On nettoye les données */
	foreach($_POST as $key => $value) {
		$post[$key] = strip_tags(trim($value));
	}

	/*Vérification de la longueur du prénom*/
	if(strlen($post['firstname']) < 3) {
		$errors[] = 'Le prénom doit comporter au moins 3 caractères';
	}

	/*Vérification de la longueur du nom*/
	if(strlen($post['lastname']) < 3) {
		$errors[] = 'Le nom doit comporter au moins 3 caractères';
	}

	/*Vérification de la syntaxe de l'adresse email*/
	if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL)){
		$errors[] = 'L\'adresse email est invalide';
	}

	/*Vérification si la date de naissance n'est pas vide*/
	if(empty($post['birthdate'])) {
		$errors[] = 'La date de naissance doit être complétée';
	}
	else {
		/* Vérification du format de la date grace à la classe DateTime*/
		$birthdate = DateTime::createFromFormat('d-m-Y', $post['birthdate']);

		if (!$birthdate instanceOf DateTime) {
			$errors[] = 'la date de naissance n\'est pas au bon format';
 		}
	}

	/*Vérification si le champ ville n'est pas vide*/
	if(empty($post['city'])){
		$errors[] = 'La ville ne peut être vide';
	}

	/*Si il n'y a aucune erreur, on insère en base de données*/
	if(count($errors) === 0) {

		$insertUser = $db->prepare('INSERT INTO users (gender, firstname, lastname, email, birthdate, city) VALUES(:gender, :firstname, :lastname, :email, :birthdate, :city)');
		$insertUser->bindValue(':gender', $post['gender']);
		$insertUser->bindValue(':firstname', $post['firstname']);
		$insertUser->bindValue(':lastname', $post['lastname']);
		$insertUser->bindValue(':email', $post['email']);
		$insertUser->bindValue(':birthdate', date('Y-m-d', strtotime($post['birthdate'])));
		$insertUser->bindValue(':city', $post['city']);

		if($insertUser->execute()) {
			$createUser = true; /* Si l'insertion d en bdd s'est bien passée, on creer une variable $createUser que l'on passe a true*/
		}
		else {
			$errors[] = 'Erreur SQL';
		}

	}

}

/* On récupère les informations des utilisateurs en bdd*/
$queryUsers = $db->prepare('SELECT * FROM users'.$order);
if($queryUsers->execute()){
	/* Si la requete réussi, on stocke les informations dans $users*/
	$users = $queryUsers->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Exercice 1</title>
	<meta charset="utf-8">
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

	<div class="container">

		<h1>Liste des utilisateurs</h1>

		<!-- Liens pour trier la liste des utilisateurs -->
		<p>Trier par :
			<a href="index.php?column=firstname&order=asc">Prénom (croissant)</a> |
			<a href="index.php?column=firstname&order=desc">Prénom (décroissant)</a> |
			<a href="index.php?column=lastname&order=asc">Nom (croissant)</a> |
			<a href="index.php?column=lastname&order=desc">Nom (décroissant)</a> |
			<a href="index.php?column=birthdate&order=desc">Âge (croissant)</a> |
			<a href="index.php?column=birthdate&order=asc">Âge (décroissant)</a>
		</p>
		<br>

		<div class="row">

			<!-- Affichage des erreurs ou du message de succés -->
			<?php
			if(isset($createUser) && $createUser == true){
				echo '<div class="col-md-6 col-md-offset-3">';
				echo '<div class="alert alert-success">Le nouvel utilisateur a été ajouté avec succès.</div>';
				echo '</div><br>';
			}
			if(isset($errors) && !empty($errors)){
				echo '<div class="col-md-6 col-md-offset-3">';
				echo '<div class="alert alert-danger">'.implode('<br>', $errors).'</div>';
				echo '</div><br>';
			}
			?>

			<!-- Table contenant les données des  utilisateurs -->
			<div class="col-md-7">
				<table class="table">
					<thead>
						<tr>
							<th>Civilité</th>
							<th>Prénom</th>
							<th>Nom</th>
							<th>Email</th>
							<th>Age</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($users as $user):?>
						<tr>
							<td><?php echo $user['gender'];?></td>
							<td><?php echo $user['firstname'];?></td>
							<td><?php echo $user['lastname'];?></td>
							<td><?php echo $user['email'];?></td>
							<td><?php echo DateTime::createFromFormat('Y-m-d', $user['birthdate'])->diff(new DateTime('now'))->y;?> ans</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>


			<!-- Formulaire d'inscription des utilisateurs -->
			<div class="col-md-5">

				<form method="post" class="form-horizontal well well-sm">
					<fieldset>
						<legend>Ajouter un utilisateur</legend>

						<div class="form-group">
							<label class="col-md-4 control-label" for="gender">Civilité</label>
							<div class="col-md-8">
								<select id="gender" name="gender" class="form-control input-md" required>
									<option value="Mlle">Mademoiselle</option>
									<option value="Mme">Madame</option>
									<option value="M">Monsieur</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label" for="firstname">Prénom</label>
							<div class="col-md-8">
								<input id="firstname" name="firstname" type="text" class="form-control input-md" required>
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label" for="lastname">Nom</label>
							<div class="col-md-8">
								<input id="lastname" name="lastname" type="text" class="form-control input-md" required>
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label" for="email">Email</label>
							<div class="col-md-8">
								<input id="email" name="email" type="email" class="form-control input-md" required>
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label" for="city">Ville</label>
							<div class="col-md-8">
								<input id="city" name="city" type="text" class="form-control input-md" required>
							</div>
						</div>

						<div class="form-group">
							<label class="col-md-4 control-label" for="birthdate">Date de naissance</label>
							<div class="col-md-8">
								<input id="birthdate" name="birthdate" type="text" placeholder="JJ-MM-AAAA" class="form-control input-md" required>
								<span class="help-block">au format JJ-MM-AAAA</span>
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-4 col-md-offset-4">
								<button type="submit" class="btn btn-primary">Envoyer</button>
							</div>
						</div>
					</fieldset>
				</form>

			</div>

		</div> <!--  Fin .row -->

	</div> <!--  Fin .container -->
</body>
</html>
