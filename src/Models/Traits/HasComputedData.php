<?php

namespace ExpertShipping\Spl\Models\Traits;

trait HasComputedData
{

    public function __construct()
    {
        parent::__construct();
        $this->casts = array_merge($this->getCasts(), [
            'computed_data' => 'array',
        ]);
    }

    /**
     * Get a value from the computed_data JSON column.
     *
     * @param string $key
     * @return mixed|null
     */
    public function getComputedData($key)
    {
        return $this->computed_data[$key] ?? null;
    }

    /**
     * Update a value in the computed_data JSON column and save the model.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setComputedData($key, $value)
    {
        $computedData = $this->computed_data ?? [];
        $computedData[$key] = $value;
        $this->computed_data = $computedData;
        $this->saveQuietly();
    }

    /**
     * Check if a specific key exists in the computed_data JSON column.
     *
     * @param string $key
     * @return bool
     */
    public function hasComputedData($key)
    {
        return isset($this->computed_data[$key]) && $this->computed_data[$key] !== null;
    }

    public function setComputedDataForKeys(array $computedKeys)
    {
        foreach ($computedKeys as $key => $callbackMethod) {
            if ($this->isDirty($key) && method_exists($this, $callbackMethod)) {
                $this->{$callbackMethod}();
            }
        }
    }

}
