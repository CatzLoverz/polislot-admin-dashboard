# Graph Report - polislot-admin-dashboard  (2026-08-20)

## Corpus Check
- 277 files · ~117,746 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1351 nodes · 2724 edges · 206 communities (123 shown, 83 thin omitted)
- Extraction: 82% EXTRACTED · 18% INFERRED · 0% AMBIGUOUS · INFERRED: 477 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `d011e0e8`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Queue\SerializesModels
- Controller
- UserFaq
- AuthControllerTest
- What You Must Do When Invoked
- InfoBoard.php
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
- ParkSubareaControllerTest
- parking_detector_mqtt.py
- parking_detector_mqtt_preview.py
- parking_detector_ws.py
- parking_detector_ws_preview.py
- IotDevice
- Illuminate\Foundation\Testing\RefreshDatabase
- Mission
- Illuminate\Foundation\Testing\WithoutMiddleware
- scripts
- RBAC.php
- UserValidation
- Illuminate\Http\Request
- ParkAmenity
- ScrubAndTraceProcessor
- DashboardController.php
- Claude Code — Project Instructions
- composer.json
- Illuminate\Database\Eloquent\Model
- graphify reference: extra exports and benchmark
- TestCase
- graphify reference: query, path, explain
- ParkAreaControllerTest
- ParkSubarea
- AppServiceProvider
- require
- FeedbackCategory.php
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
- IotDevice.php
- DashboardControllerTest
- PoliSlotLoginTest
- psr-4
- RBACTest
- Laravel Coding Standards Enforcer
- HistoryServiceTest
- autoload-dev
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
- MissionController
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
- post-create-project-cmd
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- ParkSubareaController
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
- `HistoryController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/HistoryController.php → app/Http/Controllers/Controller.php
- `IotDetectionController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/IotDetectionController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Docker Compose Service Stack** — docker_docker_compose_app_service, docker_docker_compose_db_service, docker_docker_compose_scheduler_service, docker_docker_compose_logrotate_service, docker_docker_compose_mosquitto_service [EXTRACTED 0.90]
- **IoT Parking Detection Pipeline** — docs_project_summary_edge_iot_parking_detector, docker_docker_compose_mosquitto_service, docs_project_summary_hmac_shared_secret, python_requirements_python_iot_dependencies [INFERRED 0.80]
- **PoliSlot Three-Component Architecture** — docker_docker_compose_app_service, docs_project_summary_mobile_app_flutter, docs_project_summary_edge_iot_parking_detector [INFERRED 0.85]

## Communities (206 total, 83 thin omitted)

### Community 0 - "Illuminate\Queue\SerializesModels"
Cohesion: 0.08
Nodes (18): IotCommandSent, IotCountUpdated, IotDetectionReceived, IotDeviceStatusChanged, IotThresholdUpdated, SubareaStatusUpdated, AccountLockedMail, LoginNotificationMail (+10 more)

### Community 1 - "Controller"
Cohesion: 0.10
Nodes (11): FeedbackCategoryController, FeedbackController, InfoBoardController, IotWsAuthController, Controller, ParkAmenityController, ValidationController, Illuminate\Foundation\Auth\Access\AuthorizesRequests (+3 more)

### Community 2 - "UserFaq"
Cohesion: 0.22
Nodes (3): UserFaqController, UserFaqController, UserFaq

### Community 4 - "What You Must Do When Invoked"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 6 - "Validation"
Cohesion: 0.23
Nodes (3): Validation, UserValidationControllerTest, ValidationTest

### Community 7 - "FeedbackCategory"
Cohesion: 0.07
Nodes (15): FeedbackCategoryController, Feedback, FeedbackCategory, UserFactory, DatabaseSeeder, FeedbackCategorySeeder, FeedbackSeeder, UserSeeder (+7 more)

### Community 8 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.09
Nodes (7): AuthController, FeedbackController, ParkAreaController, ProfileController, RewardController, Illuminate\Http\RedirectResponse, Illuminate\View\View

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
Cohesion: 0.11
Nodes (6): Reward, UserReward, RewardControllerTest, RewardControllerTest, RewardTest, UserRewardTest

### Community 15 - "Command"
Cohesion: 0.14
Nodes (12): BackupAuto, BackupClean, BackupDatabase, DbList, DbRestore, MqttListenerCommand, SetupDatabaseAdmin, SetupDatabaseUser (+4 more)

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

### Community 21 - "IotDevice"
Cohesion: 0.16
Nodes (4): IotDetectionController, IotDetectionController, IotCapture, IotDevice

### Community 22 - "Illuminate\Foundation\Testing\RefreshDatabase"
Cohesion: 0.18
Nodes (3): Illuminate\Foundation\Testing\RefreshDatabase, IotWebhookControllerTest, IotWsAuthControllerTest

### Community 23 - "Mission"
Cohesion: 0.07
Nodes (10): MissionController, UserValidationController, Mission, UserMission, MissionService, Illuminate\Database\Eloquent\Factories\HasFactory, MissionControllerTest, MissionTest (+2 more)

### Community 24 - "Illuminate\Foundation\Testing\WithoutMiddleware"
Cohesion: 0.28
Nodes (5): Illuminate\Foundation\Testing\WithoutMiddleware, FeedbackControllerTest, HistoryControllerTest, MapVisualizationControllerTest, UserFaqControllerTest

### Community 25 - "scripts"
Cohesion: 0.14
Nodes (14): scripts, dev, post-autoload-dump, post-root-package-install, post-update-cmd, test, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump (+6 more)

### Community 26 - "RBAC.php"
Cohesion: 0.14
Nodes (9): ApiEncryption, RBAC, TrustProxies, NotCurrentPassword, Closure, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Configuration\Middleware, Illuminate\Http\Middleware\TrustProxies (+1 more)

### Community 28 - "Illuminate\Http\Request"
Cohesion: 0.13
Nodes (7): AuthController, HistoryController, ProfileController, SubareaCommentController, DashboardController, Illuminate\Http\JsonResponse, Illuminate\Http\Request

### Community 30 - "ScrubAndTraceProcessor"
Cohesion: 0.15
Nodes (4): CustomizeFormatter, ScrubAndTraceProcessor, Monolog\Processor\ProcessorInterface, ScrubAndTraceProcessorTest

### Community 31 - "DashboardController.php"
Cohesion: 0.21
Nodes (3): RewardController, RewardVerificationController, HistoryService

### Community 32 - "Claude Code — Project Instructions"
Cohesion: 0.20
Nodes (9): 1. Import (Namespace / `use` Statement), 2. PHPDoc (Komentar Fungsi & Class), 3. Konvensi Penulisan Model (Eloquent), 4. Best Practices Laravel, Claude Code — Project Instructions, Development Cycle Workflow, Environment Isolation (Pemisahan Pengembangan dan Runtime), Graphify Knowledge Graph (+1 more)

### Community 33 - "composer.json"
Cohesion: 0.18
Nodes (10): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type (+2 more)

### Community 34 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.15
Nodes (5): SubareaComment, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo, Illuminate\Database\Eloquent\Relations\HasOne, SubareaCommentTest

### Community 35 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 36 - "TestCase"
Cohesion: 0.12
Nodes (6): Illuminate\Foundation\Testing\TestCase, PHPUnit\Framework\TestCase, ExampleTest, MissionControllerTest, TestCase, ExampleTest

### Community 37 - "graphify reference: query, path, explain"
Cohesion: 0.33
Nodes (5): For /graphify explain, For /graphify path, graphify reference: query, path, explain, Step 0 — Constrained query expansion (REQUIRED before traversal), Step 1 — Traversal

### Community 39 - "ParkSubarea"
Cohesion: 0.18
Nodes (5): ParkArea, ParkSubarea, SubareaCommentControllerTest, ParkAreaTest, ParkSubareaTest

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
Cohesion: 0.09
Nodes (7): User, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens, ProfileControllerTest, ProfileControllerTest, UserTest

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

### Community 58 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 64 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 65 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 108 - "graphify reference: query, path, explain"
Cohesion: 0.33
Nodes (5): For /graphify explain, For /graphify path, graphify reference: query, path, explain, Step 0 — Constrained query expansion (REQUIRED before traversal), Step 1 — Traversal

### Community 194 - "post-create-project-cmd"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 195 - "graphify reference: add a URL and watch a folder"
Cohesion: 0.50
Nodes (3): For /graphify add, For --watch, graphify reference: add a URL and watch a folder

### Community 196 - "graphify reference: commit hook and native CLAUDE.md integration"
Cohesion: 0.50
Nodes (3): For git commit hook, For native CLAUDE.md integration, graphify reference: commit hook and native CLAUDE.md integration

### Community 197 - "graphify reference: incremental update and cluster-only"
Cohesion: 0.50
Nodes (3): For --cluster-only, For --update (incremental re-extraction), graphify reference: incremental update and cluster-only

## Knowledge Gaps
- **210 isolated node(s):** `run.sh script`, `run.sh script`, `$schema`, `name`, `type` (+205 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **83 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `UserFaq`, `AuthControllerTest`, `Validation`, `FeedbackCategory`, `Illuminate\Http\RedirectResponse`, `InfoBoard`, `Reward`, `AuthControllerTest`, `ParkSubareaControllerTest`, `Mission`, `UserValidation`, `Illuminate\Http\Request`, `Illuminate\Database\Eloquent\Model`, `ParkAreaControllerTest`, `ParkSubarea`, `UserHistory`, `DashboardControllerTest`, `RBACTest`, `HistoryServiceTest`, `User.php`, `Illuminate\Database\Eloquent\Relations\HasMany`?**
  _High betweenness centrality (0.066) - this node is a cross-community bridge._
- **Why does `TestCase` connect `TestCase` to `AuthControllerTest`, `InfoBoard.php`, `Validation`, `FeedbackCategory`, `InfoBoard`, `Reward`, `AuthControllerTest`, `ParkSubareaControllerTest`, `Illuminate\Foundation\Testing\RefreshDatabase`, `Mission`, `Illuminate\Foundation\Testing\WithoutMiddleware`, `UserValidation`, `ParkAmenity`, `ScrubAndTraceProcessor`, `Illuminate\Database\Eloquent\Model`, `ParkAreaControllerTest`, `ParkSubarea`, `FeedbackCategory.php`, `IotDetectionControllerTest`, `User`, `UserHistory`, `DashboardControllerTest`, `RBACTest`, `HistoryServiceTest`, `User.php`?**
  _High betweenness centrality (0.037) - this node is a cross-community bridge._
- **Why does `ParkSubarea` connect `ParkSubarea` to `Illuminate\Queue\SerializesModels`, `Controller`, `Illuminate\Database\Eloquent\Model`, `ParkSubareaController`, `Validation`, `ParkSubareaControllerTest`, `User.php`, `ParkAmenity`, `IotDevice`, `Illuminate\Foundation\Testing\RefreshDatabase`, `IotDevice.php`, `DashboardControllerTest`, `Illuminate\Foundation\Testing\WithoutMiddleware`, `UserValidation`, `Illuminate\Http\Request`, `Illuminate\Database\Eloquent\Relations\HasMany`, `DashboardController.php`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._
- **Are the 142 inferred relationships involving `User` (e.g. with `.login()` and `.register()`) actually correct?**
  _`User` has 142 INFERRED edges - model-reasoned connections that need verification._
- **What connects `run.sh script`, `run.sh script`, `$schema` to the rest of the system?**
  _210 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Queue\SerializesModels` be split into smaller, more focused modules?**
  _Cohesion score 0.07609427609427609 - nodes in this community are weakly interconnected._
- **Should `Controller` be split into smaller, more focused modules?**
  _Cohesion score 0.1010752688172043 - nodes in this community are weakly interconnected._