<?php

namespace App\Models;

use App\Http\Requests\ApiRequest;

/**
 * Trait ApiRequestFillable
 *
 * @package App\Models
 */
trait ApiRequestFillable
{
    /**
     * Fill model with request validated data.
     *
     * @param \App\Http\Requests\ApiRequest $request
     *
     * @throws \Throwable
     */
    public function fillFromRequest(ApiRequest $request)
    {
        $this->fillable($request->getFillableFields());
        $this->fill(array_intersect_key($request->validated(), $this->getAttributes()));
        $this->saveOrFail();
    }
}
