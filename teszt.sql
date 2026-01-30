SET FOREIGN_KEY_CHECKS = 0;

-- Clear existing test data
TRUNCATE TABLE `poll_answers`;
TRUNCATE TABLE `establishment_requests`;
TRUNCATE TABLE `event_shows`;
TRUNCATE TABLE `event_messages`;
TRUNCATE TABLE `event_favourites`;
TRUNCATE TABLE `event_feedbacks`;
TRUNCATE TABLE `class_students`;
TRUNCATE TABLE `poll_options`;
TRUNCATE TABLE `polls`;
TRUNCATE TABLE `events`;
TRUNCATE TABLE `classes`;
TRUNCATE TABLE `students`;
TRUNCATE TABLE `personels`;
TRUNCATE TABLE `establishments`;
TRUNCATE TABLE `settlements`;
TRUNCATE TABLE `inner_regions`;
TRUNCATE TABLE `regions`;
TRUNCATE TABLE `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- Insert regions (vármegyék)
INSERT INTO `regions` (`id`, `title`, `created_at`, `updated_at`) VALUES
(1, 'Pest vármegye', NOW(), NOW()),
(2, 'Hajdú-Bihar vármegye', NOW(), NOW()),
(3, 'Szabolcs-Szatmár-Bereg vármegye', NOW(), NOW());

-- Insert inner_regions (járások)
INSERT INTO `inner_regions` (`id`, `region_id`, `title`, `created_at`, `updated_at`) VALUES
(1, 1, 'Pest járás', NOW(), NOW()),
(2, 1, 'Gödöllői járás', NOW(), NOW()),
(3, 2, 'Debreceni járás', NOW(), NOW());

-- Insert settlements (települések)
INSERT INTO `settlements` (`id`, `inner_region_id`, `title`, `number`, `created_at`, `updated_at`) VALUES
(1, 1, 'Budapest', '1011', NOW(), NOW()),
(2, 2, 'Gödöllő', '2100', NOW(), NOW()),
(3, 3, 'Debrecen', '4000', NOW(), NOW());

-- Insert establishments
INSERT INTO `establishments` (`id`, `title`, `description`, `settlements_id`, `created_at`) VALUES
(1, 'Teszt Általános Iskola', 'Egy teszt általános iskola Budapesten', 1, NOW()),
(2, 'Gödöllői Gimnázium', 'Teszt gimnázium Gödöllőn', 2, NOW()),
(3, 'Debreceni Szakközépiskola', 'Teszt szakközépiskola Debrecenben', 3, NOW());

-- Insert users (password is 'password' hashed with bcrypt)
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Tanár Béla', 'teacher@test.com', NOW(), '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5K3Ox/1u7yQxK', NULL, NOW(), NOW()),
(2, 'Diák Anna', 'student@test.com', NOW(), '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5K3Ox/1u7yQxK', NULL, NOW(), NOW()),
(3, 'Kettős Károly', 'both@test.com', NOW(), '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5K3Ox/1u7yQxK', NULL, NOW(), NOW()),
(4, 'Sima Péter', 'regular@test.com', NOW(), '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5K3Ox/1u7yQxK', NULL, NOW(), NOW()),
(5, 'Admin István', 'admin@test.com', NOW(), '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5K3Ox/1u7yQxK', NULL, NOW(), NOW());

-- Insert personels (teachers/admins)
INSERT INTO `personels` (`id`, `role`, `establishment_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'teacher', 1, 1, NOW(), NOW()),
(2, 'admin', 2, 5, NOW(), NOW()),
(3, 'teacher', 3, 3, NOW(), NOW());

-- Insert students
INSERT INTO `students` (`id`, `alias`, `establishment_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'Diák Anna Alias', 1, 2, NOW(), NOW()),
(2, 'KK Alias', 2, 3, NOW(), NOW());

-- Insert classes
INSERT INTO `classes` (`id`, `users_id`, `name`, `grade`, `establishments_id`, `created_at`) VALUES
(1, 1, '5.A osztály', 5, 1, NOW()),
(2, 5, '9.B osztály', 9, 2, NOW()),
(3, 3, '11.C osztály', 11, 3, NOW());

-- Insert class_students
INSERT INTO `class_students` (`classes_id`, `users_id`, `created_at`, `updated_at`) VALUES
(1, 2, NOW(), NOW()),
(2, 3, NOW(), NOW());

-- Insert events
INSERT INTO `events` (`id`, `type`, `title`, `description`, `content`, `users_id`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'local', 'Osztálykirándulás', 'Kirándulás a Budai várba', 'Részletes program: Találkozás a Budavári-palota előtt 10 órakor...', 1, DATE_ADD(NOW(), INTERVAL 7 DAY), DATE_ADD(NOW(), INTERVAL 8 DAY), 'upcoming', NOW(), NOW()),
(2, 'global', 'Iskolai Farsang', 'Farsangi bál minden diáknak', 'Jelmezverseny és tánc a tornateremben...', 5, DATE_ADD(NOW(), INTERVAL 14 DAY), DATE_ADD(NOW(), INTERVAL 14 DAY), 'upcoming', NOW(), NOW());

-- Insert event_shows
INSERT INTO `event_shows` (`events_id`, `users_id`, `classes_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NOW(), NOW()),
(2, 5, 2, NOW(), NOW());

-- Insert polls
INSERT INTO `polls` (`id`, `events_id`, `title`, `users_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'Melyik dátum megfelelő?', 1, NOW(), NOW());

-- Insert poll_options
INSERT INTO `poll_options` (`id`, `polls_id`, `title`, `created_at`, `updated_at`) VALUES
(1, 1, 'Március 15.', NOW(), NOW()),
(2, 1, 'Március 22.', NOW(), NOW()),
(3, 1, 'Március 29.', NOW(), NOW());

-- Insert poll_answers
INSERT INTO `poll_answers` (`polls_id`, `users_id`, `poll_options_id`) VALUES
(1, 2, 1);

-- Insert event_feedbacks
INSERT INTO `event_feedbacks` (`id`, `events_id`, `answer`, `users_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'y', 2, NOW(), NOW()),
(2, 2, 'y', 3, NOW(), NOW());

-- Insert event_messages
INSERT INTO `event_messages` (`id`, `events_id`, `users_id`, `content`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'Nagyon várom már!', NOW(), NOW()),
(2, 1, 1, 'Ne felejtsétek el a csomagokat!', NOW(), NOW());

-- Insert event_favourites
INSERT INTO `event_favourites` (`id`, `events_id`, `users_id`, `created_at`, `updated_at`) VALUES
(1, 1, 2, NOW(), NOW()),
(2, 2, 3, NOW(), NOW());

-- Insert establishment_requests
INSERT INTO `establishment_requests` (`id`, `users_id`, `establishments_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'pending', NOW(), NOW());

-- Test credentials and expected results:
-- Email: teacher@test.com | Password: password | is_teacher: true, is_student: false
-- Email: student@test.com | Password: password | is_teacher: false, is_student: true
-- Email: both@test.com | Password: password | is_teacher: true, is_student: true
-- Email: regular@test.com | Password: password | is_teacher: false, is_student: false
-- Email: admin@test.com | Password: password | is_teacher: true (admin), is_student: false