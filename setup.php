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
    $db->exec("CREATE TABLE IF NOT EXISTS snippets(
`id` INTEGER PRIMARY KEY,
`title` VARCHAR(255) NOT NULL,
`url_public` VARCHAR(127) NOT NULL UNIQUE,
`url_private` VARCHAR(127) NOT NULL UNIQUE,
`snippet` TEXT NOT NULL,
`created` DATETIME DEFAULT CURRENT_TIMESTAMP,
`updated` DATETIME DEFAULT CURRENT_TIMESTAMP
)");
    $db->exec("CREATE TABLE IF NOT EXISTS  versions(
`id` INTEGER PRIMARY KEY,
`version` INTEGER NOT NULL,
`updated` DATETIME DEFAULT CURRENT_TIMESTAMP
)");

    $result = $db->query("SELECT MAX(version) FROM versions");
    if ($result === false) {
        throw new Exception("ERROR querying versions table");
    } else {
        $row = $result->fetchArray();
        $currentVersion = 0;
        if ($row[0] === null) {
            $db->exec("INSERT INTO versions(version) VALUES (0)");
        } else {
            $currentVersion = $row[0];
        }

        if ($currentVersion < 1) {
            $db->exec("ALTER TABLE snippets ADD COLUMN public BOOLEAN NOT NULL DEFAULT 0 CHECK (public IN (0,1))");
            $db->exec("INSERT INTO versions(version) VALUES (1)");
        }
    }
    echo "OK";
} catch (Exception $e) {
    echo $e;
} finally {
    $db->close();
}

?>
</body>
</html>