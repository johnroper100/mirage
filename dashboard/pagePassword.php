<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($documentTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="robots" content="noindex, nofollow">
    <?php echo $socialMetaTags; ?>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: linear-gradient(160deg, #f7fafc 0%, #edf2f7 45%, #e2e8f0 100%);
            color: #0f172a;
            font-family: Arial, sans-serif;
        }

        .mirage-protected-page {
            width: min(100%, 28rem);
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 1rem;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
            padding: 2rem;
        }

        .mirage-protected-page__eyebrow {
            display: inline-block;
            margin-bottom: 0.85rem;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #1d4ed8;
        }

        .mirage-protected-page h1 {
            margin: 0 0 0.75rem;
            font-size: 1.8rem;
            line-height: 1.15;
        }

        .mirage-protected-page p {
            margin: 0 0 1rem;
            line-height: 1.6;
            color: #475569;
        }

        .mirage-protected-page label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
            color: #0f172a;
        }

        .mirage-protected-page input {
            width: 100%;
            padding: 0.85rem 0.95rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.75rem;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .mirage-protected-page button {
            width: 100%;
            margin-top: 1rem;
            padding: 0.9rem 1rem;
            border: 0;
            border-radius: 0.75rem;
            background: #0f172a;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        .mirage-protected-page button:hover {
            background: #1e293b;
        }

        .mirage-protected-page__alert {
            margin-bottom: 1rem;
            padding: 0.8rem 0.9rem;
            border: 1px solid #fecaca;
            border-radius: 0.75rem;
            background: #fef2f2;
            color: #b91c1c;
        }

        .mirage-protected-page__meta {
            margin-top: 1.25rem;
            font-size: 0.9rem;
            color: #64748b;
        }
    </style>
</head>
<body>
    <main class="mirage-protected-page">
        <div class="mirage-protected-page__eyebrow">Password Protected</div>
        <h1><?php echo htmlspecialchars($promptTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p>This page is protected. Enter the password to continue.</p>
        <?php if ($description !== '') { ?>
            <p><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php } ?>
        <?php if ($errorMessage !== '') { ?>
            <div class="mirage-protected-page__alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>
        <form method="POST" action="<?php echo htmlspecialchars($actionUrl, ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo getCsrfTokenFieldHtml(); ?>
            <label for="mirage-page-password">Password</label>
            <input id="mirage-page-password" name="pagePassword" type="password" autocomplete="current-password" required>
            <button type="submit">Open Page</button>
        </form>
        <div class="mirage-protected-page__meta"><?php echo htmlspecialchars($siteTitleLabel, ENT_QUOTES, 'UTF-8'); ?></div>
    </main>
</body>
</html>
