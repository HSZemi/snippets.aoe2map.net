<?php
$db = null;
$snippets = [];
try {
    $db = new SQLite3("data/db.sqlite");

    $result = $db->query("SELECT title, url_public, updated FROM snippets WHERE public=1 ORDER BY updated DESC LIMIT 154");
    if ($result !== false) {
        while (($row = $result->fetchArray(SQLITE3_ASSOC)) !== false) {
            $snippets[] = ['title' => $row['title'], 'url_public' => $row['url_public']];
        }
    }
} finally {
    if ($db !== null) {
        $db->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>snippets.aoe2map.net</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        html,
        body {
            height: 100%;
        }

        .container {
            min-height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        #btn-show-snippets,
        #linklist a {
            color: silver;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="jumbotron text-center">
        <h1 class="display-4">snippets.aoe2map.net</h1>
        <p class="lead">Share Age of Empires 2 Random Map Script snippets online</p>
        <hr class="my-4">
        <div>
            <a class="btn btn-primary btn-lg" href="./edit/new" id="a-new-snippet">New Snippet…</a>
        </div>

        <div>
            <button class="btn btn-link mt-4" id="btn-show-snippets">Show Snippets</button>
        </div>

        <div class="text-left" id="linklist">
            <ul class="list-unstyled">
                <?php
                foreach ($snippets as $snippet) {
                    $title = $snippet['title'];
                    $url = $snippet['url_public'];
                    echo "<li><a href='./$url'>$title</a></li>\n";
                }
                ?>
            </ul>
        </div>
    </div>
</div>
<script type="text/javascript">
    let linklist = document.getElementById('linklist');
    let button = document.getElementById('btn-show-snippets');
    linklist.style.display = 'none';
    button.addEventListener('click', function () {
        if (linklist.style.display === 'none') {
            linklist.style.display = 'block';
        } else {
            linklist.style.display = 'none';
        }
    });
</script>
</body>
</html>