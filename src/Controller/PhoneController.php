<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class PhoneController extends AbstractFOSRestController
{

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var PhoneRepository
     */
    private $repository;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * PhoneController constructor.
     * @param EntityManagerInterface $em
     * @param PhoneRepository $repository
     * @param SerializerInterface $serializer
     */
    public function __construct(EntityManagerInterface $em, PhoneRepository $repository, SerializerInterface $serializer)
    {
        $this->em = $em;
        $this->repository = $repository;
        $this->serializer = $serializer;
    }

    /**
     * @Rest\Get("/list/phone", name="list_phones")
     * @Rest\View()
     * @return object[]
     */
    public function getListPhonesAction(){
        return $this->repository->findAll();
    }

    /**
     * @Rest\Get("/detail/phone/{id}", name="one_phone", requirements={"id"="\d+"})
     * @Rest\View()
     * @param Phone $phone
     * @return Phone
     */
    public function getOnePhoneAction(Phone $phone){
        return $phone;
    }

}