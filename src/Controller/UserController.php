<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UserService;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
            $this->translator->trans('title.web_permissions')
        );
    }

    /**
     * This page display all users for admin
     *
     * @Route("/users/list", name="users_index")
     * @IsGranted("ROLE_ADMIN")
     *
     * @param UserService $userService
     * @return Response
     */
    public function getUsers(UserService $userService): Response
    {
        $this->breadcrumbs->addItem(
            $this->translator->trans('title.users'),
            $this->get('router')->generate("users_index")
        );

        $users = $userService->findAll();

        return $this->render('core/route/users/index.html.twig', ['users' => $users]);
    }

    // TODO: find a way to block access to edit if user connected is different that userId asked

    /**
     * @Route ("/user/new", name="user_new")
     * @Route ("/user/edit/{userId}", name="user_edit")
     * @param Request $request
     * @param UserService $userService
     * @param int|null $userId
     * @return RedirectResponse|Response
     * @throws ORMException
     */
    public function setUser(Request $request, UserService $userService, int $userId = null): Response
    {
        // Add breadcrumb + allow "new user" only for admins
        $router = $this->get('router');
        if (!empty($userId)) {
            $this->breadcrumbs->addItem("form.edit_user", $router->generate("user_edit", ['userId' => $userId]));
        } else {
            $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
            $this->breadcrumbs->addItem("form.new_user", $router->generate("user_new"));
        }

        $user = new User();
        $isAdmin = $this->isGranted(User::ROLE_ADMIN);

        // Get the edited entity if ID is present
        if (!empty($userId)) {
            $user = $userService->findOneById($userId);
            if (empty($user)) {
                $this->addFlash('danger', $this->translator->trans('flash.user_not_found'));
                return $this->redirect($this->generateUrl('users_index'));
            }
            if ($user !== $this->getUser() && !$isAdmin) {
                // Non-admin user attempt to edit another user. No specific message for security purposes.
                return $this->redirect($this->generateUrl('dashboard'));
            }
        }

        // Create the form
        $form = $this->createForm(UserType::class, $user, [
            'appRoles' => $userService->getAvailableAppRoles(),
            'isNew' => empty($userId),
            'allowAdminEdit' => $isAdmin
        ]);

        // Handle the form
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userData = $form->getData();

            if (empty($userId)) {
                $userCreated = $userService->create($userData);
                if ($userCreated) {
                    $this->addFlash('success', $this->translator->trans('flash.user_created'));
                    return $this->redirect($this->generateUrl('users_index'));
                } else {
                    $errors = '';
                    foreach ($userService->getErrors() as $error) {
                        $errors .= $error . ', ';
                    }
                    $this->addFlash('danger', 'User cannot be created. Reasons: ' . substr($errors, 0, -2));
                    return $this->redirect($router->generate('users_index'));
                }
            } else {
                $userSaved = $userService->update($userData);
                if ($userSaved) {
                    $this->addFlash('success', $this->translator->trans('flash.user_saved'));
                } else {
                    $errors = '';
                    foreach ($userService->getErrors() as $error) {
                        $errors .= $error . ', ';
                    }
                    $this->addFlash('danger', 'Cannot save user. Reasons: ' . substr($errors, 0, -2));
                }
            }

            return $this->redirect($router->generate("user_edit", ['userId' => $userId]));
        }

        // Fill template vars
        $templateVars = [
            'user' => $user,
            'form' => $form->createView()
        ];
        return $this->render('core/route/users/user.html.twig', $templateVars);
    }

    /**
     * @Route ("/user/delete/{userId}", name="user_delete")
     * @IsGranted("ROLE_ADMIN")
     * @param UserService $userService
     * @param int $userId
     * @return RedirectResponse|Response
     * @throws ORMException
     */
    public function deleteUser(UserService $userService, int $userId): Response
    {
        $userDeleted = $userService->delete($userId);

        if ($userDeleted) {
            $this->addFlash('success', $this->translator->trans('flash.user_deleted'));
        } else {
            $errors = '';
            foreach ($userService->getErrors() as $error) {
                $errors .= $error . ', ';
            }
            $this->addFlash('danger', 'Cannot delete user. Reasons: ' . substr($errors, 0, -2));
        }

        return $this->redirect($this->generateUrl('users_index'));
    }

    /**
     * Permissions index page.
     * This page lists the available roles, Bot roles (everyone) and App roles (if you are admin).
     *
     * @Route("/users/roles", name="users_roles")
     * @param UserService $userService
     * @return Response
     */
    public function roles(UserService $userService): Response
    {
        $this->breadcrumbs->addItem(
            $this->translator->trans('title.roles'),
            $this->get("router")->generate("users_roles")
        );

        $templateVars = [];
        $templateVars['roles'] = $userService->getAvailableAppRoles();
        return $this->render('core/route/users/roles.html.twig', $templateVars);
    }
}
