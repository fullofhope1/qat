<?php
// includes/error_page.php

function showErrorPage($title, $msg, $detail = '')
{
?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - <?= htmlspecialchars($title) ?></title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            body {
                background: #f8f9fa;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: "Tajawal", sans-serif;
            }

            .err-card {
                max-width: 500px;
                width: 90%;
                border-radius: 15px;
                border: none;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            }

            .err-icon {
                font-size: 80px;
                color: #dc3545;
                margin-bottom: 20px;
            }
        </style>
    </head>

    <body>
        <div class="card err-card p-5 text-center">
            <div class="err-icon"><i class="fas fa-exclamation-circle"></i></div>
            <h3 class="fw-bold text-danger mb-3"><?= htmlspecialchars($title) ?></h3>
            <p class="fs-5 mb-4 text-dark"><?= htmlspecialchars($msg) ?></p>
            <?php if ($detail): ?>
                <div class="alert alert-warning small py-2" dir="ltr"><?= $detail ?></div>
            <?php endif; ?>
            <button onclick="history.back()" class="btn btn-secondary btn-lg w-100 fw-bold">عودة للتصحيح (Back)</button>
        </div>
    </body>

    </html>
<?php
    exit;
}
?>