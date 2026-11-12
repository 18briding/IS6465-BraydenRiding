<?php
require_once 'includes/header.php';
require_once 'includes/nav.php';
?>

<body>
    <div class="container">
        <header>
            <h1>Log in to see rewards</h1>
        </header>

        <main>
            
            <!-- Directs user to card-list.php upon clicking Login -->
            <form action="card-list.php" method="get" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Login</button>
                </div>
            </form>
        </main>
    </div>

    <?php require_once 'includes/footer.php'; ?>