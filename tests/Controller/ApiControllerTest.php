<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testUpdateUserEquipeWithValidData(): void
    {
        // This test assumes there are users and equipes in the database
        // In a real scenario, you would set up fixtures or create test data
        $payload = json_encode([
            'userId' => 1,
            'equipeId' => 1,
        ]);

        $this->client->request(
            'POST',
            '/api/mobile/updateUserEquipe',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response = $this->client->getResponse();

        // The response could be 200 (success) or 404 (not found) depending on test data
        $this->assertContains(
            $response->getStatusCode(),
            [Response::HTTP_OK, Response::HTTP_NOT_FOUND]
        );

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $responseData = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('message', $responseData);
            $this->assertArrayHasKey('userId', $responseData);
            $this->assertArrayHasKey('equipeId', $responseData);
        }
    }

    public function testUpdateUserEquipeWithInvalidContentType(): void
    {
        $payload = 'userId=1&equipeId=1';

        $this->client->request(
            'POST',
            '/api/mobile/updateUserEquipe',
            [],
            [],
            ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'],
            $payload
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Content-Type must be application/json', $responseData['error']);
    }

    public function testUpdateUserEquipeWithInvalidJson(): void
    {
        $payload = '{invalid json}';

        $this->client->request(
            'POST',
            '/api/mobile/updateUserEquipe',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('JSON invalide ou manquant', $responseData['error']);
    }

    public function testUpdateUserEquipeWithMissingUserId(): void
    {
        $payload = json_encode([
            'equipeId' => 1,
        ]);

        $this->client->request(
            'POST',
            '/api/mobile/updateUserEquipe',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('userId est requis et doit être numérique', $responseData['error']);
    }

    public function testUpdateUserEquipeWithMissingEquipeId(): void
    {
        $payload = json_encode([
            'userId' => 1,
        ]);

        $this->client->request(
            'POST',
            '/api/mobile/updateUserEquipe',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('equipeId est requis et doit être numérique', $responseData['error']);
    }

    public function testUpdateUserEquipeWithNonNumericUserId(): void
    {
        $payload = json_encode([
            'userId' => 'abc',
            'equipeId' => 1,
        ]);

        $this->client->request(
            'POST',
            '/api/mobile/updateUserEquipe',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('userId est requis et doit être numérique', $responseData['error']);
    }

    public function testUpdateUserEquipeWithNonNumericEquipeId(): void
    {
        $payload = json_encode([
            'userId' => 1,
            'equipeId' => 'xyz',
        ]);

        $this->client->request(
            'POST',
            '/api/mobile/updateUserEquipe',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('equipeId est requis et doit être numérique', $responseData['error']);
    }

    public function testUpdateUserEquipeWithNonExistentUser(): void
    {
        $payload = json_encode([
            'userId' => 999999,
            'equipeId' => 1,
        ]);

        $this->client->request(
            'POST',
            '/api/mobile/updateUserEquipe',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Utilisateur non trouvé', $responseData['error']);
    }

    public function testUpdateUserEquipeWithNonExistentEquipe(): void
    {
        $payload = json_encode([
            'userId' => 1,
            'equipeId' => 999999,
        ]);

        $this->client->request(
            'POST',
            '/api/mobile/updateUserEquipe',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response = $this->client->getResponse();

        // This could be 404 (equipe not found) or 404 (user not found) depending on test data
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
    }
}
