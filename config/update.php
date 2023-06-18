<?php

use Psr\Http\Message\ResponseInterface as Response;

class Update
{
    private string $sql;
    private Response $response;
    private mixed $result = null; // Initialize the $result property
    private mixed $error = null;
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

            $statement = $conn->prepare($this->sql);
            $this->result = $statement->execute();
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
        if($this->error != null) {
            $this->response->getBody()->write(json_encode($this->error));
            return $this->response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }else {
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
        }
    }
}