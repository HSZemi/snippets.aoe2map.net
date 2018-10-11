<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>snippets.aoe2map.net</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
</head>
<body>
<h1>Setup</h1>

<?php
try {
    $db = new SQLite3("data/db.sqlite");
    $db->exec("CREATE TABLE snippets(
`id` INTEGER PRIMARY KEY,
`title` VARCHAR(255) NOT NULL,
`url_public` VARCHAR(127) NOT NULL UNIQUE,
`url_private` VARCHAR(127) NOT NULL UNIQUE,
`snippet` TEXT NOT NULL
)");

    echo "OK";
} catch (Exception $e) {
    echo $e;
}

?>
</body>
</html>