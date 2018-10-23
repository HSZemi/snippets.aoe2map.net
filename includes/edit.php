<?php
include 'functions.php';
$db = null;
$title = 'TITLE';
$snippet = 'SNIPPET';
$url_private = 'URL_PRIVATE';
$url_public = 'URL_PUBLIC';
$updated = false;
$new_snippet = false;
if (!isset($_GET['edit'])) {
    http_response_code(404);
    die('Not found');
} else {
    $url_private = $_GET['edit'];
    if ($url_private === "new") {
        $new_snippet = true;
    }
    try {
        $db = new SQLite3("data/db.sqlite");

        if (isset($_POST['titleInput']) && isset($_POST['rmsInput'])) {
            if ($new_snippet) {
                $url_private = getNewPrivateUrl($db);
                $url_public = getNewPublicUrl($db);

                $insertStatement = $db->prepare("INSERT INTO snippets(title, snippet, url_private, url_public) VALUES (:title, :snippet, :url_private, :url_public)");
                $insertStatement->bindValue(':title', $_POST['titleInput']);
                $insertStatement->bindValue(':snippet', $_POST['rmsInput']);
                $insertStatement->bindValue(':url_private', $url_private);
                $insertStatement->bindValue(':url_public', $url_public);
                $inserted = $insertStatement->execute();
                if ($inserted !== false) {
                    header("Location: ./$url_private", true, 303);
                    die();
                }
            } else {
                $updateStatement = $db->prepare("UPDATE snippets SET title=:title, snippet=:snippet, updated=CURRENT_TIMESTAMP WHERE url_private=:url_private");
                $updateStatement->bindValue(':title', $_POST['titleInput']);
                $updateStatement->bindValue(':snippet', $_POST['rmsInput']);
                $updateStatement->bindValue(':url_private', $url_private);
                $updated = $updateStatement->execute();
            }
        }

        if ($new_snippet) {
            $title = "";
            $snippet = "";
        } else {
            $statement = $db->prepare("SELECT title, snippet, url_private, url_public FROM snippets WHERE url_private=:url_private");
            $statement->bindValue(':url_private', $_GET['edit']);
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
                    $url_private = $row['url_private'];
                }
            }
        }
    } catch
    (Exception $e) {
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
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/railscasts.css">
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

        #rmsInput {
            font-family: "Courier New", monospace;
            font-size: .8rem;
            border: 1px solid gray;
            background-color: #444;
            color: inherit;
        }
    </style>
</head>
<body spellcheck="false">
<div class="container">
    <div class="card mt-4 mb-4">
        <div class="card-header text-center">
            <h1>
                <a href="../">snippets.aoe2map.net</a>
            </h1>
            <p class="lead">Share Age of Empires 2 Random Map Script snippets online</p>
        </div>
        <div class="card-body">

            <?php if (!$new_snippet) { ?>

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Public sharing URL</span>
                    </div>
                    <input type="text" class="form-control" aria-label="Public URL"
                           aria-describedby="copyPublicUrlButton"
                           id="publicUrlInput" value="https://snippets.aoe2map.net/<?php echo $url_public; ?>" disabled>
                    <div class="input-group-append">
                        <a class="btn btn-secondary" type="button" href="../<?php echo $url_public; ?>"
                           target="_blank">Open</a>
                    </div>
                    <div class="input-group-append">
                        <button class="btn btn-secondary" type="button" id="copyPublicUrlButton">Copy</button>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text text-danger">Secret edit URL</span>
                    </div>
                    <input type="text" class="form-control" aria-label="Private URL"
                           aria-describedby="copyPrivateUrlButton"
                           id="privateUrlInput" value="https://snippets.aoe2map.net/edit/<?php echo $url_private; ?>"
                           disabled>
                    <div class="input-group-append">
                        <button class="btn btn-secondary" type="button" id="copyPrivateUrlButton">Copy</button>
                    </div>
                </div>

                <hr>

            <?php } ?>

            <div class="card-text">

                <?php if ($updated) { ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Saved!</strong> You can continue editing the snippet.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"
                                onclick="this.parentNode.parentNode.removeChild(this.parentNode)">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php } ?>

                <form method="POST">
                    <div class="row">
                        <div class="col-10">
                            <div class="form-group">
                                <label for="titleInput">Snippet Title</label>
                                <input type="text" class="form-control" id="titleInput" name="titleInput"
                                       aria-describedby="titleInputHelp"
                                       value="<?php echo htmlspecialchars($title); ?>">
                            </div>
                        </div>
                        <div class="col-2">
                            <button type="submit" class="btn btn-lg btn-block btn-primary" style="height: 100%;">Save
                            </button>
                        </div>
                    </div>

                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" id="tabWrite" href="#">Write</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tabPreview" href="#">Preview</a>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link btn btn-secondary" id="tabAutoformat" type="button">Autoformat
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="write">
                        <div class="form-group">
                            <textarea id="rmsInput" name="rmsInput" class="col-12"
                                      rows="20"><?php echo htmlspecialchars($snippet); ?></textarea>
                        </div>
                    </div>
                    <div class="tab-content" id="preview" style="display: none;">
                        <pre><code class="rms" id="codearea"></code></pre>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="../js/highlight.js"></script>
<script src="../js/highlight.js-rms.js"></script>
<script src="../js/highlightjs-line-numbers.min.js"></script>
<script type="text/javascript">
    function autoformat() {
        let oldValue = document.querySelector('#rmsInput').value;
        document.querySelector('#rmsInput').value = normalizeMap(oldValue);
        updatePreviewArea();
    }

    hljs.registerLanguage('rmslanguage', rmslanguage);

    function updatePreviewArea() {
        let codearea = document.querySelector('#codearea');
        codearea.textContent = document.querySelector('#rmsInput').value;
        hljs.highlightBlock(codearea);
        hljs.lineNumbersBlock(codearea, {singleLine: true});
    }

    document.querySelector('#tabPreview').addEventListener('click', function (e) {
        e.preventDefault();
        updatePreviewArea();
        document.querySelector('#tabWrite').classList.remove('active');
        document.querySelector('#tabPreview').classList.add('active');
        document.querySelector('#write').style.display = 'none';
        document.querySelector('#preview').style.display = 'block';
    });

    document.querySelector('#tabWrite').addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector('#tabWrite').classList.add('active');
        document.querySelector('#tabPreview').classList.remove('active');
        document.querySelector('#write').style.display = 'block';
        document.querySelector('#preview').style.display = 'none';
    });

    let copyPublicUrlButton = document.querySelector('#copyPublicUrlButton');
    if (copyPublicUrlButton !== null) {
        copyPublicUrlButton.addEventListener('click', function () {
            copyToClipboard(document.querySelector('#publicUrlInput').value);
            document.querySelector('#copyPublicUrlButton').innerHTML = 'Copied!';
            setTimeout(function () {
                document.querySelector('#copyPublicUrlButton').innerHTML = 'Copy';
            }, 1500);
        });
    }

    let copyPrivateUrlButton = document.querySelector('#copyPrivateUrlButton');
    if (copyPrivateUrlButton !== null) {
        copyPrivateUrlButton.addEventListener('click', function () {
            copyToClipboard(document.querySelector('#privateUrlInput').value);
            document.querySelector('#copyPrivateUrlButton').innerHTML = 'Copied!';
            setTimeout(function () {
                document.querySelector('#copyPrivateUrlButton').innerHTML = 'Copy';
            }, 1500);
        });
    }

    document.querySelector('#tabAutoformat').addEventListener('click', function () {
        autoformat();
    });

    function copyToClipboard(str) {
        const el = document.createElement('textarea');
        el.value = str;
        el.setAttribute('readonly', '');
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        const selected =
            document.getSelection().rangeCount > 0
                ? document.getSelection().getRangeAt(0)
                : false;
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        if (selected) {
            document.getSelection().removeAllRanges();
            document.getSelection().addRange(selected);
        }
    }

</script>
</body>
</html>