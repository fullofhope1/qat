<?php
// includes/classes/UserService.php

class UserService extends BaseService
{
    protected $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function listUsers()
    {
        return $this->userRepo->getAllUsers();
    }

    public function addUser(array $data)
    {
        if (isset($data['role_group'])) {
            $roles = $this->extractRoleVars($data['role_group']);
            $data['role'] = $roles['role'];
            $data['sub_role'] = $roles['sub_role'];
            unset($data['role_group']);
        }

        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return $this->userRepo->create($data);
    }

    public function updateUser($id, array $data)
    {
        if (isset($data['role_group'])) {
            $roles = $this->extractRoleVars($data['role_group']);
            $data['role'] = $roles['role'];
            $data['sub_role'] = $roles['sub_role'];
            unset($data['role_group']);
        }

        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        return $this->userRepo->update($id, $data);
    }

    public function deleteUser($id)
    {
        return $this->userRepo->delete($id);
    }

    public function login($username, $password)
    {
        $user = $this->userRepo->getByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function verifyPassword($id, $password)
    {
        $user = $this->userRepo->getById($id);
        return $user && password_verify($password, $user['password']);
    }

    public function changePassword($id, $newPassword)
    {
        return $this->userRepo->update($id, ['password' => password_hash($newPassword, PASSWORD_DEFAULT)]);
    }

    public function changeUsername($id, $newUsername)
    {
        return $this->userRepo->update($id, ['username' => $newUsername]);
    }

    public function getUser($id)
    {
        return $this->userRepo->getById($id);
    }

    public function usernameExists($username)
    {
        return $this->userRepo->getByUsername($username) !== false;
    }

    protected function extractRoleVars($role_group)
    {
        if ($role_group === 'super_admin_full') {
            return ['role' => 'super_admin', 'sub_role' => 'full'];
        }
        if ($role_group === 'super_admin_verifier') {
            return ['role' => 'super_admin', 'sub_role' => 'verifier'];
        }
        if ($role_group === 'super_admin_seller') {
            return ['role' => 'super_admin', 'sub_role' => 'seller'];
        }
        if ($role_group === 'super_admin_accountant') {
            return ['role' => 'super_admin', 'sub_role' => 'accountant'];
        }
        if ($role_group === 'super_admin_partner') {
            return ['role' => 'super_admin', 'sub_role' => 'partner'];
        }
        if ($role_group === 'admin_full') {
            return ['role' => 'admin', 'sub_role' => 'full'];
        }
        return ['role' => 'user', 'sub_role' => 'full'];
    }
}
