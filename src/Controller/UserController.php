<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

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
     * PhoneController constructor.
     * @param EntityManagerInterface $em
     * @param UserRepository $repository
     * @param SerializerInterface $serializer
     */
    public function __construct(EntityManagerInterface $em, UserRepository $repository, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->repository = $repository;
        $this->serializer = $serializer;
    }

    /**
     * @Rest\Get("/list/user", name="list_user")
     * @Rest\View()
     * @return object[]
     */
    public function getListPhonesAction(){
        return $this->repository->findAll();
    }

    /**
     * @Rest\Get("/detail/user/{id}", name="one_user", requirements={"id"="\d+"})
     * @Rest\View()
     * @param User $phone
     * @return User
     */
    public function getOnePhoneAction(User $phone){
        return $phone;
    }

}