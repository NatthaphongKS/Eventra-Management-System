<?php

/**
 * ชื่อไฟล์: cors.php
 * ที่อยู่: config/cors.php
 * คำอธิบาย: การตั้งค่า CORS สำหรับแอปพลิเคชัน — กำหนดต้นทางที่อนุญาต
 * ผู้เขียน/แก้ไข: Yothin S.
 * วันที่แก้ไขล่าสุด: 18 มีนาคม 2026
 */

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Keep this empty and use patterns so localhost can use any dev port.
    'allowed_origins' => [],

    'allowed_origins_patterns' => [
        '#^https?://localhost(:\d+)?$#',
        '#^https?://127\.0\.0\.1(:\d+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
