<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Panel - Usaha Rosok</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap538/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    
    <style>
        body {
            background-color: var(--ink); 
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-box {
            background-color: var(--paper);
            padding: 40px 30px;
            border-radius: 12px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }
        .brand-title {
            color: var(--ink);
            font-size: 24px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 2px;
        }
        .brand-sub {
            color: var(--rust);
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            text-align: center;
            display: block;
            margin-bottom: 30px;
            font-weight: 500;
        }
    </style>
</head>
<body>

    <div class="login-box">
        <div class="brand-title">Usaha Rosok</div>
        <span class="brand-sub">Admin Panel Auth</span>

        <?php if($this->session->flashdata('error')): ?>
            <div class="alert alert-danger border-0 text-center" style="font-size: 13px; padding: 10px;">
                <?= $this->session->flashdata('error') ?>
            </div>
        <?php endif; ?>
        
        <form action="<?= base_url('auth/process') ?>" method="POST">
            <div class="mb-3">
                <label class="form-label text-muted" style="font-size: 12px; font-weight: 500;">Username</label>
                <input type="text" name="username" class="form-control form-control-lg" style="font-size: 14px;" required autofocus autocomplete="off">
            </div>
            <div class="mb-4">
                <label class="form-label text-muted" style="font-size: 12px; font-weight: 500;">Password</label>
                <input type="password" name="password" class="form-control form-control-lg" style="font-size: 14px;" required>
            </div>
            
            <button type="submit" class="btn btn-rust w-100 py-2" style="font-size: 14.5px;">
                Masuk Sistem
            </button>
        </form>
    </div>

</body>
</html>