<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Base Eloquent repository. Domain repositories extend this class.
 */
abstract class EloquentRepository implements RepositoryInterface
{
    /**
     * @return class-string<Model>
     */
    abstract protected function model(): string;

    protected function newModelQuery()
    {
        $model = $this->model();

        return $model::query();
    }
}
