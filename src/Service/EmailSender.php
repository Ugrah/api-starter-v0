<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;


class EmailSender {

    private $params;
    private $mailer;

    public function __construct(ContainerBagInterface $params, MailerInterface $mailer)
    {
        $this->params = $params;
        $this->mailer = $mailer;
    }

    public function newUserAdded($user_email = null, $data = [])
    {
        $from = $this->params->get('app.admin_email');

        $email = (new TemplatedEmail())
            ->from($from)
            ->to(new Address($user_email))
            ->subject('New user added!')
            // ->priority(Email::PRIORITY_HIGH)
            
            // path of the Twig template to render
            ->htmlTemplate('emails/registered.html.twig')

            // pass variables (name => value) to the template
            ->context($data);

        // $response = $this->mailer->send($email);
        return $this->mailer->send($email);
    }

}