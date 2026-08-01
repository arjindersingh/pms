<?php

// Vercel Functions have a read-only project filesystem. Laravel's compiled
// views are directed to /tmp by vercel.json, so ensure the directory exists.
if (! is_dir('/tmp/views')) {
    mkdir('/tmp/views', 0755, true);
}

require __DIR__.'/../public/index.php';
