# Graph Report - polislot-admin-dashboard  (2026-07-15)

## Corpus Check
- 250 files · ~91,003 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1341 nodes · 2595 edges · 194 communities (137 shown, 57 thin omitted)
- Extraction: 82% EXTRACTED · 18% INFERRED · 0% AMBIGUOUS · INFERRED: 473 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `81fb7cbb`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- PHP Infrastructure
- Feedback API
- Feedback Models
- Console Commands
- Core Models
- Reward System
- IoT Events
- Auth Tests
- Area Management
- User Models
- 2.1. Backend Server & Admin Dashboard (Via Docker - Recommended)
- InfoBoard
- devDependencies
- Reward
- AuthControllerTest
- Command
- ParkSubarea
- parking_detector_mqtt.py
- parking_detector_mqtt_preview.py
- parking_detector_ws.py
- parking_detector_ws_preview.py
- IotDetectionController
- ParkArea.php
- IotDevice
- .log
- scripts
- ApiEncryption.php
- UserValidationController.php
- IotDevice.php
- ParkAmenity
- ScrubAndTraceProcessor
- Langkah Instalasi
- Claude Code — Project Instructions
- composer.json
- HasFactory
- ParkSubareaController.php
- IotWebhookControllerTest.php
- static
- README.md
- ParkAreaTest
- AuthServiceProvider.php
- require
- Langkah Instalasi
- IotDetectionControllerTest
- ProfileControllerTest
- User
- config
- require-dev
- Polislot Admin Dashboard
- UserTest
- Aturan Standar Penulisan Kode PHP & Laravel (Coding Standards)
- UserHistory
- 🚀 Instalasi
- 🚗 PoliSlot — Perangkat Edge IoT Deteksi Parkir
- ▶️ Menjalankan Script
- 8. Konfigurasi Manual MQTT Broker (Mosquitto)
- DashboardControllerTest
- PoliSlotLoginTest
- psr-4
- RBACTest
- Laravel Coding Standards Enforcer
- HistoryServiceTest
- post-create-project-cmd
- 9. Mengatasi Masalah Koneksi Broker Satu Jaringan (Troubleshooting)
- autoload-dev
- extra
- environment_isolation.md
- graphify.md
- development_cycle.md
- graphify.md
- Contents.IoTDetection.partials.captures_grid
- App Service (Laravel/Reverb/Queue)
- docker-entrypoint.sh
- logrotate-entrypoint.sh
- Production Security Checklist
- copilot-instructions.md
- Consistent API Response Format
- DB Transaction & Error Handling
- Eloquent Model Convention
- Fat Model Skinny Controller
- N+1 Query Prevention (Eager Loading)
- Namespace Import Convention
- PHP & Laravel Coding Standards
- PHPDoc Documentation Standard
- Development/Runtime Environment Isolation
- Docker Runtime Read-Only Policy
- Graphify Consultation Rule
- Development Cycle Workflow
- Graphify Pipeline Workflow
- run.sh
- ParkSubarea.php
- DB Service (MariaDB)
- Logrotate Sidecar Service
- Mosquitto MQTT Broker Service
- Scheduler Service (Laravel Cron)
- Cloudflare Tunnel Service (Optional)
- Docker Installation Guide
- Manual Installation Guide
- RSA Key Generation Step
- Client-Server IoT Deployment Architecture
- Edge IoT Parking Detector (YOLOv8)
- IoT HMAC Shared Secret Validation
- Hybrid RSA/AES Payload Encryption
- PoliSlot Mobile App (Flutter)
- PoliSlot Project Summary
- Python IoT Detector Dependencies
- API Encryption (RSA Hybrid)
- Custom Artisan Utility Commands
- Database Backup & Restore Feature
- Database RBAC (Privilege Separation)

## God Nodes (most connected - your core abstractions)
1. `User` - 161 edges
2. `TestCase` - 93 edges
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
- `IotDetectionController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/IotDetectionController.php → app/Http/Controllers/Controller.php
- `IotWebhookController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/IotWebhookController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Docker Compose Service Stack** — docker_docker_compose_app_service, docker_docker_compose_db_service, docker_docker_compose_scheduler_service, docker_docker_compose_logrotate_service, docker_docker_compose_mosquitto_service [EXTRACTED 0.90]
- **IoT Parking Detection Pipeline** — docs_project_summary_edge_iot_parking_detector, docker_docker_compose_mosquitto_service, docs_project_summary_hmac_shared_secret, python_requirements_python_iot_dependencies [INFERRED 0.80]
- **PoliSlot Three-Component Architecture** — docker_docker_compose_app_service, docs_project_summary_mobile_app_flutter, docs_project_summary_edge_iot_parking_detector [INFERRED 0.85]

## Communities (194 total, 57 thin omitted)

### Community 0 - "PHP Infrastructure"
Cohesion: 0.13
Nodes (10): IotCommandSent, IotCountUpdated, IotDetectionReceived, IotDeviceStatusChanged, IotThresholdUpdated, SubareaStatusUpdated, Dispatchable, InteractsWithSockets (+2 more)

### Community 1 - "Feedback API"
Cohesion: 0.05
Nodes (31): FeedbackCategoryController, JsonResponse, FeedbackController, JsonResponse, Request, HistoryController, JsonResponse, Request (+23 more)

### Community 2 - "Feedback Models"
Cohesion: 0.12
Nodes (13): JsonResponse, Request, SubareaCommentController, JsonResponse, UserFaqController, RedirectResponse, Request, UserFaqController (+5 more)

### Community 4 - "Core Models"
Cohesion: 0.06
Nodes (18): MissionController, JsonResponse, JsonResponse, Request, ProfileController, MissionController, RedirectResponse, Request (+10 more)

### Community 5 - "Reward System"
Cohesion: 0.10
Nodes (12): BaseTestCase, RefreshDatabase, ExampleTest, FeedbackCategoryControllerTest, FeedbackControllerTest, HistoryControllerTest, InfoBoardControllerTest, MapVisualizationControllerTest (+4 more)

### Community 7 - "Auth Tests"
Cohesion: 0.07
Nodes (18): FeedbackCategoryController, RedirectResponse, Request, FeedbackController, RedirectResponse, Request, Feedback, BelongsTo (+10 more)

### Community 9 - "User Models"
Cohesion: 0.17
Nodes (8): LoginNotificationMail, Content, Envelope, Content, Envelope, SendOtpMail, Mailable, Queueable

### Community 10 - "2.1. Backend Server & Admin Dashboard (Via Docker - Recommended)"
Cohesion: 0.06
Nodes (30): 1. Deployment Architecture, 2.1. Backend Server & Admin Dashboard (Via Docker - Recommended), 2.2. Backend Server (Instalasi Manual Lokal / XAMPP), 2.3. PoliSlot Mobile App (Flutter), 2.4. Perangkat Edge IoT (Parking Detector), 2. Installation Procedure, 3. Security Checklist, API & Komunikasi Data (+22 more)

### Community 11 - "InfoBoard"
Cohesion: 0.14
Nodes (7): InfoBoardController, RedirectResponse, Request, InfoBoard, BelongsTo, InfoBoardControllerTest, InfoBoardTest

### Community 12 - "devDependencies"
Cohesion: 0.08
Nodes (25): axios, concurrently, laravel-echo, laravel-vite-plugin, devDependencies, axios, concurrently, laravel-echo (+17 more)

### Community 13 - "Reward"
Cohesion: 0.05
Nodes (22): JsonResponse, Request, RewardController, DashboardController, JsonResponse, Request, View, RedirectResponse (+14 more)

### Community 15 - "Command"
Cohesion: 0.19
Nodes (9): BackupAuto, BackupClean, BackupDatabase, DbList, DbRestore, SetupDatabaseAdmin, SetupDatabaseUser, Command (+1 more)

### Community 16 - "ParkSubarea"
Cohesion: 0.13
Nodes (9): ParkArea, ParkSubarea, UserValidation, Validation, SubareaCommentControllerTest, UserValidationControllerTest, ParkSubareaTest, SubareaCommentTest (+1 more)

### Community 17 - "parking_detector_mqtt.py"
Cohesion: 0.19
Nodes (15): CameraStream, encrypt_image_aes(), generate_hmac_signature(), get_aes_key(), is_bbox_in_any_polygon(), is_bbox_in_polygon(), load_local_config(), main() (+7 more)

### Community 18 - "parking_detector_mqtt_preview.py"
Cohesion: 0.19
Nodes (15): CameraStream, encrypt_image_aes(), generate_hmac_signature(), get_aes_key(), is_bbox_in_any_polygon(), is_bbox_in_polygon(), load_local_config(), main() (+7 more)

### Community 19 - "parking_detector_ws.py"
Cohesion: 0.19
Nodes (15): CameraStream, detector_loop(), encrypt_image_aes(), fetch_remote_config(), generate_auth_signature(), generate_hmac_signature(), get_aes_key(), handle_command() (+7 more)

### Community 20 - "parking_detector_ws_preview.py"
Cohesion: 0.19
Nodes (15): CameraStream, encrypt_image_aes(), fetch_remote_config(), generate_auth_signature(), generate_hmac_signature(), get_aes_key(), handle_command(), is_bbox_in_any_polygon() (+7 more)

### Community 21 - "IotDetectionController"
Cohesion: 0.30
Nodes (4): IotDetectionController, JsonResponse, Request, View

### Community 22 - "ParkArea.php"
Cohesion: 0.12
Nodes (3): HasMany, IotWsAuthControllerTest, ParkAreaControllerTest

### Community 23 - "IotDevice"
Cohesion: 0.26
Nodes (5): IotDetectionController, JsonResponse, Request, IotDevice, ScrubAndTraceProcessorTest

### Community 24 - ".log"
Cohesion: 0.10
Nodes (13): error(), logInfo(), AuthController, JsonResponse, Request, AuthController, RedirectResponse, Request (+5 more)

### Community 25 - "scripts"
Cohesion: 0.14
Nodes (14): scripts, dev, post-autoload-dump, post-root-package-install, post-update-cmd, test, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump (+6 more)

### Community 26 - "ApiEncryption.php"
Cohesion: 0.13
Nodes (10): ApiEncryption, Closure, Request, Closure, Request, RBAC, TrustProxies, Middleware (+2 more)

### Community 27 - "UserValidationController.php"
Cohesion: 0.21
Nodes (4): JsonResponse, Request, UserValidationController, BelongsTo

### Community 28 - "IotDevice.php"
Cohesion: 0.16
Nodes (8): IotWebhookController, JsonResponse, Request, IotWsAuthController, JsonResponse, Request, BelongsTo, HasMany

### Community 29 - "ParkAmenity"
Cohesion: 0.31
Nodes (3): ParkAmenity, BelongsTo, ParkAmenityTest

### Community 30 - "ScrubAndTraceProcessor"
Cohesion: 0.24
Nodes (3): CustomizeFormatter, ScrubAndTraceProcessor, ProcessorInterface

### Community 31 - "Langkah Instalasi"
Cohesion: 0.17
Nodes (12): 10. Re-up Container, 11. Buat port forward local dan firewall rule - Optional untuk instalasi pada WSL, 1. Konfigurasi Environment (.env), 2. Generate RSA Keys (Di Root), 3. Verifikasi Credential, 4. Menjalankan Container, 5. Generate Application Key, 6. Migrasi Database (+4 more)

### Community 32 - "Claude Code — Project Instructions"
Cohesion: 0.20
Nodes (9): 1. Import (Namespace / `use` Statement), 2. PHPDoc (Komentar Fungsi & Class), 3. Konvensi Penulisan Model (Eloquent), 4. Best Practices Laravel, Claude Code — Project Instructions, Development Cycle Workflow, Environment Isolation (Pemisahan Pengembangan dan Runtime), Graphify Knowledge Graph (+1 more)

### Community 33 - "composer.json"
Cohesion: 0.18
Nodes (10): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type (+2 more)

### Community 34 - "HasFactory"
Cohesion: 0.33
Nodes (4): MqttListenerCommand, IotCapture, BelongsTo, HasFactory

### Community 35 - "ParkSubareaController.php"
Cohesion: 0.39
Nodes (4): ParkSubareaController, JsonResponse, RedirectResponse, Request

### Community 37 - "static"
Cohesion: 0.27
Nodes (3): UserFactory, Factory, static

### Community 38 - "README.md"
Cohesion: 0.20
Nodes (6): Instalasi dengan Docker, Khusus Pengguna Linux, Persiapan File Konfigurasi, Prasyarat, Instalasi Manual (Tanpa Docker), Prasyarat

### Community 40 - "AuthServiceProvider.php"
Cohesion: 0.31
Nodes (3): AppServiceProvider, AuthServiceProvider, ServiceProvider

### Community 41 - "require"
Cohesion: 0.22
Nodes (9): require, fakerphp/faker, laravel/framework, laravel/reverb, laravel/sanctum, laravel/tinker, php, php-mqtt/laravel-client (+1 more)

### Community 42 - "Langkah Instalasi"
Cohesion: 0.22
Nodes (9): 10. Menjalankan Service Aplikasi, 1. Konfigurasi Environment (.env), 2. Generate RSA Keys (Wajib), 3. Instalasi Dependency, 4. Generate Application Key, 5. Migrasi Database & Seeding, 6. Setup Database Roles (RBAC), 7. Atur Ulang Environment (.env) - PENTING (+1 more)

### Community 45 - "User"
Cohesion: 0.12
Nodes (6): HasMany, User, Authenticatable, HasApiTokens, Notifiable, ProfileControllerTest

### Community 46 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 47 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, pestphp/pest, pestphp/pest-plugin-laravel (+2 more)

### Community 48 - "Polislot Admin Dashboard"
Cohesion: 0.29
Nodes (7): 🧰 Custom Utility Commands, 🚀 Fitur Utama, 📖 Panduan Instalasi, Polislot Admin Dashboard, Prerequisites External Service, 📂 Struktur Direktori, 🛠️ Tech Stack

### Community 50 - "Aturan Standar Penulisan Kode PHP & Laravel (Coding Standards)"
Cohesion: 0.33
Nodes (5): 1. Import (Namespace / `use` Statement), 2. PHPDoc (Komentar Fungsi & Class), 3. Konvensi Penulisan Model (Eloquent), 4. Best Practices Laravel Lainnya, Aturan Standar Penulisan Kode PHP & Laravel (Coding Standards)

### Community 51 - "UserHistory"
Cohesion: 0.31
Nodes (3): BelongsTo, UserHistory, UserHistoryTest

### Community 52 - "🚀 Instalasi"
Cohesion: 0.33
Nodes (6): 1. Salin Folder `python/` ke Perangkat Edge, 2. Buat Virtual Environment (Disarankan), 3. Install Dependensi, 4. Konfigurasi File `.env`, Format Model yang Didukung (`YOLO_WEIGHTS`), 🚀 Instalasi

### Community 53 - "🚗 PoliSlot — Perangkat Edge IoT Deteksi Parkir"
Cohesion: 0.33
Nodes (6): 🗂️ Caching Konfigurasi Lokal, 📋 Daftar Script, 🔐 Keamanan, 🌐 Koneksi Melalui Cloudflare Tunnel (Produksi), ⚙️ Persyaratan Sistem, 🚗 PoliSlot — Perangkat Edge IoT Deteksi Parkir

### Community 54 - "▶️ Menjalankan Script"
Cohesion: 0.33
Nodes (6): Contoh — MQTT dengan Preview, Contoh — MQTT Headless, Contoh — WebSocket dengan Preview, Contoh — WebSocket Headless, ▶️ Menjalankan Script, Sintaks

### Community 55 - "8. Konfigurasi Manual MQTT Broker (Mosquitto)"
Cohesion: 0.33
Nodes (6): 8. Konfigurasi Manual MQTT Broker (Mosquitto), A. Instalasi Mosquitto, B. Konfigurasi `mosquitto.conf`, C. Membuat File Password & Kredensial User, D. Membuat File ACL (Access Control List), E. Menjalankan Service Mosquitto

### Community 58 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 62 - "post-create-project-cmd"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 63 - "9. Mengatasi Masalah Koneksi Broker Satu Jaringan (Troubleshooting)"
Cohesion: 0.50
Nodes (4): 9. Mengatasi Masalah Koneksi Broker Satu Jaringan (Troubleshooting), A. Konfigurasi IP Host & Port (.env), B. Windows Defender Firewall (Sering Menjadi Penyebab Utama), C. Isolasi Jaringan Router (AP Isolation)

### Community 64 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 65 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 125 - "ParkSubarea.php"
Cohesion: 0.18
Nodes (3): BelongsTo, HasMany, HasOne

## Knowledge Gaps
- **187 isolated node(s):** `run.sh script`, `$schema`, `name`, `type`, `description` (+182 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **57 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Feedback Models`, `Console Commands`, `Core Models`, `Reward System`, `Auth Tests`, `Area Management`, `InfoBoard`, `Reward`, `AuthControllerTest`, `ParkSubarea`, `ParkArea.php`, `.log`, `HasFactory`, `ProfileControllerTest`, `UserTest`, `UserHistory`, `DashboardControllerTest`, `RBACTest`, `HistoryServiceTest`?**
  _High betweenness centrality (0.086) - this node is a cross-community bridge._
- **Why does `TestCase` connect `Reward System` to `Console Commands`, `Core Models`, `IoT Events`, `Auth Tests`, `Area Management`, `InfoBoard`, `Reward`, `AuthControllerTest`, `ParkSubarea`, `ParkArea.php`, `IotDevice`, `ApiEncryption.php`, `IotDevice.php`, `ParkAmenity`, `IotWebhookControllerTest.php`, `ParkAreaTest`, `IotDetectionControllerTest`, `ProfileControllerTest`, `User`, `UserTest`, `UserHistory`, `DashboardControllerTest`, `RBACTest`, `HistoryServiceTest`?**
  _High betweenness centrality (0.043) - this node is a cross-community bridge._
- **Why does `Controller` connect `Feedback API` to `Feedback Models`, `ParkSubareaController.php`, `Core Models`, `Auth Tests`, `InfoBoard`, `Reward`, `IotDetectionController`, `IotDevice`, `.log`, `UserValidationController.php`, `IotDevice.php`?**
  _High betweenness centrality (0.040) - this node is a cross-community bridge._
- **Are the 149 inferred relationships involving `User` (e.g. with `.forgotPasswordOtpResend()` and `.forgotPasswordOtpVerify()`) actually correct?**
  _`User` has 149 INFERRED edges - model-reasoned connections that need verification._
- **What connects `run.sh script`, `$schema`, `name` to the rest of the system?**
  _187 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `PHP Infrastructure` be split into smaller, more focused modules?**
  _Cohesion score 0.13368983957219252 - nodes in this community are weakly interconnected._
- **Should `Feedback API` be split into smaller, more focused modules?**
  _Cohesion score 0.0544464609800363 - nodes in this community are weakly interconnected._