<?php

use Psr\Http\Message\ResponseInterface as Response;

class GetAll
{
    private string $sql;
    private Response $response;
    private mixed $result;
    private mixed $error;
    private int $count = -1;

    public function __construct(string $sql, Response $response)
    {
        $this->sql = $sql;
        $this->response = $response;
    }

    public function evaluate(): void
    {
        try {
            $db = new DB();
            $conn = $db->connect();

            $statement = $conn->query($this->sql);
            $this->result = $statement->fetchAll(PDO::FETCH_OBJ);
            $this->count = $statement->rowCount();

            $db = null;
        } catch (PDOException $e) {
            $this->error = array(
                "Message" => $e->getMessage()
            );
        }
    }

    public function return(): Response
    {
        try {
            if ($this->count != 0) {
                $this->response->getBody()->write(json_encode($this->result));
                return $this->response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $this->response->getBody()->write(json_encode("SQL not found"));
                return $this->response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(404);
            }
        } catch (PDOException) {
            $this->response->getBody()->write(json_encode($this->error));
            return $this->response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    }

    public function getterCount(): int
    {
        return $this->count;
    }

    public function getterResult(): mixed
    {
        return $this->result;
    }
}