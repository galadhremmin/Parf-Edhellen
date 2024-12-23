<?php

namespace App\Http\Controllers\Contributions;

use App\Models\Contribution;

class ViewModel
{
    public Contribution $contribution;

    public string $model;

    public string $viewName;

    public function __construct(Contribution $contribution, string $viewName, array $model)
    {
        $this->contribution = $contribution;
        $this->model = $model;
        $this->viewName = $viewName;
    }

    public function toModelArray()
    {
        return [
            'contribution' => $this->contribution,
            'model' => $this->model,
            'viewName' => $this->viewName,
        ];
    }
}
