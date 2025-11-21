<?php

namespace App\Controller;

use App\Entity\Epreuve;
use App\Entity\User;

use App\Repository\UserRepository;
use App\Repository\EquipeRepository;
use App\Repository\EpreuveRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTime;
use InvalidArgumentException;



use App\Utils\Utils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


final class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }
    
 #[Route('/api/mobile/register', name: 'app_mobile_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        ValidatorInterface $validator
    ): JsonResponse {
        // 1) vérifier content-type
        if (0 !== strpos($request->headers->get('Content-Type', ''), 'application/json')) {
            return new JsonResponse(['error' => 'Content-Type must be application/json'], Response::HTTP_BAD_REQUEST);
        }

        // 2) parser le JSON en sécurité
        $data = json_decode($request->getContent(), false);
        if (null === $data) {
            return new JsonResponse(['error' => 'JSON invalide ou manquant'], Response::HTTP_BAD_REQUEST);
        }

        // 3) récupérer et valider la présence des champs
        $email = $data->Email ?? null;
        $plainPassword = $data->Password ?? null;
        $nom = $data->Nom ?? null;
        $prenom = $data->Prenom ?? null;

        if (!$email || !$plainPassword || !$nom || !$prenom) {
            return new JsonResponse(['error' => 'Champs manquants'], Response::HTTP_BAD_REQUEST);
        }

        // 4) vérif format email basique et unicité
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Email invalide'], Response::HTTP_BAD_REQUEST);
        }

        if ($userRepository->findOneBy(['email' => mb_strtolower($email)])) {
            return new JsonResponse(['error' => 'Email déjà utilisé'], Response::HTTP_CONFLICT);
        }

        // 5) règles mot de passe minimales (adapter à ta politique)
        if (mb_strlen($plainPassword) < 8) {
            return new JsonResponse(['error' => 'Mot de passe trop court (= 8 caractères)'], Response::HTTP_BAD_REQUEST);
        }
        // tu peux ajouter complexity (majuscule, chiffre, symbole) si besoin

        // 6) création utilisateur
        $user = new User();
        $user->setEmail(mb_strtolower($email));
        $user->setNom(trim($nom));
        $user->setPrenom(trim($prenom));
        $user->setStatut(false); // défaut: inactif en attendant vérif e-mail
        $user->setRoles(['ROLE_USER']);

        $hashed = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        // 7) validation de l'entité (contraintes @Assert sur l'entité)
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $err = [];
            foreach ($errors as $violation) {
                $err[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            return new JsonResponse(['error' => 'Validation failed', 'details' => $err], Response::HTTP_BAD_REQUEST);
        }

        // 8) persister
        try {
            $em->persist($user);
            $em->flush();
        } catch (\Exception $e) {
            // ne pas renvoyer le message d'exception en prod
            return new JsonResponse(['error' => 'Erreur serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // 9) ne pas renvoyer l'entité complète  renvoyer un payload minimal
        $payload = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'statut' => $user->getStatut(),
        ];

        // 10) (optionnel) générer token de confirmation email et l'envoyer ici

        return new JsonResponse($payload, Response::HTTP_CREATED);
    }
    
#[Route('/api/mobile/GetFindUser', name: 'app_api_mobile_getuser')]
public function GetFindUser(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher,)
{
$postdata = json_decode($request->getContent());
if (isset($postdata->Email) && isset($postdata->Password)) {
$email = $postdata->Email;
$password = $postdata->Password;
} else 
return  Utils::ErrorMissingArgumentsDebug($request->getContent());
$user = $userRepository->findOneBy( ['email' => $email]);
if (!$user || !$userPasswordHasher->isPasswordValid($user, $password)) {
return Utils::ErrorInvalidCredentials();
}
$response = new Utils;
$tab = [];
return $response->GetJsonResponse($request, $user,$tab);
}

#[Route('/api/mobile/getAllUsers', name: 'app_api_getAllUsers')]
public function getAllUsers(Request $request,UserRepository $userRepository)
{
    $postdata = json_decode($request->getContent());
$var =  $userRepository->findAll();
$response = new Utils;
$tab = [];
return $response->GetJsonResponse($request, $var,$tab);
}

#[Route('/api/mobile/getAllEquipes', name: 'app_api_getAllEquipes')]
public function getAllEquipess(Request $request,EquipeRepository $equipeRepository)
{
    $postdata = json_decode($request->getContent());
$var =  $equipeRepository->findAll();
$response = new Utils;
$tab = [];
return $response->GetJsonResponse($request, $var,$tab);
}
/**
* Retourne la liste de toutes les épreuves au format JSON.
*
* Exemple de route : GET /api/epreuves
*/
#[Route('/api/mobile/getAllEpreuves', name: 'app_api_getAllEpreuves')]
public function getAllEpreuves(Request $request, EpreuveRepository $epreuveRepository)
{
    $postdata = json_decode($request->getContent()); // si tu as besoin de paramètres
    $var = $epreuveRepository->findAll();

    $response = new Utils;
    $tab = []; // meta / extras si besoin
    return $response->GetJsonResponse($request, $var, $tab);
}

#[Route('/api/mobile/createEpreuve', name: 'app_api_create_epreuve', methods: ['POST'])]
public function createEpreuve(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    if (0 !== strpos($request->headers->get('Content-Type', ''), 'application/json')) {
        return new JsonResponse(['error' => 'Content-Type must be application/json'], Response::HTTP_BAD_REQUEST);
    }

    $data = json_decode($request->getContent(), true);
    if (JSON_ERROR_NONE !== json_last_error()) {
        return new JsonResponse(['error' => 'JSON invalide ou manquant'], Response::HTTP_BAD_REQUEST);
    }

    $epreuve = new Epreuve();

    try {
        $this->hydrateEpreuve($epreuve, $data);
    } catch (InvalidArgumentException $exception) {
        return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
    }

    try {
        $entityManager->persist($epreuve);
        $entityManager->flush();
    } catch (\Exception $exception) {
        return new JsonResponse(['error' => 'Erreur serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    $utils = new Utils();
    $response = $utils->GetJsonResponse($request, $epreuve);
    $response->setStatusCode(Response::HTTP_CREATED);

    return $response;
}

#[Route('/api/mobile/updateEpreuve/{id}', name: 'app_api_update_epreuve', methods: ['PUT', 'PATCH', 'POST'])]
public function updateEpreuve(int $id, Request $request, EpreuveRepository $epreuveRepository, EntityManagerInterface $entityManager): JsonResponse
{
    if (0 !== strpos($request->headers->get('Content-Type', ''), 'application/json')) {
        return new JsonResponse(['error' => 'Content-Type must be application/json'], Response::HTTP_BAD_REQUEST);
    }

    $epreuve = $epreuveRepository->find($id);
    if (!$epreuve) {
        return new JsonResponse(['error' => 'Epreuve non trouvee'], Response::HTTP_NOT_FOUND);
    }

    $data = json_decode($request->getContent(), true);
    if (JSON_ERROR_NONE !== json_last_error()) {
        return new JsonResponse(['error' => 'JSON invalide ou manquant'], Response::HTTP_BAD_REQUEST);
    }

    try {
        $this->hydrateEpreuve($epreuve, $data);
    } catch (InvalidArgumentException $exception) {
        return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
    }

    try {
        $entityManager->flush();
    } catch (\Exception $exception) {
        return new JsonResponse(['error' => 'Erreur serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    $utils = new Utils();
    return $utils->GetJsonResponse($request, $epreuve);
}

#[Route('/api/mobile/epreuves/{id}/inscription', name: 'app_api_register_epreuve_user', methods: ['POST'])]
public function registerUserToEpreuve(
    int $id,
    Request $request,
    EpreuveRepository $epreuveRepository,
    UserRepository $userRepository,
    EntityManagerInterface $entityManager
): JsonResponse {
    if (0 !== strpos($request->headers->get('Content-Type', ''), 'application/json')) {
        return new JsonResponse(['error' => 'Content-Type must be application/json'], Response::HTTP_BAD_REQUEST);
    }

    $payload = json_decode($request->getContent(), true);
    if (JSON_ERROR_NONE !== json_last_error()) {
        return new JsonResponse(['error' => 'JSON invalide ou manquant'], Response::HTTP_BAD_REQUEST);
    }

    if (!isset($payload['userId']) || !is_numeric($payload['userId'])) {
        return new JsonResponse(['error' => 'userId est requis et doit être numérique'], Response::HTTP_BAD_REQUEST);
    }

    $epreuve = $epreuveRepository->find($id);
    if (!$epreuve) {
        return new JsonResponse(['error' => 'Épreuve non trouvée'], Response::HTTP_NOT_FOUND);
    }

    $user = $userRepository->find((int) $payload['userId']);
    if (!$user) {
        return new JsonResponse(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
    }

    if ($epreuve->getParticipants()->contains($user)) {
        return new JsonResponse(['message' => 'Utilisateur déjà inscrit à cette épreuve'], Response::HTTP_OK);
    }

    $epreuve->addParticipant($user);
    $entityManager->flush();

    return new JsonResponse([
        'message' => 'Inscription enregistrée',
        'epreuveId' => $epreuve->getId(),
        'userId' => $user->getId(),
    ], Response::HTTP_CREATED);
}

#[Route('/api/mobile/equipes/{id}/points', name: 'app_api_update_equipe_points', methods: ['POST', 'PUT', 'PATCH'])]
public function updateEquipePoints(
    int $id,
    Request $request,
    EquipeRepository $equipeRepository,
    EntityManagerInterface $entityManager
): JsonResponse {
    if (0 !== strpos($request->headers->get('Content-Type', ''), 'application/json')) {
        return new JsonResponse(['error' => 'Content-Type must be application/json'], Response::HTTP_BAD_REQUEST);
    }

    $payload = json_decode($request->getContent(), true);
    if (JSON_ERROR_NONE !== json_last_error()) {
        return new JsonResponse(['error' => 'JSON invalide ou manquant'], Response::HTTP_BAD_REQUEST);
    }

    if (!isset($payload['points']) || !is_numeric($payload['points'])) {
        return new JsonResponse(['error' => 'points est requis et doit être numérique'], Response::HTTP_BAD_REQUEST);
    }

    $equipe = $equipeRepository->find($id);
    if (!$equipe) {
        return new JsonResponse(['error' => 'Équipe non trouvée'], Response::HTTP_NOT_FOUND);
    }

    $equipe->setPoint((float) $payload['points']);
    $entityManager->flush();

    return new JsonResponse([
        'message' => 'Points mis à jour',
        'equipeId' => $equipe->getId(),
        'points' => $equipe->getPoint(),
    ], Response::HTTP_OK);
}

private function hydrateEpreuve(Epreuve $epreuve, array $data): void
{
    $requiredFields = ['nomEpreuve', 'libelle', 'duree', 'difficulte', 'pointEpreuve', 'lieuEpreuve', 'typeEpreuve', 'nbIndiceAGagner', 'dateEpreuveDebut', 'dateEpreuveFin', 'coeffAnnee'];

    foreach ($requiredFields as $field) {
        if (!array_key_exists($field, $data)) {
            throw new InvalidArgumentException(sprintf('Champ manquant : %s', $field));
        }
    }

    if (!is_numeric($data['duree'])) {
        throw new InvalidArgumentException('La durée doit être un entier (minutes)');
    }

    try {
        $dateDebut = new DateTime($data['dateEpreuveDebut']);
        $dateFin = new DateTime($data['dateEpreuveFin']);
    } catch (\Exception $exception) {
        throw new InvalidArgumentException('Format de date invalide');
    }

    $epreuve->setNomEpreuve($data['nomEpreuve']);
    $epreuve->setLibelle($data['libelle']);
    $epreuve->setDuree((int) $data['duree']);
    $epreuve->setDifficulte((int) $data['difficulte']);
    $epreuve->setPointEpreuve((float) $data['pointEpreuve']);
    $epreuve->setLieuEpreuve($data['lieuEpreuve']);
    $epreuve->setTypeEpreuve($data['typeEpreuve']);
    $epreuve->setNbIndiceAGagner((int) $data['nbIndiceAGagner']);
    $epreuve->setDateEpreuveDebut($dateDebut);
    $epreuve->setDateEpreuveFin($dateFin);
    $epreuve->setCoeffAnnee((float) $data['coeffAnnee']);
}

}
