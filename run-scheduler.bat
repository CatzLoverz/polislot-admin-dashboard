@echo off

@REM ganti sesuai path project dan php anda

@REM path project
cd /d C:\laragon\www\PoliSlot_Admin

@REM path php
C:\laragon\bin\php\php-8.4.8-Win32-vs17-x64\php.exe artisan schedule:run >> storage\logs\scheduler.log 2>&1