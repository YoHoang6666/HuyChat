<?php
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function display_error($message) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>';
}

function display_success($message) {
    echo '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
}
?>