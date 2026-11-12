<?php

function validateArticle($data): array
{
    $data = is_array($data) ? $data : [];
    $values = [];
    foreach (['title', 'slug', 'body'] as $field) {
        $value = $data[$field] ?? '';
        $values[$field] = is_string($value) ? trim($value) : '';
    }

    $errors = [];
    if ($values['title'] === '') {
        $errors['title'] = 'Escribe el título.';
    }
    if (!preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $values['slug'])) {
        $errors['slug'] = 'Usa minúsculas sin acentos, números '
            . 'y guiones simples.';
    }
    if ($values['body'] === '') {
        $errors['body'] = 'Escribe el contenido del artículo.';
    }

    return ['values' => $values, 'errors' => $errors];
}
