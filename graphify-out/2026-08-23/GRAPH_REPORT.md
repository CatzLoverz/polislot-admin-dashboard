# Graph Report - polislot-admin-dashboard  (2026-08-23)

## Corpus Check
- 278 files · ~118,034 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1357 nodes · 2740 edges · 206 communities (122 shown, 84 thin omitted)
- Extraction: 83% EXTRACTED · 17% INFERRED · 0% AMBIGUOUS · INFERRED: 478 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `374e3050`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Database\Eloquent\Model
- Controller
- UserFaq
- AuthControllerTest
- What You Must Do When Invoked
- ApiEncryption.php
- Validation
- FeedbackCategory
- Illuminate\Http\RedirectResponse
- graphify reference: extra exports and benchmark
- What You Must Do When Invoked
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
- .log
- Illuminate\Foundation\Testing\RefreshDatabase
- Mission
- Api/AuthController.php
- scripts
- RBAC.php
- UserValidation
- Illuminate\Http\Request
- ParkAmenity
- ScrubAndTraceProcessor
- DashboardController.php
- Claude Code — Project Instructions
- composer.json
- SubareaComment
- graphify reference: extra exports and benchmark
- TestCase
- graphify reference: query, path, explain
- ParkAreaControllerTest
- ParkArea
- AppServiceProvider
- require
- IotDetectionControllerTest
- graphify reference: add a URL and watch a folder
- User
- config
- require-dev
- graphify reference: commit hook and native CLAUDE.md integration
- Procedures
- graphify reference: incremental update and cluster-only
- UserHistory
- 🚗 PoliSlot — Perangkat Edge IoT Deteksi Parkir
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- Illuminate\View\View
- DashboardControllerTest
- PoliSlotLoginTest
- psr-4
- RBACTest
- Laravel Coding Standards Enforcer
- UserHistory.php
- ProfileControllerTest
- .store
- extra
- test_01_tambah_feedback.py
- test_02_ubah_feedback.py
- test_03_hapus_feedback.py
- test_01_tambah_infoboard.py
- test_02_ubah_infoboard.py
- test_03_hapus_infoboard.py
- test_01_tambah_misi.py
- test_02_ubah_misi.py
- test_03_hapus_misi.py
- test_01_view_profil.py
- test_02_ubah_profil.py
- test_01_tambah_reward.py
- test_02_ubah_reward.py
- test_03_hapus_reward.py
- test_04_view_verify.py
- test_01_tambah_faq.py
- test_02_ubah_faq.py
- test_03_hapus_faq.py
- extraction-spec.md
- ExampleTest
- Contents.IoTDetection.partials.captures_grid
- App Service (Laravel/Reverb/Queue)
- docker-entrypoint.sh
- logrotate-entrypoint.sh
- Production Security Checklist
- graphify reference: query, path, explain
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
- IotWebhookControllerTest.php
- run.sh
- Illuminate\Database\Eloquent\Relations\HasMany
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
- autoload-dev
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- post-autoload-dump
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- Workflow: Laravel Coding Standards
- .agents/skills/graphify/references/extraction-spec.md
- run.sh script

## God Nodes (most connected - your core abstractions)
1. `User` - 154 edges
2. `TestCase` - 93 edges
3. `ParkSubarea` - 77 edges
4. `Controller` - 68 edges
5. `ParkArea` - 53 edges
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

## Communities (206 total, 84 thin omitted)

### Community 0 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.06
Nodes (17): IotCommandSent, IotCountUpdated, IotDetectionReceived, IotDeviceStatusChanged, IotThresholdUpdated, SubareaStatusUpdated, IotCapture, ParkSubareaHistory (+9 more)

### Community 1 - "Controller"
Cohesion: 0.08
Nodes (12): FeedbackCategoryController, FeedbackController, HistoryController, InfoBoardController, IotWsAuthController, Controller, FeedbackController, ValidationController (+4 more)

### Community 2 - "UserFaq"
Cohesion: 0.27
Nodes (3): UserFaqController, UserFaqController, UserFaq

### Community 4 - "What You Must Do When Invoked"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 6 - "Validation"
Cohesion: 0.23
Nodes (3): Validation, UserValidationControllerTest, ValidationTest

### Community 7 - "FeedbackCategory"
Cohesion: 0.08
Nodes (14): Feedback, FeedbackCategory, UserFactory, DatabaseSeeder, FeedbackCategorySeeder, FeedbackSeeder, UserSeeder, ValidationSeeder (+6 more)

### Community 8 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.13
Nodes (4): AuthController, FeedbackCategoryController, MissionController, Illuminate\Http\RedirectResponse

### Community 9 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 10 - "What You Must Do When Invoked"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 11 - "InfoBoard"
Cohesion: 0.15
Nodes (4): InfoBoardController, InfoBoard, InfoBoardControllerTest, InfoBoardTest

### Community 12 - "devDependencies"
Cohesion: 0.08
Nodes (25): axios, concurrently, laravel-echo, laravel-vite-plugin, devDependencies, axios, concurrently, laravel-echo (+17 more)

### Community 13 - "Reward"
Cohesion: 0.10
Nodes (7): RewardController, Reward, UserReward, RewardControllerTest, RewardControllerTest, RewardTest, UserRewardTest

### Community 15 - "Command"
Cohesion: 0.14
Nodes (12): BackupAuto, BackupClean, BackupDatabase, DbList, DbRestore, MqttListenerCommand, SetupDatabaseAdmin, SetupDatabaseUser (+4 more)

### Community 16 - "ParkSubarea"
Cohesion: 0.15
Nodes (3): ParkSubarea, ParkSubareaControllerTest, ParkSubareaTest

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

### Community 21 - ".log"
Cohesion: 0.17
Nodes (4): IotDetectionController, IotWebhookController, IotDetectionController, IotDevice

### Community 23 - "Mission"
Cohesion: 0.09
Nodes (8): MissionController, Mission, UserMission, MissionService, MissionControllerTest, MissionTest, UserMissionTest, MissionServiceTest

### Community 24 - "Api/AuthController.php"
Cohesion: 0.13
Nodes (9): AccountLockedMail, LoginNotificationMail, SendOtpMail, IpLocationService, Illuminate\Bus\Queueable, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Mail\Mailable, Illuminate\Mail\Mailables\Content (+1 more)

### Community 25 - "scripts"
Cohesion: 0.13
Nodes (15): scripts, dev, post-create-project-cmd, post-root-package-install, post-update-cmd, test, Composer\\Config::disableProcessTimeout, npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite --kill-others (+7 more)

### Community 26 - "RBAC.php"
Cohesion: 0.14
Nodes (9): ApiEncryption, RBAC, TrustProxies, NotCurrentPassword, Closure, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\TrustProxies (+1 more)

### Community 28 - "Illuminate\Http\Request"
Cohesion: 0.10
Nodes (9): AuthController, MapVisualizationController, ProfileController, SubareaCommentController, DashboardController, ParkAmenityController, ParkSubareaController, Illuminate\Http\JsonResponse (+1 more)

### Community 30 - "ScrubAndTraceProcessor"
Cohesion: 0.24
Nodes (3): CustomizeFormatter, ScrubAndTraceProcessor, Monolog\Processor\ProcessorInterface

### Community 31 - "DashboardController.php"
Cohesion: 0.16
Nodes (3): RewardController, RewardVerificationController, HistoryService

### Community 32 - "Claude Code — Project Instructions"
Cohesion: 0.20
Nodes (9): 1. Import (Namespace / `use` Statement), 2. PHPDoc (Komentar Fungsi & Class), 3. Konvensi Penulisan Model (Eloquent), 4. Best Practices Laravel, Claude Code — Project Instructions, Development Cycle Workflow, Environment Isolation (Pemisahan Pengembangan dan Runtime), Graphify Knowledge Graph (+1 more)

### Community 33 - "composer.json"
Cohesion: 0.18
Nodes (10): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type (+2 more)

### Community 35 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 36 - "TestCase"
Cohesion: 0.13
Nodes (10): Illuminate\Foundation\Testing\TestCase, Illuminate\Foundation\Testing\WithoutMiddleware, ExampleTest, FeedbackCategoryControllerTest, FeedbackControllerTest, HistoryControllerTest, MapVisualizationControllerTest, MissionControllerTest (+2 more)

### Community 37 - "graphify reference: query, path, explain"
Cohesion: 0.33
Nodes (5): For /graphify explain, For /graphify path, graphify reference: query, path, explain, Step 0 — Constrained query expansion (REQUIRED before traversal), Step 1 — Traversal

### Community 39 - "ParkArea"
Cohesion: 0.24
Nodes (3): ParkArea, SubareaCommentControllerTest, ParkAreaTest

### Community 40 - "AppServiceProvider"
Cohesion: 0.22
Nodes (4): AppServiceProvider, AuthServiceProvider, Illuminate\Foundation\Support\Providers\AuthServiceProvider, Illuminate\Support\ServiceProvider

### Community 41 - "require"
Cohesion: 0.20
Nodes (10): require, fakerphp/faker, laravel/framework, laravel/octane, laravel/reverb, laravel/sanctum, laravel/tinker, php (+2 more)

### Community 44 - "graphify reference: add a URL and watch a folder"
Cohesion: 0.50
Nodes (3): For /graphify add, For --watch, graphify reference: add a URL and watch a folder

### Community 45 - "User"
Cohesion: 0.11
Nodes (6): User, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens, ProfileControllerTest, UserTest

### Community 46 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 47 - "require-dev"
Cohesion: 0.20
Nodes (10): require-dev, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, pestphp/pest, pestphp/pest-plugin-laravel (+2 more)

### Community 48 - "graphify reference: commit hook and native CLAUDE.md integration"
Cohesion: 0.50
Nodes (3): For git commit hook, For native CLAUDE.md integration, graphify reference: commit hook and native CLAUDE.md integration

### Community 49 - "Procedures"
Cohesion: 0.29
Nodes (6): 1. Test Code Formatting with Pint, 2. Auto-Fix Code Formatting with Pint, 3. Coding Standards Verification Checklist, Helper Scripts, Laravel Coding Standards Enforcer, Procedures

### Community 50 - "graphify reference: incremental update and cluster-only"
Cohesion: 0.50
Nodes (3): For --cluster-only, For --update (incremental re-extraction), graphify reference: incremental update and cluster-only

### Community 52 - "🚗 PoliSlot — Perangkat Edge IoT Deteksi Parkir"
Cohesion: 0.11
Nodes (18): 1. Salin Folder `python/` ke Perangkat Edge, 2. Buat Virtual Environment (Disarankan), 3. Install Dependensi, 4. Konfigurasi File `.env`, 🗂️ Caching Konfigurasi Lokal, Contoh — MQTT dengan Preview, Contoh — MQTT Headless, Contoh — WebSocket dengan Preview (+10 more)

### Community 55 - "Illuminate\View\View"
Cohesion: 0.17
Nodes (3): ParkAreaController, ProfileController, Illuminate\View\View

### Community 58 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 65 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 108 - "graphify reference: query, path, explain"
Cohesion: 0.33
Nodes (5): For /graphify explain, For /graphify path, graphify reference: query, path, explain, Step 0 — Constrained query expansion (REQUIRED before traversal), Step 1 — Traversal

### Community 194 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 195 - "graphify reference: add a URL and watch a folder"
Cohesion: 0.50
Nodes (3): For /graphify add, For --watch, graphify reference: add a URL and watch a folder

### Community 196 - "graphify reference: commit hook and native CLAUDE.md integration"
Cohesion: 0.50
Nodes (3): For git commit hook, For native CLAUDE.md integration, graphify reference: commit hook and native CLAUDE.md integration

### Community 197 - "graphify reference: incremental update and cluster-only"
Cohesion: 0.50
Nodes (3): For --cluster-only, For --update (incremental re-extraction), graphify reference: incremental update and cluster-only

### Community 198 - "post-autoload-dump"
Cohesion: 0.67
Nodes (3): post-autoload-dump, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump, @php artisan package:discover --ansi

## Knowledge Gaps
- **210 isolated node(s):** `run.sh script`, `run.sh script`, `$schema`, `name`, `type` (+205 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **84 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Database\Eloquent\Model`, `AuthControllerTest`, `Validation`, `FeedbackCategory`, `Illuminate\Http\RedirectResponse`, `InfoBoard`, `Reward`, `AuthControllerTest`, `ParkSubarea`, `Illuminate\Foundation\Testing\RefreshDatabase`, `Mission`, `UserValidation`, `Illuminate\Http\Request`, `DashboardController.php`, `SubareaComment`, `TestCase`, `ParkAreaControllerTest`, `ParkArea`, `UserHistory`, `DashboardControllerTest`, `RBACTest`, `UserHistory.php`, `ProfileControllerTest`, `Illuminate\Database\Eloquent\Relations\HasMany`?**
  _High betweenness centrality (0.082) - this node is a cross-community bridge._
- **Why does `TestCase` connect `TestCase` to `Illuminate\Database\Eloquent\Model`, `AuthControllerTest`, `ApiEncryption.php`, `Validation`, `FeedbackCategory`, `InfoBoard`, `Reward`, `AuthControllerTest`, `ParkSubarea`, `Illuminate\Foundation\Testing\RefreshDatabase`, `Mission`, `UserValidation`, `ParkAmenity`, `SubareaComment`, `ParkAreaControllerTest`, `ParkArea`, `FeedbackCategory.php`, `IotDetectionControllerTest`, `User`, `UserHistory`, `DashboardControllerTest`, `RBACTest`, `UserHistory.php`, `ProfileControllerTest`, `ExampleTest`, `IotWebhookControllerTest.php`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Why does `ParkSubarea` connect `ParkSubarea` to `Illuminate\Database\Eloquent\Model`, `Controller`, `.store`, `SubareaComment`, `TestCase`, `Validation`, `ParkArea`, `UserValidation`, `IotDetectionControllerTest`, `ParkAmenity`, `.log`, `Illuminate\Foundation\Testing\RefreshDatabase`, `DashboardControllerTest`, `IotWebhookControllerTest.php`, `Illuminate\Http\Request`, `Illuminate\Database\Eloquent\Relations\HasMany`, `DashboardController.php`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._
- **Are the 142 inferred relationships involving `User` (e.g. with `.login()` and `.register()`) actually correct?**
  _`User` has 142 INFERRED edges - model-reasoned connections that need verification._
- **What connects `run.sh script`, `run.sh script`, `$schema` to the rest of the system?**
  _210 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Database\Eloquent\Model` be split into smaller, more focused modules?**
  _Cohesion score 0.06126126126126126 - nodes in this community are weakly interconnected._
- **Should `Controller` be split into smaller, more focused modules?**
  _Cohesion score 0.08253968253968254 - nodes in this community are weakly interconnected._