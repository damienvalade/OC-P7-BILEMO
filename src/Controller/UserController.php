<?php

namespace App\Controller;

use App\Entity\AccessToken;
use App\Entity\Client;
use App\Entity\User;
use App\Errors\Errors;
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
     * @var Errors
     */
    private $errors;

    /**
     * UserController constructor.
     * @param UserRepository $repository
     * @param UserPasswordEncoderInterface $encoder
     * @param Errors $errors
     */
    public function __construct(
        UserRepository $repository,
        UserPasswordEncoderInterface $encoder,
        Errors $errors
    )
    {
        $this->repository = $repository;
        $this->encoder = $encoder;
        $this->errors = $errors;
    }

    /**
     * List Users
     * @Rest\Get("/list/user", name="list_user")
     * @Rest\QueryParam(
     *     name="keyword",
     *     nullable=true,
     *     default="",
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
            $paramFetcher->get('keyword'),
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
     * @Rest\Post("/admin/add/user", name="add_user")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("user", converter="fos_rest.request_body")
     * @SWG\Parameter(
     *     name="username",
     *     in="body",
     *     type="string",
     *     description="Username of the User",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="username", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="email",
     *     in="body",
     *     type="string",
     *     description="Email of the User",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="email", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="password",
     *     in="body",
     *     type="string",
     *     description="Password of the User",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="password", type="string")
     *     )
     * )
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
     * @param Request $request
     * @param User $user
     * @param AccessTokenRepository $token
     * @return User
     */
    public function addUser(Request $request, User $user, AccessTokenRepository $token)
    {

        $client = $this->isAllowed($request, [
            'token' => $token,
            'user' => false
        ]);

        $this->validitySearch(json_decode($request->getContent(), true));

        $this->isUniq($user);

        $entityManager = $this->getDoctrine()->getManager();

        foreach ($user->getRoles() as $value) {
            if ($value == "ROLE_SUPER_ADMIN") {
                $this->errors->errorAllowed();
            }
        }

        $user->setClient($client->getClient());
        $this->commonSet($user);

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    /**
     * Patch Phone
     * @Rest\Patch("/admin/patch/user/{id}", name="patch_user", requirements={"id"="\d+"})
     * @ParamConverter("user", converter="fos_rest.request_body")
     * @Rest\View(StatusCode = 200)
     * @SWG\Parameter(
     *     name="username",
     *     in="body",
     *     type="string",
     *     description="Username of the User",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="username", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="email",
     *     in="body",
     *     type="string",
     *     description="Email of the User",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="email", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="password",
     *     in="body",
     *     type="string",
     *     description="Password of the User",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="password", type="string")
     *     )
     * )
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
    public function patchUserAction(Request $request, User $user, AccessTokenRepository $token)
    {
        $result = $this->repository->findOneBy(['id' => $request->get('id')]);

        $this->isAllowed($request, [
            'token' => $token,
            'user' => false
        ]);

        $this->validitySearch(json_decode($request->getContent(), true));

        $this->isUniq($user);

        foreach ($user->getRoles() as $value) {
            if ($value == "ROLE_SUPER_ADMIN") {
                $this->errors->errorAllowed();
            }
        }

        $this->commonSet($user);
        $this->dataSet($result, $user);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($result);
        $entityManager->flush();

        return $result;
    }

    /**
     * Delete User
     * @Rest\Delete("/admin/delete/user/{id}", name="delete_user", requirements={"id"="\d+"})
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
     * Delete User
     * @Rest\Post("/admin/promote/user/{id}", name="promote_user", requirements={"id"="\d+"})
     * @Rest\View(StatusCode = 200)
     * @SWG\Response(
     *     response=200,
     *     description="Promote User"
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
    public function promoteUser(Request $request, User $user, AccessTokenRepository $token)
    {
        $this->isAllowed($request, [
            'token' => $token,
            'user' => $user
        ]);

        $entityManager = $this->getDoctrine()->getManager();
        $user->addRole("ROLE_ADMIN");
        $entityManager->persist($user);
        $entityManager->flush();

        return;
    }

    /**
     * Delete User
     * @Rest\Post("/admin/demote/user/{id}", name="demote_user", requirements={"id"="\d+"})
     * @Rest\View(StatusCode = 200)
     * @SWG\Response(
     *     response=200,
     *     description="Demote User"
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
    public function demoteUser(Request $request, User $user, AccessTokenRepository $token)
    {
        $this->isAllowed($request, [
            'token' => $token,
            'user' => $user
        ]);

        $entityManager = $this->getDoctrine()->getManager();
        $user->removeRole("ROLE_ADMIN");
        $entityManager->persist($user);
        $entityManager->flush();

        return;
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
            $this->errors->errorAllowed();
        }

        return $client;
    }

    public function validitySearch($data)
    {

        $param = [
            "username",
            "email",
            "password"
        ];

        foreach ($param as $name) {
            if (empty($data[$name]) || !$data[$name]) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, $name . ' missing');
            }
        }
    }

    /**
     * @param User $user
     * @return User
     */
    public function commonSet(User $user)
    {
        $user->setUsernameCanonical($user->getUsername());
        $user->setEmailCanonical($user->getEmail());
        $user->setEnabled(1);
        $user->setSalt('');
        $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));

        return $user;
    }

    public function dataSet(User $result, User $user)
    {
        $result->setUsername($user->getUsername());
        $result->setUsernameCanonical($user->getUsernameCanonical());
        $result->setEmail($user->getEmail());
        $result->setEmailCanonical($user->getEmailCanonical());
        $result->setEnabled($user->isEnabled());
        $result->setSalt($user->getSalt());
        $result->setPassword($user->getPassword());
        $result->setLastLogin($user->getLastLogin());
        $result->setConfirmationToken($user->getConfirmationToken());
        $result->setPasswordRequestedAt($user->getPasswordRequestedAt());
        $result->setRoles($user->getRoles());
    }

    public function isUniq(User $user)
    {
        $check = $this->getDoctrine()->getRepository(User::class);
        $email = $check->findOneBy(['email' => $user->getEmail()]);
        $username = $check->findOneBy(['username' => $user->getUsername()]);

        if(!empty($email) || !empty($username)){
            $this->errors->errorCustom(Response::HTTP_CONFLICT, "Username ou email déja existant");
        }
        return true;
    }

}
