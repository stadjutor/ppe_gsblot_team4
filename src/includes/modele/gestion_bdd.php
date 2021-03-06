<?php
	function AjoutNewLot($doute,$medocID,$nbEchantillon){
		require "connectBdd.php";
		$sql = 'insert into gsb_lot (gsb_dateFabrication,gsb_idMedicament)
		VALUES ("'.$doute.'",'.$medocID.')';
		$exec=$bdd->prepare($sql) ;
        $exec->execute() ;

		$sql = 'select max(gsb_numero) from gsb_lot';
		$exec=$bdd->prepare($sql) ;
        $exec->execute() ;
        $curseur = $exec->fetchAll();
        //echo $curseur[0][0];
        
        for ($i=1; $i < $nbEchantillon+1; $i++) { 
        	$sql = 'insert into gsb_echantillon (gsb_numero, gsb_numeroLot)
			VALUES ('.$i.','.$curseur[0][0].')';
			$exec=$bdd->prepare($sql) ;
        	$exec->execute() ;
        }
		
	}

	/**
	 * Récupère un utilisateur selon un identifiant.
	 * @param username : nom d'utilisateur du compte
	 * @param pass : le mot de passe de du compte 
	 * @return curseur : tableau contenant id, autorisation, nom et prénom si correct; rien si ids incorrects
	*/
	function getUser($username, $pass) {
		require "connectBdd.php";

		$sql = 
		"SELECT gsb_id, gsb_autorisation, gsb_nom, gsb_prenom 
		FROM gsb_visitualisateur 
		WHERE gsb_login = :username AND gsb_mdp = :pass";

		$exec=$bdd->prepare($sql);
		$exec->bindParam('username', $username);
		$exec->bindParam('pass', $pass);
		$exec->execute();
		$curseur=$exec->fetchAll();
		return $curseur;
	}

	/**
	 * Récupère tous les échantillions disponibles.
	 * @return curseur : tableau 2D contenant tous les échantillions non donnés.
	 */
	function getEchantillons() {
		require "connectBdd.php";

		$sql = "SELECT gsb_echantillon.*, gsb_medicament.libelle FROM gsb_echantillon 
		INNER JOIN gsb_lot ON gsb_lot.gsb_numero = gsb_echantillon.gsb_numeroLot
		INNER JOIN gsb_medicament ON gsb_medicament.id = gsb_lot.gsb_idMedicament
		WHERE dateSortie IS NULL";

		$exec = $bdd->prepare($sql);
		$exec->execute();
		$curseur = $exec->fetchAll();
		return $curseur;
	}

	/**
	 * Recupère tous les échantillions selon un médicament, date de sortie et visiteur
	 * @param medicamentId : Identifiant du médicament
	 * @param dateSortie : date the sortie du médicement
	 * @param visiteurId : identifiant de l'utilisateur
	 * @return curseur : tous les échantillons correspondants à la recherche. 
	 */
	function getEchantillonsSortis($medicamentId, $dateSortie, $visiteurId) {
		require "connectBdd.php";

		$sql = "SELECT gsb_echantillon.gsb_numero, gsb_lot.gsb_numero, gsb_medicament.libelle, gsb_visitualisateur.gsb_nom, gsb_visitualisateur.gsb_prenom
				FROM gsb_lot 
				INNER JOIN gsb_echantillon ON gsb_echantillon.gsb_numeroLot = gsb_lot.gsb_numero
				INNER JOIN gsb_medicament ON gsb_medicament.id = gsb_lot.gsb_idMedicament
				INNER JOIN gsb_visitualisateur ON gsb_visitualisateur.gsb_id = gsb_echantillon.gsb_idVisitualisateur
		
				WHERE gsb_medicament.libelle = ':medicament' AND gsb_echantillon.dateSortie LIKE ':date' AND gsb_visitualisateur.gsb_nom = ':visiteur'
				
				ORDER BY gsb_echantillon.gsb_numero, gsb_lot.gsb_numero";
				
		$exec = $bdd->prepare($sql);
		$exec->bindParam('medicament', $medicamentId);
		$exec->bindParam('date', $dateSortie);
		$exec->bindParam('visiteur', $visiteurId);

		$exec->execute();
		$curseur=$exec->fetchAll();
		return $curseur;
	}
	
	function getMedicaments() {
		require "connectBdd.php";

		$sql = 
		"select * FROM gsb_medicament";

		$exec = $bdd->prepare($sql);
		$exec->execute();
		$curseur = $exec->fetchAll();
		return $curseur;
	}



	/*					Consultation					*/

		///		Visiteur Medical

			/**
			 * Récupère les échantillon en fonction d'un médicament donné.
			 * @param libelleMedicament : libelle du medicament
			 * @return tableau : numero d'echantillon, numero de lot, date de sortie, date donnation, id du visiteur, matriculeMedecins
			*/
			function getEchantillonMedicament($libelleMedicament) {
				require "connectBdd.php";
				$sql = "SELECT gsb_echantillon.*
						FROM gsb_echantillon
						WHERE gsb_echantillon.gsb_numero 
						IN (SELECT gsb_lot.gsb_numero 
							FROM gsb_lot 
							INNER JOIN gsb_medicament 
							ON gsb_medicament.id = gsb_lot.gsb_idMedicament 
							WHERE libelle = ".$libelleMedicament.");";
				$exec=$bdd->prepare($sql);
				$exec->execute();
				$curseur=$exec->fetchAll();
				return $curseur;
			}

			/**
			 * Récupère les échantillon en fonction d'une date donné.
			 * @param dateVisite : Date de visite chez le médecin à qui l'échantillon a été laissé
			 * @return tableau : numero d'echantillon, numero de lot, date de sortie, date donnation, id du visiteur, matriculeMedecins
			*/
			function getEchantillonDate($dateVisite) {
				require "connectBdd.php";
				$sql = "SELECT gsb_echantillon.*
						FROM gsb_echantillon
						WHERE gsb_echantillon.dateDon = ".$DateVisite.";";
				$exec=$bdd->prepare($sql);
				$exec->execute();
				$curseur=$exec->fetchAll();
				return $curseur;

			}

			/**
			 * Récupère les échantillon en fonction d'un médicament donné.
			 * @param nom : nom d'un medecin
			 * @param prenom : prenom d'un medecin
			 * @return tableau : numero d'echantillon, numero de lot, date de sortie, date donnation, id du visiteur, matriculeMedecins
			*/	
			function getEchantillonMedecin($nom,$prenom) {
				require "connectBdd.php";
				$sql = "SELECT gsb_echantillon.*
						FROM gsb_echantillon
						INNER JOIN gsb_medecins
						ON gsb_medecins.gsb_matricule = gsb_echantillon.gsb_matriculeMedecins
						WHERE gsb_medecins.gsb_nom = ".$nom." AND gsb_medecins.gsb_prenom = ".$prenom.";";			
				$exec=$bdd->prepare($sql);
				$exec->execute();
				$curseur=$exec->fetchAll();
				return $curseur;
			}




		///		Production




		///		Magasin

	/*					FinConsulation					*/



?>