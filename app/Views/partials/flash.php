<?php

use App\Core\Session;

$success = Session::flash('success');
$error = Session::flash('error');
?>
<?php if ($success !== null): ?>
    <div class="flash flash-success" role="status"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error !== null): ?>
    <div class="flash flash-error" role="alert"><?= e($error) ?></div>
<?php endif; ?>
