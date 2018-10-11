<?php

if (isset($_GET['view'])) {
    include('includes/view.php');
} elseif (isset($_GET['edit'])) {
    include('includes/edit.php');
} else {
    include('includes/start.php');
}
