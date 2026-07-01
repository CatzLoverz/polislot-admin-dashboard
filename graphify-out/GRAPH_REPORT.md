# Graph Report - polislot-admin-dashboard  (2026-07-02)

## Corpus Check
- 236 files · ~84,132 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1268 nodes · 2049 edges · 175 communities (140 shown, 35 thin omitted)
- Extraction: 91% EXTRACTED · 9% INFERRED · 0% AMBIGUOUS · INFERRED: 190 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `1a5037c2`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- [[_COMMUNITY_Auth Controller & Login Flow|Auth Controller & Login Flow]]
- [[_COMMUNITY_IoT Detection Controller|IoT Detection Controller]]
- [[_COMMUNITY_IoT Broadcast Events|IoT Broadcast Events]]
- [[_COMMUNITY_Mission Controller & Service|Mission Controller & Service]]
- [[_COMMUNITY_Feedback Module|Feedback Module]]
- [[_COMMUNITY_Backup & DB Commands|Backup & DB Commands]]
- [[_COMMUNITY_Auth Controller Tests (EN)|Auth Controller Tests (EN)]]
- [[_COMMUNITY_Deployment & Infrastructure|Deployment & Infrastructure]]
- [[_COMMUNITY_Subarea Comment & Amenity Tests|Subarea Comment & Amenity Tests]]
- [[_COMMUNITY_Auth Controller Tests (ID)|Auth Controller Tests (ID)]]
- [[_COMMUNITY_FeedbackHistoryMission Tests|Feedback/History/Mission Tests]]
- [[_COMMUNITY_Parking Detector WebSocket|Parking Detector WebSocket]]
- [[_COMMUNITY_Parking Detector WS Preview|Parking Detector WS Preview]]
- [[_COMMUNITY_InfoBoard & IoT WS Auth Tests|InfoBoard & IoT WS Auth Tests]]
- [[_COMMUNITY_User Validation Geofence Tests|User Validation Geofence Tests]]
- [[_COMMUNITY_Parking Detector MQTT|Parking Detector MQTT]]
- [[_COMMUNITY_Parking Detector MQTT Preview|Parking Detector MQTT Preview]]
- [[_COMMUNITY_FeedbackCategory & InfoBoard Controllers|FeedbackCategory & InfoBoard Controllers]]
- [[_COMMUNITY_Frontend Dependencies (npm)|Frontend Dependencies (npm)]]
- [[_COMMUNITY_User FAQ Module|User FAQ Module]]
- [[_COMMUNITY_IoT Device Model|IoT Device Model]]
- [[_COMMUNITY_Controller Tests (MapFAQ)|Controller Tests (Map/FAQ)]]
- [[_COMMUNITY_User Model|User Model]]
- [[_COMMUNITY_Park Subarea Model|Park Subarea Model]]
- [[_COMMUNITY_Profile Controller Tests (EN)|Profile Controller Tests (EN)]]
- [[_COMMUNITY_Mission Controller Validation|Mission Controller Validation]]
- [[_COMMUNITY_Feedback Category & Amenity Models|Feedback Category & Amenity Models]]
- [[_COMMUNITY_Profile Controller Tests (ID)|Profile Controller Tests (ID)]]
- [[_COMMUNITY_IoT Detection Tests|IoT Detection Tests]]
- [[_COMMUNITY_User Validation Controller|User Validation Controller]]
- [[_COMMUNITY_Park Subarea Controller|Park Subarea Controller]]
- [[_COMMUNITY_Mission & History Models|Mission & History Models]]
- [[_COMMUNITY_Composer Project Metadata|Composer Project Metadata]]
- [[_COMMUNITY_PHP Dependencies (composer)|PHP Dependencies (composer)]]
- [[_COMMUNITY_Profile Controller|Profile Controller]]
- [[_COMMUNITY_Community 35|Community 35]]
- [[_COMMUNITY_Community 36|Community 36]]
- [[_COMMUNITY_Community 37|Community 37]]
- [[_COMMUNITY_Community 38|Community 38]]
- [[_COMMUNITY_Community 39|Community 39]]
- [[_COMMUNITY_Community 40|Community 40]]
- [[_COMMUNITY_Community 41|Community 41]]
- [[_COMMUNITY_Community 42|Community 42]]
- [[_COMMUNITY_Community 43|Community 43]]
- [[_COMMUNITY_Community 44|Community 44]]
- [[_COMMUNITY_Community 45|Community 45]]
- [[_COMMUNITY_Community 46|Community 46]]
- [[_COMMUNITY_Community 47|Community 47]]
- [[_COMMUNITY_Community 48|Community 48]]
- [[_COMMUNITY_Community 49|Community 49]]
- [[_COMMUNITY_Community 50|Community 50]]
- [[_COMMUNITY_Community 51|Community 51]]
- [[_COMMUNITY_Community 52|Community 52]]
- [[_COMMUNITY_Community 53|Community 53]]
- [[_COMMUNITY_Community 54|Community 54]]
- [[_COMMUNITY_Community 55|Community 55]]
- [[_COMMUNITY_Community 56|Community 56]]
- [[_COMMUNITY_Community 57|Community 57]]
- [[_COMMUNITY_Community 58|Community 58]]
- [[_COMMUNITY_Community 59|Community 59]]
- [[_COMMUNITY_Community 60|Community 60]]
- [[_COMMUNITY_Community 61|Community 61]]
- [[_COMMUNITY_Community 62|Community 62]]
- [[_COMMUNITY_Community 63|Community 63]]
- [[_COMMUNITY_Community 64|Community 64]]
- [[_COMMUNITY_Community 65|Community 65]]
- [[_COMMUNITY_Community 66|Community 66]]
- [[_COMMUNITY_Community 67|Community 67]]
- [[_COMMUNITY_Community 68|Community 68]]
- [[_COMMUNITY_Community 69|Community 69]]
- [[_COMMUNITY_Community 70|Community 70]]
- [[_COMMUNITY_Community 71|Community 71]]
- [[_COMMUNITY_Community 72|Community 72]]
- [[_COMMUNITY_Community 73|Community 73]]
- [[_COMMUNITY_Community 74|Community 74]]
- [[_COMMUNITY_Community 75|Community 75]]
- [[_COMMUNITY_Community 76|Community 76]]
- [[_COMMUNITY_Community 77|Community 77]]
- [[_COMMUNITY_Community 78|Community 78]]
- [[_COMMUNITY_Community 86|Community 86]]
- [[_COMMUNITY_Community 113|Community 113]]
- [[_COMMUNITY_Community 114|Community 114]]
- [[_COMMUNITY_Community 115|Community 115]]
- [[_COMMUNITY_Community 116|Community 116]]
- [[_COMMUNITY_Community 135|Community 135]]
- [[_COMMUNITY_Community 166|Community 166]]
- [[_COMMUNITY_Community 167|Community 167]]
- [[_COMMUNITY_Community 168|Community 168]]
- [[_COMMUNITY_Community 169|Community 169]]
- [[_COMMUNITY_Community 170|Community 170]]
- [[_COMMUNITY_Community 171|Community 171]]

## God Nodes (most connected - your core abstractions)
1. `TestCase` - 92 edges
2. `Controller` - 68 edges
3. `ParkSubarea` - 60 edges
4. `Response` - 36 edges
5. `AuthControllerTest` - 34 edges
6. `Mission` - 29 edges
7. `AuthControllerTest` - 26 edges
8. `Validation` - 25 edges
9. `ValidationException` - 18 edges
10. `UserMission` - 14 edges

## Surprising Connections (you probably didn't know these)
- `API Encryption (RSA Hybrid)` --semantically_similar_to--> `Hybrid RSA/AES Payload Encryption`  [INFERRED] [semantically similar]
  README.md → docs/PROJECT_SUMMARY.md
- `Development/Runtime Environment Isolation` --references--> `Client-Server IoT Deployment Architecture`  [INFERRED]
  .agents/rules/environment_isolation.md → docs/PROJECT_SUMMARY.md
- `Database RBAC (Privilege Separation)` --conceptually_related_to--> `Production Security Checklist`  [INFERRED]
  README.md → docs/PROJECT_SUMMARY.md
- `RSA Key Generation Step` --conceptually_related_to--> `API Encryption (RSA Hybrid)`  [INFERRED]
  docs/INSTALLATION_MANUAL.md → README.md
- `Client-Server IoT Deployment Architecture` --references--> `App Service (Laravel/Reverb/Queue)`  [EXTRACTED]
  docs/PROJECT_SUMMARY.md → docker/docker-compose.yml

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Docker Compose Service Stack** — docker_compose_app_service, docker_compose_db_service, docker_compose_scheduler_service, docker_compose_logrotate_service, docker_compose_mosquitto_service [EXTRACTED 0.90]
- **IoT Parking Detection Pipeline** — project_summary_edge_iot_parking_detector, docker_compose_mosquitto_service, project_summary_hmac_shared_secret, requirements_python_iot_dependencies [INFERRED 0.80]
- **PoliSlot Three-Component Architecture** — docker_compose_app_service, project_summary_mobile_app_flutter, project_summary_edge_iot_parking_detector [INFERRED 0.85]

## Communities (175 total, 35 thin omitted)

### Community 0 - "Auth Controller & Login Flow"
Cohesion: 0.06
Nodes (25): AuthController, RewardController, SubareaCommentController, JsonResponse, MissionService, Request, HistoryService, JsonResponse (+17 more)

### Community 1 - "IoT Detection Controller"
Cohesion: 0.30
Nodes (4): JsonResponse, Request, View, IotDetectionController

### Community 2 - "IoT Broadcast Events"
Cohesion: 0.08
Nodes (18): Content, Envelope, Content, Envelope, Dispatchable, IotCommandSent, IotCountUpdated, IotDetectionReceived (+10 more)

### Community 3 - "Mission Controller & Service"
Cohesion: 0.09
Nodes (11): MissionController, JsonResponse, MissionService, HistoryService, Mission, MissionTest, UserMissionTest, MissionServiceTest (+3 more)

### Community 4 - "Feedback Module"
Cohesion: 0.08
Nodes (13): RedirectResponse, Request, BelongsTo, Factory, FeedbackTest, Feedback, Seeder, DatabaseSeeder (+5 more)

### Community 5 - "Backup & DB Commands"
Cohesion: 0.10
Nodes (14): HistoryService, RedirectResponse, Request, Command, BackupAuto, BackupClean, BackupDatabase, DbList (+6 more)

### Community 7 - "Deployment & Infrastructure"
Cohesion: 0.08
Nodes (32): Development Cycle Workflow, App Service (Laravel/Reverb/Queue), DB Service (MariaDB), Logrotate Sidecar Service, Mosquitto MQTT Broker Service, Scheduler Service (Laravel Cron), Cloudflare Tunnel Service (Optional), Docker Deploy Bundle (DockerHub Image) (+24 more)

### Community 8 - "Subarea Comment & Amenity Tests"
Cohesion: 0.09
Nodes (6): SubareaCommentControllerTest, ParkAmenityTest, ParkSubareaTest, SubareaCommentTest, ParkSubarea, ParkSubareaControllerTest

### Community 11 - "Parking Detector WebSocket"
Cohesion: 0.19
Nodes (15): CameraStream, detector_loop(), encrypt_image_aes(), fetch_remote_config(), generate_auth_signature(), generate_hmac_signature(), get_aes_key(), handle_command() (+7 more)

### Community 12 - "Parking Detector WS Preview"
Cohesion: 0.19
Nodes (15): CameraStream, encrypt_image_aes(), fetch_remote_config(), generate_auth_signature(), generate_hmac_signature(), get_aes_key(), handle_command(), is_inside_any_polygon() (+7 more)

### Community 13 - "InfoBoard & IoT WS Auth Tests"
Cohesion: 0.09
Nodes (8): FeedbackCategoryControllerTest, HistoryControllerTest, BaseTestCase, ExampleTest, ParkAreaTest, HistoryServiceTest, TestCase, ExampleTest

### Community 14 - "User Validation Geofence Tests"
Cohesion: 0.13
Nodes (4): UserValidationControllerTest, UserValidationTest, ValidationTest, Validation

### Community 15 - "Parking Detector MQTT"
Cohesion: 0.18
Nodes (13): CameraStream, encrypt_image_aes(), generate_hmac_signature(), get_aes_key(), is_inside_any_polygon(), is_inside_polygon(), load_local_config(), main() (+5 more)

### Community 16 - "Parking Detector MQTT Preview"
Cohesion: 0.18
Nodes (13): CameraStream, encrypt_image_aes(), generate_hmac_signature(), get_aes_key(), is_inside_any_polygon(), is_inside_polygon(), load_local_config(), main() (+5 more)

### Community 17 - "FeedbackCategory & InfoBoard Controllers"
Cohesion: 0.16
Nodes (10): FeedbackCategoryController, IotWsAuthController, JsonResponse, JsonResponse, Request, JsonResponse, AuthorizesRequests, BaseController (+2 more)

### Community 18 - "Frontend Dependencies (npm)"
Cohesion: 0.11
Nodes (17): devDependencies, axios, concurrently, laravel-echo, laravel-vite-plugin, pusher-js, tailwindcss, @tailwindcss/vite (+9 more)

### Community 19 - "User FAQ Module"
Cohesion: 0.20
Nodes (7): UserFaqController, JsonResponse, RedirectResponse, Request, BelongsTo, UserFaq, UserFaqController

### Community 20 - "IoT Device Model"
Cohesion: 0.17
Nodes (5): BelongsTo, HasMany, UserFactory, IotDevice, static

### Community 21 - "Controller Tests (Map/FAQ)"
Cohesion: 0.10
Nodes (5): FeedbackControllerTest, MapVisualizationControllerTest, RewardControllerTest, UserFaqControllerTest, WithoutMiddleware

### Community 22 - "User Model"
Cohesion: 0.26
Nodes (5): HasMany, Authenticatable, HasApiTokens, User, Notifiable

### Community 23 - "Park Subarea Model"
Cohesion: 0.27
Nodes (4): BelongsTo, HasMany, HasOne, ParkSubarea

### Community 25 - "Mission Controller Validation"
Cohesion: 0.42
Nodes (4): RedirectResponse, Request, ValidationException, MissionController

### Community 26 - "Feedback Category & Amenity Models"
Cohesion: 0.29
Nodes (5): HasMany, BelongsTo, Model, FeedbackCategory, ParkAmenity

### Community 29 - "User Validation Controller"
Cohesion: 0.36
Nodes (5): UserValidationController, HistoryService, JsonResponse, MissionService, Request

### Community 30 - "Park Subarea Controller"
Cohesion: 0.39
Nodes (4): JsonResponse, RedirectResponse, Request, ParkSubareaController

### Community 31 - "Mission & History Models"
Cohesion: 0.33
Nodes (5): HasMany, BelongsTo, HasFactory, Mission, UserHistory

### Community 32 - "Composer Project Metadata"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 33 - "PHP Dependencies (composer)"
Cohesion: 0.22
Nodes (9): require, fakerphp/faker, laravel/framework, laravel/reverb, laravel/sanctum, laravel/tinker, php, php-mqtt/laravel-client (+1 more)

### Community 34 - "Profile Controller"
Cohesion: 0.43
Nodes (4): ProfileController, JsonResponse, MissionService, Request

### Community 35 - "Community 35"
Cohesion: 0.06
Nodes (30): 1. Deployment Architecture, 2.1. Backend Server & Admin Dashboard (Via Docker - Recommended), 2.2. Backend Server (Instalasi Manual Lokal / XAMPP), 2.3. PoliSlot Mobile App (Flutter), 2.4. Perangkat Edge IoT (Parking Detector), 2. Installation Procedure, 3. Security Checklist, API & Komunikasi Data (+22 more)

### Community 36 - "Community 36"
Cohesion: 0.07
Nodes (27): 10. Re-up Container, 11. Buat port forward local dan firewall rule - Optional untuk instalasi pada WSL, 1. Konfigurasi Environment (.env), 2. Generate RSA Keys (Di Root), 3. Verifikasi Credential, 4. Menjalankan Container, 5. Generate Application Key, 6. Migrasi Database (+19 more)

### Community 37 - "Community 37"
Cohesion: 0.46
Nodes (3): RedirectResponse, Request, FeedbackCategoryController

### Community 38 - "Community 38"
Cohesion: 0.46
Nodes (3): RedirectResponse, Request, InfoBoardController

### Community 39 - "Community 39"
Cohesion: 0.50
Nodes (3): RedirectResponse, Request, RewardController

### Community 40 - "Community 40"
Cohesion: 0.25
Nodes (8): Consistent API Response Format, DB Transaction & Error Handling, Eloquent Model Convention, Fat Model Skinny Controller, N+1 Query Prevention (Eager Loading), Namespace Import Convention, PHP & Laravel Coding Standards, PHPDoc Documentation Standard

### Community 42 - "Community 42"
Cohesion: 0.29
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
Cohesion: 0.29
Nodes (7): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, test

### Community 51 - "Community 51"
Cohesion: 0.53
Nodes (3): MapVisualizationController, JsonResponse, Request

### Community 52 - "Community 52"
Cohesion: 0.53
Nodes (3): RedirectResponse, Request, ValidationController

### Community 56 - "Community 56"
Cohesion: 0.08
Nodes (7): InfoBoardControllerTest, IotWebhookControllerTest, IotWsAuthControllerTest, MissionControllerTest, RewardTest, UserRewardTest, RefreshDatabase

### Community 57 - "Community 57"
Cohesion: 0.24
Nodes (3): ApiEncryptionTest, RBACTest, Response

### Community 58 - "Community 58"
Cohesion: 0.53
Nodes (3): IotDetectionController, JsonResponse, Request

### Community 59 - "Community 59"
Cohesion: 0.60
Nodes (3): FeedbackController, JsonResponse, Request

### Community 60 - "Community 60"
Cohesion: 0.60
Nodes (3): HistoryController, JsonResponse, Request

### Community 61 - "Community 61"
Cohesion: 0.39
Nodes (4): JsonResponse, Request, View, DashboardController

### Community 62 - "Community 62"
Cohesion: 0.53
Nodes (3): IotWebhookController, JsonResponse, Request

### Community 67 - "Community 67"
Cohesion: 0.60
Nodes (3): Closure, NotCurrentPassword, ValidationRule

### Community 68 - "Community 68"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 69 - "Community 69"
Cohesion: 0.53
Nodes (3): JsonResponse, Request, ParkAmenityController

### Community 70 - "Community 70"
Cohesion: 0.33
Nodes (5): 1. Import (Namespace / `use` Statement), 2. PHPDoc (Komentar Fungsi & Class), 3. Konvensi Penulisan Model (Eloquent), 4. Best Practices Laravel Lainnya, Aturan Standar Penulisan Kode PHP & Laravel (Coding Standards)

### Community 72 - "Community 72"
Cohesion: 0.60
Nodes (3): Closure, Request, RBAC

### Community 77 - "Community 77"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 78 - "Community 78"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Knowledge Gaps
- **127 isolated node(s):** `HistoryService`, `$schema`, `name`, `type`, `description` (+122 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **35 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `TestCase` connect `InfoBoard & IoT WS Auth Tests` to `Mission Controller & Service`, `Feedback Module`, `Auth Controller Tests (EN)`, `Subarea Comment & Amenity Tests`, `Auth Controller Tests (ID)`, `Feedback/History/Mission Tests`, `User Validation Geofence Tests`, `Controller Tests (Map/FAQ)`, `Profile Controller Tests (EN)`, `Profile Controller Tests (ID)`, `IoT Detection Tests`, `Community 47`, `Community 48`, `Community 49`, `Community 50`, `Community 54`, `Community 55`, `Community 56`, `Community 57`, `Community 71`?**
  _High betweenness centrality (0.110) - this node is a cross-community bridge._
- **Why does `ParkSubarea` connect `Subarea Comment & Amenity Tests` to `Auth Controller & Login Flow`, `IoT Broadcast Events`, `User Validation Geofence Tests`, `Community 61`, `Controller Tests (Map/FAQ)`, `Community 56`, `IoT Detection Tests`, `User Validation Controller`, `Park Subarea Controller`?**
  _High betweenness centrality (0.092) - this node is a cross-community bridge._
- **Why does `Controller` connect `FeedbackCategory & InfoBoard Controllers` to `Auth Controller & Login Flow`, `IoT Detection Controller`, `Mission Controller & Service`, `Feedback Module`, `Backup & DB Commands`, `User FAQ Module`, `Mission Controller Validation`, `User Validation Controller`, `Park Subarea Controller`, `Profile Controller`, `Community 37`, `Community 38`, `Community 167`, `Community 39`, `Community 43`, `Community 51`, `Community 52`, `Community 58`, `Community 59`, `Community 60`, `Community 61`, `Community 62`, `Community 69`?**
  _High betweenness centrality (0.072) - this node is a cross-community bridge._
- **Are the 34 inferred relationships involving `Response` (e.g. with `.receiveConfigQuery()` and `.receiveCount()`) actually correct?**
  _`Response` has 34 INFERRED edges - model-reasoned connections that need verification._
- **What connects `HistoryService`, `$schema`, `name` to the rest of the system?**
  _134 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Auth Controller & Login Flow` be split into smaller, more focused modules?**
  _Cohesion score 0.05997778600518327 - nodes in this community are weakly interconnected._
- **Should `IoT Broadcast Events` be split into smaller, more focused modules?**
  _Cohesion score 0.0792156862745098 - nodes in this community are weakly interconnected._