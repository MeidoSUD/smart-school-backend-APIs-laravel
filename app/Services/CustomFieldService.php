<?php

namespace App\Services;

use Modules\Core\Entities\CustomField;
use Modules\Core\Entities\CustomFieldValue;

class CustomFieldService
{
    public function getFields(string $formType): \Illuminate\Support\Collection
    {
        return CustomField::where('form_type', $formType)
            ->where('is_active', 'yes')
            ->get();
    }

    public function getValues(string $belongTo, int $belongId): \Illuminate\Support\Collection
    {
        return CustomFieldValue::where('belong_to', $belongTo)
            ->where('belong_id', $belongId)
            ->get()
            ->keyBy('custom_fields_id');
    }

    public function saveValues(string $belongTo, int $belongId, array $values): void
    {
        foreach ($values as $fieldId => $value) {
            CustomFieldValue::updateOrCreate(
                [
                    'custom_fields_id' => $fieldId,
                    'belong_to' => $belongTo,
                    'belong_id' => $belongId,
                ],
                ['value' => $value]
            );
        }
    }

    public function deleteValues(string $belongTo, int $belongId): void
    {
        CustomFieldValue::where('belong_to', $belongTo)
            ->where('belong_id', $belongId)
            ->delete();
    }
}
