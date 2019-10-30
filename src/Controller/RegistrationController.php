<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/registration", name="registration")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $form = $this->createFormBuilder()
        ->add('username', EmailType::class)
        ->add('password', RepeatedType::class, [
            'type'=>PasswordType::class,
            'invalid_message' => 'The password fields must match.',
            'options' => ['attr' => ['class' => 'password-field']],
            'required' => true,
            'first_options'  => ['label' => 'Password'],
            'second_options' => ['label' => 'Confirm  Password'],
        ])
        ->add('save', SubmitType::class, ['label'=>'Register'])
        ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //dump($form);
            $user = new User();
            $userName = $form['username']->getData();
            $userPassword = $form['password']->getData();
            $user->setEmail($userName);   //we use email as username
            //--------
            $encodedPassword = $passwordEncoder->encodePassword($user, $userPassword);
            $user->setPassword($encodedPassword);
            //----------
            // $roles = array('ROLE_CONNECTED');
            // $user->setRoles($roles);
            //dump($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash(
                'success',
                'User account is created'
            );

            return $this->redirect($this->generateUrl('app_login'));
        }else{
            return $this->render('registration/index.html.twig', [
                'form' =>$form->createView()
            ]);
        }
    }
}
