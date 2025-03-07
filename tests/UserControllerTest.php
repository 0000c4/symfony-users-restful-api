<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = self::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        
        // Clear the database before each test
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeStatement($platform->getTruncateTableSQL('`user`', true));
    }

    public function testGetUsers(): void
    {
        // Create test users
        $this->createTestUser('John Doe', 'john@example.com');
        $this->createTestUser('Jane Smith', 'jane@example.com');

        // Make request
        $this->client->request('GET', '/api/users');
        $response = $this->client->getResponse();
        
        // Assert response
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('success', $content['status']);
        $this->assertCount(2, $content['data']);
    }

    public function testCreateUser(): void
    {
        // Create request data
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ];

        // Make request
        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );
        $response = $this->client->getResponse();

        // Assert response
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('success', $content['status']);
        $this->assertEquals('Test User', $content['data']['name']);
        $this->assertEquals('test@example.com', $content['data']['email']);
        
        // Check if user was actually created in database
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);
        $this->assertEquals('Test User', $user->getName());
    }

    public function testCreateUserInvalidData(): void
    {
        // Create request with invalid data (missing email)
        $userData = [
            'name' => 'Test User'
        ];

        // Make request
        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );
        $response = $this->client->getResponse();

        // Assert response
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('error', $content['status']);
        $this->assertArrayHasKey('errors', $content);
    }

    public function testDeleteUser(): void
    {
        // Create test user
        $user = $this->createTestUser('Test User', 'test@example.com');
        
        // Make request
        $this->client->request('DELETE', '/api/users/1');
        $response = $this->client->getResponse();

        // Assert response
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('success', $content['status']);
        
        // Check if user was actually deleted from database
        $deletedUser = $this->entityManager->getRepository(User::class)->find(1);
        $this->assertNull($deletedUser);
    }

    public function testDeleteNonExistentUser(): void
    {
        // Make request with non-existent ID
        $this->client->request('DELETE', '/api/users/999');
        $response = $this->client->getResponse();

        // Assert response
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('error', $content['status']);
    }

    private function createTestUser(string $name, string $email): User
    {
        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user;
    }
}