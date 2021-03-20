<?php

namespace App\Controller;

use App\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends BaseController
{
    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        $this->breadcrumbs->addItem(
            $this->translator->trans('title.users'),
            $this->get('router')->generate("users_index")
        );
    }

    /**
     * This page display all users for admin
     *
     * @Route("/users", name="users_index")
     * @IsGranted("ROLE_ADMIN")
     *
     * @param UserService $userService
     * @return Response
     */
    public function getUsers(UserService $userService): Response
    {
        $users = $userService->findAll();

        return $this->render('core/route/users/index.html.twig', ['users' => $users]);
    }

    // @TODO find a way to block access to edit if user connected is different that userId asked
    /**
     * @Route ("/user/new", name="user_new")
     * @Route ("/user/edit/{userId}", name="user_edit")
     * @param UserService $userService
     * @param int|null $userId
     * @return RedirectResponse|Response
     */
    public function setUser(UserService $userService, int $userId = null): Response
    {
        $user = $userService->findOneById($userId);

        // Add breadcrumb
        $router = $this->get('router');
        if (!empty($userId)) {
            $this->breadcrumbs->addItem($user->username, $router->generate("user_edit", ['userId' => $userId]));
        } else {
            $this->breadcrumbs->addItem("New user", $router->generate("user_new"));
        }

        return false;
    }

    /**
     * @Route ("/user/delete/{userId}", name="user_delete")
     * @param UserService $userService
     * @param int $userId
     * @return RedirectResponse|Response
     */
    public function deleteUser(UserService $userService, int $userId): Response
    {
        $user = $userService->findOneById($userId);

        return false;
    }
}
