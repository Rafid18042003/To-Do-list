<?php
$filename = "tasks.json"; // 🔹 File penyimpanan data

// 🔹 Ambil data dari file JSON jika ada
if (file_exists($filename)) {
    $tasks = json_decode(file_get_contents($filename), true);
} else {
    $tasks = [];
}

// 🔹 Tambah tugas baru (mode normal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task']) && !isset($_POST['edit_id'])) {
    $newTask = [
        "id" => time(), // ID unik
        "title" => htmlspecialchars($_POST['task']),
        "status" => "belum"
    ];
    $tasks[] = $newTask;
    file_put_contents($filename, json_encode($tasks, JSON_PRETTY_PRINT));
    header("Location: index.php");
    exit;
}

// 🔹 Simpan hasil edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    foreach ($tasks as &$task) {
        if ($task['id'] == $_POST['edit_id']) {
            $task['title'] = htmlspecialchars($_POST['task']);
            break;
        }
    }
    file_put_contents($filename, json_encode($tasks, JSON_PRETTY_PRINT));
    header("Location: index.php");
    exit;
}

// 🔹 Ubah status selesai/belum
if (isset($_GET['toggle'])) {
    foreach ($tasks as &$task) {
        if ($task['id'] == $_GET['toggle']) {
            $task['status'] = $task['status'] === "belum" ? "selesai" : "belum";
            break;
        }
    }
    file_put_contents($filename, json_encode($tasks, JSON_PRETTY_PRINT));
    header("Location: index.php");
    exit;
}

// 🔹 Hapus tugas
if (isset($_GET['hapus'])) {
    $tasks = array_filter($tasks, fn($task) => $task['id'] != $_GET['hapus']);
    file_put_contents($filename, json_encode(array_values($tasks), JSON_PRETTY_PRINT));
    header("Location: index.php");
    exit;
}

// 🔹 Ambil data tugas yang sedang diedit
$editTask = null;
if (isset($_GET['edit'])) {
    foreach ($tasks as $task) {
        if ($task['id'] == $_GET['edit']) {
            $editTask = $task;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>To-Do List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- 🔹 Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h1 class="text-center mb-4">📝 To-Do List</h1>

    <!-- 🔹 Form Tambah / Edit -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="POST" class="d-flex gap-2">
                <input type="text" name="task" class="form-control"
                       value="<?= $editTask ? htmlspecialchars($editTask['title']) : '' ?>"
                       placeholder="<?= $editTask ? 'Edit tugas...' : 'Tambahkan tugas baru...' ?>" required>

                <?php if ($editTask): ?>
                    <input type="hidden" name="edit_id" value="<?= $editTask['id'] ?>">
                    <button type="submit" class="btn btn-warning">💾 Simpan</button>
                    <a href="index.php" class="btn btn-secondary">✖ Batal</a>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">+ Tambah</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- 🔹 Daftar Tugas -->
    <div class="card">
        <div class="card-header fw-bold">Daftar Tugas</div>
        <ul class="list-group list-group-flush">
            <?php if (empty($tasks)): ?>
                <li class="list-group-item text-muted">Belum ada tugas.</li>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center 
                        <?= $task['status'] === 'selesai' ? 'text-decoration-line-through text-muted' : '' ?>">
                        <div>
                            <?= $task['title'] ?>
                            <!-- 🔸 Badge oranye -->
                            <span class="badge bg-warning text-dark ms-2"><?= $task['status'] ?></span>
                        </div>
                        <div class="d-flex">
                            <!-- 🔹 Tombol Ubah Status -->
                            <a href="?toggle=<?= $task['id'] ?>" class="btn btn-warning btn-sm me-2">✔</a>
                            <!-- 🔹 Tombol Edit -->
                            <a href="?edit=<?= $task['id'] ?>" class="btn btn-secondary btn-sm me-2">✏️</a>
                            <!-- 🔹 Tombol Hapus -->
                            <a href="?hapus=<?= $task['id'] ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Hapus tugas ini?')">🗑</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>

<!-- 🔹 Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
