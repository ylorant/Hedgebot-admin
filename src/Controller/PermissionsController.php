<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\BotRoleType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PermissionsController
 * Including App roles and Bot roles management
 *
 * @package App\Controller
 */
class PermissionsController extends BaseController
{
    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();
        $this->breadcrumbs->addItem(
            'title.bot_permissions',
            $this->generateUrl('permissions_index')
        );
    }

    /**
     * Bot permissions index page.
     * This page lists the available bot roles.
     *
     * @Route("/permissions", name="permissions_index")
     * @param UserService $userService
     * @return Response
     */
    public function index(UserService $userService): Response
    {
        $templateVars = [];
        $securityEndpoint = $this->apiClientService->endpoint('/security');
        $templateVars['bot_roles'] = (array) $securityEndpoint->getRoles();
        if ($this->isGranted(User::ROLE_ADMIN)) {
            $templateVars['app_roles'] = $userService->getAvailableAppRoles();
        }

        return $this->render('core/route/permissions/bot.html.twig', $templateVars);
    }

    /**
     * Bot Role detail page.
     * This page shows the detail of a role allowing bot commands usage.
     *
     * @Route ("/permissions/bot/role/new", name="permissions_role_new")
     * @Route ("/permissions/bot/role/edit/{roleId}", name="permissions_role_edit")
     * @param Request $request
     * @param null $roleId
     * @return RedirectResponse|Response
     */
    public function role(Request $request, $roleId = null)
    {
        // Get the role and the available rights
        $securityEndpoint = $this->apiClientService->endpoint('/security');
        $role = $securityEndpoint->getRole($roleId);
        $roles = (array) $securityEndpoint->getRoles();
        $rights = (array) $securityEndpoint->getRights();

        // Add breadcrumb
        if (!empty($roleId)) {
            $this->breadcrumbs->addItem($role->name, $this->router->generate("permissions_role_edit", ['roleId' => $roleId]));
        } else {
            $this->breadcrumbs->addItem("New role", $this->router->generate("permissions_role_new"));
        }

        // Filter the role rights to make them as an array of the actually defined rights.
        if (!empty($role)) {
            $role->rights = (array) $role->rights;
            $role->inheritedRights = (array) $role->inheritedRights;
        }

        // Create the form
        $form = $this->createForm(BotRoleType::class, $role, [
            'rights' => $rights, // Give the form the available rights
            'botRoles' => $roles, // Give it all the roles to be able to figure out inheritance
            'allowIdEdit' => empty($roleId) // Set wether we can edit the role ID or not
        ]);

        // Handle the form
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $roleData = $form->getData();
            $roleCreated = true;

            // If this is a new role, we have to create it before trying to save anything
            if (empty($roleId)) {
                $roleCreated = $securityEndpoint->createRole($roleData->id);
                if (!$roleCreated) {
                    $this->addFlash('danger', 'Role cannot be created.');
                    return $this->redirect($this->router->generate('permissions_index'));
                }

                $roleId = $roleData->id;
            }

            if ($roleCreated) {
                $roleSaved = $securityEndpoint->saveRole($roleId, $roleData);
                if ($roleSaved) {
                    $this->addFlash('success', 'Role saved.');
                } else {
                    $this->addFlash('danger', 'Cannot save role.');
                }
            }

            return $this->redirect($this->router->generate("permissions_role_edit", ['roleId' => $roleId]));
        }

        // Fill template vars
        $templateVars = [
            'role' => $role,
            'rights' => $rights,
            'form' => $form->createView()
        ];
        return $this->render('core/route/permissions/role.html.twig', $templateVars);
    }

    /**
     * Delete role action.
     * Deletes a role by its ID.
     *
     * @Route ("/permissions/bot/role/delete/{roleId}", name="permissions_role_delete")
     * @param $roleId
     * @return RedirectResponse
     */
    public function deleteRole($roleId): RedirectResponse
    {
        $securityEndpoint = $this->apiClientService->endpoint('/security');
        $roleDeleted = $securityEndpoint->deleteRole($roleId);
        if ($roleDeleted) {
            $this->addFlash('success', "Role deleted.");
        } else {
            $this->addFlash('danger', 'Failed to delete role.');
        }

        return $this->redirect($this->router->generate("permissions_index"));
    }
}
