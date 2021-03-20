<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{
    /** @var EntityManagerInterface */
    private $em;
    /**
     * @var UserRepository|ObjectRepository
     */
    private $repository;
    /**
     * @var ValidationService
     */
    private $validator;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var User
     */
    private $user;
    /**
     * @var array
     */
    private $errors = [];

    public function __construct(
        EntityManagerInterface $em,
        ValidationService $validator,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->em = $em;
        $this->validator = $validator;
        $this->passwordEncoder = $passwordEncoder;
        $this->repository = $this->em->getRepository(User::class);
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * Return errors. Mainly useful for commands
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Users list
     *
     * @return User[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * User by id
     *
     * @param int $userId
     * @return User
     */
    public function findOneById(int $userId): User
    {
        return $this->repository->find($userId);
    }

    /**
     * User by username
     *
     * @param string $username
     * @return User|null
     */
    public function findOneByUsername(string $username): ?User
    {
        return $this->repository->findOneBy(['username' => $username]);
    }

    /**
     * Create a new user
     * @param $credentials
     * @return bool
     * @throws ORMException
     */
    public function create($credentials = []): bool
    {
        $username = isset($credentials['username']) ? $credentials['username'] : "";
        $password = isset($credentials['password']) ? $credentials['password'] : "";
        $passwordConfirmation = isset($credentials['password_confirmation']) ?
            $credentials['password_confirmation'] : "";

        $errors = [];
        if ($this->findOneByUsername($username)) {
            $errors[] = "This username already exists.";
        }
        if ($password != $passwordConfirmation) {
            $errors[] = "Password does not match the password confirmation.";
        }
        if (strlen($password) < 8) {
            $errors[] = "Password should be at least 8 characters.";
        }

        if (!$errors) {
            $user = new User();
            $encodedPassword = $this->passwordEncoder->encodePassword($user, $password);
            $user->setUsername($username);
            $user->setPassword($encodedPassword);

            $isValid = $this->validator->validate($user);
            if ($isValid) {
                $this->repository->save($user);

                $this->user = $user;
                return true;
            } else {
                $errors = $this->validator->getErrors();
            }
        }

        $this->errors = $errors;
        return false;
    }

    /**
     * @param User $user
     */
    public function update(User $user): void
    {
        try {
            $this->repository->save($user);
        } catch (OptimisticLockException | ORMException $e) {
        }
    }
}
