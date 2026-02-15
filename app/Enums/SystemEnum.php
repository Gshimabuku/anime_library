<?php

namespace App\Enums;

enum SystemEnum: string
{
    case CLOUDINARY_CLOUD_NAME = 'cloudinary_cloud_name';
    case CLOUDINARY_API_KEY = 'cloudinary_api_key';
    case CLOUDINARY_API_SECRET = 'cloudinary_api_secret';
    case CLOUDINARY_FOLDER = 'cloudinary_folder';
}
