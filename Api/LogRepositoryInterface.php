<?php

namespace Probance\M2connector\Api;

use Probance\M2connector\Api\Data\LogInterface;

interface LogRepositoryInterface
{
    public function save(LogInterface $log);

    public function getById($id);

    public function delete(LogInterface $log);

    public function deleteById($id);
}