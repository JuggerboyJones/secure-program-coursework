<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/database.php';

header('Content-Type: text/html; charset=UTF-8');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; form-action 'self'; base-uri 'self'; frame-ancestors 'none'");
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

// This first milestone is a read-only form preview, not a submission endpoint.
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($method, ['GET', 'HEAD'], true)) {
    header('Allow: GET, HEAD');
    http_response_code(405);
    echo 'This setup preview does not accept report submissions.';
    exit;
}

$connected = false;
$categories = [];

try {
    $categories = database()->query('SELECT id, name FROM categories ORDER BY id')->fetchAll();
    $connected = count($categories) > 0;
} catch (Throwable $exception) {
    error_log('Incident Reporting: the database connection or category query failed.');
}

if (!$connected) {
    http_response_code(503);
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Report an incident | Incident Reporting</title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
    <header class="site-header">
        <div class="brand">
            <span class="brand-mark" aria-hidden="true">IR</span>
            <span>Incident Reporting</span>
        </div>
        <span class="preview-label">Local setup preview</span>
    </header>

    <main>
        <div class="page-heading">
            <p class="eyebrow">Incident Reporting and Management System</p>
            <h1>Report an incident</h1>
            <p class="intro">Choose a category and describe what happened.</p>
        </div>

        <section class="form-card" aria-labelledby="form-title">
            <div class="card-heading">
                <h2 id="form-title">Incident details</h2>
                <span class="status <?= $connected ? 'status-ready' : 'status-waiting' ?>">
                    <span class="status-dot" aria-hidden="true"></span>
                    <?= $connected ? 'Database connected' : 'Database unavailable' ?>
                </span>
            </div>

            <?php if (!$connected): ?>
                <p class="connection-notice" role="alert">The database is not ready. Start the project services, then refresh this page.</p>
            <?php endif; ?>

            <form method="post" action="/" aria-describedby="preview-note">
                <div class="field">
                    <label for="category">Incident category</label>
                    <select id="category" name="category_id" required <?= !$connected ? 'disabled' : '' ?>>
                        <option value="" selected disabled>Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= escape((string) $category['id']) ?>"><?= escape($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="description">What happened?</label>
                    <p id="description-help" class="field-help">Include the details that will help someone understand the incident.</p>
                    <textarea id="description" name="description" rows="7" maxlength="5000" required aria-describedby="description-help" placeholder="Describe the incident..."></textarea>
                </div>

                <div class="form-footer">
                    <p id="preview-note">This is a setup preview. Report submission will be enabled when sign-in is connected.</p>
                    <button type="submit" disabled>Submit report</button>
                </div>
            </form>
        </section>

        <p class="setup-result">
            <?php if ($connected): ?>
                PHP is running, MySQL is connected, and the incident categories have loaded.
            <?php else: ?>
                PHP is running. The database connection still needs to be checked.
            <?php endif; ?>
        </p>
    </main>

    <footer class="site-footer">Jubri Mustapha &middot; Bridget Osagiede Egwim </footer>
</body>
</html>
