<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tugas 4 Pemrograman Web</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header>
            <h1 class="header-text">Welcome to my To-Do List App</h1>
            <button id="theme-toggle">Change theme</button>
        </header>
        <?php include "menubar.php"; ?>
        <?php include "content.php"; ?>
        
        <footer class="site-footer">
            <div class="footer-github">
                <a href="https://www.github.com/mindofkaka" target="_blank" rel="noopener noreferrer" class="github-link">
                    <img src="https://thumb.wikimedia.org/wikipedia/commons/thumb/c/c2/GitHub_Invertocat_Logo.svg/960px-GitHub_Invertocat_Logo.svg.png?utm_source=en.wikipedia.org&utm_campaign=index&utm_content=thumbnail" alt="GitHub Logo" class="github-logo">
                    <span class="github-text">GitHub</span>
                </a>
            </div>

            <div class="footer-copyright">
                &copy; Kaka 2026
            </div>

            <div>
                <a href="manifest.json" download>Click here to install as PWA</a>
            </div>
        </footer>
        <script src="script.js" defer></script>
    </body>
</html>
