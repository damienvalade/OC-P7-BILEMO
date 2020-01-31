<?php

namespace App\Controller;

use App\Entity\AccessToken;
use App\Entity\Client;
use App\Entity\User;
use App\Repository\AccessTokenRepository;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\Representation\Users;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;


class UserController extends AbstractFOSRestController
{
    /**
     * @var UserRepository
     */
    private $repository;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * UserController constructor.
     * @param UserRepository $repository
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(
        UserRepository $repository,
        UserPasswordEncoderInterface $encoder
    )
    {
        $this->repository = $repository;
        $this->encoder = $encoder;
    }

    /**
     * List Users
     * @Rest\Get("/list/user", name="list_user")
     * @Rest\QueryParam(
     *     name="keyword",
     *     requirements="[a-zA-Z0-9]",
     *     default="{id}",
     *     nullable=true,
     *     description="The keyword to search for."
     * )
     * @Rest\QueryParam(
     *     name="order",
     *     requirements="asc|desc",
     *     default="asc",
     *     description="Sort order (asc or desc)"
     * )
     * @Rest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     default="15",
     *     description="Max number of users per page."
     * )
     * @Rest\QueryParam(
     *     name="offset",
     *     requirements="\d+",
     *     default="1",
     *     description="The pagination offset"
     * )
     * @Rest\View()
     * @SWG\Response(
     *     response=200,
     *     description="Returns list of all users",
     *     @Model(type=User::class)
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not allowed for this request"
     * )
     * @SWG\Tag(name="User")
     * @Security(name="Bearer")
     * @param Request $request
     * @param AccessTokenRepository $token
     * @param ParamFetcherInterface $paramFetcher
     * @return Users
     */
    public function getListUsersAction(Request $request, AccessTokenRepository $token, ParamFetcherInterface $paramFetcher)
    {
        $client = $this->isAllowed($request, [
            'token' => $token,
            'user' => false
        ]);

        $pager = $this->repository->search(
            $client,
            $paramFetcher->get('order'),
            $paramFetcher->get('limit'),
            $paramFetcher->get('offset')
        );

        if ($pager === false) {
            throw new HttpException(404, 'No user found');
        }

        return new Users($pager);
    }

    /**
     * Detail User
     * @Rest\Get("/detail/user/{id}", name="one_user", requirements={"id"="\d+"})
     * @Rest\View()
     * @SWG\Response(
     *     response=200,
     *     description="Returns detail of an user",
     *     @Model(type=User::class)
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not allowed for this request"
     * )
     * @SWG\Tag(name="User")
     * @Security(name="Bearer")
     * @param User $user
     * @param Request $request
     * @param AccessTokenRepository $token
     * @return User
     * @throws HttpException
     */
    public function getOneUserAction(Request $request, User $user, AccessTokenRepository $token)
    {
        $this->isAllowed($request, [
            'token' => $token,
            'user' => $user
        ]);

        return $user;
    }

    /**
     * Add User
     * @Rest\Post("/add/user", name="add_user")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("user", converter="fos_rest.request_body")
     * @SWG\Response(
     *     response=201,
     *     description="Add new User",
     *     @Model(type=User::class)
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not allowed for this request"
     * )
     * @SWG\Tag(name="Admin/User")
     * @Security(name="Bearer")
     * @param User $user
     * @param Request $request
     * @param AccessTokenRepository $token
     * @return User
     */
    public function addUser(Request $request, User $user, AccessTokenRepository $token)
    {

        $client = $this->isAllowed($request, [
            'token' => $token,
            'user' => $user
        ]);

        $entityManager = $this->getDoctrine()->getManager();

        $user->setClient($client->getClient());
        $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    /**
     * Delete User
     * @Rest\Delete("/delete/user/{id}", name="delete_user", requirements={"id"="\d+"})
     * @Rest\View(StatusCode = 204)
     * @SWG\Response(
     *     response=204,
     *     description="Delete User"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not allowed for this request"
     * )
     * @SWG\Tag(name="Admin/User")
     * @Security(name="Bearer")
     * @param User $user
     * @param Request $request
     * @param AccessTokenRepository $token
     * @return void
     */
    public function deleteUser(Request $request, User $user, AccessTokenRepository $token)
    {
        $this->isAllowed($request, [
            'token' => $token,
            'user' => $user
        ]);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        return;
    }


    /**
     * Patch Phone
     * @Rest\Patch("/patch/user/{id}", name="patch_user", requirements={"id"="\d+"})
     * @ParamConverter("phone", converter="fos_rest.request_body")
     * @Rest\View(StatusCode = 200)
     * @SWG\Response(
     *     response=200,
     *     description="Patch User"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not allowed for this request"
     * )
     * @SWG\Tag(name="Admin/User")
     * @Security(name="Bearer")
     * @param Request $request
     * @param User $user
     * @param AccessTokenRepository $token
     * @return User
     */
    public function patchPhoneAction(Request $request, User $user, AccessTokenRepository $token)
    {
        $this->isAllowed($request, [
            'token' => $token,
            'user' => $user
        ]);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    /**
     * @param Request $request
     * @param AccessTokenRepository $client
     * @return AccessToken|null
     */
    public function getClientAction(Request $request, AccessTokenRepository $client)
    {
        return $client = $client->getClientByToken($request->headers->get('X-AUTH-TOKEN'));
    }

    /**
     * @param $client
     * @param $user
     * @return string
     */
    public function userIsMatch($client, $user)
    {
        if ($user === false || $user->getClient() === $client->getClient()) {
            return true;
        }
        return false;
    }

    /**
     * @param Request $request
     * @param array $data
     * @return AccessToken|null
     */
    public function isAllowed(Request $request, array $data)
    {

        $client = $this->getClientAction($request, $data['token']);
        $isMatch = $this->userIsMatch($client, $data['user']);

        if ($client === null || $isMatch === false) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'You are not allowed for this request');
        }

        return $client;
    }

}
