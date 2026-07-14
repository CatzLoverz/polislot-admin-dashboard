# Graph Report - C:/laragon/www/polislot-admin-dashboard  (2026-07-14)

## Corpus Check
- 246 files · ~87,026 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1181 nodes · 2462 edges · 145 communities (127 shown, 18 thin omitted)
- Extraction: 81% EXTRACTED · 19% INFERRED · 0% AMBIGUOUS · INFERRED: 472 edges (avg confidence: 0.79)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Core Infrastructure
- IoT Integration
- Documentation
- Community 3
- Community 4
- Community 5
- Community 6
- Community 7
- Community 8
- Community 9
- Community 10
- Community 11
- Community 12
- Community 13
- Community 14
- Community 15
- Community 16
- Community 17
- Community 18
- Community 19
- Community 20
- Community 21
- Community 22
- Community 23
- Community 24
- Community 25
- Community 26
- Community 27
- Community 28
- Community 29
- Community 30
- Community 31
- Community 32
- Community 33
- Community 34
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
- Community 47
- Community 48
- Community 49
- Community 50
- Community 51
- Community 52
- Community 53
- Community 54
- Community 55
- Community 56
- Community 90
- Community 91
- Community 92
- Community 93
- Community 107
- Community 114
- Community 115

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
- `main()` --indirect_call--> `start_websocket_thread()`  [INFERRED]
  python/parking_detector_ws.py → python/parking_detector_ws_preview.py
- `AuthController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/AuthController.php → app/Http/Controllers/Controller.php
- `IotDetectionController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/IotDetectionController.php → app/Http/Controllers/Controller.php
- `IotWebhookController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/IotWebhookController.php → app/Http/Controllers/Controller.php
- `IotWsAuthController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/Api/IotWsAuthController.php → app/Http/Controllers/Controller.php

## Import Cycles
- None detected.

## Communities (145 total, 18 thin omitted)

### Community 0 - "Core Infrastructure"
Cohesion: 0.06
Nodes (21): error(), logInfo(), AuthController, JsonResponse, Request, JsonResponse, Request, ProfileController (+13 more)

### Community 1 - "IoT Integration"
Cohesion: 0.05
Nodes (35): FeedbackCategoryController, JsonResponse, FeedbackController, JsonResponse, Request, HistoryController, JsonResponse, Request (+27 more)

### Community 2 - "Documentation"
Cohesion: 0.06
Nodes (21): FeedbackCategoryController, RedirectResponse, Request, FeedbackController, RedirectResponse, Request, Feedback, BelongsTo (+13 more)

### Community 3 - "Community 3"
Cohesion: 0.09
Nodes (16): BackupAuto, BackupClean, BackupDatabase, DbList, DbRestore, SetupDatabaseAdmin, SetupDatabaseUser, DashboardController (+8 more)

### Community 4 - "Community 4"
Cohesion: 0.12
Nodes (8): RefreshDatabase, FeedbackCategoryControllerTest, FeedbackControllerTest, HistoryControllerTest, InfoBoardControllerTest, MapVisualizationControllerTest, UserFaqControllerTest, WithoutMiddleware

### Community 5 - "Community 5"
Cohesion: 0.09
Nodes (11): RedirectResponse, Request, RewardController, HasMany, Reward, BelongsTo, UserReward, RewardControllerTest (+3 more)

### Community 6 - "Community 6"
Cohesion: 0.13
Nodes (10): IotCommandSent, IotCountUpdated, IotDetectionReceived, IotDeviceStatusChanged, IotThresholdUpdated, SubareaStatusUpdated, Dispatchable, InteractsWithSockets (+2 more)

### Community 8 - "Community 8"
Cohesion: 0.11
Nodes (6): BaseTestCase, ExampleTest, IotWebhookControllerTest, IotWsAuthControllerTest, TestCase, ExampleTest

### Community 9 - "Community 9"
Cohesion: 0.11
Nodes (6): HasMany, User, Authenticatable, HasApiTokens, Notifiable, ProfileControllerTest

### Community 10 - "Community 10"
Cohesion: 0.08
Nodes (25): axios, concurrently, laravel-echo, laravel-vite-plugin, devDependencies, axios, concurrently, laravel-echo (+17 more)

### Community 12 - "Community 12"
Cohesion: 0.14
Nodes (7): InfoBoardController, RedirectResponse, Request, InfoBoard, BelongsTo, InfoBoardControllerTest, InfoBoardTest

### Community 13 - "Community 13"
Cohesion: 0.13
Nodes (10): ApiEncryption, Closure, Request, Closure, Request, RBAC, TrustProxies, Middleware (+2 more)

### Community 14 - "Community 14"
Cohesion: 0.13
Nodes (5): Mission, MissionControllerTest, MissionControllerTest, MissionTest, MissionServiceTest

### Community 15 - "Community 15"
Cohesion: 0.15
Nodes (9): JsonResponse, UserFaqController, RedirectResponse, Request, UserFaqController, HasMany, BelongsTo, UserFaq (+1 more)

### Community 16 - "Community 16"
Cohesion: 0.19
Nodes (15): CameraStream, encrypt_image_aes(), generate_hmac_signature(), get_aes_key(), is_bbox_in_any_polygon(), is_bbox_in_polygon(), load_local_config(), main() (+7 more)

### Community 17 - "Community 17"
Cohesion: 0.19
Nodes (15): CameraStream, encrypt_image_aes(), generate_hmac_signature(), get_aes_key(), is_bbox_in_any_polygon(), is_bbox_in_polygon(), load_local_config(), main() (+7 more)

### Community 18 - "Community 18"
Cohesion: 0.19
Nodes (15): CameraStream, detector_loop(), encrypt_image_aes(), fetch_remote_config(), generate_auth_signature(), generate_hmac_signature(), get_aes_key(), handle_command() (+7 more)

### Community 19 - "Community 19"
Cohesion: 0.19
Nodes (15): CameraStream, encrypt_image_aes(), fetch_remote_config(), generate_auth_signature(), generate_hmac_signature(), get_aes_key(), handle_command(), is_bbox_in_any_polygon() (+7 more)

### Community 20 - "Community 20"
Cohesion: 0.15
Nodes (8): IotWsAuthController, JsonResponse, Request, JsonResponse, Request, UserValidationController, BelongsTo, HasMany

### Community 21 - "Community 21"
Cohesion: 0.15
Nodes (4): ParkSubarea, HasMany, SubareaCommentControllerTest, ParkSubareaControllerTest

### Community 22 - "Community 22"
Cohesion: 0.23
Nodes (6): JsonResponse, Request, SubareaCommentController, BelongsTo, SubareaComment, SubareaCommentTest

### Community 23 - "Community 23"
Cohesion: 0.17
Nodes (8): LoginNotificationMail, Content, Envelope, Content, Envelope, SendOtpMail, Mailable, Queueable

### Community 24 - "Community 24"
Cohesion: 0.26
Nodes (5): IotDetectionController, JsonResponse, Request, IotDevice, ScrubAndTraceProcessorTest

### Community 25 - "Community 25"
Cohesion: 0.20
Nodes (5): MissionController, JsonResponse, BelongsTo, UserMission, UserMissionTest

### Community 26 - "Community 26"
Cohesion: 0.30
Nodes (4): IotDetectionController, JsonResponse, Request, View

### Community 27 - "Community 27"
Cohesion: 0.18
Nodes (4): ParkArea, HasMany, ParkAreaTest, ParkSubareaTest

### Community 28 - "Community 28"
Cohesion: 0.23
Nodes (3): Validation, UserValidationControllerTest, ValidationTest

### Community 29 - "Community 29"
Cohesion: 0.14
Nodes (14): scripts, dev, post-autoload-dump, post-root-package-install, post-update-cmd, test, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump (+6 more)

### Community 30 - "Community 30"
Cohesion: 0.24
Nodes (3): CustomizeFormatter, ScrubAndTraceProcessor, ProcessorInterface

### Community 31 - "Community 31"
Cohesion: 0.18
Nodes (10): description, extra, laravel, dont-discover, license, minimum-stability, name, prefer-stable (+2 more)

### Community 33 - "Community 33"
Cohesion: 0.29
Nodes (3): BelongsTo, UserValidation, UserValidationTest

### Community 34 - "Community 34"
Cohesion: 0.33
Nodes (4): MqttListenerCommand, IotCapture, BelongsTo, HasFactory

### Community 35 - "Community 35"
Cohesion: 0.39
Nodes (4): ParkSubareaController, JsonResponse, RedirectResponse, Request

### Community 36 - "Community 36"
Cohesion: 0.33
Nodes (3): ParkAmenity, BelongsTo, ParkAmenityTest

### Community 37 - "Community 37"
Cohesion: 0.31
Nodes (3): BelongsTo, UserHistory, UserHistoryTest

### Community 38 - "Community 38"
Cohesion: 0.31
Nodes (3): AppServiceProvider, AuthServiceProvider, ServiceProvider

### Community 39 - "Community 39"
Cohesion: 0.22
Nodes (9): require, fakerphp/faker, laravel/framework, laravel/reverb, laravel/sanctum, laravel/tinker, php, php-mqtt/laravel-client (+1 more)

### Community 41 - "Community 41"
Cohesion: 0.25
Nodes (8): Consistent API Response Format, DB Transaction & Error Handling, Eloquent Model Convention, Fat Model Skinny Controller, N+1 Query Prevention (Eager Loading), Namespace Import Convention, PHP & Laravel Coding Standards, PHPDoc Documentation Standard

### Community 42 - "Community 42"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 43 - "Community 43"
Cohesion: 0.29
Nodes (7): require-dev, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, phpunit/phpunit

### Community 45 - "Community 45"
Cohesion: 0.53
Nodes (3): IotWebhookController, JsonResponse, Request

### Community 50 - "Community 50"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 53 - "Community 53"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 54 - "Community 54"
Cohesion: 0.67
Nodes (3): Development/Runtime Environment Isolation, Docker Runtime Read-Only Policy, Development Cycle Workflow

### Community 55 - "Community 55"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 56 - "Community 56"
Cohesion: 0.67
Nodes (3): keywords, framework, laravel

## Knowledge Gaps
- **70 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+65 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **18 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `Community 9` to `Core Infrastructure`, `Documentation`, `Community 3`, `Community 4`, `Community 5`, `Community 7`, `Community 11`, `Community 12`, `Community 14`, `Community 15`, `Community 21`, `Community 22`, `Community 25`, `Community 28`, `Community 32`, `Community 33`, `Community 34`, `Community 37`, `Community 40`, `Community 46`, `Community 47`, `Community 51`, `Community 52`?**
  _High betweenness centrality (0.130) - this node is a cross-community bridge._
- **Why does `Controller` connect `IoT Integration` to `Core Infrastructure`, `Documentation`, `Community 3`, `Community 35`, `Community 5`, `Community 12`, `Community 45`, `Community 15`, `Community 20`, `Community 22`, `Community 24`, `Community 25`, `Community 26`?**
  _High betweenness centrality (0.061) - this node is a cross-community bridge._
- **Why does `TestCase` connect `Community 8` to `Documentation`, `Community 4`, `Community 5`, `Community 7`, `Community 9`, `Community 11`, `Community 12`, `Community 13`, `Community 14`, `Community 20`, `Community 21`, `Community 22`, `Community 24`, `Community 25`, `Community 27`, `Community 28`, `Community 32`, `Community 33`, `Community 36`, `Community 37`, `Community 40`, `Community 44`, `Community 46`, `Community 47`, `Community 51`, `Community 52`?**
  _High betweenness centrality (0.054) - this node is a cross-community bridge._
- **Are the 149 inferred relationships involving `User` (e.g. with `.forgotPasswordOtpResend()` and `.forgotPasswordOtpVerify()`) actually correct?**
  _`User` has 149 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _70 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Core Infrastructure` be split into smaller, more focused modules?**
  _Cohesion score 0.060764587525150904 - nodes in this community are weakly interconnected._
- **Should `IoT Integration` be split into smaller, more focused modules?**
  _Cohesion score 0.050921861281826165 - nodes in this community are weakly interconnected._