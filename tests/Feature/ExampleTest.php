<?php

test('halaman utama mengalihkan ke login', function () {
    $this->get('/')->assertRedirect('/home');
});
