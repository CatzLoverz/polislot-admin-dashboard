@echo off
cd /d C:\laragon\www\polislot-admin\polislot-admin-dashboard
C:\laragon\bin\php\php-8.3.25-nts-Win32-vs16-x64\php.exe artisan schedule:run >> storage\logs\scheduler.log 2>&1