<?php
function hash_password(string $plain): string {
    return password_hash($plain, PASSWORD_DEFAULT);
}

function verify_password_hash(string $plain, string $hash): bool {
    return password_verify($plain, $hash);
}

function needs_hash(string $hash): bool {
    return password_needs_rehash($hash, PASSWORD_DEFAULT);
}
?>