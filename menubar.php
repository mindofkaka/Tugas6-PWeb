<?php
$curType = isset($_GET['type']) && in_array($_GET['type'], ['personal', 'shared']) ? $_GET['type'] : 'personal';
?>

<div class="menu-bar">
    <nav>
        <a href="index.php?type=personal" class="menu-item <?= $curType === 'personal' ? 'active' : '' ?>">Personal</a>
        <a href="index.php?type=shared" class="menu-item <?= $curType === 'shared' ? 'active' : '' ?>">Shared</a>
    </nav>
</div>
