<?php

namespace App\Controller;

use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Response;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;

class SecurityController extends AbstractFOSRestController
{
    private $client_manager;

    public function __construct(ClientManagerInterface $client_manager)
    {
        $this->client_manager = $client_manager;
    }

    /**
     * Create Client.
     * @FOSRest\Post("/superadmin/add/client", name="create_client")
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

        $rows = [
            'client_id' => $client->getPublicId(), 'client_secret' => $client->getSecret()
        ];

        return $this->handleView($this->view($rows));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function isDataEmpty(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['redirect-uri']) || empty($data['grant-type'])) {
            return $this->handleView($this->view($data));
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
        $clientManager->updateClient($client);

        return $client;
    }
}
