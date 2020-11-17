<?php

namespace Probance\M2connector\Api\Data;

interface LogInterface
{
    public function getId();

    public function setId($id);

    public function getFilename();

    public function setFilename($filename);

    public function getErrors();

    public function setErrors($errors);

    public function getCreatedAt();

    public function setCreatedAt($date);
}