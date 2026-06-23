# Graph Report - polislot-admin-dashboard  (2026-06-23)

## Corpus Check
- 232 files · ~79,696 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1278 nodes · 2453 edges · 163 communities (132 shown, 31 thin omitted)
- Extraction: 92% EXTRACTED · 8% INFERRED · 0% AMBIGUOUS · INFERRED: 187 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `9fddadf1`
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
- [[_COMMUNITY_Community 46|Community 46]]
- [[_COMMUNITY_Community 51|Community 51]]
- [[_COMMUNITY_Community 77|Community 77]]
- [[_COMMUNITY_Community 78|Community 78]]
- [[_COMMUNITY_Community 79|Community 79]]
- [[_COMMUNITY_Community 80|Community 80]]
- [[_COMMUNITY_Community 81|Community 81]]
- [[_COMMUNITY_Community 82|Community 82]]
- [[_COMMUNITY_Community 83|Community 83]]
- [[_COMMUNITY_Community 84|Community 84]]
- [[_COMMUNITY_Community 97|Community 97]]
- [[_COMMUNITY_Community 122|Community 122]]
- [[_COMMUNITY_Community 123|Community 123]]
- [[_COMMUNITY_Community 130|Community 130]]
- [[_COMMUNITY_Community 135|Community 135]]
- [[_COMMUNITY_Community 136|Community 136]]
- [[_COMMUNITY_Community 137|Community 137]]
- [[_COMMUNITY_Community 138|Community 138]]
- [[_COMMUNITY_Community 139|Community 139]]
- [[_COMMUNITY_Community 140|Community 140]]
- [[_COMMUNITY_Community 141|Community 141]]
- [[_COMMUNITY_Community 142|Community 142]]
- [[_COMMUNITY_Community 143|Community 143]]
- [[_COMMUNITY_Community 144|Community 144]]
- [[_COMMUNITY_Community 145|Community 145]]
- [[_COMMUNITY_Community 146|Community 146]]
- [[_COMMUNITY_Community 147|Community 147]]
- [[_COMMUNITY_Community 148|Community 148]]
- [[_COMMUNITY_Community 149|Community 149]]
- [[_COMMUNITY_Community 150|Community 150]]
- [[_COMMUNITY_Community 153|Community 153]]
- [[_COMMUNITY_Community 154|Community 154]]
- [[_COMMUNITY_Community 155|Community 155]]
- [[_COMMUNITY_Community 156|Community 156]]
- [[_COMMUNITY_Community 157|Community 157]]
- [[_COMMUNITY_Community 158|Community 158]]
- [[_COMMUNITY_Community 159|Community 159]]
- [[_COMMUNITY_Community 162|Community 162]]
- [[_COMMUNITY_Community 165|Community 165]]
- [[_COMMUNITY_Community 166|Community 166]]

## God Nodes (most connected - your core abstractions)
1. `TestCase` - 113 edges
2. `ParkSubarea` - 71 edges
3. `Controller` - 71 edges
4. `Response` - 36 edges
5. `AuthControllerTest` - 34 edges
6. `Mission` - 30 edges
7. `Validation` - 29 edges
8. `AuthControllerTest` - 26 edges
9. `UserMission` - 21 edges
10. `ValidationException` - 18 edges

## Surprising Connections (you probably didn't know these)
- `BackupAuto` --rationale_for--> `Database Backup & Restore`  [INFERRED]
  app/Console/Commands/BackupAuto.php → README.md
- `BackupClean` --rationale_for--> `Database Backup & Restore`  [INFERRED]
  app/Console/Commands/BackupClean.php → README.md
- `API Encryption (RSA)` --inherits--> `TestCase`  [EXTRACTED]
  README.md → tests/TestCase.php
- `Docker Installation Guide` --references--> `Logrotate Sidecar Container`  [INFERRED]
  docs/INSTALLATION_DOCKER.md → docker/docker-compose.yml
- `Validation` --inherits--> `TestCase`  [EXTRACTED]
  app/Models/Validation.php → tests/TestCase.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Selenium Shared Login Flow** — mission_test_01_tambah_misi_test_add_mission, mission_test_02_ubah_misi_test_edit_mission, mission_test_03_hapus_misi_test_delete_mission, profil_test_01_view_profil_test_view_profile, profil_test_02_ubah_profil_test_edit_profile_name, reward_test_01_tambah_reward_test_add_reward, reward_test_02_ubah_reward_test_edit_reward, reward_test_03_hapus_reward_test_delete_reward, reward_test_04_view_verify_test_filter_verify_reward, userfaq_test_01_tambah_faq_test_add_faq, userfaq_test_02_ubah_faq_test_edit_faq, userfaq_test_03_hapus_faq_test_delete_faq [INFERRED 0.95]
- **Selenium Driver Fixture Pattern** — mission_test_01_tambah_misi_driver, mission_test_02_ubah_misi_driver, mission_test_03_hapus_misi_driver, profil_test_01_view_profil_driver, profil_test_02_ubah_profil_driver, reward_test_01_tambah_reward_driver, reward_test_02_ubah_reward_driver, reward_test_03_hapus_reward_driver, reward_test_04_view_verify_driver, userfaq_test_01_tambah_faq_driver, userfaq_test_02_ubah_faq_driver, userfaq_test_03_hapus_faq_driver [INFERRED 0.95]

## Communities (163 total, 31 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.06
Nodes (56): CameraStream, encrypt_image_aes(), generate_hmac_signature(), get_aes_key(), is_inside_any_polygon(), is_inside_polygon(), load_local_config(), main() (+48 more)

### Community 1 - "Community 1"
Cohesion: 0.08
Nodes (18): IotDetectionController, IotDetectionControllerTest, JsonResponse, Request, JsonResponse, Request, View, JsonResponse (+10 more)

### Community 2 - "Community 2"
Cohesion: 0.15
Nodes (11): FeedbackCategoryController, InfoBoardController, UserFaqController, JsonResponse, JsonResponse, JsonResponse, JsonResponse, AuthorizesRequests (+3 more)

### Community 3 - "Community 3"
Cohesion: 0.10
Nodes (14): RedirectResponse, Request, BelongsTo, HasMany, FeedbackTest, Feedback, FeedbackCategory, Seeder (+6 more)

### Community 4 - "Community 4"
Cohesion: 0.09
Nodes (20): HistoryService, RedirectResponse, Request, Command, BackupAuto, BackupClean, BackupDatabase, DbList (+12 more)

### Community 5 - "Community 5"
Cohesion: 0.29
Nodes (4): RedirectResponse, Request, View, AuthController

### Community 6 - "Community 6"
Cohesion: 0.20
Nodes (7): BelongsTo, BelongsTo, BelongsTo, Model, InfoBoard, ParkAmenity, UserReward

### Community 7 - "Community 7"
Cohesion: 0.22
Nodes (8): description, keywords, license, minimum-stability, name, prefer-stable, $schema, type

### Community 8 - "Community 8"
Cohesion: 0.15
Nodes (7): RewardController, RewardControllerTest, HistoryService, JsonResponse, Request, HistoryServiceTest, HistoryService

### Community 9 - "Community 9"
Cohesion: 0.36
Nodes (4): IotWebhookController, IotWebhookControllerTest, JsonResponse, Request

### Community 10 - "Community 10"
Cohesion: 0.11
Nodes (16): Content, Dispatchable, Envelope, IotCommandSent, IotCountUpdated, IotDetectionReceived, IotDeviceStatusChanged, IotThresholdUpdated (+8 more)

### Community 12 - "Community 12"
Cohesion: 0.23
Nodes (6): HistoryService, Mission, UserMissionTest, MissionServiceTest, MissionService, UserMission

### Community 14 - "Community 14"
Cohesion: 0.05
Nodes (21): MapVisualizationController, SubareaCommentController, SubareaCommentControllerTest, UserValidationController, UserValidationControllerTest, JsonResponse, Request, JsonResponse (+13 more)

### Community 15 - "Community 15"
Cohesion: 0.32
Nodes (5): RedirectResponse, Request, BelongsTo, UserFaq, UserFaqController

### Community 16 - "Community 16"
Cohesion: 0.11
Nodes (17): devDependencies, axios, concurrently, laravel-echo, laravel-vite-plugin, pusher-js, tailwindcss, @tailwindcss/vite (+9 more)

### Community 17 - "Community 17"
Cohesion: 0.12
Nodes (3): ParkAreaTest, UserTest, ExampleTest

### Community 18 - "Community 18"
Cohesion: 0.06
Nodes (30): 1. Deployment Architecture, 2.1. Backend Server & Admin Dashboard (Via Docker - Recommended), 2.2. Backend Server (Instalasi Manual Lokal / XAMPP), 2.3. PoliSlot Mobile App (Flutter), 2.4. Perangkat Edge IoT (Parking Detector), 2. Installation Procedure, 3. Security Checklist, API & Komunikasi Data (+22 more)

### Community 19 - "Community 19"
Cohesion: 0.23
Nodes (5): ProfileController, ProfileControllerTest, JsonResponse, MissionService, Request

### Community 20 - "Community 20"
Cohesion: 0.33
Nodes (4): MissionController, MissionControllerTest, JsonResponse, MissionService

### Community 21 - "Community 21"
Cohesion: 0.13
Nodes (16): 10. Atur Ulang Environment (.env) - PENTING, 11. Re-up Container, 2. Atur docker-compose.yml, 3. Generate RSA Keys (Di Root), 4. Verifikasi Credential, 5. Menjalankan Container, 7. Migrasi Database, 8. Setup Admin User (Seeding) (+8 more)

### Community 22 - "Community 22"
Cohesion: 0.27
Nodes (5): JsonResponse, Request, View, DashboardController, DashboardControllerTest

### Community 23 - "Community 23"
Cohesion: 0.10
Nodes (20): 6. Generate Application Key, code:bash (docker compose exec app php artisan key:generate), code:ini (# Ganti dengan user "polislot_admin" yang dibuat di langkah ), 2. Generate RSA Keys (Wajib), 3. Instalasi Dependency, 4. Generate Application Key, 5. Migrasi Database & Seeding, 6. Setup Database Roles (RBAC) (+12 more)

### Community 24 - "Community 24"
Cohesion: 0.08
Nodes (24): Closure, Request, Closure, logrotate-entrypoint.sh script, Docker Compose Configuration, Logrotate Sidecar Container, Mosquitto MQTT Broker, Docker Installation Guide (+16 more)

### Community 26 - "Community 26"
Cohesion: 0.32
Nodes (3): ProcessorInterface, ScrubAndTraceProcessor, ScrubAndTraceProcessorTest

### Community 28 - "Community 28"
Cohesion: 0.25
Nodes (8): 1. Konfigurasi Environment (.env), code:bash (cp .env.example .env), code:ini (DB_CONNECTION=mysql), code:ini (DUMP_BINARY_PATH="C:/xampp/mysql/bin/"), code:ini (# DUMP_BINARY_PATH=""), code:ini (ADMIN_EMAIL=email_valid_anda@gmail.com), code:ini (MAIL_MAILER=smtp), code:ini (GOOGLE_MAPS_JS="isi_api_key_google_cloud_anda")

### Community 29 - "Community 29"
Cohesion: 0.25
Nodes (7): import_classes, import_constants, import_functions, preset, rules, fully_qualified_strict_types, global_namespace_import

### Community 30 - "Community 30"
Cohesion: 0.29
Nodes (7): 1. Konfigurasi Environment (.env), code:ini (DB_CONNECTION=mariadb), code:ini (ADMIN_EMAIL=email_valid_anda@gmail.com), code:ini (MAIL_MAILER=smtp), code:ini (GOOGLE_MAPS_JS="isi_api_key_google_cloud_anda"), code:ini (TUNNEL_TOKEN="isi_token_cloudflare_tunnel_anda"), code:ini (MQTT_AUTH_USERNAME=MQTTPoliSlot)

### Community 34 - "Community 34"
Cohesion: 0.20
Nodes (7): code:bash (chmod +x logrotate-entrypoint.sh), Instalasi dengan Docker, Khusus Pengguna Linux, Persiapan File Konfigurasi, Prasyarat, Instalasi Manual (Tanpa Docker), Prasyarat

### Community 36 - "Community 36"
Cohesion: 0.17
Nodes (9): RedirectResponse, Request, View, JsonResponse, RedirectResponse, Request, ParkAreaController, ParkAreaControllerTest (+1 more)

### Community 37 - "Community 37"
Cohesion: 0.33
Nodes (5): 1. Import (Namespace / `use` Statement), 2. PHPDoc (Komentar Fungsi & Class), 3. Konvensi Penulisan Model (Eloquent), 4. Best Practices Laravel Lainnya, Aturan Standar Penulisan Kode PHP & Laravel (Coding Standards)

### Community 40 - "Community 40"
Cohesion: 0.08
Nodes (11): FeedbackCategoryControllerTest, HistoryControllerTest, InfoBoardControllerTest, MapVisualizationControllerTest, BaseTestCase, FeedbackCategoryTest, RewardTest, UserRewardTest (+3 more)

### Community 41 - "Community 41"
Cohesion: 0.34
Nodes (4): AuthController, JsonResponse, MissionService, Request

### Community 46 - "Community 46"
Cohesion: 0.20
Nodes (6): BelongsTo, HasMany, UserFactory, Factory, IotDevice, static

### Community 135 - "Community 135"
Cohesion: 0.70
Nodes (3): HistoryController, JsonResponse, Request

### Community 138 - "Community 138"
Cohesion: 0.33
Nodes (4): IotWsAuthController, IotWsAuthControllerTest, JsonResponse, Request

### Community 139 - "Community 139"
Cohesion: 0.22
Nodes (9): require, fakerphp/faker, laravel/framework, laravel/reverb, laravel/sanctum, laravel/tinker, php, php-mqtt/laravel-client (+1 more)

### Community 140 - "Community 140"
Cohesion: 0.42
Nodes (4): RedirectResponse, Request, ValidationException, MissionController

### Community 141 - "Community 141"
Cohesion: 0.50
Nodes (3): RedirectResponse, Request, RewardController

### Community 142 - "Community 142"
Cohesion: 0.25
Nodes (8): autoload, autoload-dev, psr-4, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\, Tests\\

### Community 143 - "Community 143"
Cohesion: 0.39
Nodes (3): AppServiceProvider, AuthServiceProvider, ServiceProvider

### Community 144 - "Community 144"
Cohesion: 0.43
Nodes (4): RedirectResponse, Request, View, ProfileController

### Community 145 - "Community 145"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 146 - "Community 146"
Cohesion: 0.29
Nodes (7): require-dev, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, phpunit/phpunit

### Community 147 - "Community 147"
Cohesion: 0.29
Nodes (7): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, test

### Community 148 - "Community 148"
Cohesion: 0.53
Nodes (3): RedirectResponse, Request, ValidationController

### Community 153 - "Community 153"
Cohesion: 0.24
Nodes (6): BelongsTo, HasMany, HasOne, evaluateThresholdShift, getLiveStatus, ParkSubarea

### Community 154 - "Community 154"
Cohesion: 0.46
Nodes (3): RedirectResponse, Request, InfoBoardController

### Community 155 - "Community 155"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

### Community 156 - "Community 156"
Cohesion: 0.27
Nodes (5): HasMany, Authenticatable, HasApiTokens, User, Notifiable

### Community 157 - "Community 157"
Cohesion: 0.33
Nodes (5): HasMany, BelongsTo, HasFactory, Mission, UserHistory

### Community 158 - "Community 158"
Cohesion: 0.31
Nodes (4): FeedbackController, FeedbackControllerTest, JsonResponse, Request

### Community 159 - "Community 159"
Cohesion: 0.46
Nodes (3): RedirectResponse, Request, FeedbackCategoryController

## Knowledge Gaps
- **138 isolated node(s):** `$schema`, `name`, `type`, `description`, `keywords` (+133 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **31 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `TestCase` connect `Community 40` to `Community 1`, `Community 2`, `Community 3`, `Community 135`, `Community 8`, `Community 9`, `Community 138`, `Community 11`, `Community 12`, `Community 13`, `Community 14`, `Community 17`, `Community 19`, `Community 20`, `Community 149`, `Community 150`, `Community 22`, `Community 24`, `Community 25`, `Community 26`, `Community 27`, `Community 158`, `Community 31`, `Community 32`, `Community 33`, `Community 36`, `Community 38`, `Community 166`, `Community 42`?**
  _High betweenness centrality (0.184) - this node is a cross-community bridge._
- **Why does `API Encryption (RSA)` connect `Community 24` to `Community 40`?**
  _High betweenness centrality (0.090) - this node is a cross-community bridge._
- **Why does `Polislot Admin Dashboard` connect `Community 24` to `Community 34`, `Community 4`?**
  _High betweenness centrality (0.079) - this node is a cross-community bridge._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _142 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Community 0` be split into smaller, more focused modules?**
  _Cohesion score 0.05909351692484223 - nodes in this community are weakly interconnected._
- **Should `Community 1` be split into smaller, more focused modules?**
  _Cohesion score 0.08246753246753247 - nodes in this community are weakly interconnected._
- **Should `Community 2` be split into smaller, more focused modules?**
  _Cohesion score 0.1471861471861472 - nodes in this community are weakly interconnected._