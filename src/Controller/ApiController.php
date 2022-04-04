<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Security;

use App\Entity\User;
use App\Repository\UserRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class ApiController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;

    private $userRepository;



    public function __construct(UserRepository $userRepository, Security $security, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->security = $security;
        $this->userRepository = $userRepository;
        $this->_passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/api/me", name="api_me")
     */
    public function me(): JsonResponse
    {
        $user = $this->security->getUser();
        
        if (!$user) return new JsonResponse(['code' => Response::HTTP_UNAUTHORIZED, 'message' => Response::$statusTexts[Response::HTTP_UNAUTHORIZED]], Response::HTTP_UNAUTHORIZED);
        
        $data = [
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
            'email' => $user->getEmail(),
        ];
        return new JsonResponse($data);
    }

    /**
     * @Route("/api/register", name="api_register")
     */
    public function register(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $user = null;
        $content = @json_decode($request->getContent());

        if (!empty($content->email)) $user = $this->userRepository->findOneByEmail($content->email);


        if ($user) return new JsonResponse(['code' => Response::HTTP_FORBIDDEN, 'message' => Response::$statusTexts[Response::HTTP_FORBIDDEN]], Response::HTTP_FORBIDDEN);

        // if(!$content->termsChecker) return new JsonResponse(['code' => Response::HTTP_FORBIDDEN, 'message' => Response::$statusTexts[Response::HTTP_FORBIDDEN]], Response::HTTP_FORBIDDEN);

        $data = [];
        $status = null;
        try {
            $password =  $this->getRandomPassword();

            $user = new User();
            $user->setPlainPassword($password);
            $user->setPassword(
                $this->_passwordEncoder->encodePassword(
                    $user,
                    $user->getPlainPassword()
                )
            );

            $user->setFirstname($content->firstname);
            $user->setLastname($content->lastname);
            $username = '';
            $fixed_username = $content->firstname;
            while($this->userRepository->findOneByUsername($username)){
                $username = $fixed_username.'_'.time();
            }

            $user->setUsername($username);
            $user->setEmail($content->email);
            $user->setPhonenumber($content->phonenumber);
            $user->setBuilding($content->building);
            $user->setApartment($content->apartment);
            $user->setStatus(false); // Make a activation system to send link by email to activate user. Add status in response OR admin manual activation

            // Email Sender
            if ($user->getPlainPassword()) {
                $mail_data = [
                    'username' => $user->getUserIdentifier(),
                    'password' => $user->getPlainPassword()
                ];
                // if ($user->getEmail()) $this->emailSender->newUserAdded($user->getEmail(), $mail_data);
                // Create template for main admin receive new user added in DB
            }
            // * End Email sender

            $user->eraseCredentials();

            $em->persist($user);
            $em->flush($user);

            $status = 201;
            $data = [
                'id' => $user->getId(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                // 'username' => $user->getUsername(),
                // 'roles' => $user->getRoles(),
                'email' => $user->getEmail(),
                'phonenumber' => $user->getPhonenumber(),
                'building' => $user->getBuilding(),
                'apartment' => $user->getApartment(),
            ];
        } catch (\Exception $e) {
            // dd($e);
            $status = 500;
            $data['message'] = $e->getMessage();
        }

        if ($status == Response::HTTP_CREATED) return new JsonResponse($data, $status);
        else return new JsonResponse(['code' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => isset($data['message']) ? $data['message'] : 'An error has occurred. please try again later.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function getRandomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}
