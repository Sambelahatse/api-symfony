<?php

namespace App\Controller;

use App\Entity\Emprunteur;
use App\Repository\EmprunteurRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api')]
class EmprunteurController extends AbstractController
{
    /**
     * Lister Emprunteurs
     *
     * @param EmprunteurRepository $emprunteurRepository
     * @return JsonResponse
     */
    #[Route('/emprunteurs', name: 'get_emprunteur', methods:['GET'])]
    public function index(EmprunteurRepository $emprunteurRepository): JsonResponse
    {
        $emprunteurs = $emprunteurRepository->findAll();
        $data = [];
        foreach ($emprunteurs as $emprunteur) {
            $data = [
                "nom" => $emprunteur->getNom(),
                "prenom" => $emprunteur->getPrenom(),
                "adresse" => $emprunteur->getAdresse(),
                "contact" => $emprunteur->getContact(),
            ];
        }

        return $this->json($data);    
    }

    /**
     * Voir un Emprunteur
     *
     * @param EmprunteurRepository $emprunteurRepository
     * @param integer $id
     * @return JsonResponse
     */
    #[Route('/emprunteur/{id}', name: 'show_emprunteur', methods: ['GET'])]
    public function show_emprunteur(int $id, EmprunteurRepository $emprunteurRepository): JsonResponse
    {
        $emprunteur = $emprunteurRepository->find($id);
        
        if ($emprunteur) {

            $data = [
                "nom" => $emprunteur->getNom(),
                "prenom" => $emprunteur->getPrenom(),
                "adresse" => $emprunteur->getAdresse(),
                "contact" => $emprunteur->getContact(),
            ];

            return $this->json($data); 

        } else {
            return $this->json("Pas d'emprunteur pour l'id " . $id, 404);
        }
        

    }

    /**
     * Ajouter ou Créer emprunteur
     *
     * @param Request $request
     * @param ManagerRegistry $managerRegistry
     * @return Response
     */
    #[Route('/emprunteur', name:"add_emprunteur", methods:['POST'])]
    public function addLivre(Request $request, ManagerRegistry $managerRegistry): Response
    {
        $entityManager = $managerRegistry->getManager();

        $data = json_decode($request->getContent(), true);

        if ($data!=null) {
            $emprunteur = new Emprunteur();
            $emprunteur->setNom($data['nom']);
            $emprunteur->setPrenom($data['prenom']);
            $emprunteur->setAdresse($data['adresse']);
            $emprunteur->setContact($data['contact']);

            $entityManager->persist($emprunteur);
            $entityManager->flush();

            return $this->json('Emprunteur ajouté avec succès',200,$data);

        } else {
            return $this->json('Erreur de remplissage des paramettre',400);
        }

    }


    /**
     * Mettre à jour Emprunteur
     * @param Request $request
     * @param ManagerRegistry $managerRegistry
     * @param int $id
     * @return Response
     */
    #[Route('/emprunteur/{id}', name:'update_emprunteur', methods:['PUT', 'PATCH', 'POST'])]
    public function update(Request $request, ManagerRegistry $managerRegistry, int $id): Response
    {
        $entityManager = $managerRegistry->getManager();
        $emprunteur = $entityManager->getRepository(Emprunteur::class)->find($id);

        if (!$emprunteur) {
            return $this->json("Cette identifiant n'existe pas",400);
        }

        $content = json_decode($request->getContent(), true);

        $emprunteur->setNom($content['nom']);
        $emprunteur->setPrenom($content['prenom']);
        $emprunteur->setAdresse($content['adresse']);
        $emprunteur->setContact($content['contact']);
        $entityManager->flush();
        $data = [
                "id"          => $emprunteur->getId(),
                "nom"         => $emprunteur->getNom(),
                "prenom"      => $emprunteur->getPrenom(),
                "adresse"     => $emprunteur->getAdresse(),
                "contact"     => $emprunteur->getContact(),
            ];

        return $this->json($data, 200);
        
    }




    /**
     * Supprimer Emprunteur
     * @param ManagerRegistry $managerRegistry
     * @param int $id
     * @return Response
     */
    #[Route('/emprunteur/{id}', name:'delete_emprunteur', methods:['DELETE'])]
    public function delete(ManagerRegistry $managerRegistry, int $id, ): Response
    {
        $entityManager = $managerRegistry->getManager();
        $emprunteur = $entityManager->getRepository(Emprunteur::class)->find($id);

        if (!$emprunteur) {
            return $this->json("Aucun correspondance à cet identifiant", 404);
        }

        $entityManager->remove($emprunteur);
        $entityManager->flush();
        
        return $this->json("Emprunteur supprimé avec succès", 200);
    }



}
