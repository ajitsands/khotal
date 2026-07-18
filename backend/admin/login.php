<?php
require_once __DIR__ . '/../config/session_helper.php';
if (isset($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit;
}

// Fetch hotel name for custom login branding
require_once __DIR__ . '/../config/db_connection.php';
$hotelName = 'The K Hotel';
$hotelLogo = '';
try {
    $stmtS = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('hotel_name', 'hotel_logo')");
    $rows = $stmtS->fetchAll();
    foreach ($rows as $row) {
        if ($row['setting_key'] === 'hotel_name') $hotelName = $row['setting_value'];
        if ($row['setting_key'] === 'hotel_logo') $hotelLogo = $row['setting_value'];
    }
} catch (Exception $e) { /* ignore */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars($hotelName); ?> Loyalty Portal</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(22, 28, 45, 0.4);
            --border-color: rgba(251, 191, 36, 0.15);
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --primary: #d97706;
            --primary-hover: #b45309;
            --accent-gold: #fbbf24;
            --danger: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(217, 119, 6, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(251, 191, 36, 0.05) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 10;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-section img {
            max-height: 70px;
            margin-bottom: 12px;
            border-radius: 12px;
            border: 1px solid rgba(251, 191, 36, 0.2);
            padding: 4px;
            background: rgba(0,0,0,0.3);
        }

        .logo-icon {
            font-size: 40px;
            color: var(--accent-gold);
            margin-bottom: 15px;
            text-shadow: 0 0 15px rgba(251, 191, 36, 0.4);
            display: inline-block;
        }

        .logo-section h2 {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .logo-section p {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .form-group {
            margin-bottom: 22px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            transition: color 0.3s;
        }

        .form-control {
            width: 100%;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 13px 15px 13px 45px;
            font-size: 15px;
            color: #fff;
            outline: none;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--accent-gold);
            background: rgba(0, 0, 0, 0.4);
            box-shadow: 0 0 10px rgba(251, 191, 36, 0.15);
        }

        .form-control:focus + i {
            color: var(--accent-gold);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), #fbbf24);
            border: none;
            border-radius: 10px;
            color: #0b0f19;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(217, 119, 6, 0.3);
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(217, 119, 6, 0.5);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Toast message styling */
        .toast {
            position: fixed;
            top: 25px;
            right: 25px;
            padding: 15px 25px;
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            z-index: 1000;
            transform: translateY(-20px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast-success {
            background: #10b981;
            border-left: 5px solid #047857;
        }

        .toast-error {
            background: #ef4444;
            border-left: 5px solid #b91c1c;
        }

        .spinner {
            animation: spin 1s infinite linear;
            display: none;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="logo-section">
            <?php if (!empty($hotelLogo)): ?>
                <img src="<?php echo htmlspecialchars($hotelLogo); ?>" alt="Hotel Logo">
            <?php else: ?>
                <div class="logo-icon"><i class="fa-solid fa-hotel"></i></div>
            <?php endif; ?>
            <h2><?php echo htmlspecialchars($hotelName); ?></h2>
            <p>Loyalty Program Portal Login</p>
        </div>

        <form id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required autofocus autocomplete="username">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required autocomplete="current-password">
                    <i class="fa-solid fa-lock"></i>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <span id="btn-text">Sign In</span>
                <i class="fa-solid fa-spinner spinner" id="btn-spinner"></i>
            </button>
        </form>
    </div>

    <!-- Toast Alert Container -->
    <div id="toast" class="toast">
        <span id="toast-icon"></span>
        <span id="toast-message"></span>
    </div>

    <script>
        function showToast(message, isError = false) {
            const toast = $('#toast');
            const icon = $('#toast-icon');
            
            toast.removeClass('toast-success toast-error show');
            toast.addClass(isError ? 'toast-error' : 'toast-success');
            
            icon.html(isError ? '<i class="fa-solid fa-triangle-exclamation"></i>' : '<i class="fa-solid fa-circle-check"></i>');
            $('#toast-message').text(message);
            
            setTimeout(() => {
                toast.addClass('show');
            }, 50);
            
            setTimeout(() => {
                toast.removeClass('show');
            }, 4000);
        }

        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            $('#btn-text').text('Signing In...');
            $('#btn-spinner').show();
            $('button[type="submit"]').prop('disabled', true);
            
            $.ajax({
                url: 'admin_actions.php?action=login',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#btn-spinner').hide();
                    if (response.success) {
                        showToast("Authentication successful! Redirecting...");
                        setTimeout(() => {
                            window.location.href = 'index.php';
                        }, 1000);
                    } else {
                        showToast(response.message, true);
                        $('#btn-text').text('Sign In');
                        $('button[type="submit"]').prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    $('#btn-spinner').hide();
                    $('#btn-text').text('Sign In');
                    $('button[type="submit"]').prop('disabled', false);
                    const msg = xhr.responseJSON?.message || "Failed to establish server connection.";
                    showToast(msg, true);
                }
            });
        });
    </script>
</body>
</html>
