<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Repository\LivreRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api')]
class LivreController extends AbstractController
{
    // Lister les Livres
    #[Route('/livres', name:'livre_index', methods:['GET'])]
function index(LivreRepository $livreRepository): Response
    {
    $livres = $livreRepository->findAll();
    $data = [];
    foreach ($livres as $livre) {
        $data[] = [
            "id" => $livre->getId(),
            "titre" => $livre->getTitre(),
            "auteur" => $livre->getAuteur(),
            "editeur" => $livre->getEditeur(),
            "dateParution" => $livre->getDateParution(),
        ];
    }

    return $this->json($data);

}

//Voir un Livre
#[Route('/livre/{id}', name:'show_livre', methods:['GET'])]
function show_livre(int $id, LivreRepository $livreRepository): Response
    {
    $livre = $livreRepository->find($id);

    if ($livre) {

        $data = [
            "id" => $livre->getId(),
            "titre" => $livre->getTitre(),
            "auteur" => $livre->getAuteur(),
            "editeur" => $livre->getEditeur(),
            "dateParution" => $livre->getDateParution(),
        ];

        return $this->json($data);

    } else {
        return $this->json("Pas de livre pour l'id " . $id, 404);
    }

}

// Ajouter un Livre
#[Route('/livre', name:"addLivre", methods:['POST'])]
function addLivre(Request $request, ManagerRegistry $managerRegistry): Response
    {
    $entityManager = $managerRegistry->getManager();

    $data = json_decode($request->getContent(), true);

    //Convertir date de format string en DateTime
    $timestamp = strtotime($data['dateParution']);
    $dateParution = date("d-m-Y", $timestamp);
    $date = date_create($dateParution);

    if ($data) {
        $livre = new Livre();
        $livre->setTitre($data['titre']);
        $livre->setAuteur($data['auteur']);
        $livre->setEditeur($data['editeur']);
        $livre->setDateParution($date);

        $entityManager->persist($livre);
        $entityManager->flush();

        return $this->json('Livre ajouté avec succès', 200, $data);

    } else {
        return $this->json('Erreur de remplissage des paramettre', 400);
    }

}

//Mettre à jour un livre
#[Route('/livre/{id}', name:'update_livre', methods:['PUT', 'PATCH', 'POST'])]
function update(Request $request, ManagerRegistry $managerRegistry, int $id)
    {
    $entityManager = $managerRegistry->getManager();
    $livre = $entityManager->getRepository(Livre::class)->find($id);

    if (!$livre) {
        return $this->json("Cette identifiant n'existe pas", 400);
    }

    $content = json_decode($request->getContent(), true);

    //Convertir date de format string en DateTime
    $timestamp = strtotime($content['dateParution']);
    $dateParution = date("d-m-Y", $timestamp);
    $date = date_create($dateParution);

    $livre->setTitre($content['titre']);
    $livre->setAuteur($content['auteur']);
    $livre->setEditeur($content['editeur']);
    $livre->setDateParution($date);
    $entityManager->flush();
    $data = [
        "id" => $livre->getId(),
        "titre" => $livre->getTitre(),
        "auteur" => $livre->getAuteur(),
        "editeur" => $livre->getEditeur(),
        "dateParution" => $livre->getDateParution(),
    ];

    return $this->json($data, 200);

}

//Supprimer un livre
#[Route('/livre/{id}', name:'delete_livre', methods:['DELETE'])]
function delete(int $id, ManagerRegistry $managerRegistry)
    {
    $entityManager = $managerRegistry->getManager();
    $livre = $entityManager->getRepository(Livre::class)->find($id);

    if (!$livre) {
        return $this->json("Aucun correspondance à cet identifiant", 404);
    }

    $entityManager->remove($livre);
    $entityManager->flush();

    return $this->json("Livre supprimé avec succès", 200);
}
}
