SET NAMES utf8mb4;
INSERT INTO `users` (`name`, `email`, `role`, `status`, `email_verified_at`, `password`, `created_at`, `updated_at`) VALUES
('Administrator', 'roxilv1205@gmail.com', 'admin', 'active', '2026-08-14 14:05:14', '$2y$12$sMezk3CGV0a0AhDfkd6WjuQUkRBq75Yz46grz5V.OgUFYvy7NnWrO', '2026-08-14 14:05:14', '2026-08-14 14:05:14')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `role` = 'admin', `status` = 'active', `email_verified_at` = VALUES(`email_verified_at`), `password` = VALUES(`password`), `updated_at` = VALUES(`updated_at`);
