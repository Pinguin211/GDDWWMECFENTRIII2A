<?php

namespace App\Interface;

interface LocationInterface
{
    public function getId(): ?int;
    public function getFullName(): string;
    public function getName(): ?string;
}