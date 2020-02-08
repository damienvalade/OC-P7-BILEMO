<?php

namespace App\Controller;

use App\Entity\User;
use App\Errors\Errors;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Response;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractFOSRestController
{
    private $client_manager;
    public $encoder;
    public $errors;

    public function __construct(ClientManagerInterface $client_manager,UserPasswordEncoderInterface $encoder, Errors $errors)
    {
        $this->client_manager = $client_manager;
        $this->encoder = $encoder;
        $this->errors = $errors;
    }

    /**
     * Create Client.
     * @FOSRest\Post("/superadmin/add/client", name="create_client")
     * @SWG\Parameter(
     *     name="redirect-uri",
     *     in="body",
     *     type="string",
     *     description="redirect-uri",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="redirect-uri", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="grant-type",
     *     in="body",
     *     type="string",
     *     description="grant-type",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="grant-type", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="name",
     *     in="body",
     *     type="string",
     *     description="name",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="name", type="string")
     *     )
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Add new client",
     *     @SWG\Schema(
     *          @SWG\Property(
     *              property="client_id",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="client_secret",
     *              type="string"
     *          )
     *     )
     * )
     * @SWG\Tag(name="SuperAdmin/Client")
     * @Security(name="Bearer")
     * @param Request $request
     * @return Response
     */
    public function AuthenticationAction(Request $request)
    {
        $data = $this->isDataEmpty($request);
        $client = $this->clientSet($data);
        $user = $this->userSet($client);

        $rows = [
            'client_id' => $client->getPublicId(), 'client_secret' => $client->getSecret(),'user_default' => $user
        ];


        return $this->handleView($this->view($rows));
    }

    /**
     * @param Request $request
     * @return string
     */
    public function isDataEmpty(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['redirect-uri']) || empty($data['grant-type']) || empty($data['name'])) {
            $this->errors->errorBadRequest();
        }

        return $data;
    }

    /**
     * @param $data
     * @return ClientInterface
     */
    public function clientSet($data)
    {
        $clientManager = $this->client_manager;
        $client = $clientManager->createClient();
        $client->setRedirectUris([$data['redirect-uri']]);
        $client->setAllowedGrantTypes([$data['grant-type']]);
        $client->setName($data['name']);
        $clientManager->updateClient($client);

        return $client;
    }

    /**
     * @param $client
     * @return User
     */
    public function userSet($client)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $user = New User();
        $user->setUsername($client->getName());
        $user->setUsernameCanonical($client->getName());
        $user->setEmail('admin@admin.fr');
        $user->setEmailCanonical('admin@admin.fr');
        $user->setEnabled(1);
        $user->setPassword($this->encoder->encodePassword($user, "admin"));
        $user->setRoles(["ROLE_ADMIN"]);
        $user->setClient($client);

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}
