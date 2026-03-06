<?php
$b = ['c' => 'Hello, World!']; // Example array
$a = ""; // Default value

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $b['c']; // Assign value on button click
}
?>

<form method="post">
    <button type="submit">Set $a</button>
</form>

<p>Value of $a: <?php echo htmlspecialchars($a); ?></p>