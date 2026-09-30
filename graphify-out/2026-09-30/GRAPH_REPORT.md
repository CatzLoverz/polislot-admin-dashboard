# Graph Report - polislot-admin-dashboard  (2026-09-30)

## Corpus Check
- 277 files · ~117,562 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 25 file(s) not represented in the graph (top: (none) 12, .conf 2, .xml 2)

## Summary
- 1425 nodes · 3210 edges · 179 communities (49 shown, 85 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 126 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `34bd76da`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- IotDevice
- Controller
- Illuminate\Database\Eloquent\Model
- User
- What You Must Do When Invoked
- IotCapture
- Validation
- FeedbackCategory
- Illuminate\Http\RedirectResponse
- graphify reference: extra exports and benchmark
- What You Must Do When Invoked
- InfoBoard
- package.json
- Reward
- ParkSubareaController.php
- Illuminate\Console\Command
- .store
- parking_detector_mqtt.py
- parking_detector_mqtt_preview.py
- parking_detector_ws.py
- parking_detector_ws_preview.py
- SubareaStatusUpdated
- TestCase
- Mission
- IpLocationService
- scripts
- ApiEncryption
- IotWebhookControllerTest
- Illuminate\Http\Request
- MapVisualizationControllerTest
- Exception
- ParkSubareaHistory
- Claude Code — Project Instructions
- composer.json
- Illuminate\Database\Eloquent\Relations\BelongsTo
- graphify reference: extra exports and benchmark
- TrustProxies.php
- graphify reference: query, path, explain
- ParkAreaControllerTest
- ParkSubarea
- autoload-dev
- require
- IotDetectionControllerTest
- graphify reference: add a URL and watch a folder
- User.php
- config
- require-dev
- graphify reference: commit hook and native CLAUDE.md integration
- Procedures
- graphify reference: incremental update and cluster-only
- UserHistory
- 🚗 PoliSlot — Perangkat Edge IoT Deteksi Parkir
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- IotWsAuthControllerTest
- PoliSlotLoginTest
- psr-4
- Laravel Coding Standards Enforcer
- Illuminate\Support\Facades\Schema
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
- run.sh
- Illuminate\Database\Eloquent\Relations\HasMany
- ScrubAndTraceProcessor
- mqtt-client.php
- sanctum.php
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
- IotDevice.php
- Illuminate\Support\Facades\Schedule
- IotApiStressTest.php
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- Workflow: Laravel Coding Standards
- .agents/skills/graphify/references/extraction-spec.md
- run.sh script

## God Nodes (most connected - your core abstractions)
1. `User` - 200 edges
2. `TestCase` - 93 edges
3. `ParkSubarea` - 77 edges
4. `ParkArea` - 73 edges
5. `Controller` - 68 edges
6. `IotDevice` - 45 edges
7. `Validation` - 36 edges
8. `FeedbackCategory` - 34 edges
9. `AuthControllerTest` - 34 edges
10. `Mission` - 33 edges

## Surprising Connections (you probably didn't know these)
- `Robots Disallow-All Policy` --conceptually_related_to--> `Production Security Checklist`  [INFERRED]
  public/robots.txt → docs/PROJECT_SUMMARY.md
- `IpLocationServiceTest` --references--> `IpLocationService`  [EXTRACTED]
  tests/Unit/Service/IpLocationServiceTest.php → app/Services/IpLocationService.php
- `AuthController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/AuthController.php → app/Http/Controllers/Controller.php
- `IotDetectionController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/IotDetectionController.php → app/Http/Controllers/Controller.php
- `ProfileController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/ProfileController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Docker Compose Service Stack** — docker_docker_compose_app_service, docker_docker_compose_db_service, docker_docker_compose_scheduler_service, docker_docker_compose_logrotate_service, docker_docker_compose_mosquitto_service [EXTRACTED 0.90]
- **IoT Parking Detection Pipeline** — docs_project_summary_edge_iot_parking_detector, docker_docker_compose_mosquitto_service, docs_project_summary_hmac_shared_secret, python_requirements_python_iot_dependencies [INFERRED 0.80]
- **PoliSlot Three-Component Architecture** — docker_docker_compose_app_service, docs_project_summary_mobile_app_flutter, docs_project_summary_edge_iot_parking_detector [INFERRED 0.85]

## Communities (179 total, 85 thin omitted)

### Community 0 - "IotDevice"
Cohesion: 0.06
Nodes (23): MqttListenerCommand, IotCommandSent, IotCountUpdated, IotDetectionReceived, IotDeviceStatusChanged, IotThresholdUpdated, IotDetectionController, IotDevice (+15 more)

### Community 1 - "Controller"
Cohesion: 0.12
Nodes (14): FeedbackCategoryController, FeedbackController, HistoryController, InfoBoardController, IotWebhookController, IotWsAuthController, MapVisualizationController, MissionController (+6 more)

### Community 3 - "User"
Cohesion: 0.04
Nodes (6): User, AuthControllerTest, ProfileControllerTest, AuthControllerTest, ProfileControllerTest, UserTest

### Community 4 - "What You Must Do When Invoked"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 6 - "Validation"
Cohesion: 0.14
Nodes (5): Validation, Mockery, UserValidationControllerTest, UserValidationTest, ValidationTest

### Community 7 - "FeedbackCategory"
Cohesion: 0.10
Nodes (11): Feedback, FeedbackCategory, DatabaseSeeder, FeedbackCategorySeeder, FeedbackSeeder, UserSeeder, ValidationSeeder, Faker\Factory (+3 more)

### Community 8 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.06
Nodes (13): AuthController, FeedbackCategoryController, FeedbackController, InfoBoardController, MissionController, ParkAmenityController, ParkAreaController, ProfileController (+5 more)

### Community 9 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 10 - "What You Must Do When Invoked"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 11 - "InfoBoard"
Cohesion: 0.13
Nodes (3): InfoBoard, InfoBoardControllerTest, InfoBoardTest

### Community 12 - "package.json"
Cohesion: 0.07
Nodes (25): devDependencies, axios, concurrently, laravel-echo, laravel-vite-plugin, pusher-js, tailwindcss, @tailwindcss/vite (+17 more)

### Community 13 - "Reward"
Cohesion: 0.12
Nodes (6): Reward, UserReward, RewardControllerTest, RewardControllerTest, RewardTest, UserRewardTest

### Community 15 - "Illuminate\Console\Command"
Cohesion: 0.11
Nodes (13): BackupAuto, BackupClean, BackupDatabase, DbList, DbRestore, SetupDatabaseAdmin, SetupDatabaseUser, LoggableOutput (+5 more)

### Community 17 - "parking_detector_mqtt.py"
Cohesion: 0.18
Nodes (15): CameraStream, encrypt_image_aes(), generate_hmac_signature(), get_aes_key(), is_bbox_in_any_polygon(), is_bbox_in_polygon(), load_local_config(), main() (+7 more)

### Community 18 - "parking_detector_mqtt_preview.py"
Cohesion: 0.18
Nodes (15): CameraStream, encrypt_image_aes(), generate_hmac_signature(), get_aes_key(), is_bbox_in_any_polygon(), is_bbox_in_polygon(), load_local_config(), main() (+7 more)

### Community 19 - "parking_detector_ws.py"
Cohesion: 0.15
Nodes (16): CameraStream, detector_loop(), encrypt_image_aes(), fetch_remote_config(), generate_auth_signature(), generate_hmac_signature(), get_aes_key(), handle_command() (+8 more)

### Community 20 - "parking_detector_ws_preview.py"
Cohesion: 0.16
Nodes (15): CameraStream, encrypt_image_aes(), fetch_remote_config(), generate_auth_signature(), generate_hmac_signature(), get_aes_key(), handle_command(), is_bbox_in_any_polygon() (+7 more)

### Community 21 - "SubareaStatusUpdated"
Cohesion: 0.16
Nodes (4): SubareaStatusUpdated, UserValidationController, IotDetectionController, ZipArchive

### Community 22 - "TestCase"
Cohesion: 0.10
Nodes (17): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Foundation\Testing\WithoutMiddleware, Illuminate\Http\UploadedFile, Illuminate\Support\Facades\Config, Illuminate\Support\Facades\Event, Mockery\MockInterface, PhpMqtt\Client\Contracts\MqttClient (+9 more)

### Community 23 - "Mission"
Cohesion: 0.08
Nodes (9): Mission, UserMission, HistoryService, MissionService, MissionControllerTest, MissionTest, UserMissionTest, HistoryServiceTest (+1 more)

### Community 24 - "IpLocationService"
Cohesion: 0.05
Nodes (23): AccountLockedMail, Content, Envelope, LoginNotificationMail, Content, Envelope, Content, Envelope (+15 more)

### Community 25 - "scripts"
Cohesion: 0.29
Nodes (7): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, test

### Community 26 - "ApiEncryption"
Cohesion: 0.08
Nodes (14): ApiEncryption, RBAC, NotCurrentPassword, Closure, Illuminate\Auth\Middleware\Authenticate, Illuminate\Auth\Middleware\RedirectIfAuthenticated, Illuminate\Contracts\Validation\ValidationRule, Illuminate\Foundation\Application (+6 more)

### Community 28 - "Illuminate\Http\Request"
Cohesion: 0.12
Nodes (7): AuthController, ProfileController, SubareaCommentController, DashboardController, SendOtpMail, Illuminate\Http\JsonResponse, Illuminate\Http\Request

### Community 30 - "Exception"
Cohesion: 0.15
Nodes (19): UserValidation, Carbon\Carbon, Exception, Illuminate\Support\Carbon, Illuminate\Support\Facades\Auth, Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Hash (+11 more)

### Community 32 - "Claude Code — Project Instructions"
Cohesion: 0.20
Nodes (9): 1. Import (Namespace / `use` Statement), 2. PHPDoc (Komentar Fungsi & Class), 3. Konvensi Penulisan Model (Eloquent), 4. Best Practices Laravel, Claude Code — Project Instructions, Development Cycle Workflow, Environment Isolation (Pemisahan Pengembangan dan Runtime), Graphify Knowledge Graph (+1 more)

### Community 33 - "composer.json"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 35 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 37 - "graphify reference: query, path, explain"
Cohesion: 0.33
Nodes (5): For /graphify explain, For /graphify path, graphify reference: query, path, explain, Step 0 — Constrained query expansion (REQUIRED before traversal), Step 1 — Traversal

### Community 39 - "ParkSubarea"
Cohesion: 0.08
Nodes (10): ParkAmenity, ParkArea, ParkSubarea, SubareaCommentControllerTest, DashboardControllerTest, ParkSubareaControllerTest, ParkAmenityTest, ParkAreaTest (+2 more)

### Community 40 - "autoload-dev"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 41 - "require"
Cohesion: 0.22
Nodes (9): require, fakerphp/faker, laravel/framework, laravel/reverb, laravel/sanctum, laravel/tinker, php, php-mqtt/laravel-client (+1 more)

### Community 44 - "graphify reference: add a URL and watch a folder"
Cohesion: 0.50
Nodes (3): For /graphify add, For --watch, graphify reference: add a URL and watch a folder

### Community 45 - "User.php"
Cohesion: 0.50
Nodes (3): Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens

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

### Community 51 - "UserHistory"
Cohesion: 0.23
Nodes (3): UserHistory, Illuminate\Database\Eloquent\Factories\HasFactory, UserHistoryTest

### Community 52 - "🚗 PoliSlot — Perangkat Edge IoT Deteksi Parkir"
Cohesion: 0.11
Nodes (18): 1. Salin Folder `python/` ke Perangkat Edge, 2. Buat Virtual Environment (Disarankan), 3. Install Dependensi, 4. Konfigurasi File `.env`, 🗂️ Caching Konfigurasi Lokal, Contoh — MQTT dengan Preview, Contoh — MQTT Headless, Contoh — WebSocket dengan Preview (+10 more)

### Community 58 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 62 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.07
Nodes (3): Illuminate\Database\Migrations\Migration, Illuminate\Database\Schema\Blueprint, Illuminate\Support\Facades\Schema

### Community 65 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 108 - "graphify reference: query, path, explain"
Cohesion: 0.33
Nodes (5): For /graphify explain, For /graphify path, graphify reference: query, path, explain, Step 0 — Constrained query expansion (REQUIRED before traversal), Step 1 — Traversal

### Community 130 - "ScrubAndTraceProcessor"
Cohesion: 0.11
Nodes (11): CustomizeFormatter, ScrubAndTraceProcessor, Illuminate\Log\Logger, Illuminate\Support\Str, Monolog\Formatter\LineFormatter, Monolog\Handler\NullHandler, Monolog\Handler\StreamHandler, Monolog\Handler\SyslogUdpHandler (+3 more)

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
- **206 isolated node(s):** `run.sh script`, `run.sh script`, `$schema`, `name`, `type` (+201 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 509 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **85 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `User` to `Illuminate\Database\Eloquent\Model`, `Validation`, `ParkSubarea`, `Illuminate\Http\RedirectResponse`, `FeedbackCategory`, `ParkAreaControllerTest`, `InfoBoard`, `Reward`, `User.php`, `MapVisualizationControllerTest`, `UserHistory`, `TestCase`, `Mission`, `IpLocationService`, `ApiEncryption`, `Illuminate\Http\Request`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Exception`?**
  _High betweenness centrality (0.112) - this node is a cross-community bridge._
- **Why does `TestCase` connect `TestCase` to `IotDevice`, `User`, `Validation`, `ParkSubarea`, `ParkAreaControllerTest`, `FeedbackCategory`, `IotDetectionControllerTest`, `InfoBoard`, `Reward`, `UserHistory`, `Mission`, `IotWsAuthControllerTest`, `IpLocationService`, `ApiEncryption`, `IotWebhookControllerTest`, `MapVisualizationControllerTest`, `Exception`?**
  _High betweenness centrality (0.042) - this node is a cross-community bridge._
- **Why does `Controller` connect `Controller` to `IotDevice`, `Illuminate\Http\RedirectResponse`, `ParkSubareaController.php`, `.store`, `SubareaStatusUpdated`, `Illuminate\Http\Request`, `Exception`?**
  _High betweenness centrality (0.026) - this node is a cross-community bridge._
- **What connects `run.sh script`, `run.sh script`, `$schema` to the rest of the system?**
  _206 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `IotDevice` be split into smaller, more focused modules?**
  _Cohesion score 0.06196291270918137 - nodes in this community are weakly interconnected._
- **Should `Controller` be split into smaller, more focused modules?**
  _Cohesion score 0.1225071225071225 - nodes in this community are weakly interconnected._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.03950617283950617 - nodes in this community are weakly interconnected._