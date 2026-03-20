<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\LoginFormType;

/**
 * Contrôleur de sécurité : connexion, déconnexion et inscription.
 */
final class SecurityController extends AbstractController
{
    /**
     * Affiche le formulaire de connexion et gère les erreurs éventuelles.
     *
     * @param AuthenticationUtils $authenticationUtils Fournit le dernier identifiant et la dernière erreur
     *
     * @return Response Réponse HTML de la page de connexion
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginFormType::class);
        // On pré-remplit le champ email avec le dernier identifiant utilisé
        $form->get('email')->setData($lastUsername);

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'loginForm' => $form->createView(),
        ]);
    }

    /**
     * Point de sortie pour la déconnexion.
     * La logique est gérée par le firewall SecurityBundle.
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Ce code peut rester vide : Symfony interceptera cette requête.
    }

    /**
     * Gère l'inscription d'un nouvel utilisateur.
     *
     * @param Request                    $request        Requête HTTP contenant le formulaire
     * @param UserPasswordHasherInterface $passwordHasher Service de hachage des mots de passe
     * @param EntityManagerInterface     $entityManager  Gestionnaire Doctrine pour la persistance
     *
     * @return Response Réponse HTML du formulaire ou redirection vers la connexion
     */
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User(); // apiAccess sera false par défaut
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            // Pour l’instant : redirection vers la connexion
            $this->addFlash('success', 'Votre compte a été créé, vous pouvez vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
