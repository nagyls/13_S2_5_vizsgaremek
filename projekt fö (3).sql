CREATE TABLE `user` (
  `id` integer PRIMARY KEY,
  `username` varchar(255),
  `email` varchar(255),
  `password` varchar(255),
  `created_at` timestamp
);

CREATE TABLE `establishment` (
  `id` integer PRIMARY KEY,
  `title` varchar(255),
  `grades_id` int,
  `description` text COMMENT 'Content of the post',
  `settlement_id` integer,
  `created_at` timestamp
);

CREATE TABLE `establishment_requests` (
  `id` integer PRIMARY KEY,
  `user_id` integer NOT NULL,
  `establishment_id` integer NOT NULL,
  `status` enum('pending','accepted','rejected'),
  `created_at` timestamp
);

CREATE TABLE `class` (
  `id` integer PRIMARY KEY,
  `user_id` integer NOT NULL,
  `name` varchar(255),
  `grade` integer,
  `created_at` timestamp,
  `establishment_id` integer NOT NULL
);

CREATE TABLE `class_students` (
  `class_id` integer NOT NULL,
  `user_id` integer NOT NULL,
  `created_at` timestamp
);

CREATE TABLE `personnel` (
  `role` enum(admin,teacher),
  `establishment_id` integer NOT NULL,
  `user_id` integer NOT NULL,
  `created_at` timestamp
);

CREATE TABLE `students` (
  `alias` varchar(255),
  `establishment_id` integer NOT NULL,
  `user_id` integer NOT NULL,
  `created_at` timestamp
);

CREATE TABLE `event` (
  `id` integer PRIMARY KEY,
  `type` enum(local,global),
  `title` varchar(255),
  `description` text,
  `content` text COMMENT 'Content of the post',
  `user_id` integer NOT NULL,
  `start_date` datetime,
  `end_date` datetime,
  `status` enum('open','closed'),
  `created_at` timestamp
);

CREATE TABLE `event_shown_to` (
  `event_id` integer NOT NULL,
  `user_id` integer,
  `class_id` integer NOT NULL
);

CREATE TABLE `event_msg` (
  `id` integer PRIMARY KEY,
  `event_id` integer NOT NULL,
  `user_id` integer NOT NULL,
  `content` text COMMENT 'Content of the post',
  `created_at` timestamp
);

CREATE TABLE `event_feedback` (
  `event_id` integer NOT NULL,
  `answer` enum(y,n),
  `user_id` integer NOT NULL,
  `updated_at` timestamp
);

CREATE TABLE `event_favourite` (
  `id` integer PRIMARY KEY,
  `event_id` integer NOT NULL,
  `user_id` integer NOT NULL
);

CREATE TABLE `poll` (
  `id` integer PRIMARY KEY,
  `event_id` integer NOT NULL,
  `title` varchar(255),
  `user_id` integer NOT NULL,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `poll_option` (
  `id` integer PRIMARY KEY,
  `poll_id` integer NOT NULL,
  `title` varchar(255)
);

CREATE TABLE `poll_answer` (
  `poll_id` integer,
  `user_id` integer,
  `poll_option_id` integer,
  PRIMARY KEY (`poll_id`, `user_id`)
);

CREATE TABLE `region` (
  `id` integer PRIMARY KEY,
  `title` varchar(255),
  `created_at` timestamp
);

CREATE TABLE `inner_region` (
  `id` integer PRIMARY KEY,
  `region_id` integer,
  `title` varchar(255),
  `created_at` timestamp
);

CREATE TABLE `settlement` (
  `id` integer PRIMARY KEY,
  `inner_region_id` integer,
  `title` varchar(255),
  `number` varchar(255),
  `created_at` timestamp
);

ALTER TABLE `inner_region` ADD FOREIGN KEY (`region_id`) REFERENCES `region` (`id`);

ALTER TABLE `settlement` ADD FOREIGN KEY (`inner_region_id`) REFERENCES `inner_region` (`id`);

ALTER TABLE `class_students` ADD FOREIGN KEY (`class_id`) REFERENCES `class` (`id`);

ALTER TABLE `event_shown_to` ADD FOREIGN KEY (`class_id`) REFERENCES `class` (`id`);

ALTER TABLE `personnel` ADD FOREIGN KEY (`establishment_id`) REFERENCES `establishment` (`id`);

ALTER TABLE `establishment_requests` ADD FOREIGN KEY (`establishment_id`) REFERENCES `establishment` (`id`);

ALTER TABLE `students` ADD FOREIGN KEY (`establishment_id`) REFERENCES `establishment` (`id`);

ALTER TABLE `class` ADD FOREIGN KEY (`establishment_id`) REFERENCES `establishment` (`id`);

ALTER TABLE `establishment` ADD FOREIGN KEY (`settlement_id`) REFERENCES `settlement` (`id`);

ALTER TABLE `event_favourite` ADD FOREIGN KEY (`event_id`) REFERENCES `event` (`id`);

ALTER TABLE `event_msg` ADD FOREIGN KEY (`event_id`) REFERENCES `event` (`id`);

ALTER TABLE `event_feedback` ADD FOREIGN KEY (`event_id`) REFERENCES `event` (`id`);

ALTER TABLE `event_shown_to` ADD FOREIGN KEY (`event_id`) REFERENCES `event` (`id`);

ALTER TABLE `poll` ADD FOREIGN KEY (`event_id`) REFERENCES `event` (`id`);

ALTER TABLE `class_students` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

ALTER TABLE `class` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

ALTER TABLE `event_msg` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

ALTER TABLE `event_feedback` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

ALTER TABLE `event` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

ALTER TABLE `personnel` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

ALTER TABLE `students` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

ALTER TABLE `poll_answer` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

ALTER TABLE `event_favourite` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

ALTER TABLE `poll` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

ALTER TABLE `event_shown_to` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

ALTER TABLE `poll_option` ADD FOREIGN KEY (`poll_id`) REFERENCES `poll` (`id`);

ALTER TABLE `poll_answer` ADD FOREIGN KEY (`poll_id`) REFERENCES `poll` (`id`);

ALTER TABLE `poll_answer` ADD FOREIGN KEY (`poll_option_id`) REFERENCES `poll_option` (`id`);
