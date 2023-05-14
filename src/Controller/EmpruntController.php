<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Entity\Emprunteur;
use App\Entity\Exemplaire;
use App\Repository\EmpruntRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api')]
class EmpruntController extends AbstractController
{

/**
 * Lister les emprunts
 *
 * @param EmpruntRepository $empruntRepository
 * @return JsonResponse
 */
    #[Route('/emprunts', name:'get_emprunt', methods:['GET'])]
function index(EmpruntRepository $empruntRepository): JsonResponse
    {
    $emprunts = $empruntRepository->findAll();
    $data = [];
    foreach ($emprunts as $emprunt) {
        $data[] = [

            "id" => $emprunt->getId(),
            "exemplaire_id" => $emprunt->getExemplaire()->getLivre()->getTitre(),
            "emprunteur_id" => $emprunt->getEmprunteur()->getNom(),
            "nombreEmprunte" => $emprunt->getNombreEmprunte(),
            "dateEmprunt" => $emprunt->getDateEmprunt(),
            "dateRetour" => $emprunt->getDateRetour(),
        ];
    }

    return $this->json($data);
}

/**
 * Voir un Emprunt
 *
 * @param EmpruntRepository $empruntRepository
 * @param integer $id
 * @return JsonResponse
 */
#[Route('/emprunt/{id}', name:'show_emprunt', methods:['GET'])]
function show_emprunt(int $id, EmpruntRepository $empruntRepository): JsonResponse
    {
    $emprunt = $empruntRepository->find($id);

    if ($emprunt) {

        $data = [
            "id" => $emprunt->getId(),
            "exemplaire_id" => $emprunt->getExemplaire()->getLivre()->getTitre(),
            "emprunteur_id" => $emprunt->getEmprunteur()->getNom(),
            "nombreEmprunte" => $emprunt->getNombreEmprunte(),
            "dateEmprunt" => $emprunt->getDateEmprunt(),
            "dateRetour" => $emprunt->getDateRetour(),
        ];

        return $this->json($data);

    } else {
        return $this->json("Pas d'emprunt pour l'id " . $id, 404);
    }

}

/**
 * Ajouter ou Créer emprunt
 *
 * @param Request $request
 * @param ManagerRegistry $managerRegistry
 * @return Response
 */
#[Route('/emprunt', name:"add_emprunt", methods:['POST'])]
function addExemplaire(Request $request, ManagerRegistry $managerRegistry)
    {
    $entityManager = $managerRegistry->getManager();

    $data = json_decode($request->getContent(), true);

    if (is_numeric($data['exemplaire_id']) && is_numeric($data['emprunteur_id']) && is_numeric($data['nombreEmprunte']) && $data['dateRetour'] && $data['dateEmprunt']) {

        $emprunt = new Emprunt();

        $exemplaire = $entityManager->getRepository(Exemplaire::class)->find($data['exemplaire_id']);
        $emprunteur = $entityManager->getRepository(Emprunteur::class)->find($data['emprunteur_id']);

        //Convertir date de format string en DateTime
        $timestamp1 = strtotime($data['dateEmprunt']);
        $dateE = date("d-m-Y", $timestamp1);
        $dateEmprunt = date_create($dateE);

        $timestamp2 = strtotime($data['dateRetour']);
        $dateR = date("d-m-Y", $timestamp2);
        $dateRetour = date_create($dateR);

        if ($exemplaire && $emprunteur) {

            if ($data['nombreEmprunte'] > $exemplaire->getNombre()) {
                return $this->json("Le nombre d'exemplaire disponible est insuffisant", 200);
            }

            // Modification de nombre des exemplaires existants

            $exemplaire->setNombre($exemplaire->getNombre() - $data['nombreEmprunte']);

            $emprunt->setExemplaire($exemplaire);
            $emprunt->setEmprunteur($emprunteur);
            $emprunt->setNombreEmprunte($data['nombreEmprunte']);
            $emprunt->setDateEmprunt($dateEmprunt);
            $emprunt->setDateRetour($dateRetour);
            $entityManager->persist($emprunt);
            $entityManager->flush();

            $data = [
                "id" => $emprunt->getId(),
                "exemplaire_id" => $emprunt->getExemplaire()->getLivre()->getTitre(),
                "emprunteur_id" => $emprunt->getEmprunteur()->getNom(),
                "nombreEmprunte" => $emprunt->getNombreEmprunte(),
                "dateEmprunt" => $emprunt->getDateEmprunt(),
                "dateRetour" => $emprunt->getDateRetour(),
            ];

            return $this->json(['Emprunt de livre avec succès', $data], 200);

        } else {
            return $this->json(["L'exemplaire et/ou l'emprunteur correspondant à l'identifiant saisi n'existe pas!"], 404);
        }
    } else {
        return $this->json(['Erreur de remplissage des paramettres'], 402);
    }

}

/**
 * Mettre à jour Emprunt
 * @param Request $request
 * @param ManagerRegistry $managerRegistry
 * @param int $id
 * @return Response
 */
#[Route('/emprunt/{id}', name:'update_emprunt', methods:['PUT', 'PATCH', 'POST'])]
function update(Request $request, ManagerRegistry $managerRegistry, int $id): Response
    {
    $entityManager = $managerRegistry->getManager();

    $emprunt = $entityManager->getRepository(Emprunt::class)->find($id);

    if (!$emprunt) {
        return $this->json("Cette identifiant n'existe pas", 404);
    }

    $content = json_decode($request->getContent(), true);

    if (is_numeric($content['exemplaire_id']) && is_numeric($content['emprunteur_id']) && is_numeric($content['nombreEmprunte']) && $content['dateRetour'] && $content['dateEmprunt']) {

        $exemplaire = $entityManager->getRepository(Exemplaire::class)->find($content['exemplaire_id']);
        $emprunteur = $entityManager->getRepository(Emprunteur::class)->find($content['emprunteur_id']);

        //Convertir date de format string en DateTime
        $timestamp1 = strtotime($content['dateEmprunt']);
        $dateE = date("d-m-Y", $timestamp1);
        $dateEmprunt = date_create($dateE);

        $timestamp2 = strtotime($content['dateRetour']);
        $dateR = date("d-m-Y", $timestamp2);
        $dateRetour = date_create($dateR);

        if ($exemplaire && $emprunteur) {

            if ($content['nombreEmprunte'] > $exemplaire->getNombre()) {
                return $this->json("Le nombre d'exemplaire disponible est insuffisant", 200);
            }

            if ($content['nombreEmprunte'] > $emprunt->getNombreEmprunte()) {
                $difference = $content['nombreEmprunte'] - $emprunt->getNombreEmprunte();
                $exemplaire->setNombre($exemplaire->getNombre() - $difference);

            } else {
                $addition = $emprunt->getNombreEmprunte() - $content['nombreEmprunte'];
                $exemplaire->setNombre($exemplaire->getNombre() + $addition);

            }

            $emprunt->setExemplaire($exemplaire);
            $emprunt->setEmprunteur($emprunteur);
            $emprunt->setNombreEmprunte($content['nombreEmprunte']);
            $emprunt->setDateEmprunt($dateEmprunt);
            $emprunt->setDateRetour($dateRetour);
            $entityManager->persist($emprunt);
            $entityManager->flush();

            $data = [
                "id" => $emprunt->getId(),
                "exemplaire_id" => $emprunt->getExemplaire()->getLivre()->getTitre(),
                "emprunteur_id" => $emprunt->getEmprunteur()->getNom(),
                "nombreEmprunte" => $emprunt->getNombreEmprunte(),
                "dateEmprunt" => $emprunt->getDateEmprunt(),
                "dateRetour" => $emprunt->getDateRetour(),
            ];

            return $this->json(["Modification d'emprunt avec succès", $data], 200);

        } else {
            return $this->json(["L'exemplaire et/ou l'emprunteur correspondant à l'identifiant saisi n'existe pas!"], 404);
        }
    } else {

        $data = [
            "id" => $emprunt->getId(),
            "exemplaire_id" => $emprunt->getExemplaire()->getLivre()->getTitre(),
            "emprunteur_id" => $emprunt->getEmprunteur()->getNom(),
            "nombreEmprunte" => $emprunt->getNombreEmprunte(),
            "dateEmprunt" => $emprunt->getDateEmprunt(),
            "dateRetour" => $emprunt->getDateRetour(),
        ];

        return $this->json(["Modification non succès, veuillez bien saisir le formulaire", $data], 402);

    }

}

/**
 * Supprimer Emprunt ou Desemprunter livre
 * @param ManagerRegistry $managerRegistry
 * @param int $id
 * @return Response
 */
#[Route('/emprunt/{id}', name:'delete_emprunt', methods:['DELETE'])]
function delete(ManagerRegistry $managerRegistry, int $id, ): Response
    {
    $entityManager = $managerRegistry->getManager();
    $emprunt = $entityManager->getRepository(Emprunt::class)->find($id);
    $exemplaire = $entityManager->getRepository(Exemplaire::class)->find($emprunt->getExemplaire()->getId());

    if (!$emprunt) {
        return $this->json("Aucun correspondance à cet identifiant", 404);
    }

    if (!$exemplaire) {
        return $this->json(["Une erreur est survenue lors de suppression"], 402);
    }

    $exemplaire->setNombre($exemplaire->getNombre() + $emprunt->getNombreEmprunte());
    $entityManager->remove($emprunt);
    $entityManager->flush();

    return $this->json(["Emprunt supprimé avec succès"], 200);
}

}
