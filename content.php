<?php 
include_once "config.php";

$type = isset($_GET['type']) && in_array($_GET['type'], ['personal', 'shared']) ? $_GET['type'] : 'personal';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['action']) && $_POST['action'] === 'create_task') {
    $title = trim($_POST['task-title']);
    $description = trim($_POST['task-description']);
    $deadline = !empty($_POST['task-deadline']) ? trim($_POST['task-deadline']) : NULL;
    $notify_at = !empty($_POST['task-notify']) ? trim($_POST['task-notify']) : NULL;
    $task_type = $_POST['task-type'];
    $attachment = NULL;

    if(isset($_FILES['image-file']) && $_FILES['image-file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if(!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['image-file']['name']);
        $targetFile = $uploadDir . $fileName;
        if(move_uploaded_file($_FILES['image-file']['tmp_name'], $targetFile)) {
            $attachment = $targetFile;
        }
    }

    $stmt = $conn->prepare("INSERT INTO todos (title, description, status, type, deadline, notify_at, attachment) VALUES (?, ?, 'not-started', ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $title, $description, $task_type, $deadline, $notify_at, $attachment);
    $stmt->execute();
    header("Location: index.php?type=" . $task_type);
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_task') {
    $task_id = intval($_POST['task-id']);
    $title = trim($_POST['task-title']);
    $description = trim($_POST['task-description']);
    $status = $_POST['task-status'];
    $deadline = !empty($_POST['task-deadline']) ? trim($_POST['task-deadline']) : NULL;
    $notify_at = !empty($_POST['task-notify']) ? trim($_POST['task-notify']) : NULL;
    $stmt = $conn->prepare('UPDATE todos SET title = ?, description = ?, status = ?, deadline = ?, notify_at = ? WHERE id = ?');
    $stmt->bind_param("sssssi", $title, $description, $status, $deadline, $notify_at, $task_id);
    $stmt->execute();
    header("Location: index.php?type=" . $type);
    exit;
}

if(isset($_GET['toggle_id'])) {
    $toggle_id = intval($_GET['toggle_id']);
    $conn->query("UPDATE todos SET status = IF(status='completed', 'not-started', 'completed') WHERE id = $toggle_id");
    header("Location: index.php?type=" . $type);
    exit;
}

if(isset($_GET["delete_id"])) {
    $delete_id = intval($_GET["delete_id"]);
    $res = $conn->query("SELECT attachment FROM todos WHERE id = $delete_id");
    if($row = $res->fetch_assoc()) {
        if(!empty($row['attachment']) && file_exists($row['attachment'])) {
            unlink($row['attachment']);
        }
    }
    $conn->query("DELETE FROM todos WHERE id = $delete_id");
    header("Location: index.php?type=" . $type);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM todos WHERE type = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $type);
$stmt->execute();
$result = $stmt->get_result();
$tasks = $result->fetch_all(MYSQLI_ASSOC);
?>

<aside>
    <div class="task-list">
        <h3 class="list-title"> <?= ucfirst($type) ?> Task List </h3>
        <ul class="task-section"> 
            <?php if(count($tasks) > 0): ?>
                <?php foreach($tasks as $task): ?>
                    <li class="task-text" onclick="selectTask(<?= htmlspecialchars(json_encode($task)) ?>)">
                        <input type="checkbox" class="task-check" <?= $task['status'] === 'completed' ? 'checked' : '' ?> onclick="event.stopPropagation(); window.location.href='index.php?type=<?= $type ?>&toggle_id=<?= $task['id'] ?>';">
                        <span class="task-label <?= $task['status'] === 'completed' ? 'task-done' : ($task['status'] === 'in-progress' ? 'task-in-progress' : '') ?>">
                            <?= htmlspecialchars($task['title']) ?>
                        </span>
                        <a href="index.php?type=<?= $type ?>&delete_id=<?= $task['id'] ?>" class="delete-task" onclick="event.stopPropagation(); return confirm('Hapus task ini?');" style="text-decoration: none;">Delete</a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Tidak ada task <?= $type ?> ditemukan.</p>
            <?php endif; ?>
        </ul>
    </div>

    <div class="task-details">
        <h3 class="details-title">Task Details</h3>
        <p class="detail-text">Pilih task untuk melihat atau memperbarui detailnya.</p>
        <form action="index.php?type=<?= $type ?>" method="POST" id="detail-form"> 
            <input type="hidden" name="action" value="update_task">
            <input type="hidden" name="task-id" id="detail-task-id">

            <div class="form-group">
                <h5 class="details-subtitle">Title</h5>
                <input type="text" name="task-title" id="detail-title" placeholder="Enter task title" required><br>
            </div>
            <div class="form-group">
                <h5 class="details-subtitle">Description</h5>
                <textarea name="task-description" id="detail-description" placeholder="Enter task description"></textarea><br>
            </div>
            <div class="form-group">
                <h5 class="details-subtitle">Status</h5>
                <select name="task-status" id="detail-status">
                    <option value="not-started">Not Started</option>
                    <option value="in-progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select><br>
            </div>
            <div class="form-group">
                <h5 class="details-subtitle">Deadline</h5>
                <input type="date" name="task-deadline" id="detail-deadline"><br>
            </div>
            <div class="form-group">
                <h5 class="details-subtitle">Notify At</h5>
                <div class="field-wrap">
                    <input type="datetime-local" name="task-notify" id="detail-notify"><br>
                    <small class="field-note">You can leave `notify-at` time empty</small>
                </div>
            </div>
            <div class="form-group">
                <h5 class="details-subtitle">Image/Camera</h5>
                <div class="image-field">
                    <img id="task-image-preview" class="image-preview" alt="Task image" hidden>
                    <video id="camera-video" class="camera-video" autoplay playsinline muted hidden></video>
                    <canvas id="camera-canvas" hidden></canvas>
                    <div class="image-actions">
                        <button type="button" id="open-camera" class="image-button">Open Camera</button>
                        <button type="button" id="capture-photo" class="image-button" hidden>Capture Image</button>
                        <button type="button" id="stop-camera" class="image-button" hidden>Cancel</button>
                        <label class="image-button">Choose Photo
                            <input type="file" id="image-file" accept="image/*" name="image-file" capture="environment" hidden>
                        </label>
                        <button type="button" id="remove-image" class="image-button image-remove" hidden>Remove Image</button>
                    </div>
                    <p id="camera-message" class="camera-message" role="status"></p>
                </div>
            </div>
            <button type="submit" class="details-button">Update task</button>
        </form>
    </div>
</aside>

<form class="task-form" id="task-form" action="index.php?type=<?= $type ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="create_task">
    <input type="hidden" name="task-type" value="<?= $type ?>">
    <h2 class="details-title">Create New <?= ucfirst($type) ?> Task </h2>
    <div class="form-group">
        <label class="form-text">Task Title</label>
        <input type="text" name="task-title" placeholder="Enter task title" required><br>
    </div>
    <div class="form-group">
        <label class="form-text">Task Description</label>
        <textarea name="task-description" placeholder="Enter task description"></textarea><br>
    </div>
    <div class="form-group">
        <label class="form-text">Task Deadline</label>
        <input type="date" name="task-deadline"><br>
    </div>
    <div class="form-group">
        <label class="form-text">Reminder Time</label>
        <div class="field-wrap">
            <input type="datetime-local" name="task-notify"><br>
            <small class="field-note">You can leave reminder time empty</small>
        </div>
    </div>
    <div class="form-group">
        <label class="form-text">Photo</label>
        <input type="file" name="image-file" accept="image/*"><br>
    </div>
    <button type="submit" class="form-button">Create Task</button>
</form>

<!-- <script src="script.js"></script> -->