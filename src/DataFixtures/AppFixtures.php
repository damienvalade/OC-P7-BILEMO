<?php

namespace App\DataFixtures;

use App\Controller\SecurityController;
use App\Entity\Client;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;

class AppFixtures extends Fixture
{
    public $client_manager;
    public $user;

    public function __construct(ClientManagerInterface $client_manager,SecurityController $user)
    {
        $this->client_manager = $client_manager;
        $this->user = $user;
    }

    /**
     * Use it to create first SUPER_ADMIN
     * @param ObjectManager $manager
     */

    public function load(ObjectManager $manager)
    {

        /**
         * add generic client
         */

        $client = $this->client_manager->createClient();
        $client->setAllowedGrantTypes(['password,refresh_token']);
        $client->setRedirectUris(['http://localhost/api/']);
        $client->setName('default');

        $manager->persist($client);

        /**
         * add generic user
         */

        $manager->persist($this->user->userSet($client, "ROLE_SUPER_ADMIN"));

        $manager->flush();
    }
}
