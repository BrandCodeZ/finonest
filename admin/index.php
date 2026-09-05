<?php
/**
 * Admin panel index.
 * If no index file were present, Apache/nginx could list the
 * admin directory. This redirects to the dashboard instead.
 */
require_once __DIR__ . '/includes/auth.php';

redirect('dashboard.php');