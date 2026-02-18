<?php

if (!function_exists('app_env_value')) {
    function app_env_value($key, $default = '')
    {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return (string) $value;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return (string) $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return (string) $_SERVER[$key];
        }

        return (string) $default;
    }
}

if (!function_exists('app_request_scheme')) {
    function app_request_scheme()
    {
        $https = $_SERVER['HTTPS'] ?? '';
        if ($https === 'on' || $https === '1') {
            return 'https';
        }

        $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        if ($forwardedProto === 'https') {
            return 'https';
        }

        return 'http';
    }
}

if (!function_exists('app_request_host')) {
    function app_request_host()
    {
        $forwardedHost = trim((string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? ''));
        if ($forwardedHost !== '') {
            $parts = explode(',', $forwardedHost);
            return trim((string) $parts[0]);
        }

        $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($host !== '') {
            return $host;
        }

        return 'localhost';
    }
}

if (!function_exists('app_project_path_from_fs')) {
    function app_project_path_from_fs()
    {
        $documentRootRaw = $_SERVER['DOCUMENT_ROOT'] ?? '';
        if ($documentRootRaw === '') {
            return '';
        }

        $documentRoot = realpath($documentRootRaw);
        $projectRoot = realpath(dirname(__DIR__, 2));
        if (!$documentRoot || !$projectRoot) {
            return '';
        }

        $documentRootNorm = str_replace('\\', '/', $documentRoot);
        $projectRootNorm = str_replace('\\', '/', $projectRoot);
        if (stripos($projectRootNorm, $documentRootNorm) !== 0) {
            return '';
        }

        $relative = substr($projectRootNorm, strlen($documentRootNorm));
        $relative = str_replace('\\', '/', (string) $relative);
        $relative = trim($relative, '/');

        return $relative === '' ? '' : '/' . $relative;
    }
}

if (!function_exists('app_join_url')) {
    function app_join_url($base, $path = '')
    {
        $base = rtrim((string) $base, '/');
        $path = ltrim((string) $path, '/');

        if ($path === '') {
            return $base;
        }

        return $base . '/' . $path;
    }
}

if (!function_exists('app_url_base')) {
    function app_url_base()
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $configuredBase = trim(app_env_value('URL_BASE', ''));
        if ($configuredBase !== '') {
            if (preg_match('#^https?://#i', $configuredBase)) {
                $cached = rtrim($configuredBase, '/');
                return $cached;
            }

            $scheme = app_request_scheme();
            $host = app_request_host();
            $path = '/' . trim($configuredBase, '/');
            $path = $path === '/' ? '' : $path;
            $cached = $scheme . '://' . $host . $path;
            return $cached;
        }

        $scheme = app_request_scheme();
        $host = app_request_host();
        $projectPath = app_project_path_from_fs();
        $cached = $scheme . '://' . $host . $projectPath;

        return $cached;
    }
}

if (!function_exists('app_api_url')) {
    function app_api_url($path = '')
    {
        return app_join_url(app_url_base(), app_join_url('api', $path));
    }
}

if (!function_exists('app_asset_url')) {
    function app_asset_url($path = '')
    {
        return app_join_url(app_url_base(), ltrim((string) $path, '/'));
    }
}

