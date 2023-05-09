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
    #[Route('/exemplaires', name: 'get_exemplaire', methods:['GET'])]
    public function index(ExemplaireRepository $exemplaireRepository): JsonResponse
    {
        $exemplaires = $exemplaireRepository->findAll();
        $data = [];
        foreach ($exemplaires as $exemplaire) {
            $data = [
                "livre_id" => $exemplaire->getLivre()->getId(),
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
    #[Route('/exemplaire/{id}', name: 'show_exemplaire', methods: ['GET'])]
    public function show_exemplaire(int $id, ExemplaireRepository $exemplaireRepository): JsonResponse
    {
        $exemplaire = $exemplaireRepository->find($id);
        
        if ($exemplaire) {

            $data = [
                "livre_id" => $exemplaire->getLivre()->getId(),
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
    public function addExemplaire(Request $request, ManagerRegistry $managerRegistry): Response
    {
        $entityManager = $managerRegistry->getManager();

        $data = json_decode($request->getContent(), true);

        if ($data!=null) {
            $exemplaire = new Exemplaire();

            $livre = $entityManager->getRepository(Livre::class)->find($data['livre_id']);

            $exemplaire->setLivre($livre);
            $exemplaire->setNumero($data['numero']);

            $entityManager->persist($exemplaire);
            $entityManager->flush();

            return $this->json('Exemplaire ajouté avec succès',200,$data);

        } else {
            return $this->json('Erreur de remplissage des paramettre',400);
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
    public function update(Request $request, ManagerRegistry $managerRegistry, int $id): Response
    {
        $entityManager = $managerRegistry->getManager();
        $exemplaire = $entityManager->getRepository(Exemplaire::class)->find($id);

        if (!$exemplaire) {
            return $this->json("Cette identifiant n'existe pas",400);
        }

        $content = json_decode($request->getContent(), true);

        $exemplaire->setLivre($content['livre_id']);
        $exemplaire->setNumero($content['numero']);
        $entityManager->flush();
        $data = [
                "id"          => $exemplaire->getId(),
                "livre_id"         => $exemplaire->getLivre(),
                "numero"      => $exemplaire->getNumero(),
            ];

        return $this->json($data, 200);
        
    }




    /**
     * Supprimer Exemplaire
     * @param ManagerRegistry $managerRegistry
     * @param int $id
     * @return Response
     */
    #[Route('/exemplaire/{id}', name:'delete_exemplaire', methods:['DELETE'])]
    public function delete(ManagerRegistry $managerRegistry, int $id, ): Response
    {
        $entityManager = $managerRegistry->getManager();
        $exemplaire = $entityManager->getRepository(Exemplaire::class)->find($id);

        if (!$exemplaire) {
            return $this->json("Aucun correspondance à cet identifiant", 404);
        }

        $entityManager->remove($exemplaire);
        $entityManager->flush();
        
        return $this->json("Exemplaire supprimé avec succès", 200);
    }
}
