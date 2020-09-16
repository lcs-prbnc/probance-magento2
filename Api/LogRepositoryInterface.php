<?php

namespace Walkwizus\Probance\Api;

use Walkwizus\Probance\Api\Data\LogInterface;

interface LogRepositoryInterface
{
    public function save(LogInterface $log);

    public function getById($id);

    public function delete(LogInterface $log);

    public function deleteById($id);
}