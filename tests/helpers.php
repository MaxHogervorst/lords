<?php

if (!function_exists('factory')) {
    /**
     * Create a model factory builder for a given class and amount.
     *
     * @param  string  $class
     * @param  int|null  $amount
     * @return \Illuminate\Database\Eloquent\FactoryBuilder
     */
    function factory($class, $amount = null)
    {
        $factory = app(\Illuminate\Database\Eloquent\Factory::class);

        if (isset($amount) && is_numeric($amount)) {
            return $factory->of($class)->times($amount);
        }

        return $factory->of($class);
    }
}
