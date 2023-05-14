<?php

namespace App\Controller;

use App\Entity\Exemplaire;
use App\Entity\Livre;
use App\Repository\ExemplaireRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api')]
class ExemplaireController extends AbstractController
{
    /**
     * Lister Exemplaires
     *
     * @param ExemplaireRepository $exemplaireRepository
     * @return JsonResponse
     */
    #[Route('/exemplaires', name:'get_exemplaire', methods:['GET'])]
function index(ExemplaireRepository $exemplaireRepository): JsonResponse
    {
    $exemplaires = $exemplaireRepository->findAll();
    $data = [];
    foreach ($exemplaires as $exemplaire) {
        $data[] = [
            "id" => $exemplaire->getId(),
            "livre_id" => $exemplaire->getLivre()->getId(),
            "nombre" => $exemplaire->getNombre(),
            "numero" => $exemplaire->getNumero(),
        ];
    }

    return $this->json($data);
}

/**
 * Voir un Exemplaire
 *
 * @param ExemplaireRepository $exemplaireRepository
 * @param integer $id
 * @return JsonResponse
 */
#[Route('/exemplaire/{id}', name:'show_exemplaire', methods:['GET'])]
function show_exemplaire(int $id, ExemplaireRepository $exemplaireRepository): JsonResponse
    {
    $exemplaire = $exemplaireRepository->find($id);

    if ($exemplaire) {

        $data = [
            "id" => $exemplaire->getId(),
            "livre_id" => $exemplaire->getLivre()->getId(),
            "nombre" => $exemplaire->getNombre(),
            "numero" => $exemplaire->getNumero(),
        ];

        return $this->json($data);

    } else {
        return $this->json("Pas d'exemplaire pour l'id " . $id, 404);
    }

}

/**
 * Ajouter ou Créer exemplaire
 *
 * @param Request $request
 * @param ManagerRegistry $managerRegistry
 * @return Response
 */
#[Route('/exemplaire', name:"add_exemplaire", methods:['POST'])]
function addExemplaire(Request $request, ManagerRegistry $managerRegistry)
    {
    $entityManager = $managerRegistry->getManager();

    $data = json_decode($request->getContent(), true);

    if (is_numeric($data['livre_id']) && $data['numero']) {
        $exemplaire = new Exemplaire();

        $livre = $entityManager->getRepository(Livre::class)->find($data['livre_id']);

        if ($livre) {
            $exemplaire->setLivre($livre);
            $exemplaire->setNombre($data['nombre']);
            $exemplaire->setNumero($data['numero']);

            $entityManager->persist($exemplaire);
            $entityManager->flush();

            $data = [
                "id" => $exemplaire->getId(),
                "livre_id" => $exemplaire->getLivre()->getId(),
                "nombre" => $exemplaire->getNombre(),
                "numero" => $exemplaire->getNumero(),
            ];

            return $this->json('Exemplaire ajouté avec succès', 200, $data);

        } else {
            return $this->json("Le livre correspondant à l'identifiant saisi n'existe pas!", 404);
        }
    } else {
        return $this->json('Erreur de remplissage des paramettres', 402);
    }

}

/**
 * Mettre à jour Exemplaire
 * @param Request $request
 * @param ManagerRegistry $managerRegistry
 * @param int $id
 * @return Response
 */
#[Route('/exemplaire/{id}', name:'update_exemplaire', methods:['PUT', 'PATCH', 'POST'])]
function update(Request $request, ManagerRegistry $managerRegistry, int $id): Response
    {
    $entityManager = $managerRegistry->getManager();

    $exemplaire = $entityManager->getRepository(Exemplaire::class)->find($id);

    if (!$exemplaire) {
        return $this->json("Cette identifiant n'existe pas", 400);
    }

    $content = json_decode($request->getContent(), true);

    if (is_numeric($content['livre_id']) && is_numeric($content['nombre']) && $content['numero']) {

        $livre = $entityManager->getRepository(Livre::class)->find($content['livre_id']);

        if ($livre) {
            $exemplaire->setLivre($livre);
            $exemplaire->setNombre($content['nombre']);
            $exemplaire->setNumero($content['numero']);

            $entityManager->flush();

            $data = [
                "id" => $exemplaire->getId(),
                "livre_id" => $exemplaire->getLivre()->getId(),
                "nombre" => $exemplaire->getNombre(),
                "numero" => $exemplaire->getNumero(),
            ];

            return $this->json(["Modification avec succès", $data], 200);

        } else {
            return $this->json(["Le livre correspondant à l'identifiant saisi n'existe pas!"], 404);
        }

    } else {

        $data = [
            "id" => $exemplaire->getId(),
            "livre_id" => $exemplaire->getLivre()->getId(),
            "nombre" => $exemplaire->getNombre(),
            "numero" => $exemplaire->getNumero(),
        ];

        return $this->json(["Modification non succès, veuillez bien saisir le formulaire", $data], 402);

    }

}

/**
 * Supprimer Exemplaire
 * @param ManagerRegistry $managerRegistry
 * @param int $id
 * @return Response
 */
#[Route('/exemplaire/{id}', name:'delete_exemplaire', methods:['DELETE'])]
function delete(ManagerRegistry $managerRegistry, int $id, ): Response
    {
    $entityManager = $managerRegistry->getManager();
    $exemplaire = $entityManager->getRepository(Exemplaire::class)->find($id);

    if (!$exemplaire) {
        return $this->json("Aucun correspondance à cet identifiant", 404);
    }

    $entityManager->remove($exemplaire);
    $entityManager->flush();

    return $this->json(["Exemplaire supprimé avec succès"], 200);
}
}
