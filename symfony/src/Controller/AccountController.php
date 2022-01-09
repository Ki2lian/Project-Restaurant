<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\RegistrationFormType;
use App\Form\EditAccountFormType;

class AccountController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $isRestaurateur = isset($request->get('registration_form')["restaurateur"]);
            if($isRestaurateur) $user->setRoles(['ROLE_ADMIN']);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('login');
        }

        return $this->render('account/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('account/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }
    
    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(): void
    {
        // controller can be blank: it will never be called!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    #[Route('/account', name: 'account', methods: ['GET'])]
    public function account(): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditAccountFormType::class, $user);
        return $this->render('account/account.html.twig', [
            'editAccountForm' => $form->createView(),
        ]);
    }

    #[Route('/account/edit', name: 'edit_account', methods: ['POST'])]
    public function editAccount(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $isAjax = $request->isXMLHttpRequest();
        if (!$isAjax) return new Response('', 404);

        $user = $this->getUser();
        $form = $this->createForm(EditAccountFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $match = $userPasswordHasher->isPasswordValid($user, $form->get('plainPassword')->getData());
            if($match){
                $entityManager->flush();
                return $this->json(array(
                    "code" => 200,
                    "message" => "Your information has been updated",
                    "info" => array(
                        'firstname' => $user->getFirstname(),
                        'lastname' => $user->getLastname(),
                    )
                ),200);
            }
            return $this->json(array(
                "code" => 200,
                "errors" => array(
                    array(
                        "message" => "Password is incorrect"
                    )
                )
            ),200);

        }

        return $this->json(array(
            "code" => 200,
            "errors" => $form->getErrors()
        ),200);
    }

    #[Route('/account/edit_password', name: 'edit_password', methods: ['POST'])]
    public function editPassword(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $isAjax = $request->isXMLHttpRequest();
        if (!$isAjax) return new Response('', 404);

        $edit_password_form = $request->get('edit_password_form');
        $oldPassword = $edit_password_form['oldPassword'];
        $newPassword = $edit_password_form['newPassword'];
        $user = $this->getUser();

        if(preg_match('/^(?=\P{Ll}*\p{Ll})(?=\P{Lu}*\p{Lu})(?=\P{N}*\p{N})(?=[\p{L}\p{N}]*[^\p{L}\p{N}])[\s\S]{8,4096}$/', $oldPassword) &&
           preg_match('/^(?=\P{Ll}*\p{Ll})(?=\P{Lu}*\p{Lu})(?=\P{N}*\p{N})(?=[\p{L}\p{N}]*[^\p{L}\p{N}])[\s\S]{8,4096}$/', $newPassword)){
            $match = $userPasswordHasher->isPasswordValid($user, $oldPassword);
            if($match){
                if($oldPassword !== $newPassword){
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $newPassword
                        )
                    );

                    $entityManager->flush();
                    return $this->json(array(
                        "code" => 200,
                        "message" => "Your password has been changed"
                    ),200);
                }else{
                    $errors = array(
                        "message" => "The new password must be different from the old one"
                    );
                }
            }else{
                $errors = array(
                    "message" => "Old password is incorrect"
                );
            }
        }else{
            $errors = array(
                "message" => "Passwords must contain:
                a minimum of 1 lower case letter, 
                a minimum of 1 upper case letter, 
                a minimum of 1 special character and 
                your password should be at least 8 characters."
            );
        }
        return $this->json(array(
            "code" => 200,
            "errors" => array($errors)
        ),200);
    }
}
