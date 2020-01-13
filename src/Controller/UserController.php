<?php

namespace App\Controller;

use App\Entity\AccessToken;
use App\Entity\User;
use App\Repository\AccessTokenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var AccessTokenRepository
     */
    private $tokenRepository;

    /**
     * PhoneController constructor.
     * @param EntityManagerInterface $em
     * @param UserRepository $repository
     * @param AccessTokenRepository $tokenRepository
     * @param SerializerInterface $serializer
     */
    public function __construct(EntityManagerInterface $em, UserRepository $repository, AccessTokenRepository $tokenRepository, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->repository = $repository;
        $this->serializer = $serializer;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * @Rest\Get("/list/user", name="list_user")
     * @Rest\View()
     * @param Request $request
     * @param AccessTokenRepository $token
     * @return object[]
     * @throws HttpException
     */
    public function getListUsersAction(Request $request, AccessTokenRepository $token)
    {
        $client = $this->getClientAction($request, $token);

        if($client === null){
            throw new HttpException(Response::HTTP_FORBIDDEN,'You are not allowed for this request');
        }
        return $this->repository->getUserInfo($client);
    }

    /**
     * @Rest\Get("/detail/user/{id}", name="one_user", requirements={"id"="\d+"})
     * @Rest\View()
     * @param User $user
     * @param Request $request
     * @param AccessTokenRepository $token
     * @return int
     * @throws HttpException
     */
    public function getOneUserAction(User $user, Request $request, AccessTokenRepository $token)
    {
        $client = $this->getClientAction($request, $token);

        if($client === null || $user->getClient() !== $client->getClient()){
            throw new HttpException(Response::HTTP_FORBIDDEN,'You are not allowed for this request');
        }

        return $this->repository->getContractedUserInfo($user->getId());
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
}
