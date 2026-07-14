<?php

declare(strict_types=1);

/**
 * =============================================================================
 * POLISLOT API STRESS TEST MAPPING
 * =============================================================================
 *
 * Dokumen referensi mapping seluruh API endpoint untuk stress testing.
 * Dihasilkan berdasarkan analisis graphify dan routes/api.php.
 *
 * Cara Menjalankan:
 *   ./vendor/bin/pest --testsuite=Stress
 *   ./vendor/bin/pest stress http://localhost:8000/api/info-board --concurrency=50 --duration=30
 *
 * Konfigurasi Base URL:
 *   Set env STRESS_BASE_URL di .env.testing atau phpunit.xml
 *   Default: http://localhost:8000
 *
 * =============================================================================
 * ENDPOINT MAP
 * =============================================================================
 *
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ GRUP: IoT Device (Public — throttle:500,1)                              │
 * │ Middleware: throttle:500,1                                              │
 * │ Prioritas Stress: TINGGI (device mengirim data terus-menerus)           │
 * ├──────────────────────────────┬──────────┬──────────────────────────────┤
 * │ Endpoint                     │ Method   │ Controller@Action            │
 * ├──────────────────────────────┼──────────┼──────────────────────────────┤
 * │ /api/iot/detection           │ POST     │ IotDetectionController@      │
 * │                              │          │   receiveDetection           │
 * │ /api/iot/ws-auth             │ POST     │ IotWsAuthController@         │
 * │                              │          │   authenticate               │
 * │ /api/iot/snapshot            │ POST     │ IotDetectionController@      │
 * │                              │          │   receiveSnapshot            │
 * │ /api/iot/count               │ POST     │ IotDetectionController@      │
 * │                              │          │   receiveCount               │
 * │ /api/iot/config              │ POST     │ IotDetectionController@      │
 * │                              │          │   receiveConfigQuery         │
 * │ /api/iot/webhook             │ POST     │ IotWebhookController@handle  │
 * └──────────────────────────────┴──────────┴──────────────────────────────┘
 *
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ GRUP: Autentikasi (Public — encryptApi + throttle:api)                  │
 * │ Middleware: encryptApi, throttle:api                                    │
 * │ Prioritas Stress: SEDANG                                               │
 * ├──────────────────────────────┬──────────┬──────────────────────────────┤
 * │ Endpoint                     │ Method   │ Controller@Action            │
 * ├──────────────────────────────┼──────────┼──────────────────────────────┤
 * │ /api/login-attempt           │ POST     │ AuthController@login         │
 * │ /api/register-attempt        │ POST     │ AuthController@register      │
 * │ /api/register-otp-verify     │ POST     │ AuthController@              │
 * │                              │          │   registerOtpVerify          │
 * │ /api/register-otp-resend     │ POST     │ AuthController@              │
 * │                              │          │   registerOtpResend          │
 * │ /api/forgot-attempt          │ POST     │ AuthController@              │
 * │                              │          │   forgotPasswordVerify       │
 * │ /api/forgot-otp-verify       │ POST     │ AuthController@              │
 * │                              │          │   forgotPasswordOtpVerify    │
 * │ /api/forgot-otp-resend       │ POST     │ AuthController@              │
 * │                              │          │   forgotPasswordOtpResend    │
 * │ /api/reset-pass-attempt      │ POST     │ AuthController@resetPassword │
 * └──────────────────────────────┴──────────┴──────────────────────────────┘
 *
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ GRUP: Protected (auth:sanctum + role:admin,user + encryptApi)           │
 * │ Middleware: encryptApi, throttle:api, auth:sanctum, role:admin,user     │
 * │ Prioritas Stress: TINGGI (endpoint yang paling sering diakses user)    │
 * ├──────────────────────────────┬──────────┬──────────────────────────────┤
 * │ Endpoint                     │ Method   │ Controller@Action            │
 * ├──────────────────────────────┼──────────┼──────────────────────────────┤
 * │ /api/user                    │ GET      │ AuthController@authCheck     │
 * │ /api/logout                  │ POST     │ AuthController@logout        │
 * │ /api/profile                 │ GET      │ ProfileController@show       │
 * │ /api/profile                 │ PUT/POST │ ProfileController@update     │
 * │ /api/info-board              │ GET      │ InfoBoardController@index    │
 * │ /api/missions                │ GET      │ MissionController@index      │
 * │ /api/feedback-categories     │ GET      │ FeedbackCategoryController@  │
 * │                              │          │   index                      │
 * │ /api/feedback                │ POST     │ FeedbackController@store     │
 * │ /api/rewards                 │ GET      │ RewardController@index       │
 * │ /api/rewards/redeem          │ POST     │ RewardController@redeem      │
 * │ /api/rewards/history         │ GET      │ RewardController@history     │
 * │ /api/history                 │ GET      │ HistoryController@index      │
 * │ /api/map-visualization       │ GET      │ MapVisualizationController@  │
 * │                              │          │   index                      │
 * │ /api/map-visualization/{id}  │ GET      │ MapVisualizationController@  │
 * │                              │          │   show                       │
 * │ /api/validation              │ POST     │ UserValidationController@    │
 * │                              │          │   store                      │
 * │ /api/comment                 │ GET      │ SubareaCommentController@    │
 * │                              │          │   index                      │
 * │ /api/comment                 │ POST     │ SubareaCommentController@    │
 * │                              │          │   store                      │
 * │ /api/comment/{id}            │ DELETE   │ SubareaCommentController@    │
 * │                              │          │   destroy                    │
 * │ /api/comment/{id}            │ PUT/POST │ SubareaCommentController@    │
 * │                              │          │   update                     │
 * │ /api/user-faq                │ GET      │ UserFaqController@index      │
 * └──────────────────────────────┴──────────┴──────────────────────────────┘
 *
 * =============================================================================
 * STRATEGI STRESS TEST
 * =============================================================================
 *
 * Level 1 - Smoke (ringan):
 *   concurrency: 5, duration: 5s
 *   Tujuan: verifikasi endpoint tidak error di bawah beban ringan
 *
 * Level 2 - Load (standar):
 *   concurrency: 20, duration: 10s
 *   Tujuan: simulasi penggunaan normal
 *
 * Level 3 - Stress (tinggi):
 *   concurrency: 50, duration: 30s
 *   Tujuan: cari titik batas dan bottleneck
 *
 * Level 4 - Spike (IoT):
 *   concurrency: 100, duration: 10s
 *   Tujuan: simulasi banyak IoT device mengirim data bersamaan
 *
 * =============================================================================
 * CATATAN MIDDLEWARE
 * =============================================================================
 *
 * - Endpoint `encryptApi` membutuhkan RSA/AES encryption via header X-Session-Key.
 *   Untuk stress test, gunakan opsi WithoutMiddleware atau bypass encryption
 *   di environment testing.
 * - Endpoint `auth:sanctum` membutuhkan Bearer token valid.
 *   Generate token terlebih dahulu sebelum menjalankan stress test authenticated.
 * - Endpoint IoT menggunakan throttle:500,1 (500 req/menit).
 *   Pastikan throttle sesuai skenario test.
 */
