# API Documentation - Update User Equipe

## Endpoint: `/api/mobile/updateUserEquipe`

### Description
Updates a user's team (équipe) assignment. This endpoint removes the user from any existing team and assigns them to the specified team.

### HTTP Method
`POST`

### Request Headers
- `Content-Type: application/json` (Required)

### Request Body
JSON object with the following fields:

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `userId` | integer | Yes | The ID of the user to update |
| `equipeId` | integer | Yes | The ID of the team to assign the user to |

### Example Request
```bash
curl -X POST http://localhost:8000/api/mobile/updateUserEquipe \
  -H "Content-Type: application/json" \
  -d '{
    "userId": 1,
    "equipeId": 2
  }'
```

### Success Response

**Code:** `200 OK`

**Content:**
```json
{
  "message": "Équipe de l'utilisateur mise à jour avec succès",
  "userId": 1,
  "userEmail": "user@example.com",
  "userNom": "Doe",
  "userPrenom": "John",
  "equipeId": 2,
  "equipeNom": "Team A"
}
```

### Error Responses

#### Invalid Content-Type
**Code:** `400 BAD REQUEST`

**Content:**
```json
{
  "error": "Content-Type must be application/json"
}
```

#### Invalid JSON
**Code:** `400 BAD REQUEST`

**Content:**
```json
{
  "error": "JSON invalide ou manquant"
}
```

#### Missing userId
**Code:** `400 BAD REQUEST`

**Content:**
```json
{
  "error": "userId est requis et doit être numérique"
}
```

#### Missing equipeId
**Code:** `400 BAD REQUEST`

**Content:**
```json
{
  "error": "equipeId est requis et doit être numérique"
}
```

#### User Not Found
**Code:** `404 NOT FOUND`

**Content:**
```json
{
  "error": "Utilisateur non trouvé"
}
```

#### Team Not Found
**Code:** `404 NOT FOUND`

**Content:**
```json
{
  "error": "Équipe non trouvée"
}
```

#### Team Full
**Code:** `409 CONFLICT`

**Content:**
```json
{
  "error": "Équipe complète (nombre maximum de joueurs atteint)"
}
```

#### User Already in Team
**Code:** `200 OK`

**Content:**
```json
{
  "message": "Utilisateur déjà dans cette équipe",
  "userId": 1,
  "equipeId": 2
}
```

#### Server Error
**Code:** `500 INTERNAL SERVER ERROR`

**Content:**
```json
{
  "error": "Erreur serveur"
}
```

## Business Logic

1. **Validation**: The endpoint validates that both `userId` and `equipeId` are provided and numeric.
2. **Existence Check**: Verifies that both the user and team exist in the database.
3. **Duplicate Check**: If the user is already in the specified team, returns a success message without making changes.
4. **Capacity Check**: Ensures the target team has not reached its maximum capacity (`maxJoueurs`).
5. **Team Assignment**: 
   - Removes the user from any existing teams (a user can only be in one team at a time)
   - Adds the user to the new team
6. **Persistence**: Saves all changes to the database.

## Testing

The endpoint includes comprehensive unit tests covering:
- Valid requests
- Invalid content types
- Malformed JSON
- Missing required fields
- Non-numeric IDs
- Non-existent users and teams

Run tests with:
```bash
php vendor/bin/phpunit tests/Controller/ApiControllerTest.php
```

## Implementation Details

- **Controller**: `src/Controller/ApiController.php`
- **Method**: `updateUserEquipe()`
- **Route Name**: `app_api_mobile_update_user_equipe`
- **Entities**: Uses `User` and `Equipe` entities with a many-to-many relationship via `lesUsers` collection
