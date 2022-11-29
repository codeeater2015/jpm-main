<?php

namespace AppBundle\Controller\System;


use AppBundle\Entity\User;
use AppBundle\Form\ChangePasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends Controller
{

    /**
     * @Route("/edit_account", name="edit_account_profile")
     */
    public function editAccountAction()
    {
        return $this->render("template/system/account_profile.html.twig",[]);
    }

    /**
     * @Route("/change_password", name="change_password")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function changePasswordAction(Request $request)
    {
        $account = new User();
        $form = $this->createForm(ChangePasswordType::class, $account);

        $form->handleRequest($request);

        if ($request->getMethod() == "POST") {
            if ($form->isSubmitted() && $form->isValid()) {
                $password = $this->get('security.password_encoder')->encodePassword($account, $account->getPassword());

                $em = $this->getDoctrine()->getManager();
                $user = $this->getUser();

                $user->setPassword($password);
                $em->flush();

                $this->addFlash(
                    'success',
                    'change_password.updated_successfully'
                );

                return $this->redirectToRoute("change_password");
            }
        }

        return $this->render("template/system/change_password.html.twig",[
            'form' => $form->createView()
        ]);
    }
}