<?php
namespace Hedgebot\CoreBundle\Controller;

use Hedgebot\CoreBundle\Entity\User;
use Hedgebot\CoreBundle\Entity\DashboardLayout;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Hedgebot\CoreBundle\Form\RoleType;

class SecurityController extends BaseController
{
    /**
     * Hook that is executed before the action is called.
     * Mainly, binds the breadcrumb for the whole controller.
     */
    public function beforeActionHook()
    {
        parent::beforeActionHook();

        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $breadcrumbs->addItem("Permissions", $this->get("router")->generate("security_index"));
    }

    /** Security index page.
     * This page lists the available roles.
     * 
     * @Route("/security", name="security_index")
     */
    public function indexAction()
    {
        $templateVars = [];

        $securityEndpoint = $this->get('hedgebot_api')->endpoint('/security');
        $templateVars['roles'] = (array) $securityEndpoint->getRoles();

        return $this->render('HedgebotCoreBundle::route/security/index.html.twig', $templateVars);
    }
    
    /** Role detail page.
     * This page shows the detail of a role.
     * 
     * @Route ("/security/role/new", name="security_role_new")
     * @Route ("/security/role/edit/{roleId}", name="security_role_edit")
     */
    public function roleAction(Request $request, $roleId = null)
    {
        // Get the role and the available rights
        $securityEndpoint = $this->get('hedgebot_api')->endpoint('/security');
        $role = $securityEndpoint->getRole($roleId);
        $roles = (array) $securityEndpoint->getRoles();
        $rights = (array) $securityEndpoint->getRights();
        
        // Add breadcrumb
        $breadcrumbs = $this->get('white_october_breadcrumbs');
        $router = $this->get("router");
        if(!empty($roleId))    
            $breadcrumbs->addItem($role->name, $router->generate("security_role_edit", ['roleId' => $roleId]));
        else
            $breadcrumbs->addItem("New role", $router->generate("security_role_new"));
        
        // Filter the role rights to make them as an array of the actually defined rights.
        if(!empty($role))
        {
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

        if($form->isSubmitted() && $form->isValid())
        {
            $roleData = $form->getData();
            $roleCreated = true;

            // If this is a new role, we have to create it before trying to save anything
            if(empty($roleId))
            {
                $roleCreated = $securityEndpoint->createRole($roleData->id);
                if(!$roleCreated)
                    $this->addFlash('danger', 'Role cannot be created.');
                
                $roleId = $roleData->id;
            }

            if($roleCreated)
            {
                $roleSaved = $securityEndpoint->saveRole($roleId, $roleData);

                if($roleSaved)
                    $this->addFlash('info', 'Role saved.');
            }

            return $this->redirect($router->generate("security_role_edit", ['roleId' => $roleId]));
        }

        // Fill template vars
        $templateVars = [
            'role' => $role,
            'rights' => $rights,
            'form' => $form->createView()
        ];

        return $this->render('HedgebotCoreBundle::route/security/role.html.twig', $templateVars);
    }
}