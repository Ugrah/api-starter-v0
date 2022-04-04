<?php

namespace App\Controller;

use App\Entity\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;

use App\Service\JWTService;
use App\Service\RefreshTokenManager;

use Gesdinet\JWTRefreshTokenBundle\Service\RefreshToken;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Service\EmailSender;

class JWTController extends AbstractController
{
    private $userRepository;
    private $jwtService;
    private $refreshTokenManager;

    private $_passwordEncoder;
    private $emailSender;


    public function __construct(
        UserRepository $userRepository,
        JWTService $jwtService,
        UserPasswordEncoderInterface $passwordEncoder,
        EmailSender $emailSender
    ) {
        $this->userRepository = $userRepository;
        $this->jwtService = $jwtService;
        $this->_passwordEncoder = $passwordEncoder;
        $this->emailSender = $emailSender;
    }

    /**
     * @Route("/jwt/get_token", name="get_token")
     */
    public function index(JWTTokenManagerInterface $JWTManager, EntityManagerInterface $em, Request $request): JsonResponse
    {
        $content = @json_decode($request->getContent());

        if ($content) {

            $hash_data = $this->encrypt_decrypt_user_data($content->hash_data, 'decrypt');
            $explode_hash_data = explode("|", $hash_data);


            if ($explode_hash_data[1] == $content->email) {
                $user = $this->userRepository->findOneByEmail($content->email);

                $refresh_token = bin2hex(random_bytes(64));
                $valid_date = date("Y-m-d H:i:s", strtotime("+1 hours"));


                if (!$user) return new JsonResponse(['code' => Response::HTTP_UNAUTHORIZED, 'message' => Response::$statusTexts[Response::HTTP_UNAUTHORIZED]], Response::HTTP_UNAUTHORIZED);

                /*
                if (!$user) {
                    $password =  $this->getRandomPassword();

                    $user = new User();
                    $user->setPlainPassword($password);
                    $user->setPassword(
                        $this->_passwordEncoder->encodePassword(
                            $user,
                            $user->getPlainPassword()
                        )
                    );

                    $user_lf_name = explode(" ", $content->name);
                    $user->setFirstname($user_lf_name[0]);
                    $user->setLastname($user_lf_name[count($user_lf_name) - 1]);
                    $username = strtolower(join('', $user_lf_name));
                    $user->setUsername($username);
                    $user->setEmail($content->email);
                    // $user->setEmail('xixonep193@terasd.com'); // To fixed email test
                    $user->setStatus(false); // Make a activation system to send link by email to activate user. Add status in response OR admin manual activation

                    // Email Sender
                    if ($user->getPlainPassword()) {
                        $mail_data = [
                            'username' => $user->getUserIdentifier(),
                            'password' => $user->getPlainPassword()
                        ];
                        if ($user->getEmail()) $this->emailSender->newUserAdded($user->getEmail(), $mail_data);
                    }
                    // * End Email sender
                    $user->eraseCredentials();

                    $em->persist($user);
                    $em->flush($user);
                } */
                $status = null;
                try {
                    $query = 'INSERT INTO `refresh_tokens` (`refresh_token`, `username`, `valid`) VALUES (:refresh_token, :username, :valid )';
                    $conn = $em->getConnection();
                    $stmt = $conn->prepare($query);
                    $stmt->bindValue(':refresh_token', $refresh_token);
                    $stmt->bindValue(':username', $user->getUsername());
                    $stmt->bindValue(':valid', $valid_date);
                    $resultOfInsert = $stmt->execute();

                    $status = 201;
                } catch (\Exception $e) {
                    // dd($e);
                    $status = 500;
                    $data['message'] = $e->getMessage();
                }

                $token = ['token' => $JWTManager->createFromPayload($user, ['status' => $user->getStatus()]), 'refresh_token' => $refresh_token];

                if (!$user->getStatus()) return new JsonResponse(['code' => Response::HTTP_UNAUTHORIZED, 'message' => 'User not activate'], Response::HTTP_UNAUTHORIZED);

                if ($status == Response::HTTP_CREATED) return new JsonResponse($token, $status);
                else return new JsonResponse(['code' => Response::HTTP_INTERNAL_SERVER_ERROR, 'message' => isset($data['message']) ? $data['message'] : 'An error has occurred. please try again later.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            } else return new JsonResponse(['code' => Response::HTTP_UNAUTHORIZED, 'message' => Response::$statusTexts[Response::HTTP_UNAUTHORIZED]], Response::HTTP_UNAUTHORIZED);
        } else return new JsonResponse(['code' => Response::HTTP_BAD_REQUEST, 'message' => 'Bad Request'], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/admin/jwt/get_token", name="admin_get_token")
     */
    public function admin(JWTTokenManagerInterface $JWTManager, EntityManagerInterface $em, Request $request): JsonResponse
    {
        // $username = 'admin'; // Get request username

        $content = @json_decode($request->getContent());
        // var_dump( $content ); exit;

        // IMPORTANT !!!
        // Make content hash verification to check data to send and check  result before auth user.

        if (!$content) return new JsonResponse(['code' => Response::HTTP_BAD_REQUEST, 'message' => 'Bad Request'], Response::HTTP_BAD_REQUEST);

        $user = $this->userRepository->findOneByEmail($content->email);

        $refresh_token = bin2hex(random_bytes(64));
        $valid_date = date("Y-m-d H:i:s", strtotime("+1 hours"));

        // Unauthorized if not admin user
        if ((!$user) || (!in_array('ROLE_ADMIN', $user->getRoles()))) return new JsonResponse(['code' => Response::HTTP_UNAUTHORIZED, 'message' => Response::$statusTexts[Response::HTTP_UNAUTHORIZED]], Response::HTTP_UNAUTHORIZED);

        $status = null;

        try {
            $query = 'INSERT INTO `refresh_tokens` (`refresh_token`, `username`, `valid`) VALUES (:refresh_token, :username, :valid )';
            $conn = $em->getConnection();
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':refresh_token', $refresh_token);
            $stmt->bindValue(':username', $user->getUsername());
            $stmt->bindValue(':valid', $valid_date);
            $resultOfInsert = $stmt->execute();

            $status = 201;
        } catch (\Exception $e) {
            // dd($e);
            $status = $e->getCode();
            $data['message'] = $e->getMessage();
        }

        $token = ['token' => $JWTManager->createFromPayload($user, ['status' => $user->getStatus()]), 'refresh_token' => $refresh_token];

        if (!$user->getStatus()) return new JsonResponse(['code' => Response::HTTP_UNAUTHORIZED, 'message' => 'User not activate'], Response::HTTP_UNAUTHORIZED);

        if ($status == Response::HTTP_CREATED) return new JsonResponse($token, $status);
        else return new JsonResponse(['code' => $status, 'message' => isset($data['message']) ? $data['message'] : Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR]], Response::HTTP_INTERNAL_SERVER_ERROR);
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

    public function encrypt_decrypt_user_data($string_data, $action = 'encrypt')
    {
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'z$B&E)H@McQfTjWnZr4u7x!A%D*F-JaN'; // user define private key
        $secret_iv = '4t7w!z%C*F-JaNdR'; // user define secret key
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string_data, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string_data), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
}
