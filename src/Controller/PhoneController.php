<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use App\Representation\Phones;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     * * @Rest\QueryParam(
     *     name="keyword",
     *     requirements="[a-zA-Z0-9]",
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
     *     description="Max number of phones per page."
     * )
     * @Rest\QueryParam(
     *     name="offset",
     *     requirements="\d+",
     *     default="1",
     *     description="The pagination offset"
     * )
     * @Rest\View()
     * @param ParamFetcherInterface $paramFetcher
     * @return Phones | View
     */
    public function getListPhonesAction(ParamFetcherInterface $paramFetcher)
    {
        $pager = $this->repository->search(
            $paramFetcher->get('keyword'),
            $paramFetcher->get('order'),
            $paramFetcher->get('limit'),
            $paramFetcher->get('offset')
        );

        if($pager === false){
            throw new HttpException(204, 'No phone found');
        }
        return new Phones($pager);
    }

    /**
     * @Rest\Get("/detail/phone/{id}", name="one_phone", requirements={"id"="\d+"})
     * @Rest\View()
     * @param Phone $phone
     * @return Phone
     */
    public function getOnePhoneAction(Phone $phone)
    {
        return $phone;
    }
}
