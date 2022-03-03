<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class UserService
{
    /** @var EntityManagerInterface */
    private EntityManagerInterface $em;
    /**
     * @var UserRepository|ObjectRepository
     */
    private $repository;
    /**
     * @var ValidationService
     */
    private ValidationService $validator;
    /**
     * @var RoleHierarchyInterface
     */
    private RoleHierarchyInterface $roleHierarchy;
    /**
    /**
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $passwordHasher;
    /**
     * @var User
     */
    private User $user;
    /**
     * @var array
     */
    private array $errors = [];

    public function __construct(
        EntityManagerInterface $em,
        ValidationService $validator,
        UserPasswordHasherInterface $passwordHasher,
        RoleHierarchyInterface $roleHierarchy
    ) {
        $this->em = $em;
        $this->validator = $validator;
        $this->passwordHasher = $passwordHasher;
        $this->roleHierarchy = $roleHierarchy;
        $this->repository = $this->em->getRepository(User::class);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Return available app roles.
     * Useful for app role choices on user creation/edition.
     * @TODO find a way for modules to insert their own roles without edit this method each time
     *
     * @return string[]
     */
    public function getAvailableAppRoles(): array
    {
        $appRoles[User::ROLE_USER] = [
            'name' => User::ROLE_USER,
            'description' => 'Default user role. Access to main features.',
            'hierarchy' => $this->roleHierarchy->getReachableRoleNames([User::ROLE_USER])
        ];
        $appRoles[User::ROLE_ADMIN] = [
            'name' => User::ROLE_ADMIN,
            'description' => 'Administrator role. Access to everything.',
            'hierarchy' => $this->roleHierarchy->getReachableRoleNames([User::ROLE_ADMIN])
        ];

        return $appRoles;
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
     * @param User $user
     * @return bool
     * @throws ORMException
     */
    public function create(User $user): bool
    {
        $isValid = $this->validator->validate($user);
        if ($isValid) {
            $password = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $this->repository->save($user);

            $this->user = $user;
            return true;
        } else {
            $errors = $this->validator->getErrors();
        }


        $this->errors = $errors;
        return false;
    }

    /**
     * @param User $user
     * @return bool
     * @throws ORMException
     */
    public function update(User $user): bool
    {
        $userExist = $this->findOneById($user->getId());

        if ($userExist instanceof User) {
            $isValid = $this->validator->validate($user);
            if ($isValid) {
                if (!empty($user->getPlainPassword())) {
                    $password = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
                    $user->setPassword($password);
                }
                $this->repository->save($user);
                return true;
            }
        } else {
            $this->errors[] = 'user does not exist';
        }

        $this->errors = $this->validator->getErrors();
        return false;
    }


    /**
     * @param int $userId
     * @return bool
     * @throws ORMException
     */
    public function delete(int $userId): bool
    {
        $userExist = $this->findOneById($userId);

        if ($userExist instanceof User) {
            $this->repository->delete($userExist);
            return true;
        }

        return false;
    }
}
