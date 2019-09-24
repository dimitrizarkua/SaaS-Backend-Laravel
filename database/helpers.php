<?php

if (!function_exists('randomOrCreate')) {
    /**
     * Select random model or create if last one doesn't exists.
     *
     * @param  string $modelClassName
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    function randomOrCreate(string $modelClassName)
    {
        $entities = call_user_func([$modelClassName, 'all']);
        if (false === $entities->isEmpty()) {
            return $entities->random();
        }

        return factory($modelClassName)->create();
    }
}
