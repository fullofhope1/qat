<?php
// includes/classes/CustomerService.php

class CustomerService extends BaseService
{
    private $repository;

    public function __construct(CustomerRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listCustomers()
    {
        return $this->repository->getAllActive();
    }

    public function getCustomer($id)
    {
        return $this->repository->getById($id);
    }

    public function addCustomer($name, $phone, $debtLimit = null)
    {
        // Validation: Name must be unique
        if ($this->repository->getByName($name)) {
            throw new Exception("الاسم موجود مسبقاً (This name already exists)");
        }

        // Validation: Phone must be unique if provided
        if (!empty($phone) && $this->repository->getByPhone($phone)) {
            throw new Exception("رقم الهاتف موجود مسبقاً (This phone number already exists)");
        }

        return $this->repository->create($name, $phone, $debtLimit);
    }

    public function updateCustomer($id, $name, $phone, $debtLimit)
    {
        $existing = $this->repository->getById($id);
        if (!$existing) {
            throw new Exception("المستخدم غير موجود (Customer not found)");
        }

        // Check name uniqueness if changed
        if ($existing['name'] !== $name) {
            if ($this->repository->getByName($name)) {
                throw new Exception("الاسم الجديد موجود مسبقاً (New name already exists)");
            }
        }

        // Check phone uniqueness if changed
        if (!empty($phone) && $existing['phone'] !== $phone) {
            if ($this->repository->getByPhone($phone)) {
                throw new Exception("رقم الهاتف الجديد موجود مسبقاً (New phone number already exists)");
            }
        }

        return $this->repository->update($id, $name, $phone, $debtLimit);
    }

    public function removeCustomer($id)
    {
        return $this->repository->delete($id);
    }
}
