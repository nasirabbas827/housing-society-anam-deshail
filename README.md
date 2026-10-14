# Housing Society Management System

housing-society-anam-deshail is a housing society management application.

![PHP](https://img.shields.io/badge/PHP-2563EB?style=flat-square) ![MySQL](https://img.shields.io/badge/MySQL-0F172A?style=flat-square)

## Overview

The data model covers `admins`, `announcements`, `complaints`, `facilities`, `facility_bookings`, `users`.

## Application areas

| Area | Pages in the repository |
| --- | --- |
| Admin | [`admin/add_announcement.php`](admin/add_announcement.php), [`admin/add_facility.php`](admin/add_facility.php), [`admin/add_users.php`](admin/add_users.php), [`admin/admin_home.php`](admin/admin_home.php), [`admin/admin_login.php`](admin/admin_login.php) |
| Root Pages | [`login.php`](login.php), [`register.php`](register.php) |
| Resident | [`resident/announcements.php`](resident/announcements.php), [`resident/complains.php`](resident/complains.php), [`resident/dashboard.php`](resident/dashboard.php), [`resident/facilities.php`](resident/facilities.php), [`resident/profile.php`](resident/profile.php) |
| Service Provider | [`service_provider/announcements.php`](service_provider/announcements.php), [`service_provider/complains.php`](service_provider/complains.php), [`service_provider/dashboard.php`](service_provider/dashboard.php), [`service_provider/profile.php`](service_provider/profile.php) |

## Getting started

### 1. Get the project

```bash
git clone https://github.com/nasirabbas827/housing-society-anam-deshail.git
cd housing-society-anam-deshail
```

### 2. Configure PHP and the database

1. Use a local PHP server with MySQL or MariaDB, such as XAMPP.
2. Import [`Database/housing_db.sql`](Database/housing_db.sql) into your local database server.
3. Set your local database connection details in [`admin/config.php`](admin/config.php), [`config.php`](config.php), [`resident/config.php`](resident/config.php), [`service_provider/config.php`](service_provider/config.php).
4. Keep the existing asset and upload directory paths intact.

### 3. Start the application

With the database running, start a local development server from the repository root:

```bash
php -S 127.0.0.1:8000
```

Open http://127.0.0.1:8000/index.php. If using Apache/XAMPP instead, place the project in its document root and open the matching localhost folder.

## Repository guide

| Path | Purpose |
| --- | --- |
| [`Database`](Database) | Database scripts |
| [`config.php`](config.php) | Application/database configuration |
| [`css`](css) | Stylesheets |
| [`images`](images) | Image assets |
| [`index.php`](index.php) | Web entry page |

## Development notes

These setup instructions are based on the repository source. The application has not been executed as part of this documentation update; verify dependencies and configuration in your local environment.
