# Graph Report - polislot-admin-dashboard  (2026-06-10)

## Corpus Check
- 203 files · ~72,377 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1051 nodes · 1453 edges · 156 communities (103 shown, 53 thin omitted)
- Extraction: 91% EXTRACTED · 9% INFERRED · 0% AMBIGUOUS · INFERRED: 127 edges (avg confidence: 0.81)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `cc90eea8`
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
- [[_COMMUNITY_Community 66|Community 66]]
- [[_COMMUNITY_Community 68|Community 68]]
- [[_COMMUNITY_Community 70|Community 70]]
- [[_COMMUNITY_Community 71|Community 71]]
- [[_COMMUNITY_Community 72|Community 72]]
- [[_COMMUNITY_Community 73|Community 73]]
- [[_COMMUNITY_Community 74|Community 74]]
- [[_COMMUNITY_Community 76|Community 76]]
- [[_COMMUNITY_Community 78|Community 78]]
- [[_COMMUNITY_Community 79|Community 79]]
- [[_COMMUNITY_Community 80|Community 80]]
- [[_COMMUNITY_Community 81|Community 81]]
- [[_COMMUNITY_Community 82|Community 82]]
- [[_COMMUNITY_Community 91|Community 91]]
- [[_COMMUNITY_Community 92|Community 92]]
- [[_COMMUNITY_Community 95|Community 95]]
- [[_COMMUNITY_Community 112|Community 112]]
- [[_COMMUNITY_Community 114|Community 114]]
- [[_COMMUNITY_Community 116|Community 116]]
- [[_COMMUNITY_Community 120|Community 120]]
- [[_COMMUNITY_Community 121|Community 121]]
- [[_COMMUNITY_Community 123|Community 123]]
- [[_COMMUNITY_Community 167|Community 167]]
- [[_COMMUNITY_Community 181|Community 181]]
- [[_COMMUNITY_Community 182|Community 182]]

## God Nodes (most connected - your core abstractions)
1. `Controller` - 65 edges
2. `Request` - 38 edges
3. `TestCase` - 35 edges
4. `AuthControllerTest` - 31 edges
5. `AuthControllerTest` - 24 edges
6. `Validation` - 22 edges
7. `AuthController` - 13 edges
8. `ParkSubarea` - 12 edges
9. `AuthController` - 12 edges
10. `Polislot Admin Dashboard` - 12 edges

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

## Communities (156 total, 53 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.09
Nodes (8): UserValidationControllerTest, Request, UserValidationTest, ValidationTest, Validation, DatabaseSeeder, ValidationSeeder, ValidationController

### Community 1 - "Community 1"
Cohesion: 0.05
Nodes (28): HistoryService, Request, Command, BackupAuto, BackupClean, BackupDatabase, DbList, DbRestore (+20 more)

### Community 4 - "Community 4"
Cohesion: 0.13
Nodes (22): capture_frame(), draw_parking_placeholders(), encrypt_image_aes(), generate_auth_signature(), generate_hmac_signature(), get_aes_key(), handle_command(), main() (+14 more)

### Community 5 - "Community 5"
Cohesion: 0.09
Nodes (12): IotStreamController, IotWebhookController, Request, Request, Request, TrustProxies, IotCapture, AppServiceProvider (+4 more)

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
Cohesion: 0.30
Nodes (4): AuthController, JsonResponse, MissionService, Request

### Community 11 - "Community 11"
Cohesion: 0.12
Nodes (15): devDependencies, axios, concurrently, laravel-echo, laravel-vite-plugin, pusher-js, tailwindcss, @tailwindcss/vite (+7 more)

### Community 12 - "Community 12"
Cohesion: 0.22
Nodes (5): UserFaqController, JsonResponse, Request, UserFaq, UserFaqController

### Community 13 - "Community 13"
Cohesion: 0.23
Nodes (11): draw_hud(), is_inside_polygon(), load_config(), main(), mouse_callback(), Saves polygon points and detection threshold to a JSON file., Loads polygon points and threshold from a JSON file., Callback function for mouse events to handle polygon drawing. (+3 more)

### Community 15 - "Community 15"
Cohesion: 0.36
Nodes (10): capture_frame(), chat_input_thread(), encrypt_image_aes(), generate_hmac_signature(), get_aes_key(), on_connect(), on_connect_success(), on_message() (+2 more)

### Community 17 - "Community 17"
Cohesion: 0.06
Nodes (9): SubareaCommentControllerTest, UserValidationController, HistoryService, MissionService, Request, ParkAmenityTest, ParkSubareaTest, SubareaCommentTest (+1 more)

### Community 19 - "Community 19"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 20 - "Community 20"
Cohesion: 0.22
Nodes (9): require, fakerphp/faker, laravel/framework, laravel/reverb, laravel/sanctum, laravel/tinker, php, php-mqtt/laravel-client (+1 more)

### Community 23 - "Community 23"
Cohesion: 0.07
Nodes (11): MissionController, MissionControllerTest, JsonResponse, MissionService, HistoryService, Mission, MissionTest, UserMissionTest (+3 more)

### Community 25 - "Community 25"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 26 - "Community 26"
Cohesion: 0.29
Nodes (7): require-dev, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, phpunit/phpunit

### Community 27 - "Community 27"
Cohesion: 0.29
Nodes (7): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, test

### Community 29 - "Community 29"
Cohesion: 0.16
Nodes (5): Request, HistoryService, error(), logInfo(), AuthController

### Community 30 - "Community 30"
Cohesion: 0.36
Nodes (4): RewardController, HistoryService, JsonResponse, Request

### Community 32 - "Community 32"
Cohesion: 0.11
Nodes (10): FeedbackController, IotWsAuthController, JsonResponse, Request, Request, Request, Request, Controller (+2 more)

### Community 33 - "Community 33"
Cohesion: 0.09
Nodes (12): Dispatchable, IotCommandSent, IotCountUpdated, IotDeviceStatusChanged, IotStreamReceived, IotThresholdUpdated, SubareaStatusUpdated, InteractsWithSockets (+4 more)

### Community 52 - "Community 52"
Cohesion: 0.43
Nodes (4): ProfileController, JsonResponse, MissionService, Request

### Community 53 - "Community 53"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 54 - "Community 54"
Cohesion: 0.60
Nodes (3): HistoryController, JsonResponse, Request

### Community 62 - "Community 62"
Cohesion: 0.20
Nodes (3): HasFactory, IotDevice, UserHistory

### Community 63 - "Community 63"
Cohesion: 0.50
Nodes (4): Membuat HMAC-SHA256 signature., Menangkap video dari webcam dan mengirimkannya via HTTP POST     dengan HMAC sig, sign_request(), start_video_stream()

### Community 68 - "Community 68"
Cohesion: 0.53
Nodes (3): MapVisualizationController, JsonResponse, Request

### Community 70 - "Community 70"
Cohesion: 0.22
Nodes (3): Model, Feedback, UserReward

### Community 78 - "Community 78"
Cohesion: 0.06
Nodes (32): 10. Atur Ulang Environment (.env) - PENTING, 11. Re-up Container, 1. Konfigurasi Environment (.env), 2. Atur docker-compose.yml, 3. Generate RSA Keys (Di Root), 4. Verifikasi Credential, 5. Menjalankan Container, 6. Generate Application Key (+24 more)

### Community 79 - "Community 79"
Cohesion: 0.14
Nodes (4): FeedbackCategoryControllerTest, InfoBoardControllerTest, TestCase, ScrubAndTraceProcessorTest

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
Cohesion: 0.19
Nodes (6): Request, Closure, ApiEncryption, RBAC, API Encryption (RSA), NotCurrentPassword

## Knowledge Gaps
- **98 isolated node(s):** `TrustProxies`, `$schema`, `name`, `type`, `description` (+93 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **53 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `TestCase` connect `Community 79` to `Community 0`, `Community 16`, `Community 17`, `Community 18`, `Community 23`, `Community 24`, `Community 34`, `Community 35`, `Community 36`, `Community 38`, `Community 39`, `Community 41`, `Community 42`, `Community 43`, `Community 44`, `Community 50`, `Community 51`, `Community 59`, `Community 60`, `Community 82`?**
  _High betweenness centrality (0.197) - this node is a cross-community bridge._
- **Why does `ParkSubarea` connect `Community 17` to `Community 0`, `Community 33`, `Community 48`, `Community 56`, `Community 62`?**
  _High betweenness centrality (0.107) - this node is a cross-community bridge._
- **Why does `Controller` connect `Community 32` to `Community 0`, `Community 1`, `Community 5`, `Community 10`, `Community 12`, `Community 17`, `Community 23`, `Community 28`, `Community 29`, `Community 30`, `Community 31`, `Community 40`, `Community 46`, `Community 47`, `Community 48`, `Community 49`, `Community 52`, `Community 54`, `Community 55`, `Community 56`, `Community 57`, `Community 61`, `Community 68`?**
  _High betweenness centrality (0.072) - this node is a cross-community bridge._
- **What connects `TrustProxies`, `$schema`, `name` to the rest of the system?**
  _120 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Community 0` be split into smaller, more focused modules?**
  _Cohesion score 0.08870967741935484 - nodes in this community are weakly interconnected._
- **Should `Community 1` be split into smaller, more focused modules?**
  _Cohesion score 0.050170068027210885 - nodes in this community are weakly interconnected._
- **Should `Community 2` be split into smaller, more focused modules?**
  _Cohesion score 0.06451612903225806 - nodes in this community are weakly interconnected._