<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EquipeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EquipeRepository $equipeRepository,
    ) {
    }

    #[Route('/equipes', name: 'equipes_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return new JsonResponse([
                'message' => 'Corps de requête JSON invalide.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $nom = isset($payload['nom']) ? trim((string) $payload['nom']) : '';
        if ($nom === '') {
            return new JsonResponse([
                'message' => 'Le champ "nom" est obligatoire.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $equipe = new Equipe();
        $equipe->setNom($nom);

        $this->entityManager->persist($equipe);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $equipe->getId(),
            'nom' => $equipe->getNom(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/equipes/{id}', name: 'equipes_update', requirements: ['id' => '\\d+'], methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $equipe = $this->equipeRepository->find($id);
        if (!$equipe instanceof Equipe) {
            return new JsonResponse([
                'message' => sprintf('Aucune équipe trouvée pour l\'identifiant %d.', $id),
            ], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return new JsonResponse([
                'message' => 'Corps de requête JSON invalide.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (array_key_exists('nom', $payload)) {
            $nom = trim((string) $payload['nom']);
            if ($nom === '') {
                return new JsonResponse([
                    'message' => 'Le champ "nom" ne peut pas être vide.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $equipe->setNom($nom);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $equipe->getId(),
            'nom' => $equipe->getNom(),
        ]);
    }
}
