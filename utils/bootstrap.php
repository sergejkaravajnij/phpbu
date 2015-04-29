<?php
// make class accessible
require 'CopycomWithDeleting.php';

// register class as source with override
try {
	phpbu\App\Factory::register('sync', 'copycom', '\\sk\\CopycomWithDeleting', true);
} catch (Exception $e) {
	die($e->getMessage());
}