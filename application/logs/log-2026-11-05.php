<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-11-05 08:35:26 --> 404 Page Not Found: 
ERROR - 2026-11-05 08:35:27 --> 404 Page Not Found: 
ERROR - 2026-11-05 08:35:27 --> 404 Page Not Found: 
ERROR - 2026-11-05 08:35:27 --> 404 Page Not Found: 
ERROR - 2026-11-05 08:35:46 --> 404 Page Not Found: 
ERROR - 2026-11-05 08:35:46 --> 404 Page Not Found: 
ERROR - 2026-11-05 08:35:47 --> 404 Page Not Found: 
ERROR - 2026-11-05 08:35:47 --> 404 Page Not Found: 
ERROR - 2026-11-05 08:36:00 --> 404 Page Not Found: 
ERROR - 2026-11-05 08:39:08 --> Severity: error --> Exception: Call to undefined method CI_Loader::can() /www/wwwroot/koperasi/application/views/layouts/admin.php 24
ERROR - 2026-11-05 08:39:08 --> Severity: error --> Exception: Call to undefined method CI_Loader::can() /www/wwwroot/koperasi/application/views/layouts/admin.php 24
ERROR - 2026-11-05 08:39:08 --> Severity: error --> Exception: Call to undefined method CI_Loader::can() /www/wwwroot/koperasi/application/views/layouts/admin.php 24
ERROR - 2026-11-05 08:39:08 --> Severity: error --> Exception: Call to undefined method CI_Loader::can() /www/wwwroot/koperasi/application/views/layouts/admin.php 24
ERROR - 2026-11-05 08:39:08 --> Severity: error --> Exception: Call to undefined method CI_Loader::can() /www/wwwroot/koperasi/application/views/layouts/admin.php 24
ERROR - 2026-11-05 08:39:08 --> Severity: error --> Exception: Call to undefined method CI_Loader::can() /www/wwwroot/koperasi/application/views/layouts/admin.php 24
ERROR - 2026-11-05 08:44:05 --> Severity: error --> Exception: Call to undefined method CI_DB_mysqli_driver::end_group() /www/wwwroot/koperasi/application/controllers/Cooperatives.php 21
ERROR - 2026-11-05 08:52:50 --> 404 Page Not Found: Faviconico/index
ERROR - 2026-11-05 08:57:15 --> 404 Page Not Found: Welcome/index
ERROR - 2026-11-05 08:57:26 --> 404 Page Not Found: Indexhtml/index
ERROR - 2026-11-05 11:12:46 --> Query error: Column 'id' in WHERE is ambiguous - Invalid query: SELECT `a`.`id`, `a`.`fiscal_year`, `a`.`revision_no`, `a`.`status`, `a`.`locked_at`, `c`.`name` AS `cooperative_name`, `m`.`name` AS `form_name`
FROM (`assessments` `a`, `users`)
JOIN `cooperatives` `c` ON `c`.`id`=`a`.`cooperative_id`
JOIN `master_items` `m` ON `m`.`id`=`a`.`form_item_id`
WHERE `id` = 2
AND `active` = 1
ERROR - 2026-11-05 11:12:46 --> Severity: error --> Exception: Call to a member function row_array() on bool /www/wwwroot/koperasi/application/core/MY_Controller.php 10
ERROR - 2026-11-05 11:20:38 --> Severity: error --> Exception: Class "finfo" not found /www/wwwroot/koperasi/application/controllers/Kdkmp.php 61
ERROR - 2026-11-05 11:57:11 --> Query error: Unknown column 'e.created_at' in 'SELECT' - Invalid query: SELECT `e`.`after_json`, `e`.`created_at`, `u`.`full_name`
FROM `audit_events` `e`
LEFT JOIN `users` `u` ON `u`.`id`=`e`.`actor_user_id`
WHERE `e`.`entity_type` = 'assessments'
AND `e`.`entity_id` = '28'
AND `e`.`action_code` = 'review'
ORDER BY `e`.`id` DESC
 LIMIT 1
ERROR - 2026-11-05 11:57:11 --> Severity: error --> Exception: Call to a member function row_array() on bool /www/wwwroot/koperasi/application/controllers/Assessments.php 231
ERROR - 2026-11-05 13:01:17 --> 404 Page Not Found: Admin/impor
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:19:59 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:00 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:00 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:00 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:00 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:00 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:00 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:00 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:00 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:00 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:00 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:00 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:00 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:00 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:00 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:01 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:01 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:01 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:01 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:01 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:01 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:01 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:01 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:01 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:01 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:01 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:01 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:01 --> Severity: Warning --> Undefined variable $site /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:01 --> Severity: Warning --> Trying to access array offset on value of type null /www/wwwroot/koperasi/application/views/public/profile_links.php 1
ERROR - 2026-11-05 13:20:01 --> 404 Page Not Found: Admin/impor
ERROR - 2026-11-05 13:25:53 --> 404 Page Not Found: 
ERROR - 2026-11-05 13:33:31 --> Severity: Warning --> mkdir(): Permission denied /www/wwwroot/koperasi/application/controllers/Annual_reports.php 114
