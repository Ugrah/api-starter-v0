<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

use Symfony\Component\Routing\Annotation\Route;

use App\Service\EmailSender;


class MailerController extends AbstractController
{
    private $emailSender;

    public function __construct(EmailSender $emailSender)
    {
        $this->emailSender = $emailSender;
    }

    /**
     * @Route("/test/emails/registered", name="email_add_user")
     */
    public function index(): Response
    {
        return $this->render('emails/registered.html.twig', [
            // 'controller_name' => 'MailerController',
            'username' => 'WIS67361',
            'password' => '4017893HDZJDNZU'
        ]);
    }

    /**
     * @Route("/test/email")
     */
    // public function sendEmail(MailerInterface $mailer): Response
    public function sendEmail(MailerInterface $mailer)
    {
        // $email = (new Email())
        //     ->from('grulog23@gmail.com')
        //     ->to('ulrich@maxmind.ma')
        //     //->cc('cc@example.com')
        //     //->bcc('bcc@example.com')
        //     //->replyTo('fabien@example.com')
        //     //->priority(Email::PRIORITY_HIGH)
        //     ->subject('New user added!')
        //     ->text('Sending emails is fun again!')
        //     ->html('<p>See Twig integration for better HTML integration!</p>');

        // $email = (new TemplatedEmail())
        //     ->from('grulog23@gmail.com')
        //     ->to(new Address('ulrich@maxmind.ma'))
        //     ->subject('New user added!')

        //     // path of the Twig template to render
        //     ->htmlTemplate('emails/registered.html.twig')

        //     // pass variables (name => value) to the template
        //     ->context([
        //         'username' => 'WIS67361',
        //         'password' => '4017893HDZJDNZU'
        //     ]);

        // $mailer->send($email);

        $user_email = 'ulrich@maxmind.ma';
        $data = [
            'username' => 'WIS67361',
            'password' => '4017893HDZJDNZU'
        ];
        $response = $this->emailSender->newUserAdded($user_email, $data);

        // $response = 'Ok ;)';
        // ...
        return $this->json($response);
    }
}
