<?php

final class ArticleRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listPage(int $limit, int $offset): array
    {
        $statement = $this->pdo->prepare('
            SELECT slug, title
            FROM articles
            ORDER BY id
            LIMIT :limit OFFSET :offset
        ');
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->pdo->prepare('
            SELECT title, body
            FROM articles
            WHERE slug = :slug
        ');
        $statement->execute(['slug' => $slug]);
        $article = $statement->fetch();

        return $article === false ? null : $article;
    }
}
