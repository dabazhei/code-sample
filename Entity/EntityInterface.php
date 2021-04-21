<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * Interface EntityInterface
 * @package App\Entity
 */
interface EntityInterface
{
    /**
     * @param array $array
     * @return $this
     */
    public function fromArray(array $array): self;
}