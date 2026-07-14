# Graph Report - polislot-admin-dashboard  (2026-07-14)

## Corpus Check
- 240 files · ~87,026 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1314 nodes · 2559 edges · 195 communities (142 shown, 53 thin omitted)
- Extraction: 82% EXTRACTED · 18% INFERRED · 0% AMBIGUOUS · INFERRED: 473 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `a80ecda7`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Auth Controller & Login Flow
- IoT Detection Controller
- IoT Broadcast Events
- Mission Controller & Service
- Feedback Module
- Backup & DB Commands
- Auth Controller Tests (EN)
- Deployment & Infrastructure
- Subarea Comment & Amenity Tests
- Auth Controller Tests (ID)
- Feedback/History/Mission Tests
- Parking Detector WebSocket
- Parking Detector WS Preview
- InfoBoard & IoT WS Auth Tests
- User Validation Geofence Tests
- Parking Detector MQTT
- Parking Detector MQTT Preview
- FeedbackCategory & InfoBoard Controllers
- Frontend Dependencies (npm)
- User FAQ Module
- IoT Device Model
- Controller Tests (Map/FAQ)
- User Model
- Park Subarea Model
- Profile Controller Tests (EN)
- Mission Controller Validation
- Feedback Category & Amenity Models
- Profile Controller Tests (ID)
- IoT Detection Tests
- User Validation Controller
- Park Subarea Controller
- Mission & History Models
- Composer Project Metadata
- PHP Dependencies (composer)
- Profile Controller
- Community 35
- Community 36
- Community 37
- Community 38
- Community 39
- Community 40
- Community 41
- Community 42
- Community 43
- Community 44
- Community 45
- Community 46
- Community 49
- Community 50
- Community 51
- Community 52
- Community 53
- Community 54
- Community 55
- Community 56
- Community 57
- Community 58
- Community 59
- Community 66
- Community 68
- Community 74
- Community 76
- Community 77
- Community 98
- Community 99
- Community 100
- Community 101
- Community 102
- Community 103
- Community 104
- Community 106
- Community 107
- Community 108
- Community 109
- Community 110
- Community 111
- Community 112
- Community 113
- Community 114
- Community 115
- Community 116
- Community 117
- Community 118
- Community 119
- Community 123
- Community 126
- Community 135
- Community 136
- Community 137
- Community 138
- Community 139
- Community 140
- Community 141
- Community 142
- Community 143
- Community 144
- Community 145
- Community 146
- Community 147
- Community 148
- Community 153
- Community 154
- Community 156
- Community 157
- Community 158
- Community 161
- Community 162
- Community 178
- Community 180
- Community 182
- Community 183

## God Nodes (most connected - your core abstractions)
1. `User` - 161 edges
2. `TestCase` - 92 edges
3. `ParkSubarea` - 70 edges
4. `Controller` - 68 edges
5. `ParkArea` - 50 edges
6. `IotDevice` - 34 edges
7. `AuthControllerTest` - 34 edges
8. `Mission` - 33 edges
9. `AuthControllerTest` - 26 edges
10. `Validation` - 25 edges

## Surprising Connections (you probably didn't know these)
- `Robots Disallow-All Policy` --conceptually_related_to--> `Production Security Checklist`  [INFERRED]
  public/robots.txt → docs/PROJECT_SUMMARY.md
- `main()` --indirect_call--> `start_websocket_thread()`  [INFERRED]
  python/parking_detector_ws.py → python/parking_detector_ws_preview.py
- `AuthController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/AuthController.php → app/Http/Controllers/Controller.php
- `FeedbackController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/FeedbackController.php → app/Http/Controllers/Controller.php
- `HistoryController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/HistoryController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Docker Compose Service Stack** — docker_docker_compose_app_service, docker_docker_compose_db_service, docker_docker_compose_scheduler_service, docker_docker_compose_logrotate_service, docker_docker_compose_mosquitto_service [EXTRACTED 0.90]
- **IoT Parking Detection Pipeline** — docs_project_summary_edge_iot_parking_detector, docker_docker_compose_mosquitto_service, docs_project_summary_hmac_shared_secret, python_requirements_python_iot_dependencies [INFERRED 0.80]
- **PoliSlot Three-Component Architecture** — docker_docker_compose_app_service, docs_project_summary_mobile_app_flutter, docs_project_summary_edge_iot_parking_detector [INFERRED 0.85]

## Communities (195 total, 53 thin omitted)

### Community 1 - "IoT Detection Controller"
Cohesion: 0.05
Nodes (26): MqttListenerCommand, error(), logInfo(), AuthController, JsonResponse, Request, IotDetectionController, JsonResponse (+18 more)

### Community 2 - "IoT Broadcast Events"
Cohesion: 0.07
Nodes (21): IotCommandSent, IotCountUpdated, IotDetectionReceived, IotDeviceStatusChanged, IotThresholdUpdated, SubareaStatusUpdated, IotWebhookController, JsonResponse (+13 more)

### Community 4 - "Feedback Module"
Cohesion: 0.07
Nodes (18): FeedbackCategoryController, RedirectResponse, Request, FeedbackController, RedirectResponse, Request, Feedback, BelongsTo (+10 more)

### Community 5 - "Backup & DB Commands"
Cohesion: 0.19
Nodes (9): BackupAuto, BackupClean, BackupDatabase, DbList, DbRestore, SetupDatabaseAdmin, SetupDatabaseUser, Command (+1 more)

### Community 6 - "Auth Controller Tests (EN)"
Cohesion: 0.39
Nodes (4): DashboardController, JsonResponse, Request, View

### Community 7 - "Deployment & Infrastructure"
Cohesion: 0.29
Nodes (7): 🧰 Custom Utility Commands, 🚀 Fitur Utama, 📖 Panduan Instalasi, Polislot Admin Dashboard, Prerequisites External Service, 📂 Struktur Direktori, 🛠️ Tech Stack

### Community 8 - "Subarea Comment & Amenity Tests"
Cohesion: 0.31
Nodes (3): BelongsTo, UserHistory, UserHistoryTest

### Community 10 - "Feedback/History/Mission Tests"
Cohesion: 0.36
Nodes (3): ParkAmenity, BelongsTo, ParkAmenityTest

### Community 11 - "Parking Detector WebSocket"
Cohesion: 0.19
Nodes (15): CameraStream, detector_loop(), encrypt_image_aes(), fetch_remote_config(), generate_auth_signature(), generate_hmac_signature(), get_aes_key(), handle_command() (+7 more)

### Community 12 - "Parking Detector WS Preview"
Cohesion: 0.19
Nodes (15): CameraStream, encrypt_image_aes(), fetch_remote_config(), generate_auth_signature(), generate_hmac_signature(), get_aes_key(), handle_command(), is_bbox_in_any_polygon() (+7 more)

### Community 13 - "InfoBoard & IoT WS Auth Tests"
Cohesion: 0.13
Nodes (5): BaseTestCase, ExampleTest, IotWsAuthControllerTest, TestCase, ExampleTest

### Community 14 - "User Validation Geofence Tests"
Cohesion: 0.20
Nodes (6): Instalasi dengan Docker, Khusus Pengguna Linux, Persiapan File Konfigurasi, Prasyarat, Instalasi Manual (Tanpa Docker), Prasyarat

### Community 15 - "Parking Detector MQTT"
Cohesion: 0.19
Nodes (15): CameraStream, encrypt_image_aes(), generate_hmac_signature(), get_aes_key(), is_bbox_in_any_polygon(), is_bbox_in_polygon(), load_local_config(), main() (+7 more)

### Community 16 - "Parking Detector MQTT Preview"
Cohesion: 0.19
Nodes (15): CameraStream, encrypt_image_aes(), generate_hmac_signature(), get_aes_key(), is_bbox_in_any_polygon(), is_bbox_in_polygon(), load_local_config(), main() (+7 more)

### Community 17 - "FeedbackCategory & InfoBoard Controllers"
Cohesion: 0.17
Nodes (9): FeedbackCategoryController, JsonResponse, InfoBoardController, JsonResponse, Controller, JsonResponse, AuthorizesRequests, BaseController (+1 more)

### Community 18 - "Frontend Dependencies (npm)"
Cohesion: 0.08
Nodes (25): axios, concurrently, laravel-echo, laravel-vite-plugin, devDependencies, axios, concurrently, laravel-echo (+17 more)

### Community 19 - "User FAQ Module"
Cohesion: 0.21
Nodes (7): JsonResponse, UserFaqController, RedirectResponse, Request, UserFaqController, BelongsTo, UserFaq

### Community 20 - "IoT Device Model"
Cohesion: 0.22
Nodes (9): 10. Menjalankan Service Aplikasi, 1. Konfigurasi Environment (.env), 2. Generate RSA Keys (Wajib), 3. Instalasi Dependency, 4. Generate Application Key, 5. Migrasi Database & Seeding, 6. Setup Database Roles (RBAC), 7. Atur Ulang Environment (.env) - PENTING (+1 more)

### Community 21 - "Controller Tests (Map/FAQ)"
Cohesion: 0.17
Nodes (8): RefreshDatabase, FeedbackCategoryControllerTest, FeedbackControllerTest, HistoryControllerTest, InfoBoardControllerTest, MapVisualizationControllerTest, UserFaqControllerTest, WithoutMiddleware

### Community 22 - "User Model"
Cohesion: 0.12
Nodes (6): User, Authenticatable, HasApiTokens, Notifiable, ProfileControllerTest, UserTest

### Community 23 - "Park Subarea Model"
Cohesion: 0.13
Nodes (6): ParkSubarea, BelongsTo, HasMany, HasOne, SubareaCommentControllerTest, ParkSubareaControllerTest

### Community 25 - "Mission Controller Validation"
Cohesion: 0.42
Nodes (4): MissionController, RedirectResponse, Request, ValidationException

### Community 26 - "Feedback Category & Amenity Models"
Cohesion: 0.18
Nodes (4): ParkArea, HasMany, ParkAreaTest, ParkSubareaTest

### Community 28 - "IoT Detection Tests"
Cohesion: 0.33
Nodes (5): 1. Import (Namespace / `use` Statement), 2. PHPDoc (Komentar Fungsi & Class), 3. Konvensi Penulisan Model (Eloquent), 4. Best Practices Laravel Lainnya, Aturan Standar Penulisan Kode PHP & Laravel (Coding Standards)

### Community 29 - "User Validation Controller"
Cohesion: 0.33
Nodes (6): 1. Salin Folder `python/` ke Perangkat Edge, 2. Buat Virtual Environment (Disarankan), 3. Install Dependensi, 4. Konfigurasi File `.env`, Format Model yang Didukung (`YOLO_WEIGHTS`), 🚀 Instalasi

### Community 30 - "Park Subarea Controller"
Cohesion: 0.33
Nodes (6): 🗂️ Caching Konfigurasi Lokal, 📋 Daftar Script, 🔐 Keamanan, 🌐 Koneksi Melalui Cloudflare Tunnel (Produksi), ⚙️ Persyaratan Sistem, 🚗 PoliSlot — Perangkat Edge IoT Deteksi Parkir

### Community 31 - "Mission & History Models"
Cohesion: 0.08
Nodes (12): MissionController, JsonResponse, Mission, HasMany, BelongsTo, UserMission, HasFactory, MissionControllerTest (+4 more)

### Community 32 - "Composer Project Metadata"
Cohesion: 0.18
Nodes (10): description, extra, laravel, dont-discover, license, minimum-stability, name, prefer-stable (+2 more)

### Community 33 - "PHP Dependencies (composer)"
Cohesion: 0.22
Nodes (9): require, fakerphp/faker, laravel/framework, laravel/reverb, laravel/sanctum, laravel/tinker, php, php-mqtt/laravel-client (+1 more)

### Community 34 - "Profile Controller"
Cohesion: 0.10
Nodes (11): JsonResponse, Request, ProfileController, JsonResponse, Request, UserValidationController, NotCurrentPassword, Closure (+3 more)

### Community 35 - "Community 35"
Cohesion: 0.06
Nodes (30): 1. Deployment Architecture, 2.1. Backend Server & Admin Dashboard (Via Docker - Recommended), 2.2. Backend Server (Instalasi Manual Lokal / XAMPP), 2.3. PoliSlot Mobile App (Flutter), 2.4. Perangkat Edge IoT (Parking Detector), 2. Installation Procedure, 3. Security Checklist, API & Komunikasi Data (+22 more)

### Community 36 - "Community 36"
Cohesion: 0.17
Nodes (12): 10. Re-up Container, 11. Buat port forward local dan firewall rule - Optional untuk instalasi pada WSL, 1. Konfigurasi Environment (.env), 2. Generate RSA Keys (Di Root), 3. Verifikasi Credential, 4. Menjalankan Container, 5. Generate Application Key, 6. Migrasi Database (+4 more)

### Community 37 - "Community 37"
Cohesion: 0.53
Nodes (3): ParkAmenityController, JsonResponse, Request

### Community 38 - "Community 38"
Cohesion: 0.33
Nodes (6): Contoh — MQTT dengan Preview, Contoh — MQTT Headless, Contoh — WebSocket dengan Preview, Contoh — WebSocket Headless, ▶️ Menjalankan Script, Sintaks

### Community 39 - "Community 39"
Cohesion: 0.08
Nodes (14): JsonResponse, Request, RewardController, RedirectResponse, Request, RewardController, HasMany, Reward (+6 more)

### Community 41 - "Community 41"
Cohesion: 0.24
Nodes (3): CustomizeFormatter, ScrubAndTraceProcessor, ProcessorInterface

### Community 42 - "Community 42"
Cohesion: 0.31
Nodes (3): AppServiceProvider, AuthServiceProvider, ServiceProvider

### Community 43 - "Community 43"
Cohesion: 0.43
Nodes (4): RedirectResponse, Request, View, ProfileController

### Community 44 - "Community 44"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 45 - "Community 45"
Cohesion: 0.29
Nodes (7): require-dev, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, phpunit/phpunit

### Community 46 - "Community 46"
Cohesion: 0.14
Nodes (14): scripts, dev, post-autoload-dump, post-root-package-install, post-update-cmd, test, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump (+6 more)

### Community 51 - "Community 51"
Cohesion: 0.53
Nodes (3): MapVisualizationController, JsonResponse, Request

### Community 52 - "Community 52"
Cohesion: 0.53
Nodes (3): RedirectResponse, Request, ValidationController

### Community 56 - "Community 56"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 57 - "Community 57"
Cohesion: 0.33
Nodes (6): 8. Konfigurasi Manual MQTT Broker (Mosquitto), A. Instalasi Mosquitto, B. Konfigurasi `mosquitto.conf`, C. Membuat File Password & Kredensial User, D. Membuat File ACL (Access Control List), E. Menjalankan Service Mosquitto

### Community 58 - "Community 58"
Cohesion: 0.67
Nodes (3): keywords, framework, laravel

### Community 59 - "Community 59"
Cohesion: 0.60
Nodes (3): FeedbackController, JsonResponse, Request

### Community 66 - "Community 66"
Cohesion: 0.13
Nodes (6): BelongsTo, UserValidation, Validation, UserValidationControllerTest, UserValidationTest, ValidationTest

### Community 68 - "Community 68"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 74 - "Community 74"
Cohesion: 0.31
Nodes (4): ParkAreaController, RedirectResponse, Request, View

### Community 76 - "Community 76"
Cohesion: 0.13
Nodes (10): ApiEncryption, Closure, Request, Closure, Request, RBAC, TrustProxies, Middleware (+2 more)

### Community 77 - "Community 77"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 158 - "Community 158"
Cohesion: 0.25
Nodes (6): HistoryController, JsonResponse, Request, IotWsAuthController, JsonResponse, Request

### Community 161 - "Community 161"
Cohesion: 0.31
Nodes (3): RedirectResponse, Request, RewardVerificationController

### Community 178 - "Community 178"
Cohesion: 0.50
Nodes (4): 9. Mengatasi Masalah Koneksi Broker Satu Jaringan (Troubleshooting), A. Konfigurasi IP Host & Port (.env), B. Windows Defender Firewall (Sering Menjadi Penyebab Utama), C. Isolasi Jaringan Router (AP Isolation)

### Community 180 - "Community 180"
Cohesion: 0.39
Nodes (4): ParkSubareaController, JsonResponse, RedirectResponse, Request

### Community 182 - "Community 182"
Cohesion: 0.23
Nodes (6): JsonResponse, Request, SubareaCommentController, BelongsTo, SubareaComment, SubareaCommentTest

### Community 183 - "Community 183"
Cohesion: 0.14
Nodes (7): InfoBoardController, RedirectResponse, Request, InfoBoard, BelongsTo, InfoBoardControllerTest, InfoBoardTest

## Knowledge Gaps
- **175 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+170 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **53 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User Model` to `Auth Controller & Login Flow`, `IoT Detection Controller`, `Mission Controller & Service`, `Feedback Module`, `Auth Controller Tests (EN)`, `Subarea Comment & Amenity Tests`, `Auth Controller Tests (ID)`, `User FAQ Module`, `Controller Tests (Map/FAQ)`, `Park Subarea Model`, `Profile Controller Tests (EN)`, `Mission & History Models`, `Profile Controller`, `Community 39`, `Community 49`, `Community 50`, `Community 54`, `Community 183`, `Community 182`, `Community 55`, `Community 66`, `Community 127`?**
  _High betweenness centrality (0.093) - this node is a cross-community bridge._
- **Why does `Controller` connect `FeedbackCategory & InfoBoard Controllers` to `IoT Detection Controller`, `IoT Broadcast Events`, `Feedback Module`, `Auth Controller Tests (EN)`, `User FAQ Module`, `Mission Controller Validation`, `Community 158`, `Mission & History Models`, `Community 161`, `Profile Controller`, `Community 37`, `Community 39`, `Community 43`, `Community 51`, `Community 180`, `Community 52`, `Community 182`, `Community 183`, `Community 59`, `Community 74`?**
  _High betweenness centrality (0.044) - this node is a cross-community bridge._
- **Why does `TestCase` connect `InfoBoard & IoT WS Auth Tests` to `Auth Controller & Login Flow`, `IoT Detection Controller`, `Mission Controller & Service`, `Feedback Module`, `Subarea Comment & Amenity Tests`, `Auth Controller Tests (ID)`, `Feedback/History/Mission Tests`, `Controller Tests (Map/FAQ)`, `User Model`, `Park Subarea Model`, `Feedback Category & Amenity Models`, `Profile Controller Tests (ID)`, `Mission & History Models`, `Community 39`, `Community 47`, `Community 48`, `Community 49`, `Community 50`, `Community 54`, `Community 183`, `Community 182`, `Community 55`, `Community 66`, `Community 76`, `Community 123`, `Community 127`?**
  _High betweenness centrality (0.041) - this node is a cross-community bridge._
- **Are the 149 inferred relationships involving `User` (e.g. with `.forgotPasswordOtpResend()` and `.forgotPasswordOtpVerify()`) actually correct?**
  _`User` has 149 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _175 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Auth Controller & Login Flow` be split into smaller, more focused modules?**
  _Cohesion score 0.06451612903225806 - nodes in this community are weakly interconnected._
- **Should `IoT Detection Controller` be split into smaller, more focused modules?**
  _Cohesion score 0.05128205128205128 - nodes in this community are weakly interconnected._