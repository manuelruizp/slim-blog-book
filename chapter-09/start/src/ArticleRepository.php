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

    public function create(string $slug, string $title, string $body): bool
    {
        $statement = $this->pdo->prepare('
            INSERT INTO articles (slug, title, body)
            VALUES (:slug, :title, :body)
            ON CONFLICT (slug) DO NOTHING
        ');
        $statement->execute([
            'slug' => $slug,
            'title' => $title,
            'body' => $body,
        ]);

        return $statement->rowCount() === 1;
    }

    public function listAll(): array
    {
        return $this->pdo->query('
            SELECT id, slug, title
            FROM articles
            ORDER BY id DESC
        ')->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('
            SELECT id, slug, title, body
            FROM articles
            WHERE id = :id
        ');
        $statement->execute(['id' => $id]);
        $article = $statement->fetch();

        return $article === false ? null : $article;
    }

    public function update(
        int $id,
        string $slug,
        string $title,
        string $body
    ): bool {
        $statement = $this->pdo->prepare('
            UPDATE OR IGNORE articles
            SET slug = :slug, title = :title, body = :body
            WHERE id = :id
        ');
        $statement->execute([
            'id' => $id,
            'slug' => $slug,
            'title' => $title,
            'body' => $body,
        ]);

        return $statement->rowCount() === 1;
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('
            DELETE FROM articles WHERE id = :id
        ');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() === 1;
    }
}
