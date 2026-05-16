<?php

namespace App\Services\Export;

use Maatwebsite\Excel\Concerns\FromArray;

class ArrayExport implements FromArray
{
    protected array $data;

    public function __construct(
        array $headers,
        array $rows
    ) {
        $this->data = [
            $headers,
            ...$rows
        ];
    }

    public function array(): array
    {
        return $this->data;
    }
}
