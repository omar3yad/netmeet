<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialLoginSettingRequest extends FormRequest
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
            'GOOGLE_SOCIAL_LOGIN' => 'required|string|in:enabled,disabled',
            'GOOGLE_CLIENT_ID' => 'required_if:GOOGLE_SOCIAL_LOGIN,enabled',
            'GOOGLE_CLIENT_SECRET' => 'required_if:GOOGLE_SOCIAL_LOGIN,enabled',

            'FACEBOOK_SOCIAL_LOGIN' => 'required|string|in:enabled,disabled',
            'FACEBOOK_CLIENT_ID' => 'required_if:FACEBOOK_SOCIAL_LOGIN,enabled',
            'FACEBOOK_CLIENT_SECRET' => 'required_if:FACEBOOK_SOCIAL_LOGIN,enabled',

            'LINKEDIN_SOCIAL_LOGIN' => 'required|string|in:enabled,disabled',
            'LINKEDIN_CLIENT_ID' => 'required_if:LINKEDIN_SOCIAL_LOGIN,enabled',
            'LINKEDIN_CLIENT_SECRET' => 'required_if:LINKEDIN_SOCIAL_LOGIN,enabled',

            'TWITTER_SOCIAL_LOGIN' => 'required|string|in:enabled,disabled',
            'TWITTER_CLIENT_ID' => 'required_if:TWITTER_SOCIAL_LOGIN,enabled',
            'TWITTER_CLIENT_SECRET' => 'required_if:TWITTER_SOCIAL_LOGIN,enabled',
        ];
    }

    public function attributes()
    {
        return [
            'GOOGLE_SOCIAL_LOGIN' => __('Google Social'),
            'GOOGLE_CLIENT_ID' => __('Google Client ID'),
            'GOOGLE_CLIENT_SECRET' => __('Google Client Secret'),

            'FACEBOOK_SOCIAL_LOGIN' => __('Facebook Social'),
            'FACEBOOK_CLIENT_ID' => __('Facebook Client ID'),
            'FACEBOOK_CLIENT_SECRET' => __('Facebook Client Secret'),

            'LINKEDIN_SOCIAL_LOGIN' => __('Linkedin Social'),
            'LINKEDIN_CLIENT_ID' => __('Linkedin Client ID'),
            'LINKEDIN_CLIENT_SECRET' => __('Linkedin Client Secret'),

            'TWITTER_SOCIAL_LOGIN' => __('Twitter Social'),
            'TWITTER_CLIENT_ID' => __('Twitter Client ID'),
            'TWITTER_CLIENT_SECRET' => __('Twitter Client Secret'),
        ];
    }
}