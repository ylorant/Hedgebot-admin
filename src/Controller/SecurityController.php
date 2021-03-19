<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use App\Form\RoleType;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends BaseController
{
    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();
        $this->breadcrumbs->addItem(
            $this->translator->trans('title.permissions'),
            $this->get("router")->generate("security_index")
        );
    }

    /** Security index page.
     * This page lists the available roles.
     *
     * @Route("/security", name="security_index")
     */
    public function indexAction()
    {
        $templateVars = [];
        $securityEndpoint = $this->apiClientService->endpoint('/security');
        $templateVars['roles'] = (array) $securityEndpoint->getRoles();
        return $this->render('core/route/security/index.html.twig', $templateVars);
    }

    /** Role detail page.
     * This page shows the detail of a role.
     *
     * @Route ("/security/role/new", name="security_role_new")
     * @Route ("/security/role/edit/{roleId}", name="security_role_edit")
     * @param Request $request
     * @param null $roleId
     * @return RedirectResponse|Response
     */
    public function roleAction(Request $request, $roleId = null)
    {
        // Get the role and the available rights
        $securityEndpoint = $this->apiClientService->endpoint('/security');
        $role = $securityEndpoint->getRole($roleId);
        $roles = (array) $securityEndpoint->getRoles();
        $rights = (array) $securityEndpoint->getRights();

        // Add breadcrumb
        $router = $this->get('router');
        if (!empty($roleId)) {
            $this->breadcrumbs->addItem($role->name, $router->generate("security_role_edit", ['roleId' => $roleId]));
        } else {
            $this->breadcrumbs->addItem("New role", $router->generate("security_role_new"));
        }

        // Filter the role rights to make them as an array of the actually defined rights.
        if (!empty($role)) {
            $role->rights = (array) $role->rights;
            $role->inheritedRights = (array) $role->inheritedRights;
        }

        // Create the form
        $form = $this->createForm(RoleType::class, $role, [
            'rights' => $rights, // Give the form the available rights
            'roles' => $roles, // Give it all the roles to be able to figure out inheritance
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
                    return $this->redirect($router->generate('security_index'));
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

            return $this->redirect($router->generate("security_role_edit", ['roleId' => $roleId]));
        }

        // Fill template vars
        $templateVars = [
            'role' => $role,
            'rights' => $rights,
            'form' => $form->createView()
        ];
        return $this->render('core/route/security/role.html.twig', $templateVars);
    }

    /** Delete role action.
     * Deletes a role by its ID.
     *
     * @Route ("/security/role/delete/{roleId}", name="security_role_delete")
     * @param $roleId
     * @return RedirectResponse
     */
    public function deleteRoleAction($roleId)
    {
        $router = $this->get("router");
        $securityEndpoint = $this->apiClientService->endpoint('/security');
        $roleDeleted = $securityEndpoint->deleteRole($roleId);
        if ($roleDeleted) {
            $this->addFlash('success', "Role deleted.");
        } else {
            $this->addFlash('danger', 'Failed to delete role.');
        }

        return $this->redirect($router->generate("security_index"));
    }
}
