<?php
// Temporary script to install Livewire
$output = shell_exec('cd .. && composer require livewire/livewire 2>&1');
echo "<pre>Output:\n$output</pre>";
