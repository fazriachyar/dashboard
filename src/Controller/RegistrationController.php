<?php
  
namespace App\Controller;
  
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
  
/**
 * @Route("/api", name="api_")
 */
class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="register", methods={"POST"})
     */
    public function index(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
          
        $em = $doctrine->getManager();
        $data = json_decode($request->getContent(),true);

        $checkName = $em->getRepository(User::class)
            ->findOneBy([
                "username" => $data['username']
            ]);
        if($checkName){
            $message['response']['failed'] = "Username Already Exist";
        }
        else{
            $user = new User();

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $data['password']
            );

            $user->setPassword($hashedPassword);
            $user->setUsername($data['username']);

            if(isset($data['email'])){
                $user->setEmail($data['email']);
            }
            if(isset($data['type'])){
                if($data['type'] == "customer"){
                    $user->setIsCustomer(1);
                    $user->setIsStaff(0);
                }
                elseif($data['type'] == "staff"){
                    $user->setIsStaff(1);
                    $user->setIsCustomer(0);
                }
            }
            if(isset($data['fullName'])){
                $user->setFullName($data['fullName']);
            }
            $em->persist($user);
            $em->flush();
            $message['response']['success'] = "Register Success";
        }
        return $this->json($message);
    }
}