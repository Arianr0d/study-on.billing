<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use Symfony\Component\HttpFoundation\Response;

class APITest extends AbstractTest
{
    private $serializer;

    protected function getFixtures(): array
    {
        return [AppFixtures::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = self::$kernel->getContainer()->get('jms_serializer');
    }

    // проверка успешности авторизации пользователя
    public function testAuthorization() : void
    {
        $user = $this->serializer->serialize([
            'username' => 'user@study-on-billing.ru',
            'password' => 'passwordUser'
        ], 'json');
        $client = self::getClient();
        $client->request('POST','/api/v1/auth', [], [],
            ['CONTENT_TYPE' => 'application/json'], $user);
        $this->assertResponseOk();
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['token']);
    }

    // проверка на авторизацию несуществующего пользователя
    public function testNotExistUserAuth() : void
    {
        $user = $this->serializer->serialize([
            'username' => 'newuser@study-on-billing.ru',
            'password' => 'passwordNotExistUser'
        ], 'json');
        $client = self::getClient();
        $client->request('POST','/api/v1/auth', [], [],
            ['CONTENT_TYPE' => 'application/json'], $user);
        $this->assertResponseCode(Response::HTTP_UNAUTHORIZED);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $error = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid credentials.', $error['message']);
    }

    // проверка успешности регистрации пользователя
    public function testRegister() : void
    {
        $user = $this->serializer->serialize([
            'username' => 'new_user@study-on-billing.ru',
            'password' => 'passwordNewUser'
        ], 'json');
        $client = self::getClient();
        $client->request('POST','/api/v1/register', [], [],
            ['CONTENT_TYPE' => 'application/json'], $user);
        $this->assertResponseCode(Response::HTTP_CREATED);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['token']);
    }

    // проверка на существование пользователя при регистрации
    public function testRegisterExistUser() : void
    {
        $user = $this->serializer->serialize([
            'username' => 'user@study-on-billing.ru',
            'password' => 'passwordNotExistUser'
        ], 'json');
        $client = self::getClient();
        $client->request('POST','/api/v1/register', [], [],
            ['CONTENT_TYPE' => 'application/json'], $user);
        $this->assertResponseCode(Response::HTTP_BAD_REQUEST);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse  = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['error']);
        $this->assertEquals('Пользователь user@study-on-billing.ruуже существует', $jsonResponse ['error']);
    }

    // проверка корректности пароля при регистрации пользователя
    public function testRegisterInvalidPassword() : void
    {
        // ввод пароля нулевой длины
        $user = $this->serializer->serialize([
            'username' => 'user111@study-on-billing.ru',
            'password' => ''
        ], 'json');
        $client = self::getClient();
        $client->request('POST','/api/v1/register', [], [],
            ['CONTENT_TYPE' => 'application/json'], $user);
        $this->assertResponseCode(Response::HTTP_BAD_REQUEST);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['errors']['password']);
        $this->assertContains('Password is mandatory',
            $jsonResponse['errors']['password']);


        // размер пароля меньше 6 символов
        $user = $this->serializer->serialize([
            'username' => 'user111@study-on-billing.ru',
            'password' => 'sos'
        ], 'json');
        $client = self::getClient();
        $client->request('POST','/api/v1/register', [], [],
            ['CONTENT_TYPE' => 'application/json'], $user);
        $this->assertResponseCode(Response::HTTP_BAD_REQUEST);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['errors']['password']);
        $this->assertContains('Password must be longer than 6 characters',
            $jsonResponse['errors']['password']);
    }

    // проверка на корректность почты
    public function testRegisterInvalidEmail() : void
    {
        // не указана почта
        $user = $this->serializer->serialize([
            'username' => '',
            'password' => 'sos'
        ], 'json');
        $client = self::getClient();
        $client->request('POST','/api/v1/register', [], [],
            ['CONTENT_TYPE' => 'application/json'], $user);
        $this->assertResponseCode(Response::HTTP_BAD_REQUEST);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['errors']['username']);
        $this->assertContains('Name is mandatory',
            $jsonResponse['errors']['username']);

        // неверный формат записи почты
        $user = $this->serializer->serialize([
            'username' => 'emailuser.ru',
            'password' => 'sos'
        ], 'json');
        $client = self::getClient();
        $client->request('POST','/api/v1/register', [], [],
            ['CONTENT_TYPE' => 'application/json'], $user);
        $this->assertResponseCode(Response::HTTP_BAD_REQUEST);
        $this->assertTrue($client->getResponse()->headers->contains(
            'Content-Type',
            'application/json'
        ));
        $jsonResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($jsonResponse['errors']['username']);
        $this->assertContains('Invalid email address',
            $jsonResponse['errors']['username']);
    }
}