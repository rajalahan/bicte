-- ============================================================
-- Lahan Municipality Smart Information Board – database schema
-- MySQL 8 / MariaDB 10.6+
-- This file is a vendor-neutral reference; the canonical schema
-- is the Laravel migrations under database/migrations/.
-- ============================================================

CREATE DATABASE IF NOT EXISTS `bicte`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `bicte`;

-- ----------- 1. Auth / RBAC (Laravel + Spatie) -------------

CREATE TABLE `users` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`              VARCHAR(120)  NOT NULL,
  `email`             VARCHAR(200)  NOT NULL UNIQUE,
  `email_verified_at` TIMESTAMP NULL,
  `password`          VARCHAR(255)  NOT NULL,
  `remember_token`    VARCHAR(100) NULL,
  `created_at`        TIMESTAMP NULL,
  `updated_at`        TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Spatie permission tables (roles, permissions, model_has_roles, role_has_permissions)
-- are created by `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
-- followed by `php artisan migrate`.

-- ----------- 2. Officials ------------------------------------

CREATE TABLE `officials` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role`         ENUM('mayor','deputy','cao','other') NOT NULL,
  `name`         VARCHAR(200)  NOT NULL,
  `designation`  VARCHAR(200)  NULL,
  `photo`        VARCHAR(500)  NULL,
  `phone`        VARCHAR(40)   NULL,
  `email`        VARCHAR(200)  NULL,
  `message`      TEXT          NULL,
  `sort_order`   INT           NOT NULL DEFAULT 0,
  `is_active`    TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP NULL,
  `updated_at`   TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_officials_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- 3. Departments ----------------------------------

CREATE TABLE `departments` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`                VARCHAR(80)   NOT NULL UNIQUE,
  `name`                VARCHAR(200)  NOT NULL,
  `name_en`             VARCHAR(200)  NULL,
  `summary`             TEXT          NULL,
  `icon`                VARCHAR(80)   NULL,
  `room`                VARCHAR(80)   NULL,
  `floor`               VARCHAR(80)   NULL,
  `contact_person`      VARCHAR(200)  NULL,
  `contact_designation` VARCHAR(200)  NULL,
  `phone`               VARCHAR(40)   NULL,
  `email`               VARCHAR(200)  NULL,
  `timings`             VARCHAR(200)  NULL,
  `charter`             TEXT          NULL,
  `public_url`          VARCHAR(500)  NULL,
  `services`            JSON          NULL,
  `required_documents`  JSON          NULL,
  `process`             JSON          NULL,
  `fees`                JSON          NULL,
  `forms`               JSON          NULL,
  `sort_order`          INT           NOT NULL DEFAULT 0,
  `is_active`           TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`          TIMESTAMP NULL,
  `updated_at`          TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_departments_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- 4. Notices --------------------------------------

CREATE TABLE `notices` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id`  BIGINT UNSIGNED NULL,
  `title`          VARCHAR(255)  NOT NULL,
  `summary`        VARCHAR(500)  NULL,
  `body`           LONGTEXT      NULL,
  `category`       VARCHAR(80)   NULL,
  `attachment_url` VARCHAR(500)  NULL,
  `is_active`      TINYINT(1)    NOT NULL DEFAULT 1,
  `is_urgent`      TINYINT(1)    NOT NULL DEFAULT 0,
  `published_at`   TIMESTAMP NULL,
  `expires_at`     TIMESTAMP NULL,
  `created_at`     TIMESTAMP NULL,
  `updated_at`     TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_notices_pub` (`is_active`, `published_at`),
  CONSTRAINT `fk_notices_dept`
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- 5. FAQ (AI assistant knowledge base) ------------

CREATE TABLE `faqs` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` BIGINT UNSIGNED NULL,
  `intent`        VARCHAR(80)   NOT NULL,
  `keywords`      JSON          NULL,
  `question`      VARCHAR(300)  NOT NULL,
  `answer_ne`     LONGTEXT      NOT NULL,
  `answer_en`     LONGTEXT      NULL,
  `sort_order`    INT           NOT NULL DEFAULT 0,
  `is_active`     TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP NULL,
  `updated_at`    TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_faqs_intent` (`intent`),
  FULLTEXT INDEX `ft_faqs_question` (`question`, `answer_ne`),
  CONSTRAINT `fk_faqs_dept`
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- 6. Achievements ---------------------------------

CREATE TABLE `achievements` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `year`         VARCHAR(20)   NOT NULL,
  `title`        VARCHAR(200)  NOT NULL,
  `description`  TEXT          NULL,
  `image_url`    VARCHAR(500)  NULL,
  `sort_order`   INT           NOT NULL DEFAULT 0,
  `is_active`    TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP NULL,
  `updated_at`   TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- 7. Citizen feedback / complaints ----------------

CREATE TABLE `feedbacks` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket`        VARCHAR(40)   NOT NULL UNIQUE,
  `name`          VARCHAR(120)  NULL,
  `phone`         VARCHAR(20)   NULL,
  `email`         VARCHAR(200)  NULL,
  `department_id` BIGINT UNSIGNED NULL,
  `type`          ENUM('complaint','suggestion','appreciation','info') NOT NULL,
  `subject`       VARCHAR(200)  NOT NULL,
  `message`       TEXT          NOT NULL,
  `status`        ENUM('new','in_progress','resolved','closed') NOT NULL DEFAULT 'new',
  `ip_address`    VARCHAR(45)   NULL,
  `submitted_at`  TIMESTAMP NULL,
  `resolved_at`   TIMESTAMP NULL,
  `created_at`    TIMESTAMP NULL,
  `updated_at`    TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_feedbacks_status` (`status`, `submitted_at`),
  CONSTRAINT `fk_feedbacks_dept`
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- 8. Tokens / queue -------------------------------

CREATE TABLE `tokens` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` BIGINT UNSIGNED NOT NULL,
  `code`          VARCHAR(20)   NOT NULL,
  `sequence`      INT           NOT NULL,
  `name`          VARCHAR(120)  NULL,
  `phone`         VARCHAR(20)   NULL,
  `status`        ENUM('waiting','serving','done','cancelled') NOT NULL DEFAULT 'waiting',
  `issued_at`     TIMESTAMP NOT NULL,
  `served_at`     TIMESTAMP NULL,
  `closed_at`     TIMESTAMP NULL,
  `created_at`    TIMESTAMP NULL,
  `updated_at`    TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tokens_today` (`department_id`, `issued_at`),
  CONSTRAINT `fk_tokens_dept`
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- 9. Generic content / forms ----------------------

CREATE TABLE `forms` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` BIGINT UNSIGNED NOT NULL,
  `title`         VARCHAR(200)  NOT NULL,
  `file_url`      VARCHAR(500)  NOT NULL,
  `language`      VARCHAR(10)   NOT NULL DEFAULT 'ne',
  `sort_order`    INT           NOT NULL DEFAULT 0,
  `created_at`    TIMESTAMP NULL,
  `updated_at`    TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_forms_dept`
    FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------- 10. Audit log -----------------------------------

CREATE TABLE `audit_logs` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NULL,
  `action`     VARCHAR(80)   NOT NULL,
  `subject`    VARCHAR(80)   NULL,
  `subject_id` BIGINT UNSIGNED NULL,
  `payload`    JSON          NULL,
  `ip_address` VARCHAR(45)   NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_audit_subject` (`subject`, `subject_id`),
  CONSTRAINT `fk_audit_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
