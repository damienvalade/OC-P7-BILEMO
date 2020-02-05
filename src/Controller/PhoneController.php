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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;


class PhoneController extends AbstractFOSRestController
{
    /**
     * @var PhoneRepository
     */
    private $repository;

    /**
     * PhoneController constructor.
     * @param PhoneRepository $repository
     */
    public function __construct(PhoneRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * List phones
     * @Rest\Get("/list/phone", name="list_phones")
     * @Rest\QueryParam(
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
     * @SWG\Response(
     *     response=200,
     *     description="Returns list of  all phones",
     *     @Model(type=Phone::class)
     * )
     * @SWG\Tag(name="Phone")
     * @Security(name="Bearer")
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

        if ($pager === false) {
            throw new HttpException(404, 'No phone found');
        }
        return new Phones($pager);
    }

    /**
     * Detail Phone
     * @Rest\Get("/detail/phone/{id}", name="one_phone", requirements={"id"="\d+"})
     * @Rest\View()
     * @SWG\Response(
     *     response=200,
     *     description="Returns detail of one phone",
     *     @Model(type=Phone::class)
     * )
     * @SWG\Tag(name="Phone")
     * @Security(name="Bearer")
     * @param Phone $phone
     * @return Phone
     */
    public function getOnePhoneAction(Phone $phone)
    {
        return $phone;
    }

    /**
     * Add Phone
     * @Rest\Post("/admin/add/phone", name="add_phone")
     * @ParamConverter("phone", converter="fos_rest.request_body")
     * @Rest\View()
     * @SWG\Response(
     *     response=201,
     *     description="add Phone"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not allowed for this request"
     * )
     * @SWG\Tag(name="Admin/Phone")
     * @Security(name="Bearer")
     * @param Phone $phone
     * @return Phone
     */
    public function addPhoneAction(Phone $phone)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($phone);
        $entityManager->flush();

        return $phone;
    }

    /**
     * Delete Phone
     * @Rest\Delete("/admin/delete/phone/{id}", name="delete_phone", requirements={"id"="\d+"})
     * @Rest\View(StatusCode = 204)
     * @SWG\Response(
     *     response=204,
     *     description="Delete Phone"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not allowed for this request"
     * )
     * @SWG\Tag(name="Admin/Phone")
     * @Security(name="Bearer")
     * @param Phone $phone
     * @return void
     */
    public function deletePhoneAction(Phone $phone)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($phone);
        $entityManager->flush();

        return;
    }

    /**
 * Patch Phone
 * @Rest\Patch("/admin/patch/phone/{id}", name="patch_phone", requirements={"id"="\d+"})
 * @ParamConverter("phone", converter="fos_rest.request_body")
 * @Rest\View(StatusCode = 200)
 * @SWG\Response(
 *     response=200,
 *     description="Delete Phone"
 * )
 * @SWG\Response(
 *     response=403,
 *     description="You are not allowed for this request"
 * )
 * @SWG\Tag(name="Admin/Phone")
 * @Security(name="Bearer")
 * @param Phone $phone
 * @return Phone
 */
    public function patchPhoneAction(Phone $phone)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($phone);
        $entityManager->flush();

        return $phone;
    }
}
