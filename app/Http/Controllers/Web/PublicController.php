<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function privacyPolicy()
    {
        $settings = $this->getSchoolSettings();

        return view('privacy-policy', compact('settings'));
    }

    protected function getSchoolSettings(): array
    {
        try {
            $settings = \Modules\Core\Entities\Setting::whereIn('key', [
                'school_name', 'school_email', 'school_phone', 'school_address',
                'email', 'phone', 'address',
            ])->pluck('value', 'key')->toArray();

            return [
                'name'    => $settings['school_name'] ?? config('app.name', 'Smart School'),
                'email'   => $settings['school_email'] ?? $settings['email'] ?? 'info@school.com',
                'phone'   => $settings['school_phone'] ?? $settings['phone'] ?? '+000000000',
                'address' => $settings['school_address'] ?? $settings['address'] ?? '',
            ];
        } catch (\Exception $e) {
            return [
                'name'    => config('app.name', 'Smart School'),
                'email'   => 'info@school.com',
                'phone'   => '+000000000',
                'address' => '',
            ];
        }
    }
}
