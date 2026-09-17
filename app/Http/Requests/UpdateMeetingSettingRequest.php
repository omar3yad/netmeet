<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeetingSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'SIGNALING_URL' => 'required|url|max:50',
            'MODERATOR_RIGHTS' => 'required|string|in:enabled,disabled',
            'DEFAULT_USERNAME' => 'required|string|max:15',
            'END_URL' => 'required|string|max:255',
            'LIMITED_SCREEN_SHARE' => 'required|string|in:enabled,disabled',
            'STUN_URL' => 'required|string|starts_with:stun:|max:50',
            'TURN_URL' => 'required|string|starts_with:turn:|max:50',
            'TURN_USERNAME' => 'required|string|max:50',
            'TURN_PASSWORD' => 'required|string|max:50',
        ];
    }

    public function attributes()
    {
        return [
            'SIGNALING_URL' => __('Signaling URL'),
            'MODERATOR_RIGHTS' => __('Moderator Rights'),
            'DEFAULT_USERNAME' => __('Default Username'),
            'END_URL' => __('End URL'),
            'LIMITED_SCREEN_SHARE' => __('Limited Screen Sharing'),
            'STUN_URL' => __('STUN URL'),
            'TURN_URL' => __('TURN URL'),
            'TURN_USERNAME' => __('TURN Username'),
            'TURN_PASSWORD' => __('TURN Password'),
        ];
    }
}
