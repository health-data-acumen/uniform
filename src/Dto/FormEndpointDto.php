<?php

namespace App\Dto;

readonly class FormEndpointDto
{
    public function __construct(
        public string $id,
        public string $name,
        public bool $isActive,
        public int $submissionsCount,
    ) {
    }
}
