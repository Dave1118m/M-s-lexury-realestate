<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f4f4; color: #333; }
        .page { max-width: 680px; margin: 10vh auto; padding: 2rem; background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); text-align: center; }
        h1 { font-size: 3rem; margin-bottom: 0.5rem; }
        p { margin: 0.75rem 0; color: #555; }
        a { color: #1a73e8; text-decoration: none; font-weight: 600; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="page">
        <h1>404 Not Found</h1>
        <p>The page you requested was not found on this server.</p>
        <p><a href="/">Return to Home</a></p>
    </div>
</body>
</html>
