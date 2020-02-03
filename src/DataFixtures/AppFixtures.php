<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\TennisMatch;
use App\Entity\TennisSet;
use App\Entity\TennisTour;
use App\Entity\TennisTournoi;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR'); // create a French faker

        // On crée un Tournoi
		$tableauStatutTournoi = array("Non Commencé", "Phase d'incriptions", "Commencé", "Terminé");
		for($t=1; $t<=4; $t++){
        $tournoi = new TennisTournoi();
        $tournoi->setNom($faker->realText($maxNbChars = 10, $indexSize = 2));
        $tournoi->setAdresse($faker->realText($maxNbChars = 10, $indexSize = 2));
        $tournoi->setDateDebuttournoi($faker->dateTimeBetween($startDate = '-1 year', $endDate = '-1 month', $timezone = null));
        $tournoi->setDateFintournoi($faker->dateTimeBetween($startDate = '-1 month', $endDate = 'now', $timezone = null));
        $tournoi->setEstVisible($faker->boolean($chanceOfGettingTrue = 50));
        $tournoi->setSurface($faker->realText($maxNbChars = 10, $indexSize = 2));
		$age=$faker->regexify('[1-5][0-9]');
		$ageplusdeux=$age+2;
        $tournoi->setCategorieAge("".$age."/".$ageplusdeux."");
        $tournoi->setGenreHomme($faker->boolean($chanceOfGettingTrue = 50));
        $tournoi->setDescription($faker->realText($maxNbChars = 50, $indexSize = 2));
        $tournoi->setDateDebutInscriptions($faker->dateTimeBetween($startDate = '-1 year', $endDate = '-1 month', $timezone = null));
        $tournoi->setDateFinInscriptions($faker->dateTimeBetween($startDate = '-1 month', $endDate = 'now', $timezone = null));
        $tournoi->setInscriptionsManuelles($faker->boolean($chanceOfGettingTrue = 50));
        $tournoi->setNbPlaces($faker->numberBetween(10,20));
        $tournoi->setMotDePasse($faker->realText($maxNbChars = 10, $indexSize = 2));
        $tournoi->setValidationInscriptions($faker->boolean($chanceOfGettingTrue = 50));
		$nbSetsGagnants=$faker->numberBetween(2,3);
        $tournoi->setNbSetsGagnants($nbSetsGagnants);
        $tournoi->setStatut($tableauStatutTournoi[$t-1]);
        $manager->persist($tournoi);
        // Ajout de tours dans ce Tournoi (sauf s tournoi non commencé ou en phase d'inscriptions (pas possible de creer de tour & matchs)
		if($tournoi->getStatut() != "Non Commencé" && $tournoi->getStatut() != "Phase d'incriptions"){
			$tableauStatutTour = array("Terminé", "Commencé", "Organisation");
            for($i=1; $i<=3; $i++){
                $tour = new TennisTour();
                $tour->setType("Intermédiaire");
                $tour->setDateFinTour($faker->dateTimeBetween($startDate = '-5 days', $endDate = 'now', $timezone = null));
				if($tournoi->getStatut() == "Terminé")
				{
					$tour->setStatut("Terminé");
				}
				else 
				{
					$tour->setStatut($tableauStatutTour[$i-1]);
				}
                $tour->setNumero($i);
                $tour->setTennistournoi($tournoi);
                $tournoi->addTennisTour($tour);
                // Ajout de matchs dans chaque tour
				$tableauEtatMatch = array("Pas encore joué", "Terminé");
                for($j=1; $j<=2; $j++){
                    $match = new TennisMatch();
					if($tour->getStatut() == "Terminé")
					{
						$match->setEtat("Terminé");
					}
					else if($tour->getStatut() == "Commencé")
					{
						$match->setEtat($tableauEtatMatch[$j-1]);
					}
					else //Organisation (crétion des matchs, aucun joués)
					{
						$match->setEtat("Pas encore joué");
					}
                    $match->setTennisTour($tour);
                    $tour->addTennisMatch($match);
                    $manager->persist($tour);
                    $manager->persist($match);
                    // Ajout de sets dans chaque match
                    for($k=1; $k<=$nbSetsGagnants; $k++){
                        $set = new TennisSet();
                        $set->setNbJeuxDuGagnant(6);
                        $set->setNbJeuxDuPerdant($faker->numberBetween(0,4));
                        $set->setTennisMatch($match);
                        $match->addTennisSet($set);
                        $manager->persist($set);

                    }
                }
            }
        $manager->flush();
		}
		}
	}
}
