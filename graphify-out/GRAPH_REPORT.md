# Graph Report - polislot-admin-dashboard  (2026-06-16)

## Corpus Check
- 233 files · ~76,675 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1237 nodes · 1940 edges · 162 communities (124 shown, 38 thin omitted)
- Extraction: 91% EXTRACTED · 9% INFERRED · 0% AMBIGUOUS · INFERRED: 180 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `c8074640`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- [[_COMMUNITY_Community 0|Community 0]]
- [[_COMMUNITY_Community 1|Community 1]]
- [[_COMMUNITY_Community 2|Community 2]]
- [[_COMMUNITY_Community 3|Community 3]]
- [[_COMMUNITY_Community 4|Community 4]]
- [[_COMMUNITY_Community 5|Community 5]]
- [[_COMMUNITY_Community 6|Community 6]]
- [[_COMMUNITY_Community 7|Community 7]]
- [[_COMMUNITY_Community 8|Community 8]]
- [[_COMMUNITY_Community 9|Community 9]]
- [[_COMMUNITY_Community 10|Community 10]]
- [[_COMMUNITY_Community 11|Community 11]]
- [[_COMMUNITY_Community 12|Community 12]]
- [[_COMMUNITY_Community 13|Community 13]]
- [[_COMMUNITY_Community 14|Community 14]]
- [[_COMMUNITY_Community 15|Community 15]]
- [[_COMMUNITY_Community 16|Community 16]]
- [[_COMMUNITY_Community 17|Community 17]]
- [[_COMMUNITY_Community 18|Community 18]]
- [[_COMMUNITY_Community 19|Community 19]]
- [[_COMMUNITY_Community 20|Community 20]]
- [[_COMMUNITY_Community 21|Community 21]]
- [[_COMMUNITY_Community 22|Community 22]]
- [[_COMMUNITY_Community 23|Community 23]]
- [[_COMMUNITY_Community 24|Community 24]]
- [[_COMMUNITY_Community 25|Community 25]]
- [[_COMMUNITY_Community 26|Community 26]]
- [[_COMMUNITY_Community 27|Community 27]]
- [[_COMMUNITY_Community 28|Community 28]]
- [[_COMMUNITY_Community 29|Community 29]]
- [[_COMMUNITY_Community 30|Community 30]]
- [[_COMMUNITY_Community 31|Community 31]]
- [[_COMMUNITY_Community 32|Community 32]]
- [[_COMMUNITY_Community 33|Community 33]]
- [[_COMMUNITY_Community 34|Community 34]]
- [[_COMMUNITY_Community 35|Community 35]]
- [[_COMMUNITY_Community 36|Community 36]]
- [[_COMMUNITY_Community 37|Community 37]]
- [[_COMMUNITY_Community 39|Community 39]]
- [[_COMMUNITY_Community 40|Community 40]]
- [[_COMMUNITY_Community 41|Community 41]]
- [[_COMMUNITY_Community 42|Community 42]]
- [[_COMMUNITY_Community 43|Community 43]]
- [[_COMMUNITY_Community 44|Community 44]]
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
- [[_COMMUNITY_Community 57|Community 57]]
- [[_COMMUNITY_Community 58|Community 58]]
- [[_COMMUNITY_Community 60|Community 60]]
- [[_COMMUNITY_Community 61|Community 61]]
- [[_COMMUNITY_Community 63|Community 63]]
- [[_COMMUNITY_Community 64|Community 64]]
- [[_COMMUNITY_Community 65|Community 65]]
- [[_COMMUNITY_Community 67|Community 67]]
- [[_COMMUNITY_Community 68|Community 68]]
- [[_COMMUNITY_Community 69|Community 69]]
- [[_COMMUNITY_Community 70|Community 70]]
- [[_COMMUNITY_Community 71|Community 71]]
- [[_COMMUNITY_Community 74|Community 74]]
- [[_COMMUNITY_Community 75|Community 75]]
- [[_COMMUNITY_Community 77|Community 77]]
- [[_COMMUNITY_Community 78|Community 78]]
- [[_COMMUNITY_Community 81|Community 81]]
- [[_COMMUNITY_Community 90|Community 90]]
- [[_COMMUNITY_Community 91|Community 91]]
- [[_COMMUNITY_Community 92|Community 92]]
- [[_COMMUNITY_Community 95|Community 95]]
- [[_COMMUNITY_Community 129|Community 129]]
- [[_COMMUNITY_Community 146|Community 146]]
- [[_COMMUNITY_Community 147|Community 147]]
- [[_COMMUNITY_Community 167|Community 167]]
- [[_COMMUNITY_Community 179|Community 179]]
- [[_COMMUNITY_Community 181|Community 181]]
- [[_COMMUNITY_Community 182|Community 182]]

## God Nodes (most connected - your core abstractions)
1. `TestCase` - 92 edges
2. `Controller` - 68 edges
3. `Response` - 36 edges
4. `AuthControllerTest` - 34 edges
5. `AuthControllerTest` - 26 edges
6. `Validation` - 24 edges
7. `ParkSubarea` - 18 edges
8. `ValidationException` - 16 edges
9. `AuthController` - 13 edges
10. `AuthController` - 12 edges

## Surprising Connections (you probably didn't know these)
- `ApiEncryption` --rationale_for--> `API Encryption (RSA)`  [INFERRED]
  app/Http/Middleware/ApiEncryption.php → README.md
- `BackupAuto` --rationale_for--> `Database Backup & Restore`  [INFERRED]
  app/Console/Commands/BackupAuto.php → README.md
- `BackupClean` --rationale_for--> `Database Backup & Restore`  [INFERRED]
  app/Console/Commands/BackupClean.php → README.md
- `BackupDatabase` --rationale_for--> `Database Backup & Restore`  [INFERRED]
  app/Console/Commands/BackupDatabase.php → README.md
- `MqttListenerCommand` --conceptually_related_to--> `Mosquitto MQTT Broker`  [INFERRED]
  app/Console/Commands/MqttListenerCommand.php → docker/docker-compose.yml

## Import Cycles
- None detected.

## Communities (162 total, 38 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.09
Nodes (12): UserValidationController, UserValidationControllerTest, HistoryService, JsonResponse, MissionService, Request, RedirectResponse, Request (+4 more)

### Community 1 - "Community 1"
Cohesion: 0.06
Nodes (31): HistoryService, RedirectResponse, Request, Command, BackupAuto, BackupClean, BackupDatabase, DbList (+23 more)

### Community 4 - "Community 4"
Cohesion: 0.06
Nodes (24): IotDetectionController, JsonResponse, Request, JsonResponse, Request, View, JsonResponse, Request (+16 more)

### Community 6 - "Community 6"
Cohesion: 0.20
Nodes (15): CameraStream, detector_loop(), encrypt_image_aes(), fetch_remote_config(), generate_auth_signature(), generate_hmac_signature(), get_aes_key(), handle_command() (+7 more)

### Community 7 - "Community 7"
Cohesion: 0.20
Nodes (15): CameraStream, encrypt_image_aes(), fetch_remote_config(), generate_auth_signature(), generate_hmac_signature(), get_aes_key(), handle_command(), is_inside_any_polygon() (+7 more)

### Community 8 - "Community 8"
Cohesion: 0.22
Nodes (13): CameraStream, encrypt_image_aes(), generate_hmac_signature(), get_aes_key(), is_inside_any_polygon(), is_inside_polygon(), load_local_config(), main() (+5 more)

### Community 9 - "Community 9"
Cohesion: 0.22
Nodes (13): CameraStream, encrypt_image_aes(), generate_hmac_signature(), get_aes_key(), is_inside_any_polygon(), is_inside_polygon(), load_local_config(), main() (+5 more)

### Community 10 - "Community 10"
Cohesion: 0.46
Nodes (3): RedirectResponse, Request, MissionController

### Community 11 - "Community 11"
Cohesion: 0.11
Nodes (17): devDependencies, axios, concurrently, laravel-echo, laravel-vite-plugin, pusher-js, tailwindcss, @tailwindcss/vite (+9 more)

### Community 12 - "Community 12"
Cohesion: 0.20
Nodes (7): UserFaqController, JsonResponse, RedirectResponse, Request, BelongsTo, UserFaq, UserFaqController

### Community 13 - "Community 13"
Cohesion: 0.25
Nodes (7): import_classes, import_constants, import_functions, preset, rules, fully_qualified_strict_types, global_namespace_import

### Community 16 - "Community 16"
Cohesion: 0.09
Nodes (10): FeedbackCategoryControllerTest, FeedbackControllerTest, HistoryControllerTest, InfoBoardControllerTest, MapVisualizationControllerTest, MissionControllerTest, RewardControllerTest, UserFaqControllerTest (+2 more)

### Community 17 - "Community 17"
Cohesion: 0.09
Nodes (4): IotWebhookControllerTest, IotWsAuthControllerTest, SubareaCommentControllerTest, ParkSubarea

### Community 19 - "Community 19"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 20 - "Community 20"
Cohesion: 0.22
Nodes (9): require, fakerphp/faker, laravel/framework, laravel/reverb, laravel/sanctum, laravel/tinker, php, php-mqtt/laravel-client (+1 more)

### Community 21 - "Community 21"
Cohesion: 0.26
Nodes (5): HasMany, Authenticatable, HasApiTokens, User, Notifiable

### Community 22 - "Community 22"
Cohesion: 0.27
Nodes (4): BelongsTo, HasMany, HasOne, ParkSubarea

### Community 23 - "Community 23"
Cohesion: 0.11
Nodes (9): MissionController, JsonResponse, MissionService, HistoryService, Mission, UserMissionTest, MissionServiceTest, MissionService (+1 more)

### Community 25 - "Community 25"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 26 - "Community 26"
Cohesion: 0.29
Nodes (7): require-dev, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, phpunit/phpunit

### Community 27 - "Community 27"
Cohesion: 0.29
Nodes (7): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, test

### Community 28 - "Community 28"
Cohesion: 0.29
Nodes (3): AppServiceProvider, AuthServiceProvider, ServiceProvider

### Community 29 - "Community 29"
Cohesion: 0.05
Nodes (25): AuthController, RewardController, SubareaCommentController, JsonResponse, MissionService, Request, HistoryService, JsonResponse (+17 more)

### Community 31 - "Community 31"
Cohesion: 0.46
Nodes (3): RedirectResponse, Request, InfoBoardController

### Community 32 - "Community 32"
Cohesion: 0.21
Nodes (9): FeedbackController, JsonResponse, Request, JsonResponse, AuthorizesRequests, BaseController, Controller, ValidatesRequests (+1 more)

### Community 33 - "Community 33"
Cohesion: 0.09
Nodes (16): Content, Dispatchable, Envelope, IotCommandSent, IotCountUpdated, IotDetectionReceived, IotDeviceStatusChanged, IotThresholdUpdated (+8 more)

### Community 35 - "Community 35"
Cohesion: 0.07
Nodes (15): RedirectResponse, Request, BelongsTo, UserFactory, Factory, FeedbackTest, Feedback, Seeder (+7 more)

### Community 39 - "Community 39"
Cohesion: 0.46
Nodes (3): RedirectResponse, Request, FeedbackCategoryController

### Community 40 - "Community 40"
Cohesion: 0.53
Nodes (3): IotWebhookController, JsonResponse, Request

### Community 47 - "Community 47"
Cohesion: 0.43
Nodes (4): RedirectResponse, Request, View, ProfileController

### Community 49 - "Community 49"
Cohesion: 0.50
Nodes (3): RedirectResponse, Request, RewardController

### Community 52 - "Community 52"
Cohesion: 0.43
Nodes (4): ProfileController, JsonResponse, MissionService, Request

### Community 53 - "Community 53"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 54 - "Community 54"
Cohesion: 0.60
Nodes (3): HistoryController, JsonResponse, Request

### Community 60 - "Community 60"
Cohesion: 0.11
Nodes (6): BaseTestCase, ExampleTest, ParkAreaTest, RewardTest, TestCase, ExampleTest

### Community 67 - "Community 67"
Cohesion: 0.33
Nodes (5): 1. Import (Namespace / `use` Statement), 2. PHPDoc (Komentar Fungsi & Class), 3. Konvensi Penulisan Model (Eloquent), 4. Best Practices Laravel Lainnya, Aturan Standar Penulisan Kode PHP & Laravel (Coding Standards)

### Community 68 - "Community 68"
Cohesion: 0.53
Nodes (3): MapVisualizationController, JsonResponse, Request

### Community 69 - "Community 69"
Cohesion: 0.60
Nodes (3): IotWsAuthController, JsonResponse, Request

### Community 70 - "Community 70"
Cohesion: 0.05
Nodes (27): HasMany, BelongsTo, BelongsTo, HasMany, BelongsTo, HasMany, HasMany, BelongsTo (+19 more)

### Community 78 - "Community 78"
Cohesion: 0.06
Nodes (32): 10. Atur Ulang Environment (.env) - PENTING, 11. Re-up Container, 1. Konfigurasi Environment (.env), 2. Atur docker-compose.yml, 3. Generate RSA Keys (Di Root), 4. Verifikasi Credential, 5. Menjalankan Container, 6. Generate Application Key (+24 more)

### Community 81 - "Community 81"
Cohesion: 0.07
Nodes (27): 1. Konfigurasi Environment (.env), 2. Generate RSA Keys (Wajib), 3. Instalasi Dependency, 4. Generate Application Key, 5. Migrasi Database & Seeding, 6. Setup Database Roles (RBAC), 7. Atur Ulang Environment (.env) - PENTING, 8. Jalankan Aplikasi (+19 more)

### Community 91 - "Community 91"
Cohesion: 0.67
Nodes (3): autoload-dev, psr-4, Tests\\

### Community 92 - "Community 92"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 95 - "Community 95"
Cohesion: 0.24
Nodes (7): Closure, Request, Closure, Closure, ApiEncryption, NotCurrentPassword, ValidationRule

## Knowledge Gaps
- **112 isolated node(s):** `BeforeTool`, `$schema`, `name`, `type`, `description` (+107 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **38 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `TestCase` connect `Community 60` to `Community 0`, `Community 2`, `Community 3`, `Community 4`, `Community 5`, `Community 15`, `Community 16`, `Community 17`, `Community 18`, `Community 23`, `Community 30`, `Community 34`, `Community 35`, `Community 36`, `Community 41`, `Community 42`, `Community 43`, `Community 44`, `Community 46`, `Community 48`, `Community 50`, `Community 51`, `Community 55`, `Community 61`, `Community 63`, `Community 64`, `Community 65`, `Community 70`?**
  _High betweenness centrality (0.133) - this node is a cross-community bridge._
- **Why does `ParkSubarea` connect `Community 17` to `Community 0`, `Community 33`, `Community 64`, `Community 65`, `Community 4`, `Community 15`, `Community 16`, `Community 29`, `Community 30`, `Community 63`?**
  _High betweenness centrality (0.084) - this node is a cross-community bridge._
- **Why does `Controller` connect `Community 32` to `Community 0`, `Community 1`, `Community 4`, `Community 10`, `Community 12`, `Community 23`, `Community 29`, `Community 31`, `Community 35`, `Community 39`, `Community 40`, `Community 47`, `Community 49`, `Community 52`, `Community 54`, `Community 57`, `Community 68`, `Community 69`, `Community 75`?**
  _High betweenness centrality (0.064) - this node is a cross-community bridge._
- **Are the 34 inferred relationships involving `Response` (e.g. with `.receiveConfigQuery()` and `.receiveCount()`) actually correct?**
  _`Response` has 34 INFERRED edges - model-reasoned connections that need verification._
- **What connects `BeforeTool`, `$schema`, `name` to the rest of the system?**
  _116 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Community 0` be split into smaller, more focused modules?**
  _Cohesion score 0.08907563025210084 - nodes in this community are weakly interconnected._
- **Should `Community 1` be split into smaller, more focused modules?**
  _Cohesion score 0.06108597285067873 - nodes in this community are weakly interconnected._