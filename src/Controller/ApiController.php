<?php

namespace App\Controller;

use App\Entity\Equipe;
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

    #[Route('/api/mobile/users', name: 'app_api_mobile_users_list', methods: ['GET'])]
    public function listUsers(Request $request, UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        $utils = new Utils();

        return $utils->GetJsonResponse($request, $users, ['password']);
    }

    #[Route('/api/mobile/users/{id}', name: 'app_api_mobile_users_show', methods: ['GET'])]
    public function showUser(Request $request, UserRepository $userRepository, int $id): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $utils = new Utils();

        return $utils->GetJsonResponse($request, $user, ['password']);
    }

    #[Route('/api/mobile/users', name: 'app_api_mobile_users_create', methods: ['POST'])]
    public function createUser(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): JsonResponse {
        if (0 !== strpos($request->headers->get('Content-Type', ''), 'application/json')) {
            return new JsonResponse(['error' => 'Content-Type must be application/json'], Response::HTTP_BAD_REQUEST);
        }

        $payload = json_decode($request->getContent(), true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            return new JsonResponse(['error' => 'JSON invalide ou manquant'], Response::HTTP_BAD_REQUEST);
        }

        $requiredFields = ['email', 'password', 'nom', 'prenom'];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $payload)) {
                return new JsonResponse(['error' => sprintf('Champ manquant : %s', $field)], Response::HTTP_BAD_REQUEST);
            }
        }

        if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Email invalide'], Response::HTTP_BAD_REQUEST);
        }

        $normalizedEmail = mb_strtolower($payload['email']);
        if ($userRepository->findOneBy(['email' => $normalizedEmail])) {
            return new JsonResponse(['error' => 'Email déjà utilisé'], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($normalizedEmail);
        $user->setNom(trim((string) $payload['nom']));
        $user->setPrenom(trim((string) $payload['prenom']));
        $user->setStatut((bool) ($payload['statut'] ?? false));
        $user->setRoles(is_array($payload['roles'] ?? null) ? $payload['roles'] : ['ROLE_USER']);
        if (isset($payload['point']) && is_numeric($payload['point'])) {
            $user->setPoint((float) $payload['point']);
        } else {
            $user->setPoint(0.0);
        }

        $hashedPassword = $passwordHasher->hashPassword($user, (string) $payload['password']);
        $user->setPassword($hashedPassword);

        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => 'Erreur serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($this->buildUserPayload($user), Response::HTTP_CREATED);
    }

    #[Route('/api/mobile/users/{id}', name: 'app_api_mobile_users_update', methods: ['PUT', 'PATCH'])]
    public function updateUser(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        if (0 !== strpos($request->headers->get('Content-Type', ''), 'application/json')) {
            return new JsonResponse(['error' => 'Content-Type must be application/json'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            return new JsonResponse(['error' => 'JSON invalide ou manquant'], Response::HTTP_BAD_REQUEST);
        }

        if (array_key_exists('email', $payload)) {
            if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
                return new JsonResponse(['error' => 'Email invalide'], Response::HTTP_BAD_REQUEST);
            }

            $normalizedEmail = mb_strtolower($payload['email']);
            $existingUser = $userRepository->findOneBy(['email' => $normalizedEmail]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                return new JsonResponse(['error' => 'Email déjà utilisé'], Response::HTTP_CONFLICT);
            }

            $user->setEmail($normalizedEmail);
        }

        if (array_key_exists('nom', $payload)) {
            $user->setNom(trim((string) $payload['nom']));
        }

        if (array_key_exists('prenom', $payload)) {
            $user->setPrenom(trim((string) $payload['prenom']));
        }

        if (array_key_exists('statut', $payload)) {
            $user->setStatut((bool) $payload['statut']);
        }

        if (array_key_exists('roles', $payload) && is_array($payload['roles'])) {
            $user->setRoles($payload['roles']);
        }

        if (array_key_exists('point', $payload) && is_numeric($payload['point'])) {
            $user->setPoint((float) $payload['point']);
        }

        if (!empty($payload['password'])) {
            $hashedPassword = $passwordHasher->hashPassword($user, (string) $payload['password']);
            $user->setPassword($hashedPassword);
        }

        try {
            $entityManager->flush();
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => 'Erreur serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse($this->buildUserPayload($user));
    }

    #[Route('/api/mobile/users/{id}', name: 'app_api_mobile_users_delete', methods: ['DELETE'])]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        try {
            $entityManager->remove($user);
            $entityManager->flush();
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => 'Erreur serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['message' => 'Utilisateur supprimé'], Response::HTTP_OK);
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

#[Route('/api/mobile/updateUserEquipe', name: 'app_api_mobile_update_user_equipe', methods: ['POST'])]
public function updateUserEquipe(
    Request $request,
    UserRepository $userRepository,
    EquipeRepository $equipeRepository,
    EntityManagerInterface $entityManager
): JsonResponse {
    // 1) Validate Content-Type
    if (0 !== strpos($request->headers->get('Content-Type', ''), 'application/json')) {
        return new JsonResponse(['error' => 'Content-Type must be application/json'], Response::HTTP_BAD_REQUEST);
    }

    // 2) Parse JSON payload
    $payload = json_decode($request->getContent(), true);
    if (JSON_ERROR_NONE !== json_last_error()) {
        return new JsonResponse(['error' => 'JSON invalide ou manquant'], Response::HTTP_BAD_REQUEST);
    }

    // 3) Validate required fields
    if (!isset($payload['userId']) || !is_numeric($payload['userId'])) {
        return new JsonResponse(['error' => 'userId est requis et doit être numérique'], Response::HTTP_BAD_REQUEST);
    }

    if (!isset($payload['equipeId']) || !is_numeric($payload['equipeId'])) {
        return new JsonResponse(['error' => 'equipeId est requis et doit être numérique'], Response::HTTP_BAD_REQUEST);
    }

    // 4) Check if user exists
    $user = $userRepository->find((int) $payload['userId']);
    if (!$user) {
        return new JsonResponse(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
    }

    // 5) Check if equipe exists
    $equipe = $equipeRepository->find((int) $payload['equipeId']);
    if (!$equipe) {
        return new JsonResponse(['error' => 'Équipe non trouvée'], Response::HTTP_NOT_FOUND);
    }

    // 6) Check if user is already in this equipe
    if ($equipe->getLesUsers()->contains($user)) {
        return new JsonResponse([
            'message' => 'Utilisateur déjà dans cette équipe',
            'userId' => $user->getId(),
            'equipeId' => $equipe->getId(),
        ], Response::HTTP_OK);
    }

    // 7) Check if equipe is full
    if ($equipe->getLesUsers()->count() >= $equipe->getMaxJoueurs()) {
        return new JsonResponse(['error' => 'Équipe complète (nombre maximum de joueurs atteint)'], Response::HTTP_CONFLICT);
    }

    // 8) Remove user from all other equipes (assuming a user can only be in one equipe at a time)
    $allEquipes = $equipeRepository->findAll();
    foreach ($allEquipes as $otherEquipe) {
        if ($otherEquipe->getLesUsers()->contains($user)) {
            $otherEquipe->removeLesUser($user);
        }
    }

    // 9) Add user to the new equipe
    $equipe->addLesUser($user);

    // 10) Persist changes
    try {
        $entityManager->flush();
    } catch (\Exception $exception) {
        return new JsonResponse(['error' => 'Erreur serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    // 11) Return success response
    return new JsonResponse([
        'message' => 'Équipe de l\'utilisateur mise à jour avec succès',
        'userId' => $user->getId(),
        'userEmail' => $user->getEmail(),
        'userNom' => $user->getNom(),
        'userPrenom' => $user->getPrenom(),
        'equipeId' => $equipe->getId(),
        'equipeNom' => $equipe->getNomEquipe(),
    ], Response::HTTP_OK);
}

#[Route('/api/mobile/createEquipe', name: 'app_api_create_equipe', methods: ['POST'])]
public function createEquipe(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    if (0 !== strpos($request->headers->get('Content-Type', ''), 'application/json')) {
        return new JsonResponse(['error' => 'Content-Type must be application/json'], Response::HTTP_BAD_REQUEST);
    }

    $data = json_decode($request->getContent(), true);
    if (JSON_ERROR_NONE !== json_last_error()) {
        return new JsonResponse(['error' => 'JSON invalide ou manquant'], Response::HTTP_BAD_REQUEST);
    }

    $equipe = new Equipe();

    try {
        $this->hydrateEquipe($equipe, $data);
    } catch (InvalidArgumentException $exception) {
        return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
    }

    try {
        $entityManager->persist($equipe);
        $entityManager->flush();
    } catch (\Exception $exception) {
        return new JsonResponse(['error' => 'Erreur serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    $utils = new Utils();
    $response = $utils->GetJsonResponse($request, $equipe);
    $response->setStatusCode(Response::HTTP_CREATED);

    return $response;
}

#[Route('/api/mobile/updateEquipe/{id}', name: 'app_api_update_equipe', methods: ['POST', 'PUT', 'PATCH'])]
public function updateEquipe(int $id, Request $request, EquipeRepository $equipeRepository, EntityManagerInterface $entityManager): JsonResponse
{
    if (0 !== strpos($request->headers->get('Content-Type', ''), 'application/json')) {
        return new JsonResponse(['error' => 'Content-Type must be application/json'], Response::HTTP_BAD_REQUEST);
    }

    $equipe = $equipeRepository->find($id);
    if (!$equipe) {
        return new JsonResponse(['error' => 'Équipe non trouvée'], Response::HTTP_NOT_FOUND);
    }

    $data = json_decode($request->getContent(), true);
    if (JSON_ERROR_NONE !== json_last_error()) {
        return new JsonResponse(['error' => 'JSON invalide ou manquant'], Response::HTTP_BAD_REQUEST);
    }

    try {
        $this->hydrateEquipe($equipe, $data);
    } catch (InvalidArgumentException $exception) {
        return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
    }

    try {
        $entityManager->flush();
    } catch (\Exception $exception) {
        return new JsonResponse(['error' => 'Erreur serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    $utils = new Utils();
    return $utils->GetJsonResponse($request, $equipe);
}

#[Route('/api/mobile/deleteEquipe/{id}', name: 'app_api_delete_equipe', methods: ['POST', 'DELETE'])]
public function deleteEquipe(int $id, EquipeRepository $equipeRepository, EntityManagerInterface $entityManager): JsonResponse
{
    $equipe = $equipeRepository->find($id);
    if (!$equipe) {
        return new JsonResponse(['error' => 'Équipe non trouvée'], Response::HTTP_NOT_FOUND);
    }

    try {
        $entityManager->remove($equipe);
        $entityManager->flush();
    } catch (\Exception $exception) {
        return new JsonResponse(['error' => 'Erreur serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    return new JsonResponse(['message' => 'Équipe supprimée'], Response::HTTP_OK);
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

#[Route('/api/mobile/nextEpreuve', name: 'app_api_next_epreuve', methods: ['GET'])]
public function getNextEpreuve(Request $request, EpreuveRepository $epreuveRepository): JsonResponse
{
    $nextEpreuve = $epreuveRepository->findNextEpreuve();

    if (!$nextEpreuve) {
        return new JsonResponse(['message' => 'Aucune épreuve à venir'], Response::HTTP_NOT_FOUND);
    }

    $response = new Utils();

    return $response->GetJsonResponse($request, $nextEpreuve);
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
public function updateEpreuve(int $id, Request $request, EpreuveRepository $epreuveRepository, EntityManagerInterface $entityManager)
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

private function buildUserPayload(User $user): array
{
    return [
        'id' => $user->getId(),
        'email' => $user->getEmail(),
        'nom' => $user->getNom(),
        'prenom' => $user->getPrenom(),
        'roles' => $user->getRoles(),
        'statut' => $user->getStatut(),
        'point' => $user->getPoint(),
    ];
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

private function hydrateEquipe(Equipe $equipe, array $data): void
{
    $requiredFields = ['maxJoueurs', 'point', 'nomEquipe', 'statut', 'nbIndice'];

    foreach ($requiredFields as $field) {
        if (!array_key_exists($field, $data)) {
            throw new InvalidArgumentException(sprintf('Champ manquant : %s', $field));
        }
    }

    if (!is_numeric($data['maxJoueurs'])) {
        throw new InvalidArgumentException('maxJoueurs doit être numérique');
    }

    if (!is_numeric($data['point'])) {
        throw new InvalidArgumentException('point doit être numérique');
    }

    if (!is_bool($data['statut'])) {
        throw new InvalidArgumentException('statut doit être un booléen');
    }

    if (!is_numeric($data['nbIndice'])) {
        throw new InvalidArgumentException('nbIndice doit être numérique');
    }

    $equipe->setMaxJoueurs((int) $data['maxJoueurs']);
    $equipe->setPoint((float) $data['point']);
    $equipe->setNomEquipe($data['nomEquipe']);
    $equipe->setStatut($data['statut']);
    $equipe->setNbIndice((int) $data['nbIndice']);
}

}
