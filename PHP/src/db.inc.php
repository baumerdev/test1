<?php

class Database
{
    private PDO $pdo;

    public function __construct(string $database)
    {
        $this->pdo = new PDO('sqlite:'.$database);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->createDatabase($database);
    }

    public function addEntry(
        string $uploadPath,
        string $filename,
        string $mimeType,
        int $sizeBytes,
        ?string $comment
    ): void {
        $query = "INSERT INTO files (upload_path, filename, mime_type, size_bytes, comment) 
        VALUES (:upload_path, :filename, :mime_type, :size_bytes, :comment)
        ";

        $stmt = $this->pdo->prepare($query);

        // Bind values to the statement
        $stmt->bindValue(':upload_path', $uploadPath);
        $stmt->bindValue(':filename', $filename);
        $stmt->bindValue(':mime_type', $mimeType);
        $stmt->bindValue(':size_bytes', $sizeBytes);
        $stmt->bindValue(':comment', $comment !== null && strlen($comment) > 0 ? $comment : null);

        $stmt->execute();
    }

    public function getAllEntries(): array|false
    {
        $query = "SELECT * FROM files ORDER BY upload_date DESC";
        $statement = $this->pdo->query($query);
        if (!$statement) {
            return false;
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEntry(int $id): mixed
    {
        $query = "SELECT * FROM files WHERE id = :id";
        $statement = $this->pdo->prepare($query);

        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    private function createDatabase($database): void
    {
        if (filesize($database) > 0) {
            return;
        };

        $query = "
            CREATE TABLE files (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                upload_path TEXT NOT NULL,
                filename TEXT NOT NULL,
                mime_type TEXT NOT NULL,
                size_bytes INTEGER NOT NULL,
                comment TEXT,
                upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ";

        $this->pdo->exec($query);
    }
}
