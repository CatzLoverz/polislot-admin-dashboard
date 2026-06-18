# Graph Report - .  (2026-06-17)

## Corpus Check
- 237 files · ~76,675 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1093 nodes · 1720 edges · 137 communities (111 shown, 26 thin omitted)
- Extraction: 90% EXTRACTED · 10% INFERRED · 0% AMBIGUOUS · INFERRED: 174 edges (avg confidence: 0.8)
- Token cost: 15,500 input · 2,850 output

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

## God Nodes (most connected - your core abstractions)
1. `TestCase` - 63 edges
2. `Controller` - 52 edges
3. `Response` - 36 edges
4. `AuthControllerTest` - 26 edges
5. `Validation` - 22 edges
6. `ParkSubarea` - 20 edges
7. `ValidationException` - 15 edges
8. `UserMission` - 15 edges
9. `AuthController` - 13 edges
10. `IotDetectionController` - 12 edges

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

## Communities (137 total, 26 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.06
Nodes (56): CameraStream, encrypt_image_aes(), generate_hmac_signature(), get_aes_key(), is_inside_any_polygon(), is_inside_polygon(), load_local_config(), main() (+48 more)

### Community 1 - "Community 1"
Cohesion: 0.05
Nodes (20): JsonResponse, Request, JsonResponse, Request, View, JsonResponse, Request, Request (+12 more)

### Community 2 - "Community 2"
Cohesion: 0.07
Nodes (24): JsonResponse, Request, JsonResponse, RedirectResponse, Request, RedirectResponse, Request, RedirectResponse (+16 more)

### Community 3 - "Community 3"
Cohesion: 0.05
Nodes (19): RedirectResponse, Request, BelongsTo, HasMany, BelongsTo, HasMany, autoload, psr-4 (+11 more)

### Community 4 - "Community 4"
Cohesion: 0.06
Nodes (30): HistoryService, RedirectResponse, Request, Command, BackupAuto, BackupClean, DbList, DbRestore (+22 more)

### Community 5 - "Community 5"
Cohesion: 0.09
Nodes (13): AuthController, JsonResponse, Request, RedirectResponse, Request, View, RedirectResponse, Request (+5 more)

### Community 6 - "Community 6"
Cohesion: 0.08
Nodes (22): BelongsTo, HasMany, HasMany, HasMany, BelongsTo, BelongsTo, BelongsTo, BelongsTo (+14 more)

### Community 7 - "Community 7"
Cohesion: 0.05
Nodes (41): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages, description (+33 more)

### Community 8 - "Community 8"
Cohesion: 0.07
Nodes (9): JsonResponse, JsonResponse, Request, JsonResponse, JsonResponse, Request, JsonResponse, Request (+1 more)

### Community 9 - "Community 9"
Cohesion: 0.08
Nodes (13): JsonResponse, Request, JsonResponse, Request, BelongsTo, HasMany, BelongsTo, HasOne (+5 more)

### Community 10 - "Community 10"
Cohesion: 0.10
Nodes (10): Content, Dispatchable, Envelope, IotCommandSent, IotDetectionReceived, InteractsWithSockets, Mailable, Queueable (+2 more)

### Community 12 - "Community 12"
Cohesion: 0.12
Nodes (6): JsonResponse, MissionService, HasMany, HistoryService, Mission, UserMission

### Community 14 - "Community 14"
Cohesion: 0.16
Nodes (5): HistoryService, JsonResponse, MissionService, Request, Validation

### Community 15 - "Community 15"
Cohesion: 0.19
Nodes (6): JsonResponse, RedirectResponse, Request, BelongsTo, UserFaq, UserFaqController

### Community 16 - "Community 16"
Cohesion: 0.11
Nodes (17): devDependencies, axios, concurrently, laravel-echo, laravel-vite-plugin, pusher-js, tailwindcss, @tailwindcss/vite (+9 more)

### Community 17 - "Community 17"
Cohesion: 0.15
Nodes (5): BaseTestCase, ExampleTest, RewardTest, TestCase, ExampleTest

### Community 18 - "Community 18"
Cohesion: 0.15
Nodes (4): InfoBoardTest, ParkAmenityTest, UserRewardTest, RefreshDatabase

### Community 19 - "Community 19"
Cohesion: 0.18
Nodes (3): JsonResponse, MissionService, Request

### Community 21 - "Community 21"
Cohesion: 0.14
Nodes (15): 11. Re-up Container, 2. Atur docker-compose.yml, 3. Generate RSA Keys (Di Root), 4. Verifikasi Credential, 5. Menjalankan Container, 7. Migrasi Database, 8. Setup Admin User (Seeding), 9. Setup Database Roles (+7 more)

### Community 22 - "Community 22"
Cohesion: 0.23
Nodes (3): JsonResponse, Request, View

### Community 23 - "Community 23"
Cohesion: 0.15
Nodes (13): 2. Generate RSA Keys (Wajib), 3. Instalasi Dependency, 5. Migrasi Database & Seeding, 6. Setup Database Roles (RBAC), 8. Jalankan Aplikasi, 9. Mengaktifkan Scheduler (Backup Otomatis), code:bash (php artisan migrate --fresh --seed), code:bash (# Setup user database untuk Admin Dashboard) (+5 more)

### Community 24 - "Community 24"
Cohesion: 0.27
Nodes (5): RedirectResponse, Request, BelongsTo, InfoBoard, InfoBoardController

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
Cohesion: 0.33
Nodes (5): code:bash (chmod +x logrotate-entrypoint.sh), Instalasi dengan Docker, Khusus Pengguna Linux, Persiapan File Konfigurasi, Prasyarat

### Community 37 - "Community 37"
Cohesion: 0.33
Nodes (5): 1. Import (Namespace / `use` Statement), 2. PHPDoc (Komentar Fungsi & Class), 3. Konvensi Penulisan Model (Eloquent), 4. Best Practices Laravel Lainnya, Aturan Standar Penulisan Kode PHP & Laravel (Coding Standards)

### Community 40 - "Community 40"
Cohesion: 0.67
Nodes (3): 6. Generate Application Key, code:bash (docker compose exec app php artisan key:generate), code:bash (php artisan key:generate)

### Community 41 - "Community 41"
Cohesion: 0.67
Nodes (3): code:ini (# Ganti dengan user "polislot_admin" yang dibuat di langkah ), 7. Atur Ulang Environment (.env) - PENTING, code:ini (# Ganti dengan user "polislot_admin")

## Knowledge Gaps
- **114 isolated node(s):** `BeforeTool`, `Envelope`, `Content`, `$schema`, `name` (+109 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **26 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `TestCase` connect `Community 17` to `Community 1`, `Community 2`, `Community 3`, `Community 5`, `Community 8`, `Community 9`, `Community 11`, `Community 12`, `Community 13`, `Community 14`, `Community 15`, `Community 18`, `Community 19`, `Community 20`, `Community 22`, `Community 25`, `Community 26`, `Community 27`, `Community 31`, `Community 32`, `Community 33`, `Community 36`, `Community 38`?**
  _High betweenness centrality (0.152) - this node is a cross-community bridge._
- **Why does `autoload` connect `Community 3` to `Community 7`?**
  _High betweenness centrality (0.078) - this node is a cross-community bridge._
- **Are the 34 inferred relationships involving `Response` (e.g. with `.receiveConfigQuery()` and `.receiveCount()`) actually correct?**
  _`Response` has 34 INFERRED edges - model-reasoned connections that need verification._
- **What connects `BeforeTool`, `Envelope`, `Content` to the rest of the system?**
  _119 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Community 0` be split into smaller, more focused modules?**
  _Cohesion score 0.05502392344497608 - nodes in this community are weakly interconnected._
- **Should `Community 1` be split into smaller, more focused modules?**
  _Cohesion score 0.0546583850931677 - nodes in this community are weakly interconnected._
- **Should `Community 2` be split into smaller, more focused modules?**
  _Cohesion score 0.06599326599326599 - nodes in this community are weakly interconnected._