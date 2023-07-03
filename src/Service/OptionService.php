<?php

namespace App\Service;

use App\Repository\OptionRepository;

class OptionService
{
    public function __construct(private OptionRepository $optionRepo)
    {
        
    }

    public function findAll(): array
    {
        return $this->optionRepo->findAllForTwig();
    }

    public function getValue(string $name): mixed
    {
        return $this->optionRepo->getValue($name);
    }
}