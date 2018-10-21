<?php
$db = null;
$title = 'TITLE';
$snippet = 'SNIPPET';
$url_public = 'URL_PUBLIC';
if (!isset($_GET['view'])) {
    http_response_code(404);
    die('Not found');
} else {
    try {
        $db = new SQLite3("data/db.sqlite");

        $statement = $db->prepare("SELECT title, snippet, url_public FROM snippets WHERE url_public=:url_public");
        $statement->bindValue(':url_public', $_GET['view']);
        $result = $statement->execute();
        if ($result !== false) {
            $row = $result->fetchArray(SQLITE3_ASSOC);
            if ($row === false) {
                http_response_code(404);
                die('Not found');
            } else {
                $result->finalize();

                $title = $row['title'];
                $snippet = $row['snippet'];
                $url_public = $row['url_public'];
            }
        }
    } catch (Exception $e) {
        echo $e;
    } finally {
        if ($db !== null) {
            $db->close();
        }
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
    <link rel="stylesheet" href="css/railscasts.css">
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

        .hljs-ln td.hljs-ln-numbers {
            text-align: right;
            padding-right: 1rem;
            color: gray;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card mt-4 mb-4">
        <div class="card-header text-center">
            <h1>
                <a href="./">snippets.aoe2map.net</a>
            </h1>
            <p class="lead">Share Age of Empires 2 Random Map Script snippets online</p>
        </div>
        <div class="card-body">
            <div class="card-text">
                <h5 class="card-title">
                    <a href="./<?php echo $url_public; ?>"><?php echo htmlspecialchars($title); ?></a>
                </h5>
                <pre><code class="rms" id="codearea"><?php echo htmlspecialchars($snippet); ?></code></pre>
            </div>
        </div>
    </div>
</div>
<script src="js/highlight.js"></script>
<script src="js/highlight.js-rms.js"></script>
<script src="js/highlightjs-line-numbers.min.js"></script>
<script type="text/javascript">
    function onload() {
        hljs.registerLanguage('rmslanguage', rmslanguage);
        hljs.initHighlighting();
    }

    addEventListener('DOMContentLoaded', onload, false);
    hljs.initLineNumbersOnLoad();
</script>
</body>
</html>