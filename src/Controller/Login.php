<?php


namespace App\Controller;

use FOS\OAuthServerBundle\Controller\TokenController as BaseController;
use OAuth2\OAuth2;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;

class Login extends BaseController {


    /**
     * Login user
     * @Rest\Post("/login", name="login_user")
     * @SWG\Response(
     *     response=200,
     *     description="Login",
     *     @SWG\Schema(
     *          @SWG\Property(
     *              property="access_token",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="expires_in",
     *              type="integer"
     *          ),
     *          @SWG\Property(
     *              property="token_type",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="scope",
     *              type="string"
     *          ),
     *          @SWG\Property(
     *              property="refresh_token",
     *              type="string"
     *          )
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Bad Request"
     * )
     * @SWG\Tag(name="User")
     * @Security(name="Bearer")
     * @param Request $request
     * @return Response
     */
    public function tokenAction(Request $request)
    {
        return parent::tokenAction($request);
    }
}